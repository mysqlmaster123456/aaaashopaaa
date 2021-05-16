<?php 
include "header.php";
?>

<?php
if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
	$_SESSION["action"] = "customer_data";
}

?>

<?php
	if ($_SESSION["action"] == "customer_data") {
		$error = false;
		if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
		
			if ( ( isset($_POST["jmeno"]) && isset($_POST["prijmeni"])
			 && isset($_POST["telefon"]) && isset($_POST["ulice"])
			 && isset($_POST["popisne"]) && isset($_POST["psc"]) ) ) {

			 	$jmeno = $_POST["jmeno"];
			 	$prijmeni = $_POST["prijmeni"];
			 	$telefon = $_POST["telefon"];
			 	$ulice = $_POST["ulice"];
			 	$popisne = $_POST["popisne"];
			 	$psc = $_POST["psc"];



			 	if ( $jmeno == "" || $prijmeni == ""
			 		|| $telefon == "" || $ulice == ""
			 		|| $popisne == "" || $psc == "" ) {
			 		$error = "Vyplňte všechny údaje";
			 	} else {
			 		$psc = str_replace( " ", "" , trim($psc) );
			 		if ( ! is_numeric($psc) || strlen($psc) != 5 ) {
			 			$error = "Vyplňte správně PSČ";
			 		}
			 		$_SESSION["jmeno"] = $jmeno;
			 		$_SESSION["prijmeni"] = $prijmeni;
			 		$_SESSION["ulice"] = $ulice;
			 		$_SESSION["popisne"] = $popisne;
			 		$_SESSION["psc"] = $psc;

			 		$_SESSION["action"] = "doprava";
			 	}	
			}
		}
	}
	if ( $_SESSION["action"] == "doprava" ) {
		if ( isset( $_POST["doprava_submit"] ) && isset( $_SESSION["doprava"] ) ) {
			$_SESSION["action"] = "souhrn";
		}
	}
	if ( $_SESSION["action"] == "souhrn" ) {
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
		$doprava = $_SESSION["doprava"];
		$sql = "SELECT id_delivery, type_of_delivery, price  FROM deliveries WHERE id_delivery='$doprava'";
		$query = mysqli_query($conn, $sql);
		$delivery = mysqli_fetch_array($query);

		$jmeno = $_SESSION["jmeno"];
	 	$prijmeni = $_SESSION["prijmeni"];
	 	$ulice = $_SESSION["ulice"];
	 	$popisne = $_SESSION["popisne"];
	 	$psc = $_SESSION["psc"];

		if ( isset( $_POST["souhrn_submit"] ) ) {
			

		 	// Get customer
		 	$sql = "SELECT id_address FROM addresses WHERE street='$ulice' AND street_number='$popisne' AND postal_code='$psc'";
		 	$query = mysqli_query($conn, $sql);
		 	$address = mysqli_fetch_array($query);
		 	if (! $address){
		 		$sql = "INSERT INTO addresses (street, street_number, postal_code) VALUES ('$ulice', '$popisne', '$psc')";
		 		$conn->query($sql);
		 		$id_address = $conn->insert_id;

		 	} else {
				$id_address = $address[0];
			}

			$sql = "SELECT id_customer FROM customers WHERE name='$jmeno' AND surname='$prijmeni' AND id_address=$id_address";
			$query = mysqli_query($conn, $sql);
		 	$customer = mysqli_fetch_array($query);
		 	if (! $customer){
		 		$sql = "INSERT INTO customers (name, surname, id_address) VALUES ('$jmeno', '$prijmeni', '$id_address')";
		 		$conn->query($sql);
		 		$id_customer = $conn->insert_id;

		 	} else {
				$id_customer = $customer[0];
			}

			// Create order
			$order_price = $sum_tax + $delivery["price"];
			$sql = "INSERT INTO orders (id_address, id_delivery, price, id_customer) VALUES ($id_address, $doprava, $order_price, $id_customer)";
			$conn->query($sql);
			$id_order = $conn->insert_id;

			// Add items to order
			foreach( $items as $item ) {
				$id_item = $item[0];
				$pocet = $item["num"];
				$sql = "INSERT INTO ite_ord (id_item, id_order, pocet) VALUES ($id_item, $id_order, $pocet)";
				$conn->query($sql);
			}

			$_SESSION["action"] = "dokonceno";

		}

	}
