<?php
/**
 * Class for add a new widget for custom taxonomy (tag cloud or list)
 *
 * @package default
 * @author Amaury Balmer
 */
class SimpleTaxonomy_Widget extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public function __construct() {
		parent::__construct( 
			'staxonomy', 
			__('Simple Taxonomy Widget', 'simple-taxonomy'), 
			array(
				'classname' => 'st-widget', 
				'description' => __('A advanced tag cloud or list for your custom taxonomy!', 'simple-taxonomy')
			)
		);
	}
	
	/**
	 * Check if taxonomy exist and return it, otherwise return default post tags.
	 *
	 * @param array $instance 
	 * @return string
	 * @author Amaury Balmer
	 */
	private function _get_current_taxonomy($instance) {
		if ( !empty($instance['taxonomy']) && taxonomy_exists($instance['taxonomy']) )
			return $instance['taxonomy'];
		
		return 'post_tag';
	}
	
	/**
	 * Client side widget render
	 *
	 * @param array $args 
	 * @param array $instance 
	 * @return void
	 * @author Amaury Balmer
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$current_taxonomy = $this->_get_current_taxonomy($instance);
		
		// Build or not the name of the widget
		if ( !empty($instance['title']) ) {
			$title = $instance['title'];
		} else {
			if ( 'post_tag' == $current_taxonomy ) {
				$title = __('Tags', 'simple-taxonomy');
			} else {
				$tax = get_taxonomy($current_taxonomy);
				$title = $tax->labels->name;
			}
		}
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		
		if ( $instance['type'] == "cloud" ) {
			
			echo '<div>'  . "\n";
				wp_tag_cloud( apply_filters('simpletaxo_widget_tag_cloud_args', array('taxonomy' => $current_taxonomy, 'number' => $instance['number'], 'order' => $instance['cloudorder'])) );
			echo '</div>' . "\n";
			
		} else {
			
			$terms = get_terms( $current_taxonomy, 'number='.$instance['number'].'&order='.$instance['listorder'] );
			if ( $terms == false ) {
				echo '<p>'.__('No terms actually for this taxonomy.', 'simple-taxonomy').'</p>';
			} else {
				echo '<ul class="simpletaxonomy-list">' . "\n";
				foreach ( (array) $terms as $term) {
					echo '<li><a href="'.get_term_link( $term, $current_taxonomy ).'">'.esc_html($term->name).'</a>' . "\n";
					if ( $instance['showcount'] ) echo ' ('.$term->count.')';
					echo '</li>' . "\n";
				}
				echo '</ul>' . "\n";
			}
		
		}
		
		echo $after_widget;
	}
	
	/**
	 * Method for save widgets options
	 *
	 * @param string $new_instance 
	 * @param string $old_instance 
	 * @return void
	 * @author Amaury Balmer
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		// String
		foreach ( array('title', 'taxonomy', 'number', 'type', 'cloudorder', 'listorder') as $val ) {
			$instance[$val] = strip_tags( $new_instance[$val] );
		}
		
		// Checkbox
		$instance['showcount'] = ( isset($new_instance['showcount']) ) ? true : false;
		
		return $instance;
	}
	
	/**
	 * Control for widget admin
	 *
	 * @param array $instance 
	 * @return void
	 * @author Amaury Balmer
	 */
	public function form( $instance ) {
		$defaults = array(
			'title' 		=> __('Adv Tag Cloud', 'simple-taxonomy'),
			'type' 			=> 'cloud',
			'cloudorder' 	=> 'RAND',
			'listorder' 	=> 'ASC',
			'showcount' 	=> true,
			'number' 		=> 45,
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		
		$current_taxonomy = $this->_get_current_taxonomy($instance);
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Title", 'simple-taxonomy'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e("What to show", 'simple-taxonomy'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" class="widefat">
				<?php
				foreach ( get_taxonomies() as $taxonomy ) {
					$tax = get_taxonomy($taxonomy);
					if ( !$tax->show_tagcloud || empty($tax->labels->name) )
						continue;
					
					echo '<option '.selected( $current_taxonomy, $taxonomy, false ).' value="'.esc_attr($taxonomy).'">'.esc_html($tax->labels->name).'</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e("How to show it", 'simple-taxonomy'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat">
				<?php
				foreach( array( 'cloud' => __('Cloud', 'simple-taxonomy'), 'list' => __('List', 'simple-taxonomy') ) as $optval => $option ) {
					echo '<option '.selected( $instance['type'], $optval, false ).' value="'.esc_attr($optval).'">'.esc_html($option).'</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'cloudorder' ); ?>"><?php _e("Order for cloud", 'simple-taxonomy'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'cloudorder' ); ?>" name="<?php echo $this->get_field_name( 'cloudorder' ); ?>" class="widefat">
				<?php
				foreach( array('RAND' => __('Random', 'simple-taxonomy'), 'ASC' => __('Ascending', 'simple-taxonomy'), 'DESC' => __('Descending', 'simple-taxonomy')) as $optval => $option ) {
					echo '<option '.selected( $instance['cloudorder'], $optval, false ).' value="'.esc_attr($optval).'">'.esc_html($option).'</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'listorder' ); ?>"><?php _e("Order for list", 'simple-taxonomy'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'listorder' ); ?>" name="<?php echo $this->get_field_name( 'listorder' ); ?>" class="widefat">
				<?php
				foreach( array('ASC' => __('Ascending', 'simple-taxonomy'), 'DESC' => __('Descending', 'simple-taxonomy') ) as $optval => $option ) {
					echo '<option '.selected( $instance['listorder'], $optval, false ).' value="'.esc_attr($optval).'">'.esc_html($option).'</option>';
				}
				?>
			</select>
		</p>
	
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'showcount' ); ?>" name="<?php echo $this->get_field_name( 'showcount' ); ?>" <?php checked( $instance['showcount'], true ); ?> />
			<label for="<?php echo $this->get_field_id( 'showcount' ); ?>"><?php _e("Show post count in list ?", 'simple-taxonomy'); ?></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e("Number of terms to show", 'simple-taxonomy'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo (int) $instance['number']; ?>" class="widefat" />
		</p>
	<?php
	}
}
?>