<?php
/*
Plugin Name: flodjiShare
Plugin URI: http://flodji.de
Description: Mit flodjiShare wird Webseitenbetreibern eine einfache L&ouml;sung angeboten die Social Sharing und Bookmark Buttons der gro&szlig;en Netzwerke in die eigene Seite einzubinden.
Version: 2.1
Author: flodji
Author URI: http://flodji.de
License: GPL2
*/
global $wpdb;
add_action('wp_head', 'flodjiShareOpenGraph');
add_filter('language_attributes', 'flodjishare_schema');
$option_string = get_option('flodjishare');
if ($option_string=='ueber' or $option_string=='unter' or $option_string=='shortcode') {
		$option = array();
		$option['position'] = get_option('flodjishare');
} else {
		$option = json_decode($option_string, true);
}
if ($option['position']=='shortcode') {
add_shortcode( 'flodjishare', 'flodjishare' );
} else {
add_filter('the_content', 'flodjishare');
}

function flodjiShareStyle() {
	wp_enqueue_style( 'flodjishare', plugins_url( 'flodjishare/flodjishare.css' ) );
}
add_action( 'wp_enqueue_scripts', 'flodjiShareStyle' );

add_action( 'admin_menu', 'flodjiShareMenu' );
function flodjiShareMenu(){
    add_menu_page( 'flodjiShare Einstellungen', 'flodjiShare', 'manage_options', 'flodjishare_einstellungen', 'flodjishare_options', plugins_url( 'flodjishare/buttons/fs_ico.png' ), 102 );
	add_submenu_page( 'flodjishare_einstellungen', 'flodjiShare Klick Counter', 'flodjiShare Klick Counter', 'manage_options', 'klick-counter', 'flodjiShareKlickCounter' ); 
}

