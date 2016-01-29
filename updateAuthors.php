<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Редактиране на автор';
include 'includes/header.php';

	if (isset($_GET['update_author'])) {
		$authorId = (int) $_GET['update_author'];
		$authorOldName = returnAuthorNameById($db, $authorId);
	}
	if($_POST){		
		//check inputted author name
		$authorName = $db->real_escape_string(trim($_POST['authorName']));
		$errMsg = array();
		$errMsg = validateInputtedValue($db, $authorName, 'authorName');
		
		if (count($errMsg)>0) {    
			foreach($errMsg as $err) {
				echo $err . '</ br>';
			}
		}
		else {
			if (updateAuthorByName($db, $authorName, $authorId) === false) {
				echo 'Грешка при редакция на автора!';
			}
			else {
				echo 'Успешна редакция на автора!';
			}
		}	
	}
?>
<p>Редактиране на автор:</p>
<form method="POST">
    <div>Име на автор:
	     <input type="text" name="authorName" value="<?php echo $authorOldName; ?>"/>
	     <input type="submit" value="Редактирай" />
	</div>
</form>
<p></p>
<?php
	include 'includes/footer.php';
?>