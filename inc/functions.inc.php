<?php
/**
 * Get term datas with only the taxonomy and the term taxonomy ID.
 * 
 * @param (integer) $term_taxonomy_id
 * @param (string) $taxonomy
 * @return boolean/object
 */
function get_term_by_tt_id( $term_taxonomy_id = 0, $taxonomy = '' ) {
	global $wpdb;
	
	$term_taxonomy_id = (int) $term_taxonomy_id;
	if ( $term_taxonomy_id == 0 )
		return false;
		
	if ( !isset($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy) )
		return false;
	
	$key = md5( $term_taxonomy_id . $taxonomy );
	$term = wp_cache_get($key, 'terms');
	if ( false === $term ) {
		$term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND tt.term_taxonomy_id = %d LIMIT 1", $taxonomy, $term_taxonomy_id) );
		wp_cache_set( $key, $term, 'terms' );
	}
	
	return $term;
}
?>