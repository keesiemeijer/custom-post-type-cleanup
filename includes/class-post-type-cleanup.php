<?php

class CPTC_Post_Type_Cleanup {

	/**
	 * Unused post types.
	 *
	 * @since  1.0.0
	 * @var array
	 */
	private $unused_cpts;

	/**
	 * Batch size.
	 *
	 * @since  1.1.0
	 * @var int
	 */
	private $batch_size = 100;

	/**
	 * Initialize plugin
	 *
	 * @since 1.0.0
	 */
	public function init() {
		if ( ! is_admin() ) {
			return;
		}

		// Hook in *very* late to catch all registered custom post types.
		add_action( 'init', array( $this, 'register_post_type' ), 9999999 );

		// Add the admin page for this plugin.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Re-registers an unused post type if needed.
	 *
	 * @since  1.1.0
	 */
	public function register_post_type() {

		// Unregistered (unused) post types.
		$this->unused_cpts = $this->get_unused_post_types();

		// The post type from a $_POST request (to delete posts from).
		$post_type = $this->get_requested_post_type();

		/**
		 * Filter the batch size.
		 *
		 * @param int $batch_size Batch size. Default 100.
		 */
		$batch_size = apply_filters( 'custom_post_type_cleanup_batch_size', $this->batch_size, $post_type );
		$this->batch_size = absint( $batch_size );

		if ( ! empty( $this->unused_cpts ) && ! empty( $post_type ) ) {
			// Register the non-existent post type.
			register_post_type( $post_type,
				array(
					'public' => false,
					'hierarchical' => true, // Children are re-assigned.
				)
			);
		}
	}

	/**
	 * Adds a settings page for this plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu() {
		$page_hook = add_management_page(
			__( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ),
			__( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ),
			'manage_options',
			'custom-post-type-cleanup.php',
			array( $this, 'admin_page' )
		);

		add_action( 'admin_print_scripts-' . $page_hook, array( $this, 'enqueue_script' ) );
	}

	/**
	 * Enqueue Javascript for confirm dialog to delete posts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_script() {
		$plugin_url = plugins_url( 'js/custom-post-type-cleanup.js', __FILE__ );
		wp_register_script( 'custom-post-type-cleanup', $plugin_url, array( 'jquery' ), false, true );
		wp_enqueue_script( 'custom-post-type-cleanup' );

		/* translators: %s: post type name */
		$confirm = __( 'You are about to permanently delete posts from the post_type: %s.', 'custom-post-type-cleanup' );
		$confirm .= "\n  " . __( "'Cancel' to stop, 'OK' to delete.", 'custom-post-type-cleanup' );

		$js_vars = array(
			'confirm'        => $confirm,
			'remove_storage' => (bool) ! ( 'POST' === $_SERVER['REQUEST_METHOD'] ),
		);

		wp_localize_script( 'custom-post-type-cleanup', 'cptc_plugin', $js_vars );
	}

	/**
	 * Displays the admin settings page for this plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_page() {
		$post_type     = '';
		$notice        = '';
		$options       = '';
		$plugin_url    = admin_url( 'plugins.php#custom-post-type-cleanup' );
		$total         = 0;

		$plugin_text   = _x(
			'Custom Post Type Cleanup',
			'Text of link to plugin',
			'custom-post-type-cleanup'
		);

		$plugin_link = '<a href="https://github.com/keesiemeijer/custom-post-type-cleanup">' . $plugin_text . '</a>';

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'custom_post_type_cleanup_nonce', 'security' );

			$post_type = $this->get_requested_post_type();
			$notice    = $this->delete_posts( stripslashes_deep( $_POST ) );
		}

		$type_count = count( $this->unused_cpts );
		$type_str   = _n( 'custom post type', 'custom post types', $type_count );

		if ( ! empty( $this->unused_cpts ) ) {
			foreach ( $this->unused_cpts as $unused_type ) {
				$selected = ( $unused_type === $post_type ) ? " selected='selected'" : '';
				$value = esc_attr( $unused_type );
				$count = $this->get_posts_count( $unused_type );
				$total += $count;
				$count = $count ? ' (' . $count . ')' :  '';
				$options .= "<option value='{$value}'{$selected}>{$value}{$count}</option>";
			}

			require plugin_dir_path( __FILE__ ) . 'templates/admin-form.php';
		} else {
			require plugin_dir_path( __FILE__ ) . 'templates/admin-no-posts.php';
		}
	}

	/**
	 * Returns the post type from a $_POST request.
	 *
	 * @since 1.0.0
	 * @return string Post type to delete posts from or empty string.
	 */
	private function get_requested_post_type() {

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return '';
		}

		$request = stripslashes_deep( $_POST );

		// Check it's this plugin's settings form that was submitted.
		if ( ! isset( $request['custom_post_type_cleanup'] ) ) {
			return '';
		}

