<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$prefix = 'suzuri-for-wp_';

delete_option($prefix . "_api_key");
delete_option($prefix . "_user_name");
delete_option($prefix . "_limit");
delete_option($prefix . "_product_type");
delete_option($prefix . "_choice_id");
