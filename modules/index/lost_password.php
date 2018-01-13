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

title("Neues Passwort beantragen");

//require_once('inc/error.php');
//system_failure("Diese Funktion ist noch nicht fertiggestellt.");

if (isset($_POST['username']))
{
  require_once('newpass.php');
  $username = find_username($_POST['username']);
  if ($username)
  {
    if (create_token($username))
    {
      require_once('mail.php');
      require_once('inc/base.php');
      send_user_token($username);
      logger(LOG_INFO, "modules/index/lost_password", "pwrecovery", "token sent for customer »{$_POST['username']}/{$username}«");
    }
  }
  success_msg('Sofern die eingegebenen Daten korrekt waren, erhalten Sie umgehend eine E-Mail.');
}

output('<p>Wenn Sie Ihr Benutzer-Passwort nicht mehr kennen, können Sie hier ein neues Passwort beantragen. Sie müssen dafür Ihren Benutzernamen kennen. Kennen Sie diese Daten nicht, wenden Sie sich bitte <a href="mailto:'.config('adminmail').'">an die Administratoren</a>.</p>
<p>Nach dem Ausfüllen dieses Formulars erhalten Sie eine E-Mail an die bei uns hinterlegte E-Mail-Adresse. Diese Mail enthält einem Link, den Sie in Ihrem Browser öffnen müssen. Dort können Sie dann ein neues Passwort eingeben.</p>
<p><span style="font-weight: bold;">Hinweis:</span> Sie können auf diesem Weg nur das Passwort des Hauptbenutzers neu anfordern. Sind Sie Mitbenutzer eines anderen Kunden, dann kann dieser Ihr Passwort neu setzen.</p>
<form action="" method="post">
<p><span class="login_label">Benutzername:</span> <input type="text" name="username" size="30" /></p>
<p><span class="login_label">&#160;</span> <input type="submit" value="Passwort anfordern" /></p>
</form>');



?>
