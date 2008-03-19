<?php

require_once('inc/base.php');
require_once('inc/security.php');

function my_invoices()
{
  $c = (int) $_SESSION['customerinfo']['customerno'];
  $result = db_query("SELECT id,datum,betrag,bezahlt,abbuchung FROM kundendaten.ausgestellte_rechnungen WHERE kunde={$c}");
  $ret = array();
  while($line = mysql_fetch_assoc($result))
  	array_push($ret, $line);
  return $ret;
}


function get_pdf($id)
{
  $c = (int) $_SESSION['customerinfo']['customerno'];
  $id = (int) $id;
  $result = db_query("SELECT pdfdata FROM kundendaten.ausgestellte_rechnungen WHERE kunde={$c} AND id={$id}");
  if (mysql_num_rows($result) == 0)
	system_failure('Ungültige Rechnungsnummer oder nicht eingeloggt');
  return mysql_fetch_object($result)->pdfdata;

}


function invoice_details($id)
{
  $c = (int) $_SESSION['customerinfo']['customerno'];
  $id = (int) $id;
  $result = db_query("SELECT kunde,datum,betrag,bezahlt,abbuchung FROM kundendaten.ausgestellte_rechnungen WHERE kunde={$c} AND id={$id}");
  if (mysql_num_rows($result) == 0)
	system_failure('Ungültige Rechnungsnummer oder nicht eingeloggt');
  return mysql_fetch_assoc($result);
}

function invoice_items($id)
{
  $c = (int) $_SESSION['customerinfo']['customerno'];
  $id = (int) $id;
  $result = db_query("SELECT id, beschreibung, datum, enddatum, betrag, brutto, mwst, anzahl FROM kundendaten.rechnungsposten WHERE rechnungsnummer={$id} AND kunde={$c}");
  if (mysql_num_rows($result) == 0)
	system_failure('Ungültige Rechnungsnummer oder nicht eingeloggt');
  $ret = array();
  while($line = mysql_fetch_assoc($result))
  array_push($ret, $line);
  return $ret;
}


function upcoming_items()
{
  $c = (int) $_SESSION['customerinfo']['customerno'];
  $result = db_query("SELECT anzahl, beschreibung, startdatum, enddatum, betrag, brutto, mwst FROM kundendaten.upcoming_items WHERE kunde={$c} ORDER BY startdatum ASC");
  $ret = array();
  while($line = mysql_fetch_assoc($result))
	  array_push($ret, $line);
  return $ret;
}


?>