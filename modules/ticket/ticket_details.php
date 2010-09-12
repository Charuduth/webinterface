<?php

require_once('inc/base.php');
require_once('inc/security.php');
require_once('tickets.php');

$t = ticket_details($_REQUEST['t']);
$answers = $t['answers'];

$title = 'Support-Tickets';

output('<h3>'.filter_input_general($t['subject']).'</h3>');

$status = 'unbekannt';
switch ($t['status'])
{
  case 'new':
      $status = 'neu';
      break;
  case 'open':
      $status = 'offen';
      break;
  case 'closed':
      $status = 'erledigt';
      break;
}
output('<div style="margin-left: 2em;">
<p>Datum: '.$answers[0]['date'].'</p>
<p>'.filter_input_general($answers[0]['text']).'</p>
<p>Status: <strong>'.$status.'</strong></p>
</div>');

unset($answers[0]);

if (count($answers) > 0)
{
  output('<h3>Antworten:</h3>');
  foreach ($answers as $a)
  {
    output("<h4>{$a['author']}, {$a['date']}</h4>");
    output("<div style=\"margin-left: 2em;\"><p>{$a['text']}</p></div>");
  }
}
else
{
  output('<p><em>Bisher sind keine Antworten zu diesem Ticket vorhanden</em></p>');
}

