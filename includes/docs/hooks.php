<?php
/**
 * Documentation for all hooks (actions and filters) provided by the Resume plugin.
 * 
 * @package Spenpo Resume
 * @since 1.0.0
 */

/**
 * Filter the final HTML output of the resume.
 * 
 * @since 1.0.0
 * 
 * @param string $html     The generated HTML output
 * @param array  $sections The resume sections data
 * @return string Modified HTML output
 * 
 * @example
 * // Add a wrapper div around the resume
 * add_filter('spcv_html_output', function($html, $sections) {
 *     return '<div class="my-custom-wrapper">' . $html . '</div>';
 * }, 10, 2);
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$html = apply_filters('spcv_html_output', $dom->saveHTML(), $sections);

/**
 * Action that fires before the resume is rendered.
 * 
 * @since 1.0.0
 * 
 * @param array $sections The resume sections data
 * 
 * @example
 * // Log resume rendering
 * add_action('spcv_before_render', function($sections) {
 *     error_log('Resume rendering started with ' . count($sections) . ' sections');
 * });
 */
do_action('spcv_before_render', $sections);

/**
 * Action that fires after the resume is rendered.
 * 
 * @since 1.0.0
 * 
 * @param string $html     The final HTML output
 * @param array  $sections The resume sections data
 * 
 * @example
 * // Cache the rendered resume
 * add_action('spcv_after_render', function($html, $sections) {
 *     wp_cache_set('resume_html', $html, 'spenpo_resume', 3600);
 * }, 10, 2);
 */
do_action('spcv_after_render', $html, $sections); 