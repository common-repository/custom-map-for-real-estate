<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<?php
// Register Custom Post Type
function map4re_cpt_func()
{

	$labels = array(
		'name'                  => _x('Map For RE', 'Post Type General Name', 'map4re'),
		'singular_name'         => _x('Map For RE', 'Post Type Singular Name', 'map4re'),
		'menu_name'             => __('Map For RE', 'map4re'),
		'name_admin_bar'        => __('Map For RE', 'map4re'),
		'archives'              => __('Item Archives', 'map4re'),
		'parent_item_colon'     => __('Parent Item:', 'map4re'),
		'all_items'             => __('All Items', 'map4re'),
		'add_new_item'          => __('Add New Item', 'map4re'),
		'add_new'               => __('Add New', 'map4re'),
		'new_item'              => __('New Item', 'map4re'),
		'edit_item'             => __('Edit Item', 'map4re'),
		'update_item'           => __('Update Item', 'map4re'),
		'view_item'             => __('View Item', 'map4re'),
		'search_items'          => __('Search Item', 'map4re'),
		'not_found'             => __('Not found', 'map4re'),
		'not_found_in_trash'    => __('Not found in Trash', 'map4re'),
		'featured_image'        => __('Featured Image', 'map4re'),
		'set_featured_image'    => __('Set featured image', 'map4re'),
		'remove_featured_image' => __('Remove featured image', 'map4re'),
		'use_featured_image'    => __('Use as featured image', 'map4re'),
		'insert_into_item'      => __('Insert into item', 'map4re'),
		'uploaded_to_this_item' => __('Uploaded to this item', 'map4re'),
		'items_list'            => __('Items list', 'map4re'),
		'items_list_navigation' => __('Items list navigation', 'map4re'),
		'filter_items_list'     => __('Filter items list', 'map4re'),
	);
	$args = array(
		'label'                 => __('Map For RE', 'map4re'),
		'labels'                => $labels,
		'supports'              => array('title'),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => plugin_dir_url(dirname(__FILE__)) . '/images/plugin_icon.png',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => false,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type('all_map', $args);
}
add_action('init', 'map4re_cpt_func', 0);

//Add admin inline style
function map4re_admin_css()
{
	global $post_type;
	$post_types = array(
		'all_map'
	);
	if (in_array($post_type, $post_types))
		echo '<style type="text/css">#post-preview, #view-post-btn,#message.notice-success a{display: none;}</style>';
}
add_action('admin_head-post-new.php', 'map4re_admin_css');
add_action('admin_head-post.php', 'map4re_admin_css');

//Add row to admin column
add_filter('page_row_actions', 'map4re_row_actions', 10, 2);
add_filter('post_row_actions', 'map4re_row_actions', 10, 2);
function map4re_row_actions($actions, $post)
{
	if ($post->post_type == 'all_map') {
		unset($actions['inline hide-if-no-js']);
		unset($actions['view']);
	}
	return $actions;
}

//Add new column
function map4re_cpt_admin_columns($columns)
{
	$columns = array(
		'cb' 			=> '<input type="checkbox" />',
		'title' 		=> __('Title', 'map4re'),
		'shortcode' 	=> __('Shortcode', 'map4re'),
		'date' 			=> __('Date', 'map4re'),
	);
	return $columns;
}
add_filter('manage_edit-all_map_columns', 'map4re_cpt_admin_columns');

//Add content to colum
function map4re_manage_all_map_columns($column, $post_id)
{
	global $post;
	switch ($column) {
		case 'shortcode':
			echo '[map4re id="' . $post->ID . '"]';
			break;
		default:
			break;
	}
}
add_action('manage_all_map_posts_custom_column', 'map4re_manage_all_map_columns', 10, 2);
