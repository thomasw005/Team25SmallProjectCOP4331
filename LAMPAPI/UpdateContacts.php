<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$raw = file_get_contents("php://input");
file_put_contents("/tmp/debug_update_input.log", $raw);

$inData = json_decode($raw, true);
file_put_contents("/tmp/debug_update_decoded.log", print_r($inData, true));

if (!$inData) {
    die("DEBUG: JSON decode failed. Raw input: " . $raw);
}

$id           = $inData["id"] ?? null;
$userId       = $inData["userId"] ?? null;
$firstName    = $inData["firstName"] ?? null;
$lastName     = $inData["lastName"] ?? null;
$phoneNumber  = $inData["phoneNumber"] ?? null;
$emailAddress = $inData["emailAddress"] ?? null;

if (!$id || !$userId) {
    die("DEBUG: Missing id or userId. Decoded input: " . print_r($inData, true));
}

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare("UPDATE Contacts 
        SET FirstName=?, LastName=?, Phone=?, Email=? 
        WHERE ID=? AND UserID=?");

    if (!$stmt) {
        returnWithError("Prepare failed: " . $conn->error);
        exit();
    }

    $stmt->bind_param("ssssii", $firstName, $lastName, $phoneNumber, $emailAddress, $id, $userId);
    $success = $stmt->execute();

    $affected = $stmt->affected_rows;

    $stmt->close();
    $conn->close();

    if ($success && $affected > 0) {
        returnWithSuccess("Contact updated successfully");
    } else {
        returnWithError("Update failed or contact not found");
    }
}

function sendResultInfoAsJson($obj) {
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err) {
    $retValue = '{"success":false,"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithSuccess($msg) {
    $retValue = '{"success":true,"message":"' . $msg . '"}';
    sendResultInfoAsJson($retValue);
}
?>
