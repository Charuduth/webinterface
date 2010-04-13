<?php

$role = $_SESSION['role'];

if ($role & ROLE_SYSTEMUSER)
{
    $menu["vhosts_vhosts"] = array("label" => "Webserver", "file" => "vhosts", "weight" => 2);
    $menu["vhosts_certs"] = array("label" => "SSL-Zertifikate", "file" => "certs", "weight" => 10, "submenu" => "vhosts_vhosts");
    $menu["vhosts_stats"] = array("label" => "Zugriffs-Statistiken", "file" => "stats", "weight" => 12, "submenu" => "vhosts_vhosts");
}

?>
