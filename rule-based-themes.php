<?php
/*
Plugin Name: Rule Based Themes
Plugin URI: http://cargowire.net/projects/rule-based-themes
Description: A plugin that provides a class string (most likely to be used on the body) based on user defined rules
Version: 1.0
Author: Craig Rowe
Author URI: http://cargowire.net
*/

$rbtme_rules = array(); // Default empty rules when first installed
@include_once("rules.php");

include_once("rulebasedthemes.php"); // The serializer and parser class

/**
 * wp_rulebasedthemes, The specific WP plugin aspects of a rule creator and reader
 *
 * This class hooks in the rulebasedthemes class functionality into the admin section of wordpress as well
 * as providing the template functions for use in themes.
 *
 * @author Craig Rowe <http://cargowire.net>
 * @version 1.0
 * @package rule-based-themes
 */
class wp_rulebasedthemes {
    static function init() {
        if (!is_admin()) {
            wp_enqueue_script('jquery');
        }
    }

    static function add_settings_page() {
        if ( function_exists('add_submenu_page') && current_user_can('manage_options') && is_admin() ) {
            $menutitle = __('Rule Themes', 'RBTME');

            add_submenu_page('options-general.php', __('Rule Theme Options', 'RBTME'), $menutitle, 'manage_options', basename(__FILE__), array('wp_rulebasedthemes','options_subpanel'));
            add_filter('plugin_action_links',  array('wp_rulebasedthemes','filter_plugin_actions'), 10, 2);
        }
    }

    // Defer to the settings file for the admin panel
    static function options_subpanel(){
        include "rule-based-themes-settings.php";
    }

    static function filter_plugin_actions($links, $file){
        static $this_plugin;

        if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

        if( $file == $this_plugin ){
            $settings_link = '<a href="options-general.php?page=rule-based-themes.php">' . __('Settings') . '</a>';
            $links = array_merge( array($settings_link), $links);
        }
        return $links;
    }
}

// WP Initialization
if (is_admin()) {
    add_action('init', array('wp_rulebasedthemes','init'));
    add_action('admin_menu', array('wp_rulebasedthemes','add_settings_page'));
}

// Template functions
function rbtme_get_theme(){
    return rulebasedthemes::get_current_value();
}
function rbtme_get_rule_list(){
    return rulebasedthemes::get_rule_list();
}
function rbtme_show_rule_list(){
    echo rulebasedthemes::get_rule_list();
}