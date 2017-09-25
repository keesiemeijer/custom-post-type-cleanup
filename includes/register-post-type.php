<?php

add_action( 'init', 'cptc_register_on_edit_php' );
function cptc_register_on_edit_php() {
	if ( ! is_admin() ) {
		return;
	}

	if ( isset( $_REQUEST['cptc-unused-post-types'] ) ) {
		$action = $_REQUEST['cptc-unused-post-types'];
		if ( 'unregister' === $action ) {
			return;
		}
	}

	$registerd_post_types = get_transient( 'custom_post_type_cleanup_unused_post_types' );

	if ( ! ( $registerd_post_types && is_array( $registerd_post_types ) ) ) {
		return;
	}

	foreach ( $registerd_post_types as $post_type ) {
		// code...
		// Register the non-existent post type.
		register_post_type(
			$post_type,
			array(
				'public' => true,
				'publicly_queryable' => false,
				'label' => $post_type,
				'hierarchical' => true, // Children are re-assigned.
			)
		);
	}
}
