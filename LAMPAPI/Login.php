<?php

        $inData = getRequestInfo();
        
        $id = 0;
        $firstName = "";
        $lastName = "";

        // Read users from JSON file
        $usersFile = __DIR__ . '/data/users.json';
        if (!file_exists($usersFile)) {
                returnWithError("Users data file not found");
                exit;
        }
        
        $usersData = json_decode(file_get_contents($usersFile), true);
        if ($usersData === null) {
                returnWithError("Invalid users data");
                exit;
        }
        
        // Find matching user
        $userFound = false;
        foreach ($usersData as $user) {
                if ($user['Login'] === $inData["login"] && $user['Password'] === $inData["password"]) {
                        returnWithInfo($user['firstName'], $user['lastName'], $user['ID']);
                        $userFound = true;
                        break;
                }
        }
        
        if (!$userFound) {
                returnWithError("No Records Found");
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
                $retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
                sendResultInfoAsJson( $retValue );
        }
        
        function returnWithInfo( $firstName, $lastName, $id )
        {
                $retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
                sendResultInfoAsJson( $retValue );
        }
        
?>