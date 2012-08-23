<?php
class SimpleTaxonomy_Admin {
	const admin_slug = 'simple-taxonomy-settings';
	private $admin_url 	= '';
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public function __construct() {
		// Register hooks
		// add_action( 'admin_init', array(__CLASS__, 'initStyleScript') );
		add_action( 'activity_box_end', array(__CLASS__, 'activity_box_end') );
		add_action( 'admin_init', array(__CLASS__, 'admin_init') );
		add_action( 'admin_menu', array(__CLASS__, 'admin_menu') );
	}
	
	/**
	 * Add custom taxo on dashboard
	 */
	public static function activity_box_end() {
		$options = get_option( STAXO_OPTION );
		if ( !is_array( $options['taxonomies'] ) )
			return false;
		?>
		<div id="dashboard-custom-taxo">
			<table>
				<tbody>
					<?php
					foreach( (array) $options['taxonomies'] as $taxonomy ) :
						$taxo = get_taxonomy( $taxonomy['name'] );
						if ( $taxo == false || is_wp_error($taxo) )
							continue;
						?>
						<tr>
							<td class="first b b-<?php echo $taxo->name; ?>"><a href="edit-tags.php?taxonomy=<?php echo $taxo->name; ?>">15</a></td>
							<td class="t <?php echo $taxo->name; ?>"><a href="edit-tags.php?taxonomy=<?php echo $taxo->name; ?>"><?php echo $taxo->labels->name; ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<script type="text/javascript">
			jQuery(".table_content table tbody").append( jQuery("#dashboard-custom-taxo table tbody").html() );
			jQuery("#dashboard-custom-taxo").remove();
		</script>
		<?php
	}
	
	/**
	 * Load JS and CSS need for admin features.
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	/*
	public static function initStyleScript() {
		global $pagenow;
		
		if ( in_array( $pagenow, array('post.php', 'post-new.php') ) ) {
			wp_enqueue_style( 'simple-custom-types', STAXO_URL.'/ressources/admin.css', array(), STAXO_VERSION );
		}
	}
	*/
	
