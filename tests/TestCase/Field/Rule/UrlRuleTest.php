<?php

namespace Theme\Field\Rule;

use Iwf2b\Field\Rule\UrlRule;

class UrlRuleTest extends \WP_UnitTestCase {
	/**
	 * @dataProvider valid_urls
	 */
	public function test_validate_valid_url( $url ) {
		$rule = new UrlRule();
		$rule->set_value( $url );

		$this->assertTrue( $rule->validate() );
	}

	/**
	 * @dataProvider valid_relative_urls
	 */
	public function test_validate_valid_relative_url( $url ) {
		$rule = new UrlRule( [ 'relative' => true ] );
		$rule->set_value( $url );

		$this->assertTrue( $rule->validate() );
	}

	/**
	 * @dataProvider invalid_urls
	 */
	public function test_validate_invalid_url( $url ) {
		$rule = new UrlRule();
		$rule->set_value( $url );

		$this->assertFalse( $rule->validate() );
	}

	/**
	 * @dataProvider invalid_relative_urls
	 */
	public function test_validate_invalid_relative_url( $url ) {
		$rule = new UrlRule( [ 'relative' => true ] );
		$rule->set_value( $url );

		$this->assertFalse( $rule->validate() );
	}

	/**
	 * @dataProvider valid_custom_protocol_urls
	 */
	public function test_custom_protocol_url( $url ) {
		$rule = new UrlRule( [ 'protocols' => [ 'ftp', 'file', 'git' ] ] );
		$rule->set_value( $url );

		$this->assertTrue( $rule->validate() );
	}

	public function valid_urls() {
		return [
			[ 'http://a.pl' ],
			[ 'http://www.example.com' ],
			[ 'http://www.example.com.' ],
			[ 'http://www.example.museum' ],
			[ 'https://example.com/' ],
			[ 'https://example.com:80/' ],
			[ 'http://examp_le.com' ],
			[ 'http://www.sub_domain.examp_le.com' ],
			[ 'http://www.example.coop/' ],
			[ 'http://www.test-example.com/' ],
			[ 'http://www.symfony.com/' ],
			[ 'http://symfony.fake/blog/' ],
			[ 'http://symfony.com/?' ],
			[ 'http://symfony.com/search?type=&q=url+validator' ],
			[ 'http://symfony.com/#' ],
			[ 'http://symfony.com/#?' ],
			[ 'http://www.symfony.com/doc/current/book/validation.html#supported-constraints' ],
			[ 'http://very.long.domain.name.com/' ],
			[ 'http://localhost/' ],
			[ 'http://myhost123/' ],
			[ 'http://127.0.0.1/' ],
			[ 'http://127.0.0.1:80/' ],
			[ 'http://[::1]/' ],
			[ 'http://[::1]:80/' ],
			[ 'http://[1:2:3::4:5:6:7]/' ],
			[ 'http://sãopaulo.com/' ],
			[ 'http://xn--sopaulo-xwa.com/' ],
			[ 'http://sãopaulo.com.br/' ],
			[ 'http://xn--sopaulo-xwa.com.br/' ],
			[ 'http://пример.испытание/' ],
			[ 'http://xn--e1afmkfd.xn--80akhbyknj4f/' ],
			[ 'http://مثال.إختبار/' ],
			[ 'http://xn--mgbh0fb.xn--kgbechtv/' ],
			[ 'http://例子.测试/' ],
			[ 'http://xn--fsqu00a.xn--0zwm56d/' ],
			[ 'http://例子.測試/' ],
			[ 'http://xn--fsqu00a.xn--g6w251d/' ],
			[ 'http://例え.テスト/' ],
			[ 'http://xn--r8jz45g.xn--zckzah/' ],
			[ 'http://مثال.آزمایشی/' ],
			[ 'http://xn--mgbh0fb.xn--hgbk6aj7f53bba/' ],
			[ 'http://실례.테스트/' ],
			[ 'http://xn--9n2bp8q.xn--9t4b11yi5a/' ],
			[ 'http://العربية.idn.icann.org/' ],
			[ 'http://xn--ogb.idn.icann.org/' ],
			[ 'http://xn--e1afmkfd.xn--80akhbyknj4f.xn--e1afmkfd/' ],
			[ 'http://xn--espaa-rta.xn--ca-ol-fsay5a/' ],
			[ 'http://xn--d1abbgf6aiiy.xn--p1ai/' ],
			[ 'http://☎.com/' ],
			[ 'http://username:password@symfony.com' ],
			[ 'http://user.name:password@symfony.com' ],
			[ 'http://user_name:pass_word@symfony.com' ],
			[ 'http://username:pass.word@symfony.com' ],
			[ 'http://user.name:pass.word@symfony.com' ],
			[ 'http://user-name@symfony.com' ],
			[ 'http://user_name@symfony.com' ],
			[ 'http://symfony.com?' ],
			[ 'http://symfony.com?query=1' ],
			[ 'http://symfony.com/?query=1' ],
			[ 'http://symfony.com#' ],
			[ 'http://symfony.com#fragment' ],
			[ 'http://symfony.com/#fragment' ],
			[ 'http://symfony.com/#one_more%20test' ],
			[ 'http://example.com/exploit.html?hello[0]=test' ],
		];
	}

	public function valid_relative_urls() {
		return [
			[ '//example.com' ],
			[ '//examp_le.com' ],
			[ '//symfony.fake/blog/' ],
			[ '//symfony.com/search?type=&q=url+validator' ],
		];
	}

	public function invalid_urls() {
		return [
			[ 'example.com' ],
			[ '://example.com' ],
			[ 'http ://example.com' ],
			[ 'http:/example.com' ],
			[ 'http://example.com::aa' ],
			[ 'http://example.com:aa' ],
			[ 'ftp://example.fr' ],
			[ 'faked://example.fr' ],
			[ 'http://127.0.0.1:aa/' ],
			[ 'ftp://[::1]/' ],
			[ 'http://[::1' ],
			[ 'http://hello.☎/' ],
			[ 'http://:password@symfony.com' ],
			[ 'http://:password@@symfony.com' ],
			[ 'http://username:passwordsymfony.com' ],
			[ 'http://usern@me:password@symfony.com' ],
			[ 'http://example.com/exploit.html?<script>alert(1);</script>' ],
			[ 'http://example.com/exploit.html?hel lo' ],
			[ 'http://example.com/exploit.html?not_a%hex' ],
			[ 'http://' ],
		];
	}

	public function invalid_relative_urls() {
		return [
			[ '/example.com' ],
			[ '//example.com::aa' ],
			[ '//example.com:aa' ],
			[ '//127.0.0.1:aa/' ],
			[ '//[::1' ],
			[ '//hello.☎/' ],
			[ '//:password@symfony.com' ],
			[ '//:password@@symfony.com' ],
			[ '//username:passwordsymfony.com' ],
			[ '//usern@me:password@symfony.com' ],
			[ '//example.com/exploit.html?<script>alert(1);</script>' ],
			[ '//example.com/exploit.html?hel lo' ],
			[ '//example.com/exploit.html?not_a%hex' ],
			[ '//' ],
		];
	}

	public function valid_custom_protocol_urls() {
		return [
			[ 'ftp://example.com' ],
			[ 'file://127.0.0.1' ],
			[ 'git://[::1]/' ],
		];
	}
}