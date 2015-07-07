<?php
/*
Plugin Name: flodjiShare
Plugin URI: http://flodji.de/downloads/flodjishare-fuer-wordpress/
Description: Mit flodjiShare wird Webseitenbetreibern eine einfache L&ouml;sung angeboten die Social Sharing und Bookmark Buttons der gro&szlig;en Netzwerke in die eigene Seite einzubinden.
Version: 4.0
Author: flodji
Author URI: http://flodji.de
License: GPL2
*/
function flodjiShareLocalize(){
load_plugin_textdomain('flodjishare', false, dirname(plugin_basename(__FILE__ ) ) . '/languages' );
}
add_action('init', 'flodjiShareLocalize');
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
if(!is_admin()){
wp_enqueue_style( 'flodjishare', plugins_url( 'flodjishare/flodjishare.css' ) );
}
}
add_action( 'wp_enqueue_scripts', 'flodjiShareStyle' );

add_action( 'admin_menu', 'flodjiShareMenu' );
function flodjiShareMenu(){
if(is_admin()){
add_menu_page( 'flodjiShare Einstellungen', 'flodjiShare', 'manage_options', 'flodjishare_einstellungen', 'flodjishare_options', plugins_url( 'flodjishare/buttons/fs_ico.png' ), 999 );
add_submenu_page( 'flodjishare_einstellungen', 'flodjiShare Klick Counter', 'flodjiShare Klick Counter', 'manage_options', 'klick-counter', 'flodjiShareKlickCounter' );
}
}

if($option['fs_hit_column']){
add_filter('manage_posts_columns', 'fs_add_post_hits_column', 5);
add_filter('manage_pages_columns', 'fs_add_post_hits_column', 5);
}

function fs_add_post_hits_column($cols){
$cols['fs_post_views_count'] = __('Hits');
return $cols;
}

if($option['fs_hit_column']){
add_action('manage_posts_custom_column', 'fs_display_post_hits_column', 5, 2);
add_action('manage_pages_custom_column', 'fs_display_post_hits_column', 5, 2);
}

function fs_display_post_hits_column($col, $id){
global $wpdb,$post;
switch($col){
case 'fs_post_views_count':
echo get_post_meta( $id, 'fs_post_views_count', true );
break;
}
}

function fs_sortable_columns(){
return array('fs_post_views_count' => 'fs_post_views_count');
}

if($option['fs_hit_column']){
add_filter( 'manage_edit-post_sortable_columns', 'fs_sortable_columns' );
add_filter( 'manage_edit-page_sortable_columns', 'fs_sortable_columns' );
}

function fs_sort_hits_column( $vars ){
if ( isset( $vars['orderby'] ) && 'fs_post_views_count' == $vars['orderby'] ) {
$vars = array_merge( $vars, array('meta_key' => 'fs_post_views_count', 'orderby' => 'meta_value_num') );
}
return $vars;
}

if($option['fs_hit_column']){
add_filter( 'request', 'fs_sort_hits_column' );
}

function flodjiShareAddContactMethods($contactmethods){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if($option['active_buttons']['gplusAuthor']){
$contactmethods['gplusiduser'] = __('Google Plus ID', 'flodjishare');
$contactmethods['fstwittername'] = __('Twitter Name', 'flodjishare');
return $contactmethods;
}
}
add_filter('user_contactmethods','flodjiShareAddContactMethods',10,1);

function addingStyleFShare(){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
$height = $option['height'];
if(empty($height)){
$height = '32';
}
$style = '<style type="text/css">
.fsspanflatmix {
  height: '.$height.'px ! important;
}
</style>';
echo $style;
}
add_action('wp_footer', 'addingStyleFShare');

function flodjiShareAutor(){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if($option['active_buttons']['gplusAuthor']){
if($option['gplusidpage']){
if(is_array($option['gplusidpage'])){ $option_gplusidpage = ''; } else { $option_gplusidpage = $option['gplusidpage']; }
echo '<link href="https://plus.google.com/'.stripslashes($option_gplusidpage).'/" rel="publisher" />';
echo "\n";
}
if(($option['gplusiduser']) or (get_the_author_meta('gplusiduser') != '')){
if(is_single()){
$user = get_the_author_meta('gplusiduser');
if($user == ''){
echo '<link href="https://plus.google.com/'.stripslashes($option['gplusiduser']).'" rel="author" />';
echo "\n";
} else {
echo '<link href="https://plus.google.com/'.stripslashes(get_the_author_meta('gplusiduser')).'" rel="author" />';
echo "\n";
}
}
}
}
}
if(!is_admin()){
add_action('wp_head', 'flodjiShareAutor');
}

function flodjiShareNormDesc( $title ){
$slug = $title;
$bad = array('/%/','/#/','/&/','/=/','/\(/','/\)/','/\+/','/Ä/','/Ü/','/Ö/','/ä/','/ü/','/ö/','/ß/','/\"/','/\'/','/</','/>/');
$good = array('%25','%23','%26','%3D','%28', '%29', '%2B', '%C4','%DC','%D6','%E4','%FC','%F6','%DF','%22', '%27', '%3C','%3E');
$slug = str_replace( $bad, $good, $slug );
$slug = trim($slug);
return $slug;
}

function flodjiShareNormTitle( $string ){
$string = str_replace("ä", "ae", $string);
$string = str_replace("ü", "ue", $string);
$string = str_replace("ö", "oe", $string);
$string = str_replace("Ä", "Ae", $string);
$string = str_replace("Ü", "Ue", $string);
$string = str_replace("Ö", "Oe", $string);
$string = str_replace("ß", "ss", $string);
$string = str_replace("'", "", $string);
return $string;
}

function descExcerpt(){
global $post;
$seoDesc = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
if(!empty($seoDesc)){
$excerpt = strip_tags($seoDesc);
} elseif($post->post_excerpt) {
$excerpt = strip_tags(get_the_excerpt());
} else {
$excerpt = flodjiShareShortText(strip_tags(get_the_content()),160);
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
if($n>1000000) return round(($n/1000000),1).'M';
else if($n>1000) return round(($n/1000),1).'K';
return number_format($n);
}

function deregisterFsStyle(){
wp_deregister_style( 'flodjishare' );
}

function popUpScript(){
$popUpScript = '<script type="text/javascript">
		function popup (url) {
		fenster = window.open(url, "Popupfenster", "width=530,height=400,resizable=yes");
		fenster.focus();
		return false;
		}
		</script>';
echo $popUpScript;
}
add_action('wp_footer', 'popUpScript');

function fsTitle(){
global $post;

$rawTitle = get_post_field('post_title', get_the_ID(), 'raw');
$seoTitle = get_post_meta(get_the_ID(), '_yoast_wpseo_title', true);
if(!empty($seoTitle)) {
$fsTitle = $seoTitle;
} elseif(!empty($rawTitle)){
$fsTitle = $rawTitle;
} else {
$fsTitle = get_the_title();
}
return strip_tags($fsTitle);
}

function flodjishare($content) {
global $wpdb, $post;
if(is_feed()){ return $content; }
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
	
if(is_single()){
if(!$option['show_in']['posts']){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}		
}

if(is_page()){
if(!$option['show_in']['pages']){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
}

if(is_home()){
if(!$option['show_in']['home']){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
}

if(is_category()){
if(!$option['show_in']['category']){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
}

if(is_search()){
if(!$option['show_in']['search']){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
}

if(is_archive()){
if(!$option['show_in']['archive']){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
}
	
$skipsingle = preg_split("/[\s,]+/", $option['skip_single']);
if(is_single($skipsingle)){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
	
$skippage = preg_split("/[\s,]+/", $option['skip_page']);
if(is_page($skippage)){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
	
if($option['skip_cat'] == ''){
} else {
$skippage = preg_split("/[\s,]+/", $option['skip_cat']);
if(is_category($skipcat)){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
}
$args = array( 'public'   => true, '_builtin' => false ); 
$output = 'object';
$operator = 'and';
$post_types=get_post_types($args,$output,$operator); 
foreach ($post_types  as $post_type ) {
if((!$option[$post_type->name]) && (get_post_type( get_the_ID() ) == $post_type->name)){
add_action( 'wp_print_styles', 'deregisterFsStyle', 100 );
return $content;
}
}
$outputa = '';

if ($option['align']==true){ $align = 'align="center" '; } else { $align = ''; }
$outputa .= '<div class="fsmain" '.$align.'>';
if($option['intro_height'] != ''){
$intro_height = stripslashes($option['privacy_text']);
} else {
$intro_height = '2rem';
}
if($option['intro_text'] != ''){
$intro_text = stripslashes($option['intro_text']);
} else {
$intro_text = '';
}
$outputa .= '<p style="font-size:'.$intro_height.';font-weight:700;">'.$intro_text.'</p>';

$outputa .= fsFacebook(fsTitle(), get_permalink());
$outputa .= fsTwitter(fsTitle(), get_permalink());
$outputa .= fsFlattr(fsTitle(), get_permalink(), descExcerpt());
$outputa .= fsDigg(fsTitle(), get_permalink());
$outputa .= fsDelicious(fsTitle(), get_permalink(), descExcerpt());
$outputa .= fsGplus(fsTitle(), get_permalink());
$outputa .= fsXing(fsTitle(), get_permalink());
$outputa .= fsLinkedIn(fsTitle(), get_permalink(), descExcerpt());	
$outputa .= fsPinterest(fsTitle(), get_permalink(), descExcerpt());
$outputa .= fsStumbleUpon(fsTitle(), get_permalink());
$outputa .= fsTumblr(fsTitle(), get_permalink(), descExcerpt());
$outputa .= fsWhatsapp(fsTitle(), get_permalink());
$outputa .= fsPocket(fsTitle(), get_permalink());
$outputa .= fsFeedly(fsTitle());
$outputa .= fsOwn1(get_permalink());
$outputa .= fsOwn2(get_permalink());
$outputa .= fsOwn3(get_permalink());
$outputa .= fsMobileBar(fsTitle(), get_permalink());
$outputa .= '</div><div class="fsclear"></div>';
$outputa .= fsPostViews(get_the_id(), fsTitle());
$outputa .= fsPrivacy();
$outputa .= fsSupportlink();
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

function fsSupportlink(){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['supportlink']==false) {
$output = '<span class="fssl">' . __('Social Sharing powered by', 'flodjishare') . ' <a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=SupportLink&utm_campaign=flodjiShareWP"><u>flodjiShare</u></a></span>';
return $output;
}
}

function fsPrivacy(){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['privacy']==true) {
if($option['privacy_text'] != ''){
$privacy_text = stripslashes($option['privacy_text']);
} else {
$privacy_text = '';
}
$output = '<span class="fsprivacy" title="'.$privacy_text.'"><u>' . __('Datenschutz Hinweis', 'flodjishare') . '</u></span>';
return $output;
}
}

function fsOwn1($link){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['own1']==true) {
if($option['own1content'] != ''){
$own1 = stripslashes($option['own1content']);
} else {
$own1 = '';
}
$own1 = str_replace("{link}", urlencode($link), $own1);
$output = htmlspecialchars_decode($own1);
return $output;
}
}

function fsOwn2($link){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['own2']==true) {
if($option['own2content'] != ''){
$own2 = stripslashes($option['own2content']);
} else {
$own2 = '';
}
$own2 = str_replace("{link}", urlencode($link), $own2);
$output = htmlspecialchars_decode($own2);
return $output;
}
}

function fsOwn3($link){
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['own3']==true) {
if($option['own3content'] != ''){
$own3 = stripslashes($option['own3content']);
} else {
$own3 = '';
}
$own3 = str_replace("{link}", urlencode($link), $own3);
$output = htmlspecialchars_decode($own3);
return $output;
}
}

function fsPostViews($id, $title){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['post_stats']==true){
$postviews = fs_get_post_views( $id );
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if ($option['counter']==true){
$db_title = flodjiShareNormTitle($title);
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$klicks = $wpdb->get_var("SELECT sum(klicks) FROM $dbprfx WHERE title='$db_title'");
if($klicks == ''){
$klicks = '0';
}
return '<div class="'.$div_class.'"><div class="fsstat">'.__('Statistik', 'flodjishare').':&nbsp;</div><div class="fsleft"><div class="fsviewcount">'.short_number($postviews).'</div><div class="fsaufrufe">Aufrufe</div></div><div class="fsdivdivider">|</div><div class="fsflma"><div class="fsviewcount">'.short_number($klicks).'</div><div class="fsaufrufe">'.__('Shares', 'flodjishare').'</div></div></div><div class="fsclear"></div>';
} else {
return '<div class="'.$div_class.'"><div class="fsstat">'.__('Statistik', 'flodjishare').':&nbsp;</div><div class="fsleft"><div class="fsviewcount">'.short_number($postviews).'</div><div class="fsaufrufe">Aufrufe</div></div></div><div class="fsclear"></div>';
}
}
}

function fsFacebook($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['facebook']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
$db_title = flodjiShareNormTitle($title);
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$network= __('Facebook', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode('http://www.facebook.com/sharer.php?u='.$link.'&amp;t='.urlencode($title)).'" onclick="return popup(this.href);" rel="nofollow"><img alt="' . $title . __(' auf Facebook teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Facebook32px.png"  /><span class="fsspanflatmix fsfb"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsfb" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode('http://www.facebook.com/sharer.php?u='.$link.'&amp;t='.urlencode($title)).'" onclick="return popup(this.href);" rel="nofollow"><strong>' . __('Facebook', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsfb" href="http://www.facebook.com/sharer.php?u='.urlencode($link).'&amp;t='.urlencode($title).'" onclick="return popup(this.href);" rel="nofollow"><strong>' . __('Facebook', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="http://www.facebook.com/sharer.php?u='.urlencode($link).'&amp;t='.urlencode($title).'" onclick="return popup(this.href);" rel="nofollow"><img alt="' . $title . __(' auf Facebook teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Facebook'.$size.'px.png"  /></a></div>'; 
}
return $output;
}
}

function fsTwitter($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['twitter']==true) {
if($option['twitter_text'] != ''){
$tw_link = 'https://twitter.com/share?url='.urlencode($link).'&via='.stripslashes($option['twitter_text']).'&text='.urlencode($title);
} else {
$tw_link = 'https://twitter.com/share?url='.urlencode($link).'&text='.urlencode($title);
}
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
$db_title = flodjiShareNormTitle($title);
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$network= __('Twitter', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){ $klicks = '0'; }
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($tw_link).'" onclick="return popup(this.href);" rel="nofollow"><img alt="' . $title . __(' auf Twitter teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Twitter32px.png"  /><span class="fsspanflatmix fstw"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fstw" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($tw_link).'" onclick="return popup(this.href);" rel="nofollow"><strong>' . __('Twitter', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fstw" href="'.$tw_link.'" onclick="return popup(this.href);" rel="nofollow"><strong>' . __('Twitter', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"> 
<a href="'.$tw_link.'" onclick="return popup(this.href);" rel="nofollow"><img alt="' . $title . __(' auf Twitter teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Twitter'.$size.'px.png"  /></a></div>';
}
return $output;
}
}

function fsFlattr($title, $link, $desc){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['flattr']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$db_title = flodjiShareNormTitle($title);
if($option['flattr_id'] != ''){
$flattr_id = stripslashes($option['flattr_id']);
} else {
$flattr_id = '';
}
$flattrurl = 'https://flattr.com/submit/auto?user_id='.$flattr_id.'&url='.urlencode($link).'&title='.urlencode($title).'&description='.urlencode($desc);
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$network= __('Flattr', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&db_title='.urlencode($title).'&fsurl='.urlencode($flattrurl).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' flattrn', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/flattr32.png"  /><span class="fsspanflatmix fsfl"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsfl" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&db_title='.urlencode($title).'&fsurl='.urlencode($flattrurl).'" target="_blank" rel="nofollow"><strong>' . __('Flattr', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsfl" href="'.$flattrurl.'" onclick="return popup(this.href);" rel="nofollow"><strong>' . __('Flattr', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$flattrurl.'" onclick="return popup(this.href);" rel="nofollow"><img alt="' . $title . __(' flattrn', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/flattr'.$size.'.png"  /></a></div>'; 
}
return $output;
}
}

function fsDigg($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['digg']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$digg_link = 'http://digg.com/submit?url='.$link.'&amp;title='.$title;
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Digg', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($digg_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Digg teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Digg32.png"  /><span class="fsspanflatmix fsdigg"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsdigg" target="_blank" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($digg_link).'" rel="nofollow"><strong>' . __('Digg', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsdigg" target="_blank" href="'.$digg_link.'" rel="nofollow"><strong>' . __('Digg', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"> 
<a target="_blank" href="'.$digg_link.'" rel="nofollow"><img alt="' . $title . __(' auf Digg teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Digg'.$size.'.png"  /></a></div>';
}
return $output;
}
}

function fsDelicious($title, $link, $desc){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['delicious']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$del_link =	'http://www.delicious.com/post?url='.urlencode($link).'&notes='.urlencode($desc).'&title='.urlencode($title);
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Delicious', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($del_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Delicious teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Delicious32px.png"  /><span class="fsspanflatmix fsdel"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsdel" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($del_link).'" target="_blank"><strong>' . __('Delicious', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsdel" href="'.$del_link.'" target="_blank"><strong>' . __('Delicious', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$del_link.'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Delicious teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Delicious'.$size.'px.png"  /></a></div>';
}
return $output;
}
}