function flodjiShareKlickCounter() {
mysql_query("CREATE TABLE IF NOT EXISTS flodjiShareLinks (`id` int(255) NOT NULL auto_increment,
									`klicks` varchar(100) NOT NULL,
									`title` varchar(255) NOT NULL,
									`network` varchar(200) NOT NULL,
									PRIMARY KEY  (`id`))
									ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 AUTO_INCREMENT=73");
$eintraege_pro_seite = 15;	
$sort		= $_GET['sort'];
if($sort == ''){
$sort		= 'title';
}
$seite		= $_GET['seite'];
if($seite == ''){
$seite 		= 1; 
}
$start 		= $seite * $eintraege_pro_seite - $eintraege_pro_seite; 
$ergebnis 	= mysql_query( "SELECT title,network,klicks FROM flodjiShareLinks ORDER BY $sort LIMIT $start, $eintraege_pro_seite" );
$anz_reihe 	= mysql_num_rows( $ergebnis );
$anz_felde 	= mysql_num_fields( $ergebnis );
$ausgabe = '<div style="width:400px;float:left;"><h2>flodjiShare Klick Counter</h2><table><tr><td style="width:200px;">'.followMeFlodjiShare().'</td><td>'.spendPayPalFlodjiShare().'</td></tr></table>';
$ausgabe .= "<br><br>";
$ausgabe .= '<table style="border:1px solid #000;" bgcolor="white"><tr>';
for ( $x=0; $x<$anz_felde; $x++) $ausgabe .= '<th style="border:1px solid #000;"><b><a href="admin.php?page=klick-counter&sort='.mysql_field_name($ergebnis, $x).'">' . mysql_field_name($ergebnis, $x) . '</a></b></th>';
$ausgabe .= "</tr>";
while ( $datensatz = mysql_fetch_row( $ergebnis ) )
    {
    $ausgabe .= "<tr>\n";
    foreach ( $datensatz as $feld ) {

        $ausgabe .= '<td style="border:1px solid #000;">'.$feld.'</td>';  }
    $ausgabe .= "</tr>\n";
    }
$ausgabe .= "</table>\n";
$result 		= mysql_query("SELECT title,network,klicks FROM flodjiShareLinks ORDER BY title");
$menge 			= mysql_num_rows($result);
$wieviel_seiten = $menge / $eintraege_pro_seite; 
$ausgabe .= '<br /><div align="center">';
$ausgabe .= '<strong>Seite:</strong>';
for($a=0; $a < $wieviel_seiten; $a++){
   $b = $a + 1;
   if($seite == $b){
      $ausgabe .= ' <strong>'.$b.'</strong>';
   } else {
      $ausgabe .= ' <a href="admin.php?page=klick-counter&seite='.$b.'&sort='.$sort.'">'.$b.'</a>';
      }
}
$ausgabe .= '</div><br /><p><strong>Hinweis:</strong><br />Der Klickz&auml;hler zeigt die Anzahl der Klicks auf die Share Buttons an. Diese Zahl muss nicht zwingend mit der tats&auml;chlichen Anzahl von Shares &uuml;bereinstimmen.</p></div>';
$ausgabe .= '<div style="margin-left:50px;border-left:thin solid #ccc;border-right:thin solid #ccc;border-bottom:thin solid #ccc;padding:3px;width:200px;float:left;box-shadow: 0 1px 1px #999;">
<div>
<a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=flodji.de_Logo&utm_campaign=flodjiShareWP"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/flodjidelogo02.png" width="180"/></a><h2>flodji.de Feed</h2>';
$rss = fetch_feed( "http://flodji.de/feed/" );
if(!is_wp_error($rss)){
$maxitems = $rss->get_item_quantity( 5 ); 
$rss_items = $rss->get_items( 0, $maxitems );
}
$ausgabe .= '<ul>';
if($maxitems == 0){
$ausgabe .= '<li>Keine Eintr&auml;ge</li>';
} else {
foreach ($rss_items as $item){
$ausgabe .= '<li>
    <a href="'.esc_url($item->get_permalink()).'?utm_source=flodjiShareWP&utm_medium=FeedLink&utm_campaign=flodjiShareWP" title="'.esc_html( $item->get_title() ).'" target="_blank">';
$ausgabe .= esc_html( $item->get_title() );
$ausgabe .= '</a></li>';
}
}
$ausgabe .= '</ul>
</div>
<div>
<h2>Weitere Links</h2>
<ul>
<li><a target="_blank" href="http://flodji.de/downloads/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Weitere Plugins / Themes</a></li>
<li><a target="_blank" href="http://flodji.de/category/gewinnspiele/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Gewinnspiele</a></li>
<li><a target="_blank" href="http://flodji.de/fragen-und-antworten/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">FAQs</a></li>
<li><a target="_blank" href="http://flodji.de/linkpartner/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Linkpartner werden</a></li>
<li><a target="_blank" href="http://flodji.de/kontakt/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Kontakt</a></li>
<li><a target="_blank" href="http://flodji.de/forum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Forum</a></li>
<li><a target="_blank" href="http://flodji.de/werben-auf-flodji-de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Werben auf flodji.de</a></li>
<li><a target="_blank" href="http://flodji.de/gastartikel/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Gastartikel schreiben</a></li>
<li><a target="_blank" href="http://flodji.de/die-flodji-de-android-app-beta/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">flodji.de Android App</a></li>
<li><a target="_blank" href="http://flodji.de/impressum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Impressum</a></li>
</ul>
</div>
</div>';
echo $ausgabe;
}

function flodjiShareNormDesc( $title ){
$slug = $title;
$bad = array( '"',"'",'“','”',"\n","\r", "&rarr;");
$good = array( '','','','',' ','','');
$slug = str_replace( $bad, $good, $slug );
$slug = trim($slug);
return $slug;
}

function descExcerpt(){
global $post;
if($post->post_excerpt){
$excerpt = flodjiShareNormDesc(strip_tags(get_the_excerpt()));
} else {
$excerpt = flodjiShareNormDesc(flodjiShareShortText(strip_tags(get_the_content()),140));
}
return $excerpt;
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
$option_string = get_option('flodjishare');
$option = array();
$option = json_decode($option_string, true);
if($option['active_buttons']['opengraph']==true){
	$attr .= "\n xmlns:og=\"http://opengraphprotocol.org/schema/\"";
	$attr .= "\n xmlns:fb=\"http://www.facebook.com/2008/fbml\"";
	return $attr;
}
}

function short_number($n) {
$n = (0+str_replace(",","",$n));
if(!is_numeric($n)) return false;
if($n>1000000) return round(($n/1000000),1).' M';
else if($n>1000) return round(($n/1000),1).' K';
else if($n>100) return round(($n/100),1).' H';
return number_format($n);
}

