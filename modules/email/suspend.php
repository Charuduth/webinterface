<?php
/*
This file belongs to the Webinterface of schokokeks.org Hosting

Written 2008-2014 by schokokeks.org Hosting, namely
  Bernd Wurst <bernd@schokokeks.org>
  Hanno Böck <hanno@schokokeks.org>

To the extent possible under law, the author(s) have dedicated all copyright and related and neighboring rights to this software to the public domain worldwide. This software is distributed without any warranty.

You should have received a copy of the CC0 Public Domain Dedication along with this software. If not, see 
http://creativecommons.org/publicdomain/zero/1.0/

Nevertheless, in case you use a significant part of this code, we ask (but not require, see the license) that you keep the authors' names in place and return your changes to the public. We would be especially happy if you tell us what you're going to do with this code.
*/

require_once('inc/debug.php');
require_once('inc/security.php');
require_once('inc/icons.php');

require_once('vmail.php');

$section = 'email_vmail';
require_role(array(ROLE_SYSTEMUSER));

if (! isset($_REQUEST['account'])) {
  system_failure("Fehler beim Aufruf dieser Seite");
}
$id = $_REQUEST['account'];
$account = get_account_details($id);

$suspended = False;
if ($account['smtpreply']) {
  $suspended = True;
} else {
  $account['smtpreply'] = 'Diese E-Mail-Adresse wird nicht mehr verwendet. 

Bitte besuchen Sie unsere Website um eine aktuelle Kontaktmöglichkeit zu finden.';
}

title("E-Mail-Adresse stillegen");

output('<p>Mit dieser Funktion können Sie eine E-Mail-Adresse stillegen (so werden keine Nachrichten für diese Adresse angenommen) und dabei dem Absender einen eigenen, hier festgelegten Fehlertext zukommen lassen. Diese Methode hat nicht die Probleme, die ein klassische Autoresponder verursacht, da keine Antwort-E-Mails versendet werden. Der Absender erhält von seinem Mail-Server eine Fehlermeldung mit dem entsprechenden Text.</p>
<p><strong>Wichtig:</strong> Dieses Verfahren funktioniert nur, wenn die E-Mails wirklich nicht angenommen werden (Annahme wird verweigert), somit sind keine Weiterleitung und keine Speicherung möglich. Sie können aber natürlich im Text auf eine andere E-Mail-Adresse hinweisen.</p>');

$form = "<h4>Text der Fehlermeldung</h4>".
  "<p><textarea cols=\"80\" rows=\"10\" name=\"smtpreply\" id=\"smtpreply\">{$account['smtpreply']}</textarea></p>";

$form .= '<p><input id="submit" type="submit" value="Speichern" />&#160;&#160;&#160;&#160;'.internal_link('edit', 'Abbrechen', "id=".$id).'</p>';
output(html_form('vmail_edit_mailbox', 'save', 'action=suspend&id='.$id, $form));

if ($suspended) {
  output("<p><strong>".internal_link('save', 'Stillegung aufheben', 'action=unsuspend&id='.$account['id'])."</strong></p>");
}


?>