	/**
	 * Meta function for load all check functions.
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function admin_init() {
		self::checkMergeTaxonomy();
		self::checkDeleteTaxonomy();
		self::checkExportTaxonomy();
		self::checkImportExport();
	}
	
	/**
	 * Add settings menu page
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function admin_menu() {
		add_options_page( __('Simple Taxonomy : Custom Taxonomies', 'simple-taxonomy'), __('Custom Taxonomies', 'simple-taxonomy'), 'manage_options', self::admin_slug, array( __CLASS__, 'pageManage' ) );
	}
	
	/**
	 * Allow to display only form.
	 *
	 * @param array $taxonomy 
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function pageForm( $taxonomy ) {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php printf(__('Custom Taxonomy : %s', 'simple-taxonomy'), stripslashes($taxonomy['labels']['name'])); ?></h2>
			
			<div class="form-wrap">
				<?php self::formMergeCustomType( $taxonomy ); ?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Display options on admin
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function pageManage() {
		// Admin URL
		$admin_url = admin_url( 'options-general.php?page='.self::admin_slug );

		// Get current options
		$current_options = get_option( STAXO_OPTION );
		
		// Check get for message
		if ( isset($_GET['message']) ) {
			switch ( $_GET['message'] ) {
				case 'flush-deleted' :
					add_settings_error('simple-taxonomy', 'settings_updated', __('Taxonomy and relations deleted with success !', 'simple-taxonomy'), 'updated');
					break;
				case 'deleted' :
					add_settings_error('simple-taxonomy', 'settings_updated', __('Taxonomy deleted with success !', 'simple-taxonomy'), 'updated');
					break;
				case 'added' :
					add_settings_error('simple-taxonomy', 'settings_updated', __('Taxonomy added with success !', 'simple-taxonomy'), 'updated');
					break;
				case 'updated' :
					add_settings_error('simple-taxonomy', 'settings_updated', __('Taxonomy updated with success !', 'simple-taxonomy'), 'updated');
					break;
			}
		}
		
		// Display message
		settings_errors('simple-taxonomy');
		
		if ( isset($_GET['action']) && isset($_GET['taxonomy_name']) && $_GET['action'] == 'edit' && isset($current_options['taxonomies'][$_GET['taxonomy_name']]) ) {
			self::pageForm( $current_options['taxonomies'][$_GET['taxonomy_name']] );
			return true;
		}
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e("Simple Taxonomy : Custom Taxonomies", 'simple-taxonomy'); ?></h2>
			
			<div class="message updated">
				<p><?php _e('<strong>Warning :</strong> Delete & Flush a taxonomy will also delete all terms of these taxonomy and all object relations.', 'simple-taxonomy'); ?></p>
			</div>
			
			<div id="col-container">
				<table class="widefat tag fixed" cellspacing="0">
					<thead>
						<tr>
							<th scope="col" id="label" class="manage-column column-name"><?php _e('Label', 'simple-taxonomy'); ?></th>
							<th scope="col" id="name"  class="manage-column column-slug"><?php _e('Name', 'simple-taxonomy'); ?></th>
							<th scope="col" id="label" class="manage-column column-name"><?php _e('Objects', 'simple-taxonomy'); ?></th>
							<th scope="col" id="label" class="manage-column column-name"><?php _e('Hierarchical', 'simple-taxonomy'); ?></th>
							<th scope="col" id="label" class="manage-column column-name"><?php _e('Public', 'simple-taxonomy'); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-name"><?php _e('Label', 'simple-taxonomy'); ?></th>
							<th scope="col" class="manage-column column-slug"><?php _e('Name', 'simple-taxonomy'); ?></th>
							<th scope="col" class="manage-column column-name"><?php _e('Objects', 'simple-taxonomy'); ?></th>
							<th scope="col" class="manage-column column-name"><?php _e('Hierarchical', 'simple-taxonomy'); ?></th>
							<th scope="col" class="manage-column column-name"><?php _e('Public', 'simple-taxonomy'); ?></th>
						</tr>
					</tfoot>
			
					<tbody id="the-list" class="list:taxonomies">
						<?php
						if ( $current_options == false || empty($current_options['taxonomies']) ) :
							echo '<tr><td colspan="3">'.__('No custom taxonomy.', 'simple-taxonomy').'</td></tr>';
						else :
							$class = 'alternate';
							$i = 0;
							foreach( (array) $current_options['taxonomies'] as $_t_name =>$_t ) :
								$i++;
								$class = ( $class == 'alternate' ) ? '' : 'alternate';
								?>
								<tr id="taxonomy-<?php echo $i; ?>" class="<?php echo $class; ?>">
									<td class="name column-name">
										<strong><a class="row-title" href="<?php echo $admin_url; ?>&amp;action=edit&amp;taxonomy_name=<?php echo $_t_name; ?>" title="<?php esc_attr_e(sprintf(__('Edit the taxonomy &#8220;%s&#8221;', 'simple-taxonomy'), $_t['labels']['name'])); ?>"><?php echo esc_html(stripslashes($_t['labels']['name'])); ?></a></strong>
										<br />
										<div class="row-actions">
											<span class="edit"><a href="<?php echo $admin_url; ?>&amp;action=edit&amp;taxonomy_name=<?php echo $_t_name; ?>">Modifier</a> | </span>
											<span class="export"><a class="export_php-taxonomy" href="<?php echo wp_nonce_url($admin_url.'&amp;action=export_php&amp;taxonomy_name='.$_t_name, 'export_php-taxo-'.$_t_name); ?>"><?php _e('Export PHP', 'simple-taxonomy'); ?></a> | </span>
											<span class="delete"><a class="delete-taxonomy" href="<?php echo wp_nonce_url($admin_url.'&amp;action=delete&amp;taxonomy_name='.$_t_name, 'delete-taxo-'.$_t_name); ?>" onclick="if ( confirm( '<?php echo esc_js( sprintf( __( "You are about to delete this taxonomy '%s'\n  'Cancel' to stop, 'OK' to delete.", 'simple-taxonomy' ), $_t['labels']['name'] ) ); ?>' ) ) { return true;}return false;"><?php _e('Delete', 'simple-taxonomy'); ?></a> | </span>
											<span class="delete"><a class="flush-delete-taxonomy" href="<?php echo wp_nonce_url($admin_url.'&amp;action=flush-delete&amp;taxonomy_name='.$_t_name, 'flush-delete-taxo-'.$_t_name); ?>" onclick="if ( confirm( '<?php echo esc_js( sprintf( __( "You are about to delete and flush this taxonomy '%s' and all relations.\n  'Cancel' to stop, 'OK' to delete.", 'simple-taxonomy' ), $_t['labels']['name'] ) ); ?>' ) ) { return true;}return false;"><?php _e('Flush & Delete', 'simple-taxonomy'); ?></a></span>
										</div>
									</td>
									<td><?php echo esc_html($_t['name']); ?></td>
									<td>
										<?php
										if ( is_array($_t['objects']) && !empty($_t['objects']) ) {
											foreach( $_t['objects'] as $k => $post_type ) {
												$cpt = get_post_type_object($post_type);
												if ( $cpt == null ) {
													unset($_t['objects'][$k]);
												} else {
													$_t['objects'][$k] = $cpt->labels->name;
												}
											}
											echo esc_html(implode(', ', (array) $_t['objects']));
										} else {
											echo '-';
										}
										?>
									</td>
									<td><?php echo esc_html(self::getTrueFalse($_t['hierarchical'])); ?></td>
									<td><?php echo esc_html(self::getTrueFalse($_t['public'])); ?></td>
								</tr>
							<?php
							endforeach;
						endif;
						?>
					</tbody>
				</table>
				
				<br class="clear" />
				
				<div class="form-wrap">
					<h3><?php _e('Add a new taxonomy', 'simple-taxonomy'); ?></h3>
					<?php self::formMergeCustomType(); ?>
				</div>
			</div><!-- /col-container -->
		</div>
		
		<div class="wrap">
			<h2><?php _e("Simple Taxonomy : Export/Import", 'simple-taxonomy'); ?></h2>
			
			<a class="button" href="<?php echo wp_nonce_url($admin_url.'&amp;action=export_config_st', 'export-config-st'); ?>"><?php _e("Export config file", 'simple-taxonomy'); ?></a>
			<a class="button" href="#" id="toggle-import_form"><?php _e("Import config file", 'simple-taxonomy'); ?></a>
			<script type="text/javascript">
				jQuery("#toggle-import_form").click(function(event) {
					event.preventDefault();
					jQuery('#import_form').removeClass('hide-if-js');
				});
			</script>
			<div id="import_form" class="hide-if-js">
				<form action="<?php echo $admin_url ; ?>" method="post" enctype="multipart/form-data">
					<p>
						<label><?php _e("Config file", 'simple-taxonomy'); ?></label>
						<input type="file" name="config_file" />
					</p>
					<p class="submit">
						<?php wp_nonce_field( 'import_config_file_st' ); ?>
						<input class="button-primary" type="submit" name="import_config_file_st" value="<?php _e('I want import a config from a previous backup, this action will REPLACE current configuration', 'simple-taxonomy'); ?>" />
					</p>
				</form>
			</div>
		</div>
		<?php
		return true;
	}
	
	/**
	 * Build HTML for form custom taxonomy, add with list on right column
	 *
	 * @param array $taxonomy 
	 * @return void
	 * @author Amaury Balmer
	 */
	private static function formMergeCustomType( $taxonomy = null ) {
		// Admin URL
		$admin_url = admin_url( 'options-general.php?page='.self::admin_slug );
		
		if ( $taxonomy == null ) {
			$edit 		 = false;
			$_action 	 = 'add-taxonomy';
			$submit_val	 = __('Add taxonomy', 'simple-taxonomy');
			$nonce_field = 'simpletaxonomy-add-taxo';
			
			foreach( self::getFields() as $field => $default_value ) {  // Use default value
				if ( is_array($default_value) ) {
					$taxonomy[$field] = array();
					foreach( $default_value as $k => $_v ) {
						if ( is_string($_v) ) {
							$taxonomy[$field][$k] = trim(stripslashes($_v));
						} else {
							$taxonomy[$field][$k] = $_v;
						}
					}
				} else {
					$taxonomy[$field] = $default_value;
				}
			}
		} else {
			$edit 		 = true;
			$_action 	 = 'merge-taxonomy';
			$submit_val	 = __('Update taxonomy', 'simple-taxonomy');
			$nonce_field = 'simpletaxonomy-edit-taxo';
			
			// clean values
			foreach( self::getFields() as $field => $default_value ) {
				if ( isset($taxonomy[$field]) && is_string($taxonomy[$field]) ) { // Isset, juste clean values
					$taxonomy[$field] = trim(stripslashes($taxonomy[$field]));
				} elseif ( isset($taxonomy[$field]) && is_array($taxonomy[$field]) ) { // Isset, but dispatch array
					foreach( $taxonomy[$field] as $k => $_v ) {
						if ( is_string($_v) ) {
							$taxonomy[$field][$k] = trim(stripslashes($_v));
						} else {
							$taxonomy[$field][$k] = $_v;
						}
					}
				} elseif ( !isset($taxonomy[$field]) ) { // No set, try to set default values
					if ( is_array($default_value) ) {
						$taxonomy[$field] = array();
						foreach( $default_value as $k => $_v ) {
							if ( is_string($_v) ) {
								$taxonomy[$field][$k] = trim(stripslashes($_v));
							} else {
								$taxonomy[$field][$k] = $_v;
							}
						}
					} else {
						$taxonomy[$field] = $default_value;
					}
				}
			}
		}
		?>
		<form id="addtag" method="post" action="<?php echo $admin_url ; ?>">
			<input type="hidden" name="action" value="<?php echo $_action; ?>" />
			<?php wp_nonce_field( $nonce_field ); ?>
			
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div class="inner-sidebar">
					<div class="meta-box-sortabless">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('Rewrite URL', 'simple-taxonomy'); ?></span></h3>
							<div class="inside">
								<p>
									<label for="query_var"><?php _e('Query var', 'simple-taxonomy'); ?></label>
									<input name="query_var" id="query_var" type="text" value="<?php echo esc_attr($taxonomy['query_var']); ?>" />
								</p>
								<p class="description"><?php _e("<strong>Query var</strong> is used for build URLs of taxonomy. If this value is empty, Simple Taxonomy will use a slug from label for build URL.", 'simple-taxonomy'); ?></p>
								
								<p>
									<label for="rewrite"><?php _e('Rewrite ?', 'simple-taxonomy'); ?></label>
									<select name="rewrite" id="rewrite" style="width:50%">
										<?php
										foreach( self::getTrueFalse() as $type_key => $type_name ) {
											echo '<option '.selected($taxonomy['rewrite'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
										}
										?>
									</select>
								</p>
								<p class="description"><?php _e("Rewriting allow to build nice URL for your new custom taxonomy.", 'simple-taxonomy'); ?></p>
							</div>
						</div>
					</div>
					
					<div class="meta-box-sortabless">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('Administration', 'simple-taxonomy'); ?></span></h3>
							<div class="inside">
								<p>
									<label for="show_ui"><?php _e('Display on admin ?', 'simple-taxonomy'); ?></label>
									<select name="show_ui" id="show_ui" style="width:50%">
										<?php
										foreach( self::getTrueFalse() as $type_key => $type_name ) {
											echo '<option '.selected($taxonomy['show_ui'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
										}
										?>
									</select>
									<p class="description"><?php _e("Whether to generate a default UI for managing this taxonomy.", 'simple-taxonomy'); ?></p>
								</p>
								
								<p>
									<label for="metabox"><?php _e('Admin form', 'simple-taxonomy'); ?></label>
									<select id="metabox" name="metabox" style="width:50%">
										<?php
										foreach( self::getAdminTypes() as $type_key => $type_name ) {
											echo '<option '.selected($taxonomy['metabox'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
										}
										?>
									</select>
									<p class="description"><?php _e("<strong>Admin form</strong> allow to choose a kind of selector on write content page.", 'simple-taxonomy'); ?></p>
								</p>
								
								<p>
									<label for="show_in_nav_menus"><?php _e('Show in nav menu ?', 'simple-taxonomy'); ?></label>
									<select name="show_in_nav_menus" id="show_in_nav_menus" style="width:50%">
										<?php
										foreach( self::getTrueFalse() as $type_key => $type_name ) {
											echo '<option '.selected($taxonomy['show_in_nav_menus'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
										}
										?>
									</select>
									<p class="description"><?php _e("Put this setting to true for display this taxonomy on main admin menu.", 'simple-taxonomy'); ?></p>
								</p>
								
								<p>
									<label for="show_tagcloud"><?php _e('Show in tag cloud widget ?', 'simple-taxonomy'); ?></label>
									<select name="show_tagcloud" id="show_tagcloud" style="width:50%">
										<?php
										foreach( self::getTrueFalse() as $type_key => $type_name ) {
											echo '<option '.selected($taxonomy['show_tagcloud'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
										}
										?>
									</select>
									<p class="description"><?php _e("Put this setting to true for display this taxonomy on settings of tag cloud widget.", 'simple-taxonomy'); ?></p>
								</p>
							</div>
						</div>
					</div>
					
					<div class="meta-box-sortabless">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('Permissions', 'simple-taxonomy'); ?></span></h3>
							<div class="inside">
								<p>
									<label for="manage_terms"><?php _e('Manager terms', 'simple-taxonomy'); ?></label>
									<input name="capabilities[manage_terms]" id="manage_terms" type="text" value="<?php echo esc_attr($taxonomy['capabilities']['manage_terms']); ?>" />
									<p class="description"><?php _e("Ability to view terms in the administration. Defaults to 'manage_categories'.", 'simple-taxonomy'); ?></p>
								</p>
								
								<p>
									<label for="edit_terms"><?php _e('Edit terms', 'simple-taxonomy'); ?></label>
									<input name="capabilities[edit_terms]" id="edit_terms" type="text" value="<?php echo esc_attr($taxonomy['capabilities']['edit_terms']); ?>" />
									<p class="description"><?php _e("Grants the ability to edit and create terms. Defaults to 'manage_categories'.", 'simple-taxonomy'); ?></p>
								</p>
								
								<p>
									<label for="delete_terms"><?php _e('Delete terms', 'simple-taxonomy'); ?></label>
									<input name="capabilities[delete_terms]" id="delete_terms" type="text" value="<?php echo esc_attr($taxonomy['capabilities']['delete_terms']); ?>" />
									<p class="description"><?php _e("Gives permission to delete terms from the taxonomy. Defaults to 'manage_categories'.", 'simple-taxonomy'); ?></p>
								</p>
								
								<p>
									<label for="assign_terms"><?php _e('Assign terms', 'simple-taxonomy'); ?></label>
									<input name="capabilities[assign_terms]" id="assign_terms" type="text" value="<?php echo esc_attr($taxonomy['capabilities']['assign_terms']); ?>" />
									<p class="description"><?php _e("Capability for assigning terms in the new/edit post screen. Defaults to 'edit_terms'", 'simple-taxonomy'); ?></p>
								</p>
							</div>
						</div>
					</div>
				</div>
				
				<div class="has-sidebar sm-padded">
					<div id="post-body-content" class="has-sidebar-content">
						<div class="meta-box-sortabless">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('Main information', 'simple-taxonomy'); ?></span></h3>
								
								<div class="inside">
									<table class="form-table" style="clear:none;">
										<tr valign="top">
											<th scope="row"><label for="name"><?php _e('Name', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="name" type="text" id="name" value="<?php echo esc_attr($taxonomy['name']); ?>" class="regular-text" <?php if ( $edit==true ) echo 'readonly="readonly"'; ?> />
												<span class="description"><?php _e("<strong>Name</strong> is used on DB. (All lowercase and no weird characters)", 'simple-taxonomy'); ?></span>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="hierarchical"><?php _e('Hierarchical ?', 'simple-taxonomy'); ?></label></th>
											<td>
												<select name="hierarchical" id="hierarchical" style="width:20%">
													<?php
													foreach( self::getTrueFalse() as $type_key => $type_name ) {
														echo '<option '.selected($taxonomy['hierarchical'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
													}
													?>
												</select>
												<span class="description"><?php _e("Default <strong>hierarchical</strong> in WordPress are categories. Default post tags WP aren't hierarchical.", 'simple-taxonomy'); ?></span>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label><?php _e('Post types', 'simple-taxonomy'); ?></label></th>
											<td>
												<?php
												if ( $edit == true ) {
													$current_taxo = get_taxonomy( $taxonomy['name'] );
													$objects = (array) $current_taxo->object_type;
												} else {
													$objects = array();
												}
												foreach( self::getObjectTypes() as $type ) {
													echo '<label class="inline"><input type="checkbox" '.checked( true, in_array($type->name, $objects), false).' name="objects[]" value="'.esc_attr($type->name).'" /> '.esc_html($type->label).'</label>' . "\n";
												}
												?>
												<span class="description"><?php _e("You can add this taxonomy to an builtin or custom post types. (compatible Simple Custom Types)", 'simple-taxonomy'); ?></span>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="auto"><?php _e('Auto add terms', 'simple-taxonomy'); ?></label></th>
											<td>
												<select name="auto" id="auto" style="width:50%">
													<?php
													foreach( self::getAutoContentTypes() as $type_key => $type_name ) {
														echo '<option '.selected($taxonomy['auto'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
													}
													?>
												</select>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
						
						<div class="meta-box-sortabless">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('Visibility', 'simple-taxonomy'); ?></span></h3>
								
								<div class="inside">
									<table class="form-table" style="clear:none;">
										<tr valign="top">
											<th scope="row"><label for="public"><?php _e('Public ?', 'simple-taxonomy'); ?></label></th>
											<td>
												<select name="public" id="public" style="width:20%">
													<?php
													foreach( self::getTrueFalse() as $type_key => $type_name ) {
														echo '<option '.selected($taxonomy['public'], $type_key, false).' value="'.esc_attr($type_key).'">'.esc_html($type_name).'</option>' . "\n";
													}
													?>
												</select>
												<span class="description"><?php _e("Whether taxonomy queries can be performed from the front page.", 'simple-taxonomy'); ?></span>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
						
						<div class="meta-box-sortabless">
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('Translations Wording', 'simple-taxonomy'); ?></span></h3>
								
								<div class="inside">
									<table class="form-table" style="clear:none;">
										<tr valign="top">
											<th scope="row"><label for="labels-name"><?php _e('Post Terms', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[name]" type="text" id="labels-name" value="<?php echo esc_attr($taxonomy['labels']['name']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-singular_name"><?php _e('Post Term', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[singular_name]" type="text" id="labels-singular_name" value="<?php echo esc_attr($taxonomy['labels']['singular_name']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-search_items"><?php _e('Search Terms', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[search_items]" type="text" id="labels-search_items" value="<?php echo esc_attr($taxonomy['labels']['search_items']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-popular_items"><?php _e('Popular Terms', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[popular_items]" type="text" id="labels-popular_items" value="<?php echo esc_attr($taxonomy['labels']['popular_items']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-all_items"><?php _e('All Terms', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[all_items]" type="text" id="labels-all_items" value="<?php echo esc_attr($taxonomy['labels']['all_items']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-parent_item"><?php _e('Parent Term', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[parent_item]" type="text" id="labels-parent_item" value="<?php echo esc_attr($taxonomy['labels']['parent_item']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-parent_item_colon"><?php _e('Parent Term:', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[parent_item_colon]" type="text" id="labels-parent_item_colon" value="<?php echo esc_attr($taxonomy['labels']['parent_item_colon']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-edit_item"><?php _e('Edit Term', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[edit_item]" type="text" id="labels-edit_item" value="<?php echo esc_attr($taxonomy['labels']['edit_item']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-update_item"><?php _e('Update Term', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[update_item]" type="text" id="labels-update_item" value="<?php echo esc_attr($taxonomy['labels']['update_item']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-add_new_item"><?php _e('Add New Term', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[add_new_item]" type="text" id="labels-add_new_item" value="<?php echo esc_attr($taxonomy['labels']['add_new_item']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-new_item_name"><?php _e('New Term Name', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[new_item_name]" type="text" id="labels-new_item_name" value="<?php echo esc_attr($taxonomy['labels']['new_item_name']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-separate_items_with_commas"><?php _e('Separate terms with commas', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[separate_items_with_commas]" type="text" id="labels-separate_items_with_commas" value="<?php echo esc_attr($taxonomy['labels']['separate_items_with_commas']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-add_or_remove_items"><?php _e('Add or remove terms', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[add_or_remove_items]" type="text" id="labels-add_or_remove_items" value="<?php echo esc_attr($taxonomy['labels']['add_or_remove_items']); ?>" class="regular-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="labels-choose_from_most_used"><?php _e('Choose from the most used terms', 'simple-taxonomy'); ?></label></th>
											<td>
												<input name="labels[choose_from_most_used]" type="text" id="labels-choose_from_most_used" value="<?php echo esc_attr($taxonomy['labels']['choose_from_most_used']); ?>" class="regular-text" />
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<p class="submit" style="padding:0 0 1.5em;">
				<input type="submit" class="button-primary" name="submit" id="submit" value="<?php esc_attr_e($submit_val); ?>" />
			</p>
		</form>
		<?php
	}

	/**
	 * Check $_GET/$_POST/$_FILES for Export/Import
	 * 
	 * @return boolean
	 */
	private static function checkImportExport() {
		if ( isset($_GET['action']) && $_GET['action'] == 'export_config_st' ) {
			check_admin_referer('export-config-st');
			
			// No cache
			header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
			header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
			header( 'Cache-Control: post-check=0, pre-check=0', false ); 
			header( 'Pragma: no-cache' ); 
			
			// Force download dialog
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");

			// use the Content-Disposition header to supply a recommended filename and
			// force the browser to display the save dialog.
			header("Content-Disposition: attachment; filename=simple-taxonomy-config-".date('U').".txt;");
			die('SIMPLETAXONOMY'.base64_encode(serialize(get_option( STAXO_OPTION ))));
		} elseif( isset($_POST['import_config_file_st']) && isset($_FILES['config_file']) ) {
			check_admin_referer( 'import_config_file_st' );
			
			if ( $_FILES['config_file']['error'] > 0 ) {
				add_settings_error('simple-taxonomy', 'settings_updated', __('An error occured during the config file upload. Please fix your server configuration and retry.', 'simple-taxonomy'), 'error');
			} else {
				$config_file = file_get_contents( $_FILES['config_file']['tmp_name'] );
				if ( substr($config_file, 0, strlen('SIMPLETAXONOMY')) !== 'SIMPLETAXONOMY' ) {
					add_settings_error('simple-taxonomy', 'settings_updated', __('This is really a config file for Simple Taxonomy ? Probably corrupt :(', 'simple-taxonomy'), 'error');
				} else {
					$config_file = unserialize(base64_decode(substr($config_file, strlen('SIMPLETAXONOMY'))));
					if ( !is_array($config_file) ) {
						add_settings_error('simple-taxonomy', 'settings_updated', __('This is really a config file for Simple Taxonomy ? Probably corrupt :(', 'simple-taxonomy'), 'error');
					} else {
						update_option(STAXO_OPTION, $config_file);
						add_settings_error('simple-taxonomy', 'settings_updated', __('OK. Configuration is restored.', 'simple-taxonomy'), 'updated');
					}
				}
			}
		}
	}
	
	/**
	 * Check $_POST datas for add/merge taxonomy
	 * 
	 * @return boolean
	 */
	private static function checkMergeTaxonomy() {
		if ( isset($_POST['action']) && in_array( $_POST['action'], array('add-taxonomy', 'merge-taxonomy') ) ) {
			
			if ( !current_user_can('manage_options') )
				wp_die(__( 'You cannot edit the Simple Taxonomy options.', 'simple-taxonomy' ));
				
			// Clean values from _POST
			$taxonomy = array();
			foreach( self::getFields() as $field => $default_value ) {
				if ( isset($_POST[$field]) && is_string($_POST[$field]) ) {// String ?
					$taxonomy[$field] = trim( stripslashes( $_POST[$field] ) );
				} elseif ( isset($_POST[$field]) ) {
					if ( is_array($_POST[$field]) ) {
						$taxonomy[$field] = array();
						foreach( $_POST[$field] as $k => $_v ) {
							$taxonomy[$field][$k] = $_v;
						}
					} else {
						$taxonomy[$field] = $_POST[$field];
					}
				} else {
					$taxonomy[$field] = '';
				}
			}
			
			if ( $_POST['action'] == 'merge-taxonomy' && empty($taxonomy['name']) ) {
				wp_die( __('Tcheater ? You try to edit a taxonomy without name. Impossible !', 'simple-taxonomy') );
			}
			
			if ( !empty($taxonomy['name']) ) { // Label exist ?
				// Values exist ? or build it from label ?
				$taxonomy['name'] = ( empty($taxonomy['name']) ) ? $taxonomy['labels']['name'] : $taxonomy['name'];
			
				// Clean sanitize value
				$taxonomy['name'] = sanitize_title($taxonomy['name']);
				
				// Allow plugin to filter datas...
				$taxonomy = apply_filters( 'simple-taxonomy-check-merge', $taxonomy );
				
				if ( $_POST['action'] == 'add-taxonomy' ) {
					check_admin_referer( 'simpletaxonomy-add-taxo' );
					if ( taxonomy_exists($taxonomy['name']) ) { // Default Taxo already exist ?
						wp_die( __('Tcheater ? You try to add a taxonomy with a name already used by an another taxonomy.', 'simple-taxonomy') );
					}
					self::addTaxonomy( $taxonomy );
				} else {
					check_admin_referer( 'simpletaxonomy-edit-taxo' );
					self::updateTaxonomy( $taxonomy );
				}
				
				// Flush rewriting rules !
				global $wp_rewrite;
				$wp_rewrite->flush_rules(false);
				
				return true;
			} else {
				add_settings_error('simple-taxonomy', 'settings_updated', __('Impossible to add your taxonomy... You must enter a taxonomy name.', 'simple-taxonomy'), 'error');
			}
		}
		
		return false;
	}

	/**
	 * Allow to export registration CPT with PHP
	 */
	private static function checkExportTaxonomy() {
		global $simple_taxonomy;
		
		if ( isset($_GET['action']) && isset($_GET['taxonomy_name']) && $_GET['action'] == 'export_php' ) {
			check_admin_referer( 'export_php-taxo-'.$_GET['taxonomy_name'] );
			
			// Get proper taxo name
			$taxo_name = stripslashes($_GET['taxonomy_name']);
			
			// Get taxo data
			$current_options = get_option( STAXO_OPTION );
			if ( !isset($current_options['taxonomies'][$taxo_name]) ) { // Taxo not exist ?
				wp_die( __('Tcheater ? You try to delete a taxonomy who not exist...', 'simple-taxonomy') );
				return false;
			} else {
				$taxo_data = $current_options['taxonomies'][$taxo_name];
			}
			
			// Get proper args
			$args = $simple_taxonomy['client-base']->prepareArgs( $taxo_data );
			
			// Get args to code
			$code = 'register_taxonomy( "'.$taxo_data['name'].'", '.var_export($taxo_data['objects'], true).', '.var_export($args, true).' );';
			
			// Get plugin template
			$output = file_get_contents( STAXO_DIR . '/inc/template/plugin.tpl' );
			
			// Replace marker by variables
			$output = str_replace( '%TAXO_LABEL%', $args['labels']['name'], $output );
			$output = str_replace( '%TAXO_NAME%', str_replace('-', '_', $taxo_name), $output );
			$output = str_replace( '%TAXO_CODE%', $code, $output );
			
			// Force download
			header( "Content-Disposition: attachment; filename=" . $taxo_name.'.php' );
			header( "Content-Type: application/force-download" );
			header( "Content-Type: application/octet-stream" );
			header( "Content-Type: application/download" );
			header( "Content-Description: File Transfer" ); 
			flush(); // this doesn't really matter.
			
			die($output);
			return true;
		}
		
		return false;
	}

	/**
	 * Check $_GET datas for delete a taxonomy
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	private static function checkDeleteTaxonomy() {
		if ( isset($_GET['action']) && isset($_GET['taxonomy_name']) && $_GET['action'] == 'delete' ) {
			check_admin_referer( 'delete-taxo-'.$_GET['taxonomy_name'] );
			
			$taxonomy = array();
			$taxonomy['name'] = stripslashes($_GET['taxonomy_name']);
			self::deleteTaxonomy( $taxonomy, false );
			
			// Flush rewriting rules !
			global $wp_rewrite;
			$wp_rewrite->flush_rules(false);
			
			return true;
		} elseif ( isset($_GET['action']) && isset($_GET['taxonomy_name']) && $_GET['action'] == 'flush-delete' ) {
			check_admin_referer( 'flush-delete-taxo-'.$_GET['taxonomy_name'] );
			
			$taxonomy = array();
			$taxonomy['name'] = stripslashes($_GET['taxonomy_name']);
			self::deleteTaxonomy( $taxonomy, true );
			
			// Flush rewriting rules !
			global $wp_rewrite;
			$wp_rewrite->flush_rules(false);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Add taxonomy in options
	 *
	 * @param array $taxonomy 
	 * @return void
	 * @author Amaury Balmer
	 */
	private static function addTaxonomy( $taxonomy ) {
		$current_options = get_option( STAXO_OPTION );
		
		if ( isset($current_options['taxonomies'][$taxonomy['name']]) ) { // User taxo already exist ?
			wp_die( __('Tcheater ? You try to add a taxonomy with a name already used by an another taxonomy.', 'simple-taxonomy') );
		}
		$current_options['taxonomies'][$taxonomy['name']] = $taxonomy;
		
		update_option( STAXO_OPTION, $current_options );
		
		wp_redirect( admin_url( 'options-general.php?page='.self::admin_slug ).'&message=added' );
		exit();
	}
	
	/**
	 * Update taxonomy in options
	 *
	 * @param array $taxonomy 
	 * @return void
	 * @author Amaury Balmer
	 */
	private static function updateTaxonomy( $taxonomy ) {
		$current_options = get_option( STAXO_OPTION );
		
		if ( !isset($current_options['taxonomies'][$taxonomy['name']]) ) { // Taxo not exist ?
			wp_die( __('Tcheater ? You try to edit a taxonomy with a name different as original. Simple Taxonomy dont allow update the name. Propose a patch ;)', 'simple-taxonomy') );
		}
		$current_options['taxonomies'][$taxonomy['name']] = $taxonomy;
		
		update_option( STAXO_OPTION, $current_options );
		
		wp_redirect( admin_url( 'options-general.php?page='.self::admin_slug ).'&message=updated' );
		exit();
	}
	
	/**
	 * Delete a taxonomy, and optionnaly flush contents
	 *
	 * @param string $taxonomy 
	 * @param boolean $flush_relations 
	 * @return boolean|void
	 * @author Amaury Balmer
	 */
	private static function deleteTaxonomy( $taxonomy, $flush_relations = false ) {
		$current_options = get_option( STAXO_OPTION );
		
		if ( !isset($current_options['taxonomies'][$taxonomy['name']]) ) { // Taxo not exist ?
			wp_die( __('Tcheater ? You try to delete a taxonomy who not exist...', 'simple-taxonomy') );
			return false;
		}
		
		unset($current_options['taxonomies'][$taxonomy['name']]); // Delete from options
		
		if ( $flush_relations == true )
			self::deleteObjectsTaxonomy( $taxonomy['name'] ); // Delete object relations/terms
		
		update_option( STAXO_OPTION, $current_options );
		
		wp_redirect( admin_url( 'options-general.php?page='.self::admin_slug ).'&message=deleted' );
		exit();
	}
	
	/**
	 * Delete all relationship between objects and terms for a specific taxonomy
	 *
	 * @param string $taxo_name 
	 * @return boolean
	 * @author Amaury Balmer
	 */
	private static function deleteObjectsTaxonomy( $taxo_name = '' ) {
		if ( empty($taxo_name) )
			return false;
	
		$terms = get_terms( $taxo_name, 'hide_empty=0&fields=ids' );
		if ( $terms == false || is_wp_error($terms) ) 
			return false;
			
		foreach( (array) $terms as $term ) {
			wp_delete_term( $term, $taxo_name );
		}
		
		return true;
	}
	
	/**
	 * Use for build admin taxonomy
	 *
	 * @param string $key 
	 * @return array|object
	 * @author Amaury Balmer
	 */
	private static function getObjectTypes( $key = '' ) {
		// Get all post types registered.
		$object_types = get_post_types( array(), 'objects' );
		$object_types = apply_filters( 'staxo-object-types', $object_types, $key );
		if ( isset($object_types[$key]) ) {
			return $object_types[$key];
		}
		
		return $object_types;
	}
	
	/**
	 * Use for build selector
	 * 
	 * @param $key
	 * @return string/array
	 */
	private static function getTrueFalse( $key = '' ) {
		$types = array( 
			'1' => __('True', 'simple-taxonomy'), 
			'0' => __('False', 'simple-taxonomy')
		);
		
		if ( isset($types[$key]) ) {
			return $types[$key];
		}
		
		return $types;
	}

	/**
	 * Use for build selector auto terms
	 *
	 * @param string $key 
	 * @return array|string
	 * @author Amaury Balmer
	 */
	private static function getAutoContentTypes( $key = '' ) {
		$content_types = array( 
			'none' 		=> __('None', 'simple-taxonomy'), 
			'content' 	=> __('Content', 'simple-taxonomy'), 
			'excerpt' 	=> __('Excerpt', 'simple-taxonomy'), 
			'both' 		=> __('Content and excerpt', 'simple-taxonomy')
		);
		
		$content_types = apply_filters( 'staxo-auto-content-types', $content_types, $key );
		if ( isset($content_types[$key]) ) {
			return $content_types[$key];
		}
		
		return $content_types;
	}
	
	/**
	 * All types available for write page
	 *
	 * @param string $key 
	 * @return array|string
	 * @author Amaury Balmer
	 */
	private static function getAdminTypes( $key = '' ) {
		$admin_types = array(
			'default'		=> __('Default', 'simple-taxonomy'),
			'select' 		=> __('Select list', 'simple-taxonomy'),
			'select-multi' 	=> __('Select list (multiple)', 'simple-taxonomy')
		);
		
		$admin_types = apply_filters( 'staxo-admin-types', $admin_types, $key );
		if ( isset($admin_types[$key]) ) {
			return $admin_types[$key];
		}
		
		return $admin_types;
	}

	/**
	 * Get array fields for CPT object
	 */
	private static function getFields() {
		return array( 
			'name' 			=> '',
			'objects' 		=> array(),
			'update_count_callback' => '',
			'hierarchical' 	=> 1, 
			'rewrite' 		=> 1,
			'query_var' 	=> '',
			'show_ui' 		=> 1,
			'show_tagcloud' => 1,
			'show_in_nav_menus' => 1,
			'labels' 		=> array(
								'name' 							=> _x( 'Post Terms', 'taxonomy general name', 'simple-taxonomy' ),
								'singular_name' 				=> _x( 'Post Term', 'taxonomy singular name', 'simple-taxonomy' ),
								'search_items' 					=> __( 'Search Terms', 'simple-taxonomy' ),
								'popular_items' 				=> __( 'Popular Terms', 'simple-taxonomy' ),
								'all_items' 					=> __( 'All Terms', 'simple-taxonomy' ),
								'parent_item' 					=> __( 'Parent Term', 'simple-taxonomy' ),
								'parent_item_colon' 			=> __( 'Parent Term:', 'simple-taxonomy' ),
								'edit_item' 					=> __( 'Edit Term', 'simple-taxonomy' ),
								'update_item' 					=> __( 'Update Term', 'simple-taxonomy' ),
								'add_new_item' 					=> __( 'Add New Term', 'simple-taxonomy' ),
								'new_item_name' 				=> __( 'New Term Name', 'simple-taxonomy' ),
								'separate_items_with_commas' 	=> __( 'Separate terms with commas', 'simple-taxonomy' ),
								'add_or_remove_items' 			=> __( 'Add or remove terms', 'simple-taxonomy' ),
								'choose_from_most_used' 		=> __( 'Choose from the most used terms', 'simple-taxonomy' )
							),
			'capabilities' 	=> array(
								'manage_terms' => 'manage_categories',
								'edit_terms'   => 'manage_categories',
								'delete_terms' => 'manage_categories',
								'assign_terms' => 'edit_posts'
							),
			'public' 		=> 1,
			// Specific to plugin
			'objects'		=> array(),
			'metabox' 		=> 'default',
			'auto' 			=> 'none'
		);
	}
}
?>