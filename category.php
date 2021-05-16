<?php 
include "header.php";
?>

<?php
if (isset($_GET["id"])) {
	$category_id = $_GET["id"];
}

$sql = "SELECT * from categories WHERE id_cat='$category_id'";
$query = mysqli_query($conn,$sql);
$categories = mysqli_fetch_array($query);
$title = $categories["title"];
$sql = "SELECT id, title, description, price_tax from items where id_category='$category_id'";
$query = mysqli_query($conn,$sql);
$items = mysqli_fetch_all($query);
?>
Výpis katerogie:<br>
	<?php foreach($items as $item): ?>
		<u><a href="item.php?id=<?= $item[0] ?>">Item: <?= $item[1] ?> Popis: <?= $item[2] ?> Cena s DPH: <?= ceil($item[3]) ?> Cena bez DPH: <?= ceil($item[3] / 1.21) ?></a></u><br>
	<?php endforeach ?>

<?php
include "footer.php";
?>