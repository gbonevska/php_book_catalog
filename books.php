<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Въвеждане на нова книга';
include 'includes/header.php';

	if($_POST && $_POST['submitted'] == "Въведи"){
		$bookName = ''; 
		$bookNotes = '';
		$errMsg = array();
		$selectedAuthorIds = array();
		$selectedCollectionIds = array();
		$bookName = $db->real_escape_string(trim($_POST['bookName']));
		$bookNotes = $db->real_escape_string(trim($_POST['bookNotes']));

		//check inputted book name
		$errMsg = validateInputtedValue($db, $bookName, 'bookName');
		$errMsg = validateInputtedValue($db, $bookNotes, 'bookNotes');
		
		if (array_key_exists('multiAuthors', $_POST)) {
			foreach ($_POST['multiAuthors'] as $multiAuthorId) {
				$selectedAuthorIds[] = (int) $multiAuthorId;
			}
		}

		if (array_key_exists('multiCollections', $_POST)) {
			foreach ($_POST['multiCollections'] as $multiCollectionId) {
					$selectedCollectionIds[] = (int) $multiCollectionId;
				}
		}
		
		if (count($errMsg)>0) {    
			foreach($errMsg as $err) {
				echo $err . '</ br>';
			}
		}
		else {
			mysqli_autocommit($db,FALSE);

			$newBookId = insertBooks($db, $bookName, $bookNotes); 

			if($newBookId === false) {
				echo 'Грешка при въвеждане на книга!';
				mysqli_rollback($db);
			}
			else {
				$insertedIds = array();				
				$insertedIds = insertRelationBookAuthorCollection($db, $newBookId, $selectedAuthorIds, $selectedCollectionIds);

				if (count($insertedIds) > 0 && ($insertedIds !== false)){
					echo 'Успешен запис на книга.';
					mysqli_commit($db);
					mysqli_autocommit($db,TRUE);
				}
				else {
					echo 'Грешка!';
					mysqli_rollback($db);
					mysqli_autocommit($db,TRUE);
					header('Location: index.php');
					exit;
				}	
			}
		}
	}	
?>
<p>Въвеждане на нова книга:</p>
<form method="POST">
    <div>
		Ново заглание на книга: <input type="text" name="bookName" /> <br />
		Избери автор:
	    <?php
			$authors = array();
			$authors = selectAllAuthors($db);
		?>
		<select name="multiAuthors[]" multiple="multiple">
        <?php		
			if (!($authors === false)) {
			    foreach($authors as $key =>$author) {
					echo '<option name="item" value="' . $key . '">' . $author . '</option>' ;
				}
			}
		?>
		</select> <br />
		Избери колекция:
	    <?php
			$collections = array();
			$collections = selectAllCollections($db);
		?>
		<select name="multiCollections[]" multiple="multiple">
        <?php		
			if (!($collections === false)) {
			    foreach($collections as $key =>$collection) {
					echo '<option name="item" value="' . $key . '">' . $collection . '</option>' ;
				}
			}
		?>
		</select> <br />
		Забележка към книга: <textarea rows="4" cols="50" name="bookNotes">
		</textarea>	<br />
    	<input type="submit" name = "submitted" value="Въведи" />
	</div>
</form>
<?php
include 'includes/footer.php';
?>