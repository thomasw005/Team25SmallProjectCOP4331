<?php
        // Read JSON request body, and decode into a PHP array
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { $input = []; } // ensure $input is an array

        // Find out what HTTP method was used
        $method = $_SERVER['REQUEST_METHOD'];

        // File paths for data storage
        $contactsFile = __DIR__ . '/data/contacts.json';
        
        // Ensure contacts file exists
        if (!file_exists($contactsFile)) {
                file_put_contents($contactsFile, json_encode([]));
        }

        // Read contacts from JSON file
        $contactsData = json_decode(file_get_contents($contactsFile), true);
        if ($contactsData === null) {
                $contactsData = [];
        }

        // Decide what to do based on HTTP method
        switch($method){
            case 'GET':
                $searchResults = "";
                $searchCount = 0;
                
                // Search term from input or empty if unprovided
                $term = $input['search'] ?? '';
                // UserID is needed to scope queries
                $userid = $input['UserID'] ?? ($_GET['UserID'] ?? 0);

                // Search contacts belonging to this user, matching the search term
                foreach ($contactsData as $contact) {
                        if ($contact['UserID'] == $userid && 
                            (empty($term) || 
                             stripos($contact['FirstName'], $term) !== false ||
                             stripos($contact['LastName'], $term) !== false ||
                             stripos($contact['Phone'], $term) !== false ||
                             stripos($contact['Email'], $term) !== false)) {
                                
                                if($searchCount > 0) {
                                        $searchResults .= ",";
                                }
                                $searchCount++;
                                $searchResults .= json_encode([
                                        "ID" => $contact["ID"],
                                        "FirstName" => $contact["FirstName"],
                                        "LastName" => $contact["LastName"],
                                        "Phone" => $contact["Phone"],
                                        "Email" => $contact["Email"]
                                ]);
                        }
                }

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

                // Generate new ID
                $newId = count($contactsData) > 0 ? max(array_column($contactsData, 'ID')) + 1 : 1;

                // Create new contact
                $newContact = [
                        "ID" => $newId,
                        "FirstName" => $firstname,
                        "LastName" => $lastname,
                        "Phone" => $phone,
                        "Email" => $email,
                        "UserID" => $userid
                ];

                // Add to contacts array
                $contactsData[] = $newContact;

                // Save back to file
                file_put_contents($contactsFile, json_encode($contactsData, JSON_PRETTY_PRINT));

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

                // Find and update contact
                foreach ($contactsData as &$contact) {
                        if ($contact['ID'] == $id && $contact['UserID'] == $userid) {
                                $contact['FirstName'] = $firstname;
                                $contact['LastName'] = $lastname;
                                $contact['Phone'] = $phone;
                                $contact['Email'] = $email;
                                break;
                        }
                }

                // Save back to file
                file_put_contents($contactsFile, json_encode($contactsData, JSON_PRETTY_PRINT));

                returnWithError("");
                break;

            case 'DELETE':
                // Delete a specific contact by ID and UserID
                $id = $_GET['ID'] ?? $input['ID'];
                $userid = $input['UserID'];

                // Filter out the contact to delete
                $contactsData = array_filter($contactsData, function($contact) use ($id, $userid) {
                        return !($contact['ID'] == $id && $contact['UserID'] == $userid);
                });

                // Re-index array and save
                $contactsData = array_values($contactsData);
                file_put_contents($contactsFile, json_encode($contactsData, JSON_PRETTY_PRINT));

                returnWithError("");
                break;
            
            default:
                // If method is somehow not GET/POST/PUT/DELETE
                returnWithError("Invalid request");
                break;
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