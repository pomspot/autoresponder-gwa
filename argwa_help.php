<?php
    function  argwa_help(){
  global $wpdb;
    $jscode ='
    <script type="text/javascript">
    jQuery(document).ready( function($) {
    var options1 = {\'content\':\'<h3>'.__("ARGWA Pro Status Box",'argwa').'<\/h3><p>'.
    __("Here you can see and reset the time that the daily autoresponder mailings will be generated each day. Hover over the clock to see the exact time of the next mailing.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'bottom\'}};
    $(\'#help1\').click(function(){ $(this).pointer( options1 ).pointer(\'open\'); });';
  $rce = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_lists",ARRAY_A);
  foreach($rce as $e) {
$jscode .='
var options5'.$e['xID'].' = {\'content\':\'<h3>'.__("List Details",'argwa').'<\/h3><p>'.__("Configure all main list settings in this box. See documentation for more info about each option. <p>Be sure to use a From: email address at your domain, not a gmail/yahoo/hotmail address for best delivery rates, as forging/faking your return/from address can get all your email quickly labeled as spam.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help5'.$e['xID'].'\').click(function(){ $(this).pointer( options5'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options7'.$e['xID'].' = {\'content\':\'<h3>'.__("Manage Messages",'argwa').'<\/h3><p>'.__("Complete all form options entirely and correctly. Click the subject to edit a message, use the buttons to add new messages or send a broadcast mailing.<p>Autoresponder messages require you set the Day# (after subscribing) that the message will be sent to subscribers.<p>To create HTML Messages use the HTML WYSIWYG editor panel or paste your html source using the source pane of the editor. Use the TEXT Panel to enter a text-only message which is shown to subscribers who cannot view HTML formatted messages.<p>To send a plain-text only message to all subscribers complete Text-Panel only (leave HTML empty.)",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help7'.$e['xID'].'\').click(function(){ $(this).pointer( options7'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options8'.$e['xID'].' = {\'content\':\'<h3>'.__("Manage Leads",'argwa').'<\/h3><p>'.__("Edit lead info directly in the form below. You may change the Day# for any subscriber record to reset position in message sequence. Click the plus sign to view or edit additional data fields for each lead.<p>Use the buttons to import and export to/from your leads list. Follow the instructions to be sure you import leads correctly. Copy and paste leads list from export window.<p>To view Unconfirmed and Deleted Leads use the drop-down in the search form. Enter keyword (all or part of name or email) to search leads. Click the Email Icon next to each unconfirmed address to re-send their confirm message.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help8'.$e['xID'].'\').click(function(){ $(this).pointer( options8'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options9'.$e['xID'].' = {\'content\':\'<h3>'.__("Subscription Form Editor",'argwa').'<\/h3><p>'.__("Use the WYSIWYG Editor to modify your subscription form widget display. Alternatively you may copy and paste your modified form (from your preferred external html editor software) into the Source Pane of the editor panel. All changes will appear on your blog immediately.<p>This form html may be posted anywhere online to subscribe new leads to your plugin. Be sure to keep all form fields intact (names must be same) including hidden fields.<p>WARNING: Do not edit javascript at top of form html, this is used to check the form fields for errors before submission.<p>Click the button to reset the form to default if it stop working after editing.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help9'.$e['xID'].'\').click(function(){ $(this).pointer( options9'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options10'.$e['xID'].' = {\'content\':\'<h3>'.__("Subscribe Page",'argwa').'<\/h3><p>'.__("Select a public blog page at the top or enter an external URL where subscribers will be forwarded.<p>Alternatively enter custom content in the WYSIWYG Editor that will be shown to the user after subscribing. The content will be displayed automatically using the <code>page.php</code> template in your theme folder.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help10'.$e['xID'].'\').click(function(){ $(this).pointer( options10'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options11'.$e['xID'].' = {\'content\':\'<h3>'.__("Confirm Page",'argwa').'<\/h3><p>'.__("Select a public blog page at the top or enter an external URL where subscribers will be forwarded after clicking the confirm link.<p>Alternatively enter custom content in the WYSIWYG Editor that will be shown to the user after clicking the confirm link. The content will be displayed automatically using the <code>page.php</code> template in your theme folder.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help11'.$e['xID'].'\').click(function(){ $(this).pointer( options11'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options12'.$e['xID'].' = {\'content\':\'<h3>'.__("Unsubscribe Page",'argwa').'<\/h3><p>'.__("Select a public blog page at the top or enter an external URL where subscribers will be forwarded after unsubscribing.<p>Alternatively enter custom content in the WYSIWYG Editor that will be shown to the user after unsubscribing. The content will be displayed automatically using the <code>page.php</code> template in your theme folder.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help12'.$e['xID'].'\').click(function(){ $(this).pointer( options12'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options13'.$e['xID'].' = {\'content\':\'<h3>'.__("Error Page",'argwa').'<\/h3><p>'.__("Select a public blog page at the top or enter an external URL where subscribers will be forwarded when an error occurs.<p>Alternatively enter custom content in the WYSIWYG Editor that will be shown to the user when an error is encountered like email already subscribed. The content will be displayed automatically using the <code>page.php</code> template in your theme folder.",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help13'.$e['xID'].'\').click(function(){ $(this).pointer( options13'.$e['xID'].' ).pointer(\'open\'); });
';
$jscode .='
var options15'.$e['xID'].' = {\'content\':\'<h3>'.__("Confirm Message Contents",'argwa')."<\/h3><p>".__("This is the confirm message sent with a confirm link to each user after subscribing. Using the double opt-in process the user must click the link in the message to be subscribed to your list.<p>The link and disclaimer information is added automatically to the message footer. This info is required to protect you from spam complaints. View a complete log of all messages sent by the plugin in the wpdb table: <code>{prefix}_ar_gwa_log</code>",'argwa').'<\/p>\',\'position\':{\'edge\':\'left\',\'align\':\'center\'}}
$(\'#help15'.$e['xID'].'\').click(function(){ $(this).pointer( options15'.$e['xID'].' ).pointer(\'open\'); });
';

}
    $jscode .='
    });
    </script>
    ';
    echo $jscode;
    }

?>