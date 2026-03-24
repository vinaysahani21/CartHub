<?php
$host = 'localhost';
$user = 'vinay';
$pass = '1212';
$db_name = 'ecomm';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>