<?php
require_role(ROLE_SYSTEMUSER);

$data_dir = realpath( dirname(__FILE__).'/../data/' );
$config_file = $data_dir.'/gitolite-admin/conf/webinterface.conf';
$config_dir = $data_dir.'/gitolite-admin/conf/webinterface';
$key_dir = $data_dir.'/gitolite-admin/keydir';
DEBUG("gitolite-data_dir: ".$data_dir);
$git_wrapper = $data_dir . '/git-wrapper.sh';


function check_env() 
{
  global $git_wrapper, $data_dir, $config_file, $config_dir, $key_dir;
  if (!is_executable($git_wrapper)) {
    system_failure("git_wrapper.sh is not executable: {$git_wrapper}");
  }
  if (! (is_file($data_dir.'/sshkey') && is_file($data_dir.'/sshkey.pub'))) {
    system_failure("SSH-key not found. Please setup the gitolite-module correctly. Run ./data/initialize.sh");
  }
  if (! is_dir($data_dir.'/gitolite-admin')) {
    system_failure("Repository gitolite-admin ot found. Initial checkout must be made manually. Run ./data/initialize.sh");
  }
  if (! is_dir($config_dir)) {
    system_failure("gitolite-admin repository is not prepared.");
  }
  if (! (is_dir($key_dir) && is_writeable($config_file))) {
    system_failure("Repository gitolite-admin is corrupted or webinterface.conf is not writeable.");
  }
}


function validate_name($name) {
  return (preg_match('/^[[:alnum:]][[:alnum:]._-]*$/', $name));
}


function git_wrapper($commandline)
{
  global $git_wrapper, $data_dir;

  $command = $git_wrapper.' '.$commandline;
  $output = array();
  $retval = 0;
  DEBUG($command);
  exec($command, $output, $retval);
  DEBUG($output);
  DEBUG($retval);
  if ($retval > 0) {
    system_failure('Interner Fehler!');
    // FIXME: Hier sollte auf jeden Fall ein Logging angeworfen werden!
  }
}

function refresh_gitolite() 
{
  check_env();
  git_wrapper('pull');
}



function list_repos() 
{
  global $config_file, $config_dir;
  $username = $_SESSION['userinfo']['username'];
  $userconfig = $config_dir . '/' . $username . '.conf';
  DEBUG("using config file ".$userconfig);
  if (! is_file($userconfig)) {
    DEBUG("user-config does not exist");
    return array();
  }

  $repos = array();
  $lines = file($userconfig);
  $current_repo = NULL;
  $current_repo_users = array();
  foreach ($lines as $line) {
    $m = array();
    if (preg_match('_^[ \t]*repo ([^]]+)_', $line, $m) != 0) {
      if ($current_repo) {
        $repos[$current_repo] = $current_repo_users;
      }
      DEBUG("found repo ".$m[1]);
      $current_repo = chop($m[1]);
      $current_repo_users = array();
    } else if (preg_match('/^\s*(R|RW|RW+)\s*=\s*([[:alnum:]][[:alnum:]._-]*)\s*/', $line, $m) != 0) {
      DEBUG("found access rule: ".$m[1]." for ".$m[2]);
      $current_repo_users[chop($m[2])] = chop($m[1]);
    }
  }
  if ($current_repo) {
    $repos[$current_repo] = $current_repo_users;
  }
  DEBUG($repos);
  return $repos;
}



function list_users() {
  global $config_file, $config_dir;
  $username = $_SESSION['userinfo']['username'];
  $userconfig = $config_dir . '/' . $username . '.conf';
  DEBUG("using config file ".$userconfig);
  if (! is_file($userconfig)) {
    DEBUG("user-config does not exist");
    return array();
  }
  
  $lines = file($userconfig);
  $users = array();
  foreach ($lines as $line) {
    $m = array();
    if (preg_match('_# user ([^]]+)_', $line, $m) != 0) {
      $users[] = chop($m[1]);
    }
    if (preg_match('_^\s*repo .*_', $line) != 0) {
      break;
    }
  }
  DEBUG($users);
  return $users;
}

