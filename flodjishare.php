<?php
/*
Plugin Name: flodjiShare
Plugin URI: http://flodji.de
Description: Mit flodjiShare wird Webseitenbetreibern eine einfache L&ouml;sung angeboten die Social Sharing und Bookmark Buttons der gro&szlig;en Netzwerke in die eigene Seite einzubinden.
Version: 2.7
Author: flodji
Author URI: http://flodji.de
License: GPL2
*/
global $wpdb;
add_action('wp_head', 'flodjiShareOpenGraph');
add_filter('language_attributes', 'flodjishare_schema');
$option_string = get_option('flodjishare');
if ($option_string=='ueber' or $option_string=='unter' or $option_string=='both' or $option_string=='shortcode') {
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
    add_menu_page( 'flodjiShare Einstellungen', 'flodjiShare', 'manage_options', 'flodjishare_einstellungen', 'flodjishare_options', plugins_url( 'flodjishare/buttons/fs_ico.png' ), 999 );
	add_submenu_page( 'flodjishare_einstellungen', 'flodjiShare Klick Counter', 'flodjiShare Klick Counter', 'manage_options', 'klick-counter', 'flodjiShareKlickCounter' ); 
}

function flodjiShareAddContactMethods($contactmethods){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if($option['active_buttons']['gplusAuthor']){
	$contactmethods['gplusiduser'] = 'Google Plus ID';
	$contactmethods['fstwittername'] = 'Twitter Name';
	return $contactmethods;
}
}
add_filter('user_contactmethods','flodjiShareAddContactMethods',10,1);

function flodjiShareAutor(){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if($option['active_buttons']['gplusAuthor']){
if($option['gplusidpage']){
echo '<link href="https://plus.google.com/'.stripslashes($option['gplusidpage']).'/" rel="publisher" />';
echo "\n";
}
if(($option['gplusiduser']) or (get_the_author_meta('gplusiduser') != '')){
$user = get_the_author_meta('gplusiduser');
if($user == ''){
echo '<link href="https://plus.google.com/'.stripslashes($option['gplusiduser']).'?rel=author" rel="author" />';
echo "\n";
} else {
echo '<link href="https://plus.google.com/'.stripslashes(get_the_author_meta('gplusiduser')).'?rel=author" rel="author" />';
echo "\n";
}
}
}
}
add_action('wp_head', 'flodjiShareAutor');

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
$mengr			= $menge[0];
$wieviel_seiten = ceil($menge / $eintraege_pro_seite); 
$ausgabe .= '<br /><div align="center">';
$ausgabe .= '<strong>Seite:</strong>';
$ausgabe .= blaetterfunktion($seite,$wieviel_seiten,'admin.php?page=klick-counter&sort='.$sort,3);
$ausgabe .= '</div><br /><p><strong>Hinweis:</strong><br />Der Klickz&auml;hler zeigt die Anzahl der Klicks auf die Share Buttons an. Diese Zahl muss nicht zwingend mit der tats&auml;chlichen Anzahl von Shares &uuml;bereinstimmen.</p></div>';
$ausgabe .= '<div style="margin-left:50px;border-left:thin solid #ccc;border-right:thin solid #ccc;border-bottom:thin solid #ccc;padding:3px;width:200px;float:left;box-shadow: 0 1px 1px #999;">
<div>
<a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=flodji.de_Logo&utm_campaign=flodjiShareWP"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/flodjidelogo03.gif" width="180"/></a><h2>flodji.de Feed</h2>';
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

function blaetterfunktion($seite,$maxseite,$url="",$anzahl=4,$get_name="seite") {
   if(preg_match("/\?/",$url)) $anhang = "&";
   else $anhang = "?";

   if(substr($url,-1,1) == "&") {
      $url = substr_replace($url,"",-1,1);
      }
   else if(substr($url,-1,1) == "?") {
      $anhang = "?";
      $url = substr_replace($url,"",-1,1);
      }

   if($anzahl%2 != 0) $anzahl++;

   $a = $seite-($anzahl/2);
   $b = 0;
   $blaetter = array();
   while($b <= $anzahl)
      {
      if($a > 0 AND $a <= $maxseite)
         {
         $blaetter[] = $a;
         $b++;
         }
      else if($a > $maxseite AND ($a-$anzahl-2)>=0)
         {
         $blaetter = array();
         $a -= ($anzahl+2);
         $b = 0;
         }
      else if($a > $maxseite AND ($a-$anzahl-2)<0)
         {
         break;
         }

      $a++;
      }
   $return = "";
   if(!in_array(1,$blaetter) AND count($blaetter) > 1)
      {
      if(!in_array(2,$blaetter)) $return .= "&nbsp;<a href=\"{$url}{$anhang}{$get_name}=1\">1</a>&nbsp;...";
      else $return .= "&nbsp;<a href=\"{$url}{$anhang}{$get_name}=1\">1</a>&nbsp;";
      }

   foreach($blaetter AS $blatt)
      {
      if($blatt == $seite) $return .= "&nbsp;<b>$blatt</b>&nbsp;";
      else $return .= "&nbsp;<a href=\"{$url}{$anhang}{$get_name}=$blatt\">$blatt</a>&nbsp;";
      }

   if(!in_array($maxseite,$blaetter) AND count($blaetter) > 1)
      {
      if(!in_array(($maxseite-1),$blaetter)) $return .= "...&nbsp;<a href=\"{$url}{$anhang}{$get_name}=$maxseite\">letzte</a>&nbsp;";
      else $return .= "&nbsp;<a href=\"{$url}{$anhang}{$get_name}=$maxseite\">$maxseite</a>&nbsp;";
      }

   if(empty($return))
      return  "&nbsp;<b>1</b>&nbsp;";
   else
      return $return;
}

