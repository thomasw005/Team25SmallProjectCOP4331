<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$inData = getRequestInfo();

$search = isset($inData["search"]) ? trim($inData["search"]) : "";
$userId = isset($inData["userId"]) ? intval($inData["userId"]) : 0;

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $searchResults = "";

    if ($search === "") {
        $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone, Email 
                                FROM Contacts 
                                WHERE UserID=?");
        $stmt->bind_param("i", $userId);
    } else {
        $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone, Email 
                                FROM Contacts 
                                WHERE (FirstName LIKE ? 
                                   OR LastName LIKE ? 
                                   OR Phone LIKE ? 
                                   OR Email LIKE ?)
                                  AND UserID=?");
        $like = "%" . $search . "%";
        $stmt->bind_param("ssssi", $like, $like, $like, $like, $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $searchResultsArr = [];
    while ($row = $result->fetch_assoc()) {
        $searchResultsArr[] = $row;
    }

    if (count($searchResultsArr) == 0) {
        returnWithError("No Records Found");
    } else {
        returnWithInfo($searchResultsArr);
    }

    $stmt->close();
    $conn->close();
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = '{"results":[],"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($searchResults)
{
    $retValue = '{"results":' . json_encode($searchResults) . ',"error":""}';
    sendResultInfoAsJson($retValue);
}
?>
