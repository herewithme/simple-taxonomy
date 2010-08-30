<?php
/**
 * Get the current term of tax view from DB. Use WP_Query datas.
 * 
 * @return object term
 */
if ( !function_exists('get_current_term') ) :
	function get_current_term() {
		if ( !is_tax() )
			return false;
			
		// Build unique key
		$key = 'current-term-'.get_query_var('term').'-'.get_query_var('taxonomy');
		
		// Get current term
		$term = wp_cache_get( $key, 'terms' );
		if ( $term == false || $term == null ) {
			$term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy'), OBJECT, 'display' );
			if ( $term == false ) {
				return false;
			}
			wp_cache_set( $key, $term, 'terms');
		}
		
		return $term;
	}
endif;

/**
 * Return the term title on tax view.
 * 
 */
function st_get_term_title( $prefix = false ) {
	if ( !is_tax() )
		return '';
	
	// Get current term
	$term = get_current_term();
	if ( $term == false ) {
		return false;
	}
	
	if ( $prefix == true ) {
		$taxonomy = get_taxonomy ( get_query_var('taxonomy') );
		return apply_filters( 'get_term_title', $taxonomy->label .' : '. $term->name, $prefix );
	}
	
	return apply_filters( 'get_term_title', $term->name, $prefix );
}

/**
 * Display the term title on tax view.
 * 
 * use st_get_term_title()
 * 
 */
function st_term_title() {
	echo st_get_term_title();
}

/**
 * Return the term description on tax view.
 * 
 */
function st_get_term_description() {
	if ( !is_tax() )
		return '';
	
	// Get current term
	$term = get_current_term();
	if ( $term == false ) {
		return false;
	}
	
	return apply_filters( 'get_term_description', $term->description, $term );
}
?>