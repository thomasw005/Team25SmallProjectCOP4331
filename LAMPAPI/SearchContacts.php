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

        while ($row = $result->fetch_assoc())
        {
            if ($searchResults != "") $searchResults .= ",";
            $searchResults .= '{"ID":' . $row["ID"] . 
                            ',"FirstName":"' . $row["FirstName"] . 
                            '","LastName":"' . $row["LastName"] . 
                            '","Phone":"' . $row["Phone"] . 
                            '","Email":"' . $row["Email"] . '"}';
        }

        if ($searchResults == "") returnWithError("No Records Found");
        else returnWithInfo($searchResults);

        $stmt->close();
        $conn->close();
    }

    function getRequestInfo() { return json_decode(file_get_contents('php://input'), true); }
    function sendResultInfoAsJson($obj) { header('Content-type: application/json'); echo $obj; }
    function returnWithError($err) { $retValue = '{"error":"' . $err . '"}'; sendResultInfoAsJson($retValue); }
    function returnWithInfo($searchResults) { $retValue = '{"results":[' . $searchResults . '],"error":""}'; sendResultInfoAsJson($retValue); }
?>
