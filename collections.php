<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Въвеждане на нова колекция';
include 'includes/header.php';
?>
<?php 
	if($_POST){
			
		//check inputted author name
		$collectionName = $db->real_escape_string(trim($_POST['collectionName']));
		$errMsg = array();
		//$errMsg = validateInputtedValue($db, $collectionName, 'collectionName');
		
		if (count($errMsg)>0) {    
			foreach($errMsg as $err) {
				echo $err . '</ br>';
			}
		}
		else {
			$newCollectionId = insertCollectionByName($db, $collectionName);
			if ($newCollectionId === false) {
				echo 'Грешка при въвеждане на колекция!';
			}
		}	
	}
?>
<a href="index.php"> Към общия списък с книги и автори </a>
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
		<th>Редакция</th>
	</tr>
	<?php
		$collections = array();
		$collections = selectAllCollections($db);
		if (!($collections === false)) {
			foreach($collections as $key => $collection) {
				echo '<tr><td>' . $collection . ' </td>' ;
				echo '<td><a href="updateCollections.php?update_collection=' . $key . '"> Редактирай </a></td></tr>'; 
			}
		}
	?>
</table>
<?php
include 'includes/footer.php';
?>