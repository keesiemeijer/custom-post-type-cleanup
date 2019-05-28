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
	 * Initialize plugin
	 *
	 * @since 1.0.0
	 */
	public function init() {
		if ( ! is_admin() ) {
			return;
		}

		// Add the admin page for this plugin.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Adds a settings page for this plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu() {
		if ( ! current_user_can( 'delete_posts' ) ) {
			$msg = __( "You don't have sufficient permissions to access this page", 'custom-post-type-cleanup' );
			wp_die( $msg );
		}

		$page_hook = add_management_page(
			__( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ),
			__( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ),
			'manage_options',
			'custom-post-type-cleanup.php',
			array( $this, 'admin_page' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'register_post_type' ) );
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

		$request        = '';
		$post_type      = '';
		$notice         = '';
		$options        = '';
		$plugin_url     = admin_url( 'plugins.php#custom-post-type-cleanup' );
		$admin_url      = admin_url( 'admin.php?page=custom-post-type-cleanup.php' );
		$transient      = 'custom_post_type_cleanup_unused_post_types';
		$total          = 0;
		$transient_time = 10;
		$batch_size     = 100;

		$plugin   = _x(
			'Custom Post Type Cleanup',
			'text of link to plugin',
			'custom-post-type-cleanup'
		);

		$documentation = _x(
			'learn more about unused custom post types',
			'text of link to plugin documentation',
			'custom-post-type-cleanup'
		);

		$plugin_link = '<a href="https://wordpress.org/plugins/custom-post-type-cleanup">' . $plugin . '</a>';
		$doc_link    = '<a href="https://wordpress.org/plugins/custom-post-type-cleanup">' . $documentation . '</a>';

		$request = cptc_get_request( 'check_referer' );

		if ( 'delete' === $request ) {
			$post_type  = cptc_get_requested_post_type( 'check_referer' );
			$batch_size = cptc_get_batch_size( $post_type, 'check_referer' );

			$notice = $this->delete_posts( $post_type, $batch_size );
		} elseif ( 'register' === $request ) {
			if ( ! empty( $this->unused_cpts ) ) {
				set_transient( $transient, $this->unused_cpts, 60 * $transient_time );

				/* translators: %d: time in minutes left */
				$msg    = sprintf( __( 'Registered all unused custom post types for the next %s minutes', 'custom-post-type-cleanup' ), $transient_time );
				$reload = "<a href='{$admin_url}'>" . __( 'Reload this page', 'custom-post-type-cleanup' ) . '</a>';

				/* translators: %s: link to reload the page */
				$msg    .= '<br/>' . sprintf( __( '(%s to see them in the admin menu)', 'custom-post-type-cleanup' ), $reload );
				$notice .= '<div class="updated"><p>' . $msg . '</p></div>';
				$this->unused_cpts = array();
			}
		} elseif ( 'unregister' === $request ) {
			$transient_post_types = cptc_get_transient_post_types();
			if ( $transient_post_types ) {
				$msg = __( 'Stopped registering unused custom post types', 'custom-post-type-cleanup' );
				$notice = '<div class="updated"><p>' . $msg . '</p></div>';
			}

			delete_transient( $transient );
		}

		if ( ! empty( $this->unused_cpts ) ) {
			foreach ( $this->unused_cpts as $unused_type ) {
				$selected = ( $unused_type === $post_type ) ? " selected='selected'" : '';
				$value = esc_attr( $unused_type );
				$count = cptc_get_posts_count( $unused_type );
				$total += $count;
				$count = $count ? ' (' . $count . ')' : '';
				$options .= "<option value='{$value}'{$selected}>{$value}{$count}</option>";
			}
			$type_count = count( $this->unused_cpts );
			$type_str   = _n( 'custom post type', 'custom post types', $type_count );

			require plugin_dir_path( __FILE__ ) . 'templates/admin-form.php';
		} else {
			$transient_post_types = cptc_get_transient_post_types();

			if ( $transient_post_types ) {
				$minutes_left = cptc_get_transient_time();
				$type_count   = count( $transient_post_types );
				$type_str     = _n( 'custom post type', 'custom post types', $type_count );

				require plugin_dir_path( __FILE__ ) . 'templates/admin-registered-post-types.php';
			} else {
				require plugin_dir_path( __FILE__ ) . 'templates/admin-no-posts.php';
			}
		}
	}

	/**
	 * Re-registers an unused post type if needed.
	 *
	 * @since  1.1.0
	 */
	public function register_post_type() {
		// Unregistered (unused) post types.
		$this->unused_cpts = cptc_get_unused_post_types();

		if ( 'unregister' === cptc_get_request( 'check_referer' ) ) {
			return;
		}

		// The post type from a $_POST request (to delete posts from).
		$post_type = cptc_get_requested_post_type( 'check_referer' );

		if ( ! empty( $this->unused_cpts ) && ! empty( $post_type ) ) {
			// Register the non-existent post type.
			register_post_type(
				$post_type,
				array(
					'public' => false,
					'hierarchical' => true, // Children are re-assigned.
				)
			);
		}
	}

	/**
	 * Delete posts from the database.
	 *
	 * @since 1.0.0
	 * @param string $post_type Post type to delete posts from.
	 * @param int    $limit     Number of posts to delete. Default 100;
	 * @return string Admin notices.
	 */
	private function delete_posts( $post_type, $limit = 100 ) {
		global $wpdb;

		$msg       = '';

		if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
			$msg = __( 'Error: invalid post type', 'custom-post-type-cleanup' );
			return '<div class="error"><p>' . $msg . '</p></div>';
		}

		// Get post ids for this post type in batches.
		$db_post_ids = cptc_get_post_ids( $post_type, $limit );

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
		$count = absint( cptc_get_posts_count( $post_type ) );

		if ( $count ) {

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
}
