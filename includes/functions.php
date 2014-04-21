<?php
	$db = new mysqli("localhost", "root", "", "Karlovo_books");
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
	
	//function isAuthorIdExists($db, $ids) {
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
	 * inputted parameters: $db - connection, $names - array with names of authors
	 * return true if authors exists
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
			                                                      -- c.collection_name,
			                                                       b.notes
			                                                  FROM books_authors as ba
													    INNER JOIN books as b
														        ON ba.book_id = b.book_id
														INNER JOIN books_authors as bba
														 	    ON bba.book_id = ba.book_id
														INNER JOIN authors as a
															    ON bba.author_id = a.author_id 
														/*INNER JOIN collections c
                                                              ON c.collection_id = ba.collection_id*/
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
			                                      $collectionId, $bookNotes);
		
		/* fetch values */
		$booksByAuthors = array();
		if($stmtSelectAllBooksByAuthors->num_rows > 0) {
			while ($row = $stmtSelectAllBooksByAuthors->fetch()) {
				$booksByAuthors[$bookId]['bookName'] = $bookName;
				$booksByAuthors[$bookId]['authors'][$authorId] = $authorName;
				$booksByAuthors[$bookId]['bookNotes'] = $bookNotes;
				$collectionName = returnCollectionNameById($db, $collectionId);
				if ($collectionName !== false) {
					$booksByAuthors[$bookId]['collectionName'] = $collectionName;
				}
				else {
					$booksByAuthors[$bookId]['collectionName'] = '';
				}
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
				$err[] = "Дължината на потребителското име трябва да е между 0 и 1000 символа.";
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
	
	/* check in table users if inputted userName and userPass are exists
	 * if exists return userId
	 * else return false
	 * */
	function checkExisingUser($db, $userName, $userPass) {
		
		if (strlen($userName) <= 0 || strlen($userPass) <= 0) {
			return false;
		}
		
		if ( !($stmtIsUserExists = $db->prepare("SELECT user_id 
			                                       FROM users 
				                                  WHERE user_name='" . $userName . "' 
			                                        AND user_pass='" . $userPass . "'" ) )) {
				//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
				return false;
		}
			
		if (!$stmtIsUserExists->execute()) {
			//echo ' Execute select failed: ('  . $stmtIsNameExists->errno . ' ) '  . $stmtIsNameExists->error;
			return false;
		}
			
		/* store result */
		$stmtIsUserExists->store_result();
			
		/* bind result variables */
		$stmtIsUserExists->bind_result($userId);
			
		if($stmtIsUserExists->num_rows == 1) {
			while ($row = $stmtIsUserExists->fetch()) {
				return $userId;  
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
	 * input parameters $db - connection, $bookId, $newBookName, $newBookAthors[], $newBookCollection, $newBookNotes 
	 * return true if OK
	 * return false if error 
	 * */
	function updateBooks($db, $bookId, $newBookName, $newBookAthorsIds, $newBookCollectionId, $newBookNotes) {

		//$newBookAthorsIds[] = $newBookAthorsId;
		if ($bookId < 0 || strlen($newBookName) <=0) {
			return false;
		}

		//echo '$bookId = ' . $bookId;
		if (strlen($newBookName) > 0 ) {
			if (!($stmtUpdateBookTbl = $db->prepare("UPDATE books 
				                                        SET book_title = '" . $newBookName . "',
				                                            notes = '" . $newBookNotes . "'
				                                      WHERE book_id = " . $bookId ))) {
				echo "Prepare update with book_title and notes failed: (" . $db->errno . ") " . $db->error;
				return false;
			}

			if (!$stmtUpdateBookTbl->execute()) {
				echo "Execute update book failed: (" . $stmtUpdateBookTbl->errno . ") " . $stmtUpdateBookTbl->error;
				return false;
			}
		}
		
 		if (count($newBookAthorsIds) > 0 ) {
		   if (!($stmtDeleteRelatedRows = $db->prepare("DELETE FROM books_authors 
			                                             WHERE book_id = " . $bookId ))) {
				echo "Prepare delete related rows in books_authors failed: (" . $db->errno . ") " . $db->error;
				return false;
			}

			if (!$stmtDeleteRelatedRows->execute()) {
				echo "Execute delete related rows in books_authors failed: (" . $stmtDeleteRelatedRows->errno . ") " . $stmtDeleteRelatedRows->error;
				return false;
			}

			foreach ($newBookAthorsIds as $key => $author) {
				// insert new related rows based by new authors and collections
				if (!($stmtInsertRelatedTbl = $db->prepare("INSERT INTO books_authors 
					                                            (book_id, author_id, collection_id)
				                                          VALUES(" . $bookId . " , " . $author . " , " . $newBookCollectionId . ")" 
				                                          ))) {
					echo "Prepare insert related rows in books_authors failed: (" . $db->errno . ") " . $db->error;
					return false;
				}

				if (!$stmtInsertRelatedTbl->execute()) {
					echo "Execute related rows in books_authors failed: (" . $stmtInsertRelatedTbl->errno . ") " . $stmtInsertRelatedTbl->error;
					return false;
				}
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
				echo "Execute insert failed: (" . $stmtInput->errno . ") " . $stmtInput->error;
				return false;
			}
			
			return true;
		}
		else {
			echo 'Error in isNameExistsInTable';
			return false;
		}
		echo 'General error';
		return false;
		
	}
	
	/* update authorName in table authors by authorId
	 * return true if no errors
	 * */
	function updateAuthorByName($db, $authorName, $authorId){
		
		$authorIds[] = $authorId;
		if (isNameExistsInTable($db, $authorIds, 'authors', 'author_id') === true) {
			if (!($stmtInput = $db->prepare("UPDATE authors SET author_name = '" . $authorName . "' WHERE author_id = " . $authorId))) {
				echo "Prepare insert failed: (" . $db->errno . ") " . $db->error;
				return false;
			}
			
			if (!$stmtInput->execute()) {
				echo "Execute insert failed: (" . $stmtInput->errno . ") " . $stmtInput->error;
				return false;
			}
			
			return true;
		}
		else {
			echo 'Error in isNameExistsInTable';
			return false;
		}
		echo 'General error';
		return false;
		
	}

	/* insert in table books according to parameters
	 * input parameters $db - connection, $bookName, $bookNotes, $bookCollection 
	 * return inserted id if OK
	 * return false if error 
	 * */
	function insertBooks($db, $bookName, $bookNotes) {
		
		if (strlen($bookName) <=0) {
			return false;
		}
		
		if (!($stmtInput = $db->prepare("INSERT INTO books(book_title, notes) VALUES ('" . $bookName . "','" . $bookNotes . "')"))) {
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
	 * $bookCollectionId - array of values with collection_id
	 * return array with inserted ids if OK
	 * return false if errors 
	 * */
	function insertRelationBookAuthor($db, $newBookId, $selectedAuthorIds, $bookCollectionId) {
	
		if ($newBookId < 0 || count($selectedAuthorIds) <= 0 || $bookCollectionId < 0) {
			return false;
		}
		
		$insertedIds = array();

		//echo '<pre>'.print_r($bookCollectionId, true).'</pre>';
		
		for($i=0;$i<count($selectedAuthorIds);$i++) {
			if (!($stmtInputRelation = $db->prepare("INSERT INTO books_authors(author_id, book_id, collection_id) 
															VALUES (?, ?, ?)"))) {
				//echo "Prepare insert books_authors failed: (" . $db->errno . ") " . $db->error;
				return false;
			}
			
			if (!$stmtInputRelation->bind_param("ddd", $selectedAuthorIds[$i], $newBookId, $bookCollectionId)) {
				//echo "Binding insert books_authors parameters failed: (" . $stmtInputRelation->errno . ") " . $stmtInputRelation->error;
				return false;
			}
			if (!$stmtInputRelation->execute()) {
				//echo "Execute insert books_authors failed: (" . $stmtInputRelation->errno . ") " . $stmtInputRelation->error;
				return false;
			}
			
			$insertedIds[] = $stmtInputRelation->insert_id;
		}
		
		return $insertedIds;
	}
	
	function insertNewUser($db, $userName, $userPass) {
		
		if (strlen($userName) <=0 || strlen($userPass) <= 0) {
			return false;
		}
		
		if (!($stmtInput = $db->prepare("INSERT INTO users(user_name, user_pass) 
										      VALUES ('" . $userName . "', '" . $userPass . "')"))) {
			//echo "Prepare insert failed: (" . $db->errno . ") " . $db->error;
			return false;
		}
		
		if (!$stmtInput->execute()) {
			//echo "Execute insert failed: (" . $stmtInput->errno . ") " . $stmtInput->error;
			return false;
		}
		
		return $stmtInput->insert_id;;
	}
	
	function getAllMessages($db, $bookId) {
		if (!is_array($bookId) || count($bookId) <= 0) {
			return false;
		}
		
		if (!($stmtSelectAllMsg = $db->prepare("SELECT m.msg_title, m.msg_text, m.msg_date, u.user_name
		                                          FROM messages m, users u
		                                         WHERE m.msg_user = u.user_id
												   AND m.book_id IN('" . implode(',', $bookId) . "')
		                                         ORDER BY msg_date DESC"))) {
			//echo ' Prepare select failed: ('  . $db->errno . ' ) '  . $db->error;
			return false;
		}
		
		if (!$stmtSelectAllMsg->execute()) {
			//echo ' Execute select failed: ('  . $stmtSelectAllBooksByAuthors->errno . ' ) '  . $stmtSelectAllBooksByAuthors->error;
			return false;
		}
		
		/* store result */
		$stmtSelectAllMsg->store_result();
		
		/* bind result variables */
		$stmtSelectAllMsg->bind_result($msgTitle, $msgText, $msgDate, $msgUserName);
		
		/* fetch values */
		$messages = array();
		if($stmtSelectAllMsg->num_rows > 0) {
			$i=0;
			while ($row = $stmtSelectAllMsg->fetch()) {
				$messages[$i]['msgTitle'] = $msgTitle;
				$messages[$i]['msgText'] = $msgText;
				$messages[$i]['msgDate'] = $msgDate;
				$messages[$i]['msgUserName'] = $msgUserName;
				$i++;
			}
			
		}
		$stmtSelectAllMsg->close();
		
		//echo '<pre>'.print_r($messages, true).'</pre>';
		return $messages;
	}
	
	function insertNewMessage($db, $msgTitle, $msgText, $msgUser, $bookId) {
		if (strlen($msgTitle) <=1 || strlen($msgText) <=1 || $msgUser <0 || $bookId < 0) {
			return false;
		}
		
		if (!($stmtInsertMsg = $db->prepare("INSERT INTO messages(msg_title, msg_text, msg_user, book_id) 
		                                            VALUES(?, ?, ?, ?)"))) {
			echo "Prepare insert books_authors failed: (" . $db->errno . ") " . $db->error;
			return false;
		}
		
		if (!$stmtInsertMsg->bind_param("ssii", $msgTitle, $msgText, $msgUser, $bookId)) {
			echo "Binding insert books_authors parameters failed: (" . $stmtInsertMsg->errno . ") " . $stmtInsertMsg->error;
			return false;
		}
		if (!$stmtInsertMsg->execute()) {
			echo "Execute insert books_authors failed: (" . $stmtInsertMsg->errno . ") " . $stmtInsertMsg->error;
			return false;
		}
		
		return $stmtInsertMsg->insert_id;
	}
?>