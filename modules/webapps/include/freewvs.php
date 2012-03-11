<?php
/*
This file belongs to the Webinterface of schokokeks.org Hosting

Written 2008-2012 by schokokeks.org Hosting, namely
  Bernd Wurst <bernd@schokokeks.org>
  Hanno Böck <hanno@schokokeks.org>

To the extent possible under law, the author(s) have dedicated all copyright and related and neighboring rights to this software to the public domain worldwide. This software is distributed without any warranty.

You should have received a copy of the CC0 Public Domain Dedication along with this software. If not, see 
http://creativecommons.org/publicdomain/zero/1.0/

Nevertheless, in case you use a significant part of this code, we ask (but not require, see the license) that you keep the authors' names in place and return your changes to the public. We would be especially happy if you tell us what you're going to do with this code.
*/

require_once('inc/base.php');


function load_results()
{
  $uid = (int) $_SESSION['userinfo']['uid'];
  $result = db_query("SELECT directory, docroot, lastcheck, appname, version, state, safeversion, vulninfo FROM qatools.freewvs_results WHERE uid={$uid}");
  $ret = array();
  while ($line = mysql_fetch_assoc($result)) {
    array_push($ret, $line);
  }
  return $ret;
}

function get_upgradeinstructions($appname) {
  $appname = mysql_real_escape_string($appname);
  $result = db_query("SELECT url FROM qatools.freewvs_upgradeinstructions WHERE appname='{$appname}' LIMIT 1");
  if (mysql_num_rows($result) > 0) {
    $tmp = mysql_fetch_array($result);
    return $tmp[0];
  }
  return NULL;
}



