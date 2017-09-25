<?php

/**
 * Get post types no longer in use.
 *
 * @since  1.1.0
 * @return array Array with unused post type names.
 */
function cptc_get_unused_post_types() {
	$unused_cpts   = array();
	$post_types    = array_keys( get_post_types() );
	$db_post_types = cptc_db_post_types();

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
function cptc_db_post_types() {
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
function cptc_get_posts_count( $post_type ) {
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
function cptc_get_post_ids( $post_type, $limit = 100 ) {
	global $wpdb;

	if ( ! absint( $limit ) ) {
		return array();
	}

	$query = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type IN (%s) LIMIT %d";

	return $wpdb->get_col( $wpdb->prepare( $query, $post_type, absint( $limit ) ) );
}
