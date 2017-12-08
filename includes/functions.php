<?php
/**
 * Get post types no longer in use.
 *
 * @since  1.2.0
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
 * @since 1.2.0
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
 * @since 1.2.0
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
 * @since 1.2.0
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

/**
 * Get post types from plugin transient.
 *
 * @since  1.2.0
 * @return array Array with unused post type names or empty array.
 */
function cptc_get_transient_post_types() {
	$post_types = get_transient( 'custom_post_type_cleanup_unused_post_types' );
	if ( ! ( is_array( $post_types ) && ! empty( $post_types ) ) ) {
		return array();
	}

	return $post_types;
}

/**
 * Get time left for the plugin transient.
 *
 * @since  1.2.0
 * @return int Minutes left.
 */
function cptc_get_transient_time() {
	$transient = 'custom_post_type_cleanup_unused_post_types';
	$time      = get_option( "_transient_timeout_{$transient}" );
	return $time ? cptc_get_time_diff_in_minutes( $time ) : 0;
}

/**
 * Returns minutes left from two time stamps.
 *
 * @since  1.2.0
 *
 * @param int $from Unix timestamp from which the difference begins.
 * @param int $to   Unix timestamp to end the time difference. Default becomes time() if not set.
 * @return int   Minutes left.
 */
function cptc_get_time_diff_in_minutes( $from, $to = '' ) {
	if ( empty( $to ) ) {
		$to = time();
	}

	$diff = (int) abs( $to - $from );

	if ( ! $diff ) {
		return 0;
	}

	$mins = round( $diff / MINUTE_IN_SECONDS );

	if ( 1 >= $mins ) {
		$mins = 1;
	}

	return $mins;
}
