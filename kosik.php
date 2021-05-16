<?php 
include "header.php";
?>

<?php
if ( isset( $_POST["kosik_odebrat"] ) ) {
	$item_id = $_POST["kosik_odebrat"];

	if ( array_key_exists($item_id, $_SESSION["kosik"]) ) {
		if ( $_SESSION["kosik"][$item_id] < 2 ) {
			unset( $_SESSION["kosik"][$item_id] );
		} else {
			$_SESSION["kosik"][$item_id] -= 1;
		}
	} 
}


$items = [];
$sum_tax = 0;
foreach( $_SESSION["kosik"] as $item_id => $num ) {
	$sql = "SELECT id, title, description, price_tax  FROM items WHERE id='$item_id'";
	$query = mysqli_query($conn, $sql);
	$item = mysqli_fetch_array($query);
	$item["num"] = $num;
	array_push($items, $item);
	$sum_tax += $item["price_tax"] * $num;
}
?>

<form method="POST" action="kosik.php">
<?php foreach( $items as $item ): ?>
	Item: <?= $item["title"] ?> Počet: <?= $item["num"] ?> <button name="kosik_odebrat" value="<?= $item[0] ?>">Odebrat kus</button> <br>
<?php endforeach ?>
</form>

<hr>

Celková cena s DPH: <?= $sum_tax ?> Kč<br>
Celková cena bez DPH: <?= ceil($sum_tax / 1.21) ?> Kč

<hr>

<a href="objednavka.php"><button>Přejít k objednávce</button></a>


<?php
include "footer.php";
?>