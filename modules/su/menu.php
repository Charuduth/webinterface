<?php

$role = $_SESSION['role'];

if ($role & ROLE_SYSADMIN)
{
  $menu["su_su"] = array("label" => "Su-Login", "file" => "su", "weight" => -10);
}

?>
