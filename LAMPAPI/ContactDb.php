<?php
	// Connect to MySQL database
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

	// Find out what HTTP method was used
    $method = $_SERVER('REQUEST_METHOD');

	// Read JSON request body, and decode into a PHP array
	$input = json_decode(file_get_contents('php://input'), true);
	if (!is_array($input)) { $input = []; } // ensure $input is an array

	// Return error if connection fails
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		// Decide what to do based on HTTP method
		switch($method){
            case 'GET':

				$searchResults = "";
   			 	$searchCount   = 0;
				
				// Search term from input or empty if unprovided
				$term = "%" . ($input['search'] ?? '') . "%";
				// UserID is needed to scope queries
				$userid = $input['UserID'] ?? ($_GET['UserID'] ?? 0);

				// Query contacts belonging to this user, matching the search term
				$stmt = $conn->prepare("SELECT * FROM Contacts WHERE UserID = ? AND (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ?)");
				$stmt->bind_param("issss", $userid, $term, $term, $term, $term);
				$stmt->execute();

				// Loop through results and build JSON
				$result = $stmt->get_result();
				while($row = $result->fetch_assoc())
				{
					if($searchCount > 0) 
					{
						$searchResults .= ",";
					}
					$searchCount++;
					$searchResults .= json_encode([
         				"ID"        => $row["ID"],
            			"FirstName" => $row["FirstName"],
            			"LastName"  => $row["LastName"],
            			"Phone"     => $row["Phone"],
            			"Email"     => $row["Email"]
        			]);
				}
				$stmt->close();
				$conn->close();

				// Send either "no records" or the results as JSON
				if($searchCount == 0){
					returnWithError("No Records Found");
				} else{
					returnWithInfo($searchResults);
				}
                break;

            case 'POST':
				// Values come from JSON body
    			$firstname = $input['FirstName'];
    			$lastname = $input['LastName'];
    			$phone = $input['Phone'];
    			$email = $input['Email'];
    			$userid = $input['UserID'];

				// Insert new contact for this user
				$stmt = $conn->prepare("INSERT INTO Contacts(FirstName, LastName, Phone, Email, UserID) VALUES (?, ?, ?, ?, ?)");
				$stmt->bind_param("ssssi", $firstname, $lastname, $phone, $email, $userid);
				$stmt->execute();
				$stmt->close();
				$conn->close();

				// Return empty error string (success)
				returnWithError("");
                break;

            case 'PUT':
				// ID can come from query string (?ID=123) or body
				$id = $_GET['ID'] ?? $input['ID'];
				$firstname = $input['FirstName'];
    			$lastname = $input['LastName'];
    			$phone = $input['Phone'];
    			$email = $input['Email'];
    			$userid = $input['UserID'];

				// Update contact fields if ID and UserID match
				$stmt = $conn->prepare("UPDATE Contacts SET FirstName = ?, LastName = ?, Phone = ?, Email = ? WHERE ID = ? AND UserID = ?");
				$stmt->bind_param("ssssii", $firstname, $lastname, $phone, $email, $id, $userid);
				$stmt->execute();
				$stmt->close();
				$conn->close();

				returnWithError("");
                break;

            case 'DELETE':
				// Delete a specific contact by ID and UserID
				$id = $_GET['ID'] ?? $input['ID'];
    			$userid = $input['UserID'];

				$stmt = $conn->prepare("DELETE FROM Contacts WHERE ID = ? AND UserID = ?");
				$stmt->bind_param("ii", $id, $userid);
				$stmt->execute();
				$stmt->close();
				$conn->close();

				returnWithError("");
                break;
            
            default:
				// If method is somehow not GET/POST/PUT/DELETE
				returnWithError("Invalid request");
                break;
        }
	}
	// Sends raw JSON string as the response
	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

	// Wraps an error message into JSON
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	// Wraps successful search results into JSON
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>