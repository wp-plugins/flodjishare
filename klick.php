<?php
define( 'ABSPATH', $_SERVER["DOCUMENT_ROOT"] . '/' ); 
require ( ABSPATH . 'wp-config.php' );
$url = $_GET['fsurl'];
$db = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD );
if ( !is_resource( $db ) ) {
    handleSqlError();
}
if ( !mysql_select_db( DB_NAME, $db ) ) {
    handleSqlError();
}
$safe_url = "'" . mysql_real_escape_string( $url ) . "'";
$querya			= "CREATE TABLE IF NOT EXISTS flodjiShareLinks (`id` int(255) NOT NULL auto_increment,
									`klicks` varchar(100) NOT NULL,
									`title` varchar(255) NOT NULL,
									`network` varchar(200) NOT NULL,
									PRIMARY KEY  (`id`))
									ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 AUTO_INCREMENT=73";
$execute		= mysql_query( $querya, $db );
$title = $_GET['title'];
if($title == ''){
$title = $_GET['t'];
}
$network = $_GET['n'];
$query = "UPDATE flodjiShareLinks SET klicks=klicks+1 WHERE title='$title' AND network='$network'"; 
$result = mysql_query( $query, $db );
if ( mysql_affected_rows( $db ) == 0 ) {
    $query = "INSERT INTO flodjiShareLinks (title, network, klicks) VALUES ('$title', '$network', 1)";
    mysql_query( $query, $db );
}
header( "Location: " . $url );
?>