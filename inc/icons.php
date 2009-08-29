<?php

require_once('inc/base.php');

function icon_warning($title = '')
{
  global $prefix;
  return "<img src=\"{$prefix}images/warning.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}



function icon_enabled($title = '')
{
  global $prefix;
  return "<img src=\"{$prefix}images/ok.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}


function icon_disabled($title = '')
{
  global $prefix;
  return "";
  //return "<img src=\"{$prefix}images/disabled.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}


function icon_ok($title = '')
{
  global $prefix;
  return "<img src=\"{$prefix}images/ok.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}



function icon_error($title = '')
{
  global $prefix;
  return "<img src=\"{$prefix}images/error.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}

function icon_pwchange($title = '')
{
  global $prefix;
  return "<img src=\"{$prefix}images/pwchange.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}


function icon_add($title = '')
{
  global $prefix;
  return "<img src=\"{$prefix}images/add.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}



function icon_delete($title = '')
{
  global $prefix;
  return "<img src=\"{$prefix}images/delete.png\" style=\"height: 16px; width: 16px;\" alt=\"{$title}\" title=\"{$title}\" />";
}