function flodjiShareNormDesc( $title ){
$slug = $title;
$bad = array( '"',"'",'“','”',"\n","\r", "&rarr;", "&#8230;");
$good = array( '','','','',' ','','','...');
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
global $wpdb, $post;
	if(is_feed()){
	return $content;
	}

	$option_string = get_option('flodjishare');
	$option = json_decode($option_string, true);
	
	if(is_single()){
	if(!$option['show_in']['posts']){
			return $content;
		}		
	}
	if(is_page()){
	if(!$option['show_in']['pages']){
			return $content;
		}
	}
	if(is_home()){
	if(!$option['show_in']['home']){
			return $content;
		}
	}
	if(is_category()){
	if(!$option['show_in']['category']){
			return $content;
		}
	}
	if(is_search()){
	if(!$option['show_in']['search']){
			return $content;
		}
	}
	if(is_archive()){
	if(!$option['show_in']['archive']){
			return $content;
		}
	}
	
	$skipsingle = preg_split("/[\s,]+/", $option['skip_single']);
	if(is_single($skipsingle)){
			return $content;
	}
	
	$skippage = preg_split("/[\s,]+/", $option['skip_page']);
	if(is_page($skippage)){
			return $content;
	}
	
	if($option['skip_cat'] == ''){
	} else {
	$skippage = preg_split("/[\s,]+/", $option['skip_cat']);
	if(is_category($skipcat)){
			return $content;
	}
	}
	$args=array(
	'public'   => true,
	'_builtin' => false
	); 
	$output = 'object';
	$operator = 'and';
	$post_types=get_post_types($args,$output,$operator); 
	foreach ($post_types  as $post_type ) {
	if((!$option[$post_type->name]) && (get_post_type( get_the_ID() ) == $post_type->name)){
    return $content;
		}
	}
		$outputa = '';
		$outputa .= '<div class="fsmain">';
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
		$title = strip_tags(get_the_title());
		$network='Facebook';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsfb" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($title).'&fsurl='.urlencode('http://www.facebook.com/sharer.php?u='.get_permalink().'&amp;t='.get_the_title()).'" onclick="return popup(this.href);" rel="nofollow"><strong>Facebook</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsfb" href="http://www.facebook.com/sharer.php?u='.urlencode(get_permalink()).'&amp;t='.urlencode(strip_tags(get_the_title())).'" onclick="return popup(this.href);" rel="nofollow"><strong>Facebook</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="http://www.facebook.com/sharer.php?u='.urlencode(get_permalink()).'&amp;t='.urlencode(strip_tags(get_the_title())).'" onclick="return popup(this.href);" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/facebook.png" border="0" /></a></div>'; 
		}
		}
		
		if ($option['active_buttons']['flattr']==true) {
		$flattrurl = 'https://flattr.com/submit/auto?user_id='.stripslashes($option['flattr_id']).'&url='.urlencode(get_permalink()).'&title='.urlencode(strip_tags(get_the_title())).'&description='.urlencode(descExcerpt());
		if ($option['metro']==true){
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='Flattr';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsfl" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($flattrurl).'" target="_blank" rel="nofollow"><strong>Flattr</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsfl" href="'.$flattrurl.'" onclick="return popup(this.href);" rel="nofollow"><strong>Flattr</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$flattrurl.'" onclick="return popup(this.href);" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/flattr.png" border="0" /></a></div>'; 
		}
		}

		if ($option['active_buttons']['twitter']==true) {
		$title = strip_tags(get_the_title());
		$tw_link = 'https://twitter.com/share?url='.urlencode(get_permalink()).'&via='.stripslashes($option['twitter_text']).'&text='.urlencode(html_entity_decode(strip_tags($title)));
		if ($option['metro']==true){
		if ($option['counter']==true){		
		$network='Twitter';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fstw" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($tw_link).'" onclick="return popup(this.href);" rel="nofollow"><strong>Twitter</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fstw" href="'.$tw_link.'" onclick="return popup(this.href);" rel="nofollow"><strong>Twitter</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"> 
		<a href="'.$tw_link.'" onclick="return popup(this.href);" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/twitter.png" border="0" /></a></div>';
		}
		}

		if ($option['active_buttons']['digg']==true) {
		$digg_link = 'http://digg.com/submit?url='.get_permalink().'&amp;title='.strip_tags(get_the_title());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='Digg';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsdigg" target="_blank" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($digg_link).'" rel="nofollow"><strong>Digg</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsdigg" target="_blank" href="'.$digg_link.'" rel="nofollow"><strong>Digg</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"> 
		<a target="_blank" href="'.$digg_link.'" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/digg.png" border="0" /></a></div>';
		}
		}

		if ($option['active_buttons']['delicious']==true) {
		$del_link =	'http://www.delicious.com/post?url='.urlencode(get_permalink()).'&notes='.urlencode(descExcerpt()).'&title='.urlencode(strip_tags(get_the_title()));
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='Delicious';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsdel" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($del_link).'" target="_blank"><strong>Delicious</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsdel" href="'.$del_link.'" target="_blank"><strong>Delicious</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"> 
					<a href="'.$del_link.'" target="_blank" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/delicious.png" alt="Delicious" border="0" /></a></div>';
		}
		}

		if ($option['active_buttons']['vz']==true) {
		$vz_link = 'http://platform-redirect.vz-modules.net/r/Link/Share/?url='.urlencode(get_permalink()).'&title='.urlencode(strip_tags(get_the_title())).'&description='.urlencode(descExcerpt()).'&thumbnail=' . urlencode(flodjiShareFirstImage());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='VZ';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsvz" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($vz_link).'" title=" ' . $title . ' Deinen Freunden im VZ zeigen" target="_blank" rel="nofllow"><strong>VZ</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsvz" href="'.$vz_link.'" title=" ' . urlencode(strip_tags(get_the_title())) . ' Deinen Freunden im VZ zeigen" target="_blank" rel="nofllow"><strong>VZ</strong></a></div>';
		}
		} else {
		$outputa .= '<div style="float:left; padding-left:3px;margin-right:-2px;"><a target="_blank" rel="nofollow" href="'.$vz_link.'"><img style="max-width: 100%;margin:-1px;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/vz_follow.png" title="studiVZ meinVZ sch&uuml;lerVZ" border="0" alt="VZ Netzwerke" width="38" /></a></div>'; 
		}
		}
		
		if ($option['active_buttons']['gplus']==true) {
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='Google Plus';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsgp" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode('https://plus.google.com/share?url=' . urlencode(get_permalink())) . '" onclick="return popup(this.href);" rel="nofollow"><strong>Google Plus</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
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
		$title = strip_tags(get_the_title());
		$network='Xing';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsxi" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($xing_link).'" target="_blank" rel="nofollow"><strong>Xing</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fsxi" href="'.$xing_link.'" target="_blank" rel="nofollow"><strong>Xing</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$xing_link.'" target="_blank" title="Ihren XING-Kontakten zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/xing.png" border="0" alt="Ihren XING-Kontakten zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['linkedin']==true) {
		$linkedin_link = 'http://www.linkedin.com/shareArticle?mini=true&url='.urlencode(get_permalink()).'&title='.urlencode(strip_tags(get_the_title())).'&ro=false&summary='.urlencode(descExcerpt());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='LikedIn';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fsli" target="_blank" rel="nofollow" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($linkedin_link).'"><strong>LinkedIn</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
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
		$title = strip_tags(get_the_title());
		$network='Pinterest';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fspi" rel="nofollow" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($pin_link).'" onclick="return popup(this.href);"><strong>Pinterest</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fspi" rel="nofollow" href="'.$pin_link.'" onclick="return popup(this.href);"><strong>Pinterest</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$pin_link.'" target="_blank" title="Auf Pinterest zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/pinterest.png" border="0" alt="Auf Pinterest zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['stumbleupon']==true) {
		$stumble_link = "http://www.stumbleupon.com/submit?url=" . urlencode(get_permalink()) . "&title=" . urlencode(strip_tags(get_the_title()));
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='StumbleUpon';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fssu" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($stumble_link).'" target="_blank" title="Auf Stumbleupon zeigen" rel="nofollow"><strong>StumbleUpon</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fssu" href="'.$stumble_link.'" target="_blank" title="Auf Stumbleupon zeigen" rel="nofollow"><strong>StumbleUpon</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$stumble_link.'" target="_blank" title="Auf Stumbleupon zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/stumbleupon.png" border="0" alt="Auf Stumbleupon zeigen" /></a></div>';
		}
		}
		
		if ($option['active_buttons']['tumblr']==true) {
		$tumblr_link = 'http://www.tumblr.com/share/link?url='.urlencode(get_permalink()).'&name='.urlencode(strip_tags(get_the_title())).'&description='.urlencode(descExcerpt());
		if ($option['metro']==true) {
		if ($option['counter']==true){
		$title = strip_tags(get_the_title());
		$network='Tumblr.';
		$klicks = $wpdb->get_var("SELECT klicks FROM flodjiShareLinks WHERE title='$title' AND network='$network'");
		if($klicks == ''){
		$klicks = '0';
		}
		$outputa .= '<div class="fsleft"><a class="fsbase fstu" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode(strip_tags(get_the_title())).'&fsurl='.urlencode($tumblr_link).'" target="_blank" title="Auf tumblr zeigen" rel="nofollow"><strong>Tumblr.</strong></a><span class="fscounter"><strong>'.short_number($klicks).'</strong></span></div>';
		} else {
		$outputa .= '<div class="fsleft"><a class="fsbase fstu" href="'.$tumblr_link.'" target="_blank" title="Auf tumblr zeigen" rel="nofollow"><strong>Tumblr.</strong></a></div>';
		}
		} else {
		$outputa .= '<div class="fsbtnfloat"><a href="'.$tumblr_link.'" target="_blank" title="Auf tumblr zeigen" rel="nofollow"><img style="max-width: 100%;" src="'.home_url().'/wp-content/plugins/flodjishare/buttons/tumblr.png" border="0" alt="Auf tumblr zeigen" /></a></div>';
		}
		}

		$outputa .= '</div><div class="fsclear"></div>';
		if ($option['privacy']==true) {
		$outputa .= '<span class="fsprivacy" title="'.stripslashes($option['privacy_text']).'"><u>Datenschutz Hinweis</u></span>';
		}
		if ($option['supportlink']==false) {
		$outputa .= '<span class="fssl">Social Sharing powered by <a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=SupportLink&utm_campaign=flodjiShareWP"><u>flodjiShare</u></a></span>';
		}
		$outputa .= '<br />';

		if ($option['position']=='unter'){
		return $content.$outputa;
		}
		if ($option['position']=='ueber'){
		return $outputa.$content;
		}
		if ($option['position']=='both'){
		return $outputa.$content.$outputa;
		}
		if ($option['position']=='shortcode'){
		return $outputa;
		}
}

