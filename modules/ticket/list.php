<?php

require_once('inc/base.php');
require_once('inc/security.php');
require_once('tickets.php');

$title = 'Support-Tickets';

output('<h3>Support-Tickets</h3>
<p>Über dieses Ticket-System ist es Ihnen möglich, Anfragen oder Mitteilungen an den Support zu richten. An dieser Stelle sehen Sie immer den aktuellen Stand Ihrer Anfrage.</p>
');

output('<h4>Bisher vorhandene Tickets</h4>');

$tickets = list_tickets();

if (count($tickets) > 0)
{
  output('<table><tr><th>Betreff</th><th>Datum</th><th>Status</th></tr>');
  foreach ($tickets as $t)
  {
    $date = $t['created'];
    $status = 'offen';
    output('<tr><td>'.internal_link('ticket_details', filter_input_general($t['subject']), "t={$t['id']}")."</td><td>{$date}</td><td>{$status}</td>");
  }
  output('</table>');
}
else
{
  output('<p><em>Bisher sind keine Tickets vorhanden</em></p>');
}


