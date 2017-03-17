<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'genesis_footer_output', 'imp_footer_creds_text' );
/**
 * Custom footer 'creds' text.
 *
 * @since 2.0.0
 */
function imp_footer_creds_text() {

	 return '<p>&copy ' . date("Y") . ' Impressa</p>';

}
