<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

if (! isset( $_SESSION["kosik"] ) ) {
  $_SESSION["kosik"] = [];
}


require "db.php";

$sql = "SELECT * FROM categories";
$query = mysqli_query($conn,$sql);
$categories = mysqli_fetch_all($query);
?>


<!DOCTYPE html>
<html>
<head>

  <link rel="stylesheet" type="text/css" href="style.css">
<style>
     
</style>
</head>
<body>
  <div class="container">
  <div class="menu">
<nav role="navigation">
  <ul>
    
    <li><a href="#">Kategorie</a>
      <ul class="dropdown">
        <?php foreach ($categories as $category): ?>
          <a href="category.php?id=<?= $category[0] ?>">
          <li>
          <?= $category[1] ?>
          </li>
          </a>
        <?php endforeach ?>
        
      </ul>
    </li>
    <a href="kosik.php" style="float: right;"><li>Košík</li></a>
  </ul>
</nav>
</div>
</div>