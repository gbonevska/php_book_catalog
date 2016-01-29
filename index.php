<?php
mb_internal_encoding('UTF-8');
$pageTitle='Списък с книги и автори';
include 'includes/header.php';
?>
<a href="books.php"> Нова книга </a> | 
<a href="authors.php"> Автори </a> | 
<a href="collections.php"> Колекции </a> |
<p></p>
<p>Списък:</p>
<table border="1">
	<tr>
		<th>Книги</th>
		<th>Автори</th>
		<th>Колекция</th>
		<th>Забележки</th>
		<th>--------</th>
	</tr>
 <?php
	$authorId = array();
	$booksByAuthor = array();
	$collectionId = array();
	$bookNotes = '';
	
	if (isset($_GET['author_id'])) {
		$authorId[] = (int) $_GET['author_id'];
	}
	
	if (isset($_GET['collection_id'])) {
		$collectionId[] = (int) $_GET['collection_id'];
	}
	
	if (isset($_GET['book_id'])) {
		$bookDeleteId = (int) $_GET['book_id'];
		deleteBook($db, $bookDeleteId);
	}
	
	$temp = array();
	$booksDisplay = array();
	if(empty($collectionId) === true) {
		$booksDisplay = selectAllBooksByAuthors($db, $authorId, $temp);
	} else {	
		$booksDisplay = selectAllBooksByCollections($db, $collectionId);
	}
	//$booksByAuthor = selectAllBooksByAuthors($db, $authorId, $temp);
	//$booksByCollection = selectAllBooksByCollections($db, $collectionId);
	
	if(!($booksDisplay === false)) {
		//echo '<pre>'.print_r($booksDisplay, true).'</pre>';
		foreach ($booksDisplay as $book => $row) {
			echo '<tr><td><a href="updateBooks.php?book_id=' . $book . '">' . $row['bookName'] . '</a></td><td>';
			$result = array();
			foreach ($row['authors'] as $key => $author) {
				$result[] = '<a href="index.php?author_id=' . $key . '">' . $author . '</a>';
			}
			echo implode(' , ', $result) . '</td>' .'<td>';
			//'<td> '. implode(' , ', $row['collectionName']) .' </td>
			$result2 = array();
			foreach ($row['collectionName'] as $key => $collection) {
				$result2[] = '<a href="index.php?collection_id=' . $key . '">' . $collection . '</a>';
			}
			echo implode(' , ', $result2) . '</td>
			<td> ' . $row['bookNotes'] . ' </td> 
			<td> <a href="index.php?book_id=' . $book . '">Изтрий </a></td></tr>';
		}
	}			
?>
</table>
<?php
include 'includes/footer.php';
?>
