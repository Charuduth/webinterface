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
require_once('inc/jquery.php');
javascript();

require_once('vhosts.php');
require_once('certs.php');

$section = 'vhosts_vhosts';

require_role(ROLE_SYSTEMUSER);

$id = (isset($_GET['vhost']) ? (int) $_GET['vhost'] : 0);
$vhost = empty_vhost();

if ($id != 0)
  $vhost = get_vhost_details($id);

$have_v6 = false;
$server = (isset($vhost['server']) ? $vhost['server'] : $_SESSION['userinfo']['server']);
if (ipv6_possible($server))
  $have_v6 = true;

DEBUG($vhost);
if ($id == 0) {
  title("Neue Subdomain anlegen");
}
else {
  title("Subdomain bearbeiten");
}

$defaultdocroot = $vhost['domain'];
if (! $vhost['domain']) {
  $defaultdocroot = $_SESSION['userinfo']['username'].'.'.config('masterdomain');
}
if ($vhost['domain_id'] == -1) {
  $defaultdocroot = $_SESSION['userinfo']['username'].'.'.config('user_vhosts_domain');
}
if ($vhost['hostname'])
  $defaultdocroot = $vhost['hostname'].'.'.$defaultdocroot;

$defaultdocroot = $defaultdocroot.'/htdocs';

$is_default_docroot = ($vhost['docroot'] == NULL) || ($vhost['homedir'].'/websites/'.$defaultdocroot == $vhost['docroot']);

if ($vhost['docroot'] != '' && ! strstr($vhost['docroot'], '/websites/'))
{
  warning("Sie verwenden einen Speicherplatz außerhalb von »~/websites/«. Diese Einstellung ist momentan nicht mehr gestattet. Ihre Einstellung wurde daher auf die Standardeinstellung zurückgesetzt. Prüfen Sie dies bitte und verschieben Sie ggf. ihre Dateien.");
  $is_default_docroot = True;
}

$docroot = '';
if ($is_default_docroot)
  $docroot = $defaultdocroot;
else
  $docroot = substr($vhost['docroot'], strlen($vhost['homedir'].'/websites/'));

$s = (strstr($vhost['options'], 'aliaswww') ? ' checked="checked" ' : '');
$errorlog = ($vhost['errorlog'] == 1 ? ' checked="checked" ' : '');

$vhost_type = 'regular';
if ($vhost['is_dav'])
  $vhost_type = 'dav';
elseif ($vhost['is_svn'])
  $vhost_type = 'svn';
elseif ($vhost['is_webapp'])
  $vhost_type = 'webapp';

$applist = list_available_webapps();
$webapp_options = '';
foreach ($applist as $app)
  $webapp_options .= "<option value=\"{$app['id']}\">{$app['displayname']}</option>\n";


$form = "
<h4 style=\"margin-top: 2em;\">Name des VHost</h4>
    <div style=\"margin-left: 2em;\"><input type=\"text\" name=\"hostname\" id=\"hostname\" size=\"10\" value=\"{$vhost['hostname']}\" /><strong>.</strong>".domainselect($vhost['domain_id']);
$form .= "<br /><input type=\"checkbox\" name=\"options[]\" id=\"aliaswww\" value=\"aliaswww\" {$s}/> <label for=\"aliaswww\">Auch mit <strong>www</strong> davor.</label></div>

<div class=\"vhostsidebyside\">
<div class=\"vhostoptions\" id=\"options_docroot\" ".($vhost_type=='regular' || $vhost_type=='dav' ? '' : 'style="display: none;"').">
  <h4>Optionen</h4>
  <h5>Speicherort für Dateien (»Document Root«)</h5>
  <div style=\"margin-left: 2em;\">
    <input type=\"checkbox\" id=\"use_default_docroot\" name=\"use_default_docroot\" value=\"1\" ".($is_default_docroot ? 'checked="checked" ' : '')."/>&#160;<label for=\"use_default_docroot\">Standardeinstellung benutzen</label><br />
    <strong>".$vhost['homedir']."/websites/</strong>&#160;<input type=\"text\" id=\"docroot\" name=\"docroot\" size=\"30\" value=\"".$docroot."\" ".($is_default_docroot ? 'disabled="disabled" ' : '')."/>
  </div>
