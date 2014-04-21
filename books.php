<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Въвеждане на нова книга';
include 'includes/header.php';
?>
<?php 
	$bookId = array();
	$bookName = ''; 
	$bookNotes = '';
    $selectedAuthorIds = array();
	$selectedCollectionId = '';
				
    $collectionForUpdate = '';
    $selectedAuthors = array();
    

	echo '<a href="index.php"> Към общия списък с книги и автори </a>';
	if (isset($_GET['book_id'])) {
		$authorIds = array();
		$booksByAuthor = array();
		$bookId[0] = (int) $_GET['book_id'];
        

		echo '<p>Редакция на нова книга:</p>' ;
		$booksByAuthor = selectAllBooksByAuthors($db, $authorIds, $bookId);
	
		if(!($booksByAuthor === false)) {
			foreach ($booksByAuthor as $book => $row) {
	         	$bookName = $row['bookName'];
	         	foreach ($row['authors'] as $key => $author) {
					$selectedAuthors[] =  $key ;
				}
				$collectionForUpdate = $row['collectionName'];	
				$bookNotes = $row['bookNotes'];
			}
		}	
	}
	else {
		echo '<p>Въвеждане на нова книга:</p>' ;
	}


	if($_POST){	
		
		switch( $_POST['submitted'] ) {
			case "Въведи":
				$errMsg = array();
				$bookName = $db->real_escape_string(trim($_POST['bookName']));
				$bookNotes = $db->real_escape_string(trim($_POST['bookNotes']));

				//check inputted book name
				$errMsg = validateInputtedValue($db, $bookName, 'bookName');
                $errMsg = validateInputtedValue($db, $bookNotes, 'bookNotes');
				
				foreach ($_POST['multiAuthors'] as $multiAuthorId) {
					$selectedAuthorIds[] = (int) $multiAuthorId;
				}

				if ($_POST['multiCollections'] !== null) {
					//foreach ($_POST['multiCollections'] as $multiCollectionId) {
						//$selectedCollectionIds[] = (int) $multiCollectionId;
					      $selectedCollectionIds = (int)$_POST['multiCollections'][0];
					//}
				}
				
				if (count($errMsg)>0) {    
					foreach($errMsg as $err) {
						echo $err . '</ br>';
					}
				}
				else {
					$newBookId = insertBooks($db, $bookName, $bookNotes); //insertInDbByName($db, 'books', 'book_title', $bookName);

					if($newBookId === false) {
						echo 'Грешка при въвеждане на книга!';
					}
					else {
						$insertedIds = array();
						//echo '<pre>'.print_r($selectedCollectionIds, true).'</pre>';
						$insertedIds = insertRelationBookAuthor($db, $newBookId, $selectedAuthorIds, $selectedCollectionIds);
						
						if (count($insertedIds) > 0) {
							echo 'Успешен запис на книга.';
						}
						else {
							echo 'Грешка!';
							header('Location: index.php');
							exit;
						}	
					}
				}
				break;
			case "Редактирай":
			    $errMsg = array();

			    if ($bookName == null) {
			    	echo 'Грешка!';
					header('Location: index.php');
					exit;
			    }

				$bookName = $db->real_escape_string(trim($_POST['bookName']));
				$bookNotes = $db->real_escape_string(trim($_POST['bookNotes']));

				//check new inputted book name
				$errMsg = validateInputtedValue($db, $bookName, 'bookName');
                $errMsg = validateInputtedValue($db, $bookNotes, 'bookNotes');

			    foreach ($_POST['multiAuthors'] as $multiAuthorId) {
					$selectedAuthorIds[] = (int) $multiAuthorId;
				}

				if ($_POST['multiCollections'] !== null) {
					$selectedCollectionId = (int) $_POST['multiCollections'][0];
				}

				if (count($errMsg)>0) {    
					foreach($errMsg as $err) {
						echo $err . '</ br>';
					}
				}
				else {
					//check if new authorid or collection id are exist
					if (isIdsExistsInTable($db, 'authors', 'author_id', $selectedAuthorIds) === true) {
						//update books and related table
						//echo '<pre>'.print_r($selectedAuthorIds, true).'</pre>';
						if (!updateBooks($db, $bookId[0], $bookName, $selectedAuthorIds, $selectedCollectionId, $bookNotes) === false) {
							echo 'Успешна редакция!';
						}
						else {
				    		echo 'Грешка при редакция!';
				    	}
					}
					else {
				    	echo 'Грешка! Несъществуващи автори';
				    }
				}
				break;
		}
				
	}	
?>

<form method="POST">
    <div>
		Ново заглание на книга: <input type="text" name="bookName" value=" <?php echo $bookName; ?>" /> <br />
		Избери автор:
	    <?php
			$authors = array();
			$authors = selectAllAuthors($db);
		?>
		<select name="multiAuthors[]" multiple="multiple">
        <?php		
			echo '<pre>'.print_r($authors, true).'</pre>';
			if (!($authors === false)) {
			    //$authors[$authorId] = $authorName;
				foreach($authors as $key =>$author) {
					if ( in_array($key, $selectedAuthors) ) {
						echo '<option name="item" value="' . $key . '" selected >' . $author . '</option>' ;
					}
					else {
						echo '<option name="item" value="' . $key . '">' . $author . '</option>' ;
					}
				}
			}
		?>
		</select> <br />
		Избери колекция:
	    <?php
			$collections = array();
			$collections = selectAllCollections($db);
			//echo '<pre>'.print_r($collections, true).'</pre>';
		?>
		<select name="multiCollections[]" multiple="multiple">
		<?php
			echo '<pre>'.print_r($collections, true).'</pre>';
			if (!($collections === false)) {
			    //$collections[$colletionId] = $colletionName;
				echo 'before foreach';
				foreach($collections as $key =>$collection) {
					echo $collection . ' ------ '. $collectionForUpdate;
					if ( strcmp($collection, $collectionForUpdate) == 0 ) {
	                    echo '<option name="item" value="' . $key . '" selected >' . $collection . '</option>' ;
					}
					else {
						echo '<option name="item" value="' . $key . '">' . $collection . '</option>' ;
					}
				}
			}
		?>
		</select> <br />
		Забележка към книга: <textarea rows="4" cols="50" name="bookNotes"><?php echo $bookNotes;?></textarea>	<br />
    	<input type="submit" name = "submitted" value="Въведи" />
		<input type="submit" name = "submitted" value="Редактирай" />
	</div>
</form>
<?php
include 'includes/footer.php';
?>