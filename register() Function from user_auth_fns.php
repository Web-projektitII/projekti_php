function register($email, $password) {
// register new person with db
// return true or error message

  // connect to db
  $conn = db_connect();

  // check if username is unique
  $query = "select 1 from user where sahkoposti = '$email'";
  $result = $conn->query($query);
  if (!$result) {
    throw new Exception('Could not execute query');
  }

  if ($result->num_rows>0) {
    throw new Exception('That username is taken - go back and choose another one.');
  }

  // if ok, put in db
  $query = "insert into kayttajat (sahkoposti,salasana) values ('$email','$password')";
  $result = $conn->query($query);
  if (!$result) {
    throw new Exception('Could not register you in database - please try again
later.');
  }

  return true;
}