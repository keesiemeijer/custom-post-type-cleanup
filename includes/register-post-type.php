<?php

add_action( 'init', 'cptc_register_unused_custom_post_types' );

/**
 * Registers unused custom post types.
 *
 * Only registers post types if the transient
 * custom_post_type_cleanup_unused_post_types exists.
 *
 * @since  1.1.1
 */
function cptc_register_unused_custom_post_types() {

	if ( ! is_admin() || ! current_user_can( 'delete_posts' ) ) {
		return;
	}

	if ( isset( $_REQUEST['cptc-unused-post-types'] ) ) {
		$action = $_REQUEST['cptc-unused-post-types'];
		if ( 'unregister' === $action ) {
			return;
		}
	}

	$registerd_post_types = get_transient( 'custom_post_type_cleanup_unused_post_types' );
	if ( ! ( is_array( $registerd_post_types ) && ! empty( $registerd_post_types ) ) ) {
		return;
	}

	foreach ( $registerd_post_types as $post_type ) {
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
 * @since 1.1.1
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

	$transient            = 'custom_post_type_cleanup_unused_post_types';
	$registerd_post_types = get_transient( $transient );
	if ( ! ( is_array( $registerd_post_types ) && ! empty( $registerd_post_types ) ) ) {
		return;
	}

	if ( ! in_array( $post_type, $registerd_post_types ) ) {
		return;
	}

	$time = get_option( "_transient_timeout_{$transient}" );

	if ( ! $time ) {
		return;
	}

	$href   = admin_url( 'admin.php?page=custom-post-type-cleanup.php' );
	$link   = '<a href="%1$s">%2$s</a>';
	$plugin = sprintf( $link, $href,  __( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ) );

	$nonce  = '&registernonce=' . wp_create_nonce( 'cptc_register_post_type' );
	$href   .= '&cptc-unused-post-types=unregister' . $nonce;
	$unregister = sprintf( $link, $href, __( 'stop registering now', 'custom-post-type-cleanup' ) );

	$since = '';
	$mins  = cptc_get_time_diff_in_minutes( $time );
	$msg   = sprintf(
		/* translators: %s: link to plugin */
			__( 'This custom post type is registered for a limited time by the %s plugin.', 'custom-post-type-cleanup' ),
		$plugin
	);

	if ( 1 === (int) $mins ) {
		$since = sprintf(
			/* translators: %d: total of minutes left */
			__( '%d minute to go before this post type is no longer registered.', 'custom-post-type-cleanup' ), $mins
		);
	} elseif ( $mins > 1 ) {
		$since = sprintf(
			/* translators: %d: total of minutes left */
			__( '%d minutes to go before this post type is no longer registered.', 'custom-post-type-cleanup' ),
			$mins
		);
	}

	$msg = $since ? $msg . '<br/><br/>' . $since . ' (' . $unregister . ')' : $msg;

	echo  '<div class="notice" style="margin-top:1.5em;"><p>' . $msg . '</p></div>';
}