</div>
";

/*
 * Boolean option, to be used when only one PHP version is available
 */
$have_php = ($vhost['php'] == 'php56' ? ' checked="checked" ' : '');

/*
$phpoptions = "<h5>PHP</h5>
  <div style=\"margin-left: 2em;\">
    <input type=\"checkbox\" name=\"php\" id=\"php\" value=\"php53\" {$have_php}/>&#160;<label for=\"php\">PHP einschalten</label>
  </div>
";
*/
/*
 * Choose what PHP version to use
 */
//if ($vhost['php'] == 'php54')
//{
  $options = array("none" => 'ausgeschaltet', "php54" => "PHP 5.4 (veraltet)", "php55" => "PHP 5.5", "php56" => "PHP 5.6");
  $phpoptions = "
  <h5>PHP</h5>
  <div style=\"margin-left: 2em;\">
    ".html_select("php", $options, $vhost['php'])."
  </div>";
//}

$safemode = ($vhost['cgi'] == 1 ? '' : ' checked="checked" ');

$form .= "
<div class=\"vhostoptions\" id=\"options_scriptlang\" ".($vhost_type=='regular' ? '' : 'style="display: none;"').">
  ".$phpoptions."
  <h5>Abgesicherter Modus</h5>
  <div style=\"margin-left: 2em;\">
    <input type=\"checkbox\" name=\"safemode\" id=\"safemode\" value=\"yes\" {$safemode}/>&#160;<label for=\"safemode\">Abgesicherter Modus</label><br /><em>(Deaktiviert CGI, mod_rewrite und einige weitere Funktionen mit denen die Website auf andere Orte des Home-Verzeichnisses zugreifen könnte.)</em>
  </div>
</div>
";

$form .= "
<div class=\"vhostoptions\" id=\"options_webapp\" ".($vhost_type=='webapp' ? '' : 'style="display: none;"').">
  <h4>Optionen</h4>
  <h5>Anwendung</h5>
  <select name=\"webapp\" id=\"webapp\" size=\"1\">
    {$webapp_options}
  </select>
  <p>Wenn Sie diese Option wählen, wird die Anwendung automatisch eingerichtet. Sie erhalten dann ihre Zugangsdaten per E-Mail.</p>
</div>
";

$form .= "
<h4>Verwendung</h4>
        <div style=\"margin-left: 2em;\">
	  <input class=\"usageoption\" type=\"radio\" name=\"vhost_type\" id=\"vhost_type_regular\" value=\"regular\" ".(($vhost_type=='regular') ? 'checked="checked" ' : '')."/><label for=\"vhost_type_regular\">&#160;Normal (selbst Dateien hinterlegen)</label><br />
";
if ($vhost_type=='webapp')
{
  // Wird nur noch angezeigt wenn der Vhost schon auf webapp konfiguriert ist, ansonsten nicht.
  // Die User sollen den Webapp-Installer benutzen.
  $form .= "
	  <input class=\"usageoption\" type=\"radio\" name=\"vhost_type\" id=\"vhost_type_webapp\" value=\"webapp\" ".(($vhost_type=='webapp') ? 'checked="checked" ' : '')."/><label for=\"vhost_type_webapp\">&#160;Eine vorgefertigte Applikation nutzen</label><br />
";
}
$hsts_value = $vhost['hsts'];
$hsts_preset_values = array("-1" => "aus", "86400" => "1 Tag", "2592000" => "30 Tage", "31536000" => "1 Jahr", "63072000" => "2 Jahre", "custom" => "Individuell");
$hsts_preset_value = 'custom';
if (isset($hsts_preset_values[$hsts_value])) {
  $hsts_preset_value = $hsts_value;
}
$form .= "
	  <input class=\"usageoption\" type=\"radio\" name=\"vhost_type\" id=\"vhost_type_dav\" value=\"dav\" ".(($vhost_type=='dav') ? 'checked="checked" ' : '')."/><label for=\"vhost_type_dav\">&#160;WebDAV</label><br />
	  <input class=\"usageoption\" type=\"radio\" name=\"vhost_type\" id=\"vhost_type_svn\" value=\"svn\" ".(($vhost_type=='svn') ? 'checked="checked" ' : '')."/><label for=\"vhost_type_svn\">&#160;Subversion-Server</label>
	</div>
