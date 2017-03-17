<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Cleanup <head>
 *
 * @since 2.0.0
 */
remove_action( 'wp_head', 'rsd_link' );									// RSD link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );				// Parent rel link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );				// Start post rel link
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );	// Adjacent post rel link
remove_action( 'wp_head', 'wp_generator' );								// WP Version
remove_action( 'wp_head', 'wlwmanifest_link');							// WLW Manifest
// remove_action( 'wp_head', 'feed_links', 2 ); 						// Remove feed links
remove_action( 'wp_head', 'feed_links_extra', 3 ); 						// Remove comment feed links
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );				// Remove shortlink

// Remove WP-API <head> material
// See: https://wordpress.stackexchange.com/questions/211467/remove-json-api-links-in-header-html
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );

/*
 * Set a Content-Security-Policy header to help prevent XSS attacks
 *
 * See: https://www.smashingmagazine.com/2016/09/content-security-policy-your-future-best-friend/http://html5boilerplate.com/
 * See: http://githubengineering.com/githubs-csp-journey/
 *
 * @since 2.0.0
 */
// add_action(  'wp', 'imp_content_security_policy' );
function imp_content_security_policy() {

	if( is_admin() )
		return;

	ob_start();
	?>
	default-src 'none';
	base-uri 'self';
	block-all-mixed-content;
	font-src 'self' fonts.gstatic.com;
	form-action 'self';
	frame-ancestors 'none';
	img-src 'self';
	script-src 'self' cdnjs.cloudflare.com cdn.polyfill.io;
	style-src 'self' fonts.googleapis.com;
	<?php
	$csp = ob_get_clean();

	$csp = str_replace( "\n", ' ', $csp );

	header( 'Content-Security-Policy: ' . $csp );

}

remove_action( 'genesis_doctype', 'genesis_do_doctype' );
add_action( 'genesis_doctype', 'imp_do_doctype' );
/**
 * Overrides the default Genesis doctype with IE and JS identifier classes.
 *
 * See: http://html5boilerplate.com/
 *
 * @since 2.0.0
 */
function imp_do_doctype() {

?>
<!DOCTYPE html>
<html class="<?php echo is_admin_bar_showing() ? 'admin-bar-showing' : ''; ?>" <?php language_attributes( 'html' ); ?>>
<head <?php echo genesis_attr( 'head' ); ?>>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php

}

add_filter( 'wp_resource_hints', 'imp_resource_hints', 10, 2 );
/**
 * Prefetch the DNS for external resource domains. Better browser support than preconnect.
 *
 * See: https://www.igvita.com/2015/08/17/eliminating-roundtrips-with-preconnect/
 *
 * @since 2.0.0
 */
function imp_resource_hints( $hints, $relation_type ) {

	if( 'dns-prefetch' === $relation_type ) {
		// $hints[] = '//cdn.polyfill.io';
		// $hints[] = '//cdnjs.cloudflare.com';
		// $hints[] = '//fonts.googleapis.com';
	}

	return $hints;

}

remove_action( 'genesis_meta', 'genesis_load_stylesheet' );
remove_action( 'wp_enqueue_scripts', 'genesis_register_scripts' );
add_action( 'wp_enqueue_scripts', 'imp_load_assets' );
/**
 * Overrides the default Genesis stylesheet with child theme specific CSS and JS.
 *
 * Only load these styles on the front-end.
 *
 * @since 2.0.0
 */
function imp_load_assets() {

	$use_production_assets = genesis_get_option('imp_production_on');
	$use_production_assets = !empty($use_production_assets);

	$assets_version = genesis_get_option('imp_assets_version');
	$assets_version = !empty($assets_version) ? absint($assets_version) : null;

	$stylesheet_dir = get_stylesheet_directory_uri();

	// Main theme stylesheet
	$src = $use_production_assets ? '/css/style.min.css' : '/css/style.css';
	wp_enqueue_style( 'bfg', $stylesheet_dir . $src, array(), $assets_version );

	// Google Fonts
	// Consider async loading: https://github.com/typekit/webfontloader
 	// wp_enqueue_style(
 	// 	'google-fonts',
 	// 	'//fonts.googleapis.com/css?family=Open+Sans:300,400,700%7CLato',		// Open Sans (light, normal, and bold) and Lato regular, for example
 	// 	array(),
 	// 	null
 	// );

 	// Register polyfill.io with default options, as an alternative to jQuery
	$src = $use_production_assets ? '//cdn.polyfill.io/v2/polyfill.min.js' : 'https://cdn.polyfill.io/v2/polyfill.js';
	wp_register_script( 'polyfill', $src, array(), null, true );

	// Register some useful libraries from Cloudflare's CDN
	wp_deregister_script( 'jquery' );
	$src = $use_production_assets ? '//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js' : '//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.js';
	wp_register_script( 'jquery', $src, array(), null, true );

	$src = $use_production_assets ? '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js' : '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.js';
	wp_register_script( 'js-cookie', $src, array(), null, true );

	// Main vendor script file (in footer)
	$src = $use_production_assets ? '/js/vendors.min.js' : '/js/vendors.js';
	wp_enqueue_script( 'imp_vendors', $stylesheet_dir . $src, array('jquery'), $assets_version, true );

	// Main script file (in footer)
	$src = $use_production_assets ? '/js/custom.min.js' : '/js/custom.js';
	wp_enqueue_script( 'imp_custom', $stylesheet_dir . $src, array('jquery'), $assets_version, true );

}