function flodjishare($content) {
global $wpdb;		

	$option_string = get_option('flodjishare');
	if ($option_string=='ueber' or $option_string=='unter' or $option_string=='shortcode') {
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'metro'=>true, 'counter'=>true, 'supportlink'=>true, 'privacy'=>true);
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
		if ((!$option['show_in']['pages'])&&(is_page())) {
			return $content;
	}
	}
	if(is_home() or is_category() or is_archive() or is_search()) {
		if (!$option['show_in']['home']) {
			return $content;
	}
	}
		$outputa = '<div class="fsmain">';
		$outputa .= '<h3>'.stripslashes($option['intro_text']).'</h3>';
		$outputa .= '<script type="text/javascript">
		function popup (url) {
		fenster = window.open(url, "Popupfenster", "width=530,height=400,resizable=yes");
		fenster.focus();
		return false;
		}
		</script>';
		if ($option['active_buttons']['facebook']==true) {
		if ($option['metro']==true){
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Facebook';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsfb" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode('http://www.facebook.com/sharer.php?u='.urlencode(get_permalink()).'&amp;t='.urlencode(get_the_title())).'" onclick="return popup(this.href);" rel="nofollow"><strong>Facebook</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsfb" href="http://www.facebook.com/sharer.php?u='.urlencode(get_permalink()).'&amp;t='.urlencode(get_the_title()).'" onclick="return popup(this.href);" rel="nofollow"><strong>Facebook</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="http://www.facebook.com/sharer.php?u='.urlencode(get_permalink()).'&amp;t='.urlencode(get_the_title()).'" onclick="return popup(this.href);" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/facebook.png" border="0" /></a></div>'; 
		}
		}

		if ($option['active_buttons']['twitter']==true) {
		$tw_link = 'https://twitter.com/share?url='.urlencode(get_permalink()).'&via='.stripslashes($option['twitter_text']).'&text='.urlencode(get_the_title());
		if ($option['metro']==true){
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Twitter';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fstw" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($tw_link).'" onclick="return popup(this.href);" rel="nofollow"><strong>Twitter</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fstw" href="'.$tw_link.'" onclick="return popup(this.href);" rel="nofollow"><strong>Twitter</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"> 
		<a href="'.$tw_link.'" onclick="return popup(this.href);" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/twitter.png" border="0" /></a></div>';
		}
		}

		if ($option['active_buttons']['digg']==true) {
		$digg_link = 'http://digg.com/submit?url='.get_permalink().'&amp;title='.get_the_title();
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Digg';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsdigg" target="_blank" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($digg_link).'" rel="nofollow"><strong>Digg</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsdigg" target="_blank" href="'.$digg_link.'" rel="nofollow"><strong>Digg</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"> 
		<a target="_blank" href="'.$digg_link.'" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/digg.png" border="0" /></a></div>';
		}
		}

		if ($option['active_buttons']['delicious']==true) {
		$del_link =	'http://www.delicious.com/post?url='.urlencode(get_permalink()).'&notes='.urlencode(descExcerpt()).'&title='.urlencode(get_the_title());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Delicious';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsdel" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($del_link).'" target="_blank"><strong>Delicious</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsdel" href="'.$del_link.'" target="_blank"><strong>Delicious</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"> 
					<a href="'.$del_link.'" target="_blank" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/delicious.png" alt="Delicious" border="0" /></a></div>';
		}
		}

		if ($option['active_buttons']['vz']==true) {
		$vz_link = 'http://platform-redirect.vz-modules.net/r/Link/Share/?url='.urlencode(get_permalink()).'&title='.urlencode(get_the_title()).'&description='.urlencode(descExcerpt()).'&thumbnail=' . urlencode(flodjiShareFirstImage());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='VZ';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsvz" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($vz_link).'" title=" ' . $title . ' Deinen Freunden im VZ zeigen" target="_blank" rel="nofllow"><strong>VZ</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsvz" href="'.$vz_link.'" title=" ' . $title . ' Deinen Freunden im VZ zeigen" target="_blank" rel="nofllow"><strong>VZ</strong></a></div>';
		}
		} else {
		$outputa .= '<div style="float:left; padding-left:3px;margin-right:-2px;"><a target="_blank" rel="nofollow" href="'.$vz_link.'"><img style="max-width: 100%;margin:-1px;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/vz_follow.png" title="studiVZ meinVZ sch&uuml;lerVZ" border="0" alt="VZ Netzwerke" width="38" /></a></div>'; 
		}
		}
		
		if ($option['active_buttons']['gplus']==true) {
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Google Plus';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsgp" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode('https://plus.google.com/share?url=' . urlencode(get_permalink())) . '" onclick="return popup(this.href);" rel="nofollow"><strong>Google Plus</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsgp" href="https://plus.google.com/share?url=' . urlencode(get_permalink()) . '" onclick="return popup(this.href);" rel="nofollow"><strong>Google Plus</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="https://plus.google.com/share?url=' . urlencode(get_permalink()) . '" onclick="return popup(this.href);"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/googleplus.png" border="0" alt="Ihren Google Plus-Kontakten zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['xing']==true) {
		$xing_link = 'http://www.xing.com/app/user?op=share;url='.get_permalink();
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Xing';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsxi" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($xing_link).'" target="_blank" rel="nofollow"><strong>Xing</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsxi" href="'.$xing_link.'" target="_blank" rel="nofollow"><strong>Xing</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$xing_link.'" target="_blank" title="Ihren XING-Kontakten zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/xing.png" border="0" alt="Ihren XING-Kontakten zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['linkedin']==true) {
		$linkedin_link = 'http://www.linkedin.com/shareArticle?mini=true&url='.urlencode(get_permalink()).'&title='.urlencode(get_the_title()).'&ro=false&summary='.urlencode(descExcerpt());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='LikedIn';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsli" target="_blank" rel="nofollow" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($linkedin_link).'"><strong>LinkedIn</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsli" target="_blank" rel="nofollow" href="'.$linkedin_link.'"><strong>LinkedIn</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$linkedin_link.'" target="_blank" title="Ihren LinkedIn Kontakten zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/linkedin.png" border="0" alt="Ihren LinkedIn Kontakten zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['pinterest']==true) {
		$pin_link = "http://pinterest.com/pin/create/button/?url=" . urlencode(get_permalink()) . "&media=" . urlencode(flodjiShareFirstImage()) . "&description=" . urlencode(descExcerpt());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Pinterest';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fspi" rel="nofollow" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($pin_link).'" onclick="return popup(this.href);"><strong>Pinterest</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fspi" rel="nofollow" href="'.$pin_link.'" onclick="return popup(this.href);"><strong>Pinterest</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$pin_link.'" target="_blank" title="Auf Pinterest zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/pinterest.png" border="0" alt="Auf Pinterest zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['stumbleupon']==true) {
		$stumble_link = "http://www.stumbleupon.com/submit?url=" . urlencode(get_permalink()) . "&title=" . urlencode(get_the_title());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='StumbleUpon';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fssu" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($stumble_link).'" target="_blank" title="Auf Stumbleupon zeigen" rel="nofollow"><strong>StumbleUpon</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fssu" href="'.$stumble_link.'" target="_blank" title="Auf Stumbleupon zeigen" rel="nofollow"><strong>StumbleUpon</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$stumble_link.'" target="_blank" title="Auf Stumbleupon zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/stumbleupon.png" border="0" alt="Auf Stumbleupon zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['tumblr']==true) {
		$tumblr_link = 'http://www.tumblr.com/share/link?url='.urlencode(get_permalink()).'&name='.urlencode(get_the_title()).'&description='.urlencode(descExcerpt());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title=get_the_title();
		$network='Tumblr.';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fstu" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(get_the_title()).'&fsurl='.urlencode($tumblr_link).'" target="_blank" title="Auf tumblr zeigen" rel="nofollow"><strong>Tumblr.</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fstu" href="'.$tumblr_link.'" target="_blank" title="Auf tumblr zeigen" rel="nofollow"><strong>Tumblr.</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$tumblr_link.'" target="_blank" title="Auf tumblr zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/tumblr.png" border="0" alt="Auf tumblr zeigen" /></a></div>';
		}
		}

		$outputa .= '</div><div class="clear"></div>';
		if ($option['privacy']==true) {
		$outputa .= '<span class="fsprivacy" title="'.stripslashes($option['privacy_text']).'"><u>Datenschutz Hinweis</u></span>';
		}
		if ($option['supportlink']==false) {
		$outputa .= '<span class="fssl">Social Sharing powered by <a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=SupportLink&utm_campaign=flodjiShareWP"><u>flodjiShare</u></a></span>';
		}
		$outputa .= '<br />';
		
		if(is_feed()){
		$outputa = '';
		}

		if ($option['position']=='unter') {
		return $content.$outputa;
		}
		if ($option['position']=='ueber') {
		return $outputa.$content;
		}
		if ($option['position']=='shortcode') {
		return $outputa;
		}
}