function get_pubkey($handle) {
  global $key_dir;
  if (! validate_name($handle)) {
    return '';
  }
  $keyfile = $key_dir.'/'.$handle.'.pub';
  if (! file_exists($keyfile)) {
    return '';
  }
  return file_get_contents($keyfile);
}



function newkey($pubkey, $handle)
{
  global $key_dir, $config_dir;
  $username = $_SESSION['userinfo']['username'];
  
  $handle = $username.'-'.$handle;
  if (! validate_name($handle)) {
    system_failure("Der eingegebene Name enthält ungültige Zeichen. Bitte nur Buchstaben, Zahlen, Unterstrich, Binderstrich und Punkt benutzen.");
  }

  // FIXME: Muss man den SSH-Key auf Plausibilität prüfen? Aus Sicherheitsgründen vermutlich nicht.
  $keyfile = $key_dir.'/'.$handle.'.pub';
  file_put_contents($keyfile, $pubkey);
  git_wrapper('add '.$keyfile);

  $userconfig = $config_dir . '/' . $username . '.conf';
  DEBUG("using config file ".$userconfig);
  if (! is_file($userconfig)) {
    DEBUG("user-config does not exist, creating new one");
    file_put_contents($userconfig, '# user '.$handle."\n");
  } elseif (in_array($handle, list_users())) {
    # user ist schon eingetragen, nur neuer Key
  } else {
    $content = file_get_contents($userconfig);
    file_put_contents($userconfig, "# user {$handle}\n".$content);
  }
  git_wrapper('add '.$userconfig);
  
  git_wrapper('commit --allow-empty -m "added new key for '.$handle.'"');
  git_wrapper('push');
}


function delete_key($handle)
{
  global $key_dir, $config_dir;
  $username = $_SESSION['userinfo']['username'];

  if (! validate_name($handle)) {
    system_failure("Der eingegebene Name enthält ungültige Zeichen. Bitte nur Buchstaben, Zahlen, Unterstrich, Binderstrich und Punkt benutzen.");
  }
  if (!in_array($handle, list_users())) {
    DEBUG("key {$handle} not in");
    DEBUG(list_users());
    system_failure("Den angegebenen Key scheint es nicht zu geben");
  }

  // FIXME: Muss man den SSH-Key auf Plausibilität prüfen? Aus Sicherheitsgründen vermutlich nicht.
  $keyfile = $key_dir.'/'.$handle.'.pub';
  if (! file_exists($keyfile)) {
    system_failure("Der angegebene Schlüssel scheint nicht mehr vorhanden zu sein. Bitte manuelle Korrektur anfordern!");
  } 
  git_wrapper('rm '.$keyfile);


  $userconfig = $config_dir . '/' . $username . '.conf';
  DEBUG("using config file ".$userconfig);
  if (! is_file($userconfig)) {
    DEBUG("user-config does not exist, wtf?");
    system_failure("Es gibt für diesen Benutzer noch keine Konfiguration. Das sollte nicht sein!");
  } else {
    $content = file($userconfig);
    DEBUG("Old file:");
    DEBUG($content);
    $newcontent = array();
    foreach ($content as $line) {
      if (preg_match('/^# user '.$handle.'$/', $line)) {
        DEBUG("delete1: ".$line);
        continue;
      }
      if (preg_match('/^\s*(R|RW|RW+)\s*=\s*'.$handle.'\s*$/', $line)) {
        DEBUG("delete2: ".$line);
        continue;
      }
      $newcontent[] = $line;
    }
    DEBUG("Modified file:");
    DEBUG($newcontent);
    file_put_contents($userconfig, implode('', $newcontent));
  }
  git_wrapper('add '.$userconfig);
 
  git_wrapper('commit -m "deleted key for '.$handle.'"');
  git_wrapper('push');


}
