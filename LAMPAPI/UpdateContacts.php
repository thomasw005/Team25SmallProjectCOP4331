<?php
    $inData = getRequestInfo();

    $id = $inData["id"];
    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $phoneNumber = $inData["phoneNumber"];
    $emailAddress = $inData["emailAddress"];

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

    if ($conn->connect_error)
    {
        returnWithError($conn->connect_error);
    }
    else
    {
        $stmt = $conn->prepare("UPDATE Contacts SET FirstName=?, LastName=?, Phone=?, Email=? WHERE ID=?");
        if (!$stmt) {
            returnWithError("Prepare failed: " . $conn->error);
            exit();
        }
        $stmt->bind_param("ssssi", $firstName, $lastName, $phoneNumber, $emailAddress, $id);
        $stmt->execute();

        $stmt->close();
        $conn->close();
        returnWithError("");
    }

    function getRequestInfo() { return json_decode(file_get_contents('php://input'), true); }
    function sendResultInfoAsJson($obj) { header('Content-type: application/json'); echo $obj; }
    function returnWithError($err) { $retValue = '{"error":"' . $err . '"}'; sendResultInfoAsJson($retValue); }
?>
