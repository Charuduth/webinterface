<?php

require_once("inc/base.php");
require_once("inc/error.php");
require_once("inc/security.php");

require_once('class/domain.php');


function list_vhosts()
{
  $uid = (int) $_SESSION['userinfo']['uid'];
  $result = db_query("SELECT id,fqdn,docroot,docroot_is_default,php,options FROM vhosts.v_vhost WHERE uid={$uid} ORDER BY domain,hostname");
  $ret = array();
  while ($item = mysql_fetch_assoc($result))
    array_push($ret, $item);
  return $ret;
}


function empty_vhost()
{
  $vhost['hostname'] = '';
  
  $domainlist = get_domain_list($_SESSION['customerinfo']['customerno'],
                                $_SESSION['userinfo']['uid']);
  $dom = $domainlist[0];

  $vhost['domain_id'] = $dom->id;
  $vhost['domain'] = $dom->fqdn;
  
  $vhost['homedir'] = $_SESSION['userinfo']['homedir'];
  $vhost['docroot'] = NULL;
  $vhost['php'] = 'mod_php';
  $vhost['logtype'] = NULL;
    
  $vhost['options'] = '';
  return $vhost;
}


function empty_alias()
{
  $alias['hostname'] = '';
  
  $domainlist = get_domain_list($_SESSION['customerinfo']['customerno'],
                                $_SESSION['userinfo']['uid']);
  $dom = $domainlist[0];

  $alias['domain_id'] = $dom->id;
  $alias['domain'] = $dom->fqdn;
  
  $alias['options'] = '';
  return $alias;
}


function domainselect($selected = NULL, $selectattribute = '')
{
  global $domainlist;
  if ($domainlist == NULL)
    $domainlist = get_domain_list($_SESSION['customerinfo']['customerno'],
                                  $_SESSION['userinfo']['uid']);
  $selected = (int) $selected;

  $ret = '<select id="domain" name="domain" size="1" '.$selectattribute.' >';
  foreach ($domainlist as $dom)
  {
    $s = ($selected == $dom->id) ? ' selected="selected" ': '';
    $ret .= "<option value=\"{$dom->id}\"{$s}>{$dom->fqdn}</option>\n";
  }
  $ret .= '</select>';
  return $ret;
}



function get_vhost_details($id)
{
  $id = (int) $id;
  $uid = (int) $_SESSION['userinfo']['uid'];
  $result = db_query("SELECT * FROM vhosts.v_vhost WHERE uid={$uid} AND id={$id}");
  if (mysql_num_rows($result) != 1)
    system_failure('Interner Fehler beim Auslesen der Daten');

  return mysql_fetch_assoc($result);
}


function get_aliases($vhost)
{
  $result = db_query("SELECT id,fqdn,options FROM vhosts.v_alias WHERE vhost={$vhost}");
  $ret = array();
  while ($item = mysql_fetch_assoc($result)) {
    array_push($ret, $item);
  }
  return $ret;
}



function get_all_aliases($vhost)
{
  $vhost = get_vhost_details( (int) $vhost );
  $aliases = get_aliases($vhost['id']);
  $ret = array();
  if (strstr($vhost['options'], 'aliaswww')) {
    array_push($ret, array('id' => 'www', 'fqdn' => 'www.'.$vhost['fqdn'], 'options' => (strstr($vhost['options'], 'forwardwww') ? 'forward' : '')));
  }
  foreach ($aliases as $item) {
    array_push($ret, $item);
    if (strstr($item['options'], 'aliaswww')) {
      array_push($ret, array('id' => 'www_'.$item['id'], 'fqdn' => 'www.'.$item['fqdn'], 'options' => (strstr($item['options'], 'forward') ? 'forward' : '')));
    }
  }
  return $ret;
}


function delete_vhost($id)
{
  $id = (int) $id;
  if ($id == 0)
    system_failure("id == 0");
  $vhost = get_vhost_details($id);
  logger('modules/vhosts/include/vhosts.php', 'vhosts', 'Removing vhost #'.$id.' ('.$vhost['hostname'].'.'.$vhost['domain'].')');
  db_query("DELETE FROM vhosts.vhost WHERE id={$vhost['id']} LIMIT 1");
}


function save_vhost($vhost)
{
  if (! is_array($vhost))
    system_failure('$vhost kein array!');
  $id = (int) $vhost['id'];
  $hostname = maybe_null($vhost['hostname']);
  $domain = (int) $vhost['domainid'];
  if ($domain == 0)
    system_failure('$domain == 0');
  $docroot = maybe_null($vhost['docroot']);
  $php = maybe_null($vhost['php']);
  $logtype = maybe_null($vhost['logtype']);
  $options = mysql_real_escape_string( $vhost['options'] );

  if ($id != 0) {
    logger('modules/vhosts/include/vhosts.php', 'vhosts', 'Updating vhost #'.$id.' ('.$vhost['hostname'].'.'.$vhost['domain'].')');
    db_query("UPDATE vhosts.vhost SET hostname={$hostname}, domain={$domain}, docroot={$docroot}, php={$php}, logtype={$logtype}, options='{$options}' WHERE id={$id} LIMIT 1");
  }
  else {
    logger('modules/vhosts/include/vhosts.php', 'vhosts', 'Creating vhost '.$vhost['hostname'].'.'.$vhost['domain'].'');
    db_query("INSERT INTO vhosts.vhost (user, hostname, domain, docroot, php, logtype, options) VALUES ({$_SESSION['userinfo']['uid']}, {$hostname}, {$domain}, {$docroot}, {$php}, {$logtype}, '{$options}')");
  }
}


function get_alias_details($id)
{
  $id = (int) $id;
  $uid = (int) $_SESSION['userinfo']['uid'];
  $result = db_query("SELECT * FROM vhosts.v_alias WHERE id={$id}");
  
  if (mysql_num_rows($result) != 1)
    system_failure('Interner Fehler beim Auslesen der Alias-Daten');
  
  $alias = mysql_fetch_assoc($result);

  /* Das bewirkt, dass nur die eigenen Aliase gesehen werden können */
  get_vhost_details( (int) $alias['vhost'] );

  return $alias;
}


function delete_alias($id)
{
  $id = (int) $id;
  $alias = get_alias_details($id);

  logger('modules/vhosts/include/vhosts.php', 'aliases', 'Removing alias #'.$id.' ('.$alias['hostname'].'.'.$alias['domain'].')');
  db_query("DELETE FROM vhosts.alias WHERE id={$id}");
}

function save_alias($alias)
{
  if (! is_array($alias))
    system_failure('$alias kein array!');
  $id = (int) $alias['id'];
  $hostname = maybe_null($alias['hostname']);
  $domain = (int) $alias['domainid'];
  if ($domain == 0)
    system_failure('$domain == 0');
  $vhost = get_vhost_details( (int) $alias['vhost']);
  $options = mysql_real_escape_string( $alias['options'] );
  if ($id == 0) {
    logger('modules/vhosts/include/vhosts.php', 'aliases', 'Creating alias '.$alias['hostname'].'.'.$alias['domain'].' for VHost '.$vhost['id']);
    db_query("INSERT INTO vhosts.alias (hostname, domain, vhost, options) VALUES ({$hostname}, {$domain}, {$vhost['id']}, '{$options}')");
  }
  else {
    logger('modules/vhosts/include/vhosts.php', 'aliases', 'Updating alias #'.$id.' ('.$alias['hostname'].'.'.$alias['domain'].')');
    db_query("UPDATE vhosts.alias SET hostname={$hostname}, domain={$domain}, options='{$options}' WHERE id={$id} LIMIT 1");
  }
}



?>