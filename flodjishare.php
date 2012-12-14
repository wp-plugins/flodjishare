<?php
/*
Plugin Name: flodjiShare
Plugin URI: http://flodji.de
Description: Mit flodjiShare wird Webseitenbetreibern eine einfache L&ouml;sung angeboten die Social Sharing und Bookmark Buttons der gro&szlig;en Netzwerke in die eigene Seite einzubinden.
Version: 1.3
Author: flodji
Author URI: http://flodji.de
License: GPL2
*/

global $wpdb;
add_action('wp_head', 'flodjiShareOpenGraph');
add_filter('the_content', 'flodjishare');
add_filter('language_attributes', 'flodjishare_schema');
add_action('admin_menu', 'flodjishare_menu');

function flodjishare_menu(){
	add_options_page('flodjishare Options', 'flodjishare', 'manage_options', 'flodjiShare', 'flodjishare_options');
}

function flodjiShareNormDesc( $title ){
$slug = $title;
$bad = array( '"',"'",'�','�',"\n","\r", "&rarr;");
$good = array( '','','','','','','');
$slug = str_replace( $bad, $good, $slug );
$slug = trim($slug);
return $slug;
}

if(is_single()){
$excerpt = flodjiShareNormDesc(urlencode(strip_tags(get_the_excerpt())));
} else {
$excerpt = flodjiShareShortText($content,140);
}

function flodjiShareShortText($string,$lenght) {
    if(strlen($string) > $lenght) {
        $string = substr($string,0,$lenght)."...";
        $string_ende = strrchr($string, " ");
        $string = str_replace($string_ende," ...", $string);
    }
    return $string;
}

function flodjishare_schema($attr) {
	$attr .= "\n xmlns:og=\"http://opengraphprotocol.org/schema/\"";
	$attr .= "\n xmlns:fb=\"http://www.facebook.com/2008/fbml\"";
	$attr .= "\n itemscope itemtype=\"http://schema.org/Article\"";
	return $attr;
}
function flodjishare($content) {
global $wpdb;		

	$option_string = get_option('flodjishare');
	if ($option_string=='ueber' or $option_string=='unter') {
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true);
		$option['position'] = get_option('flodjishare');
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true);
	} else {
		$option = json_decode($option_string, true);
	}
	
	if(is_single()) {
		if (!$option['show_in']['posts']) {
			return $content;
		}
	} else {
		if ((!$option['show_in']['pages'])&&(is_Page())) {
			return $content;
	}
	}
	if(is_home()) {
		if (!$option['show_in']['home']) {
			return $content;
	}
	}
		$outputa = '<div style="width:100%; padding-top:2px;">';
		$outputa .= '<h3>Diesen Artikel teilen...</h3>';
		
		if ($option['active_buttons']['facebook']==true) {
		$outputa .= '<script type="text/javascript">
		function popup (url) {
		fenster = window.open(url, "Popupfenster", "width=600,height=400,resizable=yes");
		fenster.focus();
		return false;
		}
		</script><div style="float:left; padding-left:3px; margin:1px;"><a href="http://www.facebook.com/sharer.php?u='.urlencode(get_permalink()).'&amp;t='.urlencode(get_the_title()).'" onclick="return popup(this.href);" rel="nofollow"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/fb_follow.png" align="left" /></a></div>'; 
		}

		if ($option['active_buttons']['twitter']==true) {

		$tw_link = 'https://twitter.com/share?url='.urlencode(get_permalink()).'&via='.stripslashes($option['twitter_text']).'&text='.urlencode(get_the_title());
		$outputa .= '<script type="text/javascript">
		function popup (url) {
		fenster = window.open(url, "Popupfenster", "width=600,height=400,resizable=yes");
		fenster.focus();
		return false;
		}
		</script><div style="float:left; padding-left:3px; margin:1px;"> 
		<a href="'.$tw_link.'" onclick="return popup(this.href);" rel="nofollow"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/tw_follow.png" align="left" /></a></div>';
		}

		if ($option['active_buttons']['digg']==true) {
		$digg_link = 'http://digg.com/submit?url='.get_permalink().'&amp;title='.get_the_title();
		$outputa .= '<div style="float:left; padding-left:3px; margin:1px;"> 
		<a target="_blank" href="'.$digg_link.'" rel="nofollow"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/di_follow.png" align="left" /></a></div>';
		}

		if ($option['active_buttons']['delicious']==true) {
		$del_link =	'http://www.delicious.com/post?url='.urlencode(get_permalink()).'&notes='.$excerpt.'&title='.urlencode(get_the_title());	
		$outputa .= '<div style="float:left; padding-left:3px; margin:1px;"> 
					<a href="'.$del_link.'" target="_blank" rel="nofollow"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/del_follow.png" alt="Delicious" /></a></div>';
		}

		if ($option['active_buttons']['vz']==true) {
		$vz_link = 'http://platform-redirect.vz-modules.net/r/Link/Share/?url='.urlencode(get_permalink()).'&title='.urlencode(get_the_title()).'&description='.$excerpt.'&thumbnail=' . urlencode(flodjiShareFirstImage());
				$outputa .= '<div style="float:left; padding-left:3px; margin:1px;">
				<a target="_blank" rel="nofollow" href="'.$vz_link.'"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/vz_follow.png" title="studiVZ meinVZ sch&uuml;lerVZ" border="0" alt="VZ Netzwerke" /></a></div>'; 
		}
		
		if ($option['active_buttons']['gplus']==true) {
		$outputa .= '<script type="text/javascript">
		function popup (url) {
		fenster = window.open(url, "Popupfenster", "width=800,height=400,resizable=yes");
		fenster.focus();
		return false;
		}
		</script><div style="float:left; padding-left:3px; margin:1px;"><a href="https://plus.google.com/share?url=' . urlencode(get_permalink()) . '" onclick="return popup(this.href);"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/gplus.png" border="0" alt="Ihren Google Plus-Kontakten zeigen" align="left"  /></a></div>';
		}
		
		if ($option['active_buttons']['xing']==true) {
		$xing_link = 'http://www.xing.com/app/user?op=share;url='.get_permalink();
		$outputa .= '<div style="float:left; padding-left:3px; margin:1px;">
				<a href="'.$xing_link.'" target="_blank" title="Ihren XING-Kontakten zeigen" rel="nofollow"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/xi_follow.png" border="0" alt="Ihren XING-Kontakten zeigen" /></a></div>';
		}

		$outputa .= '</div><br /><br /><br />';

		if ($option['position']=='unter') {
		return $content . $outputa;
		} else {
		return $outputa.$content;
		}
}

