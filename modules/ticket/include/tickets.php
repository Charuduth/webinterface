<?php

require_once('inc/base.php');
require_once('inc/debug.php');


function list_tickets() 
{
  $customerno = (int) $_SESSION['customerinfo']['customerno'];
  $result = db_query("SELECT id, subject, created, closed, status, severity, priority FROM misc.tickets WHERE customer='{$customerno}'");
  $ret = array();
  while ($line = mysql_fetch_assoc($result))
    $ret[] = $line;

  DEBUG($ret);
  return $ret;
}


function ticket_details($id)
{
  $id = (int) $id;
  $customerno = (int) $_SESSION['customerinfo']['customerno'];
  $result = db_query("SELECT id, subject, created, closed, status, severity, priority FROM misc.tickets WHERE customer='{$customerno}' AND id={$id}");
  if (mysql_num_rows($result) < 1)
    system_failure("Sie haben kein Ticket mit dieser ID");
  $ticket = mysql_fetch_assoc($result);
  $result = db_query("SELECT id, ticket, date, author, direction, text FROM misc.tickets_msg WHERE ticket={$ticket['id']}");
  if (mysql_num_rows($result) < 1)
    system_failure("Es konnten keine Nachrichten zu diesem Ticket gefunden werden");
  $ticket['answers'] = array();
  while ($a = mysql_fetch_assoc($result))
    $ticket['answers'][] = $a;

  DEBUG($ticket);
  return $ticket;
}