function flodjiShareOpenGraph() {
$option_string = get_option('flodjishare');
	$option = json_decode($option_string, true);
	
	if(is_single()) {
		if (!$option['show_in']['posts']) {
			return;
		}		
	} else {
		if ((!$option['show_in']['pages'])&&(is_page())) {
			return;
	}
	}
	if(is_home()) {
		if (!$option['show_in']['home']) {
			return;
	}
	}
	if(is_category()) {
		if (!$option['show_in']['category']) {
			return;
	}
	}
	if(is_search()) {
		if (!$option['show_in']['search']) {
			return;
	}
	}
	if(is_archive()) {
		if (!$option['show_in']['archive']) {
			return;
	}
	}
	
	$skipsingle = preg_split("/[\s,]+/", $option['skip_single']);
	if(is_single($skipsingle)){
			return;
	}
	
	$skippage = preg_split("/[\s,]+/", $option['skip_page']);
	if(is_page($skippage)){
			return;
	}
	
	$args=array(
	'public'   => true,
	'_builtin' => false
	); 
	$output = 'object';
	$operator = 'and';
	$post_types=get_post_types($args,$output,$operator); 
	foreach ($post_types  as $post_type ) {
	if((!$option[$post_type->name]) && (get_post_type( get_the_ID() ) == $post_type->name)){
    return;
	}
	}
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
$post_id 			= get_the_ID();
$comments_count 	= wp_count_comments($post_id);
$option_string 		= get_option('flodjishare');
$option 			= json_decode($option_string, true);
$values = get_post_custom( $post->ID );
    extract( $values, EXTR_SKIP );
	$allowed_html = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'em' => array(),
		'strong' => array()
	);
