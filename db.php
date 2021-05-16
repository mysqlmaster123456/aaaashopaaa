<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "eshop";

$conn = new mysqli($servername,$username,$password,$db);
$conn->query("SET NAMES utf8");

if ($conn->connect_error){
    die('Připojení se nezdařilo '.$conn->connect_error);
}
?>