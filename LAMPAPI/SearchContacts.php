<?php
    $inData = getRequestInfo();

    $searchResults = "";
    $searchCounter = 0;

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

    if($conn->connect_error)
    {
        returnWithError($conn->connect_error);
    } else
    {
        $stmt = $conn->prepare("SELECT * FROM Contacts WHERE (FirstName LIKE ? OR LastName LIKE ?) AND UserID = ?");
        $searchTerm =  "%" . $inData["search"] . "%";
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $inData["UserID"]);
        $stmt->execute();

        $result = $stmt->get_result();

        while($row = $result->fetch_assoc())
        {
            if($searchCounter > 0 )
            {
                $searchResults .= ",";
            }
            $searchCounter++;

            $searchResults .= '{"FirstName" : "' . $row["FirstName"]. '", "LastName" : "' . $row["LastName"]. '", "PhoneNumber" : "' . $row["PhoneNumber"]. '", "EmailAddress" : "' . $row["EmailAddress"]. '", "UserID" : "' . $row["UserID"].'", "ID" : "' . $row["ID"]. '"}'
        }

        if( $searchCounter == 0 )
		{
			returnWithError( "No Records Found" );
		}
		else
		{
			returnWithInfo( $searchResults );
		}

        $stmt->close();
		$conn->close();
    }

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>