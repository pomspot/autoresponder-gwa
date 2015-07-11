<?php
/*
Plugin Name: ARGWA FREE Autoresponder
Plugin URI: http://freeautoresponder.biz
Description: This Plugin is a Newsletter and Mailing List Manager allowing you to build and manage your double opt-in email compaign right on your blog.
Author: pomspot
Author URI: http://code4cookies.com
Version: 4.0.6
*/
/*
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
define('ARGWA_PAGE', 'argwa_page');
if (!function_exists('add_action') && !isset($_GET['afn'])) {
header('Status: 403 Forbidden');
header('HTTP/1.1 403 Forbidden');
exit();
} else if(isset($_GET['afn'])) {
argwa_get_wp_root( dirname( dirname(__FILE__) ) ); if ( $wp_root ) {include_once $wp_root . '/wp-load.php';} else {die( 'Cheatin&#8217; uh?');exit;}
  define('ARGWA_TEMPLATEPATH', get_stylesheet_directory().'/');
  define('ARGWA_PLUGIN_URL', plugin_dir_url( __FILE__ ));
  define('ARGWA_PLUGIN_PATH', dirname(__FILE__) . '/');
  if( wp_verify_nonce($_GET['afn'],'afn') ) { if(isset($_GET['mid'])) { argwa_add_form(); }
    else if(isset($_GET['import']) & isset($_GET['lid'])) { argwa_import_form(); }
    else if(isset($_GET['export']) & isset($_GET['lid'])) { argwa_export_form(); }
    else if(isset($_GET['lid'])) { argwa_add_form();   } else die("Direct access problem.");
  } else die("No direct access.");} else if(!isset($_POST['argwa_run'])) { argwa_responder_start();}
define('ARGWA_TEMPLATEPATH', get_stylesheet_directory().'/');
define('ARGWA_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('ARGWA_PLUGIN_PATH', dirname(__FILE__) . '/');
function argwa_query_vars( $qvars ) {$qvars[] = 'listid';$qvars[] = 'act';$qvars[] = 'Field1';$qvars[] = 'Field2';$qvars[] = 'Field3';$qvars[] = 'Field4';$qvars[] = 'Field5';$qvars[] = 'name';$qvars[] = 'email';$qvars[] = 'gwaemail';$qvars[] = 'gwaname';$qvars[] = 'argwa';$qvars[] = 'argwaeml';$qvars[] = 'argwauid'; return $qvars;}
add_filter('query_vars', 'argwa_query_vars' );
function argwa_check_vars($wp) {
global $wp_query,$wpdb,$tlid,$ttype,$rpag,$post,$s;
  if (((isset($wp_query->query_vars['gwaname']) && isset($wp_query->query_vars['gwaemail'])) || (isset($wp_query->query_vars['name']) && isset($wp_query->query_vars['email']))) && isset($wp_query->query_vars['listid']) && isset($wp_query->query_vars['act'])) {
    $dt = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $dns = gethostbyaddr($ip);
    $listid = $wp_query->query_vars['listid'];
    if(isset($wp_query->query_vars['gwaname'])) $lName = $wpdb->escape($wp_query->query_vars['gwaname']);
      else $lName = $wpdb->escape($wp_query->query_vars['name']);
    if(isset($wp_query->query_vars['gwaemail'])) $lEmail = $wpdb->escape($wp_query->query_vars['gwaemail']);
      else $lEmail = $wpdb->escape($wp_query->query_vars['email']);
    $i_url = get_bloginfo('url');
    $lField1 = mysql_real_escape_string($wp_query->query_vars['Field1']);
    $lField2 = mysql_real_escape_string($wp_query->query_vars['Field2']);
    $lField3 = mysql_real_escape_string($wp_query->query_vars['Field3']);
    $lField4 = mysql_real_escape_string($wp_query->query_vars['Field4']);
    $lField5 = mysql_real_escape_string($wp_query->query_vars['Field5']);
    $tlid=$listid;
  if(argwa_check_email($lEmail) && (isset($listid) && $listid>0)){
	$qry = "SELECT * FROM ".$wpdb->prefix."ar_gwa_leads WHERE lEmail='".$lEmail."' AND lLID='".$listid."' AND lCnf=1";
	$qry2 = "SELECT * FROM ".$wpdb->prefix."ar_gwa_leads WHERE lEmail='".$lEmail."' AND lLID='".$listid."' AND lCnf!=1";
	if($wpdb->get_results($qry)) $ttype='pErr';	else if($wpdb->get_results($qry2)) {
	$qry = "DELETE FROM ".$wpdb->prefix."ar_gwa_leads WHERE lEmail='".$lEmail."' AND lLID='".$listid."' AND lCnf != 1";
  if($wpdb->query($qry)) $ttype='pErr';
  } else {
  $dt = date('Y-m-d H:i:s');
  $ip = $_SERVER['REMOTE_ADDR'];
  $dns = gethostbyaddr($ip);
  $pword='ar';
  $vals = "ABCDEFGHIJKLMNOPQRSTUVWXYZabchefghjkmnpqrstuvwxyz0123456789";
  while (strlen($pword) < 21) {
                mt_getrandmax();  // Returns the maximum value that can be returned by a call  rand
                $num = rand(1,strlen($vals));
                $tmp = substr($vals, $num+4, 1);
                $pword = $pword . $tmp;
                $tmp ="";
  }
    $lCnf = $pword;
    $to = "$lEmail";
    $from = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ar_gwa_lists, ".$wpdb->prefix."ar_gwa_pages WHERE xID=pLID AND xID='$listid'");
    $subj = html_entity_decode(__("Confirm your subscription at",'argwa').' '.$from->xName,ENT_NOQUOTES, get_option('ar_gwa_char_'.$listid));
    $msg = $from->pConfMsg."\r\n";
    $msg .= "\r\n".__('Click to Confirm','argwa')." -> $i_url?argwa=$lCnf";
    $msg .= "\r\n"."\r\n".sprintf(__("A newsletter subscription request was received from %s (%s) on %s.",'argwa'),$ip,$dns,$dt);
    $msg .= "\r\n"."\r\n".sprintf(__("If you did not request a subscription to our newsletter please disregard this email and you will not be contacted again. For further information please visit %s.",'argwa'),get_bloginfo('url'));
    $nicename = $from->xName;

    $suc = @mail($to."\r\n",$subj,$msg."\r\n","from: {$from->xEmail}"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=".get_option('ar_gwa_char_'.$listid)."\nContent-Transfer-Encoding: ".get_option('ar_gwa_enc_'.$listid)."\n",'-f'.$from->xEmail);
    if (!$suc) $suc = @mail($to."\r\n",$subj,$msg."\r\n","from: {$from->xEmail}"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=".get_option('ar_gwa_char_'.$listid)."\nContent-Transfer-Encoding: ".get_option('ar_gwa_enc_'.$listid)."\n");
    if(!$suc) $suc = mail($to,str_replace("\r\n","\n",$subj),str_replace("\r\n","\n",$msg),"from: {$from->xEmail}"."\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=".get_option('ar_gwa_char_'.$list_id)."\nContent-Transfer-Encoding: ".get_option('ar_gwa_enc_'.$list_id)."\n");
    if(!$suc) {
    $ttype='pErr';
    } else {
		$qry = "INSERT INTO ".$wpdb->prefix."ar_gwa_leads(lLID, lName, lEmail, lDateEntry, lDateOut, lMOut, lCnf,lIP,lDNS, lField1, lField2, lField3, lField4, lField5) VALUES('".$listid."','".$lName."', '".$lEmail."' ,current_date, current_date, '-1','".$lCnf."','".$ip."','".$dns."','".$lField1."','".$lField2."','".$lField3."','".$lField4."','".$lField5."')";
		if($wpdb->query($qry)) $ttype='pSub'; else $ttype="pErr";
    }
  }
    add_action('template_redirect','response_page');
 }
}
  else  if (isset($wp_query->query_vars['argwa']))
  {
  $ttype = "pConf";
  $r = $wp_query->query_vars['argwa'];
$qry = $wpdb->get_results("SELECT lID,lLID,lEmail, xReturn,xID FROM ".$wpdb->prefix."ar_gwa_leads, ".$wpdb->prefix."ar_gwa_lists WHERE xID=lLID AND lCnf='$r'");
    if(isset($qry[0]->lID) && $qry[0]->lID>0)  {
    $dt = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $dns = gethostbyaddr($ip);
	  if(!$wpdb->query("UPDATE ".$wpdb->prefix."ar_gwa_leads SET lCnf='1',lTime='$dt',lIP='$ip',lDNS='$dns' WHERE lID='".$qry[0]->lID."'")) {
    $ttype = "pErr";
    } else {
    if((get_option('ar_gwa_drop_'.$qry[0]->lLID)=='checked')) {
     if((get_option('ar_gwa_remove_'.$qry[0]->lLID)!='checked')) {
     $wpdb->query("UPDATE ".$wpdb->prefix."ar_gwa_leads SET lCnf='2' WHERE lEmail = '".$qry[0]->lEmail."' AND lID !='".$qry[0]->lID."'");
     } else {
     $wpdb->query("DELETE FROM ".$wpdb->prefix."ar_gwa_leads WHERE lEmail = '".$qry[0]->lEmail."' AND lID !='".$qry[0]->lID."'");
     }
    }
    $ms = new ARGWAMailProcess();
    if( $ms->setLead($qry[0]->lID) && $ms->setMsg(0,$qry[0]->xID)) $ms->mailLead(0,$qry[0]->lID);
     if(get_option('ar_gwa_notify_'.$qry[0]->xID)=='checked'){
     $mto = get_bloginfo('admin_email');
     $mfrom = get_option('ar_gwa_from');
     $msubj = __("ARGWA Signup Alert",'argwa');
     $mmsg = $ms->lead['xName'] . ' : '. $ms->lead['lName'].' ('.$ms->lead['lEmail'].') : '.$dns. ' : ( '.$ip.' ) : '.$dt."\r\n\r\n";
      $sc = @mail($mto."\r\n",$msubj."\r\n",$mmsg."\r\n","from: $mfrom"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=".get_option('ar_gwa_char_'.$qry[0]->xID)."\nContent-Transfer-Encoding: ".get_option('ar_gwa_enc_'.$qry[0]->xID)."\n",'-f'.$mfrom);
      if(!$sc) $sc = @mail($mto."\r\n",$msubj."\r\n",$mmsg."\r\n","from: $mfrom"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=".get_option('ar_gwa_char_'.$qry[0]->xID)."\nContent-Transfer-Encoding: ".get_option('ar_gwa_enc_'.$qry[0]->xID)."\n");
    if(!$sc) $sc = mail($mto,$msubj,str_replace("\r\n","\n",$mmsg),"from: $mfrom\nMime-Version: 1.0\nContent-Type: text/plain; charset=".get_option('ar_gwa_char_'.$qry[0]->xID)."\nContent-Transfer-Encoding: ".get_option('ar_gwa_enc_'.$qry[0]->xID)."\n");
      }
     }
    } else $ttype = "pErr";
    add_action('template_redirect','response_page');
  }
  else if (isset($wp_query->query_vars['argwauid']) && isset($wp_query->query_vars['argwaeml']))
  {
     if((get_option('ar_gwa_remove_1')!='checked')) {
      $sql = "UPDATE ".$wpdb->prefix."ar_gwa_leads SET lCnf='2' WHERE lEmail = '".base64_decode($wp_query->query_vars['argwaeml'])."' AND lID='".$wp_query->query_vars['argwauid']."'";
    $x = $wpdb->query($sql);
     } else {
     $sql = "DELETE FROM ".$wpdb->prefix."ar_gwa_leads WHERE lEmail = '".base64_decode($wp_query->query_vars['argwaeml'])."' AND lID='".$wp_query->query_vars['argwauid']."'";
    $y = $wpdb->query($sql);
     }
    if($x || $y) $ttype = "pUnsub"; else $ttype = "pErr";
    add_action('template_redirect','response_page');
  }
}
add_action('wp','argwa_check_vars');

function argwa_get_wp_root ( $directory ) {
    global $wp_root;
    foreach( glob( $directory . "/*" ) as $f ) {
        if ( 'wp-load.php' == basename($f) ) {
            $wp_root = str_replace( "\\", "/", dirname($f) );
            return TRUE;
        }
        if ( is_dir($f) )
            $newdir = dirname( dirname($f) );
    }
    if ( isset($newdir) && $newdir != $directory ) {
        if ( argwa_get_wp_root ( $newdir ) )
            return FALSE;
    }
    return FALSE;
}

function response_page() {
global $wpdb,$ttype,$rpag,$post;
$tlid=1;
if($ttype=='pSub') $s='argwa_sr';else if($ttype=='pErr') $s='argwa_er';else if($ttype=='pConf') $s='argwa_cr';else if($ttype=='pUnsub') $s='argwa_ur';

  $rpag = get_option($s.'_url'.$tlid);
  if(preg_match("/^http/i",$rpag)) {
      header("Location: $rpag");
      exit;
  }
  else  $rpag = get_option($s.'_page'.$tlid);
  if($rpag>0) {
    $query_string = "page_id=$rpag";
    query_posts($query_string);
    wp_reset_postdata();
  }
  else {
  $rcont = $wpdb->get_var("SELECT $ttype FROM ".$wpdb->prefix."ar_gwa_pages WHERE pLID=1");
  $post->post_content = $rcont;
  $post->post_title ='';
  $tpl = get_option($s.'_tpl'.$tlid);
  if(!$tpl || $tpl=='') $tpl = 'page.php';
  include (ARGWA_TEMPLATEPATH . $tpl);
  exit;
  }
}

function argwa_check_email($email)
{
    $atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
    $domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    return eregi("^$atom+(\\.$atom+)*@($domain?\\.)+$domain\$", $email);
}

class argwa_metabox_plugin {
var $ssd;
var $help;
var $gwa_version_tag;

function argwa_metabox_plugin() {
add_action('init',array(&$this, 'argwa_process_fields'));add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);add_action('admin_menu', array(&$this, 'on_admin_menu'));add_action('admin_post_save_argwa_metaboxes_general', array(&$this, 'on_save_changes'));$export_separator = "\t"; $this->gwa_version_tag = $gwa_version_tag = '4.0.6';@include (ARGWA_PLUGIN_PATH."argwa_help.php");if(function_exists('argwa_help')) $this->help = 1;
}

function on_screen_layout_columns($columns, $screen) {
    $columns[$this->pagehook] = 2;
    return $columns;
}
function on_admin_menu() {
$this->pagehook = add_menu_page('ARGWA Free', "ARGWA Free", 'manage_options', ARGWA_PAGE, array(&$this, 'argwa_show_page'));
add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
}

function on_load_page() {
global $wpdb;
include_once(ABSPATH . WPINC . '/rss.php');
wp_enqueue_script('common');
wp_enqueue_script('wp-lists');
wp_enqueue_script('postbox');
wp_enqueue_script('wp-pointer');
wp_enqueue_script( 'jquery-ui-demo', ARGWA_PLUGIN_URL.'js/jquery-ui-argwa.js', array( 'postbox','jquery-ui-core','jquery-ui-dialog','jquery-ui-tabs','jquery-ui-slider' ) );
wp_enqueue_script( 'jquery-ui-wysiwyg', ARGWA_PLUGIN_URL.'js/elrte.min.js');
wp_enqueue_style ( 'jquery-ui-wcss', ARGWA_PLUGIN_URL.'css/elrte.min.css' );
wp_enqueue_style ( 'jquery-ui-demo', ARGWA_PLUGIN_URL.'css/jquery-ui-demo.css' );
wp_enqueue_style ( 'jquery-ui-css', ARGWA_PLUGIN_URL.'css/jquery-ui-fresh.css' );
wp_enqueue_style('wp-pointer');
add_meta_box('argwa-metaboxes-sidebox-2', __('Support :: Using the Plugin','argwa'), array(&$this, 'on_sidebox_1_content'), $this->pagehook, 'side', 'core');
add_meta_box('argwa-metaboxes-sidebox-3', __('News, Tips & Updates','argwa'), array(&$this, 'on_sidebox_2_content'), $this->pagehook, 'side', 'core');
add_meta_box('argwa-metaboxes-sidebox-4', __('Recent Testimonials','argwa'), array(&$this, 'on_sidebox_3_content'), $this->pagehook, 'side', 'core');
}

function print_gwaCheck($lst_num) {
$script = "<script type='text/javascript'>// <![CDATA[\n" . 'function gwaCheckForm'.$lst_num.'(form){nam = form.argwa_name.value;namcnt = nam.length;eml = form.argwa_email.value;if(typeof form.argwa_field1!="undefined"){ lField1 = form.argwa_field1.value; Field1 = lField1.length }if(typeof form.argwa_field2!="undefined"){ lField2 = form.argwa_field2.value; Field2 = lField2.length }if(typeof form.argwa_field3!="undefined"){ lField3 = form.argwa_field3.value; Field3 = lField3.length }if(typeof form.argwa_field4!="undefined"){ lField4 = form.argwa_field4.value; Field4 = lField4.length }if(typeof form.argwa_field5!="undefined"){ lField5 = form.argwa_field5.value; Field5 = lField5.length }if(namcnt < 2) {alert("'.__("Sorry, Name field must be 2 characters minimum.",'argwa').'");return false;}if ((typeof form.argwa_field1!="undefined")) { if((Field1 < 2)) {alert("'.__("Sorry, Field 1 must be 2 characters minimum.",'argwa').'");return false;}}if((typeof form.argwa_field2!="undefined")) { if((Field2 < 2)) {alert("'.__("Sorry, Field 2 must be 2 characters minimum.",'argwa').'");return false;}}if((typeof form.argwa_field3!="undefined")) { if((Field3 < 2)) {alert("'.__("Sorry, Field 3 must be 2 characters minimum.",'argwa').'");return false;}}if((typeof form.argwa_field4!="undefined")) { if((Field4 < 2)) {alert("'.__("Sorry, Field 4 must be 2 characters minimum.",'argwa').'");return false;}}if((typeof form.argwa_field5!="undefined")) { if ((Field5 < 2)) {alert("'.__("Sorry, Field 5 must be 2 characters minimum.",'argwa').'");return false;}}return(echeck(eml));}function echeck(str) {var at="@";var dot=".";var lat=str.indexOf(at);var lstr=str.length;var ldot=str.indexOf(dot);if (str.indexOf(at)==-1){alert("Invalid Email Address. ");return false}if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){alert("'.__("Invalid Email Address.",'argwa')."&nbsp;".'");return false}if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){alert("'.__("Invalid Email Address.",'argwa')."&nbsp;".'");return false}if (str.indexOf(at,(lat+1))!=-1){alert("'.__("Invalid Email Address.",'argwa')."&nbsp;".'");return false}if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){alert("'.__("Invalid Email Address.",'argwa')."&nbsp;".'");return false}if (str.indexOf(dot,(lat+2))==-1){alert("'.__("Invalid Email Address.",'argwa')."&nbsp;".'");return false}if (str.indexOf(" ")!=-1){alert("'.__("Invalid Email Address.",'argwa').'");return false}return true;} '."\n// ]]></script>";
return $script;
}

function argwa_form_display($listid,$st=NULL) {
global $wpdb;
$display = preg_replace("/{id}/",'1',stripslashes($wpdb->get_var("SELECT xSubscribe FROM ".$wpdb->prefix."ar_gwa_lists WHERE xID='1'")));if ($st==1) if(get_option('ar_gwa_cb_1')=='checked') $display .= '<div class="arlink"><a class="arlink" href="http://www.freeautoresponder.biz/?/'.get_option('ar_gwa_aff_id').'" target="_blank">'.get_option('ar_gwa_aff_text').'</a> courtesy of <a href="'.get_option('ar_gwa_aff_url').'" target="_blank">'.get_option('ar_gwa_aff_title').'</a>.</div>';if ($st==1) return $this->print_gwaCheck(1).$display; else return $display;
}

function do_list_confirm_msg($list_id) {
global $wpdb;
$rec = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_pages WHERE pLID='".$list_id."'",ARRAY_A);
?>
<div class="postbox " id="argwa-metaboxes-confbox-<?php echo $list_id;?>" style="max-height:500px;">
<div title="<?php _e("Click to toggle",'argwa');?>" class="handlediv"><br></div><h3 class="hndle"><?php _e("Confirm Message Editor",'argwa');?></h3><div class="inside"><p><?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help15<?php echo $list_id;?>" class="argwahelp2"/><?php } _e("This is the confirm message content sent with a personal confirm link appended to the bottom when a person subscribes. It is a plain text formatted message and further modification is possible by creating a custom translation file for this plugin.",'argwa');?></p><textarea name="<?php echo 'mConfForm'.$list_id;?>" style="width:98%;height:108px;"><?php echo $rec[0]['pConfMsg'];?></textarea><p style="text-align:right;"><input type="button" id="argwa_confirm_message_<?php echo $list_id;?>" name="argwa_confirm_message" class="button" value="<?php _e("Save Changes",'argwa');?>"></p></div>
</div>
<?php wp_nonce_field('argwa_confirm_message','argwa_confirm_message_nonce');?>
<script type = "text/javascript">
jQuery(document).ready(function($) {
            //create a new field then append it before the add field button
            jQuery("#argwa_confirm_message_<?php echo $list_id;?>").live('click', function(){
                  var new_field5 = " <input type='hidden' name='Llid' value='<?php echo $list_id;?>' / >";
                  var new_field6 = " <input type='hidden' name='argwa_confirm_message' value='1' / >";
                  jQuery(this).before(new_field5);
                  jQuery(this).before(new_field6);
            jQuery('#admin_post_php').submit();
            });
});
</script>
<?php
}

function do_list_form_box($list_id) {
?>
<div class="postbox " id="argwa-metaboxes-formbox-<?php echo $list_id;?>" style="max-height:600px;">
<div title="<?php _e("Click to toggle",'argwa');?>" class="handlediv"><br></div><h3 class="hndle"><?php _e("Subscription Form Editor",'argwa');?></h3>
<div class="inside"><p><?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help9<?php echo $list_id;?>" class="argwahelp2"/><?php } _e("Switch to Editor Source Tab to Copy&Paste the subscription form html code to display your subscription form anywhere online.",'argwa');?></p><p><textarea id="mSubForm<?php echo $list_id;?>" name="mSubForm<?php echo $list_id;?>"><?php echo $this->argwa_form_display($list_id);?></textarea><br><?php _e("When using the subscription form on an external website the script below must be present in the page header for javascript based field checking to work properly with this form and prevent blank form submission. It is automatically included with the widgetized form.",'argwa');?><br><textarea style="width:98%;height:60px;"><?php echo $this->print_gwaCheck($list_id);?></textarea></p><p style="text-align:right;"><input type="button" name="argwa_form_reset" id="argwa_form_reset_<?php echo $list_id;?>" class="button" value="Reset Form to Default">&nbsp;&nbsp;&nbsp;<input type="button" name="argwa_form_page" id="argwa_form_page_<?php echo $list_id;?>" class="button" value="Save Changes"></p></div>
	<script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function() {
			var opts = {
				cssClass : 'el-rte',
				lang     : 'en',
				height   : 200,
				width     : 739,
				toolbar  : 'maxi',
				cssfiles : ['css/elrte-inner.css']
			}
			jQuery('#mSubForm<?php echo $list_id;?>').elrte(opts);

		})
	</script>
</div>
<?php wp_nonce_field('argwa_form_page','argwa_form_page_nonce');?>
<script type = "text/javascript">
jQuery(document).ready(function($) {
            //create a new field then append it before the add field button
            jQuery("#argwa_form_page_<?php echo $list_id;?>").live('click', function(){
                  var new_field7 = " <input type='hidden' name='Llid' value='<?php echo $list_id;?>' / >";
                  var new_field8 = " <input type='hidden' name='argwa_form_page' value='1' / >";
                  jQuery(this).before(new_field7);
                  jQuery(this).before(new_field8);
            jQuery('#admin_post_php').submit();
            });
            jQuery("#argwa_form_reset_<?php echo $list_id;?>").live('click', function(){
              if(confirm('Are you sure you want to reset the subscription form to default? All changes will be lost.')) {
                    var new_field17 = " <input type='hidden' name='Llid' value='<?php echo $list_id;?>' / >";
                    var new_field18 = " <input type='hidden' name='argwa_form_reset' value='1' / >";
                    jQuery(this).before(new_field17);
                    jQuery(this).before(new_field18);
              jQuery('#admin_post_php').submit();
              }
            });
});
</script>
<?php
}

function do_list_leads_box($list_id) {
global $wpdb;
$orig_order_by = $orderby = strip_tags($_REQUEST['orderby']);
if(!isset($orderby) || $orderby=='') $orderby = 'lName';
if($_REQUEST['dir']) {
  $dir = 0;
  $orderby .= ' desc';
  } else {
  $dir=1;
  $orderby .= ' asc';
  }
if($list_num<1) $list_num = 25;
$this_start_num = $start_num = (int)$_REQUEST['start_num'];
if($_REQUEST['SUBMIT']=='FILTER') $this_start_num = $start_num = 0;
$finish_num = $start_num+$list_num;
if($_POST['argwa_list_type']=='-1') {
$qual = 'WHERE lCnf != 1 AND lCnf != 2';
}else if($_POST['argwa_list_type']=='-2') {
$qual = 'WHERE lCnf = 2';
}else {
$qual = 'WHERE lCnf = 1';
}
$qual .= " AND lLID='".$list_id."'";
$arCnt = "SELECT COUNT(lID) FROM " . $wpdb->prefix . "ar_gwa_leads $qual";
$lCnt = $wpdb->get_var($arCnt);
if($list_num>=(int)$lCnt) { $list_num=$lCnt;$finish_num=$lCnt; }
$arNum = "SELECT COUNT(*) FROM " . $wpdb->prefix . "ar_gwa_leads ".$qual;
$rev = $wpdb->get_var($arNum);
#echo $arNum.$rev.$list_num;
if($_POST['ar_gwa_fwd'] && $rev>=$list_num) $start_num = $start_num+$list_num;
else if($_POST['ar_gwa_back']) $start_num = $start_num-$list_num;
if($start_num<0 || $start_num<$list_num) $start_num=0;
if($_POST['argwa_list_type']=='-1') {
$qual = 'WHERE lCnf != 1 AND lCnf != 2';
$list_num = $lCnt;
$sel1 = ' selected';
}else if($_POST['argwa_list_type']=='-2') {
$qual = 'WHERE lCnf = 2';
$sel2 = ' selected';
} else {
$qual = 'WHERE lCnf = 1';
$sel = ' selected';
}
$qual .= " AND lLID='".$list_id."'";
if(isset($_POST['argwa_search_button']) && $skey=$_POST['argwa_search']) $qual .= " AND lName like \"%$skey%\" OR lEmail like \"%$skey%\"";
$arSql = "SELECT * FROM " . $wpdb->prefix . "ar_gwa_leads $qual ORDER BY ".$orderby." LIMIT $start_num,$list_num";
$res = $wpdb->get_results($arSql);
//ORDERBY
if(($start_num) == 0) $no_back=1;
$prev_start_num = $start_num - $list_num;
$back='?page=argwa_page&orderby='.$orig_order_by.'&listid='.$list_id.'&start_num='.$prev_start_num.'&list_num='.$list_num;
if(($start_num+$list_num) >= $lCnt) $no_fwd=1;
$fwd='?page=argwa_page&orderby='.$orig_order_by.'&listid='.$list_id.'&start_num='.($start_num+$list_num).'&list_num='.$list_num; # added +$listnum for $fwd ??
if($lCnt>0)
  $pNum = ($lCnt/$list_num);
  for($i=0;$i<(int)$pNum;$i++) {
    $start_num = $list_num * $i;
    if($start_num == $this_start_num) $color = 'color:#f00;text-decoration:none;';
     else $color='color:#00f;text-decoration:underline;';
    $pLink .= '<a href="?page=argwa_page&orderby='.$orig_order_by.'&listid='.$list_id.'&start_num='.$start_num.'&list_num='.$list_num.'" style="font-size:11pt;font-weight:bolder;'.$color.'">'.($i+1).'</a>'."&nbsp;&nbsp;";
  }
  if($list_num<25) $list_num=25;
?>
<div class="postbox " id="argwa-metaboxes-detailbox2" style="max-height:400px;overflow:auto;">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><?php _e("Newsletter Subscriber List",'argwa');?> (<?php echo $lCnt;?> Lead<?php if($lCnt!=1) echo 's';
echo ')&nbsp;&nbsp;&nbsp;<a id="dialog_import_'.$list_id.'" href="javascript:void(0)" class="button">'.__("Import New Leads",'argwa').'</a>&nbsp;&nbsp;<a id="dialog_export_'.$list_id.'" href="javascript:void(0)" class="button">'.__("Export All Leads",'argwa').'</a>';
?></h3>
<div class="inside"><?php _e("Search",'argwa');?>:&nbsp;<input type="text" name="argwa_search" value="<?php echo $skey;?>" style="color:#666;"><select style="font-size:10pt;" id="argwa_list_type" name="argwa_list_type"><option value="0"<?php echo $sel;?>><?php _e("Active Leads",'argwa');?></option><option value="-1"<?php echo $sel1;?>><?php _e("Unconfirmed",'argwa');?></option><option value="-2"<?php echo $sel2;?>><?php _e("Removed",'argwa');?></option></select> <?php _e("Show",'argwa');?>&nbsp;<input type="textbox" size="2" name="list_num" value="<?php echo $list_num;?>">&nbsp;<?php _e("Records per Page",'argwa');?>.&nbsp;<input type="submit" name="argwa_search_button" value="<?php _e("Search Leads List",'argwa');?>" class="button"><?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help8<?php echo $list_id;?>" class="argwahelp2"/><?php } ?><input type="hidden" name="start_num" value="<?php echo $start_num;?>"><input type="hidden" name="dir" value="<?php echo $_REQUEST['dir']?>"><?php wp_nonce_field('argwa_delete_leads','argwa_delete_leads_nonce');?><?php wp_nonce_field('argwa_resend_confirm','argwa_resend_confirm_nonce');?><input type="hidden" name="listid" value="<?php echo $list_id;?>">
<style type="text/css">
.widefat tbody th.check-column {
    padding: 9px 0 2px;
    vertical-align:middle;
}
</style>
<table class="widefat">
	<thead>
	<tr>
	<th scope="col" class="check-column"><input type="checkbox" onclick="checkAll(document.getElementById('leads-filter'));" /></th>
	<th scope="col" width="120"><a href="?page=argwa_page&orderby=lName&dir=<?php echo $dir;?>&listid=<?php echo $list_id?>&start_num=<?php echo $start_num?>&list_num=<?php echo $list_num?>"><?php _e("Name",'argwa');?></a></th>
	<th scope="col" width="200"><a href="?page=argwa_page&orderby=lEmail&dir=<?php echo $dir;?>&listid=<?php echo $list_id?>&start_num=<?php echo $start_num?>&list_num=<?php echo $list_num?>"><?php _e("Email",'argwa');?></a></th>
	<th scope="col" width="70"><a href="?page=argwa_page&orderby=lMOut&dir=<?php echo $dir;?>&listid=<?php echo $list_id?>&start_num=<?php echo $start_num?>&list_num=<?php echo $list_num?>"><?php _e("Msg Out",'argwa');?></th>
	<th scope="col"><a href="?page=argwa_page&orderby=lDateEntry&dir=<?php echo $dir;?>&listid=<?php echo $list_id?>&start_num=<?php echo $start_num?>&list_num=<?php echo $list_num?>"><?php _e("Subscribe Date",'argwa');?></th>
	<th scope="col"><a href="?page=argwa_page&orderby=lDateOut&dir=<?php echo $dir;?>&listid=<?php echo $list_id?>&start_num=<?php echo $start_num?>&list_num=<?php echo $list_num?>"><?php _e("Last Date Sent",'argwa');?></th>
	</tr>
	</thead>
	<tbody>
	<tr id='post-1' class='alternate author-self status-publish' valign="top">
<?php
if($res) {
	foreach($res as $row)
	{
$ooid = $row->lID;
if($_POST['argwa_list_type']=='-1') $elink = '<a onclick="return confirm(\''.__("Resend confirm link. Are you sure?",'argwa').'\');" title="'.__("Send confirm message again",'argwa').'"><input type="submit" id="reSend" style="background-image:url('.ARGWA_PLUGIN_URL.'/images/email_icon.gif);background-color: transparent;border:none;background-repeat:no-repeat;color:transparent;" name="argwa_resend_confirm" value="'.$ooid.'"></a>';
$link = '<a title="'.__("Show/Hide additional fields",'argwa').'" style="font-size:15pt;" href="javascript:void(0)" onclick="if(document.getElementById(\'gwatable'.$ooid.'\').style.display==\'inline\') document.getElementById(\'gwatable'.$ooid.'\').style.display=\'none\'; else document.getElementById(\'gwatable'.$ooid.'\').style.display=\'inline\';">+</a>';
    $row->lLID = argwa_get_listname($row->lLID);
		echo '<tr><th scope="row" class="check-column"><input type="checkbox" name="lID[]" value="'.$row->lID.'" /></th>'
				.'<td id="tdname'.$ooid.'"><input  onblur="sss = confirm(\''.__("Confirm changes?",'argwa').'\');if(sss==true)ar_gwa_name_update(document.getElementById(\'argwaleadname'.$ooid.'\'),'.$ooid.');return false;" type="text" name="lLead" id="argwaleadname'.$ooid.'" value="'.$row->lName.'" size="12">&nbsp;'.$link.'</td>'
#				.'<td><abbr title="'.$row->lEmail.'">'.$link.'</abbr></td>'
				.'<td id="tdemail'.$ooid.'"><input  onblur="sss = confirm(\''.__("Confirm changes?",'argwa').'\');if(sss==true)ar_gwa_email_update(document.getElementById(\'argwaleademail'.$ooid.'\'),'.$ooid.',\''.$row->lEmail.'\');return false;" type="text" name="lEmail" id="argwaleademail'.$ooid.'" value="'.$row->lEmail.'" size="22">'.$elink.'</td>'
				.'<td id="tdday'.$ooid.'"><input  onblur="sss = confirm(\''.__("Confirm changes?",'argwa').'\');if(sss==true)ar_gwa_day_update(document.getElementById(\'argwaleadday'.$ooid.'\'),'.$ooid.',\''.$row->lEmail.'\');return false;" type="text" name="lMOut" id="argwaleadday'.$ooid.'" value="'.$row->lMOut.'" size="3"></td>'
				.'<td id="tdentry'.$ooid.'">'.$row->lDateEntry.'</td>'
				.'<td>'.$row->lDateOut.'</td></tr>';
echo '<tr><td colspan=7><table style="display:none;" id="gwatable'.$row->lID.'" width="100%"><tr><td><textarea style="display:none;" id="fieldOne'.$ooid.'" onblur="sss = confirm(\''.__("Confirm changes?",'argwa').'\');if(sss==true)ar_gwa_fields_update('.$ooid.',document.getElementById(\'fieldOne'.$ooid.'\'),document.getElementById(\'fieldTwo'.$ooid.'\'),document.getElementById(\'fieldThree'.$ooid.'\'),document.getElementById(\'fieldFour'.$ooid.'\'),document.getElementById(\'fieldFive'.$ooid.'\'));return false;" rows=1 cols=10>
'.$row->lField1.'</textarea>'.$row->lField1.'&nbsp;(<a title="Edit" href="javascript:void(0)" onclick="if(document.getElementById(\'fieldOne'.$ooid.'\').style.display==\'inline\') document.getElementById(\'fieldOne'.$ooid.'\').style.display=\'none\'; else document.getElementById(\'fieldOne'.$ooid.'\').style.display=\'inline\';">+</a>)
</td><td>
<textarea style="display:none;" id="fieldTwo'.$ooid.'" onblur="sss = confirm(\''.__("Confirm changes?",'argwa').'\');if(sss==true)ar_gwa_fields_update('.$ooid.',document.getElementById(\'fieldOne'.$ooid.'\'),document.getElementById(\'fieldTwo'.$ooid.'\'),document.getElementById(\'fieldThree'.$ooid.'\'),document.getElementById(\'fieldFour'.$ooid.'\'),document.getElementById(\'fieldFive'.$ooid.'\'));return false;" rows=1 cols=10>
'.$row->lField2.'
</textarea>'.$row->lField2.'&nbsp;(<a title="Edit" href="javascript:void(0)" onclick="if(document.getElementById(\'fieldTwo'.$ooid.'\').style.display==\'inline\') document.getElementById(\'fieldTwo'.$ooid.'\').style.display=\'none\'; else document.getElementById(\'fieldTwo'.$ooid.'\').style.display=\'inline\';">+</a>)
</td><td>
<textarea style="display:none;" id="fieldThree'.$ooid.'" onblur="sss = confirm(\''.__("Confirm changes?",'argwa').'\');if(sss==true)ar_gwa_fields_update('.$ooid.',document.getElementById(\'fieldOne'.$ooid.'\'),document.getElementById(\'fieldTwo'.$ooid.'\'),document.getElementById(\'fieldThree'.$ooid.'\'),document.getElementById(\'fieldFour'.$ooid.'\'),document.getElementById(\'fieldFive'.$ooid.'\'));return false;" rows=1 cols=10>
'.$row->lField3.'
</textarea>'.$row->lField3.'&nbsp;(<a title="Edit" href="javascript:void(0)" onclick="if(document.getElementById(\'fieldThree'.$ooid.'\').style.display==\'inline\') document.getElementById(\'fieldThree'.$ooid.'\').style.display=\'none\'; else document.getElementById(\'fieldThree'.$ooid.'\').style.display=\'inline\';">+</a>)
</td><td>
<textarea style="display:none;" id="fieldFour'.$ooid.'" onblur="sss = confirm(\''.__("Confirm changes?",'argwa').'\');if(sss==true)ar_gwa_fields_update('.$ooid.',document.getElementById(\'fieldOne'.$ooid.'\'),document.getElementById(\'fieldTwo'.$ooid.'\'),document.getElementById(\'fieldThree'.$ooid.'\'),document.getElementById(\'fieldFour'.$ooid.'\'),document.getElementById(\'fieldFive'.$ooid.'\'));return false;" rows=1 cols=10>'.$row->lField4.'</textarea>'.$row->lField4.'&nbsp;(<a title="Edit" href="javascript:void(0)" onclick="if(document.getElementById(\'fieldFour'.$ooid.'\').style.display==\'inline\') document.getElementById(\'fieldFour'.$ooid.'\').style.display=\'none\'; else document.getElementById(\'fieldFour'.$ooid.'\').style.display=\'inline\';">+</a>)</td><td><textarea style="display:none;" id="fieldFive'.$ooid.'" onblur="sss = confirm(\''.__("Confirm changes?",'argwa').' '.$row->lEmail.'?\');if(sss==true)ar_gwa_fields_update('.$ooid.',document.getElementById(\'fieldOne'.$ooid.'\'),document.getElementById(\'fieldTwo'.$ooid.'\'),document.getElementById(\'fieldThree'.$ooid.'\'),document.getElementById(\'fieldFour'.$ooid.'\'),document.getElementById(\'fieldFive'.$ooid.'\'));return false;" rows=1 cols=10>'.$row->lField5.'</textarea>'.$row->lField5.'&nbsp;(<a title="Edit" href="javascript:void(0)" onclick="if(document.getElementById(\'fieldFive'.$ooid.'\').style.display==\'inline\') document.getElementById(\'fieldFive'.$ooid.'\').style.display=\'none\'; else document.getElementById(\'fieldFive'.$ooid.'\').style.display=\'inline\';">+</a>)</td></tr></table></td></tr>';
  }
} else {
 echo '<tr><td colspan=7 align="center">'.__("No Records",'argwa').'</td></tr>';
}
?></tbody></table><input type="submit" class="button" name="argwa_delete_leads" value="<?php _e("Delete Leads",'argwa');?>" style="float:left;margin-top:5px;"/><p style="width:90%;text-align:center;">
<?php _e("Pages",'argwa');?>: <?php if(!$no_back) { ?><a href="<?php echo $back?>" style="text-decoration:none;font-weight:bolder;">&lt;&lt;</a><?php } ?>&nbsp;<?php echo $pLink;?>&nbsp;<?php if(!$no_fwd) { ?><a href="<?php echo $fwd?>"  style="text-decoration:none;font-weight:bolder;">&gt;&gt;</a><?php } ?></p>
</div></div>
<?php
}

function do_list_messages_box($l) {
global $wpdb;
?>
<div class="postbox " id="argwa-metaboxes-detailbox4"  style="overflow:auto;max-height:200px;">
<?php
echo '<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle">Newsletter Autoresponder Messages <a id="dialog_link_0_'.$l.'" href="javascript:void(0)" class="button">'.__("Add New Autoresponder Message",'argwa').'</a>&nbsp;&nbsp;<a  id="dialog_send_'.$l.'" href="javascript:void(0)" class="button">'.__("Send Instant Broadcast Message",'argwa').'</a></h3>';
?>
  <div class="inside">
<table class="widefat wp-list-table">
	<thead>
	<tr>
	<th scope="col" class="check-column" align="left"><input type="checkbox" onclick="checkAll(document.getElementById('armsgs-filter'));" /></th>
	<th scope="col" width="60"><?php _e("Day",'argwa');?></th>
  <?php echo '<th scope="col">'.__("Subject",'argwa'); if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help7<?php echo $l;?>" class="argwahelp2"/><?php } ?></th>
	</tr>
	</thead>
	<tbody>
    <?php
$qual = "WHERE mLID='".$l."'";
$arSql = "SELECT * FROM " . $wpdb->prefix . "ar_gwa_msg $qual ORDER BY mDay";
$res = $wpdb->get_results($arSql);
if($res)
{
	echo '';
?>
<?php
	foreach($res as $row)
	{
		echo '<tr><td><input type="checkbox" name="mID[]" value="'.$row->mID.'"></td><td align="left">';
		echo $row->mDay,$row->mDate.'</td><td>';
		echo '<a id="dialog_link_'.$row->mID.'" href="javascript:void(0)" title="'.__("Click to edit message",'argwa').'">'.htmlentities(stripslashes($row->mSubject)),'</a></td>';
?>
<?php
	}
  } else {
  echo '<tr><td colspan=3 align="center">'.__("No Records",'argwa').'</td></tr>';
  }
?></table>
<script>
jQuery(document).ready(function($) {
	$('#dialog_link_0_<?php echo $l;?>').click(function(){
    $( "#dialog" ).dialog( "option", "title", 'Add New Autoresponder Message' );
		$('#dialog').load("<?php echo ARGWA_PLUGIN_URL;?>ar_gwa.php?afn=<?php echo wp_create_nonce('afn');?>&lid=<?php echo $l;?>").dialog('open');
    return false;
	});
	$('#dialog_send_<?php echo $l;?>').click(function(){
    $( "#dialog" ).dialog( "option", "title", 'Send Instant Broadcast Message' );
		$('#dialog').load("<?php echo ARGWA_PLUGIN_URL;?>ar_gwa.php?afn=<?php echo wp_create_nonce('afn');?>&lid=<?php echo $l;?>&argwasend=1").dialog('open');
    return false;
	});
<?php
	foreach($res as $row) {
?>	$('#dialog_link_<?php echo $row->mID;?>').click(function(){
    $( "#dialog" ).dialog( "option", "title", 'Update Autoresponder Message' );
		$('#dialog').load("<?php echo ARGWA_PLUGIN_URL;?>ar_gwa.php?afn=<?php echo wp_create_nonce('afn');?>&mid=<?php echo $row->mID;?>").dialog('open');
    return false;
	});
  <?php } ?>
	$('#dialog_import_<?php echo $l;?>').click(function(){
    $( "#dialog" ).dialog( "option", "title", 'Import Leads' );
		$('#dialog').load("<?php echo ARGWA_PLUGIN_URL;?>ar_gwa.php?afn=<?php echo wp_create_nonce('afn');?>&lid=<?php echo $l;?>&import=1").dialog('open');
    return false;
	});
	$('#dialog_export_<?php echo $l;?>').click(function(){
    $( "#dialog" ).dialog( "option", "title", 'Export Leads' );
		$('#dialog').load("<?php echo ARGWA_PLUGIN_URL;?>ar_gwa.php?afn=<?php echo wp_create_nonce('afn');?>&lid=<?php echo $l;?>&export=1").dialog('open');
    return false;
	});
});
</script>
<input type="submit" class="button" name="argwa_delete_messages" value="<?php _e("Delete Messages",'argwa');?>" style="margin:5px;"/>
<?php wp_nonce_field('argwa_delete_messages','argwa_delete_messages_nonce');?></div>
</div>
<?php
}

function do_list_detail_box($l) {
global $wpdb;
if(get_option('ar_gwa_char_'.$l)=='UTF-8') $ch1='selected'; else
if(get_option('ar_gwa_char_'.$l)=='Windows-1255') $ch3='selected'; else
if(get_option('ar_gwa_char_'.$l)=='GB2312') $ch4='selected'; else
if(get_option('ar_gwa_char_'.$l)=='ISO-8859-2') $ch6='selected'; else
if(get_option('ar_gwa_char_'.$l)=='ISO-2022-JP') $ch5='selected'; else $ch2='selected';
?>
<div class="postbox closed" id="argwa-metaboxes-detailbox2">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><?php _e("List Details",'argwa'); ?>.</h3>
  <div class="inside">
<?php
$rec = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_lists WHERE xID='".$l."'");
?><?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help5<?php echo $l;?>" class="argwahelp2"/><?php } ?><div style="width:49%;float:left;"><p><?php _e("Newsletter Name",'argwa'); ?>:<br><input style="border:solid 1px #21759b" type="text" name="xName_<?php echo $l;?>" size="49" value="<?php echo $rec[0]->xName;?>"><p>&quot;From:&quot; <?php _e("Email Address",'argwa'); ?>:<br><input style="border:solid 1px #21759b" type="text" name="xEmail_<?php echo $l;?>" size="49" value="<?php echo $rec[0]->xEmail;?>">
<p><input type="checkbox" name="ar_gwa_notify_<?php echo $l;?>" value="checked" <?php echo get_option('ar_gwa_notify_'.$rec[0]->xID);?> > <?php _e("Send Admin an Email on all Subscription Confirmations.",'argwa'); ?>
<p><input type="checkbox" name="ar_gwa_addsub_<?php echo $l;?>" value="checked" <?php echo get_option('ar_gwa_addsub_'.$rec[0]->xID);?> > <?php _e("Include Unsubscribe Link automatically in messages.",'argwa'); ?>
<p><input type="checkbox" name="ar_gwa_remove_<?php echo $l;?>" value="checked"  <?php echo get_option('ar_gwa_remove_'.$rec[0]->xID);?> > <?php _e("Delete on unsubscribe (otherwise block from import.)",'argwa');
if(get_option('ar_gwa_aff_id')==1){?><p><input type="checkbox" name="ar_gwa_cb_<?php echo $l;?>" value="checked"  <?php echo get_option('ar_gwa_cb_'.$rec[0]->xID);?> > <?php _e("Display the plugin sponsor link on form & email.",'argwa'); }?></div><div style="width:49%;float:right;"><strong style="color:#000;font-size:11pt;"><?php _e("Shortcode",'argwa'); ?>:&nbsp;[GWAR]</strong>
<?php if(!(get_option('ar_gwa_reg_optinb_'.$rec[0]->xID)) && (!(get_option('ar_gwa_reg_optin_'.$rec[0]->xID)))) $ropt = 'checked';?><div style="line-height:25px;margin-top:10px;">
<input type="radio" name="ar_gwa_optin_<?php echo $l;?>" value="X"  <?php echo $ropt;?> >&nbsp;<?php _e("Do not subscribe new users on Registration Page",'argwa'); ?><br>
<input type="radio" name="ar_gwa_optin_<?php echo $l;?>" value="checked"  <?php echo get_option('ar_gwa_reg_optin_'.$rec[0]->xID);?> >&nbsp;<?php _e("Display Opt-in Checkbox on User Registration Page",'argwa'); ?><br>
<input type="radio" name="ar_gwa_optin_<?php echo $l;?>" value="selected"  <?php echo get_option('ar_gwa_reg_optinb_'.$rec[0]->xID);?> >&nbsp;<?php _e("Automatically subscribe new blog users to this list!",'argwa'); ?></div>
<?php
$wrap = get_option('ar_gwa_wrap_'.$rec[0]->xID);
echo '<p><table><tr><td>'.__("Email Text Wrapping",'argwa').': <input type="text" size="2" value="'.$wrap.'" name="ar_gwa_wrap_'.$l.'">&nbsp;<small>'.__("Characters per line - PLAIN-TEXT",'argwa').'</small></td></tr><tr><td>Email Character Set (CHAR-SET)&nbsp;<select name="ar_gwa_char_'.$l.'"><option value="utf" '.$ch1.'>UTF-8</option><option value="iso" '.$ch2.'>ISO-8859-1</option><option value="iso2" '.$ch6.'>ISO-8859-2</option><option value="win" '.$ch3.'>Windows-1255</option><option value="gbk" '.$ch4.'>GB2312</option><option value="jpn" '.$ch5.'>ISO-2022-JP</option></select>&nbsp<small>('.get_option('ar_gwa_enc_'.$l).')</small></td></tr></table></div><div style="clear:both"></div>';
?>
<p style="width:100%;text-align:right;margin:0;"><input id="update_list_details_<?php echo $l;?>" type="button" class="button" value="<?php _e("Update List Details",'argwa');?>" name="argwa_update_details"></p><?php wp_nonce_field('argwa_update_details','argwa_update_details_nonce');?>

<script type = "text/javascript">
jQuery(document).ready(function($) {
            //create a new field then append it before the add field button
            jQuery("#update_list_details_<?php echo $l;?>").live('click', function(){
                  var new_field = " <input type='hidden' name='Llid' value='<?php echo $l;?>' / >";
                  var new_field2 = " <input type='hidden' name='argwa_update_details' value='1' / >";
                  jQuery(this).before(new_field);
                  jQuery(this).before(new_field2);
            jQuery('#admin_post_php').submit();
            });
});
</script>
</div>
</div>
<?php
}

function do_list_subscribe_box($l) {
global $wpdb;
$rec = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_pages WHERE pLID='".$l."'",ARRAY_A);
?>
<div class="postbox closed" id="argwa-metaboxes-detailbox4">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle">Subscribe Page Content / Forward Page Settings</h3>
  <div class="inside">
  <?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help10<?php echo $l;?>" class="argwahelp2"/><?php } echo __("Enter an External Page URL forward to be shown after subscribing.",'argwa');?> <input type="text" name="argwa_sr_url<?php echo $l;?>" value="<?php if($g = (get_option('argwa_sr_url'.$l))) echo $g; else echo 'http://';?>" size="50"><br><?php  echo __("Select a page from your blog to display after subscribing.",'argwa').' '; wp_dropdown_pages(array('depth' => 0, 'child_of' => 0, 'selected' => get_option('argwa_sr_page'.$l), 'echo' => 1,'name' => 'argwa_sr_page'.$l, 'show_option_none' => __("Select a Public Blog Page",'argwa'))); ?><br />
<?php
   _e("Select a Template to display with content below.",'argwa').' ';
   $templates = get_page_templates();
    $tpl = get_option('argwa_sr_tpl'.$l);
   foreach ( $templates as $template_name => $template_filename ) {
    if($tpl==$template_filename) $sel = ' selected';
       $o .= "<option value='$template_filename'$sel>$template_name</option>";
    $sel='';
   }
   echo '<select name="argwa_sr_tpl'.$l.'"><option value="page.php">Default</option>'.$o.'</select><br />';
  ?>
  <?php wp_editor($rec[0]['pSub'], 'argwa_sr'.$l, $settings = array('textarea_rows'=>'10') );?><p style="text-align:left;margin:10px;"><input type="button" value="Save Changes" class="button-primary" name="argwa_subscribe_page" id="argwa_subscribe_page_<?php echo $l;?>"/></p>
</div>
</div>
<?php wp_nonce_field('argwa_subscribe_page','argwa_subscribe_page_nonce');?>
<script type = "text/javascript">
jQuery(document).ready(function($) {
            //create a new field then append it before the add field button
            jQuery("#argwa_subscribe_page_<?php echo $l;?>").live('click', function(){
                  var new_field9 = " <input type='hidden' name='Llid' value='<?php echo $l;?>' / >";
                  var new_field10 = " <input type='hidden' name='argwa_subscribe_page' value='1' / >";
                  jQuery(this).before(new_field9);
                  jQuery(this).before(new_field10);
            jQuery('#admin_post_php').submit();
            });
});
</script>
<?php
}

function do_list_confirm_box($l) {
global $wpdb;
$rec = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_pages WHERE pLID='".$l."'",ARRAY_A);
?>
<div class="postbox closed" id="argwa-metaboxes-detailbox5">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle">Confirm Page Content / Forward Page Settings</h3>
  <div class="inside"><?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help11<?php echo $l;?>" class="argwahelp2"/><?php } echo __("Enter an External Page URL forward to be shown after confirming.",'argwa');?> <input type="text" name="argwa_cr_url<?php echo $l;?>" value="<?php if($g = (get_option('argwa_cr_url'.$l))) echo $g; else echo 'http://';?>" size="50"><br><?php  echo __("Select a page from your blog to display after confirming.",'argwa').' '; wp_dropdown_pages(array('depth' => 0, 'child_of' => 0, 'selected' => get_option('argwa_cr_page'.$l), 'echo' => 1,'name' => 'argwa_cr_page'.$l, 'show_option_none' => __("Select a Public Blog Page",'argwa')));?><br />
<?php
   _e("Select a Template to display with content below.",'argwa').' ';
   $templates = get_page_templates();
    $tpl = get_option('argwa_cr_tpl'.$l);
   foreach ( $templates as $template_name => $template_filename ) {
    if($tpl==$template_filename) $sel = ' selected';
       $o .= "<option value='$template_filename'$sel>$template_name</option>";
    $sel='';
   }
   echo '<select name="argwa_cr_tpl'.$l.'"><option value="page.php">Default</option>'.$o.'</select><br />';
  ?><?php wp_editor( $rec[0]['pConf'], 'argwa_cr'.$l );?>
<p style="text-align:left;margin:10px;"><input type="button" value="Save Changes" class="button-primary" name="argwa_confirm_page" id="argwa_confirm_page_<?php echo $l;?>"/></p>
</div>
</div>
<?php wp_nonce_field('argwa_confirm_page','argwa_confirm_page_nonce');?>
<script type = "text/javascript">
jQuery(document).ready(function($) {
            //create a new field then append it before the add field button
            jQuery("#argwa_confirm_page_<?php echo $l;?>").live('click', function(){
                  var new_field11 = " <input type='hidden' name='Llid' value='<?php echo $l;?>' / >";
                  var new_field12 = " <input type='hidden' name='argwa_confirm_page' value='1' / >";
                  jQuery(this).before(new_field11);
                  jQuery(this).before(new_field12);
            jQuery('#admin_post_php').submit();
            });
});
</script>
<?php
}

function do_list_unsubscribe_box($l) {
global $wpdb;
$rec = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_pages WHERE pLID='".$l."'",ARRAY_A);
?>
<div class="postbox closed" id="argwa-metaboxes-detailbox6">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle">Unsubscribe Page Content / Forward Page Settings</h3>
  <div class="inside"><?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help12<?php echo $l;?>" class="argwahelp2"/><?php } echo __("Enter an External Page URL forward to be shown after unsubscribing.",'argwa');?> <input type="text" name="argwa_ur_url<?php echo $l;?>" value="<?php if($g = (get_option('argwa_ur_url'.$l))) echo $g; else echo 'http://';?>" size="50"><br><?php  echo __("Select a page from your blog to display after unsubscribing.",'argwa').' '; wp_dropdown_pages(array('depth' => 0, 'child_of' => 0, 'selected' => get_option('argwa_ur_page'.$l), 'echo' => 1,'name' => 'argwa_ur_page'.$l, 'show_option_none' => __("Select a Public Blog Page",'argwa')));?><br />
<?php
   _e("Select a Template to display with content below.",'argwa').' ';
   $templates = get_page_templates();
    $tpl = get_option('argwa_ur_tpl'.$l);
   foreach ( $templates as $template_name => $template_filename ) {
    if($tpl==$template_filename) $sel = ' selected';
       $o .= "<option value='$template_filename'$sel>$template_name</option>";
    $sel='';
   }
   echo '<select name="argwa_ur_tpl'.$l.'"><option value="page.php">Default</option>'.$o.'</select><br />';
  ?><?php wp_editor( $rec[0]['pUnsub'], 'argwa_ur'.$l );?>
<p style="text-align:left;margin:10px;"><input type="button" value="Save Changes" class="button-primary" name="argwa_unsubscribe_page" id="argwa_unsubscribe_page_<?php echo $l;?>"/></p>
</div>
</div>
<?php wp_nonce_field('argwa_unsubscribe_page','argwa_unsubscribe_page_nonce');?>
<script type = "text/javascript">
jQuery(document).ready(function($) {
            //create a new field then append it before the add field button
            jQuery("#argwa_unsubscribe_page_<?php echo $l;?>").live('click', function(){
                  var new_field13 = " <input type='hidden' name='Llid' value='<?php echo $l;?>' / >";
                  var new_field14 = " <input type='hidden' name='argwa_unsubscribe_page' value='1' / >";
                  jQuery(this).before(new_field13);
                  jQuery(this).before(new_field14);
            jQuery('#admin_post_php').submit();
            });
});
</script>
<?php
}

function do_list_error_box($l) {
global $wpdb;
$rec = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_pages WHERE pLID='".$l."'",ARRAY_A);
?>
<div class="postbox closed" id="argwa-metaboxes-detailbox6">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle">Error Page Content / Forward Page Settings</h3>
  <div class="inside"><?php if(isset($this->help)){?><img src="<?php echo ARGWA_PLUGIN_URL;?>images/help.gif" id="help13<?php echo $l;?>" class="argwahelp2"/><?php } echo __("Enter an External Page URL forward to be shown on errors.",'argwa');?> <input type="text" name="argwa_er_url<?php echo $l;?>" value="<?php if($g = (get_option('argwa_er_url'.$l))) echo $g; else echo 'http://';?>" size="50"><br><?php  echo __("Select a page from your blog to display on errors.",'argwa').' '; wp_dropdown_pages(array('depth' => 0, 'child_of' => 0, 'selected' => get_option('argwa_er_page'.$l), 'echo' => 1,'name' => 'argwa_er_page'.$l, 'show_option_none' => __("Select a Public Blog Page",'argwa')));?><br />
<?php
   _e("Select a Template to display with content below.",'argwa').' ';
   $templates = get_page_templates();
    $tpl = get_option('argwa_er_tpl'.$l);
   foreach ( $templates as $template_name => $template_filename ) {
    if($tpl==$template_filename) $sel = ' selected';
       $o .= "<option value='$template_filename'$sel>$template_name</option>";
    $sel='';
   }
   echo '<select name="argwa_er_tpl'.$l.'"><option value="page.php">Default</option>'.$o.'</select><br />';
  ?>
    <?php wp_editor( $rec[0]['pErr'], 'argwa_er'.$l );?>
<p style="text-align:left;margin:10px;"><input type="button" value="Save Changes" class="button-primary" name="argwa_error_page" id="argwa_error_page_<?php echo $l;?>"/></p>
</div>
</div>
<?php wp_nonce_field('argwa_error_page','argwa_error_page_nonce');?>
<script type = "text/javascript">
jQuery(document).ready(function($) {
            //create a new field then append it before the add field button
            jQuery("#argwa_error_page_<?php echo $l;?>").live('click', function(){
                  var new_field15 = " <input type='hidden' name='Llid' value='<?php echo $l;?>' / >";
                  var new_field16 = " <input type='hidden' name='argwa_error_page' value='1' / >";
                  jQuery(this).before(new_field15);
                  jQuery(this).before(new_field16);
            jQuery('#admin_post_php').submit();
            });
});
</script>
<?php
}

function statusBox() {
global $wpdb;
for($i=59;$i>-1;$i--)
$min .= '<option>'.$i.'</option>';
for($i=23;$i>-1;$i--)
$hr .= '<option>'.$i.'</option>';
$ofs = ((int)get_option('gmt_offset'))*60*60;
$stime=(((int)wp_next_scheduled('argwa_daily_event'))+($ofs));
$nstime=(((int)wp_next_scheduled('argwa_regular_event')));
$ofs = ((int)get_option('gmt_offset'))*60*60;$stime=(((int)wp_next_scheduled('argwa_daily_event'))+($ofs));
$rs = __("Next Scheduled Mailing",'argwa').' @ '.date('l F jS \@ h:i A',$stime);
$rr .= '<div style="width:50%;float:left;line-height:22px;"><p><a title="'.$rs.'"><img alt="'.$rs.'" src="'.ARGWA_PLUGIN_URL.'images/clock_32x32.png" align="left" style="border: medium none;margin-top: -5px;padding-right: 10px;"></a><span style="font-size:13pt;">'.__("Next Scheduled Mailing",'argwa')." ".__("in",'argwa')." <a href='#' title='".$rs."' style='text-decoration:none;'>".sprintf(__("%2.1f hours.",'argwa'),round(((((wp_next_scheduled('argwa_daily_event')-time())/60)/60)),1))."</a></font></span>";
$rr .= '</div><div style="width:50%;float:right;padding-top:10px;">';?><?php if(isset($this->help)){ $rr .= '<img src="'.ARGWA_PLUGIN_URL.'images/help.gif" id="help1" class="argwahelp"/>'; } $rr .= '<input type="submit" name="argwa_set_daily" value="'.__("Reset Daily Send Time",'argwa').'" class="button"> '.__("in",'argwa').' <select name="hr">'.$hr.'</select> '.__("hours",'argwa').' <select name="min">'.$min.'</select> '.__("minutes",'argwa').'.';
$rr .= '</div><div style="clear:both;">';
$rr .= '</div>';
$rr .= wp_nonce_field('argwa_set_daily','argwa_set_daily_nonce',false,false);
return $rr;
}

function returnLists() {
global $wpdb;
$lists = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_lists");// ORDER BY xID DESC");
return $lists;
}

function argwa_update_details()  {
global $wpdb;
$lid = $op['xID'] = $_POST['Llid'];
$op['xName'] = $_POST['xName_'.$lid];
$op['xEmail'] = $_POST['xEmail_'.$lid];
$sql = "UPDATE ".$wpdb->prefix."ar_gwa_lists set `xName`='".$op['xName']."', `xEmail`='".$op['xEmail']."' WHERE xID='{$op['xID']}'";
$wpdb->query($sql);update_option('ar_gwa_from',$op['xEmail']);
if($_POST['ar_gwa_optin_'.$lid]=='X') {
update_option('ar_gwa_reg_optinb_'.$op['xID'],'');
update_option('ar_gwa_reg_optin_'.$op['xID'],'');
} else if($_POST['ar_gwa_optin_'.$lid]=='selected') {
update_option('ar_gwa_reg_optinb_'.$op['xID'],'checked');
update_option('ar_gwa_reg_optin_'.$op['xID'],'');
} else if($_POST['ar_gwa_optin_'.$lid]=='checked') {
update_option('ar_gwa_reg_optin_'.$op['xID'],'checked');
update_option('ar_gwa_reg_optinb_'.$op['xID'],'');
}
$listid=$lid;
update_option('ar_gwa_remove_'.$op['xID'],$_POST["ar_gwa_remove_".$lid]);
update_option('ar_gwa_drop_'.$op['xID'],$_POST["ar_gwa_drop_".$lid]);
update_option('ar_gwa_sing_'.$op['xID'],$_POST["ar_gwa_sing_".$lid]);
update_option('ar_gwa_cb_'.$op['xID'],$_POST["ar_gwa_cb_".$lid]);
update_option('ar_gwa_addsub_'.$op['xID'],$_POST["ar_gwa_addsub_".$lid]);
update_option('ar_gwa_notify_'.$op['xID'],$_POST["ar_gwa_notify_".$lid]);
update_option('ar_gwa_captcha_'.$op['xID'],$_POST["ar_gwa_captcha_".$lid]);
if($_POST["ar_gwa_captcha_".$lid]=='checked') update_option('ar_gwa_captcha_'.$lid,$_POST["ar_gwa_captcha_".$lid]);
  if($_POST["ar_gwa_char_".$lid]=="utf" && get_option('ar_gwa_char_'.$lid)!='UTF-8') {
  update_option('ar_gwa_char_'.$lid,'UTF-8');
  update_option('ar_gwa_enc_'.$lid,'8bit');
  } else if($_POST["ar_gwa_char_".$lid]=='iso' && get_option('ar_gwa_char_'.$lid)!='ISO-8859-1'){
  update_option('ar_gwa_char_'.$lid,'ISO-8859-1');
  update_option('ar_gwa_enc_'.$lid,'8bit');
  } else if($_POST["ar_gwa_char_".$lid]=='iso2' && get_option('ar_gwa_char_'.$lid)!='ISO-8859-2'){
  update_option('ar_gwa_char_'.$lid,'ISO-8859-2');
  update_option('ar_gwa_enc_'.$lid,'8bit');
  } else if($_POST["ar_gwa_char_".$lid]=='win' && get_option('ar_gwa_char_'.$lid)!='Windows-1255'){
  update_option('ar_gwa_char_'.$lid,'Windows-1255');
  update_option('ar_gwa_enc_'.$lid,'8bit');
  }  else if($_POST["ar_gwa_char_".$lid]=='gbk' && get_option('ar_gwa_char_'.$lid)!='GB2312'){
  update_option('ar_gwa_char_'.$lid,'GB2312');
  update_option('ar_gwa_enc_'.$lid,'7bit');
  } else if($_POST["ar_gwa_char_".$lid]=='jpn' && get_option('ar_gwa_char_'.$lid)!='ISO-2022-JP'){
  update_option('ar_gwa_char_'.$lid,'ISO-2022-JP');
  update_option('ar_gwa_enc_'.$lid,'7bit');
  }
  if(isset($_POST["ar_gwa_wrap_".$lid]))
  update_option('ar_gwa_wrap_'.$lid,$_POST['ar_gwa_wrap_'.$lid]);
update_option('argwa_error',sprintf(__("List details updated successfully for Newsletter <strong>%s</strong>",'argwa'),$op['xName']));
}

function argwa_show_error($msg) {
echo '	<!--<div class="ui-widget">
		<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">-->
<div id="message" class="updated fade"><p>
      '.$msg.'
<!--		</div>
	</div><p></p>--></div>';
delete_option('argwa_error');
}

function argwa_show_page() {argwa_show_page();}

function on_save_changes() {
if ( !current_user_can('manage_options') )
wp_die( __('Cheatin&#8217; uh?') );
check_admin_referer('argwa-metaboxes-general');
wp_redirect($_POST['_wp_http_referer']);
}

function on_sidebox_2_content($data) {
return $this->argwa_feed('http://freeautoresponder.biz/wordpress/category/free-autoresponder/feed/rss');
}

function on_sidebox_3_content($data) {
return $this->argwa_feed('http://freeautoresponder.biz/wordpress/category/testimonial/feed/rss');
}

function argwa_feed($f) {
include_once(ABSPATH . WPINC . '/feed.php');

// Get a SimplePie feed object from the specified feed source.
$rss = fetch_feed($f);
if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly
    // Figure out how many total items there are, but limit it to 5.
    $maxitems = $rss->get_item_quantity(3);

    // Build an array of all the items, starting with element 0 (first element).
    $rss_items = $rss->get_items(0, $maxitems);
endif;
    echo '<ul>';
    if ($maxitems == 0) echo '<li>No items.</li>';
    else
    foreach ( $rss_items as $item ) :
    echo '<li>
        <a href="'.esc_url( $item->get_permalink() ).'"
        title="'.'Posted '.$item->get_date('j F Y | g:i a').'">'
        .esc_html( $item->get_title() ).'</a>
    </li>';
    endforeach;
    echo '</ul>';
}
function on_sidebox_1_content($data) {
echo '<h3 id="hgs" style="margin:5px"><strong>+ '.__("Help getting started.",'argwa').'</strong></h3><div id="hgsdiv" style="display:none;color:#000;font-family:verdana;font-size:10pt;">
';
_e("<p>All options are setup with default values that will allow you to get started right away.</p><ol><li>Change your <strong>From:</strong> email address under the [List Details] box near the top-left. Use an address at your blog domain and <strong>NOT A GMAIL/HOTMAIL/YAHOO ADDRESS.</strong> (NOTE: Click the headers to reveal options.)</li><li>Add each of your message by clicking the [Add New Autoresp...] button and complete all fields.</li>
<li>Display the subscription form on your blog. On the <b>Appearance ->Widget Page</b> drag the widget to your sidebar location. Also use the SHORTCODE anywhere in your posts and pages or Copy&Paste the Subscription Form anywhere.</li><li>Edit your Subscription Form to customize it using the built-in WYSIWYG Editor or Copy&Paste from your favorite editor. IMPORTANT: KEEP ALL FORM FIELD NAMES INTACT INCLUDING HIDDEN.</li>
</ol></p>",'argwa');echo '</div>';
echo '<h3 id="hgs1" style="margin:5px"><strong>+ '.__("Custom forward pages.",'argwa').'</strong></h3><div id="hgs1div" style="display:none;color:#000;font-family:verdana;font-size:10pt;">';
_e("<p>User forward pages can be set to existing blog pages, an external website url, or the built-in custom blog page response.<p>Each user interaction like Subscribe, Confirm, & Unsubscribe can be set individually.  Also the Error Page should be configured (just in case).<p>All the different forward page options can be setup in the various boxes below the Msgs & Leads boxes to the lower-left. (NOTE: Click the header to reveal options.)</p>",'argwa');echo '</div>';
echo '<h3 id="hgs2" style="margin:5px"><strong>+ '.__("Extended plugin options.",'argwa').'</strong></h3><div id="hgs2div" style="display:none;color:#000;font-family:verdana;font-size:10pt;">'; _e("<p><p>Under List Details you will find more options to customize plugin operation. Set your List Name and Select the Checkboxes for the other options.</p><ol><li> Change the Newsletter Name as desired. <i>Have you set the <b>From:</b> email as advised?</i></li><li> Tick checkbox to Send a notification email to the blog_admin with every subscription.</li><li> Delete on Unsubscribe <u>prevents importing an address again from the same list</u> after a lead has previously unsubscribed.</li> <li> Allow User Opt-in or Auto-Subscribe blog users during wordpress user registration.</li> <li> Email text wrapping for plain-text msgs allows formatting for mobile devices and fixed line length in promotional emails.</li> <li> Character set encoding for special characters in alternate languages.</li> <li>Use the SHORTCODE provided to insert your subscription form in posts and pages on your blog w/ form checking javascript.</li></ol>",'argwa');
echo '</div>';
echo '<h3 id="hgs3" style="margin:5px"><strong>+ '.__("Supercharged campaigns.",'argwa').'</strong></h3><div id="hgs3div" style="display:none;color:#000;font-family:verdana;font-size:10pt;">'; _e("<p>Your goal is getting the most of out every mailing so here are some popular ideas you should consider trying with this free plugin.</p><ol><li> Collect additional information from your subscribers using the extra form fields and use it to personalize your messages or to easily get their phone, address, and more.</li><li> Periodically attempt to resend confirmation messages to unconfirmed leads before deleting them from the list. Keeping many unconfirmed leads slows down the mailer.</li><li> Entice users with a free product download after confirming their subscription and add a link to it in the Confirm Page Content.</li><li> Customize your subscription form to attract subscribers and get your list noticed by highlighting it on your blog homepage. Use the sidebar widget to display it on every page for maximum exposure.</li></ol></p>",'argwa');
echo '</div>';
$aid = get_option('ar_gwa_aff_id');
if($aid>1) $aurl = "http://freeautoresponder.biz/?/".$aid; else $aurl="http://wordpressemailsoftware.com";
echo '<h3 id="hgs4" style="margin:5px"><strong>+ '.__("Upgrade to ARGWA Pro.",'argwa').'</strong></h3><div id="hgs4div" style="display:none;color:#000;font-family:verdana;font-size:10pt;">'; _e("<p><p>The professional version allows you to create and manage multiple lists with powerful functionality like:</p><ol><li> Schedule and Send Newsletters by Date and reach your entire list with time-sensitive mailings.</li><li> Send Attachment Files in your Newsletters to offer a weekly e-course or send a bonus file to subscribers.</li><li> URL Click Tracking & Cloaking to see how many click a link in your messages. Use to improve your click-thru rate.</li><li> Offer a User-Info Edit Page & Link to your blog where each user can update their details including extra data fields.</li> <li > SINGLE OPT-IN used to by-pass confirmation step and increase subscription rate.</li></ol>",'argwa');echo "<p style='width:100%;text-align:center;font-weight:bold;'><a href='$aurl' target='_blank'>Upgrade to ARGWA Pro Today!</a><p></div>";
echo ' <script> jQuery(document).ready(function($) {  $("#hgs").click(function() {  $("#hgsdiv").toggle();  });  $("#hgs1").click(function() {  $("#hgs1div").toggle();  });  $("#hgs2").click(function() {  $("#hgs2div").toggle();  });  $("#hgs3").click(function() {  $("#hgs3div").toggle();  });  $("#hgs4").click(function() {  $("#hgs4div").toggle();  }); }); </script>';}
  function set_daily() {
  $delay = (($_POST['hr']*(60*60)) + ($_POST['min']*60));
  wp_clear_scheduled_hook('argwa_daily_event');
  $tt = time();
	wp_schedule_event(($tt+$delay), 'daily', 'argwa_daily_event');
    update_option('argwa_error',__("Autoresponder daily send time updated."));
  }
function argwa_add_message() {
global $wpdb;
	if(!$_POST['mSubject'] || (!$_POST['mBodyText'] && !$_POST['mBodyHtml'])) { $g_error = __("Error: Form field error. Complete form correctly or contact developer.",'argwa'); }
  if(($_POST['mDay'] < 0 || $_POST['mDay'] > 365)) { $g_error = __("Error: Missing or Out-of-Range Day number setting. Correct and try again.",'argwa'); }
		$qry = "SELECT * FROM ".$wpdb->prefix."ar_gwa_msg WHERE mDay = '".$_POST['mDay']."'";
		$res = $wpdb->get_results($qry);
		if($res) { $g_error = __("Error: Message already scheduled for this day number. Try again.",'argwa'); }
if(isset($_POST['mBodyHtml']) && $_POST['mBodyHtml']!='') $mType = 'html'; else $mType = 'text';
if($_POST['mDay']!='')
		$qry = "INSERT INTO ".$wpdb->prefix."ar_gwa_msg (mSubject, mBodyText, mBodyHtml, mDay, mLID, mType $mattach_field $mnews_field) VALUES('".$wpdb->escape($_POST['mSubject'])."','".$wpdb->escape($_POST['mBodyText'])."','".$wpdb->escape($_POST['mBodyHtml'])."','".$wpdb->escape($_POST['mDay'])."','".$_POST['mLID']."','".$mType."' $mattach $mnews)";
if(!isset($g_error)) {
$wpdb->query($qry);
update_option('argwa_error',__("New autoresponder message added successfully.",'argwa'));
} else
update_option('argwa_error',__($g_error));
}

function argwa_update_message() {
global $wpdb;
	if(!$_POST['mSubject'] || (!$_POST['mBodyText'] && !$_POST['mBodyHtml'])) {  $g_error = __("Error: Form field error. Complete form correctly or contact developer.",'argwa');  }
	if(($_POST['mDay'] < 0 || $_POST['mDay'] > 365 || !is_numeric($_POST['mDay']) || !$_POST['mID'])) { $g_error = __("Error: Missing or Out-of-Range Day number setting. Correct and try again.",'argwa'); }
	if($res) { $g_error = __("Error: Message already scheduled for this day number. Try again.",'argwa'); }
  if(isset($_POST['mBodyHtml']) && $_POST['mBodyHtml']!='') $mType = 'html'; else $mType = 'text';
	$qry="UPDATE ".$wpdb->prefix."ar_gwa_msg SET mSubject='".$wpdb->escape($_POST['mSubject'])."',mBodyText='".$wpdb->escape($_POST['mBodyText'])."',mBodyHtml='".$wpdb->escape($_POST['mBodyHtml'])."',mDay='".$wpdb->escape($_POST['mDay'])."',mType='".$mType."' $attach $mnews WHERE mID='{$_POST['mID']}'";
if(!isset($g_error)) {
$wpdb->query($qry);
update_option('argwa_error',__("Autoresponder message Day# ".$_POST['mDay']." updated successfully.",'argwa'));
} else
update_option('argwa_error',__($g_error));
}

function argwa_import_leads() {
global $wpdb;
$l = $_POST['mLID'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $dt = date('Y-m-d H:i:s');
if($_REQUEST['lImport']!='' && preg_match("/\n/",$_REQUEST['lImport'])) {
 if(!preg_match("/,/",$_REQUEST['lImport']) || !$_REQUEST['mLID']) {
    return;
 } else {
  $lines2 = array();
  $lines = preg_split("/\n/",$_REQUEST['lImport']);
  $dups=0;
  foreach($lines as $ttline) {
if($ttline[0]!='' && $ttline[1]!=''){
  $tttline = preg_split("/,/",$ttline);
  $tttline[1] = preg_replace("/\s+/",'',$tttline[1]);
  $tttline[1] = preg_replace("/\n+/",'',$tttline[1]);
unset($llid);if($tttline[1]!='' && $tttline[0]!='') $llid = $wpdb->get_var("SELECT lID FROM ".$wpdb->prefix."ar_gwa_leads WHERE lEmail LIKE '%".$tttline[1]."%' AND lLID='".$l."'");
    if($llid>0) {
          $dups++;
    }    else if(isset($ttline)){
          $lines2[] = $ttline;
    }
    $lds = count($lines2);
    if($dups) update_option('argwa_error',sprintf(__("%d duplicates scrubbed and %d leads imported.",'argwa'),$dups,$lds));
    } else update_option('argwa_error',sprintf(__("Successfully imported %d leads.",'argwa'),$lds));
  }
if(count($lines2)>0) { foreach($lines2 as $tline) { $to='';$name=''; $line = preg_split("/,/",$tline); if($line[0]!='' && $line[1]!='') { $name = mysql_real_escape_string(trim($line[0])); $to = mysql_real_escape_string(str_replace("\r\n", "", trim($line[1]))); $dt = date('Y-m-d H:i:s'); $ip = $_SERVER['REMOTE_ADDR']; $dns = gethostbyaddr($ip); $i_url = get_bloginfo('url'); if($line[2]!='') $field1 = mysql_real_escape_string($line[2]); if($line[3]!='') $field2 = mysql_real_escape_string($line[3]); if($line[4]!='') $field3 = mysql_real_escape_string($line[4]); if($line[5]!='') $field4 = mysql_real_escape_string($line[5]); if($line[6]!='') $field5 = mysql_real_escape_string($line[6]); $lead = array('lLID' => $l,'lName' => $name,'lEmail' => $to); $lCnf = $this->argwa_send_confirm($lead); $qry = "INSERT INTO ".$wpdb->prefix."ar_gwa_leads (lLID,lName,lEmail,lField1,lField2,lField3,lField4,lField5,lDateEntry,lDateOut,lMOut,lCnf,lIP,lDNS) VALUES ";$qry .= "('".$l."','$name','$to','".$field1."','".$field2."','".$field3."','".$field4."','".$field5."',current_date, current_date, '-1','$lCnf','$ip','')"; $wpdb->query($qry); } } } } $success = count($lines2);} }

function argwa_send_confirm($lead) {
 global $wpdb,$phpmailerDir;
    $ip = $_SERVER['REMOTE_ADDR'];
    $dt = date('Y-m-d H:i:s');
    $i_url = get_bloginfo('url');
  $pword='ar';
  $vals = "ABCDEFGHIJKLMNOPQRSTUVWXYZabchefghjkmnpqrstuvwxyz0123456789";
  while (strlen($pword) < 21) {
                mt_getrandmax();  // Returns the maximum value that can be returned by a call  rand
                $num = rand(1,strlen($vals));
                $tmp = substr($vals, $num+4, 1);
                $pword = $pword . $tmp;
                $tmp ="";
  }
    $lCnf = $pword;
#    $to = "$lEmail";
    $from = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ar_gwa_lists, ".$wpdb->prefix."ar_gwa_pages WHERE xID=pLID AND xID='{$lead['lLID']}'");
    $subj = html_entity_decode(__("Confirm your subscription at",'argwa').' '.$from->xName."\r\n",ENT_NOQUOTES, get_option('ar_gwa_char_'.$lead['lLID']));
    $msg = $from->pConfMsg."\r\n";
    $msg .= "\r\n".__('Click to Confirm','argwa')." -> $i_url?argwa=$lCnf";
    $msg .= "\r\n"."\r\n".sprintf(__("A newsletter subscription request was received from %s on %s.",'argwa'),$ip,$dt);
    $msg .= "\r\n"."\r\n".sprintf(__("If you did not request a subscription to our newsletter please disregard this email and you will not be contacted again. For further information please visit %s.",'argwa'),get_bloginfo('url'));
    $nicename = $from->xName;
    $ar_gwa_char = get_option('ar_gwa_char_'.$lead['lLID']);
    $ar_gwa_enc = get_option('ar_gwa_enc_'.$lead['lLID']);
    $port = get_option('ar_gwa_smtp_port_'.$lead['lLID']);
    $suc = @mail($lead['lEmail'],$subj,$msg."\r\n","from: {$from->xEmail}"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=$ar_gwa_char\nContent-Transfer-Encoding: $ar_gwa_enc\n",'-f'.$from->xEmail);
    if(!$suc) $suc = @mail($lead['lEmail'],$subj,$msg."\r\n","from: {$from->xEmail}"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=$ar_gwa_char\nContent-Transfer-Encoding: $ar_gwa_enc\n");
    if(!$suc) mail($lead['lEmail'],str_replace("\r\n","",$subj),str_replace("\r\n","\n",$msg),"from: $nicename <{$from->xEmail}>\nMime-Version: 1.0\nContent-Type: text/plain; charset=$ar_gwa_char\nContent-Transfer-Encoding: $ar_gwa_char\n");
return $lCnf;
}

function argwa_resend_confirm() {
 global $wpdb,$phpmailerDir;
 $uid = $_POST['argwa_resend_confirm'];
    $lead = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_leads WHERE lID=".$uid,ARRAY_A);
    $lead = $lead[0];
    $i_url = get_bloginfo('url');
    $lCnf = $lead['lCnf'];
    $from = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ar_gwa_lists, ".$wpdb->prefix."ar_gwa_pages WHERE xID=pLID AND xID='{$lead['lLID']}'");
    $subj = html_entity_decode(__("Confirm your subscription at",'argwa').' '.$from->xName."\r\n",ENT_NOQUOTES, get_option('ar_gwa_char_'.$lead['lLID']));
    $msg = $from->pConfMsg."\r\n";
    $msg .= "\r\n".__('Click to Confirm','argwa')." -> $i_url?argwa=$lCnf";
    $msg .= "\r\n"."\r\n".sprintf(__("A newsletter subscription request was received from %s on %s.",'argwa'),$lead['lIP'],$lead['lDateEntry']);
    $msg .= "\r\n"."\r\n".sprintf(__("If you did not request a subscription to our newsletter please disregard this email and you will not be contacted again. For further information please visit %s.",'argwa'),get_bloginfo('url'));
    $nicename = $from->xName;
    $ar_gwa_char = get_option('ar_gwa_char_'.$lead['lLID']);
    $ar_gwa_enc = get_option('ar_gwa_enc_'.$lead['lLID']);
    $port = get_option('ar_gwa_smtp_port_'.$lead['lLID']);
    $suc = @mail($lead['lEmail'],$subj,$msg."\r\n","from: {$from->xEmail}"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=$ar_gwa_char\nContent-Transfer-Encoding: $ar_gwa_enc\n",'-f'.$from->xEmail);
    if(!$suc) $suc = @mail($lead['lEmail'],$subj,$msg."\r\n","from: {$from->xEmail}"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset=$ar_gwa_char\nContent-Transfer-Encoding: $ar_gwa_enc\n");
    if(!$suc) mail($lead['lEmail'],str_replace("\r\n","",$subj),str_replace("\r\n","\n",$msg),"from: {$from->xEmail}\nMime-Version: 1.0\nContent-Type: text/plain; charset=$ar_gwa_char\nContent-Transfer-Encoding: $ar_gwa_char\n");
   update_option('argwa_error',__('Resent Confirm Message to','argwa').' '. $lead['lEmail']);
}

function argwa_send_message() {
global $wpdb;
	$res = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_leads WHERE lCnf=1",ARRAY_A);
	if($res)	{
	$stime = time();
		$ms	= new ARGWAMailProcess();
    $lEmailed 	= 0;
    if(!isset($_POST['mBodyHtml'])||$_POST['mBodyHtml']=="") {
		$aMsg		= array('mType' => 'text', 'mSubject' =>$wpdb->escape($_REQUEST['mSubject']), 'mBodyText' => $wpdb->escape($_REQUEST['mBodyText']));
    } else {
		$aMsg		= array('mType' => 'html', 'mSubject' =>$wpdb->escape($_REQUEST['mSubject']), 'mBodyHtml' => $wpdb->escape($_REQUEST['mBodyHtml']), 'mBodyText' => $wpdb->escape($_REQUEST['mBodyText']));
    }
		foreach($res as $row)
		{
			if($ms->setLead($row['lID']) && $ms->setMsg2($aMsg))
			{
				$ms->mailLead(NULL,NULL,1);
				++$lEmailed;
      }
		}
	$etime = time(); update_option('argwa_error',sprintf(__("Sent %d email messages. The process ran for %d seconds.",'argwa'),$lEmailed,($etime - $stime)));
  }
}

function argwa_process_fields() {
global $my_argwa_metabox_plugin;
if(isset($_POST["Submit"]) & isset($_POST['ar_gwa_delete_all']) & ($_POST['ar_gwa_delete_all']=="delete")) {
  update_option('ar_gwa_delete','checked');
} else if(isset($_POST["Submit"])){
  delete_option('ar_gwa_delete');
}
 if(isset($_POST['argwa_resend_confirm']) && wp_verify_nonce($_POST['argwa_resend_confirm_nonce'],'argwa_resend_confirm')) {
    $my_argwa_metabox_plugin->argwa_resend_confirm();
 }
 if(isset($_POST['argwa_set_daily']) && wp_verify_nonce($_POST['argwa_set_daily_nonce'],'argwa_set_daily')) {
    $my_argwa_metabox_plugin->set_daily($_POST['hr'],$_POST['min']);
 }
 if(isset($_POST['argwa_update_details']) && wp_verify_nonce($_POST['argwa_update_details_nonce'],'argwa_update_details')) {
    $my_argwa_metabox_plugin->argwa_update_details();
 }
 if(isset($_POST['argwa_add_message']) && wp_verify_nonce($_POST['argwa_message_nonce'],'argwa_message')) {
    $my_argwa_metabox_plugin->argwa_add_message();
 }
 if(isset($_POST['argwa_update_message']) && wp_verify_nonce($_POST['argwa_message_nonce'],'argwa_message')) {
    $my_argwa_metabox_plugin->argwa_update_message();
 }
 if(isset($_POST['argwa_send_message']) && wp_verify_nonce($_POST['argwa_message_nonce'],'argwa_message')) {
    $my_argwa_metabox_plugin->argwa_send_message();
 }
 if(isset($_POST['argwa_import_leads']) && wp_verify_nonce($_POST['argwa_import_leads_nonce'],'argwa_import_leads')) {
    $my_argwa_metabox_plugin->argwa_import_leads();
 }
 if(isset($_POST['argwa_confirm_message']) && wp_verify_nonce($_POST['argwa_confirm_message_nonce'],'argwa_confirm_message')) {
    $my_argwa_metabox_plugin->argwa_confirm_message();
 }
 if(isset($_POST['argwa_error_page']) && wp_verify_nonce($_POST['argwa_error_page_nonce'],'argwa_error_page')) {
    $my_argwa_metabox_plugin->argwa_error_page();
 }
 if(isset($_POST['argwa_confirm_page']) && wp_verify_nonce($_POST['argwa_confirm_page_nonce'],'argwa_confirm_page')) {
    $my_argwa_metabox_plugin->argwa_confirm_page();
 }
 if((isset($_POST['argwa_form_page']) || isset($_POST['argwa_form_reset'])) && wp_verify_nonce($_POST['argwa_form_page_nonce'],'argwa_form_page')) {
 if(isset($_POST['argwa_form_reset']) && isset($_POST['Llid']))
      $my_argwa_metabox_plugin->argwa_form_reset();
 else
      $my_argwa_metabox_plugin->argwa_form_page();
 }
 if(isset($_POST['argwa_unsubscribe_page']) && wp_verify_nonce($_POST['argwa_unsubscribe_page_nonce'],'argwa_unsubscribe_page')) {
    $my_argwa_metabox_plugin->argwa_unsubscribe_page();
 }
 if(isset($_POST['argwa_subscribe_page']) && wp_verify_nonce($_POST['argwa_subscribe_page_nonce'],'argwa_subscribe_page')) {
    $my_argwa_metabox_plugin->argwa_subscribe_page();
 }
 if(isset($_POST['argwa_delete_leads']) && wp_verify_nonce($_POST['argwa_delete_leads_nonce'],'argwa_delete_leads')) {
    $my_argwa_metabox_plugin->argwa_delete_leads();
 }
 if(isset($_POST['argwa_delete_messages']) && wp_verify_nonce($_POST['argwa_delete_messages_nonce'],'argwa_delete_messages')) {
    $my_argwa_metabox_plugin->argwa_delete_messages();
 }
}

function argwa_delete_leads() {
global $wpdb;
	$lID = $_POST['lID'];
	for($i = 0; $i < count($lID); $i++)
{		$wpdb->query("DELETE FROM " . $wpdb->prefix . "ar_gwa_leads WHERE lID='{$lID[$i]}'");	}
 update_option('argwa_error',__('Successfully deleted','argwa').' '. count($lID) . ' ' . __("Leads").'.');
}

function argwa_delete_messages() {
global $wpdb;
	$mID = $_POST['mID'];
	for($i = 0; $i < count($mID); $i++)
{		$wpdb->query("DELETE FROM " . $wpdb->prefix . "ar_gwa_msg WHERE mID='{$mID[$i]}'");	}
 update_option('argwa_error',__('Successfully deleted','argwa').' '. count($mID) . ' ' . __("Messages").'.');
}

function argwa_confirm_message() {
global $wpdb;
 $qry = 'UPDATE '.$wpdb->prefix.'ar_gwa_pages SET pConfMsg="'.$_POST['mConfForm'.$_POST['Llid']].'" WHERE pLID='.$_POST['Llid'];
 $wpdb->query($qry);
 update_option('argwa_error',__("Confirm message content edited successfully."));
}

function argwa_error_page() {
global $wpdb;
if($_POST['argwa_er_url'.$_POST['Llid']] && $_POST['argwa_er_url'.$_POST['Llid']] != 'http://') { update_option('argwa_er_url'.$_POST['Llid'],$_POST['argwa_er_url'.$_POST['Llid']]);} else {update_option('argwa_er_url'.$_POST['Llid'],'');}
if($_POST['argwa_er_page'.$_POST['Llid']]) { update_option('argwa_er_page'.$_POST['Llid'],$_POST['argwa_er_page'.$_POST['Llid']]);} else {update_option('argwa_er_page'.$_POST['Llid'],'');}
if($_POST['argwa_er_tpl'.$_POST['Llid']]) { update_option('argwa_er_tpl'.$_POST['Llid'],$_POST['argwa_er_tpl'.$_POST['Llid']]);} else {update_option('argwa_er_tpl'.$_POST['Llid'],'');}


if(isset($_POST['argwa_er'.$_POST['Llid']])) {
 $qry = 'UPDATE '.$wpdb->prefix.'ar_gwa_pages SET pErr="'.mysql_real_escape_string($_POST['argwa_er'.$_POST['Llid']]).'" WHERE pLID='.$_POST['Llid'];
 $r=$wpdb->query($qry);
# $wpdb->show_errors();
 update_option('argwa_error',__("Error page content edited successfully."));
 }
}

function argwa_confirm_page() {
global $wpdb;
if($_POST['argwa_cr_url'.$_POST['Llid']] && $_POST['argwa_cr_url'.$_POST['Llid']] != 'http://') { update_option('argwa_cr_url'.$_POST['Llid'],$_POST['argwa_cr_url'.$_POST['Llid']]);} else {update_option('argwa_cr_url'.$_POST['Llid'],'');}
if($_POST['argwa_cr_page'.$_POST['Llid']]) { update_option('argwa_cr_page'.$_POST['Llid'],$_POST['argwa_cr_page'.$_POST['Llid']]);} else {update_option('argwa_cr_page'.$_POST['Llid'],'');}
if($_POST['argwa_cr_tpl'.$_POST['Llid']]) { update_option('argwa_cr_tpl'.$_POST['Llid'],$_POST['argwa_cr_tpl'.$_POST['Llid']]);} else {update_option('argwa_cr_tpl'.$_POST['Llid'],'');}

if(isset($_POST['argwa_cr'.$_POST['Llid']])) {
 $qry = 'UPDATE '.$wpdb->prefix.'ar_gwa_pages SET pConf="'.mysql_real_escape_string($_POST['argwa_cr'.$_POST['Llid']]).'" WHERE pLID='.$_POST['Llid'];
 $r=$wpdb->query($qry);
# $wpdb->show_errors();
 update_option('argwa_error',__("Confirm page content edited successfully."));
 }
}

function argwa_form_reset() {
global $wpdb;
    $subscribe = '<!-- [GWA] AutoResponder Begin -->'."\n".'<form name="ARGWA" action="'.get_bloginfo('url').'" method="post"><strong>Name:</strong>&nbsp;<input id="argwa_name" name="gwaname" size="16" type="text" /><br /><strong>Email:</strong>&nbsp;<input id="argwa_email" name="gwaemail" size="16" type="text" /><br /><input class="button" onclick="return gwaCheckForm1(this.form)" name="Add" type="submit" value="SUBSCRIBE" /><input name="act" type="hidden" value="s_add" /><input name="listid" type="hidden" value="1" /></form>'."\n".'<!-- [GWA] AutoResponder End -->';
        $wpdb->query( "UPDATE `".$wpdb->prefix."ar_gwa_lists` set `xSubscribe`='".mysql_real_escape_string($subscribe)."' WHERE xID='1'" );
update_option('argwa_error',__("Subscription Form reset to default."));
}

function argwa_form_page() {
global $wpdb;
 $qry = 'UPDATE '.$wpdb->prefix.'ar_gwa_lists SET xSubscribe="'.mysql_real_escape_string($_POST['mSubForm'.$_POST['Llid']]).'" WHERE xID='.$_POST['Llid'];
 $r=$wpdb->query($qry);
# $wpdb->show_errors();
 update_option('argwa_error',__("Subscription Form edited successfully."));
}

function argwa_unsubscribe_page() {
global $wpdb;
if($_POST['argwa_ur_url'.$_POST['Llid']] && $_POST['argwa_ur_url'.$_POST['Llid']] != 'http://') { update_option('argwa_ur_url'.$_POST['Llid'],$_POST['argwa_ur_url'.$_POST['Llid']]);} else {update_option('argwa_ur_url'.$_POST['Llid'],'');}
if($_POST['argwa_ur_page'.$_POST['Llid']]) { update_option('argwa_ur_page'.$_POST['Llid'],$_POST['argwa_ur_page'.$_POST['Llid']]);} else {update_option('argwa_ur_page'.$_POST['Llid'],'');}
if($_POST['argwa_ur_tpl'.$_POST['Llid']]) { update_option('argwa_ur_tpl'.$_POST['Llid'],$_POST['argwa_ur_tpl'.$_POST['Llid']]);} else {update_option('argwa_ur_tpl'.$_POST['Llid'],'');}

if(isset($_POST['argwa_ur'.$_POST['Llid']])) {
 $qry = 'UPDATE '.$wpdb->prefix.'ar_gwa_pages SET pUnsub="'.mysql_real_escape_string($_POST['argwa_ur'.$_POST['Llid']]).'" WHERE pLID='.$_POST['Llid'];
 $r=$wpdb->query($qry);
# $wpdb->show_errors();
 update_option('argwa_error',__("Unsubscribe page content edited successfully."));
 }
}

function argwa_subscribe_page() {
global $wpdb;
if($_POST['argwa_sr_url'.$_POST['Llid']] && $_POST['argwa_sr_url'.$_POST['Llid']] != 'http://') { update_option('argwa_sr_url'.$_POST['Llid'],$_POST['argwa_sr_url'.$_POST['Llid']]);} else {update_option('argwa_sr_url'.$_POST['Llid'],'');}
if($_POST['argwa_sr_page'.$_POST['Llid']]) { update_option('argwa_sr_page'.$_POST['Llid'],$_POST['argwa_sr_page'.$_POST['Llid']]);} else {update_option('argwa_sr_page'.$_POST['Llid'],'');}
if($_POST['argwa_sr_tpl'.$_POST['Llid']]) { update_option('argwa_sr_tpl'.$_POST['Llid'],$_POST['argwa_sr_tpl'.$_POST['Llid']]);} else {update_option('argwa_sr_tpl'.$_POST['Llid'],'');}

if(isset($_POST['argwa_sr'.$_POST['Llid']])) {
 $qry = 'UPDATE '.$wpdb->prefix.'ar_gwa_pages SET pSub="'.mysql_real_escape_string($_POST['argwa_sr'.$_POST['Llid']]).'" WHERE pLID='.$_POST['Llid'];
 $r=$wpdb->query($qry);
# $wpdb->show_errors();
 update_option('argwa_error',__("Subscription page content edited successfully."));
  }
}
}

function argwa_get_listname($listid) {
 global $wpdb;
 $qry = "SELECT xName FROM ".$wpdb->prefix."ar_gwa_lists WHERE xID='".$listid."'";
 return $wpdb->get_var($qry);
}

function argwa_export_form() {
global $wpdb;
$ll = $_GET['lid'];
echo '<form name="mForm" method="post" action="?page=argwa_page"><span style="font-size:10pt">';
	echo __("Exporting Leads from List",'argwa').' <strong>'.argwa_get_listname($ll).'</strong>';
 echo '<p><textarea id="lImport" class="lImport" name="lImport" style="width:600px; height:240px;">';
$leads = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_leads WHERE lLID=".$ll);
if(count($leads[0])<1) echo __('No Leads','argwa'); else
foreach($leads as $l) echo $l->lName.",".$l->lEmail.",".$l->lField1.",".$l->lField2.",".$l->lField3.",".$l->lField4.",".$l->lField5."\n";
 echo '</textarea>';
  if(isset($_GET['lid']))
	echo '<input type="hidden" name="mLID" value="'.$_GET['lid'].'">';
  wp_nonce_field('argwa_import','argwa_import_nonce');
  echo '</span></form>';
}

function argwa_import_form() {
global $wpdb;
$ll = $_GET['lid'];
echo '<form name="mForm" method="post" action="?page=argwa_page"><span style="font-size:10pt">';
echo __("Importing Leads to List",'argwa').' <strong>'.argwa_get_listname($ll).'</strong>';
echo '<p><input type="submit" name="argwa_import_leads" value="Import Leads" class="button">&nbsp;<a href="http://wordpressemailsoftware.com" style="text-decoration:none;font-weight:normal;color:#21759B;;" target="_blank" title="For DIRECT IMPORT (ALREADY CONFIRMED) and SINGLE OPT-IN click to GO PRO NOW."><input type="checkbox" name="confirm_leads" checked disabled> '.__("Send double opt-in confirm message and link.",'argwa').'</a>';
 echo '<p>Add email leads (each lead record entered must be on a single line) and must include name and email. May also include up to five (5) extra data fields on the same line. Separate all fields with a comma (,) and escape all commas in your data fields with a backslash (\,). <b><i>Always press [RETURN] at the end of each line entered.</p>
 <p><textarea id="lImport" class="lImport" name="lImport" style="width:600px; height:240px;"></textarea>';
  if(isset($_GET['lid']))
	echo '<input type="hidden" name="mLID" value="'.$_GET['lid'].'">';
  wp_nonce_field('argwa_import_leads','argwa_import_leads_nonce');
  echo '</span></form>';
}


function argwa_add_form() {
global $wpdb;

if(isset($_GET['mid']) || isset($_GET['lid'])) {
if(isset($_GET['mid'])) { $row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ar_gwa_msg WHERE mID='{$_GET['mid']}'",ARRAY_A); }
$row = $row[0];
$opt = array('text' => '', 'html' => '');
$opt[$row['mType']] = 'selected';
if($_GET['lid']) { $ll = $_GET['lid']; }
else $ll = $row['mLID'];
echo '<div class="wrap"><form name="mForm" method="post" action="?page=argwa_page">';
echo '<table align="center"><tr><td>'.__("LIST",'argwa').'</td><td style="font-size:13pt;"><strong>'.argwa_get_listname($ll).'</strong></td></tr>';
if($row['mDate']!='' || isset($_GET['mdate'])) {
if(isset($_GET['mdate'])) $dd=$_GET['mdate']; else $dd =$row['mDate'];
	echo '<tr><td>'.__("Date",'argwa').'</td>'.'<td><input type="text" name="mDate" value="'.$dd.'"> YYYY-MM-DD</td></tr>';
} else if(!isset($_GET['argwasend']))
	echo '<tr><td>'.__("Day",'argwa').'</td>'.'<td><input type="text" name="mDay" style="width:100px;" value="'.$row['mDay'].'"> (0 - 356)</td></tr>';
	echo '<tr><td>'.__("Subject",'argwa').'</td>';
	echo '<td><input type="text" name="mSubject" style="width:500px;" maxlength="250" value="'.stripslashes($row['mSubject']).'"></td></tr>';
	echo '<tr><td colspan=2><div id="tabs"><ul><li><a href="#tabs-1">Text</a></li><li><a href="#tabs-2">HTML</a></li></ul><div id="tabs-1"><textarea id="editorContent" name="mBodyText" style="width:500px; height:180px;">'.stripslashes(htmlentities($row['mBodyText'])).'</textarea></div>';
	echo '<div id="tabs-2">';
 echo '<textarea id="mBodyHtml" class="mBodyHtml" name="mBodyHtml" style="width:500px; height:180px;">'.stripslashes(htmlentities($row['mBodyHtml'])).'</textarea>';
?>
	<script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function() {
			var opts5 = {
				cssClass : 'el-rte',
				// lang     : 'ru',
				height   : 100,
				width     : 500,
				toolbar  : 'compact',
				cssfiles : ['css/elrte-inner.css']
			}
			jQuery('#mBodyHtml').elrte(opts5);
		})
	</script>
<?php
echo '</div></div></td></tr>';
	?>
<script>
jQuery(document).ready(function($) {
	$('#tabs').tabs();
});
</script>
	<tr>
		<td colspan=2 style="padding:5px;width:450px;"><?php _e('<strong>Personalize your messages</strong> and use these custom tags in your emails.','argwa');?>  <strong style="font-size:9pt;"> <&#63;Name&#63;> <&#63;Email&#63;> <&#63;Unsubscribe&#63;> <&#63;Field1&#63;> <&#63;Field2&#63;> <&#63;Field3&#63;> <&#63;Field4&#63;> <&#63;Field5&#63;> <?php if(file_exists(dirname(__FILE__).'/ar_gwa_user_edit.php'))echo "<&#63;Update&#63;>";?></strong></td>
</tr><?php
if(isset($_GET['mid']))	echo '<tr><td colspan=2><input name="argwa_update_message" type="submit" class="button-primary" value="'.__("Update Autoresponder Message",'argwa').'">'; else	if(!isset($_GET['argwasend'])) echo '<tr><td colspan=2><input name="argwa_add_message" type="submit" class="button-primary" value="'.__("Add New Autoresponder Message",'argwa').'">'; else echo '<tr><td colspan=2><input name="argwa_send_message" type="submit" class="button-primary" value="'.__("Send Instant Broadcast Message",'argwa').'">';
  if( !class_exists('ar_gwa_attach') & file_exists(dirname(__FILE__).'/ar_gwa_attach.php')) include_once(dirname(__FILE__).'/ar_gwa_attach.php');
  if(class_exists('ar_gwa_attach')) { echo "&nbsp;<span style='color:#00f;font-weight:bold;'>".__("ATTACH FILE:",'argwa')."</span>&nbsp;".admin_ar_gwa_attach($row['mAttach']).''; }
  echo '</td></tr></table>';
  if(isset($_GET['mid']))
	echo '<input type="hidden" name="mID" value="'.$_GET['mid'].'">';
  if(isset($_GET['lid']))
	echo '<input type="hidden" name="mLID" value="'.$_GET['lid'].'">';
  wp_nonce_field('argwa_message','argwa_message_nonce');
  echo '</form>';
  echo '<div style="margin-left:60px;margin-top:10px;">';
  if(class_exists('ar_gwa_attach') && ($tcc = new ar_gwa_attach)) $tcc->ar_gwa_upload();
  echo '</div>';
 } // if
}

function argwa_install () {
global $wpdb;

   $table_name = $wpdb->prefix . "ar_gwa_leads";
   $table_name2 = $wpdb->prefix . "ar_gwa_queue";
   if((@$wpdb->get_var("show tables like '$table_name'") == $table_name) &&
       (@$wpdb->get_var("show tables like '$table_name2'") != $table_name2)) {
    @$wpdb->query("ALTER TABLE `".$wpdb->prefix."ar_gwa_leads` RENAME `".$wpdb->prefix."ar_gwa_leads_tmp`");
    @$wpdb->query("ALTER TABLE `".$wpdb->prefix."ar_gwa_msg` RENAME `".$wpdb->prefix."ar_gwa_msg_tmp`");
    $repopulate = 1;
   }
   $bloginfo = array('url' => get_bloginfo('url'),
                     'name' => mysql_real_escape_string(get_bloginfo('name')),
                     'admin' => get_bloginfo('admin_name'),
                     'email' => get_bloginfo('admin_email'));
    add_option('ar_gwa_addsub_1','checked');
    add_option('ar_gwa_wrap_1','50');
    add_option('ar_gwa_notify_1','checked');
    add_option('ar_gwa_unsub','Click to unsubscribe.');
    add_option('ar_gwa_no_spam','0');
    if(!(get_option('ar_gwa_aff_id'))){
    add_option('ar_gwa_aff_id','1');
    add_option('ar_gwa_aff_url','http://wordpressemailsoftware.com');
    add_option('ar_gwa_aff_text','Free Autoresponder');
    add_option('ar_gwa_aff_title','Software for Email Marketing');
    }
    if(get_option('ar_gwa_cb') != "unbranded")
    update_option('ar_gwa_cb_1','checked');
    update_option('ar_gwa_remove_1','checked');
    add_option('ar_gwa_enc_1','8bit');
    add_option('ar_gwa_char_1','UTF-8');
    add_option('ar_gwa_reg_optin','checked');
    add_option('ar_gwa_from',$bloginfo['email']);
    wp_schedule_event((time()+86400), 'daily', 'argwa_daily_event');


   $dn = 0;
   $table_name = $wpdb->prefix . "ar_gwa_leads";
   if(@$wpdb->get_var("show tables like '$table_name'") != $table_name) {

    $sql = "CREATE TABLE `" . $table_name . "` (
  `lID` int(11) NOT NULL auto_increment,
  `lLID` int(11) NOT NULL,
  `lName` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `lEmail` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `lDateEntry` date default NULL,
  `lDateOut` date default NULL,
  `lMOut` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci  default NULL,
  `lCnf` varchar(65) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
  `lTime` timestamp NOT NULL,
  `lIP` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
  `lDNS` varchar(65) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
  `lField1` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `lField2` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `lField3` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `lField4` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `lField5` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  PRIMARY KEY  (`lID`),
  KEY `lCnf` (`lCnf`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
   $results = @$wpdb->query( $sql );
  }

   if(@$wpdb->get_var("show columns from $table_name like 'lField1'") != 'lField1') {
    $psql="ALTER TABLE `$table_name`
    ADD `lField1` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    ADD `lField2` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    ADD `lField3` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    ADD `lField4` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    ADD `lField5` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL";
    $results = @$wpdb->query( $psql );
   }

   $table_name = $wpdb->prefix . "ar_gwa_lists";
   if(@$wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sql = "CREATE TABLE `" . $table_name . "` (
  `xID` int(11) NOT NULL auto_increment,
  `xName` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci  default NULL,
  `xEmail` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci  default NULL,
  `xSubscribe` text,
  `xLabel` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
  `xPage` int(4) NOT NULL,
  `xReturn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci  default NULL,
   PRIMARY KEY  (`xID`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
   $results = @$wpdb->query( $sql );
    $subscribe = mysql_real_escape_string('<!-- [GWA] AutoResponder Begin -->'."\n".'<form name="ARGWA" action="'.get_bloginfo('url').'" method="post"><strong>Name:</strong>&nbsp;<input id="argwa_name" name="gwaname" size="16" type="text" /><br /><strong>Email:</strong>&nbsp;<input id="argwa_email" name="gwaemail" size="16" type="text" /><br /><input class="button" onclick="return gwaCheckForm1(this.form)" name="Add" type="submit" value="SUBSCRIBE" /><input name="act" type="hidden" value="s_add" /><input name="listid" type="hidden" value="1" /></form>'."\n".'<!-- [GWA] AutoResponder End -->');
    $sql = "INSERT INTO `".$table_name."` (`xID`, `xName`, `xEmail`, `xSubscribe`, `xLabel`, `xPage`) values (1,'{$bloginfo['name']}','{$bloginfo['email']}','$subscribe','{$bloginfo['name']}','')";
    $results = @$wpdb->query( $sql );
  } else {
  $results = @$wpdb->query( "ALTER TABLE `".$table_name."` CHANGE `xReturn` `xReturn` VARCHAR( 255 ) NOT NULL");
  }

   $table_name = $wpdb->prefix . "ar_gwa_pages";
     if(@$wpdb->get_var("show tables like '$table_name'") != $table_name) {
      $sql = "CREATE TABLE `" . $table_name . "` (
    `pID` int(11) NOT NULL auto_increment,
    `pLID` int(11) default NULL,
    `pConfMsg` text CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    `pConf` text CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    `pSub` text CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    `pUnsub` text CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
    `pErr` text CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
      PRIMARY KEY  (`pID`),
      INDEX (`pLID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
     $results = @$wpdb->query( $sql );
     $pConf = get_option('ar_gwa_addlead');
     if(!isset($pConf) || $pConf=='') $pConf = "You have been subscribed successfully.";
     $pUnsub = get_option('ar_gwa_rmvlead');
     if(!isset($pUnSub) || $pUnsub=='') $pUnsub = "You have been unsubscribed successfully.";
     $pErr = get_option('ar_gwa_badlead');
     if(!isset($pErr) || $pErr=='') $pErr = "There was a problem. Please contact the blog administrator for assistance.";
     @$wpdb->query("INSERT INTO ".$wpdb->prefix."ar_gwa_pages (`pLID`,`pConfMsg`,`pSub`,`pConf`,`pUnsub`,`pErr`) VALUES ('1','".__("Please click the link to confirm your subscription.",'argwa')."','".__("Please check your email to confirm your subscription.",'argwa')."','".$wpdb->escape($pConf)."','".$wpdb->escape($pUnsub)."','".$wpdb->escape($pErr)."')");
   }

   $table_name = $wpdb->prefix . "ar_gwa_msg";
   if(@$wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sql = "CREATE TABLE `" . $table_name . "` (
  `mID` int(11) NOT NULL auto_increment,
  `mLID` int(11) default NULL,
  `mSubject` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `mBody` text CHARACTER SET utf8 COLLATE utf8_general_ci ,
  `mBodyText` text CHARACTER SET utf8 COLLATE utf8_general_ci ,
  `mBodyHtml` text CHARACTER SET utf8 COLLATE utf8_general_ci ,
  `mDay` int(2) default NULL,
  `mType` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  default NULL,
    PRIMARY KEY  (`mID`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
   $results = @$wpdb->query( $sql );
   }

  if($wpdb->get_var("SHOW columns FROM `".$wpdb->prefix."ar_gwa_msg` like 'mBodyText'") != 'mBodyText') {
    $psql="ALTER TABLE `".$wpdb->prefix."ar_gwa_msg` ADD `mBodyText` text";
    @$results = $wpdb->query( $psql );
    $psql="ALTER TABLE `".$wpdb->prefix."ar_gwa_msg` ADD `mBodyHtml` text";
    @$results = $wpdb->query( $psql );
    if($rec = @$wpdb->get_results("SELECT * FROM `".$wpdb->prefix."ar_gwa_msg` WHERE 1",ARRAY_A)) {
        foreach($rec as $r) {
        if($r['mType']=='text')
          @$wpdb->query( "UPDATE ".$wpdb->prefix."ar_gwa_msg SET mBodyText=mBody WHERE mID=".$r['mID'] );
        else
          @$wpdb->query( "UPDATE ".$wpdb->prefix."ar_gwa_msg SET mBodyHtml=mBody WHERE mID=".$r['mID'] );
        }
    }
  }

   $table_name = $wpdb->prefix . "ar_gwa_log";
   if(@$wpdb->get_var("show tables like '$table_name'") != $table_name) {
   $sql = "CREATE TABLE `$table_name` (
  `id` int(11) NOT NULL auto_increment,
  `newsletterQID` int(11) NOT NULL default 0,
  `method` set('mail','smtp') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `From` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `FromName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `Host` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `SMTPAuth` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `Username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `Password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `recepientMail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `recepientName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `body` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ContentType` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `priority` int(1) NOT NULL default '0',
  `SendDate` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `QLID` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci default NOT NULL default '',
  `port` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci default NOT NULL default '',
  `charset` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci default NOT NULL default '',
  `encoding` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci default NOT NULL default '',
  `Response` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
    $results = @$wpdb->query( $sql );
  }
  if($repopulate==1) {
    $table_name = 'ar_gwa_leads';
    $rec = @$wpdb->get_results("SELECT * FROM ".$wpdb->prefix.$table_name."_tmp");
    foreach($rec as $re)
    @$wpdb->query("INSERT INTO ".$wpdb->prefix.$table_name." SET lID=NULL, lLID='1',lName='{$re->lName}',lEmail='{$re->lEmail}',lDateEntry='{$re->lDateEntry}',lDateOut='{$re->lDateOut}',lMOut='{$re->lMOut}',lCnf='{$re->lCnf}',lTime='',lIP='{$re->lIP}',lDNS='{$re->lDNS}', lField1='',lField2='',lField3='',lField4='',lField5=''");

    $table_name = 'ar_gwa_msg';
    @$wpdb->query("INSERT INTO ".$wpdb->prefix.$table_name." (SELECT * FROM ".$wpdb->prefix.$table_name."_tmp)");
    @$wpdb->query("UPDATE ".$wpdb->prefix.$table_name." set mLID='1'");
  }
  if (wp_next_scheduled('ar_gwa_daily_event')) wp_clear_scheduled_hook('ar_gwa_daily_event');
}

function argwa_responder_start() {
if (!wp_next_scheduled('argwa_daily_event') || ((wp_next_scheduled('argwa_daily_event')-time())<0) ) {
  wp_clear_scheduled_hook('argwa_daily_event');
 $sched = time()+86000;
 wp_schedule_event( $sched, 'daily', 'argwa_daily_event' );
 argwa_do_this_daily('none');
 }
}

function argwa_do_this_daily($n=NULL) {
global $wpdb;
$ar_cnt = 0;
$ms = new ARGWAMailProcess();
$qr="SELECT MAX(mDay) FROM ".$wpdb->prefix."ar_gwa_msg";
$rs = @$wpdb->get_results($qr,ARRAY_A);
$qry = "SELECT TO_DAYS(current_date) as AA, TO_DAYS(lDateEntry) as BB, lID, lLID, lMOut FROM ".$wpdb->prefix."ar_gwa_leads WHERE lCnf=1 ORDER BY lID DESC";
$res = @$wpdb->get_results($qry,ARRAY_A);
if(count($res)>0) {
 foreach($res as $row)
 {
	$dif = $row['AA'] - $row['BB'];
	if($dif > 0 && $dif < 1000)
	{
		$mDay = $row['lMOut'] + 1;
		while($mDay <= $dif)
		{
			if($ms->setLead($row['lID']) && $ms->setMsg($mDay,$row['lLID']))
			{
				$ms->mailLead();
				$ar_cnt++;
			}
			$mDay++;
		}
	}
}
}
  if (!(wp_next_scheduled('argwa_daily_event'))) {
   wp_clear_scheduled_hook('argwa_daily_event');
   wp_schedule_event( time()+86400, 'daily', 'argwa_daily_event' );
  }
}

function set_argwa_options($s=NULL)
{
global $wpdb;
$sendEmailSub =1;
if($_REQUEST['ar_gwa_addsub']=="" && isset($s) && $s==1 && isset($_REQUEST['ar_gwa_submit_cb']))
$sendEmailSub =0;
if($_REQUEST['ar_gwa_unsub']!="")
$sendEmail = $_REQUEST['ar_gwa_unsub'];
else
$sendEmail = get_option('ar_gwa_unsub');

delete_option('ar_gwa_addsub');
delete_option('ar_gwa_unsub');
add_option('ar_gwa_addsub',$sendEmailSub,'');
add_option('ar_gwa_unsub',$sendEmail,'');
}

function unset_argwa_options()
{
global $wpdb;
if(get_option('ar_gwa_delete')=='checked') {
 $sql = "DELETE FROM ".$wpdb->prefix.'options WHERE option_name LIKE "ar_gwa_%" OR option_name LIKE "argwa_%"';
 @$wpdb->query($sql);
 $sql = "DROP table ".$wpdb->prefix."ar_gwa_leads;";
 @$wpdb->query($sql);
 $sql = "DROP table ".$wpdb->prefix."ar_gwa_leads_tmp;";
 @$wpdb->query($sql);
 $sql = "DROP TABLE ".$wpdb->prefix."ar_gwa_lists;";
 @$wpdb->query($sql);
 $sql = "DROP TABLE ".$wpdb->prefix."ar_gwa_msg;";
 @$wpdb->query($sql);
 $sql = "DROP TABLE ".$wpdb->prefix."ar_gwa_msg_tmp;";
 @$wpdb->query($sql);
 $sql = "DROP TABLE ".$wpdb->prefix."ar_gwa_log;";
 @$wpdb->query($sql);
 $sql = "DROP TABLE ".$wpdb->prefix."ar_gwa_pages;";
 @$wpdb->query($sql);
}
wp_clear_scheduled_hook('argwa_daily_event');
wp_clear_scheduled_hook('argwa_regular_event');

delete_option('ar_gwa_delete');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////

function ar_gwa_js_admin_header() {
wp_print_scripts( array( 'sack' ));?>
<script type="text/javascript">
 //<![CDATA[
 function ar_gwa_name_update( name_field, id_field, email_field )
 {
  var mysack = new sack(
       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );
  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "ar_gwa_name_update" );
  mysack.setVar( "lID", id_field );
  mysack.setVar( "lName", name_field.value );
  mysack.setVar( "lEmail", email_field );
  mysack.encVar( "cookie", document.cookie, false );
  mysack.onError = function() { alert(__('Ajax error updating record','argwa'))};
  mysack.runAJAX();
  return true;
}

 function ar_gwa_email_update( email_field, id_field )
 {
  var mysack = new sack(
       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );
  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "ar_gwa_email_update" );
  mysack.setVar( "lID", id_field );
  mysack.setVar( "lEmail", email_field.value );
  mysack.encVar( "cookie", document.cookie, false );
  mysack.onError = function() { alert(__('Ajax error updating record','argwa'))};
  mysack.runAJAX();
  return true;
  }

 function ar_gwa_day_update( day_field, id_field, email_field )
 {
  var mysack = new sack(
       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );
  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "ar_gwa_day_update" );
  mysack.setVar( "lID", id_field );
  mysack.setVar( "lEmail", email_field );
  mysack.setVar( "lMOut", day_field.value );
  mysack.encVar( "cookie", document.cookie, false );
  mysack.onError = function() { alert(__('Ajax error updating record','argwa'))};
  mysack.runAJAX();
  return true;
  }

 function ar_gwa_fields_update( id_field, field_one, field_two, field_three, field_four, field_five )
 {
  var mysack = new sack(
       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );
  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "ar_gwa_fields_update" );
  mysack.setVar( "lID", id_field );
  mysack.setVar( "field_1", field_one.value );
  mysack.setVar( "field_2", field_two.value );
  mysack.setVar( "field_3", field_three.value );
  mysack.setVar( "field_4", field_four.value );
  mysack.setVar( "field_5", field_five.value );
  mysack.encVar( "cookie", document.cookie, false );
  mysack.onError = function() { alert(__('Ajax error updating record','argwa'))};
  mysack.runAJAX();
  return true;
  }
  //]]>
</script>
<?php
}

function ar_gwa_name_update() {
global $wpdb;
$qry = 'UPDATE `'.$wpdb->prefix.'ar_gwa_leads` SET lName = "'.$wpdb->escape($_POST['lName']).'" WHERE lID="'.$wpdb->escape($_POST['lID']).'"';
$z = $wpdb->query($qry);
$val = '<input  onblur="sss = confirm(\''.__("Confirm changes to",'argwa')."&nbsp;".$_POST['lEmail'].'?\');if(sss==true)ar_gwa_name_update(document.getElementById(\'argwaleadname'.$_POST['lID'].',1\'),'.$_POST['lID'].',\''.$_POST['lEmail'].'\');return false;" type="text" name="lLead" id="argwaleadname'.$_POST['lID'].'" value="'.$_POST['lName'].'" size="22">';

die("document.getElementById('tdname".$_POST['lID']."').innerHTML = '".addslashes($val)."'
alert('".__("Changes saved to database.",'argwa')."')");
}

function ar_gwa_email_update() {
global $wpdb;
$qry = 'UPDATE `'.$wpdb->prefix.'ar_gwa_leads` SET lEmail = "'.$wpdb->escape($_POST['lEmail']).'" WHERE lID="'.$wpdb->escape($_POST['lID']).'"';
$z = $wpdb->query($qry);
$val = '<input  onblur="sss = confirm(\''.__("Confirm changes to",'argwa')."&nbsp;".$_POST['lEmail'].'?\');if(sss==true)ar_gwa_email_update(document.getElementById(\'argwaleademail'.$_POST['lID'].'\'),'.$_POST['lID'].');return false;" type="text" name="lEmail" id="argwaleademail'.$_POST['lID'].'" value="'.$_POST['lEmail'].'" size="22">';

die("document.getElementById('tdemail".$_POST['lID']."').innerHTML = '".addslashes($val)."'
alert('".__("Changes saved to database.",'argwa')."')");
}

function ar_gwa_day_update() {
global $wpdb;
if(preg_match("/^\d+$/",$_POST['lMOut'])) {
$qry = 'UPDATE `'.$wpdb->prefix.'ar_gwa_leads` SET lMOut = "'.$wpdb->escape($_POST['lMOut']).'" , lDateEntry = "'.date('Y-m-d', strtotime('-'.($_POST['lMOut']+1).' days')).'" WHERE lID="'.$wpdb->escape($_POST['lID']).'"';
$z = $wpdb->query($qry);
$val = '<input  onblur="sss = confirm(\''.__("Confirm changes to",'argwa')."&nbsp;".$_POST['lEmail'].'?\');if(sss==true)ar_gwa_day_update(document.getElementById(\'argwaleadday'.$_POST['lID'].'\'),'.$_POST['lID'].',\''.$_POST['lEmail'].'\');return false;" type="text" name="lMOut" id="argwaleadday'.$_POST['lID'].'" value="'.$_POST['lMOut'].'" size="3">';

die("document.getElementById('tdday".$_POST['lID']."').innerHTML = '".addslashes($val)."'
document.getElementById('tdentry".$_POST['lID']."').innerHTML = '".date('Y-m-d', strtotime('-'.($_POST['lMOut']+1).' days'))."'
alert('".__("Changes saved to database.",'argwa')."')");
} else {
die("alert('".__("Please enter a valid Msg Day Number 0-365 only.",'argwa')."')");
}
}

function ar_gwa_fields_update() {
global $wpdb;
#$wpdb->show_errors();
$qry = 'UPDATE '.$wpdb->prefix.'ar_gwa_leads SET lField1 = "'.$wpdb->escape($_POST['field_1']).'" , lField2 = "'.$wpdb->escape($_POST['field_2']).'", lField3 = "'.$wpdb->escape($_POST['field_3']).'", lField4 = "'.$wpdb->escape($_POST['field_4']).'", lField5 = "'.$wpdb->escape($_POST['field_5']).'" WHERE lID="'.$wpdb->escape($_POST['lID']).'"';
$z = $wpdb->query($qry);
die("document.getElementById('gwatable".$_POST['lID']."').innerHTML = '".__("Updated",'argwa')."&nbsp;".$z."'");
}

function argwa_print_scripts() {
  wp_enqueue_script('jcap', ARGWA_PLUGIN_URL.'js/jcap.js');
  wp_enqueue_script('md5', ARGWA_PLUGIN_URL.'js/md5.js');
}

function argwa_reg_subscribe_user() {
 global $wpdb;
 $lName = $_REQUEST['user_login'];
 $lEmail = $_REQUEST['user_email'];
 if(is_array($_REQUEST['LIST'])){
  foreach($_REQUEST['LIST'] as $list) {
  global $wpdb;
	 $qry = "INSERT INTO ".$wpdb->prefix."ar_gwa_leads(lLID, lName, lEmail, lDateEntry, lDateOut, lMOut, lCnf) VALUES('".$list."','".$lName."', '".$lEmail."' ,current_date, current_date, '-1','1')";
	 $wpdb->query($qry);
  }
 }
}

function argwa_reg_subscribe_form() {
 global $wpdb;
 $qry = "SELECT * FROM ".$wpdb->prefix."ar_gwa_lists";
 $rec = $wpdb->get_results($qry,ARRAY_A);
 foreach($rec as $rc) {
  if(get_option('ar_gwa_reg_optin_'.$rc['xID']) == 'checked') { echo '<input type="checkbox" name="LIST[]" value="'.$rc['xID'].'" checked> '.__("Subscribe to our newsletter",'argwa').' '.$rc['xName'].'.<br /><br />'; }
  else if(get_option('ar_gwa_reg_optinb_'.$rc['xID']) == 'checked') { echo '<input type="hidden" name="LIST[]" value="'.$rc['xID'].'" checked>';}
  }
}

if(!function_exists('argwa_short_code')) {
  function argwa_short_code($atts) {
  global $my_argwa_metabox_plugin;
  $ats = shortcode_atts( array('listid' => 0), $atts );
  return $my_argwa_metabox_plugin->argwa_form_display($atts['listid'],1);
  }
}

$my_argwa_metabox_plugin = new argwa_metabox_plugin();
if($my_argwa_metabox_plugin->help==1) add_action('admin_footer','argwa_help');
add_action('admin_head','argwa_dashboard_alert');
add_action('admin_print_scripts', 'ar_gwa_js_admin_header' );
add_action('wp_print_scripts', 'argwa_print_scripts',1);
add_action('wp_ajax_ar_gwa_name_update', 'ar_gwa_name_update' );
add_action('wp_ajax_ar_gwa_email_update', 'ar_gwa_email_update' );
add_action('wp_ajax_ar_gwa_day_update', 'ar_gwa_day_update' );
add_action('wp_ajax_ar_gwa_fields_update', 'ar_gwa_fields_update' );
add_action('widgets_init', create_function('', 'return register_widget("ARGWA_Widget");'));
add_action( 'register_form', 'argwa_reg_subscribe_form' );
add_action( 'user_register', 'argwa_reg_subscribe_user' );
add_action('argwa_daily_event', 'argwa_do_this_daily');
add_shortcode('gwa-autoresponder', 'argwa_short_code');
add_shortcode('GWAR', 'argwa_short_code');
register_activation_hook(__FILE__, 'argwa_install');
register_activation_hook(__FILE__, 'argwa_responder_start');
register_deactivation_hook(__FILE__,'unset_argwa_options');

//////////////////////////////////////////////////////////////////////////////////////////////////////
class ARGWAMailProcess
{
	var $lead   = array();
	var $msg    = array();
	var $arFrom;

	function ARGWAMailProcess()
	{
		$this->arFrom = get_option('ar_gwa_from');
	}

	function setLead($lID)
	{
	global $wpdb;
		// get lead info
		$qry = "SELECT * FROM ".$wpdb->prefix."ar_gwa_leads ,".$wpdb->prefix."ar_gwa_lists WHERE lLID = xID AND lID='$lID'";
    $res = $wpdb->get_results($qry,ARRAY_A);
		if($res)
		{
			$this->lead = $res[0];
			return 1;
		}
		return 0;
	}

	function setWPLead($lID)
	{
	global $wpdb;
    $xEmail = get_bloginfo('admin_email');
    $xName = get_bloginfo('name');
		$qry = "SELECT * FROM ".$wpdb->prefix."users WHERE ID ='$lID'";
    $res = $wpdb->get_results($qry,ARRAY_A);
		if($res)
		{
			$this->lead = $res[0];
			$this->lead['xEmail'] = $xEmail;
			$this->lead['xName'] = $xName;
			return 1;
		}
		return 0;
	}

	function setLead2($listID)
	{
	global $wpdb;
		// get lead info
		$qry = "SELECT * FROM ".$wpdb->prefix."ar_gwa_lists WHERE xID='$listID'";
    $res = $wpdb->get_results($qry,ARRAY_A);
		if($res)
		{
			$this->lead = $res[0];
			$this->lead['lEmail']=get_bloginfo('admin_email');
			$this->lead['lName']=$this->lead['xName'];
      return 1;
		}
		return 0;
	}

	function setMsg($mDay,$lLID=null)
	{
	global $wpdb;

	$this->msg['mDay'] = $mDay;
		$qry = "SELECT * FROM ".$wpdb->prefix."ar_gwa_msg WHERE mDay='$mDay'";
    if($lLID) $qry .= " AND mLID=".$lLID;
		$res = $wpdb->get_results($qry,ARRAY_A);
    if($res)
		{
			$this->msg = $res[0];
if(!class_exists('ar_gwa_newsletter') && file_exists(dirname(__FILE__).'/ar_gwa_newsletter.php'))  include_once(dirname(__FILE__).'/ar_gwa_newsletter.php');
if(class_exists('ar_gwa_newsletter')) {if(isset($this->msg['nlid']) && ($this->msg['nlid']>0)) $this->msg['mBodyHtml'] = str_replace("{CONTENT}", $this->msg['mBodyHtml'], $wpdb->get_var("SELECT content FROM `".$wpdb->prefix."ar_gwa_newsletter` WHERE id='".$this->msg['nlid']."'"));}
  		if($this->msg['mType'] == 'html') {
				$this->msg['mHeader'] = 'text/html';
    		$this->msg['mBody'] = $this->msg['mBodyHtml'];
    		$this->msg['mBodyAlt'] = $this->msg['mBodyText'];
      } else {
				$this->msg['mHeader'] = 'text/plain';
    		$this->msg['mBody'] = $this->msg['mBodyText'];
			}
      return 1;
		}
		return 0;
	}

	function setMsg2($msg)
	{
		$this->msg['mSubject'] = $msg['mSubject'];
		$this->msg['mType'] = $msg['mType'];
		$this->msg['mAttach'] = $msg['mAttach'];
    if($msg['mType'] == 'html') {
    		$this->msg['mBody'] = $msg['mBodyHtml'];
    		$this->msg['mBodyAlt'] = $msg['mBodyText'];
				$this->msg['mHeader'] = 'text/html';
    } else {
				$this->msg['mHeader'] = 'text/plain';
    		$this->msg['mBody'] = $msg['mBodyText'];
		}
    return 1;
	}

	function mailWPLead($mDay=null,$lID=null,$dbug=NULL,$direct=NULL)
	{
		global $wpdb;
		if(!count($this->lead) || !count($this->msg)) { return 0; }
		$this->formatMsg(1);
        	$data = array(
            'method'      => 'mail',
    				'From'     		=> $this->lead['xEmail'],
    				'FromName' 		=> $this->lead['xName'],
    				'recepientMail' => $this->lead['user_email'],
    				'recepientName' => $this->lead['display_name'],
    				'subject'       => $this->msg['mSubject'],
    				'body'          => stripslashes($this->msg['mBody']),
    				'bodyAlt'          => stripslashes($this->msg['mBodyAlt']),
    				'ContentType'   => $this->msg['mHeader'],
    				'priority'      => '3',
    				'SendDate'      => time(),
    				'QLID'          => $this->lead['lLID'],
            'port' => get_option('ar_gwa_smtp_port_'.$this->lead['lLID']),
            'charset' => get_option('ar_gwa_char_'.$this->lead['lLID']),
            'encoding' => get_option('ar_gwa_enc_'.$this->lead['lLID']),
    				'mAttach'       => $this->msg['mAttach'],
    				);
    $add = argwa_add_email($data,$dbug);
	}

	function mailLead($mDay=null,$lID=null,$dbug=NULL,$direct=NULL)
	{
		global $wpdb;
		if(!count($this->lead) || !count($this->msg)) { return 0; }
		$this->formatMsg();
        	$data = array(
            'method'      => 'mail',
    				'From'     		=> $this->lead['xEmail'],
    				'FromName' 		=> $this->lead['xName'],
    				'recepientMail' => $this->lead['lEmail'],
    				'recepientName' => $this->lead['lName'],
    				'subject'       => $this->msg['mSubject'],
    				'body'          => stripslashes($this->msg['mBody']),
    				'bodyAlt'          => stripslashes($this->msg['mBodyAlt']),
    				'ContentType'   => $this->msg['mHeader'],
    				'priority'      => '3',
    				'SendDate'      => time(),
    				'QLID'          => $this->lead['lLID'],
    				'mAttach'       => $this->msg['mAttach']
    				);
    $add = $this->argwa_add_email($data,$dbug);

    if(isset($this->msg['mDay']))
      $subq = "lMOut='".$this->msg['mDay']."', ";
		$qry = "UPDATE ".$wpdb->prefix."ar_gwa_leads SET $subq lDateOut=current_date WHERE lID='".$this->lead['lID']."'";
		$wpdb->query($qry);
	}

function argwa_add_email($params,$dbug){
    global $wpdb;
    $params['QLID'] = $this->lead['lLID'];
            $params['charset'] = get_option('ar_gwa_char_'.$this->lead['lLID']);
            $params['encoding'] = get_option('ar_gwa_enc_'.$this->lead['lLID']);

    $data=array('method'   		=> ( isset($params['method']) )    	   ? $params['method']   	 : 'mail',
    				'From'     		=> ( isset($params['From']) )     	   ? $params['From']     	 : 'root@localhost',
    				'FromName' 		=> ( isset($params['FromName']) ) 	   ? $params['FromName'] 	 : 'Root Account',
    				'recepientMail' => ( isset($params['recepientMail']) ) ? $params['recepientMail'] : false,
    				'recepientName' => ( isset($params['recepientName']) ) ? $params['recepientName'] : '',
    				'subject'       => ( isset($params['subject']) )       ? $params['subject']       : false,
    				'body'          => ( isset($params['body']) )          ? $params['body']          : false,
    				'bodyAlt'          => ( isset($params['bodyAlt']) )   ? $params['bodyAlt']        : false,
    				'ContentType'   => ( isset($params['ContentType']) )   ? $params['ContentType']   : 'text/plain',
    				'priority'      => ( isset($params['priority']) )      ? $params['priority']      : '3',
    				'SendDate'      => ( isset($params['SendDate']) )      ? $params['SendDate']      : time(),
    				'QLID'        => ( isset($params['QLID']) )           ? $params['QLID']           : '1',
    				'charset'        => ( isset($params['charset']) )           ? $params['charset']    : false,
    				'encoding'        => ( isset($params['encoding']) )           ? $params['encoding'] : false,
    				);
    	$error=false;
    	foreach ( $data as $key=>$value ){
        $sql1 .= "`".$key.'`,';
        $sql2 .= "'".$wpdb->escape($value)."',";
    		if ($value===false) {
    			$error=true;
    			$errorMsg="Cannot send mail. Empty value for [$key]";
    		}
    	}
#var_dump($data);exit;
    	if ( $error===true ){
    	  #die( $errorMsg);
      	$this->errorMsg=$errorMsg;
      	return false;
} else {
  $mime_boundary = "b1_f8294cc0b53b06489f5c1364d85c704f";
  if($data['ContentType'] =='text/html') {
  $body_text = $data['bodyAlt'];
  $body_html = $data['body'];
  $body = "--{$mime_boundary}\n" ."Content-Type: text/plain; charset=\"{$data['charset']}\"\n" ."Content-Transfer-Encoding: {$data['encoding']}\n\n" .$body_text . "\n\n";
  $body .= "--{$mime_boundary}\n" ."Content-Type: text/html; charset=\"{$data['charset']}\"\n" ."Content-Transfer-Encoding: {$data['encoding']}\n\n" .$body_html . "\n\n";
  mail($data['recepientMail'], $data['subject'], $body, "From: {$data['FromName']} <{$data['From']}>"."\r\n"."Mime-Version: 1.0\r\n"."Content-Type: multipart/alternative;\r\n   boundary=\"{$mime_boundary}\"\r\n\r\n");
  } else {
    $suc = @mail($data['recepientMail']."\r\n",$data['subject']."\r\n",$data['body']."\r\n","From: {$data['FromName']} <{$data['From']}>"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset={$data['charset']}\nContent-Transfer-Encoding: {$data['encoding']}\n",'-f'.$from->xEmail);
    if(!$suc) $suc = @mail($data['recepientMail']."\r\n",$data['subject']."\r\n",$data['body']."\r\n","From: {$data['FromName']} <{$data['From']}>"."\r\n"."Mime-Version: 1.0\nContent-Type: text/plain; charset={$data['charset']}\nContent-Transfer-Encoding: {$data['encoding']}\n");
    if(!$suc) $suc= mail($data['recepientMail'],stripslashes($data['subject']),stripslashes(str_replace("\r\n","\n",trim($data['body']))),"From: {$data['FromName']} <{$data['From']}>\nMime-Version: 1.0\nContent-Type: text/plain; charset={$data['charset']}\nContent-Transfer-Encoding: {$data['encoding']}\n");
  }
   $sql1 = substr($sql1, 0, -1);
   $sql2 = substr($sql2, 0, -1);
   $qry = "INSERT INTO {$wpdb->prefix}ar_gwa_log ($sql1) VALUES ($sql2)";
   $insertAttempt=$wpdb->query($qry);
   if ($insertAttempt===true) return true; else { return false; }
 }
}

	function formatMsg($WP=NULL)
	{
		$unsubscribe = get_bloginfo('url').'?argwauid='.$this->lead['lID'] . '&argwaeml='.base64_encode($this->lead['lEmail']);
		$archivelink = get_bloginfo('url').'?aID='.$this->lead['lID'] . '&Email='.htmlentities($this->lead['lEmail']);
		$affiliate = 'http://www.freeautoresponder.biz/?/'.get_option('ar_gwa_aff_id');
		$ar_gwa_no_spam = get_option('ar_gwa_no_spam');
    if($ar_gwa_no_spam=='checked'){
		$ar_gwa_nospam_text = "\r\n\r\n".get_option('ar_gwa_nospam');
    } else {
		$ar_gwa_nospam_text = "\r\n\r\n".get_option('ar_gwa_footer_'.$this->lead['lLID']);
    }
     $affiliate_text_text = __("Free Autoresponder for Wordpress",'argwa')."\r\n$affiliate";
     $unsubscribe_text = __("Unsubscribe",'argwa')."\r\n$unsubscribe";
     $archivelink_text = __("View your message archive.",'argwa')."\r\n$archivelink";

		if($this->msg['mType'] == 'html') {
       if($ar_gwa_no_spam=='checked' && $ar_gwa_nospam_text != '') {
       $ar_gwa_nospam_text = preg_replace("/\r\n\r\n/",'<p>',$ar_gwa_nospam_text);
       $ar_gwa_nospam_text = preg_replace("/\r\n/",'<br>',$ar_gwa_nospam_text);
       }
     $affiliate_text = '<a href="'.$affiliate.'">'.__("Free Autoresponder for Wordpress",'argwa').'</a>';
     $unsubscribe = '<a href="'.$unsubscribe.'">'.__("Unsubscribe",'argwa').'</a>';
     $archivelink = '<a href="'.$archivelink.'">'.__("View your message archive.",'argwa').'</a>';
     } else {
     $affiliate_text = __("Free Autoresponder for Wordpress",'argwa')."\r\n$affiliate";
     $unsubscribe = __("Unsubscribe",'argwa')."\r\n$unsubscribe";
     $archivelink = __("View your message archive.",'argwa')."\r\n$archivelink";
     }

    if($WP==1) {
    $unsubscribe = "\n\n".__('Sent as part of your blog membership at','argwa').' '.get_bloginfo('name').".\n\n";
		$this->msg['mBody'] = str_replace('<?Name?>', $this->lead['display_name'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Name?>', $this->lead['display_name'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('<?Name?>', $this->lead['display_name'], $this->msg['mSubject']);
		$this->msg['mBody'] = str_replace('<?Email?>', $this->lead['user_email'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Email?>', $this->lead['user_email'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('<?Email?>', $this->lead['user_email'], $this->msg['mSubject']);
    } else {
		$this->msg['mBody'] = str_replace('<?Name?>', $this->lead['lName'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Name?>', $this->lead['lName'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('<?Name?>', $this->lead['lName'], $this->msg['mSubject']);
		$this->msg['mBody'] = str_replace('<?Email?>', $this->lead['lEmail'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Email?>', $this->lead['lEmail'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('<?Email?>', $this->lead['lEmail'], $this->msg['mSubject']);
    }
		$this->msg['mBody'] = str_replace('<?Unsubscribe?>', $unsubscribe, $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Unsubscribe?>', $unsubscribe, $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('<?Archive?>', $archivelink, $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Archive?>', $archivelink, $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('<?Field1?>', $this->lead['lField1'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Field1?>', $this->lead['lField1'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('<?Field2?>', $this->lead['lField2'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Field2?>', $this->lead['lField2'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('<?Field3?>', $this->lead['lField3'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Field3?>', $this->lead['lField3'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('<?Field4?>', $this->lead['lField4'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Field4?>', $this->lead['lField4'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('<?Field5?>', $this->lead['lField5'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('<?Field5?>', $this->lead['lField5'], $this->msg['mBodyAlt']);
//For html emails composed in tinymce
    if($WP==1) {
		$this->msg['mBody'] = str_replace('&lt;?Name?&gt;', $this->lead['display_name'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Name?&gt;', $this->lead['display_name'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('&lt;?Name?&gt;', $this->lead['display_name'], $this->msg['mSubject']);
		$this->msg['mBody'] = str_replace('&lt;?Email?&gt;', $this->lead['user_email'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Email?&gt;', $this->lead['user_email'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('&lt;?Email?&gt;', $this->lead['user_email'], $this->msg['mSubject']);
    } else {
		$this->msg['mBody'] = str_replace('&lt;?Name?&gt;', $this->lead['lName'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Name?&gt;', $this->lead['lName'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('&lt;?Name?&gt;', $this->lead['lName'], $this->msg['mSubject']);
		$this->msg['mBody'] = str_replace('&lt;?Email?&gt;', $this->lead['lEmail'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Email?&gt;', $this->lead['lEmail'], $this->msg['mBodyAlt']);
		$this->msg['mSubject'] = str_replace('&lt;?Email?&gt;', $this->lead['lEmail'], $this->msg['mSubject']);
    }
		$this->msg['mBody'] = str_replace('&lt;?Unsubscribe?&gt;', $unsubscribe, $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Unsubscribe?&gt;', $unsubscribe, $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('&lt;?Archive?&gt;', $archivelink, $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Archive?&gt;', $archivelink, $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('&lt;?Field1?&gt;', $this->lead['lField1'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Field1?&gt;', $this->lead['lField1'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('&lt;?Field2?&gt;', $this->lead['lField2'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Field2?&gt;', $this->lead['lField2'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('&lt;?Field3?&gt;', $this->lead['lField3'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Field3?&gt;', $this->lead['lField3'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('&lt;?Field4?&gt;', $this->lead['lField4'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Field4?&gt;', $this->lead['lField4'], $this->msg['mBodyAlt']);
		$this->msg['mBody'] = str_replace('&lt;?Field5?&gt;', $this->lead['lField5'], $this->msg['mBody']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Field5?&gt;', $this->lead['lField5'], $this->msg['mBodyAlt']);

if(!class_exists('ar_gwa_user_edit') && file_exists(dirname(__FILE__).'/ar_gwa_user_edit.php')) include_once(dirname(__FILE__).'/ar_gwa_user_edit.php');
  if(class_exists('ar_gwa_user_edit') && $uca = new ar_gwa_user_edit($this->lead['lID'])){
    $this->msg['mBodyAlt'] = str_replace('<?Update?>',$uca->sendEditLink('text'), $this->msg['mBodyAlt']);
		$this->msg['mBodyAlt'] = str_replace('&lt;?Update?&gt;',$uca->sendEditLink('text'), $this->msg['mBodyAlt']);
    $this->msg['mBody'] = str_replace('<?Update?>',$uca->sendEditLink($this->msg['mType']), $this->msg['mBody']);
		$this->msg['mBody'] = str_replace('&lt;?Update?&gt;',$uca->sendEditLink($this->msg['mType']), $this->msg['mBody']);
  }

	if($this->msg['mType'] == 'html') { $lb = '<br /><br />'; } else { $lb ="\r\n\r\n"; }
  if(isset($ar_gwa_nospam_text) && $ar_gwa_nospam_text != '') {
    $this->msg['mBody'] .= $ar_gwa_nospam_text.$lb;
    $this->msg['mBodyAlt'] .= $ar_gwa_nospam_text."\r\n\r\n";
	}
  if(get_option('ar_gwa_addsub_'.$this->lead['lID'])!='') {
    $this->msg['mBody'] .= $unsubscribe.$lb;
    $this->msg['mBodyAlt'] .= $unsubscribe_text."\r\n\r\n";
  }
	if(get_option('ar_gwa_cb_'.$this->lead['lLID'])=='checked'){
    $this->msg['mBody'] .= $affiliate_text.$lb;
    $this->msg['mBodyAlt'] .= $affiliate_text_text."\r\n\r\n";
  }
 }
}

function argwa_show_page() {
global $wpdb,$my_argwa_metabox_plugin; $statusBox = $my_argwa_metabox_plugin->statusBox(); $ofs = ((int)get_option('gmt_offset'))*60*60; $tim = time(); $tls = $wpdb->get_var("SELECT COUNT(lID) as ccnt FROM ".$wpdb->prefix."ar_gwa_leads WHERE lCnf=1");?>
<style type="text/css">.argwahelp, .argwahelp2 {float: right;width:16px;height:16px;}</style>
<div id="argwa-metaboxes-general" class="wrap"><?php screen_icon('options-general');?><h2>ARGWA Free Autoresponder v<?php echo $my_argwa_metabox_plugin->gwa_version_tag;?></h2><?php if($msg = get_option('argwa_error')) $my_argwa_metabox_plugin->argwa_show_error($msg);?><form id="admin_post_php" action="admin-post.php" method="post"><?php wp_nonce_field('argwa-metaboxes-general'); ?>
<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?><?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?><input type="hidden" name="action" value="save_argwa_metaboxes_general" /><div id="poststuff" class="metabox-holder has-right-sidebar"><div id="side-info-column" class="inner-sidebar"><?php do_meta_boxes($my_argwa_metabox_plugin->pagehook, 'side', $data); ?></div>
<div id="post-body" class="has-sidebar">
<div id="post-body-content" class="has-sidebar-content">
<div class="postbox " id="argwa-metaboxes-contentbox-99">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><p style="float:right;margin:0;text-align:right;"><?php _e("Current Time",'argwa'); echo ': '.date_i18n('l jS \of F Y h:i:s A',$tim+$ofs);?></p><p style="margin:0"><?php echo __("Total Active Leads",'argwa').': '.$tls.'</p>';?></h3>
<div class="inside"><?php echo $statusBox;?>
</div>
</div>
<div class="postbox " id="argwa-metaboxes-contentbox-1"><div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span><?php _e("Newsletter",'argwa');?>: <?php echo $wpdb->get_var("SELECT xName FROM ".$wpdb->prefix."ar_gwa_lists");?></span></h3><div class="inside"><?php $my_argwa_metabox_plugin->do_list_detail_box(1);?>  <?php $my_argwa_metabox_plugin->do_list_messages_box(1);?>  <?php $my_argwa_metabox_plugin->do_list_leads_box(1);?>  <?php $my_argwa_metabox_plugin->do_list_form_box(1);?>  <?php $my_argwa_metabox_plugin->do_list_confirm_msg(1);?>  <?php $my_argwa_metabox_plugin->do_list_subscribe_box(1);?>  <?php $my_argwa_metabox_plugin->do_list_confirm_box(1);?>  <?php $my_argwa_metabox_plugin->do_list_unsubscribe_box(1);?>  <?php $my_argwa_metabox_plugin->do_list_error_box(1);?></div></div><div id="dialog" title="Add / Edit Message : ARGWA Pro"><p align="center"><img src="<?php echo ARGWA_PLUGIN_URL;?>/images/progress-indicator-alpha.gif"></p></div>
<p style="padding:10px;"><input type="checkbox" name="ar_gwa_delete_all" value="delete" <?php echo get_option('ar_gwa_delete');?>>&nbsp;<span style="color:red"><?php _e("GLOBAL DELETE",'argwa');?>:</span> <?php _e("Tick this checkbox to DELETE ALL DATA when plugin is next deactivated. Please be aware: <u>All plugin data will be lost.</u>",'argwa');?></p><p style="padding:10px;width:95%;text-align:left;"><input type="submit" value="<?php _e("Save Global Options",'argwa');?>" class="button" name="Submit"/></p></div></div><br class="clear"/></div></form></div>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
postboxes.add_postbox_toggles('<?php echo $my_argwa_metabox_plugin->pagehook; ?>');
$('#argwa-metaboxes-formbox-1').addClass('closed');
$('#argwa-metaboxes-confbox-1').addClass('closed');
$('#argwa_ur1_ifr').css('height','128px');
$('div#post-body-content').find('.ui-widget').delay(5000).hide('slow','swing');
$("#admin_post_php").attr("action", "<?php echo get_admin_url();?>admin.php?page=argwa_page");
//]]>
});</script>
<?php
}

function argwa_dashboard_alert() {
$email = get_option('ar_gwa_from');
if(strpos($email,"gmail") || strpos($email,"hotmail") || strpos($email,"yahoo")) {
?>
  <style type="text/css">
.alert {
    background-color: #FFCCCC;
    border: 1px solid #FF0000;
    color: #000000;
    padding: 6px;
    display:none;
  }
  </style>
  <script type="text/javascript">
  jQuery(document).ready(function(){ //when page has fully loaded
    jQuery('h2:contains("ARGWA")').parent().prev().after('<div id="argwa-plugin-alert" class="alert"><b>ARGWA Alert:</b> Your From: address should not be your GMAIL / HOTMAIL / YAHOO email. It SHOULD BE on your domain like <b>admin@<?php echo $_SERVER['SERVER_NAME'];?></b> for best results.</div>');
    setTimeout("jQuery('#argwa-plugin-alert').fadeIn('slow');clearTimeout();",1000);
  });
  </script>
  <?
 }
 ?>
<style type="text/css">
.ui-widget-overlay {
    background-color: #000000;
    opacity: 0.6;
<?php global $wp_version;
if ( $wp_version < 3.9 ) {
?>    z-index: 100 !important;<?php
} else {
?>    z-index: 0 !important;<?php
}
?>}
</style>
 <?php
}
class ARGWA_Widget extends WP_Widget {

public function __construct() {
		parent::__construct(
	 		'WidgetARGWA', // Base ID
			'ARGWA Autoresponder', // Name
			array( 'description' => __( 'A Widget for ARGWA Subscription Form', 'argwa' ), ) // Args
		);
	}

	function widget($args, $instance) {
		extract($args);
		 $style = $instance['style'] = 1;
		echo $before_widget;
		if(!empty($instance['style'])) {
			echo $before_title."Subscribe".$after_title;
			global $my_argwa_metabox_plugin;// = new argwa_metabox_plugin();
			echo $my_argwa_metabox_plugin->argwa_form_display($instance['style'],1);
		}
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['style'] = $new_instance['style'];
		return $new_instance;
	}

  function form( $instance ) {
  global $wpdb;
  ?><p><label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e('Newsletter to Display:', ''); ?></label><select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?> class="widefat" style="width:100%;"><?php echo '<option selected="selected" value="1">'.$wpdb->get_var("SELECT xName FROM ".$wpdb->prefix."ar_gwa_lists WHERE xID=1").'</option>';?></select></p><?php
 }
}
?>