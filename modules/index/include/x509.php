<?php

require_once('inc/security.php');

function get_logins_by_cert($cert) 
{
	$cert = mysql_real_escape_string(str_replace(array('-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', ' ', "\n"), array(), $cert));
	$query = "SELECT type,username,startpage FROM system.clientcert WHERE cert='{$cert}'";
	$result = db_query($query);
	if (mysql_num_rows($result) < 1)
		return NULL;
	else {
		$ret = array();
		while ($row = mysql_fetch_assoc($result)) {
			$ret[] = $row;
		}
		return $ret;
	}
}

function get_cert_by_id($id) 
{
  $id = (int) $id;
	if ($id == 0)
	  system_failure('no ID');
	$query = "SELECT id,dn,issuer,cert,username,startpage FROM system.clientcert WHERE `id`='{$id}' LIMIT 1";
	$result = db_query($query);
	if (mysql_num_rows($result) < 1)
		return NULL;
	$ret = mysql_fetch_assoc($result);
  DEBUG($ret);
  return $ret;
}


function get_certs_by_username($username) 
{
	$username = mysql_real_escape_string($username);
	if ($username == '')
	  system_failure('empty username');
	$query = "SELECT id,dn,issuer,cert,startpage FROM system.clientcert WHERE `username`='{$username}'";
	$result = db_query($query);
	if (mysql_num_rows($result) < 1)
		return NULL;
	while ($row = mysql_fetch_assoc($result)) {
	  $ret[] = $row;
	}
	return $ret;
}


function add_clientcert($certdata, $dn, $issuer, $startpage='')
{
  $type = NULL;
  $username = NULL;
  if ($_SESSION['role'] == ROLE_SYSTEMUSER) {
    $type = 'user';
    $username = mysql_real_escape_string($_SESSION['userinfo']['username']);
    if (isset($_SESSION['subuser']))
      $username = mysql_real_escape_string($_SESSION['subuser']);
      $type = 'subuser';
  } elseif ($_SESSION['role'] == ROLE_VMAIL_ACCOUNT) {
    $type = 'email';
    $username = mysql_real_escape_string($_SESSION['mailaccount']);
  }
  if (! $type || ! $username) {
    system_failure('cannot get type or username of login');
  }
  $certdata = mysql_real_escape_string($certdata);
  $dn = maybe_null(mysql_real_escape_string($dn));
  $issuer = maybe_null(mysql_real_escape_string($issuer));
  if ($startpage &&  ! check_path($startpage))
    system_failure('Startseite kaputt');
  $startpage = maybe_null(mysql_real_escape_string($startpage));

  if ($certdata == '')
    system_failure('Kein Zertifikat');
  DEBUG($certdata);
  DEBUG($dn);
  DEBUG($issuer);

  db_query("INSERT INTO system.clientcert (`dn`, `issuer`, `cert`, `type`, `username`, `startpage`) 
VALUES ({$dn}, {$issuer}, '{$certdata}', '{$type}', '{$username}', {$startpage})");

}


function delete_clientcert($id)
{
  $id = (int) $id;
  $type = NULL;
  $username = NULL;
  if ($_SESSION['role'] == ROLE_SYSTEMUSER) {
    $type = 'user';
    $username = mysql_real_escape_string($_SESSION['userinfo']['username']);
    if (isset($_SESSION['subuser']))
      $username = mysql_real_escape_string($_SESSION['subuser']);
      $type = 'subuser';
  } elseif ($_SESSION['role'] == ROLE_VMAIL_ACCOUNT) {
    $type = 'email';
    $username = mysql_real_escape_string($_SESSION['mailaccount']);
  }
  if (! $type || ! $username) {
    system_failure('cannot get type or username of login');
  }
  db_query("DELETE FROM system.clientcert WHERE id={$id} AND type='{$type}' AND username='{$username}' LIMIT 1");
}

