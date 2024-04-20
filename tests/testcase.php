<?php
/**
 * Custom Post Type Cleanup Unit TestCase
 *
 * @package Custom Post Type Cleanup
 */
class CPTC_Post_Type_Cleanup_UnitTestCase extends WP_UnitTestCase {

	protected $cleanup;
	protected $user_id;
	protected $batch_size;

	/**
	 * Set up.
	 */
	function set_up() {
		parent::set_up();
		$this->cleanup = new CPTC_Post_Type_Cleanup();
		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user          = wp_set_current_user( $this->user_id );
	}

	function tear_down() {
		parent::tear_down();
		$this->remove_filter();
	}

	/**
	 * Creates posts.
	 *
	 * @param string  $post_type      Post type. Default 'cpt'.
	 * @param integer $posts_per_page How may posts to create. Defaut 5.
	 * @param bool    $delete         Delete posts before creatin posts. Default true.
	 * @return array                  Array with posts.
	 */
	function create_posts( $post_type = 'cpt', $posts_per_page = 5, $delete = true ) {
		if ( $delete ) {
			_delete_all_posts();
		}

		if ( ! post_type_exists( $post_type ) ) {
			$this->register_post_type( $post_type );
		}

		if ( 'post' !== $post_type ) {
			// Also create normal posts for testing.
			$this->factory->post->create_many( $posts_per_page );
		}

		// Create custom post type posts.
		$this->factory->post->create_many( $posts_per_page,
			array(
				'post_type' => $post_type,
			)
		);

		// Return posts.
		$posts = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => $post_type,
			) );

		return $posts;
	}

	/**
	 * Creates posts for a custom post types and unregisters the post type after.
	 *
	 * @param string  $post_type      Post type. Default 'cpt'.
	 * @param integer $posts_per_page How may posts to create. Defaut 5.
	 * @param bool    $delete         Delete posts before creatin posts. Default true.
	 */
	function create_not_registered_post_type_posts( $post_type = 'cpt', $posts_per_page = 5, $delete = true ) {
		$posts = $this->create_posts( $post_type, $posts_per_page, $delete );
		unregister_post_type( $post_type );
		return $posts;
	}

	/**
	 * Registers a post type.
	 *
	 * @param string $post_type Post type name. Default 'cpt'.
	 */
	function register_post_type( $post_type = 'cpt' ) {
		$args = array( 'public' => true, 'has_archive' => true, 'label' => 'Custom Post Type' );
		register_post_type( $post_type, $args );
	}

	/**
	 * Mocks the form field values and request globals.
	 *
	 * @param string $post_type Post type. Default 'cpt'.
	 */
	function mock_admin_page_globals( $post_type = 'cpt' ) {
		$_REQUEST['security'] = wp_create_nonce( 'custom_post_type_cleanup_nonce' );
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['cptc_delete'] = true;
		$_POST['cptc_post_type'] = $post_type;
	}

	/**
	 * Returns the output from the plugin admin page
	 *
	 * @return string Plugin admin page HTML.
	 */
	function get_admin_page() {
		ob_start();
		$this->cleanup->admin_page();
		$admin_page = ob_get_clean();
		return $admin_page;
	}

	/**
	 * Sets the batch size
	 *
	 * @param integer $size Number of posts to delete in one batch.
	 */
	function set_batch_size( $size = 5 ) {
		$this->batch_size = $size;
		add_filter( 'custom_post_type_cleanup_batch_size', array( $this, 'batch_size' ) );
	}

	function remove_filter() {
		$this->batch_size = null;
		remove_filter( 'custom_post_type_cleanup_batch_size', array( $this, 'batch_size' ) );
	}

	function batch_size( $size ) {
		return $this->batch_size;
	}
}
