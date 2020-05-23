<?php

require_once dirname( __FILE__ ) . '/api.php';

class SuzuriForWpWidget extends WP_Widget{
	const WIDGET_ID = 'suzuri_for_wp_widget';
	const PLUGIN_DB_PREFIX = 'suzuri-for-wp_';

	function SuzuriForWpWidget() {
		parent::__construct(
			false, 'SUZURI for WP',
			array( 'description' => 'SUZURIの商品リストを表示します') 
		);
	}

	function widget( $args, $instance ) {
		$user_name = get_option(self::PLUGIN_DB_PREFIX."_user_name");
		$api_key = get_option(self::PLUGIN_DB_PREFIX."_api_key");
		$limit = get_option(self::PLUGIN_DB_PREFIX."_limit");
    $product_type = get_option(self::PLUGIN_DB_PREFIX . "_product_type");
    $choice_id = get_option(self::PLUGIN_DB_PREFIX . "_choice_id");

		if ($user_name && $api_key && $product_type) {
			$params = array('limit' => $limit);
			$suzuri = new SuzuriForWpApi($api_key, $user_name);
			if($product_type == 'newer') {
				$products = $suzuri->get_products($params);
			} else {
				if($choice_id) {
					$products = $suzuri->get_choice_products($choice_id, $params);
				} else {
					$products = null;
				}
				
			}
			
			if($products) {
				echo $args['before_widget'];

				if ( !empty( $instance['title'] ) ) {
					$title = $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
					echo $title;
				}
				echo '<ul>';
				foreach($products['products'] as $p) {
					echo '<li><a href="' . $p['sampleUrl'] . '" target="_blank">';
					echo '<h4>' . $p['title'] . '</h4>';
					echo '<img src="' . $p['sampleImageUrl'] . '" alt="' . $p['title'] . ' sample image">';
					echo '</a></li>';
				}
				echo $args['after_widget'];
			}
		}
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'タイトル:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = (!empty( $new_instance['title'])) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
	
}

function sfw_register_widget() {
	register_widget('SuzuriForWpWidget');
}

add_action('widgets_init',  'sfw_register_widget');