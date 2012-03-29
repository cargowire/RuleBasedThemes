<?php
if(is_admin):

    if ((isset($_POST['rules'])) && wp_verify_nonce($_POST['_wpnonce'],'rbtme_nonce'))
    {
        $rules = json_decode(stripslashes($_POST['rules']), true);
        file_put_contents(dirname(__FILE__)."/rules.php",rulebasedthemes::serialize($rules));
    }

    ?>
    <style type="text/css">
        #rule-table, #rule-table table {border-collapse: separate; border-spacing: 0;}
        #rule-table {background: #fff; border: 10px solid #fff; box-shadow: 1px 1px 20px rgba(0,0,0,0.15); margin: 30px 20px; table-border: break;}
        #rule-table > thead {color: #fff; text-shadow: -1px -1px 0 rgba(0,0,0,0.5);
            background: rgb(229,157,89); /* Old browsers */
            background: -moz-linear-gradient(top, rgba(229,157,89,1) 1%, rgba(226,137,54,1) 100%); /* FF3.6+ */
            background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,rgba(229,157,89,1)), color-stop(100%,rgba(226,137,54,1))); /* Chrome,Safari4+ */
            background: -webkit-linear-gradient(top, rgba(229,157,89,1) 1%,rgba(226,137,54,1) 100%); /* Chrome10+,Safari5.1+ */
            background: -o-linear-gradient(top, rgba(229,157,89,1) 1%,rgba(226,137,54,1) 100%); /* Opera 11.10+ */
            background: -ms-linear-gradient(top, rgba(229,157,89,1) 1%,rgba(226,137,54,1) 100%); /* IE10+ */
            background: linear-gradient(top, rgba(229,157,89,1) 1%,rgba(226,137,54,1) 100%); /* W3C */
        }
        #rule-table > thead td, #rule-table > thead th {padding: 5px 5px 15px 5px; font-weight: bold; border-right: 1px solid rgba(0,0,0,0.1); border-left: 1px solid rgba(255,255,255,0.5); font-size: 1.1em;}
        #rule-table > tbody > tr:nth-child(4n+1), #rule-table > tbody > tr:nth-child(4n+2) {background: rgba(243,238,233,0.25);}
        #rule-table > tbody > tr:nth-child(4n+3), #rule-table > tbody > tr:nth-child(4n+4) {background: rgba(180, 188, 84,0.15);}

        #rule-table .deleterule, #rule-table .delete {width: 20px; height: 20px; border: 0; background: #dd4444; border-radius: 10px; color: #fff; font-size: 10px; font-weight: bold; box-shadow: 1px 1px 5px rgba(0,0,0,0.2); line-height: 30px; text-transform: uppercase; cursor: pointer; text-indent:-9999px}
        #rule-table .delete {width: 14px; height: 14px; font-size: 7px;}
        #rule-table .deleterule:hover, #rule-table .delete:hover {background: #ee5555;}

        #rule-table .rule > td {padding: 0 0;}
        #rule-table .actions > td {padding: 0 0 5px;}

        #rule-table .rule table {font-size: 10px;}
        #rule-table .rule th {font-weight: normal; padding: 0 3px; text-transform: capitalize; padding-top: 5px; border-right: 1px solid rgba(0,0,0,0.2);}
        #rule-table .rule th:last-child {border-right: 0;}
        #rule-table .rule table td {padding: 0 3px 5px; border-right: 1px solid rgba(0,0,0,0.2);}
        #rule-table .rule table td:last-child {border-right: 0;}
        #rule-table .rule table:nth-child(2n) {background: rgba(0,0,0,0.03);}

        #rule-table input[type="text"],#rule-table input[type="url"] {border-radius: 0; max-width: 110px; font-size: 11px}
        #rule-table .rule table input[maxlength="4"] {width: 45px;}
        #rule-table .rule table input.short {width: 23px;}

        #rule-table .adddate, #rule-table .addapi {background: rgba(142,182,192,0.99); color: #fff; border: 0; font-weight: bold; font-size: 11px; cursor: pointer;}
        #rule-table .adddate:hover, #rule-table .addapi:hover {background: rgba(142,182,192,0.80);}
        #rule-table .actions:after {content: ""; display: table-cell;}

        #rule-table tfoot td {text-align: right; padding: 20px 0;}
        #rule-table tfoot input, #inoutform input {background: rgba(229, 157, 89, 0.99); color: #fff; border: 0; font-weight: bold; font-size: 13px; cursor: pointer; padding: 6px;}
    </style>
    <script type="text/javascript">
        var rbtme = {};
        rbtme.events = (function(){
            return {
               init: function($){
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
                       $(this).parents('tr:first').prev('tr').find('> td:nth-child(5)').append($('#date-form').html());
                       e.preventDefault();
                   });
                   $('#rule-table').on('click', '.addapi', function(e){
                       $(this).parents('tr:first').prev('tr').find('> td:nth-child(5)').append($('#api-form').html());
                       e.preventDefault();
                   });
                   $('#export').on('click', function(e){
                       $('#inout').val(rbtme.rules.serialize());
                   });
                   $('#import').on('click', function(e){
                       rbtme.rules.save($('#inout').val());
                   });
               }
            }
        }());
        rbtme.rules = (function(){
            return {
                serialize: function(){
                    var json = "{";
                    var rules = []
                    jQuery('#rule-table tr.rule').each(function(i,v){
                        var ruleJson = "\"" + jQuery('input[name=rulekey]', this).val() + "\": {";
                        ruleJson += "\"group\":\"" + jQuery('input[name=group]', this).val() + "\",";
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

        jQuery(function($){ rbtme.events.init($); });
    </script>
    <script type="text/html" id="rule-form">
        <tr class="rule">
            <td><button class="deleterule">x</button></td>
            <td><input type="text" name="rulekey" required="required" pattern="^[a-zA-Z0-9\-]*$" value=""/></td>
            <td><input type="text" name="group" pattern="^[a-zA-Z0-9\-]*$" value=""/></td>
            <td><input type="text" name="value" pattern="^[a-zA-Z0-9\-]*$" value="" /></td>
            <td></td>
        </tr>
        <tr class="actions"><td></td><td></td><td></td><td><input type="button" class="adddate" value="add date"/><input type="button" class="addapi" value="add api"/></td></tr>
    </script>
    <script type="text/html" id="date-form">
        <table>
            <thead>
                <tr>
                    <th><button class="delete">x</button></th>
                    <th>from Day of week</th>
                    <th>from Day</th>
                    <th>from Month</th>
                    <th>from Year</th>
                    <th>from Hour</th>
                    <th>to Day of week</th>
                    <th>to Day</th>
                    <th>to Month</th>
                    <th>to Year</th>
                    <th>to Hour</th>
                </tr>
            </thead>
            <tbody>
            <tr class="sub-rule" data-type="Date">
                <td></td>
                <td><input type="text" maxlength="2" class="short" name="fromDayOfWeek" pattern="^[0-7]{1}*$" value=""/></td>
                <td><input type="text" maxlength="2" class="short" name="fromDay" pattern="^[0-9]{1,2}$" value=""/></td>
                <td><input type="text" maxlength="2" class="short" name="fromMonth" pattern="^[0-9]{1,2}$" value=""/></td>
                <td><input type="text" maxlength="4" name="fromYear" pattern="^[0-9]{1,4}$" value=""/></td>
                <td><input type="text" maxlength="2" class="short" name="fromHour" pattern="^[0-9]{1,2}$" value=""/></td>
                <td><input type="text" maxlength="2" class="short" name="toDayOfWeek" pattern="^[0-7]$" value=""/></td>
                <td><input type="text" maxlength="2" class="short" name="toDay" pattern="^[0-9]{1,2}$" value=""/></td>
                <td><input type="text" maxlength="2" class="short" name="toMonth" pattern="^[0-9]{1,2}$" value=""/></td>
                <td><input type="text" maxlength="4" name="toYear" pattern="^[0-9]{1,4}$" value=""/></td>
                <td><input type="text" maxlength="2" class="short" name="toHour" pattern="^[0-9]{1,2}$" value=""/></td>
            </tr>
            </tbody>
            </table>
    </script>
    <script type="text/html" id="api-form">
        <table>
            <thead>
                <tr>
                    <th><button class="delete">x</button></th>
                    <th>Uri</th>
                    <th>Cache Mins</th>
                    <th>XPath</th>
                    <th>Contains Value</th>
                </tr>
            </thead>
            <tbody>
                <tr class="sub-rule" data-type="publicapi">
                    <td></td>
                    <td><input type="url" name="uri" required="required" value="" /></td>
                    <td><input type="text" name="cachemins" pattern="^[0-9]{1,4}$" value=""/></td>
                    <td><input type="text" name="xpath" pattern="^[a-zA-Z0-9\@\/\[\]_-]*$" required="required" value=""/></td>
                    <td><input type="text" name="value" pattern="^[a-zA-Z0-9\-]*$" required="required" value=""/></td>
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
                                    <td>Group</td>
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
                                <td><button class="deleterule">x</button></td>
                                <td><input type="text" name="rulekey" required="required" pattern="^[a-zA-Z0-9\-]*$" value="<?php echo $rulekey; ?>"/></td>
                                <td><input type="text" name="group" pattern="^[a-zA-Z0-9\-]*$" value="<?php echo $parentRule["group"]; ?>"/></td>
                                <td><input type="text" name="value" pattern="^[a-zA-Z0-9\-]*$" value="<?php echo $parentRule["value"]; ?>" /></td>
                                <td>
    <?php
                            foreach ($parentRule["rules"] as $rulekey => $rule):
                                switch($rule["Type"]):
                                    case "Date":
    ?>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th><button class="delete">x</button></th>
                                                    <th><?php _e('from Day Of Week', 'RBTME') ?></th>
                                                    <th><?php _e('from Day', 'RBTME') ?></th>
                                                    <th><?php _e('from Month', 'RBTME') ?></th>
                                                    <th><?php _e('from Year', 'RBTME') ?></th>
                                                    <th><?php _e('from Hour', 'RBTME') ?></th>
                                                    <th><?php _e('to Day Of Week', 'RBTME') ?></th>
                                                    <th><?php _e('to Day', 'RBTME') ?></th>
                                                    <th><?php _e('to Month', 'RBTME') ?></th>
                                                    <th><?php _e('to Year', 'RBTME') ?></th>
                                                    <th><?php _e('to Hour', 'RBTME') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="sub-rule" data-type="Date">
                                                    <td></td>
                                                    <td><input type="text" maxlength="2" class="short" name="fromDayOfWeek" pattern="^[0-7]{1}$"  value="<?php echo $rule["fromDayOfWeek"];?>"/></td>
                                                    <td><input type="text" maxlength="2" class="short" name="fromDay" pattern="^[0-9]{1,2}$" value="<?php echo $rule["fromDay"];?>"/></td>
                                                    <td><input type="text" maxlength="2" class="short" name="fromMonth" pattern="^[0-9]{1,2}$" value="<?php echo $rule["fromMonth"];?>"/></td>
                                                    <td><input type="text" maxlength="4" name="fromYear" pattern="^[0-9]{1,4}$" value="<?php echo $rule["fromYear"];?>"/></td>
                                                    <td><input type="text" maxlength="2" class="short" name="fromHour" pattern="^[0-9]{1,2}$" value="<?php echo $rule["fromHour"];?>"/></td>
                                                    <td><input type="text" maxlength="2" class="short" name="toDayOfWeek" pattern="^[0-7]$" value="<?php echo $rule["toDayOfWeek"];?>"/></td>
                                                    <td><input type="text" maxlength="2" class="short" name="toDay" pattern="^[0-9]{1,2}$" value="<?php echo $rule["toDay"];?>"/></td>
                                                    <td><input type="text" maxlength="2" class="short" name="toMonth" pattern="^[0-9]{1,2}$" value="<?php echo $rule["toMonth"];?>"/></td>
                                                    <td><input type="text" maxlength="4" name="toYear" pattern="^[0-9]{1,4}$" value="<?php echo $rule["toYear"];?>"/></td>
                                                    <td><input type="text" maxlength="2" class="short" name="toHour" pattern="^[0-9]{1,2}$" value="<?php echo $rule["toHour"];?>"/></td>
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
                                                    <th><button class="delete">x</button></th>
                                                    <th><?php _e('Uri', 'RBTME') ?></th>
                                                    <th><?php _e('Cache Mins', 'RBTME') ?></th>
                                                    <th><?php _e('XPath', 'RBTME') ?></th>
                                                    <th><?php _e('Contains Value', 'RBTME') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="sub-rule" data-type="publicapi">
                                                    <td></td>
                                                    <td><input type="url" name="uri" required="required" value="<?php echo $rule["uri"];?>"/></td>
                                                    <td><input type="text" name="cachemins" pattern="^[0-9]{1,4}$" value="<?php echo $rule["cachemins"];?>"/></td>
                                                    <td><input type="text" name="xpath" required="required" pattern="^[a-zA-Z0-9\@\/\[\]_-]*$" value="<?php echo $rule["xpath"];?>"/></td>
                                                    <td><input type="text" name="value" required="required" pattern="^[a-zA-Z0-9\-]*$" value="<?php echo $rule["value"];?>"/></td>
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
                    <h4><?php _e('Notes','RBTME');?></h4>
                    <ul>
                        <li><?php _e('Rule names must be unique but are only used by the <strong>rbtme_get_rule_list()</strong> functionality.  Similarly group values
                        are only used by these list outputs (to group the rules together for display).', 'RBTME'); ?></li>
                        <li><?php _e('The "value" column is the key field which is used as the actual "class" to add to the final <strong>get_current_theme()</strong> response.', 'RBTME');?></li>
                        <li><?php _e('Sub-rules are combined using "and".  To create "or" structures simply create multiple rules.', 'RBTME');?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="poststuff">
            <div class="postbox">
                <h3><?php _e('Import/Export', 'RBTME') ?></h3>
                <div class="inside">
                    <p><?php _e('Rules are stored on the file system, not as part of the wordpress database.  Therefore to move from a dev to production environment please export and import from here.', 'RBTME'); ?></p>
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
<?php endif;