function fsGplus($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['gplus']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Google Plus', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode('https://plus.google.com/share?url=' . urlencode($link)) . '" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Google Plus teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Google+32px.png"  /><span class="fsspanflatmix fsgp"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsgp" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode('https://plus.google.com/share?url=' . urlencode($link)) . '" onclick="return popup(this.href);" rel="nofollow"><strong>' . __('Google Plus', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsgp" href="https://plus.google.com/share?url=' . urlencode($link) . '" onclick="return popup(this.href);" rel="nofollow"><strong>' . __('Google Plus', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="https://plus.google.com/share?url=' . urlencode($link) . '" onclick="return popup(this.href);"><img alt="' . $title . __(' auf Google Plus teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Google+'.$size.'px.png"  /></a></div>';
}
return $output;
}
}

function fsXing($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['xing']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$xing_link = 'http://www.xing.com/app/user?op=share;url='.$link;
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Xing', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($xing_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Xing teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/XING32px.png"  /><span class="fsspanflatmix fsxi"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsxi" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($xing_link).'" target="_blank" rel="nofollow"><strong>' . __('Xing', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsxi" href="'.$xing_link.'" target="_blank" rel="nofollow"><strong>' . __('Xing', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$xing_link.'" target="_blank" title="Ihren XING-Kontakten zeigen" rel="nofollow"><img alt="' . $title . __(' auf Xing teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/XING'.$size.'px.png"  /></a></div>';
}
return $output;
}
}

function fsLinkedIn($title, $link, $desc){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['linkedin']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$linkedin_link = 'http://www.linkedin.com/shareArticle?mini=true&url='.urlencode($link).'&title='.urlencode($title).'&ro=false&summary='.urlencode($desc);
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('LinkedIn', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($linkedin_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf LinkedIn teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/LinkedIn32px.png"  /><span class="fsspanflatmix fsli"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsli" target="_blank" rel="nofollow" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($linkedin_link).'"><strong>' . __('LinkedIn', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsli" target="_blank" rel="nofollow" href="'.$linkedin_link.'"><strong>' . __('LinkedIn', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$linkedin_link.'" target="_blank" title="Ihren LinkedIn Kontakten zeigen" rel="nofollow"><img alt="' . $title . __(' auf LinkedIn teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/LinkedIn'.$size.'px.png"  /></a></div>';
}
return $output;
}
}

function fsPinterest($title, $link, $desc){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['pinterest']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$pin_link = "http://pinterest.com/pin/create/button/?url=" . urlencode($link) . "&media=" . urlencode(flodjiShareFirstImage()) . "&description=" . urlencode($desc);
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Pinterest', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($pin_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Pinterest teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Pinterest32px.png"  /><span class="fsspanflatmix fspi"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fspi" rel="nofollow" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($pin_link).'" onclick="return popup(this.href);"><strong>' . __('Pinterest', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$outputa = '<div class="'.$div_class.'"><a class="'.$fsbase.' fspi" rel="nofollow" href="'.$pin_link.'" onclick="return popup(this.href);"><strong>' . __('Pinterest', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$pin_link.'" target="_blank" title="Auf Pinterest zeigen" rel="nofollow"><img alt="' . $title . __(' auf Pinterest teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Pinterest'.$size.'px.png"  /></a></div>';
}
return $output;
}
}

function fsStumbleUpon($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['stumbleupon']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$stumble_link = "http://www.stumbleupon.com/submit?url=" . urlencode($link) . "&title=" . urlencode($title);
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('StumbleUpon', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($stumble_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf StumbleUpon teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/StumbleUpon32px.png"  /><span class="fsspanflatmix fssu"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fssu" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($stumble_link).'" target="_blank" title="Auf Stumbleupon zeigen" rel="nofollow"><strong>' . __('StumbleUpon', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fssu" href="'.$stumble_link.'" target="_blank" rel="nofollow"><strong>' . __('StumbleUpon', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$stumble_link.'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf StumbleUpon teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/StumbleUpon'.$size.'px.png"  alt="Auf Stumbleupon zeigen" /></a></div>';
}
return $output;
}
}

function fsTumblr($title, $link, $desc){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['tumblr']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$tumblr_link = 'http://www.tumblr.com/share/link?url='.urlencode($link).'&name='.urlencode($title).'&description='.urlencode($desc);
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Tumblr.', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($tumblr_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Tumblr. teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Tumblr32px.png"  /><span class="fsspanflatmix fstu"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fstu" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($tumblr_link).'" target="_blank" rel="nofollow"><strong>' . __('Tumblr.', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fstu" href="'.$tumblr_link.'" target="_blank" rel="nofollow"><strong>' . __('Tumblr.', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$tumblr_link.'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Tumblr. teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Tumblr'.$size.'px.png"  alt="Auf tumblr zeigen" /></a></div>';
}
return $output;
}
}

function fsWhatsapp($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['whatsapp']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$ismobile = flodjishare_is_mobile();
if($ismobile === true){
$wa_link = 'whatsapp://send?text='.urlencode($title).' - '.urlencode($link);
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Whatsapp', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($wa_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Whatsapp teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/WhatsApp32px.png"  /><span class="fsspanflatmix fswa"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fswa" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($wa_link).'" target="_blank" rel="nofollow"><strong>' . __('Whatsapp', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fswa" href="'.$wa_link.'" target="_blank" rel="nofollow"><strong>' . __('Whatsapp', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$wa_link.'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Whatsapp teilen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/WhatsApp'.$size.'px.png"  alt="Bei Whatsapp teilen" /></a></div>';
}
return $output;
}
}
}

function fsPocket($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['pocket']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$po_link = 'https://getpocket.com/save?title=' . rawurlencode( $title ) . '&url=' . rawurlencode( $link );
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Pocket', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){
$klicks = '0';
}
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($po_link).'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Pocket weiterlesen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Pocket32px.png"  /><span class="fsspanflatmix fspo"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fspo" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($po_link).'" target="_blank" rel="nofollow"><strong>' . __('Pocket', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fspo" href="'.$po_link.'" target="_blank" rel="nofollow"><strong>' . __('Pocket', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$po_link.'" target="_blank" rel="nofollow"><img alt="' . $title . __(' auf Pocket weiterlesen', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Pocket'.$size.'px.png"  /></a></div>';
}
return $output;
}
}

