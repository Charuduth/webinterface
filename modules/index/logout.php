<?php

require_once('session/start.php');

require_once('inc/error.php');

if (!session_destroy())
{
  logger(LOG_INFO, "modules/index/logout", "logout", "session timed out.");
	system_failure('Die Sitzung konnte nicht geschlossen werden, eventuell ist die Wartezeit abgelaufen und die Sitzung wurde daher schon beendet.');
}
$_SESSION['role'] = ROLE_ANONYMOUS;

logger(LOG_INFO, "modules/index/logout", "logout", "logged out");

title("Abmeldung");
output('

<p>Sie wurden vom System abgemeldet.</p>

<p>Um sich neu anzumelden, klicken Sie bitte hier: '.internal_link("index", "Anmeldung").'.</p>

');



?>
