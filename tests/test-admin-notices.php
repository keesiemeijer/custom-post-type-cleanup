<?php
/**
 * Test case for admin notices after deleting post in batches.
 */
class CPTC_Test_Admin_Notices extends CPTC_Post_Type_Cleanup_UnitTestCase {

	protected $cleanup;

	/**
	 * Set up.
	 */
	function set_up() {
		parent::set_up();
		$this->set_batch_size( 5 );
		_delete_all_posts();
		add_filter( 'wp_die_handler', array( $this, 'get_wp_die_handler' ), 10 , 3 );
	}

	/**
	 * Test data provider
	 */
	public function batch_provider() {
		return array(
			// Single form, 1 post type.
			array(
				array( 'cpt' => 2 ),
				1,
				array(
					'1 post from 1 unused custom post type detected',
					'Deleted 1 post from the post type: cpt',
					'Still 1 post left in the database from the post type: cpt',
				),
			),
			// Plural form, 1 post type.
			array(
				array( 'cpt' => 5 ),
				2,
				array(
					'3 posts from 1 unused custom post type detected',
					'Deleted 2 posts from the post type: cpt',
					'Still 3 posts left in the database from the post type: cpt',
				),
			),
			// Single form, multiple post types
			array(
				array( 'cpt' => 2, 'cpt2' => 2 ),
				1,
				array(
					'3 posts from 2 unused custom post types detected',
					'Deleted 1 post from the post type: cpt',
					'Still 1 post left in the database from the post type: cpt',
				),
			),
			// Plural form, multiple post types.
			array(
				array( 'cpt' => 5, 'cpt2' => 2 ),
				2,
				array(
					'5 posts from 2 unused custom post types detected',
					'Deleted 2 posts from the post type: cpt',
					'Still 3 posts left in the database from the post type: cpt',
				),
			),
			// No posts found.
			array( array( 'cpt' => 5 ), 5, 'There are no unused custom post type posts found' ),
			array( array( 'cpt' => 5, 'cpt2' => 2 ), 5, 'No more posts left in the database from the post type: cpt' ),
			array( array(), 5, 'Notice: No posts found for the post type: cpt' ),
		);
	}

	/**
	 * Test notices when deleting posts in batches.
	 *
	 * @dataProvider batch_provider
	 * @param array        $post_types Array with post types.
	 * @param int          $batch      Batch size.
	 * @param array|string $notices    Admin notices.
	 */
	function test_batch_admin_notices( $post_types, $batch, $notices ) {
		$i = 0;
		foreach ( $post_types as $post_type => $count ) {
			$count = ! $i ? $count + $batch : $count;
			$posts = $this->create_not_registered_post_type_posts( $post_type, $count, false );
			++$i;
		}

		$this->set_batch_size( $batch );
		$this->mock_admin_page_globals();
		$this->cleanup->register_post_type();

		// Deletes the first post type posts to the post type count in the provider.
		$admin_page = $this->get_admin_page();

		// Deletes posts in a batch.
		$admin_page = $this->get_admin_page();

		if ( is_array( $notices ) ) {
			foreach ( $notices as $notice ) {
				$this->assertStringContainsString( $notice, $admin_page );
			}
		} else {
			$this->assertStringContainsString( $notices, $admin_page );
		}
	}

	/**
	 * Test invalid post type in $_POST request. (should never happen)
	 */
	function test_batch_error_invalid_post_type() {
		$this->mock_admin_page_globals( 'invalid_post_type' );
		$this->cleanup->register_post_type();
		$admin_page = $this->get_admin_page();
		$this->assertStringContainsString( 'Error: invalid post type', $admin_page );
	}

	/**
	 * Test delete capability message
	 */
	function test_delete_capability() {
		$this->create_not_registered_post_type_posts( 'cpt', 10 );
		$current_user = get_current_user_id();
		$user = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$role = get_role( 'administrator' );
		$role->remove_cap( 'delete_posts' );
		wp_set_current_user( $user );

		ob_start();
		$this->cleanup->admin_menu();
		$admin_page = ob_get_clean();

		$this->assertStringContainsString( "You don't have sufficient permissions to access this page", $admin_page );

		$role->add_cap( 'delete_posts' );
		wp_set_current_user( $current_user );
	}

	/**
	 * Test admin page without submitting form and no unused post types found.
	 */
	function test_admin_page_without_submitting_form_and_no_unused_post_types() {
		$_SERVER['REQUEST_METHOD'] = '';
		$admin_page = $this->get_admin_page();
		$this->assertStringNotContainsString( 'Error:', $admin_page );
		$this->assertStringNotContainsString( 'Notice:', $admin_page );
	}

	function get_wp_die_handler( $handler, $title = '', $args = array() ) {

		return array( $this, 'wp_die_handler' );
	}
	function wp_die_handler( $message, $title = '', $args = array() ) {
		echo $message;
	}
}
