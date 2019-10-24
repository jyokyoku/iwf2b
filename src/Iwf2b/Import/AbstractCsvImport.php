<?php

namespace Iwf2b\Import;

/**
 * Class AbstractCsvImport
 * @package Iwf2b\Import
 */
abstract class AbstractCsvImport {
	const LOG_DEBUG = 1;

	const LOG_ROW_NUM = 2;

	/**
	 * Output type
	 *
	 * @var string
	 */
	protected $log_type = 'echo';

	/**
	 * Debug mode
	 *
	 * @var bool
	 */
	protected $debug = true;

	/**
	 * Show progress bar
	 *
	 * @var bool
	 */
	protected $show_progress = false;

	/**
	 * CSV control
	 *
	 * @var string
	 */
	protected $csv_control = ',';

	/**
	 * CSV file path
	 *
	 * @var string
	 */
	protected $file_path = '';

	/**
	 * CSV charset
	 *
	 * @var string
	 */
	protected $from_charset = 'sjis-win';

	/**
	 * DB charset
	 *
	 * @var string
	 */
	protected $to_charset = 'UTF-8';

	/**
	 * Start time
	 *
	 * @var int
	 */
	protected $start_time = 0;

	/**
	 * Current row number
	 *
	 * @var int
	 */
	protected $row_num = 0;

	/**
	 * Current row data
	 *
	 * @var array
	 */
	protected $row = [];

	/**
	 * Current row size
	 *
	 * @var int
	 */
	protected $row_size = 0;

	/**
	 * Total row number
	 *
	 * @var int
	 */
	protected $total_rows = 0;

	/**
	 * Log dir
	 *
	 * @var string
	 */
	protected $log_dir = '';

	/**
	 * Current log session
	 *
	 * @var string
	 */
	protected $log_session = '';

	/**
	 * Cache clear threshold
	 *
	 * @var int
	 */
	protected $cache_clear_threshold = 50;

	/**
	 * AbstractCsvImport constructor.
	 *
	 * @param string $log_type
	 */
	public function __construct( $log_type = 'echo' ) {
		if ( $this->is_cli() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			$this->log_type = 'file';

		} else {
			$this->log_type = $log_type;
		}

		$this->log_session = date( 'Ymdhis_' ) . trim( str_replace( array( strtolower( __NAMESPACE__ ), 'import' ), '', strtolower( get_called_class() ) ), '\\' );
		$this->log_dir     = WP_CONTENT_DIR . '/csv-logs';
	}

	/**
	 * @return bool
	 */
	protected function is_cli() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * @param $dir
	 */
	public function set_log_dir( $dir ) {
		$this->log_dir = $dir;
	}

	/**
	 * @param bool $mode
	 */
	public function set_debug_mode( $mode = true ) {
		$this->debug = (bool) $mode;
	}

	/**
	 * @param bool $show_progress
	 */
	public function set_show_progress( $show_progress = true ) {
		$this->show_progress = (bool) $show_progress;
	}

	/**
	 * @param $csv_control
	 */
	public function set_csv_control( $csv_control ) {
		$this->csv_control = $csv_control;
	}

	/**
	 * @param $file_path
	 */
	public function set_file_path( $file_path ) {
		$this->file_path = $file_path;
	}

	/**
	 * @param $encode
	 */
	public function set_from_charset( $encode ) {
		$this->from_charset = $encode;
	}