<br />
<br />
<br />
</div>

<h4 style=\"clear: right; margin-top: 3em;\">Allgemeine Optionen</h4>
<div style=\"margin-left: 2em;\">
    <h5>SSL-Verschlüsselung</h5>
    <div style=\"margin-left: 2em;\">
    <select name=\"ssl\" id=\"ssl\">
      <option value=\"none\" ".($vhost['ssl'] == NULL ? 'selected="selected"' : '')." >Normal</option>
      ".($vhost['ssl'] == 'http' ? "<option value=\"http\" selected=\"selected\">kein SSL</option>" : '')."
      ".($vhost['ssl'] == 'https' ? "<option value=\"https\" selected=\"selected\">nur SSL</option>" : '')."
      <option value=\"forward\" ".($vhost['ssl'] == 'forward' ? 'selected="selected"' : '')." >Immer auf SSL umleiten</option>
    </select>  <span id=\"hsts_block\" style=\"padding-top: 0.2em;\"> <label for=\"hsts\"><a title=\"Mit HSTS können Sie festlegen, dass eine bestimmte Website niemals ohne Verschlüsselung aufgerufen werden soll. Zudem werden Zertifikate strenger geprüft.\" href=\"http://de.wikipedia.org/wiki/Hypertext_Transfer_Protocol_Secure#HSTS\">HSTS</a>:</label> ".html_select('hsts_preset', $hsts_preset_values, $hsts_preset_value)." <span id=\"hsts_seconds\"><input type=\"text\" name=\"hsts\" id=\"hsts\" size=\"10\" style=\"text-align: right;\" value=\"{$hsts_value}\" /> Sekunden</span>
    </span>
    </div>
    <h5>Logfiles <span class=\"warning\">*</span></h5>
    <div style=\"margin-left: 2em;\">
      <select name=\"logtype\" id=\"logtype\">
        <option value=\"none\" ".($vhost['logtype'] == NULL ? 'selected="selected"' : '')." >keine Logfiles</option>
        <option value=\"anonymous\" ".($vhost['logtype'] == 'anonymous' ? 'selected="selected"' : '')." >anonymisiert</option>
        <option value=\"default\" ".($vhost['logtype'] == 'default' ? 'selected="selected"' : '')." >vollständige Logfile</option>
      </select><br />
      <input type=\"checkbox\" id=\"errorlog\" name=\"errorlog\" value=\"1\" ".($vhost['errorlog'] == 1 ? ' checked="checked" ' : '')." />&#160;<label for=\"errorlog\">Fehlerprotokoll (error_log) einschalten</label><br />
      <input type=\"checkbox\" id=\"stats\" name=\"stats\" value=\"1\" ".($vhost['stats'] != NULL ? ' checked="checked" ' : '')." />&#160;<label for=\"stats\">Statistiken/Auswertungen erzeugen</label>
    </div>
    <p>Logfiles werden unter <b>/var/log/apache2/".$_SESSION['userinfo']['username']."</b> abgelegt.</p>
</div>
    ";

$ipaddrs = user_ipaddrs();
$certs = user_certs();
$available_users = available_suexec_users();
$available_servers = additional_servers();
$available_servers[] = my_server_id();
$available_servers = array_unique($available_servers);

