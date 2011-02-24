<?php
class SimpleTaxonomy_Admin_Import{
	private $import_slug 		= 'simple-taxonomy-import';
	
	// Error management
	private $message = '';
	private $status  = '';
		
	/**
	 * Constructor
	 *
	 */
	function simpletaxonomy_admin_import() {
		add_action( 'admin_init', array(&$this, 'checkAdminPost') );
		add_action( 'admin_menu', array(&$this, 'addMenu') );
	}
	
	/**
	 * Meta function for load all check functions.
	 *
	 */
	function checkAdminPost() {
		$this->checkImportation();
	}
	
	/**
	 * Add settings menu page
	 *
	 **/
	function addMenu() {
		add_management_page( __('Terms importation', 'simple-taxonomy'), __('Terms importation', 'simple-taxonomy'), 'manage_options', $this->import_slug, array( &$this, 'pageImportation' ) );
	}
	
	/**
	 * Check POST datas for bulk importation
	 *
	 * @return boolean
	 */
	function checkImportation() {
		global $wpdb;
		
		if ( isset($_POST['taxonomy-import']) ) {
			check_admin_referer( 'import-terms' );
			
			if ( !taxonomy_exists($_POST['taxonomy']) ) {
				wp_die( __('Tcheater, you try to import terms on a taxonomy that not exists.', 'simple-taxonomy') );
			}
			
			$prev_ids = array();
			$terms  = explode( "\n", $_POST['import_content'] );
			
			$j = 0;
			foreach( (array) $terms as $term_line ) {
				/*
				if ( trim($term_line) == '' ) {
					continue;
				}
				*/
				
				if ( $_POST['hierarchy'] != 'no' ) {
					if ( $_POST['hierarchy'] == 'space' ) {
						$sep = " ";
					} else {
						$sep = "\t";
					}
					
					$level = strlen($term_line) - strlen(ltrim( $term_line, $sep ));
					
					if ( $j == 0 ) {
						$prev_ids[0] = $this->createTerm( $_POST['taxonomy'], $term_line, 0 );
					} else {
						if ( ($level - 1 ) < 0 ) {
							$parent = 0;
						} else {
							$parent = $prev_ids[$level - 1];
						}
						
						$prev_ids[$level] = $this->createTerm( $_POST['taxonomy'], $term_line, $parent );
					}
				} else {
					$this->createTerm( $_POST['taxonomy'], $term_line, 0 );
				}
				
				$j++;
			}
			
			if ( $j > 0 ) {
				$this->message = sprintf(__('Done, %d terms imported with success !', 'simple-taxonomy'), $j);
			} else {
				$this->status  = 'error';
				$this->message = __('Done, but you have imported any term.', 'simple-taxonomy');
			}
			
			return true;
		
		}
		return false;
	}
	
	/**
	 * Create term on a taxonomy if necessary
	 *
	 * @param string $taxonomy 
	 * @param string $term_name 
	 * @param integer $parent 
	 * @return integer|boolean
	 * @author Amaury Balmer
	 */
	function createTerm( $taxonomy = '', $term_name = '', $parent = 0 ) {
		$term_name = trim($term_name);
		if ( empty($term_name) )
			return false;
		
		$id = term_exists($term_name, $taxonomy, $parent);
		if ( is_array($id) )
			$id = (int) $id['term_id'];
		
		if ( (int) $id != 0 ) {
			return $id;
		}
		
		// Insert on DB
		$term = wp_insert_term( $term_name, $taxonomy, array('parent' => $parent) );
		
		// Cache
		clean_term_cache($parent, $taxonomy);
		clean_term_cache($term['term_id'], $taxonomy);
		
		return $term['term_id'];
	}
	
	/**
	 * Display page for allow import in custom taxonomies.
	 *
	 */
	function pageImportation() {
		if ( !isset($_POST['import_content']) ) $_POST['import_content'] = '';
		if ( !isset($_POST['taxonomy']) ) $_POST['taxonomy'] = '';
		if ( !isset($_POST['hierarchy']) ) $_POST['hierarchy'] = '';
		
		$this->displayMessage();
		?>
		<div class="wrap">
			<h2><?php _e('Terms import', 'simple-taxonomy'); ?></h2>
			<p><?php _e('This page allows to import an list of words as terms of a taxonomy.', 'simple-taxonomy'); ?></p>
			
			<form action="<?php echo admin_url( 'tools.php?page='.$this->import_slug ); ?>" method="post">
				<p>
					<label for="taxonomy"><?php _e('Choose a taxonomy', 'simple-taxonomy'); ?></label>
					<br />
					<select name="taxonomy" id="taxonomy">
						<?php
						foreach( get_taxonomies( array( 'show_ui' => true, 'public' => true ), 'object' ) as $taxonomy ) {
							echo '<option value="'.esc_attr($taxonomy->name).'" '.selected($_POST['taxonomy'], $taxonomy->name, false).'> '.esc_html($taxonomy->label).' ('.esc_html($taxonomy->name).')</option>' . "\n";
						}
						?>
					</select>
				</p>
				
				<p>
					<label for="hierarchy"><?php _e('Keep the hierarchy ?', 'simple-taxonomy'); ?></label>
					<br />
					<select name="hierarchy" id="hierarchy">
						<option value="no" <?php selected($_POST['hierarchy'], 'no'); ?>><?php _e('No', 'simple-taxonomy'); ?></option>
						<option value="space" <?php selected($_POST['hierarchy'], 'space'); ?>><?php _e('Yes, i use space for indentation', 'simple-taxonomy'); ?></option>
						<option value="tab" <?php selected($_POST['hierarchy'], 'tab'); ?>><?php _e('Yes, i use tab for indentation', 'simple-taxonomy'); ?></option>
					</select>
				</p>
				
				<p>
					<label for="import_content"><?php _e('Your words to import', 'simple-taxonomy'); ?></label>
					<br />
					<textarea name="import_content" id="import_content" rows="30" style="width:100%"><?php echo stripslashes($_POST['import_content']); ?></textarea>
				</p>
				
				<p class="submit">
					<?php wp_nonce_field( 'import-terms' ); ?>
					<input type="submit" name="taxonomy-import" value="<?php _e('Import theses word as terms', 'simple-taxonomy'); ?>" class="button-primary" />
				</p>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Display WP alert
	 *
	 */
	function displayMessage() {
		if ( $this->message != '') {
			$message = $this->message;
			$status = $this->status;
			$this->message = $this->status = ''; // Reset
		}
		
		if ( isset($message) && !empty($message) ) {
		?>
			<div id="message" class="<?php echo ($status != '') ? $status :'updated'; ?> fade">
				<p><strong><?php echo $message; ?></strong></p>
			</div>
		<?php
		}
	}
}
?>