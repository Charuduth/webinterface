<?php

$role = $_SESSION['role'];


if ($role & ROLE_CUSTOMER)
{
  $menu["dns_dns"] = array("label" => "DNS-Einträge", "file" => "dns.php", "weight" => 1, "submenu" => "domains_domains");
  $menu["dns_dyndns"] = array("label" => "DynDNS", "file" => "dyndns.php", "weight" => 2, "submenu" => "domains_domains");
}

?>
