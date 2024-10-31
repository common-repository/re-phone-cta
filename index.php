<?php
/*
Plugin Name: RE Phone CTA
Description: Create a beautiful, elegant phone ring CTA that stay fixed on your screen. Whether you want it open a popup or make a call or just want to link to another page, it will make you sastified.
Version:     1.1.0
Author:      Team Blueotter
Author URI:  https://teamblueotter.com
License:     GPL2
Text Domain: cta
Domain Path: /languages
RE Phone CTA is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
*/

defined('ABSPATH') or die('No script kiddies please!');

define('cta_VER', '1.1.0');
define('cta_DEV_MOD', false);

load_textdomain('cta',  dirname(__FILE__) .  '/languages/' . get_locale() . '.mo');


// Register Custom Post Type
function cta_cpt_func()
{

	$labels = array(
		'name'                  => _x('RE Phone CTA', 'Post Type General Name', 'cta'),
		'singular_name'         => _x('RE Phone CTA', 'Post Type Singular Name', 'cta'),
		'menu_name'             => __('RE Phone CTA', 'cta'),
		'name_admin_bar'        => __('RE Phone CTA', 'cta'),
		'archives'              => __('Item Archives', 'cta'),
		'parent_item_colon'     => __('Parent Item:', 'cta'),
		'all_items'             => __('All Items', 'cta'),
		'add_new_item'          => __('Add New Item', 'cta'),
		'add_new'               => __('Add New', 'cta'),
		'new_item'              => __('New Item', 'cta'),
		'edit_item'             => __('Edit Item', 'cta'),
		'update_item'           => __('Update Item', 'cta'),
		'view_item'             => __('View Item', 'cta'),
		'search_items'          => __('Search Item', 'cta'),
		'not_found'             => __('Not found', 'cta'),
		'not_found_in_trash'    => __('Not found in Trash', 'cta'),
		'featured_image'        => __('Featured Image', 'cta'),
		'set_featured_image'    => __('Set featured image', 'cta'),
		'remove_featured_image' => __('Remove featured image', 'cta'),
		'use_featured_image'    => __('Use as featured image', 'cta'),
		'insert_into_item'      => __('Insert into item', 'cta'),
		'uploaded_to_this_item' => __('Uploaded to this item', 'cta'),
		'items_list'            => __('Items list', 'cta'),
		'items_list_navigation' => __('Items list navigation', 'cta'),
		'filter_items_list'     => __('Filter items list', 'cta'),
	);
	$args = array(
		'label'                 => __('RE Phone CTA', 'cta'),
		'labels'                => $labels,
		'supports'              => array('title'),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => plugin_dir_url(__FILE__) . 'images/icon.png',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => false,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type('re-phone-cta', $args);
}
add_action('init', 'cta_cpt_func', 0);

//Add admin inline style
function cta_admin_css()
{
	global $post_type;
	$post_types = array(
		're-phone-cta'
	);
	if (in_array($post_type, $post_types))
		echo '<style type="text/css">#post-preview, #view-post-btn,#message.notice-success a{display: none;}</style>';
}
add_action('admin_head-post-new.php', 'cta_admin_css');
add_action('admin_head-post.php', 'cta_admin_css');

//Add row to admin column
add_filter('page_row_actions', 'cta_row_actions', 10, 2);
add_filter('post_row_actions', 'cta_row_actions', 10, 2);
function cta_row_actions($actions, $post)
{
	if ($post->post_type == 're-phone-cta') {
		unset($actions['inline hide-if-no-js']);
		unset($actions['view']);
	}
	return $actions;
}

//Add new column
function cta_admin_columns($columns)
{
	$columns = array(
		'cb' 			=> '<input type="checkbox" />',
		'title' 		=> __('Title', 'cta'),
		'shortcode' 	=> __('Shortcode', 'cta'),
		'date' 			=> __('Date', 'cta'),
	);
	return $columns;
}
add_filter('manage_edit_re_phone_cta_columns', 'cta_admin_columns');

//Add content to column
function cta_admin_shortcode_columns($column, $post_id)
{
	global $post;
	switch ($column) {
		case 'shortcode':
			echo '[cta id="' . $post->ID . '"]';
			break;
		default:
			break;
	}
}
add_action('manage_re_phone_cta_posts_custom_column', 'cta_admin_shortcode_columns', 10, 2);


//metabox
function cta_meta_box()
{
	//post type
	$screens = array('re-phone-cta');

	foreach ($screens as $screen) {
		add_meta_box(
			'cta-metabox',
			__('CTA setting', 'cta'),
			'cta_meta_box_callback',
			$screen,
			'normal',
			'high'
		);
		add_meta_box(
			'cta-shortcode',
			__('Shortcode', 'cta'),
			'cta_shortcode_callback',
			$screen,
			'side',
			'high'
		);
	}
}
add_action('add_meta_boxes', 'cta_meta_box');

function cta_wp_default_editor()
{
	return "tinymce";
}

function cta_meta_box_callback($post)
{
	add_filter('wp_default_editor', 'cta_wp_default_editor');
	//add none field
	wp_nonce_field('map4re_save_meta_box_data', 'map4re_meta_box_nonce');

	$data_post = get_post_meta($post->ID, 'hotspot_content', true);

	if (!$data_post) {
		$data_post = maybe_unserialize($post->post_content);
	}

	$phone = (isset($data_post['phone'])) ? $data_post['phone'] : '';
	$image = (isset($data_post['image'])) ? $data_post['image'] : '';
	$position = (isset($data_post['position'])) ? $data_post['position'] : '';
	$color = (isset($data_post['color'])) ? $data_post['color'] : '';
	$text_color = (isset($data_post['text_color'])) ? $data_post['text_color'] : '';
	$font = (isset($data_post['font'])) ? $data_post['font'] : '';
	?>
<div>
	<div class="cta_row">
		<label><?php _e('Phone number', 'cta') ?></label><br>
		<input type="tel" name="phone" value="<?php echo esc_html($phone) ?>" placeholder="0123456789" pattern="[0-9]{10}" required />
	</div>
	<div class="cta_row">
		<label><?php _e('Image', 'cta') ?></label>
		<input type="hidden" name="image" class="maps_images" id="maps_images" value="<?php echo $image; ?>" />
		<div id="image_upload" class="<?= $image ? 'has-image' : ''; ?>">
			<?php if ($image) : ?>
			<img src="<?php echo esc_url($image); ?>">
			<span id="remove-image">&times;</span>
			<?php endif; ?>
			<input type="button" id="meta-image-button" class="button" value="<?php _e('Upload Image', 'map4re') ?>" />
		</div>
	</div>
	<div class="cta_row">
		<label><?php _e('Color', 'cta') ?></label>
		<input type="text" name="color" value="<?= empty($color) ? '#81d742' : sanitize_hex_color($color); ?>" id="color_picker" />
	</div>
	<div class="cta_row">
		<label><?php _e('Text color', 'cta') ?></label>
		<input type="text" name="text_color" value="<?= empty($text_color) ? '#fff' : sanitize_hex_color($text_color); ?>" id="text_color_picker" />
	</div>
	<div class="cta_row">
		<label><?php _e('Font family', 'cta') ?></label>
		<input id="select_font" type="text" value="<?php $_font = explode(",", $font);
														echo $_font[0]; ?>" placeholder="<?php _e('Select a font', 'cta') ?>">
		<input type="hidden" name="font" value="<?php echo $font; ?>">
	</div>
	<div class="cta_row">
		<label><?php _e('Position', 'cta') ?></label><br>
		<div class="option_position">
			<label><input type="radio" name="position" value="top_left" <?= ($position == 'top_left' ? 'checked="checked"' : '') ?>><?php _e('Top Left', 'cta') ?></label>
			<label><input type="radio" name="position" value="top_center" <?= ($position == 'top_center' ? 'checked="checked"' : '') ?>><?php _e('Top Center', 'cta') ?></label>
			<label><input type="radio" name="position" value="top_right" <?= ($position == 'top_right' ? 'checked="checked"' : '') ?>><?php _e('Top Right', 'cta') ?></label>
			<label><input type="radio" name="position" value="center_left" <?= ($position == 'center_left' ? 'checked="checked"' : '') ?>><?php _e('Center Left', 'cta') ?></label>
			<label for=""></label>
			<label><input type="radio" name="position" value="center_right" <?= ($position == 'center_right' ? 'checked="checked"' : '') ?>><?php _e('Center Right', 'cta') ?></label>
			<label><input type="radio" name="position" value="bottom_left" <?= ((($position == 'bottom_left') || empty($position)) ? 'checked="checked"' : '') ?>><?php _e('Bottom Left', 'cta') ?></label>
			<label><input type="radio" name="position" value="bottom_center" <?= ($position == 'bottom_center' ? 'checked="checked"' : '') ?>><?php _e('Bottom Center', 'cta') ?></label>
			<label><input type="radio" name="position" value="bottom_right" <?= ($position == 'bottom_right' ? 'checked="checked"' : '') ?>><?php _e('Bottom Right', 'cta') ?></label>
		</div>
	</div>
</div>
<?php
}
function cta_shortcode_callback($post)
{
	if (get_post_status($post->ID) == "publish") :
		?>
<span><?php _e('Copy shortcode to view', 'cta') ?></span>
<div class="shortcodemap">
	<input readonly="readonly" value='[cta id="<?= $post->ID ?>"]' id="copy_shortcode" />
	<button class="button" id="btn_shortcode"><?php _e('Copy', 'cta'); ?></button>
</div>
<?php else : ?>
<span><?php _e('Publish to view shortcode', 'cta') ?></span>
<?php
	endif;
}
function cta_save_meta_box_data($post_id)
{

	if (!isset($_POST['map4re_meta_box_nonce'])) {
		return;
	}
	if (!wp_verify_nonce($_POST['map4re_meta_box_nonce'], 'map4re_save_meta_box_data')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (isset($_POST['post_type']) && 're-phone-cta' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}

	$position =  sanitize_text_field($_POST['position']);
	$phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
	$image = esc_url($_POST['image']);
	$color = sanitize_hex_color($_POST['color']);
	$text_color = sanitize_hex_color($_POST['text_color']);
	$font = sanitize_text_field($_POST['font']);

	$data_post = array(
		'position'			=>	$position,
		'phone'				=>	$phone,
		'image'				=>	$image,
		'color'				=>	$color,
		'text_color'		=>	$text_color,
		'font'				=>	$font,
	);
	update_post_meta($post_id, 'hotspot_content', $data_post);
}
add_action('save_post', 'cta_save_meta_box_data');

/*Add admin script*/
function cta_admin_script()
{
	global $typenow;
	if ($typenow == 're-phone-cta') {
		wp_enqueue_media();

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_style('wp-color-picker');

		wp_register_script('cta_script', plugin_dir_url(__FILE__) . 'admin/js/cta_script.js', array('jquery', 'wp-color-picker'), cta_VER, true);
		wp_register_script('fontselect', plugin_dir_url(__FILE__) . 'admin/js/jquery.fontselect.js', cta_VER, true);
		wp_localize_script(
			'cta_script',
			'meta_image',
			array(
				'title' 		=> __('Select image', 'cta'),
				'button' 		=> __('Select', 'cta'),
				'site_url'		=>	home_url(),
				'ajaxurl'		=>	admin_url('admin-ajax.php'),
			)
		);
		wp_enqueue_script('cta_script');
		wp_enqueue_script('fontselect');
	}
}
add_action('admin_enqueue_scripts', 'cta_admin_script');

/*Add admin style*/
function cta_admin_styles()
{
	global $typenow;
	if ($typenow == 're-phone-cta') {
		wp_enqueue_style('cta_style', plugin_dir_url(__FILE__) . 'admin/css/cta_style.css', array(), cta_VER, 'all');
		wp_enqueue_style('fontselect', plugin_dir_url(__FILE__) . 'admin/css/jquery.fontselect.css', array(), cta_VER, 'all');
	}
}
add_action('admin_print_styles', 'cta_admin_styles');

/*Add frontend scripts*/
function cta_frontend_scripts()
{
	wp_enqueue_style('front-end-style', plugin_dir_url(__FILE__) . 'frontend/css/cta_style.css', array(), cta_VER, 'all');
	wp_enqueue_script('front-end-script', plugin_dir_url(__FILE__) . 'frontend/js/cta_script.js', array('jquery'),  cta_VER, 'all');
	wp_enqueue_script('front-end-fontselect', plugin_dir_url(__FILE__) . 'admin/js/jquery.fontselect.js', cta_VER, true);
}
add_action('wp_enqueue_scripts', 'cta_frontend_scripts');

function cta_shortcode_func($atts)
{

	$atts = shortcode_atts(array(
		'id' => '',
	), $atts, 'cta');

	$idPost =  intval($atts['id']);

	if (get_post_status($idPost) != "publish") return;

	$data_post = get_post_meta($idPost, 'hotspot_content', true);

	if (!$data_post) {
		$data_post = maybe_unserialize(get_post_field('post_content', $idPost));
	}


	$phone = (isset($data_post['phone'])) ? $data_post['phone'] : '';
	$image = (isset($data_post['image'])) ? $data_post['image'] : '';
	$position = (isset($data_post['position'])) ? $data_post['position'] : '';
	$color = (isset($data_post['color'])) ? $data_post['color'] : '';
	$text_color = (isset($data_post['text_color'])) ? $data_post['text_color'] : '';
	$font = (isset($data_post['font'])) ? $data_post['font'] : '';

	ob_start();
	if ($data_post) :
		?>
<div class="cta-box <?php echo esc_html($position); ?>">
	<div class="cta-animation"></div>
	<div class="cta-image" style="background-color:<?php echo sanitize_hex_color($color); ?>">
		<?php if (!empty($image)) : ?>
		<img src="<?php echo esc_url($image); ?>">
		<?php else : ?>
		<img src="<?php echo plugin_dir_url(__FILE__) . 'images/phone-ring.png'; ?>">
		<?php endif; ?>
	</div>
	<div class="cat-phone" style="background-color:<?php echo $color; ?>; ">
		<a href="tel: <?php echo esc_html($phone); ?>" data-css="<?php echo $font; ?>" style="color:<?php echo sanitize_hex_color($text_color); ?>"> <?php echo esc_html($phone); ?></a></div>
</div>
<?php
	endif;
	return ob_get_clean();
}
add_shortcode('cta', 'cta_shortcode_func');