$_fs_title_output 	= wp_kses($_flodjisharebox_fs_title[0], $allowed_html);
$_fs_desc_output 	= wp_kses($_flodjisharebox_fs_desc[0], $allowed_html);
$_fs_image_output 	= wp_kses($_flodjisharebox_fs_image[0], $allowed_html);
if($option['active_buttons']['opengraph']==true){
	$txt.="\n";
	if($_fs_title_output != ''){
	$txt.="<meta property='og:title' content='".strip_tags($_fs_title_output)."'/>";
	} else {
	$txt.="<meta property='og:title' content='".$parameter[0]."'/>";
	}
	$txt.="\n";
	$txt.="<meta property='og:url' content='".$parameter[1]."'/>";
	$txt.="\n";
	if($_fs_image_output != ''){
	$txt.="<meta property='og:image' content='".strip_tags($_fs_image_output)."'/>";
	$txt.="\n";
	} else {
	if($parameter[2] != ''){
	$txt.="<meta property='og:image' content='".$parameter[2]."'/>";
	$txt.="\n";
	}
	}
	$txt.="<meta property='og:site_name' content='".$parameter[3]."'/>";
	$txt.="\n";
	if($_fs_desc_output != ''){
	$txt.="<meta property='og:description' content='".strip_tags($_fs_desc_output)."'/>";
	$txt.="\n";
	} else {
	if($parameter[4] != ''){
	$txt.="<meta property='og:description' content='".strip_tags($parameter[4])."'/>";
	$txt.="\n";
	}
	}	
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
	if($_fs_title_output != ''){
	$txt.="<meta itemprop='name' content='".strip_tags($_fs_title_output)."'>";
	} else {
	$txt.="<meta itemprop='name' content='".$parameter[0]."'>";
	}
	$txt.="\n";
	if($_fs_desc_output != ''){
	$txt.="<meta itemprop='description' content='".strip_tags($_fs_desc_output)."'>";
	$txt.="\n";
	} else {
	if($parameter[4] != ''){
	$txt.="<meta itemprop='description' content='".strip_tags($parameter[4])."'>";
	$txt.="\n";
	}
	}
	if($_fs_image_output != ''){
	$txt.="<meta itemprop='image' content='".strip_tags($_fs_image_output)."'>";
	$txt.="\n";
	} else {
	if($parameter[2] != ''){
	$txt.="<meta itemprop='image' content='".$parameter[2]."'>";
	$txt.="\n";
	}
	}
	$txt.="<meta itemprop='url' content='".$parameter[1]."'>";
	$txt.="\n";
	$txt.="<meta itemprop='interactionCount' content='".$comments_count->approved ."' />";
	$txt.="\n";
	$txt.="</div>";
	$txt.="\n";
	}
	if($option['active_buttons']['metadesc']==true){
	if($_fs_desc_output != ''){
	$txt.="<meta name='description' content='".strip_tags($_fs_desc_output)."'/>";
	$txt.="\n";
	} else {
	if($parameter[4] != ''){
	$txt.="<meta name='description' content='".strip_tags(flodjiShareNormDesc($parameter[4]))."'/>";
	$txt.="\n";
	}
	}
	}
	if($option['active_buttons']['twittercards']==true){
	$txt.='<meta name="twitter:card" content="summary">';
	$txt.="\n";
	if($option['twitsite'] != ''){
	$txt.='<meta name="twitter:site" content="@'.stripslashes($option['twitsite']).'">';
	$txt.="\n";
	}
	if(get_the_author_meta('fstwittername')!=''){
	$txt.='<meta name="twitter:creator" content="@'.stripslashes(get_the_author_meta('fstwittername')).'">';
	$txt.="\n";
	} else {
	if($option['twituser'] != ''){
	$txt.='<meta name="twitter:creator" content="@'.stripslashes($option['twitter_text']).'">';
	$txt.="\n";
	}
	}
	$txt.='<meta name="twitter:url" content="'.$parameter[1].'">';
	$txt.="\n";
	if($_fs_title_output != ''){
	$txt.='<meta name="twitter:title" content="'.strip_tags($_fs_title_output).'">';
	} else {
	$txt.='<meta name="twitter:title" content="'.$parameter[0].'">';
	}
	$txt.="\n";
	if($_fs_desc_output != ''){
	$txt.='<meta name="twitter:description" content="'.strip_tags($_fs_desc_output).'">';
	$txt.="\n";
	} else {
	if($parameter[4] != ''){
	$txt.='<meta name="twitter:description" content="'.strip_tags($parameter[4]).'">';
	$txt.="\n";
	}
	}
	if($_fs_image_output != ''){
	$txt.='<meta name="twitter:image" content="'.strip_tags($_fs_image_output).'">';
	$txt.="\n";
	} else {
	if($parameter[2] != ''){
	$txt.='<meta name="twitter:image" content="'.$parameter[2].'">';
	$txt.="\n";
	}
	}
	}
	return $txt;
}

function flodjiShareFirstImage(){
global $post;
if(has_post_thumbnail()){
$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), '200' );
$image = $thumb['0'];
} else {
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
$Html = get_the_content();
$extrae = '/<img .*src=["\']([^ ^"^\']*)["\']/';
preg_match_all( $extrae  , $Html , $matches );
$image = $matches[1][0];
}
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

function flodjishare_metabox() {
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if($option['active_buttons']['metabox']){
add_meta_box( 'flodjishare_meta', 'flodjiShare', 'flodjishare_meta_data', 'post', 'normal', 'high' );
add_meta_box( 'flodjishare_meta', 'flodjiShare', 'flodjishare_meta_data', 'page', 'normal', 'high' );
$args=array(
  'public'   => true,
  '_builtin' => false
); 
$output = 'object';
$operator = 'and';
$post_types=get_post_types($args,$output,$operator); 
foreach ($post_types  as $post_type ) {
add_meta_box( 'flodjishare_meta', 'flodjiShare', 'flodjishare_meta_data', $post_type->name, 'normal', 'high' );
}
}
}
add_action( 'add_meta_boxes', 'flodjishare_metabox' );

