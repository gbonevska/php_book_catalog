<?php
	$db = new mysqli("localhost", "", "", "ggbuchen_katalog_knigi");
	if ($db->connect_errno) {
		echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
	}
	
	if (!$db->set_charset("utf8")) {
		printf("Error loading character set utf8: %s\n", $db->error);
	}
	
	//$TEST = false;
	
	/* select book data from tables books,
	* input parameters $db - connection, $bookId - id from table books
	* return array with book's details = title, notes, authors, collections
	* return false if any errors
	*/
	function selectBookDetails($db, $bookId) {
		if ($bookId < 0) {
			return false;
		}

		if (!($stmtSelectBooksDetails = $db->prepare(' SELECT DISTINCT b.book_title, 
			                                                       a.author_name, 
			                                                       a.author_id, 
			                                                       ba.collection_id,
			                                                       c.collection_name,
			                                                       b.notes
			                                                  FROM books_authors as ba
													    INNER JOIN books as b
														        ON ba.book_id = b.book_id
														INNER JOIN books_authors as bba
														 	    ON bba.book_id = ba.book_id
														INNER JOIN authors as a
															    ON bba.author_id = a.author_id 
														LEFT OUTER JOIN collections c
                                                                ON c.collection_id = ba.collection_id
														WHERE b.book_id = '.$bookId.'
														ORDER BY b.book_title, a.author_name'))) {
			//echo ($GLOBALS[$TEST] ? ' Prepare select failed: (' . $db->errno . ' ) ' . $db->error : '');
			return false;
		}
		
		if (!$stmtSelectBooksDetails->execute()) {
			echo ' Execute select failed: (' . $stmtSelectBooksDetails->errno . ' ) ' . $stmtSelectBooksDetails->error;
			return false;
		}
		
		// store result
		$stmtSelectBooksDetails->store_result();
		
		// bind result variables 
		$stmtSelectBooksDetails->bind_result($bookName, $authorName, $authorId, 
			                                 $collectionId, $collectionName, $bookNotes);
		
		// fetch values
		$bookDetails = array();
		if($stmtSelectBooksDetails->num_rows > 0) {
			while ($row = $stmtSelectBooksDetails->fetch()) {
				$bookDetails['bookName'] = $bookName;
				$bookDetails['authors'][$authorId] = $authorName;
				$bookDetails['collectionName'][$collectionId] = $collectionName; 
				$bookDetails['bookNotes'] = $bookNotes;
			}
			
		}
		$stmtSelectBooksDetails->close();
		
		//echo '<pre>'.print_r($bookDetails, true).'</pre>';
		return $bookDetails;
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
	
	/* select books related to author
	 * input parameters $db, $authorId
	 * return array with book's ids
	 */
	function findBooksByAuthor($db, $authorId){
		if($authorId < 0){
			return false;
		}
		
		if (!($stmtSelectBooksForAuthor = $db->prepare(' SELECT count(book_id)
													       FROM books_authors
													 WHERE author_id = '.$authorId ))) {
			echo ' Prepare select failed: (' . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtSelectBooksForAuthor->execute()) {
			echo ' Execute select failed: ('  . $stmtSelectBooksForAuthor->errno . ' ) '  . $stmtSelectBooksForAuthor->error;
			return false;
		}
		
		/* store result */
		$stmtSelectBooksForAuthor->store_result();
		
		/* bind result variables */
		$stmtSelectBooksForAuthor->bind_result($books);
		
		if($stmtSelectBooksForAuthor->num_rows > 0) {
			while ($row = $stmtSelectBooksForAuthor->fetch()) {
				$result = $books;
				return $books;
			}
		}
		
		$stmtSelectBooksForAuthor->close();

       return 0; 
	}
	
	/* delete author id from table authors
	 * input parameters - $db, $authorId
	 * return true if OK,
	 * return false if errors
	 */
	 function deleteAuthors($db, $authorId){
		
		if($authorId < 0){
			return false;
		}
		
		if (!($stmtDelete = $db->prepare("DELETE FROM authors 
										  WHERE author_id=".$authorId))) {
			echo "Prepare delete authors failed: (" . $db->errno . ") " . $db->error;
			return false;
		}

		if (!$stmtDelete->execute()) {
			echo "Execute delete authors failed: (" . $stmtDelete->errno . ") " . $stmtDelete->error;
			return false;
		}
		
		return true;
	 }
	
	/* select all records from table collections
	 * input parameter $db - connection
	 * return array like this $collections[$collectionId] = $collectionName;
	 * return false if any errors 	 
	 * */
	function selectAllCollections($db) {
		if (!($stmtSelectAllCollections = $db->prepare(' SELECT collection_name, collection_id 
		                                                   FROM collections
		                                                  ORDER BY collection_name' ))) {
			//echo ' Prepare select failed: (' . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtSelectAllCollections->execute()) {
			//echo ' Execute select failed: ('  . $stmtSelectAllCollections->errno . ' ) '  . $stmtSelectAllCollections->error;
			return false;
		}
		
		/* store result */
		$stmtSelectAllCollections->store_result();
		
		/* bind result variables */
		$stmtSelectAllCollections->bind_result($collectionName, $collectionId);
		
		/* fetch values */
		$collections = array();
		if($stmtSelectAllCollections->num_rows > 0) {
			while ($row = $stmtSelectAllCollections->fetch()) {
				$collections[$collectionId] = $collectionName; 
			}
		}
		$stmtSelectAllCollections->close();
		
		//echo print_r($collections, true);
		return $collections;
	}
	
	/* delete collection records
	 * input parameters: $db - connection, $collectionId
	 * return true if OK
	 * false if errors
	 */
	function deleteCollection($db, $collectionId) {
		if($collectionId < 0) {
			return false;
		}	
		
		$books = 0;
		if (!($stmtSelect = $db->prepare('SELECT count(ca.book_id) 
											FROM books_authors ba
											INNER JOIN books_authors ca 
											ON ba.book_id = ca.book_id
											WHERE ba.author_id = ca.author_id
											AND ba.collection_id != ca.collection_id
											AND ca.collection_id = '.$collectionId ))) {
			echo ' Prepare select failed: (' . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtSelect->execute()) {
			echo ' Execute select failed: ('  . $stmtSelect->errno . ' ) '  . $stmtSelect->error;
			return false;
		}
		
		/* store result */
		$stmtSelect->store_result();
		
		/* bind result variables */
		$stmtSelect->bind_result($booksCount);

		/* fetch values */
		if($stmtSelect->num_rows > 0) {
			while ($row = $stmtSelect->fetch()) {
				$books = $booksCount; 
			}
		}
		$stmtSelect->close();
		
		
		if($books == 0) {
			if (!($stmtUpdateRelation = $db->prepare("UPDATE books_authors 
														 SET collection_id = NULL 
													   WHERE collection_id=".$collectionId))) {
				echo "Prepare update books_authors failed: (" . $db->errno . ") " . $db->error;
				return false;
			}

			if (!$stmtUpdateRelation->execute()) {
				echo "Execute update books_authors failed: (" . $stmtUpdateRelation->errno . ") " . $stmtUpdateRelation->error;
				return false;
			}
		} else {
			// if there are other records in books_authors for the same book, we should delete the record with this collection_id
			if (!($stmtDeleteRelation = $db->prepare("DELETE FROM books_authors 
													  WHERE collection_id=".$collectionId))) {
				echo "Prepare delete books_authors failed: (" . $db->errno . ") " . $db->error;
				return false;
			}

			if (!$stmtDeleteRelation->execute()) {
				echo "Execute delete books_authors failed: (" . $stmtDeleteRelation->errno . ") " . $stmtDeleteRelation->error;
				return false;
			}
		}
		
		if (!($stmtDelete = $db->prepare("DELETE FROM collections 
		                                   WHERE collection_id=".$collectionId))) {
			echo "Prepare delete collections failed: (" . $db->errno . ") " . $db->error;
			return false;
		}

        if (!$stmtDelete->execute()) {
			echo "Execute delete collections failed: (" . $stmtDelete->errno . ") " . $stmtDelete->error;
			return false;
		}
		
		return true;
	}
	
	
	/* check if ids exist in some table
	 * input parameters
	 * $db - connection, 
	 * $table - table name for search, 
	 * $column - column name consists ids from above table, 
	 * $ids - array with id values for search
	 * return true if there are find any records in table equal to input ids
	 * return false if error
	 */
	function isIdsExistsInTable($db, $table, $column, $ids) {
		if (!is_array($ids)) {
			return false;
		}
		
		if (! ($stmtIsAuthorIdExists = $db->prepare( 'SELECT * 
		                                                FROM ' . $table . //authors 
		                                               ' WHERE ' . $column . //author_id 
													   ' IN(' . implode(',', $ids) . ') ' 
													   //ORDER BY author_name'
													   ) )) {
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
	 * inputted parameters: $db - connection, $names - array with searchable name values 
	 * return true if records with searchable values exist
	 * return false if not
	 * */
	function isNameExistsInTable($db, $names, $table, $tableField) {
		
		if (!is_array($names)) {
			return false;
		}
		
		if (strlen($table) <= 0 || strlen($tableField) <= 0) {
			return false;
		}
		
		if (! ($stmtIsNameExists = $db->prepare("SELECT * 
		                                           FROM " . $table .
		                                        " WHERE " . $tableField . " IN('" . implode("','", $names) . "')") )) {
			//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtIsNameExists->execute()) {
			//echo ' Execute select failed: ('  . $stmtIsNameExists->errno . ' ) '  . $stmtIsNameExists->error;
			return false;
		}
		
		/* store result */
		$stmtIsNameExists->store_result();
		
		if($stmtIsNameExists->num_rows == count($names)) {
			return true;
		}
		
		return false;
	}
	
	/* selects all book_title, author_name, auhtor_id, collection_name, book notes
	 * by inputted author ids
	 * input parameters $db - connection, $authorIds - author's id
	 * if $authorIds is empty select by all authors
	 * return array of selection 
	 * return false if any errors
	 * */
	function selectAllBooksByAuthors($db, $authorIds, $bookIds) {
		
		if (!is_array($authorIds)  || !is_array($bookIds) || count($authorIds) <= 0 || count($bookIds) <=0) {
			$whereStmt = '';
		}
		if (count($authorIds)>0){
			$whereStmt = ' WHERE ba.author_id IN(' . implode(',', $authorIds) . ')';
		}
		
		if (count($bookIds)>0 && strlen($whereStmt)>0){
			$whereStmt = $whereStmt . ' AND ba.book_id IN(' . implode(',', $bookIds) . ')';
		}
		else if (count($bookIds)>0 ) {
			$whereStmt = ' WHERE ba.book_id IN(' . implode(',', $bookIds) . ')';
		}
		
		//echo $whereStmt;
		if (!($stmtSelectAllBooksByAuthors = $db->prepare(' SELECT DISTINCT b.book_title, 
			                                                       a.author_name, 
			                                                       a.author_id, 
			                                                       b.book_id,
			                                                       ba.collection_id,
			                                                       c.collection_name,
			                                                       b.notes
			                                                  FROM books_authors as ba
													    INNER JOIN books as b
														        ON ba.book_id = b.book_id
														INNER JOIN books_authors as bba
														 	    ON bba.book_id = ba.book_id
														INNER JOIN authors as a
															    ON bba.author_id = a.author_id 
														LEFT OUTER JOIN collections c
                                                                ON c.collection_id = ba.collection_id
														' . $whereStmt 
													  . ' ORDER BY b.book_title, a.author_name'))) {
			echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtSelectAllBooksByAuthors->execute()) {
			echo ' Execute select failed: ('  . $stmtSelectAllBooksByAuthors->errno . ' ) '  . $stmtSelectAllBooksByAuthors->error;
			return false;
		}
		
		/* store result */
		$stmtSelectAllBooksByAuthors->store_result();
		
		/* bind result variables */
		$stmtSelectAllBooksByAuthors->bind_result($bookName, $authorName, $authorId, $bookId, 
			                                      $collectionId, $collectionName, $bookNotes);
		
		/* fetch values */
		$booksByAuthors = array();
		if($stmtSelectAllBooksByAuthors->num_rows > 0) {
			while ($row = $stmtSelectAllBooksByAuthors->fetch()) {
				$booksByAuthors[$bookId]['bookName'] = $bookName;
				$booksByAuthors[$bookId]['authors'][$authorId] = $authorName;
				//$booksByAuthors[$bookId]['collectionName'] = $collectionName;
				$booksByAuthors[$bookId]['collectionName'][$collectionId] = $collectionName;
				$booksByAuthors[$bookId]['bookNotes'] = $bookNotes;
			}
			
		}
		$stmtSelectAllBooksByAuthors->close();
		
		//echo '<pre>'.print_r($booksByAuthors, true).'</pre>';
		return $booksByAuthors;
	}
	
    /* selects all book_title, author_name, auhtor_id, collection_name, book notes
	 * by inputted collection ids
	 * input parameters $db - connection, $collectionIds 
	 * if $collectionIds is empty select by all collections
	 * return array of selection 
	 * return false if any errors
	 * */
	function selectAllBooksByCollections($db, $collectionIds) {
		if (!is_array($collectionIds) || count($collectionIds) <= 0 ) {
			$whereStmt = '';
		}
		if (count($collectionIds)>0){
			$whereStmt = ' WHERE ba.collection_id IN(' . implode(',', $collectionIds) . ')';
		}
		
		if (!($selectAllBooksByCollections = $db->prepare(' SELECT DISTINCT b.book_title, 
			                                                       a.author_name, 
			                                                       a.author_id, 
			                                                       b.book_id,
			                                                       ba.collection_id,
			                                                       c.collection_name,
			                                                       b.notes
			                                                  FROM books_authors as ba
													    INNER JOIN books as b
														        ON ba.book_id = b.book_id
														INNER JOIN books_authors as bba
														 	    ON bba.book_id = ba.book_id
														INNER JOIN authors as a
															    ON bba.author_id = a.author_id 
														LEFT OUTER JOIN collections c
                                                                ON c.collection_id = ba.collection_id
														' . $whereStmt 
													  . ' ORDER BY b.book_title, a.author_name'))) {
			echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$selectAllBooksByCollections->execute()) {
			echo ' Execute select failed: ('  . $selectAllBooksByCollections->errno . ' ) '  . $selectAllBooksByCollections->error;
			return false;
		}
		
		/* store result */
		$selectAllBooksByCollections->store_result();
		
		/* bind result variables */
		$selectAllBooksByCollections->bind_result($bookName, $authorName, $authorId, $bookId, 
			                                      $collectionId, $collectionName, $bookNotes);
		
		/* fetch values */
		$booksByCollections = array();
		if($selectAllBooksByCollections->num_rows > 0) {
			while ($row = $selectAllBooksByCollections->fetch()) {
				$booksByCollections[$bookId]['bookName'] = $bookName;
				$booksByCollections[$bookId]['authors'][$authorId] = $authorName;
				$booksByCollections[$bookId]['collectionName'][$collectionId] = $collectionName;
				$booksByCollections[$bookId]['bookNotes'] = $bookNotes;
			}
			
		}
		$selectAllBooksByCollections->close();
		
		//echo '<pre>'.print_r($booksByCollections, true).'</pre>';
		return $booksByCollections;
	}
	
	/* delete book records
	 * input parameters
	 * $db - connection
	 * $bookDeleteId - book id
	 */
	function deleteBook($db, $bookDeleteId){
		if ($bookDeleteId < 0) {
			return false;
		}
		
		if (!($stmtDelete = $db->prepare("DELETE FROM books WHERE book_id=".$bookDeleteId))) {
			echo "Prepare delete books failed: (" . $db->errno . ") " . $db->error;
			return false;
		}

        if (!$stmtDelete->execute()) {
			echo "Execute delete books failed: (" . $stmtDelete->errno . ") " . $stmtDelete->error;
			return false;
		}
		
		if (!($stmtDeleteRelation = $db->prepare("DELETE FROM books_authors WHERE book_id=".$bookDeleteId))) {
			echo "Prepare delete books_authors failed: (" . $db->errno . ") " . $db->error;
			return false;
		}

        if (!$stmtDeleteRelation->execute()) {
			echo "Execute delete books_authors failed: (" . $stmtDeleteRelation->errno . ") " . $stmtDeleteRelation->error;
			return false;
		}
		
		return true;
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
			if (isNameExistsInTable($db, $authorName, 'authors', 'author_name')) {
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
			if (isNameExistsInTable($db, $booksName, 'books', 'book_title')) {
				$err[] = "Книгата вече съществува, моля пробвайте с друго заглавие.";
			}
		}
		
		if ($fieldCateg == 'userName') {
			//check for lenght
			if(!checkLenght($fieldValue, 3, 15)) {
				$err[] = "Дължината на потребителското име трябва да е между 3 и 15 символа.";
			}
		}

		if ($fieldCateg == 'bookNotes') {
			//check for lenght
			if(!checkLenght($fieldValue, 0, 1000)) {
				$err[] = "Дължината на забележката трябва да е между 0 и 1000 символа.";
			}
		}

		if ($fieldCateg == 'newUserName') {
		
			// check for exists
			$userName[] = $fieldValue;
			if (isNameExistsInTable($db, $userName, 'users', 'user_name')) {
				$err[] = "Потребителското име вече съществува, моля пробвайте с друго име.";
			}
		}
		
		if ($fieldCateg == 'userPass') {
			//check for lenght
			if(!checkLenght($fieldValue, 3, 15)) {
				$err[] = "Дължината на потребителската парола трябва да е между 3 и 15 символа.";
			}
		}
		
		if ($fieldCateg == 'msgTitle') {
			//check for lenght
			if(!checkLenght($fieldValue, 1, 50)) {
				$err[] = "Заглавието на съобщението трябва да съдържа от 1 до 50 символа.";
			}
		}
		
		if ($fieldCateg == 'msgText') {
			//check for lenght
			if(!checkLenght($fieldValue, 1, 250)) {
				$err[] = "Съдържанието на съобщението трябва да съдържа от 1 до 250 символа.";
			}
		}
		
		return $err;
	}
	
	/* return bookName value from table books selected by bookId
	 * if errors return false
	 * */
    function returnBookNameById($db, $bookId) {
		
		if (strlen($bookId) < 0) {
			return false;
		}
		
		$bookIds[] = $bookId;
		if (isIdsExistsInTable($db, 'books', 'book_id', $bookIds) === true) {
			if ( !($stmtBookName = $db->prepare("SELECT book_title
			                                         FROM books 
			                                        WHERE book_id='" . $bookId . "'") )) {
				//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
				return false;
			}
			
			if (!$stmtBookName->execute()) {
				//echo ' Execute select failed: ('  . $stmtBookName->errno . ' ) '  . $stmtBookName->error;
				return false;
			}
			
			// store result 
			$stmtBookName->store_result();
			
			// bind result variables 
			$stmtBookName->bind_result($bookName);
			
			if($stmtBookName->num_rows == 1) {
				while ($row = $stmtBookName->fetch()) {
					return $bookName;  
				}
			}
	    }
		return false;
	}
	
	/* return collectionName value from table collectionss selected by collectionId
	 * if errors return false
	 * */
	function returnCollectionNameById($db, $collectionId) {
		if (strlen($collectionId) < 0) {
			return false;
		}
		
		$collectionIds[] = $collectionId;
		if (isIdsExistsInTable($db, 'collections', 'collection_id', $collectionIds) === true) {
			if ( !($stmtCollectionName = $db->prepare("SELECT collection_name
			                                            FROM collections 
			                                           WHERE collection_id='" . $collectionId . "'") )) {
				//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
				return false;
			}
			
			if (!$stmtCollectionName->execute()) {
				echo ' Execute select failed: ('  . $stmtCollectionName->errno . ' ) '  . $stmtCollectionName->error;
				//return false;
			}
			
		 /* store result */
			$stmtCollectionName->store_result();
			
		 /* bind result variables */
			$stmtCollectionName->bind_result($collectionName);
			
			if($stmtCollectionName->num_rows == 1) {
				while ($row = $stmtCollectionName->fetch()) {
					return $collectionName;  
				}
			}
	    }
		return false;
	}
	
	
	/* return authorName value from table authors selected by authorId
	 * if errors return false
	 * */
    function returnAuthorNameById($db, $authorId) {
		
		if (strlen($authorId) < 0) {
			return false;
		}
		
		$authorIds[] = $authorId;
		if (isIdsExistsInTable($db, 'authors', 'author_id', $authorIds) === true) {
			if ( !($stmtAuthorName = $db->prepare("SELECT author_name
			                                         FROM authors 
			                                        WHERE author_id='" . $authorId . "'") )) {
				//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
				return false;
			}
			
			if (!$stmtAuthorName->execute()) {
				//echo ' Execute select failed: ('  . $stmtAuthorName->errno . ' ) '  . $stmtAuthorName->error;
				return false;
			}
			
			/* store result */
			$stmtAuthorName->store_result();
			
			/* bind result variables */
			$stmtAuthorName->bind_result($authorName);
			
			if($stmtAuthorName->num_rows == 1) {
				while ($row = $stmtAuthorName->fetch()) {
					return $authorName;  
				}
			}
	    }
		return false;
	}

	/* update in table books according to parameters
	 * input parameters $db - connection, $bookId, $bookName, $bookNotes, $bookCollections, $bookAthors 
	 * //$newBookDetails['authors'][$authorId] = $authorName;
	 * //$newBookDetails['collectionName'][$collectionId] = $collectionName; 
	 * return true if OK
	 * return false if error 
	 * */
	function updateBooks($db, $bookId, $bookName, $bookNotes, $bookCollections, $bookAthors) {
		//echo '<pre>'.print_r($bookCollections, true).'</pre>';
		//echo '<pre>'.print_r($bookAthors, true).'</pre>';
		
		if ((strlen($bookName) <=0 && $bookId <= 0) 
		    && (is_array($bookCollections) && is_array($bookAthors))){
			return false;
		}
		
		// update book title and book notes
		if (!($stmtUpdate = $db->prepare("UPDATE books SET book_title = '" . $bookName . "', notes = " . (($bookNotes == "NULL") ? "NULL" : "'".$bookNotes."'") . " WHERE book_id=" . $bookId))) {
			echo "Prepare update with book_title and notes failed: (" . $db->errno . ") " . $db->error;
			return false;
		}
		
		if (!$stmtUpdate->execute()) {
			echo "Execute update book failed: (" . $stmtUpdate->errno . ") " . $stmtUpdate->error;
			return false;
		}
		
		if ( count($bookCollections) >0 || count($bookAthors) > 0) {
			// for update of relations in books_authors we need to delete old records and insert the new ones
			if (deleteRelationBookAuthorCollection($db, $bookId) === true) {
				$results = insertRelationBookAuthorCollection($db, $bookId, $bookAthors, $bookCollections);
				
				if (count($results) <= 0) {
					echo "Error on insert in books_authors.";
					return false;
				}
			}
			else {
				echo "Error on delete books_authors.";
				return false;
			}
		}
		return true;		
	}
	

	/* update collectionName in table collections by collectionId
	 * return true if no errors
	 * */
	function updateCollectionByName($db, $collectionName, $collectionId){
		
		$collectionIds[] = $collectionId;
		if (isNameExistsInTable($db, $collectionIds, 'collections', 'collection_id') === true) {
			if (!($stmtInput = $db->prepare("UPDATE collections SET collection_name = '" . $collectionName . "' WHERE collection_id = " . $collectionId))) {
				echo "Prepare insert failed: (" . $db->errno . ") " . $db->error;
				return false;
			}
			
			if (!$stmtInput->execute()) {
				//echo "Execute insert failed: (" . $stmtInput->errno . ") " . $stmtInput->error;
				return false;
			}
			
			return true;
		}
		else {
			//echo 'Error in isNameExistsInTable';
			return false;
		}
		//echo 'General error';
		return false;
		
	}
	
	/* update authorName in table authors by authorId
	 * return true if no errors
	 * */
	function updateAuthorByName($db, $authorName, $authorId){
		
		$authorIds[] = $authorId;
		if (isNameExistsInTable($db, $authorIds, 'authors', 'author_id') === true) {
			if (!($stmtInput = $db->prepare("UPDATE authors SET author_name = '" . $authorName . "' WHERE author_id = " . $authorId))) {
				//echo "Prepare insert failed: (" . $db->errno . ") " . $db->error;
				return false;
			}
			
			if (!$stmtInput->execute()) {
				//echo "Execute insert failed: (" . $stmtInput->errno . ") " . $stmtInput->error;
				return false;
			}
			
			return true;
		}
		else {
			//echo 'Error in isNameExistsInTable';
			return false;
		}
		//echo 'General error';
		return false;
		
	}

	/* insert in table books according to parameters
	 * input parameters $db - connection, $bookName, $bookNotes
	 * return inserted id if OK
	 * return false if error 
	 * */
	function insertBooks($db, $bookName, $bookNotes) {
		
		$insert='';
		
		if (strlen($bookName) <=0) {
			return false;
		}
		
		if (strlen($bookNotes) <= 0) {
			$insert="INSERT INTO books(book_title) VALUES ('" . $bookName . "')";
		}
		else {
			$insert="INSERT INTO books(book_title, notes) VALUES ('" . $bookName . "','" . $bookNotes . "')";
		}
		
		if (!($stmtInput = $db->prepare($insert))) {
			//echo "Prepare insert failed: (" . $db->errno . ") " . $db->error;
			return false;
		}
		
		if (!$stmtInput->execute()) {
			//echo "Execute insert failed: (" . $stmtInput->errno . ") " . $stmtInput->error;
			return false;
		}
		
		return $stmtInput->insert_id;
	}

	/* insert in author table by authorName 
	*  return inserted id
	* */
	function insertAuthorByName($db, $authorName) {
		return insertInDbByName($db, 'authors', 'author_name', $authorName);
	}
	
	/* insert in collection table by collectionName 
	 *  return inserted id
	 * */
	function insertCollectionByName($db, $collectionName) {
		return insertInDbByName($db, 'collections', 'collection_name', $collectionName);
	}
	
	/* insert in tables authors or books according to parameters
	 * input parameters $db - connection, $table - in which table should be insert, 
	 *                  $fieldName - field by table, $fieldValue - field value
	 * return inserted id if OK
	 * return false if error */
	function insertInDbByName($db, $table, $fieldName, $fieldValue) {
	
		if (strlen($fieldName) <=0 || strlen($table) <= 0 || strlen($fieldValue) <= 0) {
			return false;
		}
		
		if (!($stmtInput = $db->prepare("INSERT INTO " . $table . "(" . $fieldName . ") VALUES ('" . $fieldValue . "')"))) {
			//echo "Prepare insert failed: (" . $db->errno . ") " . $db->error;
			return false;
		}
		
		if (!$stmtInput->execute()) {
			//echo "Execute insert failed: (" . $stmtInput->errno . ") " . $stmtInput->error;
			return false;
		}
		
		return $stmtInput->insert_id;
	}
	
	/* insert records in table books_authors 
	 * input parameters $db - connection, 
	 * $newBookId - book_id, 
	 * $selectedAuthorIds - array of values with author_id
	 * $bookCollectionIds - array of values with collection_id
	 * return array with inserted ids if OK
	 * return false if errors 
	 * */
	function insertRelationBookAuthorCollection($db, $newBookId, $selectedAuthorIds, $bookCollectionIds) {
	/*
		 echo "book id= " . $newBookId;
		 echo 'authors id= <pre>'.print_r($selectedAuthorIds, true).'</pre>' . 'count='.count($selectedAuthorIds); 
		 echo 'collections id= <pre>'.print_r($bookCollectionIds, true).'</pre>';
	*/	
		
		 if ($newBookId < 0 || count($selectedAuthorIds) <= 0) {
			return false;
		}
		
		$insertedIds = array();
		
		if (count($bookCollectionIds) >0 ) {
			for($i=0;$i<count($selectedAuthorIds);$i++) {
				for($j=0;$j<count($bookCollectionIds);$j++) {
			
					if (!($stmtInputRelation = $db->prepare("INSERT INTO books_authors(author_id, book_id, collection_id) 
																	VALUES (?, ?, ?)"))) {
						//echo "Prepare insert books_authors failed: (" . $db->errno . ") " . $db->error;
						return false;
					}
					
					if (!$stmtInputRelation->bind_param("iii", $selectedAuthorIds[$i], $newBookId, $bookCollectionIds[$j])) {
						//echo "Binding insert books_authors parameters failed: (" . $stmtInputRelation->errno . ") " . $stmtInputRelation->error;
						return false;
					}
					if (!$stmtInputRelation->execute()) {
						//echo "Execute insert books_authors failed: (" . $stmtInputRelation->errno . ") " . $stmtInputRelation->error;
						return false;
					}
				
					$insertedIds[] = $stmtInputRelation->insert_id;
				}
			}
		}
		else {
			for($i=0;$i<count($selectedAuthorIds);$i++) {
				if (!($stmtInputRelation = $db->prepare("INSERT INTO books_authors(author_id, book_id) 
																VALUES (?, ?)"))) {
					//echo "Prepare insert books_authors failed: (" . $db->errno . ") " . $db->error;
					return false;
				}
				
				if (!$stmtInputRelation->bind_param("ii", $selectedAuthorIds[$i], $newBookId)) {
					//echo "Binding insert books_authors parameters failed: (" . $stmtInputRelation->errno . ") " . $stmtInputRelation->error;
					return false;
				}
				if (!$stmtInputRelation->execute()) {
					//echo "Execute insert books_authors failed: (" . $stmtInputRelation->errno . ") " . $stmtInputRelation->error;
					return false;
				}
			
				$insertedIds[] = $stmtInputRelation->insert_id;
			}
		}
		
		return $insertedIds;
	}

	
	/* delete records in table books_authors 
	 * input parameters $db - connection, 
	 * $bookId - book_id, 
	 * return true if OK
	 * return false if errors 
	 * */
	function deleteRelationBookAuthorCollection($db, $bookId) {

		if ($bookId < 0) {
			return false;
		}
		
		if (!($stmtDeleteRelation = $db->prepare("DELETE FROM books_authors WHERE book_id=".$bookId))) {
			//echo "Prepare delete books_authors failed: (" . $db->errno . ") " . $db->error;
			return false;
		}

        if (!$stmtDeleteRelation->execute()) {
			//echo "Execute delete books_authors failed: (" . $stmtDeleteRelation->errno . ") " . $stmtDeleteRelation->error;
			return false;
		}
		
		return true;
	}
?>