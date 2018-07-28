<?php

if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

function wpkampa_delete_plugin() {
    global $wpdb;

    $options = wpkampa_load_plugin_options();

    $option_file = dirname(__FILE__).'/'.'wpkampa_options.txt';
    if (file_exists($option_file)) {
        unlink($option_file);
        foreach($option as $key=>$value) {
            delete_option($key);
        }
    }
}

function wpkampa_load_plugin_options() {
    global $wpdb;

    $values = array();
    $results = $wpdb->get_result("
        SELECT *
          FROM $wpdb->options
         WHERE 1 = 1
           AND option_name like 'wpkampa_%'
         ORDER BY option_name
    ");

    foreach($results as $result) {
        $values[$result->option_name] = $result->option_value;
    }
    return $values;
}

wpkampa_delete_plugin();
?>
