<?php
    $inData = getRequestInfo();

    $search = $inData["search"];
    $userId = $inData["userId"];

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

    if ($conn->connect_error)
    {
        returnWithError($conn->connect_error);
    }
    else
    {
        $searchResults = "";
        $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone, Email 
                                FROM Contacts 
                                WHERE (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ?) 
                                AND UserID=?");

        $like = "%" . $search . "%";
        $stmt->bind_param("ssssi", $like, $like, $like, $like, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $contacts = [];
        while ($row = $result->fetch_assoc())
        {
            $contacts[] = $row;
        }
        if(empty($contacts))
        {
            returnWithError("No Records Found");
        } else
        {
            returnWithInfoAsJson(json_encode($contacts));
        }

        $stmt->close();
        $conn->close();
    }

    function getRequestInfo() { return json_decode(file_get_contents('php://input'), true); }
    function sendResultInfoAsJson($obj) { header('Content-type: application/json'); echo $obj; }
    function returnWithError($err) { $retValue = '{"error":"' . $err . '"}'; sendResultInfoAsJson($retValue); }

?>

