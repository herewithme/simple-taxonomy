<?php
class SimpleTaxonomy_Admin_Post {
	/**
	 * Constructor
	 *
	 * @return boolean
	 */
	public function __construct() {
		// Save taxo datas
		add_action( 'save_post', array(__CLASS__, 'saveObjectTaxonomies'), 10, 2 );
		
		// Write post box meta
		add_action( 'add_meta_boxes', array(__CLASS__, 'initObjectTaxonomies'), 10, 2 );
		
		// Add cols in list view
		add_filter( 'manage_posts_columns', array(__CLASS__, 'addColumnTaxonomies'), 10, 2 );
		add_filter( 'manage_pages_columns', array(__CLASS__, 'addColumnTaxonomies'), 10, 2 );
		add_action( 'manage_posts_custom_column', array(__CLASS__,'addCustomColumn'), 10, 2 );
		add_action( 'manage_pages_custom_column', array(__CLASS__,'addCustomColumn'), 10, 2 );
	}
	
	/**
	 * Add column for list content types for each taxonomy
	 *
	 * @param array $posts_columns 
	 * @param string $post_type 
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function addColumnTaxonomies( $posts_columns, $post_type = 'page' ) {
		$taxos = get_object_taxonomies($post_type, 'objects');
		foreach( $taxos as $taxo ) {
			if ( $taxo->public == false || $taxo->show_ui == false || $taxo->_builtin == true )
				continue;
				
			$posts_columns['staxo-'.$taxo->name] = $taxo->labels->name;
		}
		
		return $posts_columns;
	}

	/**
	 * Display tags with link for each taxonomy of content type !
	 *
	 * @param string $column_name 
	 * @param integer $post_id 
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function addCustomColumn( $column_name, $post_id ) {
		global $post;
		
		if( substr($column_name, 0, 6) == 'staxo-' ) {
			$current_taxo = str_replace('staxo-', '', $column_name);
			
			$terms = get_the_terms($post_id, $current_taxo);
			if ( !empty( $terms ) ) {
				$output = array();
				foreach ( $terms as $term ) {
					$output[] = "<a href='edit-tags.php?action=edit&taxonomy=".$current_taxo."&post_type=".$post->post_type."&tag_ID=$term->term_id'> " . esc_html(sanitize_term_field('name', $term->name, $term->term_id, $current_taxo, 'display')) . "</a>";
				}
				echo join( ', ', $output );
			} else {
				//_e('No term.','simple-case');
			}
		}
	}
	
	/**
	 * Save terms when save object.
	 *
	 * @param string $post_ID
	 * @param object $post
	 * @return boolean
	 * @author Amaury Balmer
	 */
	public static function saveObjectTaxonomies( $post_ID = 0, $post = null ) {
		foreach ( get_object_taxonomies($post->post_type) as $tax_name ) {
			// Classic fields
			if( isset($_POST['_taxo_st_'.$tax_name]) && $_POST['_taxo_st_'.$tax_name] == 'true' ) {
				if ( $_POST['sp_tax_input'][$tax_name] === '-' || !isset($_POST['sp_tax_input'][$tax_name]) ) { // Use by HTML Select Admin Taxonomy
					wp_delete_object_term_relationships( $post_ID, array($tax_name) );
				} else {
					// Secure datas
					if ( is_array($_POST['sp_tax_input'][$tax_name]) )
						$_POST['sp_tax_input'][$tax_name] = array_map( 'intval', $_POST['sp_tax_input'][$tax_name] );
					else
						$_POST['sp_tax_input'][$tax_name] = (int) $_POST['sp_tax_input'][$tax_name];
					
					wp_delete_object_term_relationships( $post_ID, array($tax_name) );
					wp_set_object_terms( $post_ID, $_POST['sp_tax_input'][$tax_name], $tax_name, false );
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Add block for each taxonomy in write page for each custom object
	 *
	 * @param string $post_type
	 * @param object $post
	 * @return boolean
	 * @author Amaury Balmer
	 */
	public static function initObjectTaxonomies( $post_type = '', $post = null ) {
		// Prepare admin type for each taxo
		$current_options = get_option( STAXO_OPTION );
		$taxonomies_admin = array();
		foreach( (array) $current_options['taxonomies'] as $taxo ) {
			$taxonomies_admin[$taxo['name']] = $taxo['metabox'];
		}
		
		// All tag-style post taxonomies
		foreach ( get_object_taxonomies($post_type) as $tax_name ) {
			$taxonomy = get_taxonomy($tax_name);
			if ( $taxonomy->show_ui == false )
				continue;
			
			// Display name
			$label = $taxonomy->labels->name;
			
			// Dispatch admin block
			$ad_type = isset($taxonomies_admin[$tax_name]) ? $taxonomies_admin[$tax_name] : 'default';
			
			// Remove default meta boxes
			if ( $ad_type != 'default' ) :
				remove_meta_box( 'tagsdiv-' . $tax_name, $post_type, 'side' );
				remove_meta_box( $tax_name . 'div', $post_type, 'side' );
			endif;
			
			// Display meta box
			switch( $ad_type ) {
				case 'select' : // Custom single selector
					add_meta_box( 'tagsdiv-' . $tax_name, $label, array(__CLASS__, 'post_select_meta_box'), $post_type, 'side', 'default', array( 'taxonomy' => $tax_name ) );
					break;
				
				case 'select-multi' : // Custom multiple selector
					add_meta_box( 'tagsdiv-' . $tax_name, $label, array(__CLASS__, 'post_select_multi_meta_box'), $post_type, 'side', 'default', array( 'taxonomy' => $tax_name ) );
					break;
				
				case 'default' : // Default
				default : // Use the best meta box depending the hierarchy...
					if ( !is_taxonomy_hierarchical($tax_name) )
						add_meta_box('tagsdiv-' . $tax_name, $label, 'post_tags_meta_box', $post_type, 'side', 'core', array( 'taxonomy' => $tax_name ));
					else
						add_meta_box($tax_name . 'div', $label, 'post_categories_meta_box', $post_type, 'side', 'core', array( 'taxonomy' => $tax_name ));
					break;
			}
			
			// Try to free memory !
			unset($label, $taxonomy, $ad_type);
		}
		return false;
	}
	
	/**
	 * Display select form fields.
	 *
	 * @param object $post
	 * @param array $box
	 */
	public static function post_select_meta_box( $post, $box, $multiple = false ) {
		// Use default or custom taxonomy ?
		$defaults = array('taxonomy' => 'post_tag');
		if ( !isset($box['args']) || !is_array($box['args']) )
			$args = array();
		else
			$args = $box['args'];
		extract( wp_parse_args($args, $defaults), EXTR_SKIP );
		
		// Prepare datas
		$tax_name = esc_attr($taxonomy);
		$taxonomy = get_taxonomy($taxonomy);
		
		// User can edit or not ?
		$disabled = !current_user_can($taxonomy->cap->assign_terms) ? 'disabled="disabled"' : '';
		$multiple = ($multiple == true) ? 'class="multiselect" multiple="multiple" style="display:block;height:auto;"' : '';
		
		// Current values
		$current_terms = wp_get_post_terms( $post->ID, $tax_name, 'fields=ids' );
		
		// User can assign terms ?
		if ( !current_user_can($taxonomy->cap->assign_terms) )
			echo '<p><em>'.__('You cannot modify this taxonomy.', 'simple-taxonomy').'</em></p>';
		
		// Display all terms
		$all_terms = get_terms( $tax_name, array('hide_empty' => 0) );
		if ( $all_terms == false || is_wp_error($all_terms) ) {
			echo '<p>'.__('No terms for this taxonomy actually.', 'simple-taxonomy').'</p>';
		} else {
			echo '<p><select '.$disabled.' '.$multiple.' id="sp-tax-input-'.esc_attr($tax_name).'" name="sp_tax_input['.esc_attr($tax_name).'][]" style="width:100%">' . "\n";
				echo '<option '.selected( $current_terms, false, false ).'  value="-">'.__('-- None --', 'simple-taxonomy').'</option>' . "\n";
				foreach( (array) $all_terms as $_term ) {
					echo '<option '.selected( true, in_array( $_term->term_id, (array) $current_terms), false ).'  value="'.intval($_term->term_id).'">'.esc_html($_term->name).'</option>' . "\n";
				}
			echo '</select></p>' . "\n";
		}
		
		echo '<input type="hidden" name="_taxo_st_'.$tax_name.'" value="true" />';
		
		// Display only the link for user can edit terms
		if ( current_user_can($taxonomy->cap->edit_terms) )
			echo '<a class="tagcloud-link" target="_blank" href="'.admin_url( 'edit-tags.php?taxonomy='.$tax_name ).'">'.sprintf( __('+ %s', 'simple-taxonomy'), $taxonomy->labels->add_new_item ).'</a>' . "\n";
		
		return true;
	}
	
	/**
	 * Display select form fields with multiples choices.
	 *
	 * @param object $post
	 * @param array $box
	 */
	public static function post_select_multi_meta_box( $post, $box ) {
		return self::post_select_meta_box( $post, $box, true );
	}
}
?>