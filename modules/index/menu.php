<?php

$role = $_SESSION['role'];

if ($role == ROLE_ANONYMOUS) {
  $menu["index_login"] = array("label" => "Login", "file" => "index", "weight" => 0);
} else {
  if ($role & (ROLE_SYSTEMUSER | ROLE_CUSTOMER)) {
    $menu["index_chpass"] = array("label" => "Passwort ändern", "file" => "chpass", "weight" => 98);
  }

  $menu["index_logout"] = array("label" => "Logout", "file" => "logout", "weight" => 99);
  $menu["index_index"] = array("label" => "Übersicht", "file" => "index", "weight" => 0);
}


?>
