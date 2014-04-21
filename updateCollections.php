<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Редактиране на автор';
include 'includes/header.php';
?>
<a href="index.php"> Към общия списък с книги и автори </a>
<p>Редактиране на колекция:</p>
<?php 
	if (isset($_GET['update_collection'])) {
		$collectionId = (int) $_GET['update_collection'];
		$collectionOldName = returnCollectionNameById($db, $collectionId);
	}
	if($_POST){		
		//check inputted author name
		$collectionName = $db->real_escape_string(trim($_POST['collectionName']));
		$errMsg = array();
		$errMsg = validateInputtedValue($db, $collectionName, 'collectionName');
		
		if (count($errMsg)>0) {    
			foreach($errMsg as $err) {
				echo $err . '</ br>';
			}
		}
		else {
			if (updateCollectionByName($db, $collectionName, $collectionId) === false) {
				echo 'Грешка при редакция на колекция!';
			}
			else {
				echo 'Успешна редакция на колекция!';
			}
		}	
	}
?>
<form method="POST">
    <div>Име на колекция:
	     <input type="text" name="collectionName" value="<?php echo $collectionOldName; ?>"/>
	     <input type="submit" value="Редактирай" />
	</div>
</form>
<p></p>
<?php
	include 'includes/footer.php';
?>