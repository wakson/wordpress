<?php
/**
 * Tests the Style Engine CSS Rules Store class.
 *
 * @package WordPress
 * @subpackage StyleEngine
 * @since 6.1.0
 *
 * @group styleEngine
 */

/**
 * Tests for registering, storing and retrieving a collection of CSS Rules (a store).
 *
 * @coversDefaultClass WP_Style_Engine_CSS_Rules_Store
 */
class Tests_Style_Engine_wpStyleEngineCSSRulesStore extends WP_UnitTestCase {
	/**
	 * Tear down after each test.
	 */
	public function tear_down() {
		WP_Style_Engine_CSS_Rules_Store::remove_all_stores();
		parent::tear_down();
	}

	/**
	 * Test creating a new store on instantiation.
	 *
	 * @ticket 56467
	 * @covers ::__construct
	 */
	public function test_should_create_new_store_on_instantiation() {
		$new_pancakes_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'pancakes-with-strawberries' );
		$this->assertInstanceOf( 'WP_Style_Engine_CSS_Rules_Store', $new_pancakes_store );
	}

	/**
	 * Tests that a `$store_name` argument is required and no store will be created without one.
	 *
	 * @ticket 56467
	 * @covers ::get_store
	 */
	public function test_should_not_create_store_without_a_store_name() {
		$not_a_store = WP_Style_Engine_CSS_Rules_Store::get_store( '' );
		$this->assertEmpty( $not_a_store );

		$also_not_a_store = WP_Style_Engine_CSS_Rules_Store::get_store( 123 );
		$this->assertEmpty( $also_not_a_store );

		$definitely_not_a_store = WP_Style_Engine_CSS_Rules_Store::get_store( null );
		$this->assertEmpty( $definitely_not_a_store );
	}

	/**
	 * Tests returning a previously created store when the same selector key is passed.
	 *
	 * @ticket 56467
	 * @covers ::get_store
	 */
	public function test_should_return_existing_store() {
		$new_fish_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'fish-n-chips' );
		$selector       = '.haddock';

		$new_fish_store->add_rule( $selector )->get_selector();
		$this->assertEquals( $selector, $new_fish_store->add_rule( $selector )->get_selector() );

		$the_same_fish_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'fish-n-chips' );
		$this->assertEquals( $selector, $the_same_fish_store->add_rule( $selector )->get_selector() );
	}

	/**
	 * Tests returning all previously created stores.
	 *
	 * @ticket 56467
	 * @covers ::get_stores
	 */
	public function test_should_get_all_existing_stores() {
		$burrito_store    = WP_Style_Engine_CSS_Rules_Store::get_store( 'burrito' );
		$quesadilla_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'quesadilla' );
		$this->assertEquals(
			array(
				'burrito'    => $burrito_store,
				'quesadilla' => $quesadilla_store,
			),
			WP_Style_Engine_CSS_Rules_Store::get_stores()
		);
	}

	/**
	 * Tests that all previously created stores are deleted.
	 *
	 * @ticket 56467
	 * @covers ::remove_all_stores
	 */
	public function test_should_remove_all_stores() {
		$dolmades_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'dolmades' );
		$tzatziki_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'tzatziki' );
		$this->assertEquals(
			array(
				'dolmades' => $dolmades_store,
				'tzatziki' => $tzatziki_store,
			),
			WP_Style_Engine_CSS_Rules_Store::get_stores()
		);
		WP_Style_Engine_CSS_Rules_Store::remove_all_stores();
		$this->assertEquals(
			array(),
			WP_Style_Engine_CSS_Rules_Store::get_stores()
		);
	}

	/**
	 * Tests adding rules to an existing store.
	 *
	 * @ticket 56467
	 * @covers ::add_rule
	 */
	public function test_should_add_rule_to_existing_store() {
		$new_pie_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'meat-pie' );
		$selector      = '.wp-block-sauce a:hover';
		$store_rule    = $new_pie_store->add_rule( $selector );
		$expected      = '';
		$this->assertEquals( $expected, $store_rule->get_css() );

		$pie_declarations = array(
			'color'         => 'brown',
			'border-color'  => 'yellow',
			'border-radius' => '10rem',
		);
		$css_declarations = new WP_Style_Engine_CSS_Declarations( $pie_declarations );
		$store_rule->add_declarations( $css_declarations );

		$store_rule = $new_pie_store->add_rule( $selector );
		$expected   = "$selector{{$css_declarations->get_declarations_string()}}";
		$this->assertEquals( $expected, $store_rule->get_css() );
	}

	/**
	 * Tests that all stored rule objects are returned.
	 *
	 * @ticket 56467
	 * @covers ::get_all_rules
	 */
	public function test_should_get_all_rule_objects_for_a_store() {
		$new_pizza_store = WP_Style_Engine_CSS_Rules_Store::get_store( 'pizza-with-mozzarella' );
		$selector        = '.wp-block-anchovies a:hover';
		$store_rule      = $new_pizza_store->add_rule( $selector );
		$expected        = array(
			$selector => $store_rule,
		);

		$this->assertEquals( $expected, $new_pizza_store->get_all_rules() );

		$pizza_declarations = array(
			'color'         => 'red',
			'border-color'  => 'yellow',
			'border-radius' => '10rem',
		);
		$css_declarations   = new WP_Style_Engine_CSS_Declarations( $pizza_declarations );
		$store_rule->add_declarations( array( $css_declarations ) );

		$expected = array(
			$selector => $store_rule,
		);
		$this->assertEquals( $expected, $new_pizza_store->get_all_rules() );

		$new_pizza_declarations = array(
			'color'        => 'red',
			'border-color' => 'red',
			'font-size'    => '10rem',
		);
		$css_declarations       = new WP_Style_Engine_CSS_Declarations( $new_pizza_declarations );
		$store_rule->add_declarations( array( $css_declarations ) );

		$expected = array(
			$selector => $store_rule,
		);
		$this->assertEquals( $expected, $new_pizza_store->get_all_rules() );

		$new_selector             = '.wp-block-mushroom a:hover';
		$newer_pizza_declarations = array(
			'padding' => '100px',
		);
		$new_store_rule           = $new_pizza_store->add_rule( $new_selector );
		$css_declarations         = new WP_Style_Engine_CSS_Declarations( $newer_pizza_declarations );
		$new_store_rule->add_declarations( array( $css_declarations ) );

		$expected = array(
			$selector     => $store_rule,
			$new_selector => $new_store_rule,
		);
		$this->assertEquals( $expected, $new_pizza_store->get_all_rules() );
	}
}