// add_filter('script_loader_tag', 'imp_script_loader_tags', 10, 2);
/**
 * Add a defer attribute to the designated <script> tags.
 *
 * See: http://calendar.perfplanet.com/2016/prefer-defer-over-async/
 *
 * @since 2.0.0
 */
function imp_script_loader_tags( $tag, $handle ) {

	switch( $handle ) {
		case 'bfg':
			return str_replace( ' src', ' defer src', $tag );
	}

	return $tag;

}

add_filter( 'genesis_attr_body', 'imp_ajax_url_attribute' );
/**
 * Add the AJAX URL as a `data-*` attribute on `<body>`, instead of an inline script, for better CSP compatibility.
 *
 * @since 2.0.0
 */
function imp_ajax_url_attribute( $atts ) {

	$atts['data-ajax_url'] = admin_url( 'admin-ajax.php' );

	return $atts;

}

add_filter( 'genesis_pre_load_favicon', 'imp_pre_load_favicon' );
/**
 * Simple favicon override to specify your favicon's location.
 *
 * @since 2.0.0
 */
function imp_pre_load_favicon() {

	return get_stylesheet_directory_uri() . '/images/favicon.ico';

}

remove_action( 'wp_head', 'genesis_load_favicon' );
add_action( 'wp_head', 'imp_load_favicons' );
/**
 * Show the best favicon, within reason.
 *
 * See: http://www.jonathantneal.com/blog/understand-the-favicon/
 *
 * @since 2.0.0
 */
function imp_load_favicons() {

	$stylesheet_dir     = get_stylesheet_directory_uri();
	$favicon_path       = $stylesheet_dir . '/images/favicons';
	$favicon_build_path = $stylesheet_dir . '/images/favicons';

	// Set to false to disable, otherwise set to a hex color
	$color = false;

	// Use a 192px X 192px PNG for the homescreen for Chrome on Android
	echo '<link rel="icon" type="image/png" href="' . $favicon_build_path . '/favicon-192.png" sizes="192x192">';

	// Use a 180px X 180px PNG for the latest iOS devices, also setup app styles
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $favicon_build_path . '/favicon-180.png">';

	// Give IE <= 9 the old favicon.ico (16px X 16px)
	echo '<!--[if IE]><link rel="shortcut icon" href="' . $favicon_path . '/favicon.ico"><![endif]-->';

	// Use a 144px X 144px PNG for Windows tablets
	echo '<meta name="msapplication-TileImage" content="' . $favicon_build_path . '/favicon-144.png">';

	if( false !== $color ) {
		// Windows icon background color
		echo '<meta name="msapplication-TileColor" content="' . $color . '">';

		// Chrome for Android taskbar color
		echo '<meta name="theme-color" content="' . $color . '">';

		// Safari 9 pinned tab color
		echo '<link rel="mask-icon" href="' . $favicon_build_path . '/favicon.svg" color="' . $color . '">';
	}

}

add_filter( 'body_class', 'imp_no_js_body_class' );
/*
 * Add a no-js class to the <body> tag
 *
 * @since 2.0.0
 */
function imp_no_js_body_class( $classes ) {

	$classes[] = 'no-js';

	return $classes;

}

/*
 * Remove the header
 *
 * @since 2.0.9
 */
// remove_action( 'genesis_header', 'genesis_do_header' );

/*
 * Remove the site title and/or description
 *
 * @since 2.0.9
 */
// remove_action( 'genesis_site_title', 'genesis_seo_site_title' );
// remove_action( 'genesis_site_description', 'genesis_seo_site_description' );
