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
            $serialized .= "    \"".rulebasedthemes::normalize_rule_name($rulekey)."\" => array(\n\r";
            $serialized .= "        \"group\" => \"".rulebasedthemes::normalize_value($parentRule["group"])."\",\n\r";
            $serialized .= "        \"value\" => \"".rulebasedthemes::normalize_value($parentRule["value"])."\",\n\r";
            $serialized .= "        \"rules\" => array(\n\r";
            foreach ($parentRule["rules"] as $srulekey => $rule){
                $serialized .= "            \"".rulebasedthemes::normalize_rule_name($srulekey)."\" => array(\n\r";
                $serialized .= "                \"Type\" => \"".rulebasedthemes::normalize_rule_name($rule["Type"])."\",\n\r";
                switch($rule["Type"]){
                    case "Date":
                        $serialized .= "                \"fromDayOfWeek\" => \"".rulebasedthemes::normalize_day_of_week($rule["fromDayOfWeek"])."\",\n\r";
                        $serialized .= "                \"fromDay\" => \"".rulebasedthemes::normalize_day_of_month($rule["fromDay"])."\",\n\r";
                        $serialized .= "                \"fromMonth\" => \"".rulebasedthemes::normalize_month($rule["fromMonth"])."\",\n\r";
                        $serialized .= "                \"fromYear\" => \"".rulebasedthemes::normalize_year($rule["fromYear"])."\",\n\r";
                        $serialized .= "                \"fromHour\" => \"".rulebasedthemes::normalize_hour($rule["fromHour"])."\",\n\r";
                        $serialized .= "                \"toDayOfWeek\" => \"".rulebasedthemes::normalize_day_of_week($rule["toDayOfWeek"])."\",\n\r";
                        $serialized .= "                \"toDay\" => \"".rulebasedthemes::normalize_day_of_month($rule["toDay"])."\",\n\r";
                        $serialized .= "                \"toMonth\" => \"".rulebasedthemes::normalize_month($rule["toMonth"])."\",\n\r";
                        $serialized .= "                \"toYear\" => \"".rulebasedthemes::normalize_year($rule["toYear"])."\",\n\r";
                        $serialized .= "                \"toHour\" => \"".rulebasedthemes::normalize_hour($rule["toHour"])."\"\n\r";
                        break;
                    case "publicapi":
                        $serialized .= "                \"uri\" => \"".rulebasedthemes::normalize_uri($rule["uri"])."\",\n\r";
                        $serialized .= "                \"cachemins\" => \"".rulebasedthemes::normalize_cache_mins($rule["cachemins"])."\",\n\r";
                        $serialized .= "                \"xpath\" => \"".rulebasedthemes::normalize_xpath($rule["xpath"])."\",\n\r";
                        $serialized .= "                \"value\" => \"".rulebasedthemes::normalize_value($rule["value"])."\"\n\r";
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

    static function normalize_rule_name($value){ return rulebasedthemes::regexnormalize('/^[a-zA-Z0-9\-]*$/', (string)$value); }
    static function normalize_day_of_week($value) { return rulebasedthemes::regexnormalize('/^[0-7]$/',substr($value,0,1)); }
    static function normalize_day_of_month($value) { return rulebasedthemes::regexnormalize('/^[0-9]{1,2}$/',substr($value,0,2)); }
    static function normalize_month($value){ return rulebasedthemes::regexnormalize('/^[0-9]{1,2}$/',substr($value,0,2)); }
    static function normalize_year($value){ return  rulebasedthemes::regexnormalize('/^[0-9]{1,4}$/',substr($value,0,4)); }
    static function normalize_hour($value){ return rulebasedthemes::regexnormalize('/^[0-9]{1,2}$/',substr($value,0,2)); }
    static function normalize_uri($value){ return filter_var(str_replace('"','', $value),FILTER_VALIDATE_URL); }
    static function normalize_cache_mins($value){ return  rulebasedthemes::regexnormalize('/^[0-9]{1,4}$/',substr($value,0,4)); }
    static function normalize_xpath($value){ return rulebasedthemes::regexnormalize('~^[a-zA-Z0-9'.preg_quote('@/[]_-').']*$~',$value); }
    static function normalize_value($value){ return rulebasedthemes::regexnormalize('/^[a-zA-Z0-9\-]*$/',$value); }
    static function regexnormalize($regex, $value){
        $matches = array();
        if(($value != "") && (preg_match($regex, $value, $matches)) && (count($matches) > 0))
            return $matches[0];
        else
            return "";
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

        return $themeModifier;
    }

    static function get_rule_list(){
        global $rbtme_rules;
        $ruleGroups = array();
        foreach ($rbtme_rules as $parentrulekey => $parentRule){
            if($ruleGroups[$parentRule["group"]]){
                array_push($ruleGroups[$parentRule["group"]], "<li data-value=\"".$parentRule["value"]."\">".$parentrulekey."</li>");
            }else{
                $ruleGroups[$parentRule["group"]] = array("<li data-value=\"".$parentRule["value"]."\">".$parentrulekey."</li>");
            }
        }
        $rulesOutput = "";
        foreach($ruleGroups as $group => $rules){
            $rulesOutput .= "<div><p>".$group."</p><ul>";
            $rulesOutput .= join($rules,"");
            $rulesOutput .= "</ul></div>";
        }
        return $rulesOutput;
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
function rbtme_get_rule_list(){
    return rulebasedthemes::get_rule_list();
}