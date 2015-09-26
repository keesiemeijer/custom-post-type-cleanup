<?php
/*
Plugin Name: Custom Post Type Cleanup
Version: 1.0
Plugin URI:
Description: Detect and delete posts from custom post types that are no longer in use.
Author: keesiemijer
Author URI:
License: GPL v2+
Text Domain: custom-post-type-cleanup
Domain Path: /languages

Custom Post Type Cleanup
Copyright 2015  Kees Meijer  (email : keesie.meijer@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version. You may NOT assume that you can use any other version of the GPL.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Custom_Post_Type_Cleanup {

	private $post_types;
	private $db_post_types;
	private $unused_cpts;

	public function __construct() {

		load_plugin_textdomain( 'custom-post-type-cleanup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Hook in *very* late to catch all registered custom post types. ¯\_(ツ)_/¯
		add_action( 'init', array( $this, 'init' ), 9999999 );
	}


	/**
	 * Initialize plugin
	 *
	 * @since 1.0
	 * @return void
	 */
	public function init() {

		// Registered post types in global $wp_post_types variable.
		$this->post_types = array_keys( get_post_types() );

		// Post types found in the database.
		$this->db_post_types = $this->db_post_types();

		// Unregistered (unused) post types.
		$this->unused_cpts = array();

		// Post type from $_POST request (to delete posts from).
		$post_type = $this->get_requested_post_type();

		if ( !empty( $this->db_post_types ) ) {
			$this->unused_cpts = array_diff( $this->db_post_types, $this->post_types );
		}

		if ( !empty( $this->unused_cpts ) && !empty( $post_type ) ) {

			// Register the non-existent post type.
			// This way we can use wp_delete_post() to delete the posts.

			// Set hierachical to false as there is no need to re-assign child posts.
			register_post_type( $post_type,
				array(
					'public' => false,
					'hierarchical' => false,
				)
			);
		}

		// Add the admin page for this plugin.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}


	/**
	 * Adds a settings page for this plugin.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function admin_menu() {
		$page_hook = add_management_page(
			__( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ),
			__( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ),
			'manage_options',
			'custom-post-type-cleanup.php',
			array( $this, 'admin_page' ) );

		add_action( 'admin_print_scripts-' . $page_hook, array( $this, 'enqueue_script' ) );
	}


	/**
	 * Enqueue Javascript for confirm dialog to delete posts.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_script() {

		wp_register_script( 'custom-post-type-cleanup', plugins_url( '/custom-post-type-cleanup.js', __FILE__ ), array( 'jquery' ), false, true );
		wp_enqueue_script( 'custom-post-type-cleanup' );


		$js_vars = array(
			/* translators: %s: post type name */
			'confirm' => __( 'You are about to permanently delete posts from the post_type: %s.', 'custom-post-type-cleanup' ),
			'remove_storage' => (bool)  !( 'POST' === $_SERVER['REQUEST_METHOD'] ),
		);

		$js_vars['confirm'] .= "\n  " . __( "'Cancel' to stop, 'OK' to delete.", 'custom-post-type-cleanup' );

		wp_localize_script( 'custom-post-type-cleanup', 'cptc_plugin', $js_vars );
	}


	/**
	 * Returns post types in the database.
	 *
	 * @since 1.0
	 * @return array Array with post types in the database.
	 */
	private function db_post_types() {
		global $wpdb;
		$query = "SELECT DISTINCT post_type FROM $wpdb->posts";
		return $wpdb->get_col( $query );
	}

	private function get_count($post_type){
		global $wpdb;
		$query = "SELECT COUNT(p.ID) FROM $wpdb->posts AS p WHERE p.post_type = %s";
		return $wpdb->get_var( $wpdb->prepare($query, $post_type) );
	}


	/**
	 * Returns the post type from a $_POST request.
	 *
	 * @since 1.0
	 * @return string Post type to delete posts from or empty string.
	 */
	private function get_requested_post_type() {

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return '';
		}

		$request = stripslashes_deep( $_POST );

		// Check it's this plugin's settings form that was submitted
		if ( !isset( $request['custom_post_type_cleanup'] ) ) {
			return '';
		}

		return isset( $request['cptc_post_type'] ) ? $request['cptc_post_type'] : '';
	}


	/**
	 * Displays the admin settings page for this plugin.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function admin_page() {

		$header        = __( 'Delete posts from custom post types that are currently not registered (no longer in use).', 'custom-post-type-cleanup' );
		$post_type     = '';
		$delete_notice = '';

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'custom_post_type_cleanup_nonce' );

			$post_type     = $this->get_requested_post_type();
			$delete_notice = $this->delete_posts( stripslashes_deep( $_POST ) );
		}

		// Start admin page output
		echo '<div class="wrap rpbt_cache">';
		echo '<h1>' . __( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ) . '</h1>';

		echo $delete_notice;

		if ( !empty( $this->unused_cpts ) ) {
			// Unused post type posts found.

			$unregistered = _n( 'Posts from unused post type detected!', 'Posts from unused post types detected!', count( $this->unused_cpts ), 'custom-post-type-cleanup' );

			echo '<h3 style="color:Chocolate;">' . $unregistered . '</h3>';
			echo '<p>' . $header . '<br/>' . __( 'Posts are deleted in batches of 100 posts.', 'custom-post-type-cleanup' ) . '</p>';
			echo '<p>' . __( "It's recommended you <strong style='color:red;'>make a database backup</strong> before proceeding.", 'custom-post-type-cleanup' ) . '</p>';

			echo '<form method="post" action="">';
			wp_nonce_field( 'custom_post_type_cleanup_nonce' );

			echo "<table class='form-table'>";
			$label = '<label for="cptc_post_type">' . __( 'Post type', 'custom-post-type-cleanup' ) . '</label>';
			echo "<tr><th scope='row'>{$label}</th>";
			echo '<td><select id="cptc_post_type" name="cptc_post_type">';

			foreach ( $this->unused_cpts as $unused_type ) {
				$selected = ( $unused_type === $post_type ) ? " selected='selected'" : '';
				$value = esc_attr( $unused_type );
				$count = $this->get_count( $unused_type );
				$count = $count ? ' (' . $count . ')' :  '';
				echo "<option value='{$value}'{$selected}>{$value}{$count}</option>";
			}

			echo '</select>';
			echo '<p class="description">' . __( 'The post type you want to delete posts from.', 'custom-post-type-cleanup' ) . '</p>';
			echo '</td></tr></table>';
			submit_button( __( 'Delete Posts!', 'custom-post-type-cleanup' ), 'primary', 'custom_post_type_cleanup' );
			echo '</form>';

		} else {
			// No unused post type posts found.

			if ( empty( $delete_notice ) ) {
				echo '<p>' . $header . '</p>';
			}

			$plugin_url    = admin_url( 'plugins.php#custom-post-type-cleanup' );
			$type_notice    = __( 'No unused custom post types found!', 'custom-post-type-cleanup' );
			$type_notice    = $type_notice . ' ' . "<a href='{$plugin_url}'>" . __( 'De-activate this plugin', 'custom-post-type-cleanup' ) . '</a>';
			$type_notice    = '<div class="updated"><p>' . $type_notice . '</p></div>';

			echo $type_notice;
		}

		echo '</div>';
	}


	/**
	 * Delete posts from the database.
	 *
	 * @since 1.0
	 * @param $args array $_POST request with post type to delete posts from.
	 * @return string Admin notices.
	 */
	private function delete_posts( $args ) {
		global $wpdb;

		$msg = '';
		$post_type = isset( $args['cptc_post_type'] ) ? $args['cptc_post_type'] : '';

		if ( empty( $post_type ) ) {
			$msg .= '<div class="error"><p>' . __( 'Error: invalid post type', 'custom-post-type-cleanup' ) . '</p></div>';
			return $msg;
		}

		$query = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type IN (%s)";
		$db_post_types = $wpdb->get_col( $wpdb->prepare( $query . " LIMIT 100", $post_type ) );

		if ( empty( $db_post_types ) ) {
			/* translators: %s: post type name */
			$msg .= '<div class="notice"><p>' . sprintf( __( 'Notice: No posts found for the post type: %s', 'custom-post-type-cleanup' ), $post_type ) . '</p></div>';
			return $msg;
		}

		$deleted = 0;
		foreach ( $db_post_types as $post ) {
			$del = wp_delete_post( $post );
			if ( false !== $del ) {
				++$deleted;
			}
		}

		if ( $deleted ) {
			/* translators: 1: deleted posts count, 2: post type name  */
			$msg .= '<div class="updated"><p>' . sprintf( __( 'Deleted %1$d posts from the post type: %2$s ', 'custom-post-type-cleanup' ), $deleted, $post_type ) . '</p></div>';
		}

		// Check if there more posts from this post type to delete.
		$db_post_types = $wpdb->get_col( $wpdb->prepare( $query, $post_type ) );

		if ( !empty( $db_post_types ) ) {
			/* translators: 1: posts count, 2: post type name  */
			$msg .= '<div class="notice"><p>' . sprintf( __( 'Still %1$d posts left in the database from the post type: %2$s ', 'custom-post-type-cleanup' ), count( $db_post_types ), $post_type ) . '</p></div>';
		} else {
			// No more posts from this post type in the database.

			if ( ( $key = array_search( $post_type, $this->unused_cpts ) ) !== false ) {
				unset( $this->unused_cpts[ $key ] );

				/* translators: %s: post type name */
				$msg .= '<div class="updated"><p>' . sprintf( __( 'No more posts left in the database from the post type: %s ', 'custom-post-type-cleanup' ), $post_type ) . '</p></div>';
			}
		}

		return $msg;
	}
}

$custom_post_type_cleanup = new Custom_Post_Type_Cleanup();