	/**
	 * @param $encode
	 */
	public function set_to_charset( $encode ) {
		$this->to_charset = $encode;
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return bool
	 * @throws \WP_CLI\ExitException
	 */
	public function do_import( $offset = 0, $limit = 0 ) {
		set_time_limit(0);

		if ( $this->to_charset != $this->from_charset ) {
			$tmp_dir       = WP_CONTENT_DIR . '/tmp_csv';
			$tmp_file_path = $tmp_dir . '/' . md5( uniqid( mt_rand(), true ) ) . '.' . pathinfo( $this->file_path, PATHINFO_EXTENSION );

			if ( ! is_dir( $tmp_dir ) ) {
				if ( ! @mkdir( $tmp_dir, 0777, true ) && ! is_dir( $tmp_dir ) ) {
					$this->log_error( 'テンポラリファイル格納用のディレクトリが生成できません。' );

					return false;
				}
			}

			$file_data = file_get_contents( $this->file_path );
			file_put_contents( $tmp_file_path, mb_convert_encoding( $file_data, $this->to_charset, $this->from_charset ) );

		} else {
			$tmp_file_path = $this->file_path;
		}

		$splFileObject = new \SplFileObject( $tmp_file_path );
		$splFileObject->setFlags( \SplFileObject::READ_CSV );
		$splFileObject->setCsvControl( $this->csv_control );

		// 全行数を取得
		$splFileObject->seek( PHP_INT_MAX );
		$this->total_rows = $splFileObject->key() + 1;

		// ポインタを先頭に戻す
		$splFileObject->rewind();

		if ( $this->is_cli() && $this->show_progress ) {
			$total_lines = $this->total_rows - $offset;

			if ( $limit > 0 && $limit < $total_lines ) {
				$total_lines = $limit;
			}

			$progress = \WP_CLI\Utils\make_progress_bar( 'Importing...', $total_lines, 100 );
		}

		$this->log_info( sprintf( 'CSVインポートを開始（全行数: %d）...', $this->total_rows ) );

		foreach ( $splFileObject as $i => $row ) {
			if ( $offset && $i + 1 <= $offset ) {
				continue;
			}

			if ( $this->is_cli() && $this->show_progress ) {
				$progress->tick();
			}

			$this->start_time = microtime( true );
			$this->row_num    = $i;
			$this->row        = array_map( 'trim', $row );
			$this->row_size   = count( $this->row );

			$result = $this->process_row();

			if ( $result === false ) {
				$this->log_error( 'CSVインポートが中止されました。' );

				return false;
			}

			if ( $limit > 0 && $offset + $limit <= $i + 1 ) {
				$this->log_info( '読み込み最大数に達しました。' );

				break;
			}

			if ( $i > 0 && $i % $this->cache_clear_threshold == 0 ) {
				wp_cache_flush();
			}
		}

		$this->log_success( 'CSVインポートを完了しました。' );

		if ( $this->is_cli() && $this->show_progress ) {
			$progress->finish();
		}

		if ( $this->to_charset != $this->from_charset ) {
			@unlink( $tmp_file_path );
		}

		return true;
	}

	abstract protected function process_row();

	/**
	 * @param $col_num
	 *
	 * @return mixed|null
	 */
	protected function get_col( $col_num ) {
		return isset( $this->row[ $col_num ] ) ? $this->row[ $col_num ] : null;
	}

	/**
	 * @param string $message
	 * @param int $flag
	 *
	 * @throws \WP_CLI\ExitException
	 */
	protected function log_success( $message, $flag = null ) {
		$this->log( $message, 'Success', '#00ba15', $flag );
	}

	/**
	 * @param string $message
	 * @param int $flag
	 *
	 * @throws \WP_CLI\ExitException
	 */
	protected function log_error( $message, $flag = null ) {
		$this->log( $message, 'Error', '#f00', $flag );
	}

	/**
	 * @param string $message
	 * @param int $flag
	 *
	 * @throws \WP_CLI\ExitException
	 */
	protected function log_info( $message, $flag = null ) {
		$this->log( $message, 'Info', '#0063ff', $flag );
	}

	/**
	 * @param string $message
	 * @param string $type
	 * @param string $color
	 * @param int $flag
	 *
	 * @throws \WP_CLI\ExitException
	 */
	protected function log( $message, $type, $color, $flag = null ) {
		if ( ! $this->debug && $flag & static::LOG_DEBUG ) {
			return;
		}

		if ( $flag & static::LOG_ROW_NUM ) {
			$message = 'Line ' . $this->row_num . ': ' . $message;
		}

		if ( $this->is_cli() && ! $this->show_progress ) {
			if ( $type === 'Info' ) {
				\WP_CLI::line( $message );

			} else if ( $type === 'Success' ) {
				\WP_CLI::success( $message );

			} else if ( $type === 'Error' ) {
				\WP_CLI::error( $message, false );
			}
		}

		$type = str_pad( $type, 7, ' ', STR_PAD_RIGHT );

		if ( $this->log_type === 'echo' ) {
			echo "<span style='color: {$color}'>[{$type}]</span> - {$message}<br>";

			if ( ob_get_level() > 0 ) {
				ob_flush();
			}

			flush();

		} else if ( $this->log_type === 'file' ) {
			$this->write_log( "[CSV Import] [{$type}] - {$message}" );
		}
	}

	/**
	 * @param null $message
	 */
	protected function write_log( $message = null ) {
		if ( ! is_string( $message ) ) {
			$message = print_r( $message, true );
		}

		if ( ! is_dir( $this->log_dir ) ) {
			if ( ! @mkdir( $this->log_dir ) && ! is_dir( $this->log_dir ) ) {
				trigger_error( 'Could not make a log directory.', E_USER_WARNING );

				return;
			}
		}

		$log_file = $this->log_dir . '/' . $this->log_session . '.txt';

		if ( ! is_file( $log_file ) ) {
			if ( ! @touch( $log_file ) ) {
				trigger_error( 'Could not make a log file.', E_USER_WARNING );

				return;
			}
		}

		$time = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		$line = sprintf( '[%s] %s', $time, $message );
		$line .= PHP_EOL;

		$fh = fopen( $log_file, 'ab' );

		if ( $fh ) {
			fwrite( $fh, $line );
			fclose( $fh );
		}
	}
}