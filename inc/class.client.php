<?php
/**
 * Simple Taxonomy Client class
 *
 * @package Simple Taxonomy
 * @author Amaury Balmer
 */
class SimpleTaxonomy_Client {
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public function __construct() {
		add_action( 'init', array(__CLASS__, 'init'), 1 );
		
		add_filter( 'the_excerpt', array(__CLASS__, 'the_excerpt'), 10, 1 );
		add_filter( 'the_content', array(__CLASS__, 'the_content'), 10, 1 );
		
		add_action( 'template_redirect', array(__CLASS__, 'template_redirect') );
		add_filter( 'wp_title', array(__CLASS__, 'wp_title'), 10, 2 );
	}
	
	/**
	 * Register all custom taxonomies to WordPress process
	 *
	 * @return boolean
	 * @author Amaury Balmer
	 */
	public static function init() {
		$options = get_option( STAXO_OPTION );
		if ( is_array( $options['taxonomies'] ) ) {
			foreach( (array) $options['taxonomies'] as $taxonomy ) {
				register_taxonomy( $taxonomy['name'], $taxonomy['objects'], self::prepareArgs( $taxonomy ) );
			}
			return true;
		}
		return false;
	}

	/**
	 * Prepare ARGS from DB to function API
	 */
	public static function prepareArgs( $taxonomy ) {
		// Empty query_private ? use name
		$taxonomy['query_var'] = trim($taxonomy['query_var']);
		if ( empty($taxonomy['query_var']) ) {
			$taxonomy['query_var'] = $taxonomy['name'];
		}
		
		// Rewrite
		$taxonomy['rewrite'] = (boolean) $taxonomy['rewrite'];
		if ( $taxonomy['rewrite'] == true ) {
			$taxonomy['rewrite'] = array( 'slug' => $taxonomy['query_var'], 'with_front' => true, 'hierarchical' => false );
		}
		
		// Clean labels
		foreach( $taxonomy['labels'] as $k => $v ) {
			$taxonomy['labels'][$k] = stripslashes($v);
		}
		
		return array(
			'hierarchical' 			=> $taxonomy['hierarchical'],
			'update_count_callback' => '_update_post_term_count', // use default WP callback
			'rewrite' 				=> $taxonomy['rewrite'],
			'query_var' 			=> $taxonomy['query_var'],
			'public' 				=> (boolean) $taxonomy['public'],
			'show_ui' 				=> (boolean) $taxonomy['show_ui'],
			'show_tagcloud' 		=> (boolean) $taxonomy['show_tagcloud'],
			'labels' 				=> $taxonomy['labels'],
			'capabilities' 			=> $taxonomy['capabilities'],
			'show_in_nav_menus' 	=> (boolean) $taxonomy['show_in_nav_menus']
		);
	}
	
	/**
	 * Allow to display the taxonomy template, even if the term is empty
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function template_redirect() {
		global $wp_query;
		
		if ( isset($wp_query->query_vars['term']) && isset($wp_query->query_vars['taxonomy']) && isset($wp_query->query_vars[$wp_query->query_vars['taxonomy']]) ) {
			$wp_query->is_404 = false;
			$wp_query->is_tax = true;
		}
	}
	
	/**
	 * Allow to build a correct page title for empty term. Otherwise, the term is null.
	 *
	 * @param string $title 
	 * @param string $sep 
	 * @return string
	 * @author Amaury Balmer
	 */
	public static function wp_title( $title = '', $sep = '' ) {
		global $wp_query;
		
		// If there's a taxonomy
		if ( is_tax() && $wp_query->get_queried_object() == null ) {
			// Taxo
			$taxonomy = get_query_var( 'taxonomy' );
			$tax = get_taxonomy( $taxonomy );
			
			// Build unique key
			$key = 'current-term'.get_query_var('term').$tax->name;
			
			// Terms
			$term = wp_cache_get( $key, 'terms' );
			if ( $term == false || $term == null ) {
				$term = get_term_by( 'slug', get_query_var('term'), $tax->name, OBJECT, 'display' );
				wp_cache_set( $key, $term, 'terms');
			}
			
			// Format Output
			$title = $tax->label . " $sep ". $term->name;
		}
		
		return $title;
	}
	
	/**
	 * Build an xHTML list of terms when the post have custom taxonomy.
	 *
	 * @param string $content 
	 * @param string $type 
	 * @return string
	 * @author Amaury Balmer
	 */
	public static function taxonomyFilter( $content, $type ) {
		global $post;
		
		$output = '';
		
		$options = get_option( STAXO_OPTION );
		foreach ( (array) $options['taxonomies'] as $taxonomy ) {
			
			$filter = false;
			if ( $type == 'content' && isset($taxonomy['filter']) && (boolean) $taxonomy['filter'] == true ) {
				$filter = true;
			} else if ( $type == 'excerpt' && isset($taxonomy['filterexcerpt']) && (boolean) $taxonomy['filterexcerpt'] == true ) {
				$filter = true;
			}
			
			if ( $filter == true ) {
				$terms = get_the_term_list( $post->ID, $taxonomy['name'], $taxonomy['label'].': ', ', ', '' );
				if ( !empty($terms) )
					$output .= "\t".'<div class="taxonomy-'.$taxonomy['name'].'">'.$terms."</div>\n";
				else
					$output .= "\t".'<!-- No terms for the taxonomy : '.$taxonomy['name'].' -->'."\n";
			}
		
		}
		
		if ( !empty($output) ) {
			$content .= '<div class="simple-taxonomy">'."\n".$output."\n".'</div>'."\n";
		}
		
		return $content;
	}
	
	/**
	 * Meta function for call filter taxonomy with the context "content"
	 *
	 * @param string $content 
	 * @return string
	 * @author Amaury Balmer
	 */
	public static function the_content( $content = '' ) {
		return self::taxonomyFilter( $content, 'content' );
	}
	
	/**
	 * Meta function for call filter taxonomy with the context "excerpt"
	 *
	 * @param string $content 
	 * @return string
	 * @author Amaury Balmer
	 */
	public static function the_excerpt( $content = '' ) {
		return self::taxonomyFilter( $content, 'excerpt' );
	}
}
?>