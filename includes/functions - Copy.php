<?php
	// ползвам потребител root, за да е по-удобно, когато се проверява домашната
	// with objects
	$db = new mysqli("localhost", "root", "", "books");
	if ($db->connect_errno) {
		echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
	}
	
	if (!$db->set_charset("utf8")) {
		printf("Error loading character set utf8: %s\n", $db->error);
	}

	/* select all records from table authors
	 * input parameter $db - connection
	 * return array like this $authors[$authorId] = $authorName;
	 * return false if any errors 	 
	 * */
	function selectAllAuthors($db) {
		if (!($stmtSelectAllAuthors = $db->prepare(' SELECT author_name, author_id 
													  FROM authors
													 ORDER BY author_name' ))) {
			//echo ' Prepare select failed: (' . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtSelectAllAuthors->execute()) {
			//echo ' Execute select failed: ('  . $stmtSelectAllAuthors->errno . ' ) '  . $stmtSelectAllAuthors->error;
			return false;
		}
		
		/* store result */
		$stmtSelectAllAuthors->store_result();
		
		/* bind result variables */
		$stmtSelectAllAuthors->bind_result($authorName, $authorId);

		/* fetch values */
		$authors = array();
		if($stmtSelectAllAuthors->num_rows > 0) {
		    $i = 0;
			while ($row = $stmtSelectAllAuthors->fetch()) {
				$authors[$authorId] = $authorName; 
			}
		}
		$stmtSelectAllAuthors->close();

        //echo print_r($authors, true);
		return $authors;
	}
	
	function isAuthorIdExists($db, $ids) {
		if (!is_array($ids)) {
			return false;
		}
		
		if (! ($stmtIsAuthorIdExists = $db->prepare( 'SELECT * FROM authors WHERE author_id IN(' . implode(',', $ids) . ') ORDER BY author_name') )) {
		    //echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
			return false;
		}
			
		if (!$stmtIsAuthorIdExists->execute()) {
			//echo ' Execute select failed: ('  . $stmtIsAuthorIdExists->errno . ' ) '  . $stmtIsAuthorIdExists->error;
			return false;
		}
			
		/* store result */
		$stmtIsAuthorIdExists->store_result();
		
		if($stmtIsAuthorIdExists->num_rows > 0 == count($ids)) {
			return true;
		}
		
		return false;
	}
	
	/*checks if author exists in authors table 
	 * inputted parameters: $db - connection, $names - array with names of authors
	 * return true if authors exists
	 * return false if not
	 * */
	function isAuthorNameExists($db, $names) {
		
		if (!is_array($names)) {
			return false;
		}
		
		if (! ($stmtIsAuthorNameExists = $db->prepare("SELECT * FROM authors WHERE author_name IN('" . implode("','", $names) . "')") )) {
			//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtIsAuthorNameExists->execute()) {
			//echo ' Execute select failed: ('  . $stmtIsAuthorNameExists->errno . ' ) '  . $stmtIsAuthorNameExists->error;
			return false;
		}
		
		/* store result */
		$stmtIsAuthorNameExists->store_result();
		
		if($stmtIsAuthorNameExists->num_rows == count($names)) {
			return true;
		}
		
		return false;
	}
	
	/* selects all book_title, author_name, auhtor_id by inputted author ids
	 * input parameters $db - connection, $authorIds - author's id
	 * if $authorIds is empty select by all authors
	 * return array of selection 
	 * return false if any errors
	 * */
	function selectAllBooksByAuthors($db, $authorIds) {
		if (!is_array($authorIds) || count($authorIds) <= 0) {
			$whereStmt = '';
		}
		else{
			$whereStmt = ' WHERE ba.author_id IN(' . implode(',', $authorIds) . ')';
		}
		
		//echo $whereStmt;
		if (!($stmtSelectAllBooksByAuthors = $db->prepare(' SELECT DISTINCT b.book_title, a.author_name, a.author_id
			                                                  FROM books_authors as ba
													    INNER JOIN books as b
														        ON ba.book_id = b.book_id
														INNER JOIN books_authors as bba
														 	    ON bba.book_id = ba.book_id
														INNER JOIN authors as a
															    ON bba.author_id = a.author_id ' . $whereStmt 
													  . ' ORDER BY b.book_title, a.author_name'))) {
			//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtSelectAllBooksByAuthors->execute()) {
			//echo ' Execute select failed: ('  . $stmtSelectAllBooksByAuthors->errno . ' ) '  . $stmtSelectAllBooksByAuthors->error;
			return false;
		}
		
		/* store result */
		$stmtSelectAllBooksByAuthors->store_result();
		
		/* bind result variables */
		$stmtSelectAllBooksByAuthors->bind_result($bookName, $authorName, $authorId);
		
		/* fetch values */
		$booksByAuthors = array();
		if($stmtSelectAllBooksByAuthors->num_rows > 0) {
			while ($row = $stmtSelectAllBooksByAuthors->fetch()) {
				$booksByAuthors[$bookName][$authorId] = $authorName;
			}
			
		}
		$stmtSelectAllBooksByAuthors->close();
		
		//echo '<pre>'.print_r($booksByAuthors, true).'</pre>';
		return $booksByAuthors;
	}
	
	/* check if field's lenght in interval [$minLenght, $maxLenght]
	 * return true if yes, else false
	*/
	function checkLenght($checkField, $minLenght, $maxLenght) {
		$res = false;
		if ($minLenght <= mb_strlen($checkField) && mb_strlen($checkField) <= $maxLenght){
			$res = true;
		}
		
		return $res;
    }
	
	/* checks inputted values by different fields category 
	 * input parameters:$db -> connection, 
	 *                  $fieldValue - value of field, 
	 *                  $fieldCateg - category of field
	 * return string error if has errors, 
	 * */
	function validateInputtedValue($db, $fieldValue, $fieldCateg) {
		$err = array();
		if ($fieldCateg == 'authorName') {
			
			//check for lenght
			if(!checkLenght($fieldValue, 3, 250)) {
				$err[] = "Дължината на автора трябва да е между 3 и 250 символа.";
			}
			
			// check for exists
			$authorName[] = $fieldValue;
			if (isAuthorNameExists($db, $authorName)) {
				$err[] = "Автор с това име вече съществува, моля пробвайте с друго име.";
			}
		}
		
		if ($fieldCateg == 'bookName') {
			
			//check for lenght
			if(!checkLenght($fieldValue, 3, 250)) {
				$err[] = "Заглавието на книгата може да бъде между 3 и 250 символа.";
			}
			
			// check for exists
			$booksName[] = $fieldValue;
			if (isBooksNameExists($db, $booksName)) {
				$err[] = "Книгата вече съществува, моля пробвайте с друго заглавие.";
			}
		}
		
		return $err;
	}
	
	/* insert in author table by authorName 
	*  return inserted id
	* */
	function insertAuthorByName($db, $authorName) {
		
		if (strlen($authorName) <=0) {
			return false;
		}
		
		if (!($stmtInputAuthor = $db->prepare("INSERT INTO authors(author_name) 
													VALUES (?)"))) {
			//echo "Prepare insert failed: (" . $db->errno . ") " . $db->error;
			return false;
		}

		if (!$stmtInputAuthor->bind_param("s", $authorName)) {
			//echo "Binding insert parameters failed: (" . $stmtInputAuthor->errno . ") " . $stmtInputAuthor->error;
			return false;
		}
		if (!$stmtInputAuthor->execute()) {
			//echo "Execute insert failed: (" . $stmtInputAuthor->errno . ") " . $stmtInputAuthor->error;
			return false;
		}
		
		return $stmtInputAuthor->insert_id;
	}
	
	
?>