function flodjishare_meta_data($post){
global $post;
$values = get_post_custom( $post->ID );
extract( $values, EXTR_SKIP );
wp_nonce_field( 'flodjishare_meta_data_action', 'flodjishare_meta_data_nonce' );
?>
<style type="text/css">
label.flodjishare{
display: inline-block; 
width: 150px;
}
input.flodjishare{
display: inline-block; 
width: 300px;
}
</style>
<p>Aus diesen Daten werden die Meta-Tags f&uuml;r <strong>Facebook</strong> <small><a target="_blank" href="http://ogp.me/"><u>(Opengraph)</u></a></small>, <strong>Twitter</strong> <small><a target="_blank" href="https://dev.twitter.com/docs/cards"><u>(Twitter Cards)</u></a></small>, <strong>Google (Google+) und Suchergebnisse</strong> z.B. bei Google, Yahoo oder Bing <small><a target="_blank" href="https://support.google.com/webmasters/answer/99170?hl=de"><u>(Rich Snippets)</u></a></small> erzeugt. Werden hier keine Daten eingetragen, dann werden der Beitrags/Seitentitel, -auszug und die URL des Post-Thumbnails bzw. des ersten Bildes verwendet.</p>
    <p>
        <label class="flodjishare" for="fs_title">&Uuml;berschrift:</label>
        <input class="flodjishare" type="flodjisharebox_breite" name="_flodjisharebox_fs_title" id="fs_title" value="<?php echo $_flodjisharebox_fs_title[0]; ?>" /><small>Max. 70 Zeichen</small>
    </p>
    <p>
        <label class="flodjishare" for="fs_desc">Beschreibung:</label>
        <input class="flodjishare" type="flodjisharebox_breite" name="_flodjisharebox_fs_desc" id="fs_desc" value="<?php echo $_flodjisharebox_fs_desc[0]; ?>" /><small>Max. 140 Zeichen</small>
    </p>
    <p>
        <label class="flodjishare" for="fs_image">Bild-URL:</label>
        <input class="flodjishare" type="flodjisharebox_breite" name="_flodjisharebox_fs_image" id="fs_image" value="<?php echo $_flodjisharebox_fs_image[0]; ?>" />
    </p>
	<p><strong>So k&ouml;nnten Deine Suchergebnisse aktuell etwa aussehen:</strong> <small>(Beitrag/Seite speichern um zu aktualisieren)</small></p>
	<p style="max-width:512px;background-color:white;border:thin solid #ccc;padding:3px;"><span style="color:#2518b5;text-decoration:underline;"><?php if($_flodjisharebox_fs_title[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_title[0], 70)); } else { echo strip_tags(flodjiShareShortText(get_the_title(), 70)); } ?></span><br />
	<span style="color:green"><?php echo get_permalink(); ?></span><br />
	<?php if($_flodjisharebox_fs_desc[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_desc[0], 140)); } else { echo strip_tags(flodjiShareShortText(get_the_excerpt(), 140)); } ?></p>
	<p><strong>So k&ouml;nnte dieser Beitrag beim Teilen auf Facebook, Twitter und Google + aussehen:</strong> <small>(Beitrag/Seite speichern um zu aktualisieren)</small></p>
	<?php if($_flodjisharebox_fs_image[0] != ''){ ?><img style="float:left;width:150px;margin-right:3px;" src="<?php echo $_flodjisharebox_fs_image[0]; ?>" /><?php } ?><p style="max-width:512px;background-color:white;border:thin solid #ccc;min-height:100px;"><span style="color:#2518b5;text-decoration:underline;"><?php if($_flodjisharebox_fs_title[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_title[0], 70)); } else { echo strip_tags(flodjiShareShortText(get_the_title(), 70)); } ?></span><br />
	<span style="color:green"><?php echo get_permalink(); ?></span><br />
	<?php if($_flodjisharebox_fs_desc[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_desc[0], 140)); } else { echo strip_tags(flodjiShareShortText(get_the_excerpt(), 140)); } ?></p>
<?php
}

function flodjishare_meta_box_save_meta($post_id){
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if( !isset( $_POST['flodjishare_meta_data_nonce'] ) || !wp_verify_nonce( $_POST['flodjishare_meta_data_nonce'], 'flodjishare_meta_data_action' ) ) return;
    if( !current_user_can( 'edit_post' ) ) return;
    $fs_meta_array = array(
        '_flodjisharebox_fs_title',
        '_flodjisharebox_fs_desc',
        '_flodjisharebox_fs_image'        
    );
    $fs_meta_defaults = array(
        '_flodjisharebox_fs_title' => 'None',
        '_flodjisharebox_fs_desc' => 'None',
        '_flodjisharebox_fs_image' => 'None'
    );
	$fs_meta_array = wp_parse_args($fs_meta_array, $fs_meta_array_defaults);
    $allowed_html = array(
    'a' => array(
        'href' => array(),
        'title' => array()
    ),
    'br' => array(),
    'em' => array(),
    'strong' => array()
);
    foreach($fs_meta_array as $item) {
        if( isset( $_POST[$item] ) )
            update_post_meta( $post_id, $item, wp_kses($_POST[$item], $allowed_html) );
    }
}
add_action( 'save_post', 'flodjishare_meta_box_save_meta' );

