<?php
/**
 * Test case for post types.
 */
class CPTC_Test_Delete extends CPTC_Post_Type_Cleanup_UnitTestCase {

	/**
	 * Set up.
	 */
	function setUp() {
		$this->cleanup    = new CPTC_Post_Type_Cleanup();
		$user_id          = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user             = wp_set_current_user( $user_id );
		$this->set_batch_size( 5 );
	}

	/**
	 * Test deleting posts in batches.
	 */
	function test_admin_page_deleting_posts_in_batches() {
		$posts = $this->create_not_registered_post_type_posts( 'cpt', 10 );
		$this->assertEquals( 10, count( get_posts( 'post_type=cpt&posts_per_page=-1' ) ) );
		$this->mock_admin_page_globals();
		$this->cleanup->register_post_type();
		$admin_page = $this->get_admin_page();
		$posts_remaining = get_posts( 'post_type=cpt&posts_per_page=-1' );
		$this->assertEquals( 5, count( $posts_remaining ) );
	}

	/**
	 * Test deleting posts in batches without incorrectly deleting built in post types.
	 *
	 * @depends test_admin_page_deleting_posts_in_batches
	 */
	function test_admin_page_deleting_posts_in_batches_correct_post_type() {
		$posts = $this->create_not_registered_post_type_posts( 'cpt', 5 );
		$this->mock_admin_page_globals();
		$this->cleanup->register_post_type();
		$admin_page = $this->get_admin_page();
		$this->assertEquals( 0, count( get_posts( 'post_type=cpt&posts_per_page=-1' ) ) );
		$admin_page = $this->get_admin_page();
		$this->assertEquals( 5, count( get_posts( 'posts_per_page=-1' ) ) );
	}
}
