<?php
/**
 * Test case for post types.
 */
class CPTC_Test_Post_Types extends CPTC_Post_Type_Cleanup_UnitTestCase {

	/**
	 * Set up.
	 */
	function set_up() {
		parent::set_up();
		$this->set_batch_size( 5 );
	}

	/**
	 * Test default post types found in database.
	 */
	function test_database_post_types_default() {
		$this->create_posts( 'post' );
		$post_types = cptc_db_post_types();
		$this->assertEquals( array( 'post' ), $post_types );
	}

	/**
	 * Test all database post types
	 */
	function test_database_post_types() {
		$this->create_not_registered_post_type_posts();
		$post_types = cptc_db_post_types();
		$this->assertEquals( array( 'cpt', 'post' ), $post_types );
	}

	/**
	 * Test if an unused post type is found.
	 *
	 * @depends test_database_post_types
	 */
	function test_unused_post_type_found() {
		$this->create_not_registered_post_type_posts();
		$this->assertEquals( array( 'cpt' ), cptc_get_unused_post_types() );
	}

	/**
	 * Test if an unused post type is re-registered.
	 *
	 * @depends test_unused_post_type_found
	 */
	function test_unused_post_type_is_registered() {
		$this->create_not_registered_post_type_posts();
		$this->mock_admin_page_globals( 'cpt' );
		$this->cleanup->register_post_type();
		$this->assertTrue( post_type_exists( 'cpt' ) );
	}
}
