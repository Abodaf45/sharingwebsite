<?php
$host = 'localhost';
$db = 'file_sharing3';
$user = 'root'; // Change if necessary
$pass = '1234'; // Change if necessary

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
