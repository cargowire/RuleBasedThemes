<?php

if ((isset($_POST['rules'])) && wp_verify_nonce($_POST['_wpnonce'],'rbtme_nonce'))
{
    $rules = json_decode(stripslashes($_POST['rules']), true);
    file_put_contents(dirname(__FILE__)."/rules.php",rulebasedthemes::serialize($rules));
}

?>
<style type="text/css">
    #rule-table { vertical-align: top; text-align: left; }
    .short { width:30px; }
</style>
<script type="text/javascript">
    var rbtme = {};
    rbtme.rules = (function(){
        return {
            serialize: function(){
                var json = "{";
                var rules = []
                jQuery('#rule-table tr.rule').each(function(i,v){
                    var ruleJson = "\"" + jQuery('input[name=rulekey]', this).val() + "\": {";
                    ruleJson += "\"value\":\"" + jQuery('input[name=value]', this).val() + "\",";
                    ruleJson += "\"rules\": {";
                    var subrules = [];
                    jQuery('tr.sub-rule', this).each(function(ind,va){
                        var subruleJson = "\""+ ind +"\": {";
                        subruleJson += "\"Type\": \""+ jQuery(va).data("type") +"\",";
                        var subrulesvals = [];
                        jQuery('input', this).each(function(inde,val){
                            subrulesvals.push("\""+jQuery(val).attr("name")+"\": \""+ jQuery(val).val() +"\"");
                        });
                        subruleJson += subrulesvals.join(",");
                        subruleJson += "}";
                        subrules.push(subruleJson);
                    });
                    ruleJson += subrules.join(",");
                    ruleJson += "}}";
                    rules.push(ruleJson);
                });
                json += rules.join(",");
                json += "}";
                return json;
            },
            save: function(rules){
                jQuery.post("<?php echo $_SERVER['PHP_SELF']; ?>?page=rule-based-themes.php", { _wpnonce: jQuery('#_wpnonce').val(), rules: JSON.stringify(JSON.parse(rules)) },
                    function(data) {
                        window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?page=rule-based-themes.php";
                    }
                );
            }
        }
    }());
    jQuery(function($){
        $('#rules-form').on('submit', function(e){
            var json = rbtme.rules.serialize();
            rbtme.rules.save(json);
            e.preventDefault();
        });
        $('#rule-table').on('click', '.delete', function(e){
            $(this).parents('table:first').remove();
            e.preventDefault();
        });
        $('#rule-table').on('click', '.deleterule', function(e){
            var ruleRow = $(this).parents('tr:first');
            var ruleRowActions = ruleRow.next('tr');
            ruleRow.remove();
            ruleRowActions.remove();
            e.preventDefault();
        });
        $('#rule-table').on('click', '.addrule', function(e){
           $('#rule-table > tbody').append($('#rule-form').html());
           e.preventDefault();
        });
        $('#rule-table').on('click', '.adddate', function(e){
            $(this).parents('tr:first').prev('tr').find('> td:nth-child(4)').append($('#date-form').html());
            e.preventDefault();
        });
        $('#rule-table').on('click', '.addapi', function(e){
            $(this).parents('tr:first').prev('tr').find('> td:nth-child(4)').append($('#api-form').html());
            e.preventDefault();
        });
        $('#export').on('click', function(e){
            $('#inout').val(rbtme.rules.serialize());
        });
        $('#import').on('click', function(e){
            rbtme.rules.save($('#inout').val());
        });
    });
</script>
<script type="text/html" id="rule-form">
    <tr class="rule">
        <td><input type="button" class="deleterule" value="x"/></td>
        <td><input type="text" name="rulekey" value=""/></td>
        <td><input type="text" name="value" value="" /></td>
        <td>
        </td>
    </tr>
    <tr class="actions"><td></td><td></td><td><input type="button" class="adddate" value="add date"/><input type="button" class="addapi" value="add api"/></td></tr>
</script>
<script type="text/html" id="date-form">
    <table>
        <thead>
            <tr>
                <th><input type="button" class="delete" value="x"/></th>
                <th>from Day of the week</th>
                <th>from Day</th>
                <th>from Month</th>
                <th>from Year</th>
                <th>from Hour</th>
                <th>to Day of the week</th>
                <th>to Day</th>
                <th>to Month</th>
                <th>to Year</th>
                <th>to Hour</th>
            </tr>
        </thead>
        <tbody>
        <tr class="sub-rule" data-type="Date">
            <td></td>
            <td><input type="text" maxlength="2" class="short" name="fromDayOfWeek" value=""/></td>
            <td><input type="text" maxlength="2" class="short" name="fromDay" value=""/></td>
            <td><input type="text" maxlength="2" class="short" name="fromMonth" value=""/></td>
            <td><input type="text" maxlength="4" name="fromYear" value=""/></td>
            <td><input type="text" maxlength="2" class="short" name="fromHour" value=""/></td>
            <td><input type="text" maxlength="2" class="short" name="toDayOfWeek" value=""/></td>
            <td><input type="text" maxlength="2" class="short" name="toDay" value=""/></td>
            <td><input type="text" maxlength="2" class="short" name="toMonth" value=""/></td>
            <td><input type="text" maxlength="4" name="toYear" value=""/></td>
            <td><input type="text" maxlength="2" class="short" name="toHour" value=""/></td>
        </tr>
        </tbody>
        </table>
