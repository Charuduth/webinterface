<?php

require_once('inc/debug.php');

require_once('webapp-installer.php');


function validate_data($post)
{
  DEBUG('Validating Data:');
  DEBUG($post);
  $fields = array('adminuser', 'adminpassword', 'adminemail', 'wikiname', 'dbhandle');
  foreach ($fields AS $field)
    if ((! isset($post[$field])) || $post[$field] == '')
      system_failure('Nicht alle Werte angegeben ('.$field.')');

  $username = mysql_real_escape_string($_SESSION['userinfo']['username']);
  $dbname = $username.'_'.$post['dbhandle'];
  $dbpassword = create_webapp_mysqldb($post['dbhandle']);

  $salt = random_string(8);
  $salthash = ':B:' . $salt . ':' . md5( $salt . '-' . md5( $post['adminpassword'] ));
  
  $data = "adminuser={$post['adminuser']}
adminpassword={$salthash}
adminemail={$post['adminemail']}
wikiname={$post['wikiname']}
dbname={$dbname}
dbuser={$dbname}
dbpass={$dbpassword}";
  DEBUG($data);
  return $data;
}

