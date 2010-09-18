<?php

require_once('config.php');
require_once('inc/debug.php');
require_once('inc/db_connect.php');
require_once("inc/base.php");
require_once("inc/theme.php");


$go = $_GET['go'];

/*
 sanitize $go
*/

// filenames can end with .php
if ( substr( $go, strlen( $go ) - 4 ) == '.php' ) {
  $go = substr( $go, 0, strlen($go) - 4);
}

DEBUG($go);

if (strstr($go, "..") or strstr($go, "./") or strstr($go, ":") or (! file_exists("modules/$go.php")) or (! is_file("modules/$go.php")))
{
  die("illegal command");
}
list($module, $page) = explode('/', $go, 2);
$page = str_replace('/', '-', $page);
if (! in_array($module, config('modules')))
{
  die("inactive module");
}


/*
 construct prefix
*/

global $prefix;
$prefix = "../";
$count = 0;
str_replace("/", "x", $go, $count);

$prefix = $prefix.str_repeat("../", $count);


require_once('session/start.php');

$output = "";
$html_header = "";
require_once("inc/base.php");
/* setup module include path */
ini_set('include_path',ini_get('include_path').':./modules/'.$module.'/include:');

/* Look where we are (but let the module override) */
$section = str_replace("/", "_", $go);

/* Let the module work */
include("modules/".$go.".php");

if ($output)
{
  if (!isset($title)) {
    $title = '';
  }
  show_page($module, $page);
}

?>
