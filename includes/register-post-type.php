<?php

add_action( 'init', 'cptc_register_unused_custom_post_types', 999 );

/**
 * Registers unused custom post types.
 *
 * Only registers post types if the unused cpt's transient exists
 * And we're not unregistering unused cpt's in the plugin settings page.
 *
 * @since  1.2.0
 */
function cptc_register_unused_custom_post_types() {

	if ( ! is_admin() || ! current_user_can( 'delete_posts' ) ) {
		return;
	}

	if ( 'unregister' === cptc_get_request() ) {
		// Unregistering cpt's in the plugin settings page.
		return;
	}

	$transient_post_types = cptc_get_transient_post_types();
	if ( ! $transient_post_types ) {
		return;
	}

	foreach ( $transient_post_types as $post_type ) {
		if ( post_type_exists( $post_type ) ) {
			continue;
		}

		// Register the non-existent post type.
		register_post_type(
			$post_type,
			array(
				'public'             => true,
				'label'              => $post_type,
				'hierarchical'       => true,
				'publicly_queryable' => false,
				'show_in_rest'       => false,
				'show_in_admin_bar'  => false,
			)
		);
	}
}

add_action( 'admin_notices', 'cptc_add_admin_notice_for_unused_post_types' );

/**
 * Adds plugin notice to registered unused post type admin screens
 *
 * @since 1.2.0
 */
function cptc_add_admin_notice_for_unused_post_types() {
	$screen = get_current_screen();

	$post_type   = $screen->post_type ? $screen->post_type : '';
	$base        = ( 'edit' === $screen->base );
	$parent_base = ( 'edit' === $screen->parent_base );
	$post        = ( 'post' === $screen->base ) && $parent_base;

	if ( ! ( $post_type && ( $base || $post ) ) ) {
		return;
	}

	$transient_post_types = cptc_get_transient_post_types();
	if ( ! $transient_post_types ) {
		return;
	}

	if ( ! in_array( $post_type, $transient_post_types ) ) {
		return;
	}

	$time = cptc_get_transient_time();
	if ( ! $time ) {
		return;
	}

	$href       = admin_url( 'admin.php?page=custom-post-type-cleanup.php#unregister' );
	$link       = '<a href="%1$s">%2$s</a>';
	$plugin     = sprintf( $link, $href,  __( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ) );
	$unregister = sprintf( $link, $href, __( 'stop registering it now', 'custom-post-type-cleanup' ) );
	$since      = '';
	$msg        = sprintf(
		/* translators: %s: link to plugin */
		__( 'This custom post type is registered for a limited time by the %s plugin.', 'custom-post-type-cleanup' ),
		$plugin
	);

	if ( 1 === (int) $time ) {
		$since = sprintf(
			/* translators: %d: 1 minute left */
			__( '%d minute to go before this post type is no longer registered.', 'custom-post-type-cleanup' ), $time
		);
	} elseif ( $time > 1 ) {
		$since = sprintf(
			/* translators: %d: more than one minute left */
			__( '%d minutes to go before this post type is no longer registered.', 'custom-post-type-cleanup' ),
			$time
		);
	}

	$msg = $since ? $msg . '<br/><br/>' . $since . ' (' . $unregister . ')' : $msg;

	echo  '<div class="notice" style="margin-top:1.5em;"><p>' . $msg . '</p></div>';
}
