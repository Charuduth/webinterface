<?php

require_once("inc/base.php");
require_role(ROLE_CUSTOMER);

$title="Neues Ticket";

output("<p>Mit einem Suport-Ticket können Sie uns eine Nachricht zukommen lassen bzw. um Hilfe bei Problemen bitten.</p>");

$form = html_form("new_ticket", "save.php", "action=new", "...");


