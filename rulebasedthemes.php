<?php
/**
 * rulebasedthemes, A rule creator and reader
 *
 * This class allows for the serialization and application of rules.  These rules are assumed
 * to exist within a global $rbtme_rules variable (serialize is intended to be used to write these
 * rules to a PHP file.  This function is used, rather than a general purpose 'write associative array to
 * file' function so as to allow for specific normalization of the expect field types.
 *
 * @author Craig Rowe <http://cargowire.net>
 * @version 1.0
 * @package rule-based-themes
 */
class rulebasedthemes {

    /**
     * Serialize takes a rules associative array and writes it to string in the format required by a php file (after normalizing all values)
     * @param array $rules the rules to serialize to text.  See the code for the format of expected rules.
     * @return string the rules as a php file string
     */
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

    /**
     * get_current_value applies the current ruleset and returns the appropriate string value output
     * @global array uses $rbtme_rules as the rules
     * @return string the appropriate values based upon the rules
     */
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
                        $response = rulebasedthemes::get_api_response($rule["uri"], $rule["cachemins"]);
                        if($response != ""){
                            $doc = new DOMDocument();
                            @$doc->loadXML($response);
                            $xpath = new DOMXpath($doc);
                            $value = @$xpath->evaluate("string(".$rule["xpath"].")");
                            $apply = stripos($value,$rule["value"]) !== false;
                        }

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

    /**
     * get_rule_list provides an html output of the current ruleset.
     * @global array uses $rbtme_rules as the rules
     * @return string the <div class="group"><p>group</p><ul><li data-value="value">rulekey</li></ul></div> html output based upon the rules
     */
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
            $rulesOutput .= "<div class=".$group."><p>".$group."</p><ul>";
            $rulesOutput .= join($rules,"");
            $rulesOutput .= "</ul></div>";
        }
        return $rulesOutput;
    }

    static function get_api_response($uri, $cacheMins){
        $cacheFile = dirname(__FILE__)."/cache/".md5($uri).".php";
        if ((!file_exists($cacheFile)) || (time() - filemtime($cacheFile) > ($cacheMins * 60))) {
            $fp = fopen($cacheFile, 'w+');
            if ($fp) {
                if (flock($fp, LOCK_EX)) {
                    $response = rulebasedthemes::get_url($uri);
                    if($response != "") {
                        fwrite($fp, $response);
                    }
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }
        }
        return file_get_contents($cacheFile);
    }

    function get_url($url, $curlopt = array()){
        $curl = curl_init();
        $defaults = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1
        );
        $curlopt = array(CURLOPT_URL => $url) + $curlopt + $defaults;
        curl_setopt_array($curl, $curlopt);
        $response = curl_exec($curl);
        curl_close($curl);

        if($response === false)
            return "";
        else
            return $response;
    }
}