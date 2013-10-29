<?php
/*
Plugin Name: WP kampa!
Version: 0.1
Plugin URI: http://www.iex3.info/wordpress-plugin
Description: The WordPress implementation of the Kampa!.
Author: kimikato
Author URI: http://www.iex3.info/
License: GPL2
*/
/*
 Copyright (c) 2013 Kiminori Kato  (email : kimi.k@iex3.info)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
@define("KAMPA_PROXY_API_URL", "http://kampa-proxy-api.herokuapp.com/kampa/");

add_action(
 	'widgets_init',
 	create_function('', 'return register_widget("WpKampa");')
);

class WpKampa extends WP_Widget {

	const DOMAIN = 'wp-kampa_widget';

	function WpKampa() {
		if (function_exists("load_plugin_textdomain"))
			load_plugin_textdomain(self::DOMAIN, false, basename(dirname( __FILE__ )).'/language');

		$widget_ops = array('classname' => 'WpKampa', 'description' => __('Show items with has been registered with Kampa!', self::DOMAIN));
		parent::WP_Widget(false, $name = 'Kampa!', $widget_ops);

		// jQuery利用
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery.jsonp', plugin_dir_url( __FILE__ ).'js/jquery.jsonp.js', array('jquery'), false, true);
		wp_enqueue_script('jquery.jcontent', plugin_dir_url( __FILE__ ).'js/jquery.jcontent.0.8.min.js', array('jquery'), false, true);
		wp_enqueue_script('jquery.easing', plugin_dir_url( __FILE__ ).'js/jquery.easing.1.3.js', array('jquery'), false, true);
		wp_enqueue_style('jcontent', plugin_dir_url( __FILE__ ).'css/jcontent.css');

		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-progressbar');
		wp_enqueue_script('wp-kampa', plugin_dir_url( __FILE__ ).'js/wp-kampa.js', array('jquery'), false, true);
		wp_enqueue_style('wp-kampa', plugin_dir_url( __FILE__ ).'css/wp-kampa.css');
	}

	public function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$kampa_key = apply_filters('widget_body', $instance['kampa_key']);

		// JavaScriptへ渡すデータ
		wp_localize_script('wp-kampa', 'kampa_key_json', array('kampa_key' => $kampa_key));
		?>
		<aside id="kampa" class="widget widget_kampa">
			<?php if ($title) ?>
			<?php echo $before_title . $title . $after_title; ?>
			<div class="slides"></div>
		</aside>
		<?php
	}

	public function form($param) {
		$title = (isset($param['title']) && $param['title']) ? $param['title'] : '';
		$title_id = $this->get_field_id('title');
		$title_name = $this->get_field_name('title');

		$kampa_key = (isset($param['kampa_key']) && $param['kampa_key']) ? $param['kampa_key'] : '';
		$kampa_key_id = $this->get_field_id('kampa_key');
		$kampa_key_name = $this->get_field_name('kampa_key');
		?>
		<p>
			<label for="<?php echo $title_id; ?>"><?php _e('タイトル:'); ?></label>
			<input class="widefat" id="<?php echo $title_id; ?>" name="<?php echo $title_name; ?>" type="text" value="<?php echo $title;?>" />
			<br />
			<label for="<?php echo $kampa_key_id; ?>"><?php _e('Kampa! Consumer Key:'); ?></label>
			<input class="widefat" rows="16" colls="20" id="<?php echo $kampa_key_id; ?>" name="<?php echo $kampa_key_name; ?>" type="text" value="<?php echo $kampa_key; ?>">
		</p>
		<?php
	}

	public function update($new_instance, $old_instance) {
		$instance = $_old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['kampa_key'] = strip_tags($new_instance['kampa_key']);
		return $new_instance;
	}

}




?>
