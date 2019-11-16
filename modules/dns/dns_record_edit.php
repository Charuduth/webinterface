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

require_once('inc/base.php');
require_once('inc/security.php');

require_once('class/domain.php');

require_role(ROLE_SYSTEMUSER);

require_once('dnsinclude.php');

$section = 'dns_dns';

$data = array();
$type = null;

$dyndns = false;
$dyndns_accounts = array();
foreach (get_dyndns_accounts() as $t) {
    $dyndns_accounts[$t['id']] = $t['handle'];
}

if (isset($_REQUEST['type']) && $_REQUEST['type'] == "dyndns") {
    $_REQUEST['type'] = 'a';
    $dyndns = true;
}
if (isset($_REQUEST['type']) && $_REQUEST['type'] == "dyndns_aaaa") {
    $_REQUEST['type'] = 'aaaa';
    $dyndns = true;
}


$new = false;
if ($_REQUEST['id'] == 'new') {
    $new = true;
    $data = blank_dns_record($_REQUEST['type']);
    $domain = new Domain((int) $_REQUEST['domain']);
    $domain->ensure_userdomain();
    $type = $_REQUEST['type'];
    if (! in_array($type, $valid_record_types)) {
        system_failure('Ungültiger Record-Typ!');
    }
    $data['domain'] = $domain->id;
    if ($dyndns) {
        $data['ttl'] = 120;
    }
}

if (! $new) {
    $data = get_dns_record($_REQUEST['id']);
    $type = $data['type'];
    $dyndns = isset($data['dyndns']);
    $domain = new Domain((int) $data['domain']);
    $domain->ensure_userdomain();
    if (! in_array($type, $valid_record_types)) {
        system_failure('Ungültiger Record-Typ!');
    }
}


if ($new) {
    title('DNS-Record erstellen');
} else {
    title('DNS-Record bearbeiten');
}

if (strtoupper($type) == 'NS') {
    output('<p style="border: 2px solid red; padding: 1em; padding-left: 4em;"><img src="'.$prefix.'images/warning.png" style="margin-left: -3em; float: left;" /><strong>Bitte beachten Sie:</strong> Das Ändern der DNS-Server für die Stammdomain wird nicht funktionieren. Bitte geben Sie unbedingt einen Hostname ein um eine Subdomain auf einen anderen DNS-Server zu delegieren.</p>');
}

if (strtoupper($type) == 'MX' && domain_is_maildomain($domain->id)) {
    output('<p style="border: 2px solid red; padding: 1em; padding-left: 4em;"><img src="'.$prefix.'images/warning.png" style="margin-left: -3em; float: left;" /><strong>Bitte beachten Sie:</strong> Wenn Sie die Mail-Verarbeitung auf Servern von '.$config['company_name'].' nicht nutzen möchten, sollten Sie <a href="'.$prefix.'go/email/domains">die lokale Mail-Verarbeitung für diese Domain ausschalten</a>.</p>');
}

output('<p style="border: 2px solid red; padding: 1em; padding-left: 4em;"><img src="'.$prefix.'images/warning.png" style="margin-left: -3em; float: left;" /><strong>Bitte beachten Sie:</strong> Um Ihnen auch ungewöhniche Konstellationen zu ermöglichen, erlaubt dieses Webinterface sehr großzügige Eintragungen, die eventuell nicht plausibel sind oder vom DNS-Server gar nicht so verstanden werden können. Wir können sicherheitskritische Einträge herausfiltern, jedoch nicht logische Fehler automatisch erkennen. Im Fehlerfall wird meistens Ihre gesamte Domain vom DNS-Server ausgeschlossen, so lange sich Fehler in der Konfiguration befinden. Sollten Sie hier also fehlerhafte Eintragungen machen, kann dies die Erreichbarkeit der betreffenden Domain im Ganzen stören.</p>');


output('<p>Record-Typ: '.strtoupper($type).'</p>');

$submit = 'Speichern';
if ($new) {
    $submit = 'Anlegen';
}

$form = '';

if (! $dyndns && ($type == 'a' || $type == 'aaaa')) {
    $form .= '
<tr><td><label for="ip">IP-Adresse:</label></td><td><input type="text" name="ip" id="ip" value="'.$data['ip'].'" /></td></tr>
';
}

if ($type == 'ns') {
    $form .= '
<tr><td><label for="data">DNS-Server:</label></td><td><input type="text" name="data" id="data" value="'.$data['data'].'" /></td></tr>
';
}

if ($type == 'ptr' || $type == 'cname') {
    $form .= '
<tr><td><label for="data">Ziel:</label></td><td><input type="text" name="data" id="data" value="'.$data['data'].'" /></td></tr>
';
}

if ($type == 'spf' || $type == 'txt') {
    $form .= '
<tr><td><label for="data">Inhalt:</label></td><td><input type="text" name="data" id="data" value="'.filter_input_general($data['data']).'" /></td></tr>
';
}

if ($type == 'sshfp') {
    $algs = array(
    1 => "RSA",
    2 => "DSA",
    3 => "ECDSA",
    4 => "ED25519"
    5 => "XMSS");
    $option="";
    foreach ($algs as $key => $alg) {
        $option .= '<option value="'.$key.'" ';
        if ($key == $data['spec']) {
            $option .= 'selected="selected"';
        }
        $option .= '>'.$alg.' ('.$key.')</option>';
    }

    $form .= '
<tr><td><label for="spec">Algorithmus:</label></td><td><select name="spec" id="spec">'.$option.'</select></td></tr>
<tr><td><label for="data">Fingerabdruck:</label></td><td><input type="text" name="data" id="data" value="'.$data['data'].'" /></td></tr>
';
}

if ($type == 'caa') {
    $option="";
    foreach ($caa_properties as $key => $property) {
        $option .= '<option value="'.$key.'" ';
        if ($key == $data['spec']) {
            $option .= 'selected="selected"';
        }
        $option .= '>'.$property.' ('.$key.')</option>';
    }
    $form .= '
<tr><td><label for="spec">Property tag:</label></td><td><select name="spec" id="spec">'.$option.'</select></td></tr>
<tr><td><label for="data">Inhalt:</label></td><td><input type="text" name="data" id="data" value="'.$data['data'].'" /></td></tr>
';
}

if ($dyndns) {
    $form .= '
<tr><td><label for="dyndns">DynDNS-Zugang:</label></td><td>'.html_select('dyndns', $dyndns_accounts, $data['dyndns']).'</td></tr>
';
}

if ($type == 'mx') {
    $form .= '
<tr><td><label for="spec">Priorität:</label></td><td><input type="text" name="spec" id="spec" value="'.$data['spec'].'" /></td></tr>
<tr><td><label for="data">Posteingangsserver:</label></td><td><input type="text" name="data" id="data" value="'.$data['data'].'" /></td></tr>
';
}


output(html_form('dns_record_edit', 'dns_record_save', "type={$type}&domain={$domain->id}&id={$_REQUEST['id']}", '<table>
<tr><td><label for="hostname">Hostname:</label></td><td><input type="text" name="hostname" id="hostname" value="'.$data['hostname'].'" />&#160;<strong>.'.$domain->fqdn.'</strong></td></tr>
'.$form.'
<tr><td><label for="ttl">TTL:</label></td><td><input type="text" name="ttl" id="ttl" value="'.$data['ttl'].'" /></td></tr>
</table>
<p><input type="submit" value="'.$submit.'" /></p>
'));