$selectable_servers = array();
$all_servers = server_names();
foreach ($all_servers as $id => $fqdn) {
  if (in_array($id, $available_servers)) {
    $selectable_servers[$id] = $fqdn;
  }
}
if (!$vhost['server']) {
  $vhost['server'] = my_server_id();
}

$extended = '';
  if (count($certs))
  {
    $certselect = array(0 => 'kein Zertifikat / System-Standard benutzen');
    foreach ($certs as $c)
    {
      $certselect[$c['id']] = $c['subject'];
    }
    $extended .= "
      <h5>verwendetes SSL-Zertifikat</h5>
      <div style=\"margin-left: 2em;\">
      ".html_select('cert', $certselect, $vhost['certid'])."
      </div>";
  }
  if (count($ipaddrs))
  {
    $ipselect = array(0 => 'System-Standard');
    foreach ($ipaddrs AS $i)
    {
      $ipselect[$i] = $i;
    }
    $extended .= "
      <h5>IP-Adresse</h5>
      <div style=\"margin-left: 2em;\">
      ".html_select('ipv4', $ipselect, $vhost['ipv4'])."
      </div>";
  } 
  if (count($available_users))
  {
    $userselect = array(0 => 'Eigener Benutzeraccount');
    foreach ($available_users AS $u)
    {
      $userselect[$u['uid']] = $u['username'];
    }
    $extended .= "
      <h5>SuExec-Benutzeraccount</h5>
      <div style=\"margin-left: 2em;\">
      ".html_select('suexec_user', $userselect, $vhost['suexec_user'])."
      </div>";
  }
  if (count($available_servers) > 1)
  {
    $extended .= "
      <h5>Einrichten auf Server</h5>
      <div style=\"margin-left: 2em;\">
      ".html_select('server', $selectable_servers, $vhost['server'])."
      </div>";

  }
if ($have_v6) 
{
  $ipv6_address = '';
  if ($vhost['id'] && ($vhost['autoipv6'] >0 ))
    $ipv6_address = '<strong>IPv6-Adresse dieser Subdomain:</strong> '.autoipv6_address($vhost['id'], $vhost['autoipv6']);
  $checked = ($vhost['autoipv6'] > 0) ? ' checked="checked"' : '';
  $checked2 = ($vhost['autoipv6'] == 2) ? ' checked="checked"' : '';
  $extended .= '<h5>IPv6</h5>
<div style="margin-left: 2em;">
<input type="checkbox" name="ipv6" id="ipv6" value="yes" '.$checked.'/>&#160;<label for="ipv6">Auch über IPv6 erreichbar machen</label><br />
<input type="checkbox" name="ipv6_separate" id="ipv6_separate" value="yes" '.$checked2.'/>&#160;<label for="ipv6_separate">Für diese Website eine eigene IPv6-Adresse reservieren</label><br />
'.$ipv6_address.'
</div>';
}
if ($extended)
  $form .= "
<h4 style=\"margin-top: 3em;\">Erweiterte Optionen</h4>
<div style=\"margin-left: 2em;\">
".$extended."</div>";




$form .= '
  <p><input type="submit" value="Speichern" />&#160;&#160;&#160;&#160;'.internal_link('vhosts', 'Abbrechen').'</p>
  <p class="warning"><span class="warning">*</span>Es ist im Moment fraglich, ob die Speicherung von Logfiles mit IP-Adressen auf Webservern
  zulässig ist. Wir weisen alle Nutzer darauf hin, dass sie selbst dafür verantwortlich sind, bei geloggten Nutzerdaten die
  Seitenbesucher darauf hinzuweisen. Wir empfehlen, wenn möglich, Logfiles abzuschalten oder anonymes Logging einzusetzen.</p>
';
output(html_form('vhosts_edit_vhost', 'save', 'action=edit&vhost='.$vhost['id'], $form));


?>
