<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Редакция на книга';
include 'includes/header.php';

	$bookOldCollectionName = array();
	$bookOldAuthors = array();
	$selectedAuthorIds = array();
	$selectedCollectionIds = array();
	
	if (isset($_GET['book_id'])) {
		$bookId = (int) $_GET['book_id'];
		
		$bookDetails = array();
		$bookDetails = selectBookDetails($db, $bookId);
		
		if ($bookDetails === false) {
			echo "Грешка! Тази книга не е открита за редакция!";
		}
		else {
			$bookOldName = $bookDetails['bookName'];
			$bookOldAuthors = $bookDetails['authors'];
			$bookOldCollectionName = $bookDetails['collectionName']; 
			$bookOldNotes = $bookDetails['bookNotes'];
			
			//echo '<pre>'.print_r($bookOldCollectionName, true).'</pre>';
		}
	}
	
	if( $_POST && $_POST['submitted'] == "Редактирай" ) {
		$bookName = ''; 
		$bookNotes = '';
		$result = '';
		$bookName = $db->real_escape_string(trim($_POST['bookName']));   //htmlspecialchars()
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
			mysqli_autocommit($db,FALSE); // set autocommit off
			if(empty($bookNotes) == true && empty($bookOldNotes) == false) {
				$bookNotes = "NULL";
			}
			$result = updateBooks($db, $bookId, $bookName, $bookNotes, $selectedCollectionIds, $selectedAuthorIds);
			if ($result === true) {
				$bookOldName = '';
				$bookOldAuthors = array();
				$bookOldCollectionName = array(); 
				$bookOldNotes = '';
				$bookDetails = array();
				
				$bookDetails = selectBookDetails($db, $bookId);
				
				$bookOldName = $bookDetails['bookName'];
				$bookOldAuthors = $bookDetails['authors'];
				$bookOldCollectionName = $bookDetails['collectionName']; 
				$bookOldNotes = $bookDetails['bookNotes'];
				
				echo 'Успешна редакция.';
				mysqli_commit($db);
				mysqli_autocommit($db,TRUE);
			} else {
				echo 'Грешка при редакция!';
				header('Location: index.php');
				exit;
			}	
		}
	}		
?>
<p>Редакция на книга:</p>
<form method="POST">
    <div>
		Заглание на книга: <input type="text" name="bookName" value="<?php echo $bookOldName; ?>" /> <br />
		Автор:
	    <?php
			$authors = array();
			$authors = selectAllAuthors($db);
		
			echo '<select name="multiAuthors[]" multiple="multiple">';
        
			if (!($authors === false)) {
				foreach($authors as $key =>$author) {
					echo '<option name="item" value="' . $key . '"';
					if (in_array($author, $bookOldAuthors)) {
						echo 'selected="true"';
					}
					echo '>' . $author . '</option>' ;
				}
			}
		?>
		</select> <br />
		Колекция:
	    <?php
			$collections = array();
			$collections = selectAllCollections($db);

			echo '<select name="multiCollections[]" multiple="multiple">';
		
			if (!($collections === false)) {
				foreach($collections as $key =>$collection) {
					echo '<option name="item" value="' . $key . '"';
					if (in_array($collection, $bookOldCollectionName)) {
						echo 'selected="true"';
					}
					echo '>' . $collection . '</option>' ;
				}
			}
		?>
		</select> <br />
		Забележка към книга: <textarea rows="4" cols="50" name="bookNotes"><?php echo $bookOldNotes; ?></textarea>	<br />
		<input type="submit" name = "submitted" value="Редактирай" />
	</div>
</form>
<?php
include 'includes/footer.php';
?>