function fsFeedly($title){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['active_buttons']['feedly']==true) {
if ($option['align']==true){ $div_class = 'fscenter'; $div_classb = 'fsbtncenter'; } else { $div_class = 'fsleft'; $div_classb = 'fsbtnfloat'; }
if($option['design']=='metro'){$fsbase = 'fsbase'; $fscounter = 'fscounter';}
if($option['design']=='flat'){$fsbase = 'fsflat'; $fscounter = 'fscounterflat';}
$fe_link = 'http://cloud.feedly.com/#subscription' . rawurlencode( '/feed/' . get_feed_link( 'rss2' ) );
if (($option['design']=='metro') or ($option['design']=='flat')){
if ($option['counter']==true){
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$db_title = flodjiShareNormTitle($title);
$network= __('Feedly', 'flodjishare');
$klicks = $wpdb->get_var("SELECT klicks FROM $dbprfx WHERE title='$db_title' AND network='$network'");
if($klicks == ''){ $klicks = '0'; }
if($option['flatmix']==true){
$output = '<div class="'.$div_class.'"><a class="flatmix" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($fe_link).'" target="_blank" rel="nofollow"><img alt="' . get_bloginfo('name') . __(' auf Feedly abonnieren', 'flodjishare') . '" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Feedly32px.png"  /><span class="fsspanflatmix fsfe"><strong>'.short_number($klicks).'</strong></span></a></div>';
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsfe" href="/wp-content/plugins/flodjishare/klick.php?n='.$network.'&title='.urlencode($db_title).'&fsurl='.urlencode($fe_link).'" target="_blank" rel="nofollow"><strong>' . __('Feedly', 'flodjishare') . '</strong></a><span class="'.$fscounter.'"><strong>'.short_number($klicks).'</strong></span></div>';
}
} else {
$output = '<div class="'.$div_class.'"><a class="'.$fsbase.' fsfe" href="'.$fe_link.'" target="_blank" rel="nofollow"><strong>' . __('Feedly', 'flodjishare') . '</strong></a></div>';
}
}
if($option['design']=='button'){
if ($option['big']==true){ $size = '64'; } else { $size = '32'; }
$output = '<div class="'.$div_classb.'"><a href="'.$fe_link.'" target="_blank" rel="nofollow"><img  src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Feedly'.$size.'px.png"  alt="' . get_bloginfo('name') . __(' auf Feedly abonnieren', 'flodjishare') . '" /></a></div>';
}
return $output;
}
}

function fsMobileBar($title, $link){
global $wpdb;

$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
if ($option['sharebar']==true) {
if(is_single()){
$isMobile = flodjishare_is_mobile();
if($isMobile === true){
$outputa .= '<style type="text/css">
body.single {padding-bottom: 64px ! important;}
</style>';
$db_title = flodjiShareNormTitle($title);
$output = '';
$output .= '<div class="fsbar">';

if($option['active_buttons']['facebook']==true){
$fb_link = 'http://www.facebook.com/sharer.php?u='.$link.'&amp;t='.$title;
if($option['counter']==true){
$output .= '<a  href="/wp-content/plugins/flodjishare/klick.php?n=Facebook&title='.urlencode($db_title).'&fsurl='.urlencode($fb_link).'" target="_blank" rel="nofollow"><img  src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Facebook64px.png" alt="' . get_bloginfo('name') . __(' auf Facebook teilen', 'flodjishare') . '" /></a>';
} else {
$output .= '<a href="'.$fb_link.'" target="_blank" rel="nofollow"><img  src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Facebook64px.png" alt="' . get_bloginfo('name') . __(' auf Facebook teilen', 'flodjishare') . '" /></a>';
}
}

if($option['active_buttons']['twitter']==true){
if($option['twitter_text'] != ''){
$tw_link = 'https://twitter.com/share?url='.urlencode($link).'&via='.stripslashes($option['twitter_text']).'&text='.urlencode($title);
} else {
$tw_link = 'https://twitter.com/share?url='.urlencode($link).'&text='.urlencode($title);
}
if($option['counter']==true){
$output .= '<a  href="/wp-content/plugins/flodjishare/klick.php?n=Twitter&title='.urlencode($db_title).'&fsurl='.urlencode($tw_link).'" target="_blank" rel="nofollow"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Twitter64px.png" alt="' . get_bloginfo('name') . __(' auf Twitter teilen', 'flodjishare') . '" /></a>';
} else {
$output .= '<a href="'.$tw_link.'" target="_blank" rel="nofollow"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Twitter64px.png" alt="' . get_bloginfo('name') . __(' auf Twitter teilen', 'flodjishare') . '" /></a>';
}
}

if($option['active_buttons']['gplus']==true){
$gp_link = 'https://plus.google.com/share?url=' . urlencode($link);
if($option['counter']==true){
$output .= '<a  href="/wp-content/plugins/flodjishare/klick.php?n=Google%20Plus&title='.urlencode($db_title).'&fsurl='.urlencode($gp_link).'" target="_blank" rel="nofollow"><img  src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Google+64px.png" alt="' . get_bloginfo('name') . __(' auf Google Plus teilen', 'flodjishare') . '" /></a>';
} else {
$output .= '<a href="'.$gp_link.'" target="_blank" rel="nofollow"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Google+64px.png" alt="' . get_bloginfo('name') . __(' auf Google Plus teilen', 'flodjishare') . '" /></a>';
}
}

if($option['active_buttons']['whatsapp']==true){
$wa_link = 'whatsapp://send?text='.urlencode($title).' - '.urlencode($link);
if($option['counter']==true){
$output .= '<a  href="/wp-content/plugins/flodjishare/klick.php?n=Whatsapp&title='.urlencode($db_title).'&fsurl='.urlencode($wa_link).'" target="_blank" rel="nofollow"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/WhatsApp64px.png" alt="' . get_bloginfo('name') . __(' auf Whatsapp teilen', 'flodjishare') . '" /></a>';
} else {
$output .= '<a  href="'.$wa_link.'" target="_blank" rel="nofollow"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/WhatsApp64px.png" alt="' . get_bloginfo('name') . __(' auf Whatsapp teilen', 'flodjishare') . '" /></a>';
}
}
$output .= '</div>';
return $output;
}
}
}
}

function flodjishare_is_mobile(){
if(preg_match('/(android|iphone|ipad|ipaq|ipod)/i', $_SERVER['HTTP_USER_AGENT']))
return true; 
else
return false;
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
	
$args = array( 'public' => true, '_builtin' => false ); 
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
$parameter[]=fsTitle();
$parameter[]=get_permalink();
$parameter[]=flodjiShareFirstImage();
$parameter[]=get_option('blogname');
$parameter[]=descExcerpt();
endwhile; endif; 
}elseif(is_category()){
$parameter[]=single_cat_title( '', false ).' - Kategorie Archiv';
$parameter[]="http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
$parameter[]=flodjiShareFirstImage();
$parameter[]=get_option('blogname');
$parameter[]=category_description();	
}else{
$parameter[]=get_option('blogname');
$parameter[]=get_option('siteurl');
$parameter[]=flodjiShareFirstImage();
$parameter[]=get_option('blogname');
if($option['desc_text']){
if(is_home()){ $parameter[]=stripslashes($option['desc_text']); }
} else {
$parameter[]=get_option('blogdescription');
}
}
if(!is_paged()){
if(!is_tag()){
if(!is_404()){
echo flodjiShareMetas($parameter);
}
}
}
}

