<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//add_action( 'admin_enqueue_scripts', 'imp_load_admin_assets' );
/**
 * Enqueue admin CSS and JS files.
 *
 * @since 2.0.0
 */
function imp_load_admin_assets() {

	$stylesheet_dir        = get_stylesheet_directory_uri();
	$use_production_assets = genesis_get_option('imp_production_on');
	$use_production_assets = !empty($use_production_assets);

	$src = $use_production_assets ? '/css/admin.min.css' : '/css/admin.css';
	wp_enqueue_style( 'bfg-admin', $stylesheet_dir . $src, array(), null );

	$src = $use_production_assets ? '/js/admin.min.js' : '/js/admin.js';
	wp_enqueue_script( 'bfg-admin', $stylesheet_dir . $src, array('jquery'), null, true );

}

add_action( 'pre_ping', 'imp_disable_self_pings' );
/**
 * Prevent the child theme from being overwritten by a WordPress.org theme with the same name.
 *
 * See: http://wp-snippets.com/disable-self-trackbacks/
 *
 * @since 2.0.0
 */
function imp_disable_self_pings( &$links ) {

	foreach ( $links as $l => $link )
		if ( 0 === mb_strpos( $link, home_url() ) )
			unset($links[$l]);

}

/**
 * Change WP JPEG compression (WP default is 90%).
 *
 * See: http://wpmu.org/how-to-change-jpeg-compression-in-wordpress/
 *
 * @since 2.0.0
 */
add_filter( 'jpeg_quality', create_function( '', 'return 80;' ) );


add_filter( 'upload_mimes', 'imp_enable_svg_uploads', 10, 1 );
/**
 * Enabled SVG uploads. Note that this could be a security issue, see: https://bjornjohansen.no/svg-in-wordpress.
 *
 * @since 2.3.38
 */
function imp_enable_svg_uploads( $mimes ) {

	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;

}


/**
 * List available image sizes with width and height.
 *
 * See: http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
 *
 * @since 2.0.0
 */
function imp_get_image_sizes( $size = '' ) {

	global $_wp_additional_image_sizes;

	$sizes = array();

	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach( $get_intermediate_image_sizes as $_size ) {
		if( in_array( $_size, array('thumbnail', 'medium', 'large'), true ) ) {
			$sizes[$_size]['width']  = get_option( $_size . '_size_w' );
			$sizes[$_size]['height'] = get_option( $_size . '_size_h' );
			$sizes[$_size]['crop']   = (bool) get_option( $_size . '_crop' );
		} elseif ( isset( $_wp_additional_image_sizes[$_size] ) ) {
			$sizes[$_size] = array(
				'width'  => $_wp_additional_image_sizes[$_size]['width'],
				'height' => $_wp_additional_image_sizes[$_size]['height'],
				'crop'   => $_wp_additional_image_sizes[$_size]['crop'],
			);
		}
	}

	// Get only 1 size if found
	if( $size )
		return isset($sizes[$size]) ? $sizes[$size] : false;

	return $sizes;

}

/*
 * Downsize the original uploaded image if it's too large
 *
 * See: https://wordpress.stackexchange.com/questions/63707/automatically-replace-original-uploaded-image-with-large-image-size
 *
 * @since 2.0.0
 */
add_filter( 'wp_generate_attachment_metadata', 'imp_downsize_uploaded_image', 99 );
function imp_downsize_uploaded_image( $image_data ) {

	$max_image_size_name = 'large';

	// Abort if no max image
	if( !isset($image_data['sizes'][$max_image_size_name]) )
		return $image_data;

	// paths to the uploaded image and the max image
	$upload_dir              = wp_upload_dir();
	$uploaded_image_location = $upload_dir['basedir'] . '/' . $image_data['file'];
	$max_image_location      = $upload_dir['path'] . '/' . $image_data['sizes'][$max_image_size_name]['file'];

	// Delete original image
	unlink($uploaded_image_location);

	// Rename max image to original image
	rename( $max_image_location, $uploaded_image_location );

	// Update and return image metadata
	$image_data['width']  = $image_data['sizes'][$max_image_size_name]['width'];
	$image_data['height'] = $image_data['sizes'][$max_image_size_name]['height'];
	unset($image_data['sizes'][$max_image_size_name]);

	return $image_data;

}

/*
 * Disable pingbacks
 *
 * See: http://wptavern.com/how-to-prevent-wordpress-from-participating-in-pingback-denial-of-service-attacks
 *
 * Still having pingback/trackback issues? This post might help: https://wordpress.org/support/topic/disabling-pingbackstrackbacks-on-pages#post-4046256
 *
 * @since 2.0.0
 */
add_filter( 'xmlrpc_methods', 'imp_remove_xmlrpc_pingback_ping' );
function imp_remove_xmlrpc_pingback_ping( $methods ) {

	unset($methods['pingback.ping']);

	return $methods;

}

/*
 * Disable XML-RPC
 *
 * See: https://wordpress.stackexchange.com/questions/78780/xmlrpc-enabled-filter-not-called
 *
 * @since 2.0.0
 */
if( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) exit;

/*
 * Automatically remove readme.html (and optionally xmlrpc.php) after a WP core update
 *
 * @since 2.0.0.
 */
add_action( '_core_updated_successfully', 'imp_remove_files_on_upgrade' );
function imp_remove_files_on_upgrade() {

	if( file_exists(ABSPATH . 'readme.html') )
		unlink(ABSPATH . 'readme.html');

	if( file_exists(ABSPATH . 'xmlrpc.php') )
		unlink(ABSPATH . 'xmlrpc.php');

}