function flodjiShareOpenGraph() {
	if(is_singular()){
		if (have_posts()) : while (have_posts()) : the_post(); 
			$parameter[]=get_the_title($post->post_title);
			$parameter[]=get_permalink();
			$parameter[]=flodjiShareFirstImage();
			$parameter[]=get_option('blogname');
			$parameter[]=get_the_excerpt();
		endwhile; endif; 
	}else{
		$parameter[]=get_option('blogname');
		$parameter[]=get_option('siteurl');
		$parameter[]=flodjiShareFirstImage();
		$parameter[]=get_option('blogname');
		$parameter[]=get_option('blogdescription');
	}
	echo flodjiShareMetas($parameter);
}

function flodjiShareMetas($parameter){
$option_string = get_option('flodjishare');
$option = array();
$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true);
$option = json_decode($option_string, true);
if($option['active_buttons']['opengraph']==true){
	$txt.="\n";
	$txt.="<meta property='og:title' content='".$parameter[0]."'/>";
	$txt.="\n";
	$txt.="<meta property='og:url' content='".$parameter[1]."'/>";
	$txt.="\n";
	if($parameter[2] != ''){
	$txt.="<meta property='og:image' content='".$parameter[2]."'/>";
	$txt.="\n";
	}
	$txt.="<meta property='og:site_name' content='".$parameter[3]."'/>";
	$txt.="\n";
	$txt.="<meta property='og:description' content='".strip_tags(flodjiShareNormDesc($parameter[4]))."'/>";
	$txt.="\n";
	$txt.="<meta property='og:type' content='article'/>";
	$txt.="\n";
	if($option['fb_app_id'] != ''){
	$txt.="<meta property='fb:app_id' content='".stripslashes($option['fb_app_id'])."'/>";
	$txt.="\n";
	}
	if($option['fb_admin'] != ''){
	$txt.="<meta property='fb:admins' content='".stripslashes($option['fb_admin'])."'/>";
	$txt.="\n";
	}
	}
	if($option['active_buttons']['richsnippets']==true){
	$txt.="<meta itemprop='name' content='".$parameter[0]."'>";
	$txt.="\n";
	$txt.="<meta itemprop='description' content='".strip_tags(flodjiShareNormDesc($parameter[4]))."'>";
	$txt.="\n";
	if($parameter[2] != ''){
	$txt.="<meta itemprop='image' content='".$parameter[2]."'>";
	$txt.="\n";
	}
	$txt.="<meta itemprop='url' content='".$parameter[1]."'>";
	$txt.="\n";
	if($parameter[4] != ''){
	$txt.="<meta name='description' content='".strip_tags(flodjiShareNormDesc($parameter[4]))."'/>";
	$txt.="\n";
	}
	}
	if($option['active_buttons']['twittercards']==true){
	$txt.='<meta name="twitter:card" content="summary">';
	$txt.="\n";
	if($option['twitsite'] != ''){
	$txt.='<meta name="twitter:site" content="@'.stripslashes($option['twitsite']).'">';
	$txt.="\n";
	}
	if($option['twituser'] != ''){
	$txt.='<meta name="twitter:creator" content="@'.stripslashes($option['twitter_text']).'">';
	$txt.="\n";
	}
	$txt.='<meta name="twitter:url" content="'.$parameter[1].'">';
	$txt.="\n";
	$txt.='<meta name="twitter:title" content="'.$parameter[0].'">';
	$txt.="\n";
	if($parameter[4] != ''){
	$txt.='<meta name="twitter:description" content="'.strip_tags(flodjiShareNormDesc($parameter[4])).'">';
	$txt.="\n";
	}
	if($parameter[2] != ''){
	$txt.='<meta name="twitter:image" content="'.$parameter[2].'">';
	$txt.="\n";
	}
	}
	return $txt;
}