function flodjiShareMetas($parameter){
$post_id 			= get_the_ID();
$comments_count 	= wp_count_comments($post_id);
$option_string 		= get_option('flodjishare');
$option 			= json_decode($option_string, true);
$values = get_post_custom( $post->ID );
extract( $values, EXTR_SKIP );
$allowed_html = array( 'a' => array( 'href' => array(), 'title' => array() ), 'em' => array(), 'strong' => array() );
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
if(!is_category()){
if(!is_home()){
$txt.="<meta property='og:type' content='article'/>";
$txt.="\n";
} else {
$txt.="<meta property='og:type' content='website'/>";
$txt.="\n";
}
}
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
if(is_category()){
$txt.="<div itemscope itemtype=\"http://schema.org/category\">";
$txt.="\n";
} elseif(is_home()){
$txt.="<div itemscope itemtype=\"http://schema.org/Blog\">";
$txt.="\n";
} else {
$txt.="<div itemscope itemtype=\"http://schema.org/Article\">";
$txt.="\n";
}
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
if(is_single()){
$txt.="<meta itemprop='interactionCount' content='".$comments_count->approved ."' />";
$txt.="\n";
}
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

if(!is_category()){
if($option['active_buttons']['news_keywords']==true){
if(is_home() && $option['keywords']){
$txt.="<meta name='news_keywords' content='".stripslashes(strtolower($option['keywords']))."'/>";
$txt.="\n";
} else {
$txt.="<meta name='news_keywords' content='".strip_tags(strtolower(get_the_tag_list('',', ','')))."'/>";
$txt.="\n";
}
}

if($option['active_buttons']['meta_keywords']==true){
if(is_home() && $option['keywords']){
$txt.="<meta name='keywords' content='".stripslashes(strtolower($option['keywords']))."'/>";
$txt.="\n";
} else {
$txt.="<meta name='keywords' content='".strip_tags(strtolower(get_the_tag_list('',', ','')))."'/>";
$txt.="\n";
}
}
}
	
if(!is_category()){	
if($option['active_buttons']['twittercards']==true){
if(!is_home()){
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

function fs_set_post_views( $postID ){
$count_key = 'fs_post_views_count';
$count = get_post_meta($postID, $count_key, true);
if($count==''){
$count = 0;
delete_post_meta( $postID, $count_key );
add_post_meta( $postID, $count_key, '0' );
} else {
$count++;
update_post_meta( $postID, $count_key, $count );
}
}

function fs_get_post_views( $postID ){
$count_key = 'fs_post_views_count';
$count = get_post_meta( $postID, $count_key, true );
if($count=='') {
delete_post_meta( $postID, $count_key );
add_post_meta( $postID, $count_key, '0' );
return "0";
}
return $count;
}

add_action('wp_head', 'fsCountPV');
function fsCountPV(){
fs_set_post_views(get_the_ID());
}

function followMeFlodjiShare(){
$fb = '<a target="_blank" href="https://www.facebook.com/pages/Flodjide/415996855137000"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Facebook32px.png" /></a> ';
$tw = '<a target="_blank" href="https://www.twitter.com/flodji"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Twitter32px.png" /></a> ';
$gp = '<a target="_blank" href="https://plus.google.com/104542622643572083517/"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Google+32px.png" /></a> ';
$fd = '<a target="_blank" href="http://flodji.de/feed/"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/RSS32px.png" /></a>';
return '<h3>' . __('Folge mir', 'flodjishare') . ':</h3>' . $fb . $tw . $gp . $fd;
}

function followMeFlodjiShareff(){
$fb = '<a target="_blank" href="https://www.facebook.com/wackelkamera"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Facebook32px.png" /></a> ';
$tw = '<a target="_blank" href="http://www.twitter.com/wackelkamera"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/Twitter32px.png" /></a> ';
$fd = '<a target="_blank" href="http://found-footage.de/feed/"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/RSS32px.png" /></a>';
return '<h3>' . __('found-footage.de folgen', 'flodjishare') . ':</h3>' . $fb . $tw . $fd;
}

function spendPayPalFlodjiShare(){
$paypalbutton = '<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K9U25CKQNA5GL"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/donate.png" height="32"/></a>';
$amazonbutton = ' <a target="_blank" href="http://www.amazon.de/registry/wishlist/2IV9DRBG5M9W3"><img alt="" src="'.site_url().'/wp-content/plugins/flodjishare/buttons/amazon.png" height="32"/></a>';
return '<h3>' . __('Spenden', 'flodjishare') . ':</h3>' . $paypalbutton . $amazonbutton;
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
<p><?php echo __('Aus diesen Daten werden die Meta-Tags für <strong>Facebook</strong> <small><a target="_blank" href="http://ogp.me/"><u>(Opengraph)</u></a></small>, <strong>Twitter</strong> <small><a target="_blank" href="https://dev.twitter.com/docs/cards"><u>(Twitter Cards)</u></a></small>, <strong>Google (Google+) und Suchergebnisse</strong> z.B. bei Google, Yahoo oder Bing <small><a target="_blank" href="https://support.google.com/webmasters/answer/99170?hl=de"><u>(Rich Snippets)</u></a></small> erzeugt. Werden hier keine Daten eingetragen, dann werden der Beitrags/Seitentitel, -auszug und die URL des Post-Thumbnails bzw. des ersten Bildes verwendet.', 'flodjishare'); ?></p>
<p><label class="flodjishare" for="fs_title"><?php echo __('Überschrift', 'flodjishare'); ?>:</label>
<input class="flodjishare" type="flodjisharebox_breite" name="_flodjisharebox_fs_title" id="fs_title" value="<?php echo $_flodjisharebox_fs_title[0]; ?>" /><small><?php echo __('Max. 55 Zeichen', 'flodjishare'); ?></small></p>
<p><label class="flodjishare" for="fs_desc"><?php echo __('Beschreibung', 'flodjishare'); ?>:</label>
<input class="flodjishare" type="flodjisharebox_breite" name="_flodjisharebox_fs_desc" id="fs_desc" value="<?php echo $_flodjisharebox_fs_desc[0]; ?>" /><small><?php echo __('Max. 160 Zeichen', 'flodjishare'); ?></small></p>
<p><label class="flodjishare" for="fs_image"><?php echo __('Bild-URL', 'flodjishare'); ?>:</label>
<input class="flodjishare" type="flodjisharebox_breite" name="_flodjisharebox_fs_image" id="fs_image" value="<?php echo $_flodjisharebox_fs_image[0]; ?>" /></p>
<p><strong><?php echo __('So könnten Deine Suchergebnisse aktuell aussehen', 'flodjishare'); ?>:</strong> <small><?php echo __('(Beitrag / Seite speichern um zu aktualisieren)', 'flodjishare'); ?></small></p>
<p style="max-width:512px;background-color:white;border:thin solid #ccc;padding:3px;"><span style="color:#2518b5;text-decoration:underline;"><?php if($_flodjisharebox_fs_title[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_title[0], 55)); } else { echo strip_tags(flodjiShareShortText(get_the_title(), 55)); } ?></span><br />
<span style="color:green"><?php echo get_permalink(); ?></span><br />
<?php if($_flodjisharebox_fs_desc[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_desc[0], 160)); } else { echo strip_tags(flodjiShareShortText(get_the_excerpt(), 160)); } ?></p>
<p><strong><?php echo __('So könnte dieser Beitrag beim Teilen auf Facebook, Twitter und Google + aussehen', 'flodjishare'); ?>:</strong> <small><?php echo __('(Beitrag / Seite speichern um zu aktualisieren)', 'flodjishare'); ?></small></p>
<?php if($_flodjisharebox_fs_image[0] != ''){ ?><img style="float:left;width:150px;margin-right:3px;" src="<?php echo $_flodjisharebox_fs_image[0]; ?>" /><?php } ?><p style="max-width:512px;background-color:white;border:thin solid #ccc;min-height:100px;"><span style="color:#2518b5;text-decoration:underline;"><?php if($_flodjisharebox_fs_title[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_title[0], 70)); } else { echo strip_tags(flodjiShareShortText(get_the_title(), 70)); } ?></span><br />
<span style="color:green"><?php echo get_permalink(); ?></span><br />
<?php if($_flodjisharebox_fs_desc[0] != ''){ echo strip_tags(flodjiShareShortText($_flodjisharebox_fs_desc[0], 160)); } else { echo strip_tags(flodjiShareShortText(get_the_excerpt(), 160)); } ?></p>
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
		wp_die( __('Deine Benutzerrechte reichen nicht aus um diese Seite anzuzeigen.', 'flodjishare') );
	}
	if( isset($_POST['flodjishare_position'])) {
		$option = array();
		$option['active_buttons'] = array('facebook'=>false, 'twitter'=>false, 'digg'=>false, 'delicious'=>false, 'xing'=>false, 'gplus'=>false, 'linkedin'=>false, 'pinterest'=>false, 'stumbleupon'=>false, 'tumblr'=>false, 'whatsapp'=>false, 'pocket'=>false, 'feedly'=>false, 'flattr'=>false, 'opengraph'=>false, 'richsnippets'=>false, 'twittercards'=>false, 'metabox'=>false, 'metadesc'=>false, 'news_keywords'=>false, 'meta_keywords'=>false, 'counter'=>false, 'align'=>false, 'flatmix'=>false, 'big'=>false, 'sharebar'=>false, 'supportlink'=>false, 'privacy'=>false, 'own1'=>false, 'own2'=>false, 'own3'=>false);
		if ($_POST['flodjishare_active_facebook']=='on') { $option['active_buttons']['facebook'] = true; }
		if ($_POST['flodjishare_active_twitter']=='on') { $option['active_buttons']['twitter'] = true; }
		if ($_POST['flodjishare_active_digg']=='on') { $option['active_buttons']['digg'] = true; }
		if ($_POST['flodjishare_active_delicious']=='on') { $option['active_buttons']['delicious'] = true; }		
		if ($_POST['flodjishare_active_xing']=='on') { $option['active_buttons']['xing'] = true; }
		if ($_POST['flodjishare_active_gplus']=='on') { $option['active_buttons']['gplus'] = true; }
		if ($_POST['flodjishare_active_linkedin']=='on') { $option['active_buttons']['linkedin'] = true; }
		if ($_POST['flodjishare_active_pinterest']=='on') { $option['active_buttons']['pinterest'] = true; }
		if ($_POST['flodjishare_active_stumbleupon']=='on') { $option['active_buttons']['stumbleupon'] = true; }
		if ($_POST['flodjishare_active_tumblr']=='on') { $option['active_buttons']['tumblr'] = true; }
		if ($_POST['flodjishare_active_whatsapp']=='on') { $option['active_buttons']['whatsapp'] = true; }
		if ($_POST['flodjishare_active_flattr']=='on') { $option['active_buttons']['flattr'] = true; }
		if ($_POST['flodjishare_active_pocket']=='on') { $option['active_buttons']['pocket'] = true; }
		if ($_POST['flodjishare_active_feedly']=='on') { $option['active_buttons']['feedly'] = true; }
		if ($_POST['flodjishare_active_counter']=='on') { $option['counter'] = true; }
		if ($_POST['flodjishare_active_flatmix']=='on') { $option['flatmix'] = true; }
		if ($_POST['flodjishare_active_align']=='on') { $option['align'] = true; }
		if ($_POST['flodjishare_active_big']=='on') { $option['big'] = true; }
		if ($_POST['flodjishare_active_sharebar']=='on') { $option['sharebar'] = true; }
		if ($_POST['flodjishare_active_opengraph']=='on') { $option['active_buttons']['opengraph'] = true; }
		if ($_POST['flodjishare_active_richsnippets']=='on') { $option['active_buttons']['richsnippets'] = true; }
		if ($_POST['flodjishare_active_twittercards']=='on') { $option['active_buttons']['twittercards'] = true; }
		if ($_POST['flodjishare_active_metabox']=='on') { $option['active_buttons']['metabox'] = true; }
		if ($_POST['flodjishare_active_metadesc']=='on') { $option['active_buttons']['metadesc'] = true; }
		if ($_POST['flodjishare_active_news_keywords']=='on') { $option['active_buttons']['news_keywords'] = true; }
		if ($_POST['flodjishare_active_meta_keywords']=='on') { $option['active_buttons']['meta_keywords'] = true; }
		if ($_POST['flodjishare_active_googleAuthor']=='on') { $option['active_buttons']['gplusAuthor'] = true; }
		if ($_POST['flodjishare_active_post_stats']=='on') { $option['active_buttons']['post_stats'] = true; }
		if ($_POST['flodjishare_active_fs_hit_column']=='on') { $option['fs_hit_column'] = ture; }
		if ($_POST['flodjishare_active_ffde_widget']=='on') { $option['ffde_widget'] = true; }
		if ($_POST['flodjishare_active_prfx']=='on') { $option['active_buttons']['prfx'] = true; }
		if ($_POST['flodjishare_active_privacy']=='on') { $option['privacy'] = true; }
		if ($_POST['flodjishare_active_own1']=='on') { $option['own1'] = true; }
		if ($_POST['flodjishare_active_own2']=='on') { $option['own2'] = true; }
		if ($_POST['flodjishare_active_own3']=='on') { $option['own3'] = true; }
		if ($_POST['flodjishare_active_supportlink']=='on') { $option['supportlink'] = true; }
		$option['position'] = esc_html($_POST['flodjishare_position']);
		$option['design'] = esc_html($_POST['flodjishare_design']);
		$option['skip_single'] = esc_html($_POST['flodjishare_skip_single']);
		$option['skip_page'] = esc_html($_POST['flodjishare_skip_page']);
		$option['skip_cat'] = esc_html($_POST['flodjishare_skip_cat']);
		$option['intro_text'] = esc_html($_POST['flodjishare_intro_text']);
		$option['intro_height'] = esc_html($_POST['flodjishare_intro_height']);
		$option['height'] = esc_html($_POST['flodjishare_height']);
		$option['dbprfx'] = esc_html($_POST['flodjishare_dbprfx']);
		$option['twitter_text'] = esc_html($_POST['flodjishare_twitter_text']);
		$option['flattr_id'] = esc_html($_POST['flodjishare_flattr_id']);
		$option['gplusidpage'] = esc_html($_POST['flodjishare_gplus_page']);
		$option['gplusiduser'] = esc_html($_POST['flodjishare_gplus_user']);
		$option['fb_app_id'] = esc_html($_POST['flodjishare_fb_app_id']);
		$option['fb_admin'] = esc_html($_POST['flodjishare_fb_admin']);
		$option['privacy_text'] = esc_html($_POST['flodjishare_privacy_text']);
		$option['own1content'] = esc_html($_POST['flodjishare_own1content']);
		$option['own2content'] = esc_html($_POST['flodjishare_own2content']);
		$option['own3content'] = esc_html($_POST['flodjishare_own3content']);
		$option['desc_text'] = esc_html($_POST['flodjishare_desc_text']);
		$option['keywords'] = esc_html($_POST['flodjishare_keywords']);
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
		$outputa .= '<div class="updated"><p><strong>' . __('Einstellungen gespeichert.', 'flodjishare' ) . '</strong></p></div>';
	}
	$option = array();
	$option_string = get_option($option_name);
	if ($option_string===false) {
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'whatsapp'=>true, 'pocket'=>true, 'feedly'=>true, 'flattr'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true, 'metabox'=>true, 'metadesc'=>true, 'news_keywords'=>true, 'meta_keywords'=>true, 'prfx'=>true, 'gplusAthor'=>true, 'post_stats'=>true, 'counter'=>true, 'align'=>true, 'flatmix'=>true, 'big'=>true, 'sharebar'=>true, 'supportlink'=>true, 'privacy'=>true, 'own1'=>true, 'own2'=>true, 'own3'=>true);
		$option['position'] = 'unter';
		$option['design'] = 'metro';
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true, 'category'=>true, 'search'=>true, 'archive'=>true);
		$option['skip_single'] = array('skip_single'=>true);
		$option['skip_page'] = array('skip_page'=>true);
		$option['skip_cat'] = array('skip_cat'=>true);
		$option['fs_hit_column'] = array('fs_hit_column'=>true);
		$option['intro_text'] = array('intro_text'=>true);
		$option['intro_height'] = array('intro_height'=>true);
		$option['height'] = array('height'=>true);
		$option['ffde_widget'] = array('ffde_widget'=>true);
		$option['dbprfx'] = array('dbprfx'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['flattr_id'] = array('flattr_id'=>true);
		$option['gplusidpage'] = array('gplusidpage'=>true);
		$option['gplusiduser'] = array('gplusiduser'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['privacy_text'] = array('privacy_text'=>true);
		$option['own1content'] = array('own1content'=>true);
		$option['own2content'] = array('own2content'=>true);
		$option['own3content'] = array('own3content'=>true);
		$option['desc_text'] = array('desc_text'=>true);
		$option['keywords'] = array('keywords'=>true);
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
		add_option($option_name, 'metro');
		$option_string = get_option($option_name);	
	}
	if ($option_string=='ueber' or $option_string=='unter' or $option_string=='both' or $option_string=='shortcode') {
		$flodjishare_options = explode('|||',$option_string);
		$option = array();
		$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'digg'=>true, 'delicious'=>true, 'xing'=>true, 'gplus'=>true, 'linkedin'=>true, 'pinterest'=>true, 'stumbleupon'=>true, 'tumblr'=>true, 'whatsapp'=>true, 'flattr'=>true, 'pocket'=>true, 'feedly'=>true, 'opengraph'=>true, 'richsnippets'=>true, 'twittercards'=>true, 'metabox'=>true, 'metadesc'=>true, 'news_keywords'=>true, 'meta_keywords'=>true, 'prfx'=>true, 'gplusAuthor'=>true, 'post_stats'=>true, 'counter'=>true,  'align'=>true, 'flatmix'=>true, 'big'=>true, 'sharebar'=>true, 'supportlink'=>true, 'privacy'=>true,'own1'=>true, 'own2'=>true, 'own3'=>true);
		$option['position'] = $flodjishare_options[0];
		$option['design'] = $flodjishare_options[0];
		$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home'=>true, 'category'=>true, 'search'=>true, 'archive'=>true);
		$option['skip_single'] = array('skip_single'=>true);
		$option['skip_page'] = array('skip_page'=>true);
		$option['fs_hit_column'] = array('fs_hit_column'=>true);
		$option['skip_cat'] = array('skip_cat'=>true);
		$option['intro_text'] = array('intro_text'=>true);
		$option['intro_height'] = array('intro_height'=>true);
		$option['height'] = array('height'=>true);
		$option['ffde_widget'] = array('ffde_widget'=>true);
		$option['dbprfx'] = array('dbprfx'=>true);
		$option['twitter_text'] = array('twitter_text'=>true);
		$option['flattr_id'] = array('flattr_id'=>true);
		$option['gplusidpage'] = array('gplusidpage'=>true);
		$option['gplusiduser'] = array('gplusiduser'=>true);
		$option['fb_app_id'] = array('fb_app_id'=>true);
		$option['fb_admin'] = array('fb_admin'=>true);
		$option['privacy_text'] = array('privacy_text'=>true);
		$option['own1content'] = array('own1content'=>true);
		$option['own2content'] = array('own2content'=>true);
		$option['own3content'] = array('own3content'=>true);
		$option['desc_text'] = array('desc_text'=>true);
		$option['keywords'] = array('keywords'=>true);
		$option['altimg'] = array('altimg'=>true);
		$option['twitsite'] = array('twitsite'=>true);
	} else {
		$option = json_decode($option_string, true);
	}
	$sel_above 			= ($option['position']=='ueber') ? 'selected="selected"' : '';
	$sel_below 			= ($option['position']=='unter') ? 'selected="selected"' : '';
	$sel_both			= ($option['position']=='both') ? 'selected="selected"' : '';
	$sel_short 			= ($option['position']=='shortcode') ? 'selected="selected"' : '';
	$active_metro		= ($option['design']=='metro') ? 'selected="selected"' : '';
	$active_flat		= ($option['design']=='flat') ? 'selected="selected"' : '';
	$active_button		= ($option['design']=='button') ? 'selected="selected"' : '';
	$skip_single		= ($option['skip_single']=='') ? 'selected="selected"' : '';
	$skip_page			= ($option['skip_page']=='') ? 'selected="selected"' : '';
	$skip_cat			= ($option['skip_cat']=='') ? 'selected="selected"' : '';
	$active_facebook 	= ($option['active_buttons']['facebook']==true) ? 'checked="checked"' : '';
	$active_twitter  	= ($option['active_buttons']['twitter'] ==true) ? 'checked="checked"' : '';
	$active_digg		= ($option['active_buttons']['digg']==true) ? 'checked="checked"' : '';
	$active_delicious	= ($option['active_buttons']['delicious']==true) ? 'checked="checked"' : '';
	$active_xing		= ($option['active_buttons']['xing']==true) ? 'checked="checked"' : '';
	$active_flattr		= ($option['active_buttons']['flattr']==true) ? 'checked="checked"' : '';
	$active_gplus		= ($option['active_buttons']['gplus']==true) ? 'checked="checked"' : '';
	$active_linkedin	= ($option['active_buttons']['linkedin']==true) ? 'checked="checked"' : '';
	$active_pinterest	= ($option['active_buttons']['pinterest']==true) ? 'checked="checked"' : '';
	$active_stumbleupon	= ($option['active_buttons']['stumbleupon']==true) ? 'checked="checked"' : '';
	$active_tumblr		= ($option['active_buttons']['tumblr']==true) ? 'checked="checked"' : '';
	$active_whatsapp	= ($option['active_buttons']['whatsapp']==true) ? 'checked="checked"' : '';
	$active_pocket		= ($option['active_buttons']['pocket']==true) ? 'checked="checked"' : '';
	$active_feedly		= ($option['active_buttons']['feedly']==true) ? 'checked="checked"' : '';
	$active_counter		= ($option['counter']==true) ? 'checked="checked"' : '';
	$active_flatmix		= ($option['flatmix']==true) ? 'checked="checked"' : '';
	$height				= ($option['height']=='') ? 'selected="selected"' : '';
	$active_align		= ($option['align']==true) ? 'checked="checked"' : '';
	$active_big			= ($option['big']==true) ? 'checked="checked"' : '';
	$active_sharebar	= ($option['sharebar']==true) ? 'checked="checked"' : '';
	$active_opengraph	= ($option['active_buttons']['opengraph']==true) ? 'checked="checked"' : '';
	$active_richsnippets= ($option['active_buttons']['richsnippets']==true) ? 'checked="checked"' : '';
	$active_twittercards= ($option['active_buttons']['twittercards']==true) ? 'checked="checked"' : '';
	$active_metabox		= ($option['active_buttons']['metabox']==true) ? 'checked="checked"' : '';
	$active_metadesc	= ($option['active_buttons']['metadesc']==true) ? 'checked="checked"' : '';
	$active_news_keywords	= ($option['active_buttons']['news_keywords']==true) ? 'checked="checked"' : '';
	$active_meta_keywords	= ($option['active_buttons']['meta_keywords']==true) ? 'checked="checked"' : '';
	$active_gplusauthor	= ($option['active_buttons']['gplusAuthor']==true) ? 'checked="checked"' : '';
	$active_post_stats 	= ($option['active_buttons']['post_stats']==true) ? 'checked="checked"' : '';
	$active_fs_hit_column = ($option['fs_hit_column']==true) ? 'checked="checked"' : '';
	$active_ffde_widget	= ($option['ffde_widget']==true) ? 'checked="checked"' : '';
	$active_prfx		= ($option['active_buttons']['prfx']==true) ? 'checked="checked"' : '';
	$active_privacy		= ($option['privacy']==true) ? 'checked="checked"' : '';
	$active_own1		= ($option['own1']==true) ? 'checked="checked"' : '';
	$active_own2		= ($option['own2']==true) ? 'checked="checked"' : '';
	$active_own3		= ($option['own3']==true) ? 'checked="checked"' : '';
	$active_supportlink	= ($option['supportlink']==true) ? 'checked="checked"' : '';
	$show_in_posts 		= ($option['show_in']['posts']==true) ? 'checked="checked"' : '';
	$show_in_pages 		= ($option['show_in']['pages'] ==true) ? 'checked="checked"' : '';
	$show_in_home 		= ($option['show_in']['home'] ==true) ? 'checked="checked"' : '';
	$show_in_category	= ($option['show_in']['category'] ==true) ? 'checked="checked"' : '';
	$show_in_search		= ($option['show_in']['search'] ==true) ? 'checked="checked"' : '';
	$show_in_archive	= ($option['show_in']['archive'] ==true) ? 'checked="checked"' : '';
	$intro_text			= ($option['intro_text']=='') ? 'selected="selected"' : '';
	$intro_height		= ($option['intro_height']=='') ? 'selected="selected"' : '';
	$dbprfx				= ($option['dbprfx']=='') ? 'selected="selected"' : '';
	$twitter_text		= ($option['twitter_text']=='') ? 'selected="selected"' : '';
	$flattr_id			= ($option['flattr_id']=='') ? 'selected="selected"' : '';
	$gplusidpage		= ($option['gplusidpage']=='') ? 'selected="selected"' : '';
	$gplusiduser		= ($option['gplusiduser']=='') ? 'selected="selected"' : '';
	$fb_app_id			= ($option['fb_app_id']=='') ? 'selected="selected"' : '';
	$fb_admin			= ($option['fb_admin']=='') ? 'selected="selected"' : '';
	$privacy_text		= ($option['privacy_text']=='') ? 'selected="selected"' : '';
	$own1content		= ($option['own1content']=='') ? 'selected="selected"' : '';
	$own2content		= ($option['own2content']=='') ? 'selected="selected"' : '';
	$own3content		= ($option['own3content']=='') ? 'selected="selected"' : '';
	$desc_text			= ($option['desc_text']=='') ? 'selected="selected"' : '';
	$keywords			= ($option['keywords']=='') ? 'selected="selected"' : '';
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
	<div style="width:600px;float:left;">
		<h2>' . __('flodjiShare Einstellungen', 'flodjishare') . '</h2>
	<table><tr><td style="width:200px;">'.followMeFlodjiShare().'</td><td>'.spendPayPalFlodjiShare().'</td></tr></table><br />
		<form name="form1" method="post" action="">
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="'.esc_attr( __('Speichern', 'flodjishare')).'" />
		</p>
		<table>
		<tr><td valign="top"><strong>' . __("flodjiShare hier aktivieren", 'flodjishare' ) . ':</strong></td>
		<td>'
		.' <input type="checkbox" name="flodjishare_show_posts" '.$show_in_posts.'> '
		. __("Einzelne Beiträge", 'flodjishare' ).'<br />'
		.' <input type="checkbox" name="flodjishare_show_pages" '.$show_in_pages.'> '
		. __("Seiten", 'flodjishare' ).'<br />'
		.' <input type="checkbox" name="flodjishare_show_home" '.$show_in_home.'> '
		. __("Startseite", 'flodjishare' ).'<br />';
		$args=array('public' => true,'_builtin' => false); 
		$output = 'object';
		$operator = 'and';
		$post_types=get_post_types($args,$output,$operator);
		if(!$post_types){
		$outputa .= __('Keine Custom Post Types vorhanden', 'flodjishare') . '<br /><small><a target="_blank" href="http://codex.wordpress.org/Post_Types">' . __('Was ist das? (engl.)', 'flodjishare') . '</a></small>';
		} else {
		foreach ($post_types  as $post_type ){
		$outputa .= ' <input type="checkbox" name="flodjishare_show_'.$post_type->name.'" '.$checked[$post_type->name].'> '
		.$post_type->name.' &nbsp;&nbsp;';
		}
		}
		$outputa .= '<br />
		<input type="checkbox" name="flodjishare_show_category" '.$show_in_category.'> ' 
		. __("Kategorien", 'flodjishare' ) . '<br />
		<input type="checkbox" name="flodjishare_show_search" '.$show_in_search.'> ' 
		. __("Suchergebnisse", 'flodjishare' ) . '<br />
		<input type="checkbox" name="flodjishare_show_archive" '.$show_in_archive.'> ' 
		. __("Archive", 'flodjishare' ) . '<br /><br /></td></tr>
		
		<tr><td valign="top"><strong>' . __('Beiträge ausschließen', 'flodjishare' ) . ':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['skip_single'])){ $option_skip_single = ''; } else { $option_skip_single = $option['skip_single']; }
		$outputa .= '<input type="text" name="flodjishare_skip_single" value="'.$option_skip_single.'" size="50"><br />
		<span class="description">' . __('Trage hier die IDs der Beiträge / Custom Post Types ein, in denen keine Share Buttons angezeigt und keine Meta Tags genereiert  werden sollen. (Mehrere durch Komma getrennt.)', 'flodjishare' ) . '<br /></span>
		</td></tr>
		
		<tr><td valign="top"><strong>' . __('Seiten ausschließen', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['skip_page'])){ $option_skip_page = ''; } else { $option_skip_page = $option['skip_page']; }
		$outputa .= '<input type="text" name="flodjishare_skip_page" value="'.$option_skip_page.'" size="50"><br />
		<span class="description">' . __('Trage hier die IDs der Seiten ein, in denen keine Share Buttons angezeigt und keine Meta Tags genereiert  werden sollen. (Mehrere durch Komma getrennt.)', 'flodjishare' ) . '<br /></span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Kategorien ausschließen', 'flodjishare' ) . ':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['skip_cat'])){ $option_skip_cat = ''; } else { $option_skip_cat = $option['skip_cat']; }
		$outputa .= '<input type="text" name="flodjishare_skip_cat" value="'.$option_skip_cat.'" size="50"><br />
		<span class="description">' . __('Trage hier die IDs der Kategorien ein, in deren &Uuml;bersicht keine Share Buttons angezeigt und keine Meta Tags genereiert werden sollen. (Mehrere durch Komma getrennt.)', 'flodjishare' ) . '<br /></span>
		</td></tr>
		
		<tr><td valign="top"><strong>' . __('flodjiShare Buttons', 'flodjishare' ) . ':</strong></td>
		<td>'
		.' <input type="checkbox" name="flodjishare_active_facebook" '.$active_facebook.'> '
		. __('Facebook Share', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_twitter" '.$active_twitter.'> '
		. __('Twitter', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_digg" '.$active_digg.'> '
		. __('Digg', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_delicious" '.$active_delicious.'> '
		. __('Delicious', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_xing" '.$active_xing.'> '
		. __('Xing', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_gplus" '.$active_gplus.'> '
		. __('Google Plus', 'flodjishare' ).' &nbsp;&nbsp;<br />'		
		.' <input type="checkbox" name="flodjishare_active_linkedin" '.$active_linkedin.'> '
		. __('LinkedIn', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_pinterest" '.$active_pinterest.'> '
		. __('Pinterest', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_stumbleupon" '.$active_stumbleupon.'> '
		. __('Stumbleupon', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_flattr" '.$active_flattr.'> '
		. __('Flattr', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_tumblr" '.$active_tumblr.'> '
		. __('Tumblr', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_pocket" '.$active_pocket.'> '
		. __('Pocket', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_feedly" '.$active_feedly.'> '
		. __('Feedly', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.' <input type="checkbox" name="flodjishare_active_whatsapp" '.$active_whatsapp.'> '
		. __('Whatsapp', 'flodjishare' ).' &nbsp;&nbsp;<br />'
		.'<br /></td></tr>
		
		<tr><td valign="top"><strong>' . __('Eigener Button 1', 'flodjishare' ) . ':</strong></td>
		<td style="padding-bottom:20px;">'
		.' <input type="checkbox" name="flodjishare_active_own1" '.$active_own1.'> '
		. __('Eigener Button 1 anzeigen', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Zeigt den Button Eigener Button 1 an.', 'flodjishare' ).'</small></span><br />';
		if(is_array($option['own1content'])){ $option_own1content = ''; } else { $option_own1content = $option['own1content']; }
		$outputa .= '<textarea name="flodjishare_own1content" placeholder="<fb:like href=\'{link}\'></fb:like>" value="'.stripslashes($option_own1content).'" cols="50" rows="5">'.stripslashes($option_own1content).'</textarea><br />
		<span class="description">'.__('Trage den HTML oder JS Code für Deinen eigenen Button nach dem obigen Beispiel ein ({link} wird später durch den Link zur jeweiligen Seite ersetzt).', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>' . __('Eigener Button 2', 'flodjishare' ) . ':</strong></td>
		<td style="padding-bottom:20px;">'
		.' <input type="checkbox" name="flodjishare_active_own2" '.$active_own2.'> '
		. __('Eigener Button 2 anzeigen', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Zeigt den Button Eigener Button 2 an.', 'flodjishare' ).'</small></span><br />';
		if(is_array($option['own2content'])){ $option_own2content = ''; } else { $option_own2content = $option['own2content']; }
		$outputa .= '<textarea name="flodjishare_own2content" placeholder="<fb:like href=\'{link}\'></fb:like>" value="'.stripslashes($option_own2content).'" cols="50" rows="5">'.stripslashes($option_own2content).'</textarea><br />
		<span class="description">'.__('Trage den HTML oder JS Code für Deinen eigenen Button nach dem obigen Beispiel ein ({link} wird später durch den Link zur jeweiligen Seite ersetzt).', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>' . __('Eigener Button 3', 'flodjishare' ) . ':</strong></td>
		<td style="padding-bottom:20px;">'
		.' <input type="checkbox" name="flodjishare_active_own3" '.$active_own3.'> '
		. __('Eigener Button 3 anzeigen', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Zeigt den Button Eigener Button 3 an.', 'flodjishare' ).'</small></span><br />';
		if(is_array($option['own3content'])){ $option_own3content = ''; } else { $option_own3content = $option['own3content']; }
		$outputa .= '<textarea name="flodjishare_own3content" placeholder="<fb:like href=\'{link}\'></fb:like>" value="'.stripslashes($option_own3content).'" cols="50" rows="5">'.stripslashes($option_own3content).'</textarea><br />
		<span class="description">'.__('Trage den HTML oder JS Code für Deinen eigenen Button nach dem obigen Beispiel ein ({link} wird später durch den Link zur jeweiligen Seite ersetzt).', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"></td>
		<td style="padding-bottom:20px;">'.__('Wenn Deine eigenen Buttons auch im Metro Design dargestellt werden sollen, dann erkläre ich Dir in der <a target="_blank" href="http://flodji.de/flodjishare-fuer-wordpress-doku/#ownbtns">Anleitung</a> gerne wie das funktioniert.', 'flodjishare' ).'</td></tr>
		
		<tr><td valign="top"><strong>'.__('Position', 'flodjishare' ).':</strong></td>
		<td><select name="flodjishare_position">
			<option value="ueber" '.$sel_above.' > '.__('Über dem Beitrag', 'flodjishare' ).'</option>
			<option value="unter" '.$sel_below.' > '.__('Unter dem Beitrag', 'flodjishare' ).'</option>
			<option value="both" '.$sel_both.' > '.__('Beides', 'flodjishare' ).'</option>
			<option value="shortcode" '.$sel_short.' > '.__('Nur bei Shortcode [flodjishare]', 'flodjishare' ).'</option>
			</select><br /> 
		<br /></td></tr>
		
		<tr><td valign="top"><strong>'.__('Design', 'flodjishare' ).':</strong></td>
		<td><select name="flodjishare_design">
			<option value="metro" '.$active_metro.' > '.__('Altes Design', 'flodjishare' ).'</option>
			<option value="flat" '.$active_flat.' > '.__('Flat Design', 'flodjishare' ).'</option>
			<option value="button" '.$active_button.' > '.__('Button Design (Kein Klickzähler)', 'flodjishare' ).'</option>
			</select><br /> 
		<br /><input type="checkbox" name="flodjishare_active_counter" '.$active_counter.'> '
		. __('Klickzähler aktivieren (nur Altes / Flat Design)', 'flodjishare' ).'&nbsp;&nbsp;<br /><br /></td>
		</tr>
		
		<tr><td valign="top"><strong>'.__('Flatmix Buttons (Nur bei Flat Design)', 'flodjishare').':</strong></td>
		<td><input type="checkbox" name="flodjishare_active_flatmix" '.$active_flatmix.'> '
		. __('Flat Button Design mit Bild / CSS Mix', 'flodjishare' ).'</td></tr>
		
		<tr><td valign="top"><strong>'.__('Counter Box Größe (Nur bei Flatmix)', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['height'])){ $option_height = ''; } else { $option_height = $option['height']; }
		$outputa .= '<input type="text" name="flodjishare_height" value="'.stripslashes($option_height).'" size="20"><br />
		<span class="description">'.__('Hier gibst Du die Größe für die Counter Box im Flatmix Design in Pixeln an (z.B. 32).', 'flodjishare' ).'<br /></span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Buttons zentrieren', 'flodjishare').':</strong></td>
		<td><input type="checkbox" name="flodjishare_active_align" '.$active_align.'> '
		. __('Buttons zentrieren (Alle Designs)', 'flodjishare' ).'</td></tr>
		
		<tr><td valign="top"><strong>'.__('Große Buttons', 'flodjishare').':</strong></td>
		<td><input type="checkbox" name="flodjishare_active_big" '.$active_big.'> '
		. __('Große Buttons aktivieren (Nur Button Design)', 'flodjishare' ).'&nbsp;&nbsp;<br /><br /></td></tr>
		
		<tr><td valign="top"><strong>'.__('Überschrift', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['intro_text'])){ $option_intro_text = ''; } else { $option_intro_text = $option['intro_text']; }
		$outputa .= '<input type="text" name="flodjishare_intro_text" value="'.stripslashes($option_intro_text).'" size="50"><br />
		<span class="description">'.__('Dieser Text steht später über den Share Buttons (z.B. Diesen Beitrag teilen...).', 'flodjishare' ).'<br /></span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Überschrift Größe', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['intro_height'])){ $option_intro_height = ''; } else { $option_intro_height = $option['intro_height']; }
		$outputa .= '<input type="text" name="flodjishare_intro_height" value="'.stripslashes($option_intro_height).'" size="20"><br />
		<span class="description">'.__('Lege hier die Größe für die Überschrift fest (z.B. 14px, 1,4rem, 1,4em).', 'flodjishare' ).'<br /></span>
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Extras', 'flodjishare' ).':</strong></td>
		<td>
		
		<input type="checkbox" name="flodjishare_active_sharebar" '.$active_sharebar.'> '
		. __('Mobile Sharebar', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Aktiviert die mobile Sharebar (mit Facebook, Twitter, Google Plus und Whatsapp).", 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_opengraph" '.$active_opengraph.'> '
		. __('Opengraph Support', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Diese Tags werden z.B. von Facebook zum Teilen von Beiträgen ausgelesen.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_richsnippets" '.$active_richsnippets.'> '
		. __('Rich Snippets Support', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__("Diese Tags werden von Suchmaschinen zum Indexieren und z.B. von Google Plus zum Teilen von Beitr&auml;gen ausgelesen.", 'flodjishare' ).'</small></span><br />

		<input type="checkbox" name="flodjishare_active_twittercards" '.$active_twittercards.'> '
		. __('Twitter Card Support', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Diese Tags werden von Twitter zum Teilen von Beiträgen ausgelesen.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_metabox" '.$active_metabox.'> '
		. __('Metaboxen aktivieren', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Im Editor wird eine Metabox zum Anpassen der Opengraph, Rich Snippets und Twitter Cards Meta Tags angezeigt.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_metadesc" '.$active_metadesc.'> '
		. __('Meta Description Tag aktivieren', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Aktiviert das Meta Description Tag.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_news_keywords" '.$active_news_keywords.'> '
		. __('News Keyword Tag aktivieren', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Aktiviert das News Keyword Tag für Google News.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_meta_keywords" '.$active_meta_keywords.'> '
		. __('Meta Keywords Tag aktivieren', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Aktiviert das Meta Keywords Tag.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_privacy" '.$active_privacy.'> '
		. __('Datenschutzhinweis anzeigen', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Zeigt einen kleinen Hoverlink mit Datenschutzhinweisen unter den Share Buttons an. Der Hinweistext muss weiter unten noch eingegeben werden.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_googleAuthor" '.$active_gplusauthor.'> '
		. __('Google Authorship Markup', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Fügt das Google Authorship Markup Tag für die Anzeige von Autorenfotos in den Suchergebnissen in den Quellcode ein.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_post_stats" '.$active_post_stats.'> '
		. __('Post Statistiken', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Zeigt die Anzahl der Seitenaufrufe und die Gesamtzahl der Shares pro Seite an.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_fs_hit_column" '.$active_fs_hit_column.'> '
		. __('Seitenaufrufe in Beitrags- / Seitenübersicht anzeigen', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Zeigt die Anzahl der Seitenaufrufe pro Beitrag / Seite in der Übersicht an.', 'flodjishare' ).'</small></span><br />
		
		<input type="checkbox" name="flodjishare_active_ffde_widget" '.$active_ffde_widget.'> '
		. __('Found-Footage.de Widget', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Aktiviert ein Widget mit aktuellen Beiträgen aus dem Found-Footage.de Feed.', 'flodjishare' ).'</small></span><br /><br />
		
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Datenbank Präfix', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['dbprfx'])){ $option_dbprfx = ''; } else { $option_dbprfx = $option['dbprfx']; }
		$outputa .= '<input type="text" name="flodjishare_dbprfx" value="'.stripslashes($option_dbprfx).'" size="20"><br />
		<span class="description">'.__('Trage hier den Datenbank Präfix für den flodjiShare Klickcounter ein.', 'flodjishare' ).'</span><br /><br />
		<input type="checkbox" name="flodjishare_active_prfx" '.$active_prfx.'> '
		. __('Stattdessen Wordpress Präfix aktivieren', 'flodjishare' ).' &nbsp;&nbsp;<br />
		<span class="description"><small>'.__('Nutzt den aktuellen Wordpress Datenbank Präfix anstatt den oben angegebenen.', 'flodjishare' ).'</small></span><br />
		<span class="description"><small>'.__('Wird diese Option nicht aktiviert oder oben kein Präfix eingetragen nutzt flodjiShare keinen Präfix. Wenn flodjiShare schon länger genutzt wird empfehle ich weder den von Wordpress genutzten, noch einen individuellen Präfix anzugeben, ansonsten werden alle Zähler wieder auf 0 gestellt.)', 'flodjishare' ).'</small></span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Startseite Beschreibung', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['desc_text'])){ $option_desc_text = ''; } else { $option_desc_text = $option['desc_text']; }
		$outputa .= '<textarea name="flodjishare_desc_text" value="'.stripslashes($option['desc_text']).'" cols="50" rows="5">'.stripslashes($option_desc_text).'</textarea><br />
		<span class="description">'.__('Trage hier den Text für deie Meta Description der Startseite ein.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Startseite Keywords', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['keywords'])){ $option_keywords = ''; } else { $option_keywords = $option['keywords']; }
		$outputa .= '<input type="text" name="flodjishare_keywords" value="'.stripslashes($option_keywords).'" size="50"><br />
		<span class="description">'.__('Trage hier die Keywords für Deine Startseite ein (Bitte alle Wörter klein schreiben und durch Kommas trennen.)', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Google Plus Page ID', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['gplusidpage'])){ $option_gplusidpage = ''; } else { $option_gplusidpage = $option['gplusidpage']; }
		$outputa .= '<input type="text" name="flodjishare_gplus_page" value="'.stripslashes($option_gplusidpage).'" size="50"><br />
		<span class="description">'.__('Trage hier die ID Deiner Google Plus Seite ein.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Google Plus User ID', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['gplusiduser'])){ $option_gplusiduser = ''; } else { $option_gplusiduser = $option['gplusiduser']; }
		$outputa .= '<input type="text" name="flodjishare_gplus_user" value="'.stripslashes($option_gplusiduser).'" size="50"><br />
		<span class="description">'.__('Trage hier die ID Deines pers&ouml;nlichen Google Plus Accounts ein.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Flattr ID', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['flattr_id'])){ $option_flattr_id = ''; } else { $option_flattr_id = $option['flattr_id']; }
		$outputa .= '<input type="text" name="flodjishare_flattr_id" value="'.stripslashes($option_flattr_id).'" size="50"><br />
		<span class="description">'.__('Trage hier Deine Flattr ID ein. Diese wird für den Flattr Button benötigt.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Twitter Name', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['twitter_text'])){ $option_twitter_text = ''; } else { $option_twitter_text = $option['twitter_text']; }
		$outputa .= '<input type="text" name="flodjishare_twitter_text" value="'.stripslashes($option_twitter_text).'" size="50"><br />
		<span class="description">'.__('Trage hier Deinen Twitter Usernamen ein. Dieser wird dann in den Twitter Cards (wenn aktiviert)<br />und am Ende der Tweets erscheinen, z.B. (via @Dein Twitter Name).', 'flodjishare' ).'</span><br />
		</td></tr>

		<tr><td valign="top"><strong>'.__('Twitter Seite', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['twitsite'])){ $option_twitsite = ''; } else { $option_twitsite = $option['twitsite']; }
		$outputa .= '<input type="text" name="twitsite" value="'.stripslashes($option_twitsite).'" size="50"><br />
		<span class="description">'.__('Trage hier den Twitter Usernamen Deiner Worspress Seite ein. Falls nicht vorhanden, trage einfach Deinen Twitter Usernamen ein.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Ersatzbild', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['altimg'])){ $option_altimg = ''; } else { $option_altimg = $option['altimg']; }
		$outputa .= '<input type="text" name="altimg" value="'.stripslashes($option_altimg).'" size="50"><br />
		<span class="description">'.__('Trage hier die URL zu einem Ersatzbild ein. Dieses wird beim Teilen verwendet, wenn im Artikel kein Bild vorhanden ist.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Facebook AppId', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['fb_app_id'])){ $option_fb_app_id = ''; } else { $option_fb_app_id = $option['fb_app_id']; }
		$outputa .= '<input type="text" name="flodjishare_fb_app_id" value="'.stripslashes($option_fb_app_id).'" size="50"><br />
		<span class="description">'.__('Trage hier Deine Facebook AppId ein.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Facebook Admin', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['fb_admin'])){ $option_fb_admin = ''; } else { $option_fb_admin = $option['fb_admin']; }
		$outputa .= '<input type="text" name="flodjishare_fb_admin" value="'.stripslashes($option_fb_admin).'" size="50"><br />
		<span class="description">'.__('Trage hier Deinen Facebook Usernamen ein.', 'flodjishare' ).'</span><br />
		</td></tr>		
			
		<tr><td valign="top"><strong>'.__('Datenschutzhinweistext', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">';
		if(is_array($option['privacy_text'])){ $option_privacy_text = ''; } else { $option_privacy_text = $option['privacy_text']; }
		$outputa .= '<textarea name="flodjishare_privacy_text" value="'.stripslashes($option_privacy_text).'" cols="50" rows="5">'.stripslashes($option_privacy_text).'</textarea><br />
		<span class="description">'.__('Trage hier den Datenschutzhinweistext ein.', 'flodjishare' ).'</span><br />
		</td></tr>
		
		<tr><td valign="top"><strong>'.__('Supportlink', 'flodjishare' ).':</strong></td>
		<td style="padding-bottom:20px;">
		<input type="checkbox" name="flodjishare_active_supportlink" '.$active_supportlink.'> '
		. __('Supportlink deaktivieren', 'flodjishare' ).'&nbsp;&nbsp;<br /></td></tr>
		
		</table>
		<hr />
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="'.esc_attr( __('Speichern', 'flodjishare')).'" />
		</p>
		</form>
		' . __('Bei Problemen oder Fragen kannst Du gern das', 'flodjishare') . ' <a target="_blank" href="http://flodji.de/forum/">' . __('Support Forum', 'flodjishare') . '</a> ' . __('besuchen', 'flodjishare') . '.</p>
		<p>' . __('Die Buttons stammen von dieser', 'flodjishare') . ' <a target="_blank" href="http://www.sitepackage.de/news/aktuell/social-media-icons-30-logos-download.html">' . __('Seite', 'flodjishare') . '</a> (flattr und Digg habe ich auf <a target="_blank" href="http://iconfinder.com">iconfinder.com</a> gefunden) | ' . __('Das Menü-Icon habe ich', 'flodjishare') . ' <a target="_blank" href="http://salleedesign.com/">' . __('hier', 'flodjishare') . '</a> ' . __('gefunden', 'flodjishare') . '. | ' . __('Das Metro Design habe ich ausschließlich mit CSS Anweisungen erstellt', 'flodjishare') . '</p>
	</div>';
	$outputa .= '<div style="margin-left:50px;border-left:thin solid #ccc;border-right:thin solid #ccc;border-bottom:thin solid #ccc;padding:3px;width:200px;float:left;box-shadow: 0 1px 1px #999;">
<div>
<a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=flodji.de_Logo&utm_campaign=flodjiShareWP"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/flodjidelogo03.png" width="180"/></a><h2>' . __('flodji.de Feed', 'flodjishare') . '</h2>';
$rss = fetch_feed( "http://flodji.de/feed/" );
if(!is_wp_error($rss)){
$maxitems = $rss->get_item_quantity( 5 ); 
$rss_items = $rss->get_items( 0, $maxitems );
}
$outputa .= '<ul>';
if($maxitems == 0){
$outputa .= '<li>' . __('Keine Einträge', 'flodjishare') . '</li>';
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
<a target="_blank" href="http://found-footage.de/?utm_source=flodjiShareWP&utm_medium=ff_Logo&utm_campaign=flodjiShareWP"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/ff_logo.png" width="180"/></a><h2>' . __('found-footage.de Feed', 'flodjishare') . '</h2>';
$rss = fetch_feed( "http://found-footage.de/feed/" );
if(!is_wp_error($rss)){
$maxitems = $rss->get_item_quantity( 5 ); 
$rss_items = $rss->get_items( 0, $maxitems );
}
$outputa .= '<ul>';
if($maxitems == 0){
$outputa .= '<li>' . __('Keine Einträge', 'flodjishare') . '</li>';
} else {
foreach ($rss_items as $item){
$outputa .= '<li>
    <a href="'.esc_url($item->get_permalink()).'?utm_source=flodjiShareWP&utm_medium=FeedLink&utm_campaign=flodjiShareWP" title="'.esc_html( $item->get_title() ).'" target="_blank">';
$outputa .= esc_html( $item->get_title() );
$outputa .= '</a></li>';
}
}
$outputa .= '<hr /><li><a href="http://found-footage.de/newsletter-informationen/?utm_source=flodjiShareWP&utm_medium=FeedLink&utm_campaign=flodjiShareWP" target="_blank">' . __('Newsletter', 'flodjishare') . '</a></li>';
$outputa .= '</ul>'.followMeFlodjiShareff().'
</div>

<div>
<h2>' . __('Weitere Links', 'flodjishare') . '</h2>
<ul>
<li><a target="_blank" href="http://flodji.de/downloads/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Weitere Plugins / Themes', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/fragen-und-antworten/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('FAQs', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/kontakt/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Kontakt', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/forum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Forum', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/impressum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Impressum', 'flodjishare') . '</a></li><hr />
<li><a target="_blank" href="http://found-footage.de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">found-footage.de</a></li><li><a target="_blank" href="http://fotodosis.de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">fotodosis.de</a></li><li><a target="_blank" href="http://larp-anfaenger.de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">larp-anfaenger.de</a></li>
</ul>
</div>
</div>';
	echo $outputa;
}

function flodjiShareKlickCounter(){
global $wpdb;
$option_string = get_option('flodjishare');
$option = json_decode($option_string, true);
$host = "$wpdb->dbhost";
$user = "$wpdb->dbuser";
$pass = "$wpdb->dbpassword";
$dbase = "$wpdb->dbname";
if($option['active_buttons']['prfx']==true){$dbprfx = $wpdb->prefix.'flodjiShareLinks';} elseif($option['dbprfx'] != ''){ $dbprfx = stripslashes($option['dbprfx']).'flodjiShareLinks'; } else { $dbprfx = 'flodjiShareLinks'; }
$connection = mysqli_connect("$host" , "$user" , "$pass" , "$dbase")  
              OR die ("Keine Verbindung zu der Datenbank moeglich."); 
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS $dbprfx (`id` int(255) NOT NULL auto_increment,
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
$feldfilter = $_GET['feld'];
$start 		= $seite * $eintraege_pro_seite - $eintraege_pro_seite;
if(!empty($feldfilter)){
$ergebnis 	= mysqli_query($connection, "SELECT title,network,klicks FROM $dbprfx WHERE network='$feldfilter' ORDER BY $sort LIMIT $start, $eintraege_pro_seite" );
} else {
$ergebnis 	= mysqli_query($connection, "SELECT title,network,klicks FROM $dbprfx ORDER BY $sort LIMIT $start, $eintraege_pro_seite" );
}
$anz_reihe 	= mysqli_num_rows( $ergebnis );
$anz_felde 	= mysqli_num_fields( $ergebnis );
$ausgabe = '<div style="width:600px;float:left;"><h2>' . __('flodjiShare Klickzähler', 'flodjishare') . '</h2><table><tr><td style="width:200px;">'.followMeFlodjiShare().'</td><td>'.spendPayPalFlodjiShare().'</td></tr></table>';
$ausgabe .= "<br><br>";
$ausgabe .= '<table style="border:1px solid #000;" bgcolor="white"><tr>';
$tablename = mysqli_fetch_fields($ergebnis);
foreach ($tablename as $rowname) $ausgabe .= '<th style="border:1px solid #000;"><b><a href="admin.php?page=klick-counter&sort='.$rowname->name.'&feld='.$feldfilter.'">' . strtoupper($rowname->name) . '</a></b></th>';
$ausgabe .= "</tr>";
while ( $datensatz = mysqli_fetch_row( $ergebnis ) )
    {
    $ausgabe .= "<tr>\n";
    foreach ( $datensatz as $feld ) {
		$netzwerke = array('Facebook','Twitter','Google Plus','Whatsapp','Xing','Flattr','Pocket','Feedly','Digg','Delicious','LinkedIn','Pinterest','StumbleUpon','Tumblr');
        if(in_array($feld, $netzwerke)){
		$ausgabe .= '<td style="border:1px solid #000;"><a href="admin.php?page=klick-counter&sort='.$sort.'&seite='.$seite.'&feld='.$feld.'" title="' . __('Nur Einträge mit diesem Netzwerk anzeigen.', 'flodjishare') . '">'.$feld.'</a></td>';
		} else {
		$ausgabe .= '<td style="border:1px solid #000;">'.$feld.'</td>';
		}
		}
    $ausgabe .= "</tr>\n";
    }
$ausgabe .= "</table>\n";
if(!empty($feldfilter)){
$result 		= mysqli_query($connection, "SELECT title,network,klicks FROM $dbprfx WHERE network='$feldfilter' ORDER BY title");
} else {
$result 		= mysqli_query($connection, "SELECT title,network,klicks FROM flodjiShareLinks ORDER BY title");
}
$menge 			= mysqli_num_rows($result);
$mengr			= $menge[0];
$wieviel_seiten = ceil($menge / $eintraege_pro_seite); 
$ausgabe .= '<br /><div align="center">';
$ausgabe .= '<strong>' . __('Seite', 'flodjishare') . ':</strong>';
$ausgabe .= blaetterfunktion($seite,$wieviel_seiten,'admin.php?page=klick-counter&sort='.$sort.'&feld='.$feldfilter,3);
$ausgabe .= '<p style="text-align:left;">' . __('Nach diesen Netzwerken filtern:', 'flodjishare') . '<br /><a href="admin.php?page=klick-counter&feld=Facebook">' . __('Facebook', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Twitter">' . __('Twitter', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Google Plus">' . __('Google Plus', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Digg">' . __('Digg', 'flodjishare' ). '</a>   | <a href="admin.php?page=klick-counter&feld=Delicious">' . __('Delicious', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Xing">' . __('Xing', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=LinkedIn">' . __('LinkedIn', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Pinterest">' . __('Pinterest', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=StumbleUpon">' . __('StumbleUpon', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Flattr">' . __('Flattr', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Tumblr">' . __('Tumblr', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Pocket">' . __('Pocket', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Feedly">' . __('Feedly', 'flodjishare' ). '</a> | <a href="admin.php?page=klick-counter&feld=Whatsapp">' . __('Whatsapp', 'flodjishare' ). '</a></p>';
$ausgabe .= '</div><br /><p><small><a href="admin.php?page=klick-counter&sort=&seite=&feld=">Filter zurücksetzen</a></small></p>';
$ausgabe .= '<br /><p><span style="font-weight: bold;">' . __('Hinweis', 'flodjishare') . ':</span><br />' . __('Der Klickzähler zeigt die Anzahl der Klicks auf die Share Buttons an. Diese Zahl muss nicht zwingend mit der tatsächlichen Anzahl von Shares übereinstimmen.', 'flodjishare') . '</p></div>';
$ausgabe .= '<div style="margin-left:50px;border-left:thin solid #ccc;border-right:thin solid #ccc;border-bottom:thin solid #ccc;padding:3px;width:200px;float:left;box-shadow: 0 1px 1px #999;">
<div>
<a target="_blank" href="http://flodji.de/?utm_source=flodjiShareWP&utm_medium=flodji.de_Logo&utm_campaign=flodjiShareWP"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/flodjidelogo03.png" width="180"/></a><h2>flodji.de Feed</h2>';
$rss = fetch_feed( "http://flodji.de/feed/" );
if(!is_wp_error($rss)){
$maxitems = $rss->get_item_quantity( 5 ); 
$rss_items = $rss->get_items( 0, $maxitems );
}
$ausgabe .= '<ul>';
if($maxitems == 0){
$ausgabe .= '<li>' . __('Keine Einträge', 'flodjishare') . '</li>';
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
<a target="_blank" href="http://found-footage.de/?utm_source=flodjiShareWP&utm_medium=ff_Logo&utm_campaign=flodjiShareWP"><img src="'.site_url().'/wp-content/plugins/flodjishare/buttons/ff_logo.png" width="180"/></a><h2>' . __('found-footage.de Feed', 'flodjishare') . '</h2>';
$rss = fetch_feed( "http://found-footage.de/feed/" );
if(!is_wp_error($rss)){
$maxitems = $rss->get_item_quantity( 5 ); 
$rss_items = $rss->get_items( 0, $maxitems );
}
$ausgabe .= '<ul>';
if($maxitems == 0){
$ausgabe .= '<li>' . __('Keine Einträge', 'flodjishare') . '</li>';
} else {
foreach ($rss_items as $item){
$ausgabe .= '<li>
    <a href="'.esc_url($item->get_permalink()).'?utm_source=flodjiShareWP&utm_medium=FeedLink&utm_campaign=flodjiShareWP" title="'.esc_html( $item->get_title() ).'" target="_blank">';
$ausgabe .= esc_html( $item->get_title() );
$ausgabe .= '</a></li>';
}
}
$ausgabe .= '<hr /><li><a href="http://found-footage.de/newsletter-informationen/?utm_source=flodjiShareWP&utm_medium=FeedLink&utm_campaign=flodjiShareWP" target="_blank">' . __('Newsletter', 'flodjishare') . '</a></li>';
$ausgabe .= '</ul>'.followMeFlodjiShareff().'
</div>

<div>
<h2>Weitere Links</h2>
<ul>
<li><a target="_blank" href="http://flodji.de/downloads/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Weitere Plugins / Themes', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/fragen-und-antworten/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('FAQs', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/kontakt/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Kontakt', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/forum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Forum', 'flodjishare') . '</a></li>
<li><a target="_blank" href="http://flodji.de/impressum/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">' . __('Impressum', 'flodjishare') . '</a></li><hr />
<li><a target="_blank" href="http://found-footage.de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">found-footage.de</a></li><li><a target="_blank" href="http://fotodosis.de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">fotodosis.de</a></li><li><a target="_blank" href="http://larp-anfaenger.de/?utm_source=flodjiShareWP&utm_medium=Weitere_Links&utm_campaign=flodjiShareWP">larp-anfaenger.de</a></li>
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
      if(!in_array(($maxseite-1),$blaetter)) $return .= "...&nbsp;<a href=\"{$url}{$anhang}{$get_name}=$maxseite\">" . __('Letzte', 'flodjishare') . "</a>&nbsp;";
      else $return .= "&nbsp;<a href=\"{$url}{$anhang}{$get_name}=$maxseite\">$maxseite</a>&nbsp;";
      }

   if(empty($return))
      return  "&nbsp;<b>1</b>&nbsp;";
   else
      return $return;
}
?>
<?php
class ffde_widget extends WP_Widget {

function __construct(){
parent::__construct('ffde_widget', __('Found-Footage.de', 'ffde_widget_domain'), array( 'description' => __( 'Zeigt den Found-Footage.de RSS Feed an.' ), ));
}

public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];
$ausgabe = '';
$ausgabe .= '<div>';
$rss = fetch_feed( "http://found-footage.de/feed/" );
if(!is_wp_error($rss)){
$maxitems = $rss->get_item_quantity( $instance['quantity'] ); 
$rss_items = $rss->get_items( 0, $maxitems );
}
$ausgabe .= '<ul style="padding-left:15px;">';
if($maxitems == 0){
$ausgabe .= '<li>' . __('Keine Einträge', 'flodjishare') . '</li>';
} else {
foreach ($rss_items as $item){
$ausgabe .= '<li style="border-bottom:1px solid #ccc;">
    <a style="font-weight:700 ! important;margin-bottom:3px;" href="'.esc_url($item->get_permalink()).'" title="'.esc_html( $item->get_title() ).'" target="_blank" rel="nofollow">';
$ausgabe .= esc_html( $item->get_title() );
$ausgabe .= '</a><br />' . strip_tags($item->get_description()) . '</li>';
}
}
$ausgabe .= '</ul>
</div><div style="clear:both;"></div>';
echo $ausgabe;
echo $args['after_widget'];
}
		
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'Found-Footage.de Feed', 'ffde_widget_domain' );
}
if ( isset( $instance[ 'quantity' ] ) ) {
$quantity = $instance[ 'quantity' ];
}
else {
$quantity = '5';
}

?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
<label for="<?php echo $this->get_field_id( 'quantity' ); ?>"><?php _e( 'Max-Items:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'quantity' ); ?>" name="<?php echo $this->get_field_name( 'quantity' ); ?>" type="text" value="<?php echo esc_attr( $quantity ); ?>" />
</p>
<?php 
}
	
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
$instance['quantity'] = ( ! empty( $new_instance['quantity'] ) ) ? strip_tags( $new_instance['quantity'] ) : '';
return $instance;
}
}

function ffde_load_widget() {
$option_string 		= get_option('flodjishare');
$option 			= json_decode($option_string, true);
if($option['ffde_widget']==true){
	register_widget( 'ffde_widget' );
}
}
add_action( 'widgets_init', 'ffde_load_widget' );
?>