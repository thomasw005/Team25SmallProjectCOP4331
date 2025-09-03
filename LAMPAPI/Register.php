<?php

    $inData = getRequestInfo();

    $login = $inData["login"];
    $password = $inData["password"];
    $id = 0;

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331"); 
    if ($conn->connect_error)
    {
        returnWithError($conn->connect_error);
    }
    else
    {
        $stmt = $conn->prepare("SELECT ID FROM Users WHERE Login=?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->fetch_assoc())
        {
            returnWithError("Username already exists");
        }
        else
        {
            $stmt = $conn->prepare("INSERT INTO Users (Login,Password,FirstName,LastName) VALUES(?,?,?,?)");
            $empty = "";
            $stmt->bind_param("ssss", $login, $password, $empty, $empty);
            $stmt->execute();

            $id = $conn->insert_id;
            returnWithInfo($id);
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
        $retValue = '{"id":0,"error":"' . $err . '"}';
        sendResultInfoAsJson( $retValue );
    }
    
    function returnWithInfo( $id )
    {
        $retValue = '{"id":' . $id . ',"error":""}';
        sendResultInfoAsJson( $retValue );
    }

?>