function flodjiShareFirstImage()
{
$option_string = get_option('flodjishare');
$option = array();
$option = json_decode($option_string, true);
$Html = get_the_content();
$extrae = '/<img .*src=["\']([^ ^"^\']*)["\']/';
preg_match_all( $extrae  , $Html , $matches );
$image = $matches[1][0];
if($image) {
return $image;
} else {
if($option['altimg'] != ''){
return stripslashes($option['altimg']);
} else {
return '';
}
}
}

function followMeFlodjiShare(){
$fb = '<a target="_blank" href="http://www.facebook.com/flodjishare"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/fb_follow.png" /></a>';
$tw = '<a target="_blank" href="http://www.twitter.com/flodji"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/tw_follow.png" /></a>';
$gp = '<a target="_blank" href="https://plus.google.com/104542622643572083517/"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/gplus.png" /></a>';
return '<h3>Folge mir:</h3>' . $fb . $tw . $gp;
}

function spendPayPalFlodjiShare(){
$paypalbutton = '<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K9U25CKQNA5GL"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/donate.png" height="48"/></a>';
return '<h3>Spenden:</h3>' . $paypalbutton;
}

function flodjishare_options () {
	$option_name = 'flodjishare';
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if( isset($_POST['flodjishare_position'])) {
		$option = array();
		$option['active_buttons'] = array('facebook'=>false, 'twitter'=>false, 'digg'=>false, 'delicious'=>false, 'vz'=>false, 'xing'=>false, 'gplus'=>false, 'opengraph'=>false, 'richsnippets'=>false, 'twittercards'=>false);
		if ($_POST['flodjishare_active_facebook']=='on') { $option['active_buttons']['facebook'] = true; }
		if ($_POST['flodjishare_active_twitter']=='on') { $option['active_buttons']['twitter'] = true; }
		if ($_POST['flodjishare_active_digg']=='on') { $option['active_buttons']['digg'] = true; }
		if ($_POST['flodjishare_active_delicious']=='on') { $option['active_buttons']['delicious'] = true; }		
		if ($_POST['flodjishare_active_vz']=='on') { $option['active_buttons']['vz'] = true; }
		if ($_POST['flodjishare_active_xing']=='on') { $option['active_buttons']['xing'] = true; }
		if ($_POST['flodjishare_active_gplus']=='on') { $option['active_buttons']['gplus'] = true; }
		if ($_POST['flodjishare_active_opengraph']=='on') { $option['active_buttons']['opengraph'] = true; }
		if ($_POST['flodjishare_active_richsnippets']=='on') { $option['active_buttons']['richsnippets'] = true; }
		if ($_POST['flodjishare_active_twittercards']=='on') { $option['active_buttons']['twittercards'] = true; }
		$option['position'] = esc_html($_POST['flodjishare_position']);
		$option['twitter_text'] = esc_html($_POST['flodjishare_twitter_text']);
		$option['fb_app_id'] = esc_html($_POST['flodjishare_fb_app_id']);
		$option['fb_admin'] = esc_html($_POST['flodjishare_fb_admin']);
		$option['show_in'] = array('posts'=>false, 'pages'=>false, 'home'=>false);
		$option['altimg'] = esc_html($_POST['altimg']);
		$option['twitsite'] = esc_html($_POST['twitsite']);
		if ($_POST['flodjishare_show_posts']=='on') { $option['show_in']['posts'] = true; }
		if ($_POST['flodjishare_show_pages']=='on') { $option['show_in']['pages'] = true; }
		if ($_POST['flodjishare_show_home']=='on') { $option['show_in']['home'] = true; }
		update_option($option_name, json_encode($option));
		$outputa .= '<div class="updated"><p><strong>'.__('Einstellungen gespeichert.', 'menu' ).'</strong></p></div>';
	}
	$option = array();
	$option_string = get_option($option_name);
	if ($option_string===false) {
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true);
		$option['position'] = 'unter';
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['altimg'] = array('altimg'=>true);
		$option['twitsite'] = array('twitsite'=>true);
		add_option($option_name, 'unter');
		$option_string = get_option($option_name);
	}
	if ($option_string=='ueber' or $option_string=='unter') {
		$flodjishare_options = explode('|||',$option_string);
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true);
		$option['position'] = $flodjishare_options[0];
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['altimg'] = array('altimg'=>true);
		$option['twitsite'] = array('twitsite'=>true);
	} else {
		$option = json_decode($option_string, true);
	}
	$sel_above = ($option['position']=='ueber') ? 'selected="selected"' : '';
	$sel_below = ($option['position']=='unter') ? 'selected="selected"' : '';
	$active_facebook 	= ($option['active_buttons']['facebook']==true) ? 'checked="checked"' : '';
	$active_twitter  	= ($option['active_buttons']['twitter'] ==true) ? 'checked="checked"' : '';
	$active_digg		= ($option['active_buttons']['digg']==true) ? 'checked="checked"' : '';
	$active_delicious	= ($option['active_buttons']['delicious']==true) ? 'checked="checked"' : '';
	$active_vz			= ($option['active_buttons']['vz']==true) ? 'checked="checked"' : '';
	$active_xing		= ($option['active_buttons']['xing']==true) ? 'checked="checked"' : '';
	$active_gplus		= ($option['active_buttons']['gplus']==true) ? 'checked="checked"' : '';
	$active_opengraph	= ($option['active_buttons']['opengraph']==true) ? 'checked="checked"' : '';
	$active_richsnippets= ($option['active_buttons']['richsnippets']==true) ? 'checked="checked"' : '';
	$active_twittercards= ($option['active_buttons']['twittercards']==true) ? 'checked="checked"' : '';
	$show_in_posts 		= ($option['show_in']['posts']==true) ? 'checked="checked"' : '';
	$show_in_pages 		= ($option['show_in']['pages'] ==true) ? 'checked="checked"' : '';
	$show_in_home 		= ($option['show_in']['home'] ==true) ? 'checked="checked"' : '';
	$twitter_text		= ($option['twitter_text']=='') ? 'selected="selected"' : '';
	$fb_app_id			= ($option['fb_app_id']=='') ? 'selected="selected"' : '';
	$fb_admin			= ($option['fb_admin']=='') ? 'selected="selected"' : '';
	$altimg				= ($option['altimg']=='') ? 'selected="selected"' : '';
	$twitsite			= ($option['twitsite']=='') ? 'selected="selected"' : '';
	$outputa .= '
	<div class="wrap">
		<h2>'.__( 'flodjiShare', 'menu' ).'</h2>
	<a target="_blank" href="http://flodji.de"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/fs_header_logo.jpg" /></a>
	<table><tr><td style="width:200px;">'.followMeFlodjiShare().'</td><td>'.spendPayPalFlodjiShare().'</td></tr></table><br />
		<form name="form1" method="post" action="">
		<table>
		<tr><td valign="top">'.__("flodjiShare auf diesen Seiten zeigen", 'menu' ).':</td>
		<td>'
		.' <input type="checkbox" name="flodjishare_show_posts" '.$show_in_posts.'> '
		. __("Einzelne Beitr&auml;ge", 'menu' ).' &nbsp;&nbsp;'
		.' <input type="checkbox" name="flodjishare_show_pages" '.$show_in_pages.'> '
		. __("Seiten", 'menu' ).' &nbsp;&nbsp;'
		.' <input type="checkbox" name="flodjishare_show_home" '.$show_in_home.'> '
		. __("Startseite", 'menu' ).' &nbsp;&nbsp;'
		.'<br /><br /></td></tr>
		<tr><td valign="top">'.__("flodjiShare Buttons", 'menu' ).':</td>
		<td>'
		.' <input type="checkbox" name="flodjishare_active_facebook" '.$active_facebook.'> '
		. __("Facebook Share", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_twitter" '.$active_twitter.'> '
		. __("Twitter", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_digg" '.$active_digg.'> '
		. __("Digg", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_delicious" '.$active_delicious.'> '
		. __("Delicious", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_vz" '.$active_vz.'> '
		. __("VZ-Netzwerke", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_xing" '.$active_xing.'> '
		. __("Xing", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_gplus" '.$active_gplus.'> '
		. __("Google Plus", 'menu' ).' &nbsp;&nbsp;<br />'		
		.'<br /><br /></td></tr>
		<tr><td valign="top">'.__("Position", 'menu' ).':</td>
		<td><select name="flodjishare_position">
			<option value="ueber" '.$sel_above.' > '.__('&Uuml;ber dem Beitrag', 'menu' ).'</option>
			<option value="unter" '.$sel_below.' > '.__('Unter dem Beitrag', 'menu' ).'</option>
			</select><br /> 
		<br /></td></tr>
		<tr><td valign="top">'.__("Twitter Name", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_twitter_text" value="'.stripslashes($option['twitter_text']).'" size="100"><br />
		<span class="description">'.__("Trage hier Deinen Twitter Usernamen ein. Dieser wird dann in den Twitter Cards (wenn aktiviert)<br />und am Ende der Tweets erscheinen, z.B. (via @Dein Twitter Name).<br />", 'menu' ).'</span>
		</td></tr>

		<tr><td valign="top">'.__("Twitter Seite", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="twitsite" value="'.stripslashes($option['twitsite']).'" size="100"><br />
		<span class="description">'.__("Trage hier den Twitter Usernamen Deiner Worspress Seite ein. Falls nicht vorhanden, trage einfach Deinen Twitter Usernamen ein.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">'.__("Ersatzbild", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="altimg" value="'.stripslashes($option['altimg']).'" size="100"><br />
		<span class="description">'.__("Trage hier den Link zu einem Ersatzbild ein. Dieses wird beim Teilen verwendet, wenn im Artikel kein Bild vorhanden ist.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">'.__("Facebook AppId", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_fb_app_id" value="'.stripslashes($option['fb_app_id']).'" size="100"><br />
		<span class="description">'.__("Trage hier Deine Facebook AppId ein.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">'.__("Facebook Admin", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_fb_admin" value="'.stripslashes($option['fb_admin']).'" size="100"><br />
		<span class="description">'.__("Trage hier Deinen Facebook Usernamen ein.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><tr><td valign="top">'.__("flodjiShare Extras", 'menu' ).':</td>
		<td><input type="checkbox" name="flodjishare_active_opengraph" '.$active_opengraph.'> '
		. __("Opengraph Support", 'menu' ).' &nbsp;&nbsp;<br />
		
		<input type="checkbox" name="flodjishare_active_richsnippets" '.$active_richsnippets.'> '
		. __("Rich Snippets Support", 'menu' ).' &nbsp;&nbsp;<br />

		<input type="checkbox" name="flodjishare_active_twittercards" '.$active_twittercards.'> '
		. __("Twitter Card Support", 'menu' ).' &nbsp;&nbsp;<br /></td></tr>		
		</table>
		<hr />
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="'.esc_attr('Speichern').'" />
		</p>
		</form>
		Bei Problemen oder Fragen kannst Du gern das <a target="_blank" href="http://flodji.de/forum/">Support Forum</a> besuchen.</p>
	</div>
	';
	echo $outputa;
}
?>