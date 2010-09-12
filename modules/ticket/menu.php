<?php

$role = $_SESSION['role'];

if ($role & ROLE_CUSTOMER)
{
    $menu["ticket_list"] = array("label" => "Support-Tickets", "file" => "list", "weight" => 1);
}

?>
