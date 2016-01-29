<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Въвеждане на нова колекция';
include 'includes/header.php';

	if (isset($_GET['delete_collection'])) {
		$collectionId =  $_GET['delete_collection'];
		if(deleteCollection($db, $collectionId) === false) {
			echo "Грешка при изтриване на колекция.";
		} else {
			echo "Успешно изтриване на колекцията.";
		}
		
	}
	if($_POST){	
		$collectionName = $db->real_escape_string(trim($_POST['collectionName']));

		$newCollectionId = insertCollectionByName($db, $collectionName);
		if ($newCollectionId === false) {
			echo 'Грешка при въвеждане на колекция!';
		}
	}	
?>
<p>Въвеждане на нова колекция:</p>
<form method="POST">
    <div>Име на нова колекция:
	     <input type="text" name="collectionName" />
	     <input type="submit" value="Въведи" />
	</div>
</form>
<p></p>
<table border = "1">
	<tr>
		<th>Колекции</th>
		<th>-------</th>
		<th>-------</th>
	</tr>
	<?php
		$collections = array();
		$collections = selectAllCollections($db);
		if (!($collections === false)) {
			foreach($collections as $key => $collection) {
				echo '<tr><td>' . $collection . ' </td>
				      <td><a href="updateCollections.php?update_collection=' . $key . '"> Редактирай </a></td>
					  <td><a href="collections.php?delete_collection=' .  $key . '">Изтрий</a></td></tr>'; 
			}
		}
	?>
</table>
<?php
include 'includes/footer.php';
?>