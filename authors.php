<?php
mb_internal_encoding('UTF-8');
$pageTitle = 'Въвеждане на нов автор';
include 'includes/header.php';
?>
<?php 
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
			$newAuthorId = insertAuthorByName($db, $authorName);
			if ($newAuthorId === false) {
				echo 'Грешка при въвеждане на автора!';
			}
			else {
				echo 'Успешно въвеждане на автора!';
			}
		}	
	}
?>
<a href="index.php"> Към общия списък с книги и автори </a>
<p>Въвеждане на нов автор:</p>
<form method="POST">
    <div>Име на нов автор:
	     <input type="text" name="authorName" />
	     <input type="submit" value="Въведи" />
	</div>
</form>
<p></p>
<table border = "1">
	<tr>
		<th>Автори</th>
		<th></th>
	</tr>
	<?php
		$authors = array();
		$authors = selectAllAuthors($db);
		if (!($authors === false)) {
			foreach($authors as $key => $author) {
				//echo '<tr><td><a href="index.php?author_id=' . $key . '> ' . $author . ' </a></td></tr>' ;
				echo '<tr><td> ' . $author . ' </td>' ;
				echo '<td><a href="updateAuthors.php?update_author=' . $key . '"> Редактирай </a></td></tr>'; 
			}
		}
	?>
</table>
<?php
include 'includes/footer.php';
?>