function flodjiShareOpenGraph() {
	if(is_singular()){
		if (have_posts()) : while (have_posts()) : the_post(); 
			$parameter[]=get_the_title($post->post_title);
			$parameter[]=get_permalink();
			$parameter[]=flodjiShareFirstImage();
			$parameter[]=get_option('blogname');
			$parameter[]=descExcerpt();
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
$post_id = get_the_ID();
$comments_count = wp_count_comments($post_id);
$option_string = get_option('flodjishare');
$option = array();
$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true, 'metro'=>true, 'supportlink'=>true, 'privacy'=>true);
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
	$txt.="<meta property='og:description' content='".strip_tags($parameter[4])."'/>";
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
	$txt.="<div itemscope itemtype=\"http://schema.org/Article\">";
	$txt.="\n";
	$txt.="<meta itemprop='name' content='".$parameter[0]."'>";
	$txt.="\n";
	$txt.="<meta itemprop='description' content='".strip_tags($parameter[4])."'>";
	$txt.="\n";
	if($parameter[2] != ''){
	$txt.="<meta itemprop='image' content='".$parameter[2]."'>";
	$txt.="\n";
	}
	$txt.="<meta itemprop='url' content='".$parameter[1]."'>";
	$txt.="\n";
	$txt.="<meta itemprop='interactionCount' content='".$comments_count->approved ."' />";
	$txt.="\n";
	$txt.="</div>";
	$txt.="\n";
	}
	if($parameter[4] != ''){
	$txt.="<meta name='description' content='".strip_tags(flodjiShareNormDesc($parameter[4]))."'/>";
	$txt.="\n";
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
	$txt.='<meta name="twitter:description" content="'.strip_tags($parameter[4]).'">';
	$txt.="\n";
	}
	if($parameter[2] != ''){
	$txt.='<meta name="twitter:image" content="'.$parameter[2].'">';
	$txt.="\n";
	}
	}
	return $txt;
}

