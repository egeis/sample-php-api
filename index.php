<?php 
if ( !isset($_REQUEST['request']) )
{
    header("HTTP/1.0 404 Not Found");
    exit;
}

error_reporting( E_ALL );
require_once("lib/api/npa.php");
require_once("lib/api/blog.php");

$api = new NPAApi( $_REQUEST['request'], true );
$api->execute();
?>