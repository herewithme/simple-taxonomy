<?php
/*
Plugin Name: Simple Taxonomy
Version: 3.4.1
Plugin URI: https://github.com/herewithme/simple-taxonomy
Description: WordPress 3.1 and up allow for reasonably simple custom taxonomy, this plugin makes it even simpler, removing the need for you to write <em>any</em> code.
Author: Amaury Balmer
Author URI: http://www.beapi.fr

----

Copyright 2010-2012 Amaury Balmer (amaury@beapi.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

---

Todo :
	Core :
		Make a class for CPT object (add, delete, update, etc)
	Admin
	Extras
	Client
*/

// Folder name
define ( 'STAXO_VERSION', '3.4.1' );
define ( 'STAXO_OPTION',  'simple-taxonomy' );
define ( 'STAXO_FOLDER',  'simple-taxonomy' );

define ( 'STAXO_URL', plugins_url('', __FILE__) );
define ( 'STAXO_DIR', dirname(__FILE__) );

// Library
require( STAXO_DIR . '/inc/functions.inc.php' );
// require( STAXO_DIR . '/inc/functions.tpl.php' );

// Call client classes
// require( STAXO_DIR . '/inc/class.base.php' );
require( STAXO_DIR . '/inc/class.client.php' );
require( STAXO_DIR . '/inc/class.widget.php' );

if ( is_admin() ) { // Call admin classes
	require( STAXO_DIR . '/inc/class.admin.php' );
	require( STAXO_DIR . '/inc/class.admin.conversion.php' );
	require( STAXO_DIR . '/inc/class.admin.import.php' );
	require( STAXO_DIR . '/inc/class.admin.post.php' );
}

// Activate/Desactive Simple Taxonomy
// register_activation_hook  ( __FILE__, array('SimpleTaxonomy_Base', 'activate') );
// register_deactivation_hook( __FILE__, array('SimpleTaxonomy_Base', 'deactivate') );

add_action( 'plugins_loaded', 'init_simple_taxonomy' );
function init_simple_taxonomy() {
	global $simple_taxonomy;
	
	// Load translations
	load_plugin_textdomain ( 'simple-taxonomy', false, basename(rtrim(dirname(__FILE__), '/')) . '/languages' );
	
	// Client
	$simple_taxonomy['client-base']  = new SimpleTaxonomy_Client();
	
	// Admin
	if ( is_admin() ) {
		// Class admin
		$simple_taxonomy['admin-base'] 		 = new SimpleTaxonomy_Admin();
		$simple_taxonomy['admin-post'] 		 = new SimpleTaxonomy_Admin_Post();
		$simple_taxonomy['admin-conversion'] = new SimpleTaxonomy_Admin_Conversion();
		$simple_taxonomy['admin-import'] 	 = new SimpleTaxonomy_Admin_Import();
	}
	
	// Widget
	add_action( 'widgets_init', create_function('', 'return register_widget("SimpleTaxonomy_Widget");') );
}
?>