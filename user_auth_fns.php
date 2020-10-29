<?php

require_once('db_fns.php');

function register($email, $password) {
  $conn = db_connect();
  $query = "select 1 from kayttajat where sahkoposti = '$email'";
  $result = $conn->query($query);
  if (!$result) {
    throw new Exception('Could not execute query');
  }
  if ($result->num_rows>0) {
    throw new Exception('That username is taken - go back and choose another one.');
    }
  $password_hash = password_hash($password,PASSWORD_DEFAULT);
  $query = "insert into kayttajat (sahkoposti,salasana) values ('$email','$password_hash')";
  $result = $conn->query($query);
  if (!$result) {
    throw new Exception('Could not register you in database - please try again later.');
    }
  return true;
}

function login($username, $password) {
  $conn = db_connect();
  $query = "select salasana from kayttajat where sahkoposti = '$username'";
  debuggeri($query);
  $result = $conn->query($query);
  if (!$result) {
    throw new Exception('Could not log you in.');
    }
  if ($result->num_rows>0) {
     $row = mysqli_fetch_row($result);
     //debuggeri("row:".var_export($row,true));
     $password_verify = password_verify($password, $row[0]);
     if ($password_verify){
       return true;
       } 
     else {
       throw new Exception('Väärä salasana.');
       }
    }
  else {
    throw new Exception('Käyttäjätunnusta ei löydy.');
    }
}
  
function check_valid_user() {
// see if somebody is logged in and notify them if not
  if (isset($_SESSION['valid_user']))  {
      echo "Logged in as ".$_SESSION['valid_user'].".<br>";
  } else {
     // they are not logged in
     do_html_header('Problem:');
     echo 'You are not logged in.<br>';
     do_html_url('login.php', 'Login');
     do_html_footer();
     exit;
  }
}

function change_password($username, $old_password, $new_password) {
  login($username, $old_password);
  $conn = db_connect();
  $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
  $query = "update kayttajat set salasana = '$password_hash'
     where sahkoposti = '$username'";
  $result = $conn->query($query);
  if (!$result) {
    throw new Exception('Password could not be changed.');
  } else {
    return true;  // changed successfully
  }
}

function get_random_word($min_length, $max_length) {
// grab a random word from dictionary between the two lengths
// and return it

   // generate a random word
  $word = '';
  // remember to change this path to suit your system
  $dictionary = '/usr/dict/words';  // the ispell dictionary
  $fp = @fopen($dictionary, 'r');
  if(!$fp) {
    return false;
  }
  $size = filesize($dictionary);

  // go to a random location in dictionary
  $rand_location = rand(0, $size);
  fseek($fp, $rand_location);

  // get the next whole word of the right length in the file
  while ((strlen($word) < $min_length) || (strlen($word)>$max_length) || (strstr($word, "'"))) {
     if (feof($fp)) {
        fseek($fp, 0);        // if at end, go to start
     }
     $word = fgets($fp, 80);  // skip first word as it could be partial
     $word = fgets($fp, 80);  // the potential password
  }
  $word = trim($word); // trim the trailing \n from fgets
  return $word;
}

function reset_password($username) {
// set password for username to a random value
// return the new password or false on failure
  // get a random dictionary word b/w 6 and 13 chars in length
  $new_password = get_random_word(6, 13);

  if($new_password == false) {
    // give a default password
    $new_password = "changeMe!";
  }

  // add a number  between 0 and 999 to it
  // to make it a slightly better password
  $rand_number = rand(0, 999);
  $new_password .= $rand_number;

  // set user's password to this in database or return false
  $conn = db_connect();
  $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
  $query = "update kayttajat set salasana = '$password_hash'
     where sahkoposti = '$username'";
  $result = $conn->query($query);
  
  if (!$result) {
    throw new Exception('Could not change password.');  // not changed
  } else {
    return $new_password;  // changed successfully
  }
}

function notify_password($email, $password) {
// notify the user that their password has been changed
    $from = "From: omniakurssi@gmail.com \r\n";
    $mesg = "Your password has been changed\r\n"
           ."Please change it next time you log in.\r\n";
    if (mail($email, 'PHPBookmark login information', $mesg, $from)) {
       return true;
       } 
    else {
       throw new Exception('Could not send email.');
       }
    }


?>