		return isset( $request['cptc_post_type'] ) ? $request['cptc_post_type'] : '';
	}

	/**
	 * Delete posts from the database.
	 *
	 * @since 1.0.0
	 * @param unknown $args array $_POST request with post type to delete posts from.
	 * @return string Admin notices.
	 */
	private function delete_posts( $args ) {
		global $wpdb;

		$msg       = '';
		$post_type = isset( $args['cptc_post_type'] ) ? $args['cptc_post_type'] : '';

		if ( empty( $post_type ) ) {
			$msg = __( 'Error: invalid post type', 'custom-post-type-cleanup' );
			return '<div class="error"><p>' . $msg . '</p></div>';
		}

		// Get post ids for this post type in batches.
		$db_post_ids = $this->get_post_ids( $post_type, $this->batch_size );

		if ( empty( $db_post_ids ) ) {
			/* translators: %s: post type name */
			$no_posts_msg = __( 'Notice: No posts found for the post type: %s', 'custom-post-type-cleanup' );
			$msg = sprintf( $no_posts_msg , $post_type );
			return '<div class="notice"><p>' . $msg . '</p></div>';
		}

		$deleted = 0;
		foreach ( $db_post_ids as $post_id ) {
			$del = wp_delete_post( $post_id );
			if ( false !== $del ) {
				++$deleted;
			}
		}

		if ( $deleted ) {
			/* translators: 1: deleted posts count, 2: post type name  */
			$updated = _n(
				'Deleted %1$d post from the post type: %2$s',
				'Deleted %1$d posts from the post type: %2$s',
				$deleted,
				'custom-post-type-cleanup'
			);

			$updated = sprintf( $updated, $deleted, $post_type );
			$msg = '<div class="updated"><p>' . $updated . '</p></div>';
		}

		// Check if there more posts from this post type to delete.
		$db_post_ids = $this->get_post_ids( $post_type );

		if ( ! empty( $db_post_ids ) ) {
			$count = count( $db_post_ids );

			/* translators: 1: posts count, 2: post type name  */
			$notice = _n(
				'Still %1$d post left in the database from the post type: %2$s',
				'Still %1$d posts left in the database from the post type: %2$s',
				$count,
				'custom-post-type-cleanup'
			);

			$notice = sprintf( $notice , $count, $post_type );

			$msg .= '<div class="notice"><p>' . $notice . '</p></div>';
		} else {
			/* No more posts from this post type left in the database. */

			$key = array_search( $post_type, $this->unused_cpts );
			if ( false !== $key ) {
				unset( $this->unused_cpts[ $key ] );

				/* translators: %s: post type name */
				$notice = __( 'No more posts left in the database from the post type: %s', 'custom-post-type-cleanup' );
				$notice = sprintf( $notice, $post_type );
				$msg .= '<div class="notice"><p>' . $notice . '</p></div>';
			}
		}

		return $msg;
	}

	/**
	 * Get post types no longer in use.
	 *
	 * @return array Array with unused post type names.
	 */
	public function get_unused_post_types() {
		$unused_cpts   = array();
		$post_types    = array_keys( get_post_types() );
		$db_post_types = $this->db_post_types();

		if ( ! empty( $db_post_types ) ) {
			$unused_cpts = array_diff( $db_post_types, $post_types );
		}
		return $unused_cpts;
	}

	/**
	 * Returns post types in the database.
	 *
	 * @since 1.0.0
	 * @return array Array with post types in the database.
	 */
	public function db_post_types() {
		global $wpdb;
		$query = "SELECT DISTINCT post_type FROM $wpdb->posts";
		return $wpdb->get_col( $query );
	}

	/**
	 * Returns post type posts count for a post type.
	 * Todo: check if wp_count_posts can be used for this.
	 *
	 * @since 1.0.0
	 * @param string $post_type Post type.
	 * @return integer Post count for a post type.
	 */
	public function get_posts_count( $post_type ) {
		global $wpdb;
		$query = "SELECT COUNT(p.ID) FROM $wpdb->posts AS p WHERE p.post_type = %s";
		return $wpdb->get_var( $wpdb->prepare( $query, $post_type ) );
	}

	/**
	 * Returns post ids from a post type.
	 *
	 * @since 1.0.0
	 * @param string  $post_type Post type.
	 * @param integer $limit     Limit how many ids are returned. Default 100.
	 * @return array Array with post ids.
	 */
	public function get_post_ids( $post_type, $limit = 100 ) {
		global $wpdb;

		if ( ! absint( $limit ) ) {
			return array();
		}

		$query = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type IN (%s) LIMIT %d";

		return $wpdb->get_col( $wpdb->prepare( $query, $post_type, absint( $limit ) ) );
	}
}
