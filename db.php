<?php
$host = 'localhost';
$db = 'booklovertest';
$user = 'root'; // change if your MySQL user is different
$pass = ''; // change if you have a password

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>





