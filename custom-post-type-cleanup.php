<?php
/*
Plugin Name: Custom Post Type Cleanup
Version: 1.3.0
Plugin URI:
Description: Detect and delete posts from custom post types that are no longer in use.
Author: keesiemeijer
Author URI:
License: GPL v2+
Text Domain: custom-post-type-cleanup
Domain Path: /languages

Custom Post Type Cleanup
Copyright 2017  Kees Meijer  (email : keesie.meijer@gmail.com)

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

if ( is_admin() ) {
	load_plugin_textdomain( 'custom-post-type-cleanup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-post-type-cleanup.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/register-post-type.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/request.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

	$cpt_cleanup = new CPTC_Post_Type_Cleanup();
	$cpt_cleanup->init();

}