</script>
<script type="text/html" id="api-form">
    <table>
        <thead>
            <tr>
                <th><input type="button" class="delete" value="x"/></th>
                <th>Uri</th>
                <th>Cache Mins</th>
                <th>XPath</th>
                <th>Contains Value</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sub-rule" data-type="publicapi">
                <td></td>
                <td><input type="text" name="uri" value="" /></td>
                <td><input type="text" name="cachemins" value=""/></td>
                <td><input type="text" name="xpath" value=""/></td>
                <td><input type="text" name="value" value=""/></td>
            </tr>
        </tbody>
        </table>
</script>
<div class="wrap">
    <h2><?php _e('Rule Based Theme Options', 'RBTME') ?></h2>
    <?php echo $message_export; ?>
    <br class="clear" />
    <div id="poststuff">
        <div class="postbox">
            <h3><?php _e('Settings', 'RBTME') ?></h3>
            <div class="inside">
                <form id="rules-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=rule-based-themes.php">
                    <?php wp_nonce_field('rbtme_nonce') ?>
<table id="rule-table">
    <thead>
        <tr>
            <th colspan="2">Rule</th>
            <th>Value</th>
            <th>Sub Rules</th>
        </tr>
    </thead>
    <tbody>
                    <?php
                    global $rbtme_rules;
                    foreach ($rbtme_rules as $rulekey => $parentRule):
?>
    <tr class="rule">
        <td><input type="button" class="deleterule" value="x"/></td>
        <td><input type="text" name="rulekey" value="<?php echo $rulekey; ?>"/></td>
        <td><input type="text" name="value" value="<?php echo $parentRule["value"]; ?>" /></td>
        <td>
<?php
                        foreach ($parentRule["rules"] as $rulekey => $rule):
                            switch($rule["Type"]):
                                case "Date":
?>
    <table>
        <thead>
            <tr>
                <th><input type="button" class="delete" value="x"/></th>
                <th>from Day Of Week</th>
                <th>from Day</th>
                <th>from Month</th>
                <th>from Year</th>
                <th>from Hour</th>
                <th>to Day Of Week</th>
                <th>to Day</th>
                <th>to Month</th>
                <th>to Year</th>
                <th>to Hour</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sub-rule" data-type="Date">
                <td></td>
                <td><input type="text" maxlength="2" class="short" name="fromDayOfWeek" value="<?php echo $rule["fromDayOfWeek"];?>"/></td>
                <td><input type="text" maxlength="2" class="short" name="fromDay" value="<?php echo $rule["fromDay"];?>"/></td>
                <td><input type="text" maxlength="2" class="short" name="fromMonth" value="<?php echo $rule["fromMonth"];?>"/></td>
                <td><input type="text" maxlength="4" name="fromYear" value="<?php echo $rule["fromYear"];?>"/></td>
                <td><input type="text" maxlength="2" class="short" name="fromHour" value="<?php echo $rule["fromHour"];?>"/></td>
                <td><input type="text" maxlength="2" class="short" name="toDayOfWeek" value="<?php echo $rule["toDayOfWeek"];?>"/></td>
                <td><input type="text" maxlength="2" class="short" name="toDay" value="<?php echo $rule["toDay"];?>"/></td>
                <td><input type="text" maxlength="2" class="short" name="toMonth" value="<?php echo $rule["toMonth"];?>"/></td>
                <td><input type="text" maxlength="4" name="toYear" value="<?php echo $rule["toYear"];?>"/></td>
                <td><input type="text" maxlength="2" class="short" name="toHour" value="<?php echo $rule["toHour"];?>"/></td>
            </tr>
        </tbody>
    </table>
<?php
                                    break;
                                case "publicapi":
?>
    <table>
        <thead>
            <tr>
                <th><input type="button" class="delete" value="x"/></th>
                <th>Uri</th>
                <th>Cache Mins</th>
                <th>XPath</th>
                <th>Contains Value</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sub-rule" data-type="publicapi">
                <td></td>
                <td><input type="text" name="uri" value="<?php echo $rule["uri"];?>"/></td>
                <td><input type="text" name="cachemins" value="<?php echo $rule["cachemins"];?>"/></td>
                <td><input type="text" name="xpath" value="<?php echo $rule["xpath"];?>"/></td>
                <td><input type="text" name="value" value="<?php echo $rule["value"];?>"/></td>
            </tr>
        </tbody>
    </table>
                            <?php break;?>
                        <?php endswitch;?>
                    <?php endforeach?>
            </td>
        </tr>
        <tr class="actions"><td></td><td></td><td></td><td><input type="button" class="adddate" value="add date"/><input type="button" class="addapi" value="add api"/></td></tr>
            <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td><input type="submit" value="save"/></td>
            <td><input type="button" class="addrule" value="add rule"/></td>
            <td></td>
        </tr>
    </tfoot>
</table>
                </form>
            </div>
        </div>
    </div>

    <div id="poststuff">
        <div class="postbox">
            <h3><?php _e('Import/Export', 'RBTME') ?></h3>
            <div class="inside">
                <form id="inoutform">
                    <textarea id="inout" name="inout" rows="8" cols="100"></textarea>
                    <p>
                        <input id="import" type="button" value="import"/>
                        <input id="export" type="button" value="export"/>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>