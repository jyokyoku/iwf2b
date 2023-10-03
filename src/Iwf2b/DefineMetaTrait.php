<?php

namespace Iwf2b;

trait DefineMetaTrait {
	private $reflection;

	private $meta_defines;

	private function get_reflection() {
		if ( $this->reflection === null ) {
			$this->reflection = new \ReflectionClass( $this );
		}

		return $this->reflection;
	}

	public function get_meta_defines() {
		if ( $this->meta_defines === null ) {
			$ref       = $this->get_reflection();
			$constants = $ref->getConstants();
			$metas     = [];

			foreach ( $constants as $constant_name => $meta_key ) {
				if ( strpos( $constant_name, 'MK_' ) === 0 && is_string( $meta_key ) ) {
					$metas[] = $meta_key;
				}
			}

			$this->meta_defines = $metas;
		}

		return $this->meta_defines;
	}
}
