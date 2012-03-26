<?php
/*
Plugin Name: Rule Based Themes
Plugin URI: http://cargowire.net/projects/rule-based-themes
Description: A plugin that provides various body classes based on predefined rules
Version: 1.0
Author: Craig Rowe
Author URI: http://cargowire.net
*/

$rbtme_rules = array();

@include_once("rules.php");

class rulebasedthemes {
    static function add_settings_page() {
        if ( function_exists('add_submenu_page') && current_user_can('manage_options') && is_admin() ) {
            $menutitle = __('Rule Themes', 'RBTME');

            add_submenu_page('options-general.php', __('Rule Theme Options', 'RBTME'), $menutitle, 'manage_options', basename(__FILE__), array('rulebasedthemes','options_subpanel'));
            add_filter('plugin_action_links',  array('rulebasedthemes','filter_plugin_actions'), 10, 2);
        }
    }

    static function options_subpanel(){
        include "rule-themes-settings.php";
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

    static function serialize($rules){
        $serialized = "<?php \n\r\$rbtme_rules = array(\n\r";

        foreach ($rules as $rulekey => $parentRule){
            $serialized .= "    \"$rulekey\" => array(\n\r";
            $serialized .= "        \"value\" => \"".$parentRule["value"]."\",\n\r";
            $serialized .= "        \"rules\" => array(\n\r";
            foreach ($parentRule["rules"] as $srulekey => $rule){
                $serialized .= "            \"".$srulekey."\" => array(\n\r";
                $serialized .= "                \"Type\" => \"".$rule["Type"]."\",\n\r";
                switch($rule["Type"]){
                    case "Date":
                        $serialized .= "                \"fromDayOfWeek\" => \"".$rule["fromDayOfWeek"]."\",\n\r";
                        $serialized .= "                \"fromDay\" => \"".$rule["fromDay"]."\",\n\r";
                        $serialized .= "                \"fromMonth\" => \"".$rule["fromMonth"]."\",\n\r";
                        $serialized .= "                \"fromYear\" => \"".$rule["fromYear"]."\",\n\r";
                        $serialized .= "                \"fromHour\" => \"".$rule["fromHour"]."\",\n\r";
                        $serialized .= "                \"toDayOfWeek\" => \"".$rule["toDayOfWeek"]."\",\n\r";
                        $serialized .= "                \"toDay\" => \"".$rule["toDay"]."\",\n\r";
                        $serialized .= "                \"toMonth\" => \"".$rule["toMonth"]."\",\n\r";
                        $serialized .= "                \"toYear\" => \"".$rule["toYear"]."\",\n\r";
                        $serialized .= "                \"toHour\" => \"".$rule["toHour"]."\"\n\r";
                        break;
                    case "publicapi":
                        $serialized .= "                \"uri\" => \"".$rule["uri"]."\",\n\r";
                        $serialized .= "                \"cachemins\" => \"".$rule["cachemins"]."\",\n\r";
                        $serialized .= "                \"xpath\" => \"".$rule["xpath"]."\",\n\r";
                        $serialized .= "                \"value\" => \"".$rule["value"]."\"\n\r";
                        break;
                }
                $serialized .= "            ),\n\r";
            }
            $serialized .= "        ),\n\r";
            $serialized .= "    ),\n\r";
        }
        $serialized .= ");\n\r";

        return $serialized;
    }

    static function get_current_value(){
        $themeModifier = "";

        global $rbtme_rules;
        foreach ($rbtme_rules as $parentrulekey => $parentRule){
            $potentialValue = $parentRule["value"];
            $apply = false;
            foreach ($parentRule["rules"] as $rulekey => $rule){
                switch($rule["Type"]){
                    case "Date":

                        $apply = (
                                        ($rule["fromDayOfWeek"] == "" || $rule["fromDayOfWeek"] <= Date("N"))
                                    &&  ($rule["fromDay"] == "" || $rule["fromDay"] <= Date("j"))
                                    &&  ($rule["fromMonth"] == "" || $rule["fromMonth"] <= Date("n"))
                                    &&  ($rule["fromYear"] == "" || $rule["fromYear"] <= Date("Y"))
                                    &&  ($rule["fromHour"] == "" || $rule["fromHour"] <= Date("G"))
                                    &&  ($rule["toDayOfWeek"] == "" || $rule["toDayOfWeek"] >= Date("N"))
                                    &&  ($rule["toDay"] == "" || $rule["toDay"] >= Date("j"))
                                    &&  ($rule["toMonth"] == "" || $rule["toMonth"] >= Date("n"))
                                    &&  ($rule["toYear"] == "" || $rule["toYear"] >= Date("Y"))
                                    &&  ($rule["toHour"] == "" || $rule["toHour"] >= Date("G"))
                                );
                        break;
                    case "publicapi":
                        $cacheMins = $rule["cachemins"];
                        $cacheFile = dirname(__FILE__)."/cache/api-".$parentrulekey."-".$rulekey.".php";
                        if (!file_exists($cacheFile) ||
                            time() - filemtime($cacheFile) > ($cacheMins * 60)) {
                            $fp = fopen($cacheFile, 'w+');
                            if ($fp) {
                                if (flock($fp, LOCK_EX)) {
                                    $response = file_get_contents($rule["uri"]);
                                    fwrite($fp, $response);
                                    flock($fp, LOCK_UN);
                                }
                                fclose($fp);
                            }
                        }
                        $response = file_get_contents($cacheFile);
                        $doc = new DOMDocument();
                        $doc->loadXML($response);
                        $xpath = new DOMXpath($doc);
                        $value = $xpath->evaluate("string(".$rule["xpath"].")");
                        $apply = strpos($value,$rule["value"]) !== false;
                        break;
                }
                if($apply)
                    break;
            }
            if($apply)
                $themeModifier .= " ".$potentialValue;
        }

        /*$themeModifier = "day";
        if(isset($_GET['night']) || ((!isset($_GET['day'])) && (date("H") > 17 || date("H") < 8)))
            $themeModifier = "night";
*/
        return $themeModifier;
    }
}
function rbtme_init(){
    if (!is_admin()) {
        wp_enqueue_script('jquery');
    }
}

if (is_admin()) {
    add_action('init', 'rbtme_init');
    add_action('admin_menu', array('rulebasedthemes','add_settings_page'));
}

function rbtme_get_theme(){
    return rulebasedthemes::get_current_value();
}