function flodjishare_options(){
	$option_name = 'flodjishare';
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if( isset($_POST['flodjishare_position'])) {
		$option = array();
		$option['active_buttons'] = array('facebook'=>false, 'twitter'=>false, 'digg'=>false, 'delicious'=>false, 'vz'=>false, 'xing'=>false, 'gplus'=>false, 'linkedin'=>false, 'pinterest'=>false, 'stumbleupon'=>false, 'tumblr'=>false, 'flattr'=>false, 'opengraph'=>false, 'richsnippets'=>false, 'twittercards'=>false, 'metabox'=>false, 'metadesc'=>false, 'metro'=>false, 'counter'=>false, 'supportlink'=>false, 'privacy'=>false);
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
		if ($_POST['flodjishare_active_flattr']=='on') { $option['active_buttons']['flattr'] = true; }
		if ($_POST['flodjishare_active_metro']=='on') { $option['metro'] = true; }
		if ($_POST['flodjishare_active_counter']=='on') { $option['counter'] = true; }
		if ($_POST['flodjishare_active_opengraph']=='on') { $option['active_buttons']['opengraph'] = true; }
		if ($_POST['flodjishare_active_richsnippets']=='on') { $option['active_buttons']['richsnippets'] = true; }
		if ($_POST['flodjishare_active_twittercards']=='on') { $option['active_buttons']['twittercards'] = true; }
		if ($_POST['flodjishare_active_metabox']=='on') { $option['active_buttons']['metabox'] = true; }
		if ($_POST['flodjishare_active_metadesc']=='on') { $option['active_buttons']['metadesc'] = true; }
		if ($_POST['flodjishare_active_googleAuthor']=='on') { $option['active_buttons']['gplusAuthor'] = true; }
		if ($_POST['flodjishare_active_privacy']=='on') { $option['privacy'] = true; }
		if ($_POST['flodjishare_active_supportlink']=='on') { $option['supportlink'] = true; }
		$option['position'] = esc_html($_POST['flodjishare_position']);
		$option['skip_single'] = esc_html($_POST['flodjishare_skip_single']);
		$option['skip_page'] = esc_html($_POST['flodjishare_skip_page']);
		$option['skip_cat'] = esc_html($_POST['flodjishare_skip_cat']);
		$option['intro_text'] = esc_html($_POST['flodjishare_intro_text']);
		$option['twitter_text'] = esc_html($_POST['flodjishare_twitter_text']);
		$option['flattr_id'] = esc_html($_POST['flodjishare_flattr_id']);
		$option['gplusidpage'] = esc_html($_POST['flodjishare_gplus_page']);
		$option['gplusiduser'] = esc_html($_POST['flodjishare_gplus_user']);
		$option['fb_app_id'] = esc_html($_POST['flodjishare_fb_app_id']);
		$option['fb_admin'] = esc_html($_POST['flodjishare_fb_admin']);
		$option['privacy_text'] = esc_html($_POST['flodjishare_privacy_text']);
		$option['show_in'] = array('posts'=>false, 'pages'=>false, 'home'=>false, 'category'=>false, 'search'=>false, 'archive'=>false);
		$option['altimg'] = esc_html($_POST['altimg']);
		$option['twitsite'] = esc_html($_POST['twitsite']);
		if ($_POST['flodjishare_show_posts']=='on') { $option['show_in']['posts'] = true; }
		if ($_POST['flodjishare_show_pages']=='on') { $option['show_in']['pages'] = true; }
		if ($_POST['flodjishare_show_home']=='on') { $option['show_in']['home'] = true; }
		if ($_POST['flodjishare_show_category']=='on') { $option['show_in']['category'] = true; }
		if ($_POST['flodjishare_show_search']=='on') { $option['show_in']['search'] = true; }
		if ($_POST['flodjishare_show_archive']=='on') { $option['show_in']['archive'] = true; }
		$args=array('public' => true,'_builtin' => false); 
		$output = 'object';
		$operator = 'and';
		$post_types=get_post_types($args,$output,$operator);
		foreach ($post_types  as $post_type ){
		if ($_POST['flodjishare_show_'.$post_type->name]=='on') { $option[$post_type->name] = true; }
		}
		update_option($option_name, json_encode($option));
		$outputa .= '<div class="updated"><p><strong>'.__('Einstellungen gespeichert.', 'flodjishare' ).'</strong></p></div>';
	}
	$option = array();
	$option_string = get_option($option_name);
	if ($option_string===false) {
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'flattr'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true, 'metabox'=>true, 'metadesc'=>true, 'gplusAthor'=>true, 'metro'=>true, 'counter'=>true, 'supportlink'=>true, 'privacy'=>true);
		$option['position'] = 'unter';
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true, 'category'=>true, 'search'=>true, 'archive'=>true);
		$option['skip_single'] = array('skip_single'=>true);
		$option['skip_page'] = array('skip_page'=>true);
		$option['skip_cat'] = array('skip_cat'=>true);
		$option['intro_text'] = array('intro_text'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['flattr_id'] = array('flattr_id'=>true);
		$option['gplusidpage'] = array('gplusidpage'=>true);
		$option['gplusiduser'] = array('gplusiduser'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['privacy_text'] = array('privacy_text'=>true);
		$option['altimg'] = array('altimg'=>true);
		$option['twitsite'] = array('twitsite'=>true);
		$args=array('public' => true,'_builtin' => false); 
		$output = 'object';
		$operator = 'and';
		$post_types=get_post_types($args,$output,$operator);
		foreach ($post_types  as $post_type ){
		$option[$post_type->name] = array($post_type->name=>true);
		}
		add_option($option_name, 'unter');
		$option_string = get_option($option_name);
	}
	if ($option_string=='ueber' or $option_string=='unter' or $option_string=='both' or $option_string=='shortcode') {
		$flodjishare_options = explode('|||',$option_string);
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'vz'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'flattr'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true, 'metabox'=>true, 'metadesc'=>true, 'gplusAuthor'=>true, 'metro'=>true, 'counter'=>true, 'supportlink'=>true, 'privacy'=>true);
		$option['position'] = $flodjishare_options[0];
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true, 'category'=>true, 'search'=>true, 'archive'=>true);
		$option['skip_single'] = array('skip_single'=>true);
		$option['skip_page'] = array('skip_page'=>true);
		$option['skip_cat'] = array('skip_cat'=>true);
		$option['intro_text'] = array('intro_text'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['flattr_id'] = array('flattr_id'=>true);
		$option['gplusidpage'] = array('gplusidpage'=>true);
		$option['gplusiduser'] = array('gplusiduser'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['privacy_text'] = array('privacy_text'=>true);
		$option['altimg'] = array('altimg'=>true);
		$option['twitsite'] = array('twitsite'=>true);
	} else {
		$option = json_decode($option_string, true);
	}
	$sel_above 			= ($option['position']=='ueber') ? 'selected="selected"' : '';
	$sel_below 			= ($option['position']=='unter') ? 'selected="selected"' : '';
	$sel_both			= ($option['position']=='both') ? 'selected="selected"' : '';
	$sel_short 			= ($option['position']=='shortcode') ? 'selected="selected"' : '';
	$skip_single		= ($option['skip_single']=='') ? 'selected="selected"' : '';
	$skip_page			= ($option['skip_page']=='') ? 'selected="selected"' : '';
	$skip_cat			= ($option['skip_cat']=='') ? 'selected="selected"' : '';
	$active_facebook 	= ($option['active_buttons']['facebook']==true) ? 'checked="checked"' : '';
	$active_twitter  	= ($option['active_buttons']['twitter'] ==true) ? 'checked="checked"' : '';
	$active_digg		= ($option['active_buttons']['digg']==true) ? 'checked="checked"' : '';
	$active_delicious	= ($option['active_buttons']['delicious']==true) ? 'checked="checked"' : '';
	$active_vz			= ($option['active_buttons']['vz']==true) ? 'checked="checked"' : '';
	$active_xing		= ($option['active_buttons']['xing']==true) ? 'checked="checked"' : '';
	$active_flattr		= ($option['active_buttons']['flattr']==true) ? 'checked="checked"' : '';
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
	$active_metabox		= ($option['active_buttons']['metabox']==true) ? 'checked="checked"' : '';
	$active_metadesc	= ($option['active_buttons']['metadesc']==true) ? 'checked="checked"' : '';
	$active_gplusauthor	= ($option['active_buttons']['gplusAuthor']==true) ? 'checked="checked"' : '';
	$active_privacy		= ($option['privacy']==true) ? 'checked="checked"' : '';
	$active_supportlink	= ($option['supportlink']==true) ? 'checked="checked"' : '';
	$show_in_posts 		= ($option['show_in']['posts']==true) ? 'checked="checked"' : '';
	$show_in_pages 		= ($option['show_in']['pages'] ==true) ? 'checked="checked"' : '';
	$show_in_home 		= ($option['show_in']['home'] ==true) ? 'checked="checked"' : '';
	$show_in_category	= ($option['show_in']['category'] ==true) ? 'checked="checked"' : '';
	$show_in_search		= ($option['show_in']['search'] ==true) ? 'checked="checked"' : '';
	$show_in_archive	= ($option['show_in']['archive'] ==true) ? 'checked="checked"' : '';
	$intro_text			= ($option['intro_text']=='') ? 'selected="selected"' : '';
	$twitter_text		= ($option['twitter_text']=='') ? 'selected="selected"' : '';
	$flattr_id			= ($option['flattr_id']=='') ? 'selected="selected"' : '';
	$gplusidpage		= ($option['gplusidpage']=='') ? 'selected="selected"' : '';
	$gplusiduser		= ($option['gplusiduser']=='') ? 'selected="selected"' : '';
	$fb_app_id			= ($option['fb_app_id']=='') ? 'selected="selected"' : '';
	$fb_admin			= ($option['fb_admin']=='') ? 'selected="selected"' : '';
	$privacy_text		= ($option['privacy_text']=='') ? 'selected="selected"' : '';
	$altimg				= ($option['altimg']=='') ? 'selected="selected"' : '';
	$twitsite			= ($option['twitsite']=='') ? 'selected="selected"' : '';
	$args=array('public' => true,'_builtin' => false); 
	$output = 'object';
	$operator = 'and';
	$post_types=get_post_types($args,$output,$operator);
	foreach ($post_types  as $post_type ){
	$checked[$post_type->name]	= ($option[$post_type->name]==true) ? 'checked="checked"' : '';
	}
	$outputa .= '
	<div style="width:400px;float:left;">
		<h2>flodjiShare Einstellungen</h2>
	<table><tr><td style="width:200px;">'.followMeFlodjiShare().'</td><td>'.spendPayPalFlodjiShare().'</td></tr></table><br />
		<form name="form1" method="post" action="">
		<table>
		<tr><td valign="top"><strong>'.__("flodjiShare hier aktivieren", 'flodjishare' ).':</strong></td>
		<td>'
		.' <input type="checkbox" name="flodjishare_show_posts" '.$show_in_posts.'> '
		. __("Einzelne Beitr&auml;ge", 'flodjishare' ).'<br />'
		.' <input type="checkbox" name="flodjishare_show_pages" '.$show_in_pages.'> '
		. __("Seiten", 'flodjishare' ).'<br />'
		.' <input type="checkbox" name="flodjishare_show_home" '.$show_in_home.'> '
		. __("Startseite", 'flodjishare' ).'<br />';
		$args=array('public' => true,'_builtin' => false); 
		$output = 'object';
		$operator = 'and';
		$post_types=get_post_types($args,$output,$operator);
		if(!$post_types){
		$outputa .= 'Keine Custom-Post-Types vorhanden.<br /><small><a target="_blank" href="http://codex.wordpress.org/Post_Types">Was ist das? (engl.)</a></small>';
		} else {
		foreach ($post_types  as $post_type ){
		$outputa .= ' <input type="checkbox" name="flodjishare_show_'.$post_type->name.'" '.$checked[$post_type->name].'> '
		.$post_type->name.' &nbsp;&nbsp;';
		}
		}
		$outputa .= '<br />
		<input type="checkbox" name="flodjishare_show_category" '.$show_in_category.'> ' 
		. __("Kategorien", 'flodjishare' ).'<br />
		<input type="checkbox" name="flodjishare_show_search" '.$show_in_search.'> ' 
		. __("Suchergebnisse", 'flodjishare' ).'<br />
		<input type="checkbox" name="flodjishare_show_archive" '.$show_in_archive.'> ' 
		. __("Archive", 'flodjishare' ).'<br /><br /></td></tr>
		
		<tr><td valign="top"><strong>'.__("Beitr&auml;ge ausschlie&szlig;en", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_skip_single" value="'.$option['skip_single'].'" size="50"><br />
		<span class="description">'.__("Trage hier die IDs der Beitr&auml;ge / Custom Post Types ein, in denen keine Share Buttons angezeigt und keine Meta Tags genereiert  werden sollen. (Mehrere durch Komma getrennt.)<br />", 'flodjishare' ).'</span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Seiten ausschlie&szlig;en", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_skip_page" value="'.$option['skip_page'].'" size="50"><br />
		<span class="description">'.__("Trage hier die IDs der Seiten ein, in denen keine Share Buttons angezeigt und keine Meta Tags genereiert  werden sollen. (Mehrere durch Komma getrennt.)<br />", 'flodjishare' ).'</span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Kategorien ausschlie&szlig;en", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_skip_cat" value="'.$option['skip_cat'].'" size="50"><br />
		<span class="description">'.__("Trage hier die IDs der Kategorien ein, in deren &Uuml;bersicht keine Share Buttons angezeigt und keine Meta Tags genereiert werden sollen. (Mehrere durch Komma getrennt.)<br />", 'flodjishare' ).'</span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("flodjiShare Buttons", 'flodjishare' ).':</strong></td>
		<td>'
		.' <input type="checkbox" name="flodjishare_active_facebook" '.$active_facebook.'> '
		. __("Facebook Share", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_twitter" '.$active_twitter.'> '
		. __("Twitter", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_digg" '.$active_digg.'> '
		. __("Digg", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_delicious" '.$active_delicious.'> '
		. __("Delicious", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_vz" '.$active_vz.'> '
		. __("VZ-Netzwerke", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_xing" '.$active_xing.'> '
		. __("Xing", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_gplus" '.$active_gplus.'> '
		. __("Google Plus", 'flodjishare' ).' &nbsp;&nbsp;<br />'		
		.' <input type="checkbox" name="flodjishare_active_linkedin" '.$active_linkedin.'> '
		. __("LinkedIn", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_pinterest" '.$active_pinterest.'> '
		. __("Pinterest", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_stumbleupon" '.$active_stumbleupon.'> '
		. __("Stumbleupon", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_flattr" '.$active_flattr.'> '
		. __("Flattr", 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_tumblr" '.$active_tumblr.'> '
		. __("Tumblr", 'flodjishare' ).' &nbsp;&nbsp;<br />'	
		.'<br /></td></tr>
		<tr><td valign="top"><strong>'.__("Position", 'flodjishare' ).':</strong></td>
		<td><select name="flodjishare_position">
			<option value="ueber" '.$sel_above.' > '.__('&Uuml;ber dem Beitrag', 'flodjishare' ).'</option>
			<option value="unter" '.$sel_below.' > '.__('Unter dem Beitrag', 'flodjishare' ).'</option>
			<option value="both" '.$sel_both.' > '.__('Beides', 'flodjishare' ).'</option>
			<option value="shortcode" '.$sel_short.' > '.__('Nur bei Shortcode [flodjishare]', 'flodjishare' ).'</option>
			</select><br /> 
		<br /></td></tr>
		
		<tr><td valign="top"><strong>'.__("Design", 'flodjishare' ).':</strong></td>
		<td><input type="checkbox" name="flodjishare_active_metro" '.$active_metro.'> '
		. __("Metro Design aktivieren", 'flodjishare' ).'<br /><input type="checkbox" name="flodjishare_active_counter" '.$active_counter.'> '
		. __("Klickz&auml;hler aktivieren (nur Metro Design)", 'flodjishare' ).'&nbsp;&nbsp;<br /><br /></td></tr>
		
		<tr><td valign="top"><strong>'.__("&Uuml;berschrift", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_intro_text" value="'.stripslashes($option['intro_text']).'" size="50"><br />
		<span class="description">'.__("Dieser Text steht sp&auml;ter &uuml;ber den Share Buttons (z.B. Diesen Beitrag teilen...).<br />", 'flodjishare' ).'</span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Extras", 'flodjishare' ).':</strong></td>
		<td><input type="checkbox" name="flodjishare_active_opengraph" '.$active_opengraph.'> '
		. __("Opengraph Support", 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Diese Tags werden z.B. von Facebook zum Teilen von Beitr&auml;gen ausgelesen.", 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_richsnippets" '.$active_richsnippets.'> '
		. __("Rich Snippets Support", 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Diese Tags werden von Suchmaschinen zum Indexieren und z.B. von Google Plus zum Teilen von Beitr&auml;gen ausgelesen.", 'flodjishare' ).'</small></span><br />

		<input type="checkbox" name="flodjishare_active_twittercards" '.$active_twittercards.'> '
		. __("Twitter Card Support", 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Diese Tags werden von Twitter zum Teilen von Beitr&auml;gen ausgelesen.", 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_metabox" '.$active_metabox.'> '
		. __("Metaboxen aktivieren", 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Im Editor wird eine Metabox zum Anpassen der Opengraph, Rich Snippets und Twitter Cards Meta Tags angezeigt.", 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_metadesc" '.$active_metadesc.'> '
		. __("Meta Description Tag aktivieren", 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Aktiviert das Meta Description Tag.", 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_privacy" '.$active_privacy.'> '
		. __("Datenschutzhinweis anzeigen", 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Zeigt einen kleinen Hoverlink mit Datenschutzhinweisen unter den Share Buttons an. Der Hinweistext muss weiter unten noch eingegeben werden.", 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_googleAuthor" '.$active_gplusauthor.'> '
		. __("Google Authorship Markup", 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("F&uuml;gt das Google Authorship Markup Tag f&uuml;r die Anzeige von Autorenfotos in den Suchergebnissen in den Quellcode ein.", 'flodjishare' ).'</small></span><br />
		
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Google Plus Page ID", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_gplus_page" value="'.stripslashes($option['gplusidpage']).'" size="50"><br />
		<span class="description">'.__("Trage hier die ID Deiner Google Plus Seite ein.", 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Google Plus User ID", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_gplus_user" value="'.stripslashes($option['gplusiduser']).'" size="50"><br />
		<span class="description">'.__("Trage hier die ID Deines pers&ouml;nlichen Google Plus Accounts ein.", 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Flattr ID", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_flattr_id" value="'.stripslashes($option['flattr_id']).'" size="50"><br />
		<span class="description">'.__("Trage hier Deine Flattr ID ein. Diese wird f&uuml;r den Flattr Button ben&ouml;tigt.", 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Twitter Name", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_twitter_text" value="'.stripslashes($option['twitter_text']).'" size="50"><br />
		<span class="description">'.__("Trage hier Deinen Twitter Usernamen ein. Dieser wird dann in den Twitter Cards (wenn aktiviert)<br />und am Ende der Tweets erscheinen, z.B. (via @Dein Twitter Name).", 'flodjishare' ).'</span><br />
		</td></tr>

		<tr><td valign="top"><strong>'.__("Twitter Seite", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="twitsite" value="'.stripslashes($option['twitsite']).'" size="50"><br />
		<span class="description">'.__("Trage hier den Twitter Usernamen Deiner Worspress Seite ein. Falls nicht vorhanden, trage einfach Deinen Twitter Usernamen ein.", 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Ersatzbild", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="altimg" value="'.stripslashes($option['altimg']).'" size="50"><br />
		<span class="description">'.__("Trage hier den Link zu einem Ersatzbild ein. Dieses wird beim Teilen verwendet, wenn im Artikel kein Bild vorhanden ist.", 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Facebook AppId", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_fb_app_id" value="'.stripslashes($option['fb_app_id']).'" size="50"><br />
		<span class="description">'.__("Trage hier Deine Facebook AppId ein.", 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Facebook Admin", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="text" name="flodjishare_fb_admin" value="'.stripslashes($option['fb_admin']).'" size="50"><br />
		<span class="description">'.__("Trage hier Deinen Facebook Usernamen ein.", 'flodjishare' ).'</span><br />
		</td></tr>		
			
		<tr><td valign="top"><strong>'.__("Datenschutzhinweistext", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<textarea name="flodjishare_privacy_text" value="'.stripslashes($option['privacy_text']).'" cols="50" rows="5">'.stripslashes($option['privacy_text']).'</textarea><br />
		<span class="description">'.__("Trage hier den Datenschutzhinweistext ein.", 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__("Supportlink", 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="checkbox" name="flodjishare_active_supportlink" '.$active_supportlink.'> '
		. __("Supportlink deaktivieren", 'flodjishare' ).'&nbsp;&nbsp;<br /></td></tr>
		
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
<a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=flodji.de_Logo&utm_campaign=flodjiShareWP"><img src="'.home_url().'/wp-content/plugins/flodjishare/buttons/flodjidelogo03.gif" width="180"/></a><h2>flodji.de Feed</h2>';
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