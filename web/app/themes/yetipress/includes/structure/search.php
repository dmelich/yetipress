<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'genesis_search_text', 'imp_search_text' );
/**
 * Customize the search form input box text.
 *
 * See: http://www.briangardner.com/code/customize-search-form/
 *
 * @since 2.0.0
 */
function imp_search_text() {

	return esc_attr( __( 'Search Text Goes Here...', CHILD_THEME_TEXT_DOMAIN ) );

}

add_filter( 'genesis_search_button_text', 'imp_search_button_text' );
/**
 * Customize the search form input button text.
 *
 * See: http://www.briangardner.com/code/customize-search-form/
 *
 * @since 2.0.0
 */
function imp_search_button_text( $text ) {

	return esc_attr( __( 'Click Here...', CHILD_THEME_TEXT_DOMAIN ) );

}

// add_action( 'template_redirect', 'imp_redirect_single_search_result' );
/**
 * Redirect to the result itself, if only one search result is returned.
 *
 * See: http://www.developerdrive.com/2013/07/5-quick-and-easy-tricks-to-improve-your-wordpress-theme/
 *
 * @since 2.0.0
 */
function imp_redirect_single_search_result() {

	if( is_search() ) {
		global $wp_query;

		if( $wp_query->post_count === 1) {
			wp_safe_redirect( get_permalink( $wp_query->posts['0']->ID ) );
		}
	}

}

// add_action( 'pre_get_posts', 'imp_only_search_posts' );
/**
 * Limit searching to just posts, excluding pages and CPTs.
 *
 * See: http://www.mhsiung.com/2009/11/limit-wordpress-search-scope-to-blog-posts/
 *
 * @since 2.0.0
 */
function imp_only_search_posts( $query ) {

	if( is_admin() )
		return;

	if( $query->is_search ) {
		$query->set( 'post_type', 'post' );
	}

}
