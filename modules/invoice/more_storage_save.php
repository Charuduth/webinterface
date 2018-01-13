<?php
/*
This file belongs to the Webinterface of schokokeks.org Hosting

Written 2008-2018 by schokokeks.org Hosting, namely
  Bernd Wurst <bernd@schokokeks.org>
  Hanno Böck <hanno@schokokeks.org>

To the extent possible under law, the author(s) have dedicated all copyright and related and neighboring rights to this software to the public domain worldwide. This software is distributed without any warranty.

You should have received a copy of the CC0 Public Domain Dedication along with this software. If not, see 
http://creativecommons.org/publicdomain/zero/1.0/

Nevertheless, in case you use a significant part of this code, we ask (but not require, see the license) that you keep the authors' names in place and return your changes to the public. We would be especially happy if you tell us what you're going to do with this code.
*/

require_once('session/start.php');

require_once('invoice.php');

require_role(ROLE_CUSTOMER);

$section = $_SESSION['more_storage_section'];

title('Zusätzlichen Speicherplatz buchen');


check_form_token('more_storage');

$valid = false;
if (isset($_POST['more_storage_handle']) && isset($_SESSION['more_storage_handle']) &&
  $_POST['more_storage_handle'] == $_SESSION['more_storage_handle']) {
  $valid = true;
}
if (!$valid) {
  system_failure("Ungültige Session");
}

if (!isset($_SESSION['more_storage_items']) || !isset($_SESSION['more_storage_count'])) {
  system_failure('Keine Daten');
}

$items = $_SESSION['more_storage_items'];
$count = $_SESSION['more_storage_count'];

if (count($items) < 2) {
  system_failure("Ungültige Daten");
}

$stub = $items[0];
if ($stub['anzahl'] == 0) {
  $stub = NULL;
}
$regular = $items[1];

$clean_items = array();

if ($stub) {
  $i = array();
  $i['beschreibung'] = $stub['beschreibung'];
  $i['datum'] = $stub['startdatum'];
  $i['kuendigungsdatum'] = $stub['enddatum'];
  $i['betrag'] = $stub['betrag'];
  $i['brutto'] = $stub['brutto'];
  $i['monate'] = $stub['anzahl'];
  $i['anzahl'] = $stub['anzahl'];
  $clean_items[] = $i;
}

$i = array();
$i['beschreibung'] = $regular['beschreibung'];
$i['datum'] = $regular['startdatum'];
$i['kuendigungsdatum'] = NULL;
$i['betrag'] = $regular['betrag'];
$i['brutto'] = $regular['brutto'];
$i['monate'] = $regular['anzahl'];
$i['anzahl'] = $regular['anzahl'];
$clean_items[] = $i;

save_more_storage($clean_items, $count);

if (have_module('systemuser')) {
  require('modules/systemuser/include/useraccounts.php');
  $useraccounts = list_useraccounts();
  if (isset($_POST['more_storage_user'])) {
    foreach ($useraccounts as $u) {
      if ($u['uid'] == $_POST['more_storage_user']) {
        $account = get_account_details($u['uid']);
        $account['quota'] = $account['quota'] + $count;
        set_account_details($account);
      }
    }
  }
  unset($_SESSION['more_storage_user']);
}


unset($_SESSION['more_storage_handle']);
unset($_SESSION['more_storage_items']);
unset($_SESSION['more_storage_count']);

if (isset($_SESSION['more_storage_section'])) {
  DEBUG('Weiterleitung zu: '.$prefix.'go/'.str_replace('_', '/', $_SESSION['more_storage_section']));
  redirect($prefix.'go/'.str_replace('_', '/', $_SESSION['more_storage_section']));
} else {
  redirect($prefix.'go/invoice/current');
}

?>
