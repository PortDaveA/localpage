<?php

/*
Plugin Name: Local Page
Description: This plugin allows you to set a specific front page for viewers from different countries.
Author: GitHub Copilot
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

function get_visitor_country_code() {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $geo_info = file_get_contents('http://freegeoip.net/json/' . $ip_address);
    $geo_info = json_decode($geo_info);

    return $geo_info->country_code;
}

function country_specific_front_page_settings() {
    $countries = ['US', 'CA', 'GB', 'AU', 'IN']; // Add more country codes as needed

    add_settings_section(
        'country_specific_front_page_section',
        'Country Specific Front Page Settings',
        'country_specific_front_page_section_callback',
        'reading'
    );

    foreach ($countries as $country) {
        add_settings_field(
            $country . '_page',
            'Page for ' . $country . ' viewers',
            'country_page_callback',
            'reading',
            'country_specific_front_page_section',
            ['country' => $country]
        );

        register_setting('reading', $country . '_page');
    }
}

add_action('admin_init', 'country_specific_front_page_settings');

function country_specific_front_page_section_callback() {
    echo 'Select the page for each country.';
}

function country_page_callback($args) {
    $pages = get_pages();
    $selected_page = get_option($args['country'] . '_page');
    echo '<select name="' . $args['country'] . '_page">';
    foreach ($pages as $page) {
        $selected = ($page->ID == $selected_page) ? 'selected' : '';
        echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
    }
    echo '</select>';
}

function redirect_country_visitors() {
    $country_code = get_visitor_country_code();
    $country_page_id = get_option($country_code . '_page');

    if ($country_page_id && is_front_page()) {
        $country_page_url = get_permalink($country_page_id);
        wp_redirect($country_page_url);
        exit;
    }
}

add_action('template_redirect', 'redirect_country_visitors');

?>