<?php
mb_internal_encoding('UTF-8');
$pageTitle='Списък с книги и автори';
include 'includes/header.php';
?>
<a href="books.php"> Нова книга </a> | 
<a href="authors.php"> Нов автор </a> | 
<a href="collections.php"> Нова поредица </a> |
<p></p>
<p>Списък:</p>
<table border="1">
	<tr>
		<th>Книги</th>
		<th>Автори</th>
		<th>Поредица</th>
		<th>Забележки</th>
		<th>Редактирай</th>
	</tr>
 <?php
	$authorId = array();
	$booksByAuthor = array();
	$collectionsNames = array();
	$bookNotes = '';
	
	if (isset($_GET['author_id'])) {
		$authorId[] = (int) $_GET['author_id'];
	}
	
	$booksByAuthor = selectAllBooksByAuthors($db, $authorId, []);
	//echo '<pre>'.print_r($authorId, true).'</pre>';
	//echo '<pre>'.print_r($booksByAuthor, true).'</pre>';

	if(!($booksByAuthor === false)) {
		foreach ($booksByAuthor as $book => $row) {
           	//echo '<tr><td><a href="messages.php?book_id=' . $book . '">' . $row['bookName'] . '</a></td>
			//<td> <a href="books.php?book_id=' . $book . '></a></td>
			echo '<tr><td>' . $row['bookName'] . '</td>
			<td>';
			$result = array();
			foreach ($row['authors'] as $key => $author) {
				$result[] = '<a href="index.php?author_id=' . $key . '">' . $author . '</a>';
			}
			echo implode(' , ', $result) . '</td>' .
			'<td> '. $row['collectionName'] .' </td>
			 <td> ' . $row['bookNotes'] . ' </td>
			 <td> <a href="books.php?book_id=' . $book. '"> Редакция </a></td>
			 </tr>';
		}
	}			
?>
</table>
<?php
include 'includes/footer.php';
?>
