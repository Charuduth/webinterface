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

require_once("class/domain.php");
require_once("domains.php");
require_once("domainapi.php");
require_role(ROLE_CUSTOMER);
check_form_token('domains_domainreg');

if (! (isset($_SESSION['domains_domainreg_owner']) && $_SESSION['domains_domainreg_owner']) ||
    ! (isset($_SESSION['domains_domainreg_admin_c']) && $_SESSION['domains_domainreg_admin_c']) ||
    ! (isset($_SESSION['domains_domainreg_domainname']) && $_SESSION['domains_domainreg_domainname'])) {
    system_failure("Fehler im Programmablauf!");
}

if (! (isset($_REQUEST['domain']) && $_REQUEST['domain']) ||
    $_REQUEST['domain'] != $_SESSION['domains_domainreg_domainname']) {
    system_failure("Fehler im Programmablauf!");
}
// Validierung der Domain entfällt hier, weil wir nur bestehende Domain aus der Datenbank laden. Bei ungültiger Eingabe wird kein Treffer gefunden.
$dom = new Domain((string) $_REQUEST['domain']);
$dom->ensure_userdomain();

// Speichere Kontakte
domain_ownerchange($dom->fqdn, $_SESSION['domains_domainreg_owner'], $_SESSION['domains_domainreg_admin_c']);

$authinfo = null;
if ($dom->status == 'pretransfer') {
    if (! (isset($_REQUEST['authinfo']) && $_REQUEST['authinfo'])) {
        system_failure("Kein Auth-Info-Code angegeben!");
    }
    $authinfo = chop($_REQUEST['authinfo']);
}

api_register_domain($dom->fqdn, $authinfo);

success_msg('Die Registrierung wurde in Auftrag gegeben. Der Domain-Status sollte sich in den nächsten Minuten entsprechend ändern.');

unset($_SESSION['domains_domainreg_owner']);
unset($_SESSION['domains_domainreg_admin_c']);
unset($_SESSION['domains_domainreg_detach']);
unset($_SESSION['domains_domainreg_domainname']);

redirect('domains');