function flodjiShareFirstImage(){
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
$fb = '<a target="_blank" href="https://www.facebook.com/pages/Flodjide/415996855137000"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/facebook.png" /></a> ';
$tw = '<a target="_blank" href="http://www.twitter.com/flodji"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/twitter.png" /></a> ';
$gp = '<a target="_blank" href="https://plus.google.com/104542622643572083517/"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/googleplus.png" /></a> ';
$fd = '<a target="_blank" href="http://flodji.de/feed/"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/rss.png" /></a>';
return '<h3>Folge mir:</h3>' . $fb . $tw . $gp . $fd;
}

function spendPayPalFlodjiShare(){
$paypalbutton = '<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K9U25CKQNA5GL"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/donate.png" height="32"/></a>';
return '<h3>Spenden:</h3>' . $paypalbutton;
}

function flodjishare_options () {
	$option_name = 'flodjishare';
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if( isset($_POST['flodjishare_position'])) {
		$option = array();
		$option['active_buttons'] = array('facebook'=>false, 'twitter'=>false, 'digg'=>false, 'delicious'=>false, 'vz'=>false, 'xing'=>false, 'gplus'=>false, 'linkedin'=>false, 'pinterest'=>false, 'stumbleupon'=>false, 'tumblr'=>false, 'opengraph'=>false, 'richsnippets'=>false, 'twittercards'=>false, 'metro'=>true, 'counter'=>true, 'supportlink'=>true, 'privacy'=>true);
		if ($_POST['flodjishare_active_facebook']=='on') { $option['active_buttons']['facebook'] = true; }
		if ($_POST['flodjishare_active_twitter']=='on') { $option['active_buttons']['twitter'] = true; }
		if ($_POST['flodjishare_active_digg']=='on') { $option['active_buttons']['digg'] = true; }
		if ($_POST['flodjishare_active_delicious']=='on') { $option['active_buttons']['delicious'] = true; }		
		if ($_POST['flodjishare_active_vz']=='on') { $option['active_buttons']['vz'] = true; }
		if ($_POST['flodjishare_active_xing']=='on') { $option['active_buttons']['xing'] = true; }
		if ($_POST['flodjishare_active_gplus']=='on') { $option['active_buttons']['gplus'] = true; }
		if ($_POST['flodjishare_active_linkedin']=='on') { $option['active_buttons']['linkedin'] = true; }
		if ($_POST['flodjishare_active_pinterest']=='on') { $option['active_buttons']['pinterest'] = true; }
		if ($_POST['flodjishare_active_stumbleupon']=='on') { $option['active_buttons']['stumbleupon'] = true; }
		if ($_POST['flodjishare_active_tumblr']=='on') { $option['active_buttons']['tumblr'] = true; }	
		if ($_POST['flodjishare_active_metro']=='on') { $option['metro'] = true; }
		if ($_POST['flodjishare_active_counter']=='on') { $option['counter'] = true; }
		if ($_POST['flodjishare_active_opengraph']=='on') { $option['active_buttons']['opengraph'] = true; }
		if ($_POST['flodjishare_active_richsnippets']=='on') { $option['active_buttons']['richsnippets'] = true; }
		if ($_POST['flodjishare_active_twittercards']=='on') { $option['active_buttons']['twittercards'] = true; }
		if ($_POST['flodjishare_active_privacy']=='on') { $option['privacy'] = true; }
		if ($_POST['flodjishare_active_supportlink']=='on') { $option['supportlink'] = true; }
		$option['position'] = esc_html($_POST['flodjishare_position']);
		$option['intro_text'] = esc_html($_POST['flodjishare_intro_text']);
		$option['twitter_text'] = esc_html($_POST['flodjishare_twitter_text']);
		$option['fb_app_id'] = esc_html($_POST['flodjishare_fb_app_id']);
		$option['fb_admin'] = esc_html($_POST['flodjishare_fb_admin']);
		$option['privacy_text'] = esc_html($_POST['flodjishare_privacy_text']);
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
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true, 'metro'=>true, 'counter'=>true, 'supportlink'=>true, 'privacy'=>true);
		$option['position'] = 'unter';
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true);
		$option['intro_text'] = array('intro_text'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['privacy_text'] = array('privacy_text'=>true);
		$option['altimg'] = array('altimg'=>true);
		$option['twitsite'] = array('twitsite'=>true);
		add_option($option_name, 'unter');
		$option_string = get_option($option_name);
	}
	if ($option_string=='ueber' or $option_string=='unter' or $option_string=='shortcode') {
		$flodjishare_options = explode('|||',$option_string);
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true, 'metro'=>true, 'counter'=>true, 'supportlink'=>true, 'privacy'=>true);
		$option['position'] = $flodjishare_options[0];
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true);
		$option['intro_text'] = array('intro_text'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['privacy_text'] = array('privacy_text'=>true);
		$option['altimg'] = array('altimg'=>true);
		$option['twitsite'] = array('twitsite'=>true);
	} else {
		$option = json_decode($option_string, true);
	}
	$sel_above = ($option['position']=='ueber') ? 'selected="selected"' : '';
	$sel_below = ($option['position']=='unter') ? 'selected="selected"' : '';
	$sel_short = ($option['position']=='shortcode') ? 'selected="selected"' : '';
	$active_facebook 	= ($option['active_buttons']['facebook']==true) ? 'checked="checked"' : '';
	$active_twitter  	= ($option['active_buttons']['twitter'] ==true) ? 'checked="checked"' : '';
	$active_digg		= ($option['active_buttons']['digg']==true) ? 'checked="checked"' : '';
	$active_delicious	= ($option['active_buttons']['delicious']==true) ? 'checked="checked"' : '';
	$active_vz			= ($option['active_buttons']['vz']==true) ? 'checked="checked"' : '';
	$active_xing		= ($option['active_buttons']['xing']==true) ? 'checked="checked"' : '';
	$active_gplus		= ($option['active_buttons']['gplus']==true) ? 'checked="checked"' : '';
	$active_linkedin	= ($option['active_buttons']['linkedin']==true) ? 'checked="checked"' : '';
	$active_pinterest	= ($option['active_buttons']['pinterest']==true) ? 'checked="checked"' : '';
	$active_stumbleupon	= ($option['active_buttons']['stumbleupon']==true) ? 'checked="checked"' : '';
	$active_tumblr		= ($option['active_buttons']['tumblr']==true) ? 'checked="checked"' : '';
	$active_metro		= ($option['metro']==true) ? 'checked="checked"' : '';
	$active_counter		= ($option['counter']==true) ? 'checked="checked"' : '';
	$active_opengraph	= ($option['active_buttons']['opengraph']==true) ? 'checked="checked"' : '';
	$active_richsnippets= ($option['active_buttons']['richsnippets']==true) ? 'checked="checked"' : '';
	$active_twittercards= ($option['active_buttons']['twittercards']==true) ? 'checked="checked"' : '';
	$active_privacy		= ($option['privacy']==true) ? 'checked="checked"' : '';
	$active_supportlink	= ($option['supportlink']==true) ? 'checked="checked"' : '';
	$show_in_posts 		= ($option['show_in']['posts']==true) ? 'checked="checked"' : '';
	$show_in_pages 		= ($option['show_in']['pages'] ==true) ? 'checked="checked"' : '';
	$show_in_home 		= ($option['show_in']['home'] ==true) ? 'checked="checked"' : '';
	$intro_text			= ($option['intro_text']=='') ? 'selected="selected"' : '';
	$twitter_text		= ($option['twitter_text']=='') ? 'selected="selected"' : '';
	$fb_app_id			= ($option['fb_app_id']=='') ? 'selected="selected"' : '';
	$fb_admin			= ($option['fb_admin']=='') ? 'selected="selected"' : '';
	$privacy_text		= ($option['privacy_text']=='') ? 'selected="selected"' : '';
	$altimg				= ($option['altimg']=='') ? 'selected="selected"' : '';
	$twitsite			= ($option['twitsite']=='') ? 'selected="selected"' : '';
	$outputa .= '
	<div style="width:400px;float:left;">
		<h2>flodjiShare Einstellungen</h2>
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
		.' <input type="checkbox" name="flodjishare_active_linkedin" '.$active_linkedin.'> '
		. __("LinkedIn", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_pinterest" '.$active_pinterest.'> '
		. __("Pinterest", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_stumbleupon" '.$active_stumbleupon.'> '
		. __("Stumbleupon", 'menu' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_tumblr" '.$active_tumblr.'> '
		. __("Tumblr", 'menu' ).' &nbsp;&nbsp;<br />'	
		.'<br /><br /></td></tr>
		<tr><td valign="top">'.__("Position", 'menu' ).':</td>
		<td><select name="flodjishare_position">
			<option value="ueber" '.$sel_above.' > '.__('&Uuml;ber dem Beitrag', 'menu' ).'</option>
			<option value="unter" '.$sel_below.' > '.__('Unter dem Beitrag', 'menu' ).'</option>
			<option value="shortcode" '.$sel_short.' > '.__('Nur bei Shortcode [flodjishare]', 'menu' ).'</option>
			</select><br /> 
		<br /></td></tr>
		
		<tr><td valign="top">'.__("Design", 'menu' ).':</td>
		<td><input type="checkbox" name="flodjishare_active_metro" '.$active_metro.'> '
		. __("Metro Design aktivieren", 'menu' ).'<br /><input type="checkbox" name="flodjishare_active_counter" '.$active_counter.'> '
		. __("Klickz&auml;hler aktivieren (nur Metro Design)", 'menu' ).'&nbsp;&nbsp;<br /><br /></td></tr>
		
		<tr><td valign="top">'.__("Intro Text", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_intro_text" value="'.stripslashes($option['intro_text']).'" size="50"><br />
		<span class="description">'.__("Trage hier den Intro Text f&uuml;r die Share Buttons ein (z.B. Diesen Beitrag teilen...).<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">'.__("flodjiShare Extras", 'menu' ).':</td>
		<td><input type="checkbox" name="flodjishare_active_opengraph" '.$active_opengraph.'> '
		. __("Opengraph Support", 'menu' ).' &nbsp;&nbsp;<br />
		
		<input type="checkbox" name="flodjishare_active_richsnippets" '.$active_richsnippets.'> '
		. __("Rich Snippets Support", 'menu' ).' &nbsp;&nbsp;<br />

		<input type="checkbox" name="flodjishare_active_twittercards" '.$active_twittercards.'> '
		. __("Twitter Card Support", 'menu' ).' &nbsp;&nbsp;<br />
		
		<input type="checkbox" name="flodjishare_active_privacy" '.$active_privacy.'> '
		. __("Datenschutzhinweis anzeigen", 'menu' ).' &nbsp;&nbsp;<br /><br />
		
		</td></tr>
		
		<tr><td valign="top">'.__("Twitter Name", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_twitter_text" value="'.stripslashes($option['twitter_text']).'" size="50"><br />
		<span class="description">'.__("Trage hier Deinen Twitter Usernamen ein. Dieser wird dann in den Twitter Cards (wenn aktiviert)<br />und am Ende der Tweets erscheinen, z.B. (via @Dein Twitter Name).<br />", 'menu' ).'</span>
		</td></tr>

		<tr><td valign="top">'.__("Twitter Seite", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="twitsite" value="'.stripslashes($option['twitsite']).'" size="50"><br />
		<span class="description">'.__("Trage hier den Twitter Usernamen Deiner Worspress Seite ein. Falls nicht vorhanden, trage einfach Deinen Twitter Usernamen ein.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">'.__("Ersatzbild", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="altimg" value="'.stripslashes($option['altimg']).'" size="50"><br />
		<span class="description">'.__("Trage hier den Link zu einem Ersatzbild ein. Dieses wird beim Teilen verwendet, wenn im Artikel kein Bild vorhanden ist.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">'.__("Facebook AppId", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_fb_app_id" value="'.stripslashes($option['fb_app_id']).'" size="50"><br />
		<span class="description">'.__("Trage hier Deine Facebook AppId ein.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">'.__("Facebook Admin", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_fb_admin" value="'.stripslashes($option['fb_admin']).'" size="50"><br />
		<span class="description">'.__("Trage hier Deinen Facebook Usernamen ein.<br />", 'menu' ).'</span>
		</td></tr>		
			
		<tr><td valign="top">'.__("Datenschutzhinweistext", 'menu' ).':</td>
		<td style="padding-bottom:20px;">
		<textarea name="flodjishare_privacy_text" value="'.stripslashes($option['privacy_text']).'" cols="50" rows="5">'.stripslashes($option['privacy_text']).'</textarea><br />
		<span class="description">'.__("Trage hier den Datenschutzhinweistext ein.<br />", 'menu' ).'</span>
		</td></tr>
		
		<tr><td valign="top">Supportlink:</td>
		<td style="padding-bottom:20px;">
		<input type="checkbox" name="flodjishare_active_supportlink" '.$active_supportlink.'> '
		. __("Supportlink deaktivieren", 'menu' ).'&nbsp;&nbsp;<br /></td></tr>
		
		</table>
		<hr />
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="'.esc_attr('Speichern').'" />
		</p>
		</form>
		Bei Problemen oder Fragen kannst Du gern das <a target="_blank" href="http://flodji.de/forum/">Support Forum</a> besuchen.</p>
		<p>Die Buttons stammen von dieser <a target="_blank" href="http://wplift.com/freebie-70-32px-custom-social-media-website-icons">Seite</a>. | Das Men&uuml;-Icon habe ich <a target="_blank" href="http://salleedesign.com/">hier</a> gefunden. | Das Metro Design habe ich ausschlie&szlig;lich mit CSS Anweisungen erstellt.</p>
	</div>';
	$outputa .= '<div style="margin-left:50px;border-left:thin solid #ccc;border-right:thin solid #ccc;border-bottom:thin solid #ccc;padding:3px;width:200px;float:left;box-shadow: 0 1px 1px #999;">
<div>
<a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=flodji.de_Logo&utm_campaign=flodjiShareWP"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/flodjidelogo02.png" width="180"/></a><h2>flodji.de Feed</h2>';
$rss = fetch_feed( "http://flodji.de/feed/" );
if(!is_wp_error($rss)){
$maxitems = $rss->get_item_quantity( 5 ); 
$rss_items = $rss->get_items( 0, $maxitems );
}
$outputa .= '<ul>';
if($maxitems == 0){
$outputa .= '<li>Keine Eintr&auml;ge</li>';
} else {
foreach ($rss_items as $item){
$outputa .= '<li>
    <a href="'.esc_url($item->get_permalink()).'?utm_source=flodjiShareWP&utm_medium=FeedLink&utm_campaign=flodjiShareWP" title="'.esc_html( $item->get_title() ).'" target="_blank">';
$outputa .= esc_html( $item->get_title() );
$outputa .= '</a></li>';
}
}
$outputa .= '</ul>
</div>
<div>
<h2>Weitere Links</h2>
<ul>
<li><a target="_blank" href="http://flodji.de/downloads/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Weitere Plugins / Themes</a></li>
<li><a target="_blank" href="http://flodji.de/category/gewinnspiele/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Gewinnspiele</a></li>
<li><a target="_blank" href="http://flodji.de/fragen-und-antworten/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">FAQs</a></li>
<li><a target="_blank" href="http://flodji.de/linkpartner/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Linkpartner werden</a></li>
<li><a target="_blank" href="http://flodji.de/kontakt/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Kontakt</a></li>
<li><a target="_blank" href="http://flodji.de/forum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Forum</a></li>
<li><a target="_blank" href="http://flodji.de/werben-auf-flodji-de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Werben auf flodji.de</a></li>
<li><a target="_blank" href="http://flodji.de/gastartikel/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Gastartikel schreiben</a></li>
<li><a target="_blank" href="http://flodji.de/die-flodji-de-android-app-beta/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">flodji.de Android App</a></li>
<li><a target="_blank" href="http://flodji.de/impressum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">Impressum</a></li>
</ul>
</div>
</div>';
	echo $outputa;
}
?>