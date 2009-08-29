<?php

require_once("certs.php");
require_role(ROLE_SYSTEMUSER);

$section = 'vhosts_certs';

if ($_GET['action'] == 'new')
{
  check_form_token('vhosts_certs_new');
  $cert = $_POST['cert'];
  $key = $_POST['key'];
  if (! isset($_POST['key']) && isset($_REQUEST['csr']))
  {
    $csr = csr_details($_REQUEST['csr']);
    $key = $csr['key'];
  }
  $oldcert = NULL;
  if ($_REQUEST['replace'])
  {
    $oldcert = cert_details($_REQUEST['replace']);
  }

  if (! $cert or ! $key)
    system_failure('Es muss ein Zertifikat und der dazu passende private Schlüssel eingetragen werden');

  $result = validate_certificate($cert, $key);
  switch ($result)
  {
    case CERT_OK:
      $certinfo = parse_cert_details($cert);
      if ($oldcert)
        refresh_cert($oldcert['id'], $certinfo, $cert, $key);
      else
        save_cert($certinfo, $cert, $key);
      if (isset($_REQUEST['csr']))
        delete_csr($_REQUEST['csr']);
      header('Location: certs');
      die();
      break;
    case CERT_INVALID:
      system_failure("Das Zertifikat konnte nicht gelesen werden. Eventuell ist der private Schlüssel mit einem Paswort versehen?");
      break;
    case CERT_NOCHAIN:
      warning('Ihr Zertifikat konnte nicht mit einer Zertifikats-Kette validiert werden. Dies wird zu Problemen beim Betrachten der damit betriebenen Websites führen. Meist liegt dies an einem nicht hinterlegten CA-Bundle. Die Admins können Ihr Zertifikats-Bundle auf dem System eintragen. Das Zertifikat wurde dennoch gespeichert.');
      $certinfo = parse_cert_details($cert);
      if ($oldcert)
        refresh_cert($oldcert['id'], $certinfo, $cert, $key);
      else
        save_cert($certinfo, $cert, $key);
      output('<p>'.internal_link('certs', 'Zurück zur Übersicht').'</p>');
      if (isset($_REQUEST['csr']))
        delete_csr($_REQUEST['csr']);
      break;
  }

}
elseif ($_GET['action'] == 'refresh')
{
  check_form_token('vhosts_certs_refresh');
  $cert = $_POST['cert'];
  $oldcert = cert_details($_REQUEST['id']);
  $key = $oldcert['key'];
  $id = (int) $_REQUEST['id'];

  if (! $cert )
    system_failure('Es muss ein Zertifikat eingetragen werden');

  $result = validate_certificate($cert, $key);
  switch ($result)
  {
    case CERT_OK:
      $certinfo = parse_cert_details($cert);
      if ($certinfo['cn'] != $oldcert['cn'])
        system_failure("Das neue Zertifikat enthält abweichende Daten. Legen Sie bitte ein neues Zertifikat an.");

      refresh_cert($id, $certinfo, $cert);
      header('Location: certs');
      die();
      break;
    case CERT_INVALID:
      system_failure("Das Zertifikat konnte nicht gelesen werden. Eventuell ist es nicht wirklich eine neue Version des bisherigen Zertifikats.");
      break;
    case CERT_NOCHAIN:
      warning('Ihr Zertifikat konnte nicht mit einer Zertifikats-Kette validiert werden. Dies wird zu Problemen beim Betrachten der damit betriebenen Websites führen. Meist liegt dies an einem nicht hinterlegten CA-Bundle. Die Admins können Ihr Zertifikats-Bundle auf dem System eintragen. Das Zertifikat wurde dennoch gespeichert.');
      $certinfo = parse_cert_details($cert);
      if ($certinfo['cn'] != $oldcert['cn'])
        system_failure("Das neue Zertifikat enthält abweichende Daten. Legen Sie bitte ein neues Zertifikat an.");

      refresh_cert($id, $certinfo, $cert);
      output('<p>'.internal_link('certs', 'Zurück zur Übersicht').'</p>');
      break;
  }

}
elseif ($_GET['action'] == 'delete')
{
  $cert = cert_details($_GET['id']);
  $sure = user_is_sure();
  if ($sure === NULL)
  {
    are_you_sure("action=delete&id={$cert['id']}", "Soll das Zertifikat für »{$cert['subject']}« (gültig von {$cert['valid_from']} bis {$cert['valid_until']}) wirklich entfernt werden?");
  }
  elseif ($sure === false)
  {
    header('Location: certs');
    die();
  }
  elseif ($sure === true)
  { 
    delete_cert($cert['id']);
    header('Location: certs');
    die();
  }
}
elseif ($_GET['action'] == 'deletecsr')
{
  $csr = csr_details($_GET['id']);
  $sure = user_is_sure();
  if ($sure === NULL)
  {
    are_you_sure("action=deletecsr&id={$csr['id']}", "Soll der CSR für »{$csr['hostname']}« ({$csr['bits']} Bits, erstellt am {$csr['created']}) wirklich entfernt werden?");
  }
  elseif ($sure === false)
  {
    header('Location: certs');
    die();
  }
  elseif ($sure === true)
  { 
    delete_csr($csr['id']);
    header('Location: certs');
    die();
  }
}
elseif ($_GET['action'] == 'newcsr')
{
  $cn = $_POST['commonname'];
  $bitlength = $_POST['bitlength'];
  
  $wildcard = ! (count(explode('.', $cn)) > 2);
  $id = save_csr($cn, $bitlength, $wildcard);

  header("Location: showcert?mode=csr&id={$id}");
  die();
}
else
{
  system_failure('not implemented');
}



