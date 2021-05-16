<?php 
include "header.php";
?>

<?php
if ( isset( $_POST["kosik_pridat"] ) ) {
	$item_id = $_POST["kosik_pridat"];

	if ( array_key_exists($item_id, $_SESSION["kosik"]) ) {
		$_SESSION["kosik"][$item_id] += 1;
	} else {
		$_SESSION["kosik"][$item_id] = 1;
	}
}

if (isset($_GET["id"])) {
	$item_id = $_GET["id"];
}

$sql = "SELECT id, title, description, price_tax  FROM items WHERE id='$item_id'";
$query = mysqli_query($conn,$sql);
$item = mysqli_fetch_array($query);
?>

Detail zboží:<br>

Item: <?= $item[1] ?> Popis: <?= $item[2] ?> Cena s DPH: <?= ceil($item[3]) ?> Cena bez DPH: <?= ceil($item[3] / 1.21) ?>

<form method="POST" action="item.php">
	<button type="submit" name="kosik_pridat" value="<?= $item_id ?>">Přidat do košíku</button>

</form>

<?php
include "footer.php";
?>
