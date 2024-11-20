<?php
if (!function_exists('add_shortcode')) {
    function add_shortcode() {}
}

if (!function_exists('do_action')) {
    function do_action() {}
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) {
        return $value;
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path, $plugin) {
        return "http://example.com/wp-content/plugins/{$path}";
    }
} 