?>

<?php if ( $_SESSION["action"] == "customer_data" ): ?>


	<?php if ($error): ?>
		<b><?= $error ?></b>
	<?php endif ?>
	<form method="POST" action="objednavka.php">
	Jméno: <input type="text" name="jmeno"><br>
	Příjmení: <input type="text" name="prijmeni"><br>
	Telefon: <input type="text" name="telefon"> Vojta vám zavolá<br>

	Ulice: <input type="text" name="ulice"><br>
	Číslo popisné: <input type="text" name="popisne"><br>
	PSČ: <input type="text" name="psc"><br>

	<button type="submit" name="submit">Odeslat</button>

	</form>


<?php elseif ( $_SESSION["action"] == "doprava" ): ?>

	<?php
	$sql = "SELECT id_delivery, type_of_delivery, price  FROM deliveries";
	$query = mysqli_query($conn, $sql);
	$deliveries = mysqli_fetch_all($query);

	if ( isset( $_POST["doprava"] ) ) {
		$doprava = $_POST["doprava"];
		$_SESSION["doprava"] = $doprava;
	}

	$sum_tax = 0;
	foreach( $_SESSION["kosik"] as $item_id => $num ) {
		$sql = "SELECT id, title, description, price_tax  FROM items WHERE id='$item_id'";
		$query = mysqli_query($conn, $sql);
		$item = mysqli_fetch_array($query);
		$sum_tax += $item["price_tax"] * $num;
	}

	$doprava_price = 0;

	?>

	<b>Vyberte způsob dopravy:</b><br>
	<?php if ( ! isset( $_POST["doprava"] ) ): ?>
		<form method="POST" action="objednavka.php">
			<?php foreach( $deliveries as $delivery ): ?>
				<button type="submit" name="doprava" value="<?= $delivery[0] ?>">✓</button> <?= $delivery[1] ?> Cena: <?= $delivery[2] ?><br>
			<?php endforeach ?>
		</form>
	<?php elseif ( isset( $_POST["doprava"] ) ): ?>
		<form method="POST" action="objednavka.php">
			<?php foreach( $deliveries as $delivery ): ?>
				<?php if ( $delivery[0] == $doprava ): ?>
					<?php $doprava_price = $delivery[2] ?>
						<b>Vybrán</b>
				<?php else: ?>
					<button type="submit" name="doprava" value="<?= $delivery[0] ?>">✓</button>
				<?php endif ?>
				<?= $delivery[1] ?> Cena: <?= $delivery[2] ?><br>
			<?php endforeach ?>
		</form>
	<?php endif ?>

	<hr>

	<b>Celková cena s DPH (s dopravou):</b> <?= $sum_tax + $doprava_price ?> <br>
	<b>Celková cena bez DPH (s dopravou):</b> <?= ceil($sum_tax / 1.21) + $doprava_price ?>

	<?php if ( isset( $_POST["doprava"] ) ): ?>
		<br>
	<form method="POST" action="objednavka.php">
		<button type="submit" name="doprava_submit">Souhrn objednávky</button>
	</form>
	<?php endif ?>

<?php elseif ( $_SESSION["action"] == "souhrn" ): ?>

<?php foreach( $items as $item ): ?>
	Item: <?= $item["title"] ?> Počet: <?= $item["num"] ?> <br>
<?php endforeach ?>

Doprava: <?= $delivery["type_of_delivery"] ?><br>

Jméno: <?= $jmeno ?><br>
Přijmené: <?= $prijmeni ?><br>

Ulice: <?= $ulice ?><br>
Číslo popisné: <?= $popisne ?><br>
PSČ: <?= $psc ?><br>


Celková cena s DPH: <?= $sum_tax + $delivery["price"] ?> Kč<br>
Celková cena bez DPH: <?= ceil($sum_tax / 1.21) + $delivery["price"] ?> Kč

<form method="POST" action="objednavka.php">
	<button type="submit" name="souhrn_submit">Dokončit</button>
</form>

<?php elseif ( $_SESSION["action"] == "dokonceno" ): ?>
	Objednávka byla úspěšně vytvořena, Špek vás odmění.
<?php endif ?>


<?php
include "footer.php";
?>