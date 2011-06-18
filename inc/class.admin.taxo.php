<?php
class SimpleTaxonomy_Admin_Taxo {
	/**
	 * Constructor
	 */
	function SimpleTaxonomy_Admin_Taxo() {
		// Fix parent menu for tags taxonomy
		add_action( 'admin_xml_ns', array(&$this, 'fixTopMenu') );
		
		// Taxonomies menu
		add_action( '_admin_menu', array(&$this, 'addTaxonomiesMenu') );
	}
	
	/**
	 * Allow to hightlight the "pages" top menu for page taxonomy
	 *
	 **/
	function fixTopMenu() {
		global $parent_file, $submenu_file, $taxonomy;
		
		if ( strpos( $submenu_file, 'edit-tags.php' ) !== false ) {
			
			$_tax = get_taxonomy( $taxonomy );
			if ( count($_tax->object_type) == 1 && in_array('page', (array) $_tax->object_type) ) {
				$parent_file = 'edit.php?post_type=page';
			}
			unset($_tax);
		
		}
	}
	
	/**
	 * Add some taxonomies menu for page custom type. For post and others object, WordPress add automatically the menu !
	 *
	 **/
	function addTaxonomiesMenu() {
		global $submenu, $wp_taxonomies;
		
		// Get current options
		$current_options = get_option( STAXO_OPTION );
		
		$i = 25;
		foreach ( $wp_taxonomies as $taxonomy ) {
			if ( !in_array( 'page', (array) $taxonomy->object_type) )
				continue;
			
			// Custom taxonomies, otherwise skip it !
			if ( !isset($current_options['taxonomies'][$taxonomy->name]) )
				continue;
			
			if ( count($taxonomy->object_type) == 1 ) { // Pages block
				$submenu['edit.php?post_type=page'][$i] = array( esc_attr($taxonomy->label), 'manage_categories', 'edit-tags.php?taxonomy=' . $taxonomy->name );
			}
			++$i;
		}
	}
}
?>