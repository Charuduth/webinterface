<?php

$menu = array();

$role = $_SESSION['role'];

require_once('include/vmail.php');


if (($role & ROLE_SYSTEMUSER) && user_has_vmail_domain())
{
  $menu["vmail_accounts"] = array("label" => "E-Mail", "file" => "accounts.php", "weight" => 10);
}


if (empty($menu))
  $menu = false;


?>