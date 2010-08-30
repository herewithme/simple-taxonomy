<?php
class SimpleTaxonomy_Admin_Conversion{
	var $conversion_url = '';
	var $conv_slug 		= 'simple-taxonomy-conversion';
	
	/**
	 * Constructor
	 *
	 */
	function simpletaxonomy_admin_conversion() {
		$this->conversion_url 	= admin_url( 'tools.php?page='.$this->conv_slug );
		
		add_action( 'admin_init', array(&$this, 'checkAdminPost') );
		add_action( 'admin_menu', array(&$this, 'addMenu') );
	}
	
	/**
	 * Meta function for load all check functions.
	 *
	 */
	function checkAdminPost() {
		// Conversion
		$this->checkConversion();
	}
	
	/**
	 * Add settings menu page
	 *
	 **/
	function addMenu() {
		add_management_page( __('Terms conversion', 'simple-taxonomy'), __('Terms conversion', 'simple-taxonomy'), 'manage_options', $this->conv_slug, array( &$this, 'pageConversion' ) );
	}
	
	/**
	 * Check POST date for convert terms on an another taxonomy.
	 *
	 * @return boolean
	 */
	function checkConversion() {
		global $wpdb;
		
		if ( isset($_POST['taxonomy-convert']) && $_POST['taxonomy-convert'] == '1' ) {
			
			check_admin_referer( 'convert-terms' );
			
			foreach( (array) $_POST['terms'] as $tt_id => $new_taxonomy ) {
				
				// Clean object cache
				$objects = $wpdb->get_col( $wpdb->prepare("SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d GROUP BY object_id", $tt_id) );
				foreach ( (array) $objects as $object )
					clean_post_cache($object);
				
				// Change the tt_id to new taxonomy
				$wpdb->update($wpdb->term_taxonomy, array('taxonomy' => $new_taxonomy), array('term_taxonomy_id' => $tt_id) );
			
			}
			
			return true;
		
		}
		return false;
	}
	
	/**
	 * Display page for allow conversion between taxonomies.
	 *
	 */
	function pageConversion() {
		global $wp_taxonomies;
		?>
		<div class="wrap">
			<h2><?php _e('Terms conversion', 'simple-taxonomy'); ?></h2>
			<p><?php _e('This page allows to convert the terms at present used as post tags in another taxonomy. The relation with objects is preserved.', 'simple-taxonomy'); ?></p>
			
			<form action="<?php echo $this->conversion_url; ?>" method="post">
				<?php if ( isset($_POST['step']) && $_POST['step'] == '1' && isset($_POST['taxonomy']) && taxonomy_exists($_POST['taxonomy']) ) : ?>
					
					<table class="form-table">
						<thead>
							<tr>
								<th><?php _e('Term name', 'simple-taxonomy'); ?></th>
								<th><?php _e('New taxonomy', 'simple-taxonomy'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$terms = get_terms( $_POST['taxonomy'], 'get=all' );
							if ( $terms == false ) :
								echo '<tr class="form-field form-required"><td colspan="2">'.__('No terms for this taxonomy.', 'simple-taxonomy').'</td></tr>' . "\n";
							else :
								
								// make once the loop.
								$select_html = '';
								foreach( (array) $wp_taxonomies as $taxonomy ) {
									$select_html .= '<option '.selected($taxonomy->name, $_POST['taxonomy'], false).' value="'.esc_attr($taxonomy->name).'"> '.esc_html($taxonomy->label).' ('.esc_html($taxonomy->name).')</option>' . "\n";
								}
								
								foreach( (array) $terms as $term ) :
	  							?>
	  							<tr class="form-field form-required">
	  								<td><label for="term-<?php echo $term->term_id; ?>"><?php esc_html_e($term->name); ?><label></td>
	  								<td>
										<select name="terms[<?php echo $term->term_taxonomy_id; ?>]" id="term-<?php echo $term->term_id; ?>">
											<?php echo $select_html; ?>
										</select>
									</td>
	  							</tr>
								<?php
								endforeach;
							endif;
							?>
						</tbody>
					</table>
					
					<?php if ( $terms == false ) : ?>
						<br />
						<a class="button-primary" href="<?php echo admin_url('tools.php?page='.$this->conv_slug); ?>"><?php _e('Use an another taxonomy.', 'simple-taxonomy'); ?></a>
					<?php else: ?>
						<p class="submit">
							<?php wp_nonce_field( 'convert-terms' ); ?>
							
							<input type="hidden" name="step" value="1" />
							<input type="hidden" name="taxonomy" value="<?php esc_attr_e($_POST['taxonomy']); ?>" />
							
							<input type="hidden" name="taxonomy-convert" value="1" />
							<input type="submit" value="<?php _e('Convert terms', 'simple-taxonomy'); ?>" class="button-primary" />
						</p>
					<?php endif; ?>
				
				<?php else : ?>
					
					<p>
						<label for="taxonomy"><?php _e('Choose a taxonomy', 'simple-taxonomy'); ?></label>
						<select name="taxonomy" id="taxonomy">
							<?php
							foreach( (array) $wp_taxonomies as $taxonomy ) {
								echo '<option value="'.esc_attr($taxonomy->name).'"> '.esc_html($taxonomy->label).' ('.esc_html($taxonomy->name).')</option>' . "\n";
							}
							?>
						</select>
					</p>
					
					<p class="submit">
						<input type="hidden" name="step" value="1" />
						<input type="submit" value="<?php _e('Load terms for this taxonomy', 'simple-taxonomy'); ?>" class="button-primary" />
					</p>
				
				<?php endif; ?>
			</form>
		</div>
		<?php
	}
}
?>