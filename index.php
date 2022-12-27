<?php 
    header('Access-Control-Allow-Origin: *');    
    header('Access-Control-Allow-Headers:  *');
    header('Access-Control-Allow-Methods:  *'); 
    include_once("login.php");
    $hData = $_POST;
    if(empty($_POST)){
        $hData = json_decode(file_get_contents("php://input"),true);
    }

    //parameters
    //api-key requires
    $apiKey = "null";
    $option = empty($hData["option"]) ? 0:$hData["option"];

    if($option == 0){
        genNoParamInfo();
        die();
    }

    //dev parameters
    $hrText = (empty($hData["human_readable"]) ? false : $hData["human_readable"]);

    $returnResponse = generateError("Invalid Option");
    if(validAPIkey($apiKey)){
        if($option == 't'){
            $returnResponse = "Test call success";
        }
        else if($option == 'l'){
            if(!empty($hData["username"]) && !empty($hData["password"])){
                $returnResponse = login($hData["username"],$hData["password"]); 
            } else {
                $returnResponse = generateError("Missing Parameters");
            }
        }
        else if($option == 'u' || $option == 'h' || $option == 'r' || $option =='f'){
            if(empty($hData["userId"]) || empty($hData["sessionId"])){
                $returnResponse = generateError("Missing Parameters");
            } 
            if($option == 'u'){
                $returnResponse = userInfo($hData["userId"],$hData["sessionId"]); 
            }
            else if($option == 'h'){
                $returnResponse = getHistory($hData["userId"],$hData["sessionId"]); 
            }
            else if($option == 'r'){
                $returnResponse = userRequest($hData["userId"],$hData["sessionId"]); 
            }
            else if($option == 'f'){
                $returnResponse = finishRequest($hData["userId"],$hData["sessionId"]); 
            }
        }
        else if($option == 'a'){
            if(!empty($hData["name"]) && !empty($hData["phoneNum"]) && 
                !empty($hData["features"]) && !empty($hData["location"]) && 
                !empty($hData["isEmergency"])){
                    if(!empty($hData["userId"])){
                        submitNewRequest(
                            $hData["name"],$hData["phoneNum"],
                            $hData["features"],$hData["location"],
                            $hData["isEmergency"],$hData["userId"],
                            $hData["aInfo"], $hData["meet_up"]
                        );
                    } else {
                        generateError("No Login not implemented yet"); 
                    }
            }
        }
    }
    echo $returnResponse;
    function validAPIkey($apiKey){ return 1; } 
    function submitNewRequest($name,$phoneNum,$features,$userId,$sessionId,
                             $location,$isEmergency,$aInfo="",$meet_up=""){
        $que = "SELECT status,currentRequestId FROM userList
                WHERE userId='$userId' and sessionId='$sessionId'";
        $res = $conn->query($que);
        if(!empty($res) || $res->num_rows) return generateError("User not found");
        $requestId = null;
        $status = null;
        while($row = $res->fetch_assoc()){
            $status= $row["status"];
        }
        if($status != 0) return generateError("Request Already in Process");

        $que = "INSERT INTO activeRequest(name,phoneNum,features,requesterId,location,
                            isEmergency,additional_info,meet_up)
                VALUES(
                    '$name','$phoneNum','$features',$userId,
                    '$location',$isEmergency,'$aInfo','$meet_up'
                )";
        $que = "UPDATE userList
                SET status = 0
                    currentRequestId=
                WHERE userId='$userId' and sessionId='$sessionId'";
        $conn->query($que);
    }
    function finishRequest($userId,$sessionId){
        $requestId = userRequest($userId,$sessionId,1);
        if(is_string($requestId)){
            return $requestId;
        }
        $que = "UPDATE userList
                SET status = 0
                WHERE userId='$userId' and sessionId='$sessionId'";
        $conn->query($que);
        //move a to b
        $que = "SELECT * FROM currentRequest
                WHERE requestId='$reqeustId'";
        $res = $conn->query($que);
        $requestInfo = $res->fetch_assoc();
         
        $que = "INSERT INTO history_".$userId."(
                    requestId,time,location,walkerId,
                    features,meet_up,emergency,additional_info
                )
                VALUES(
                    ".$requestInfo["requestId"].",
                    ".$requestInfo["time"].",
                    ".$requestInfo["location"].",
                    ".$requestInfo["walkerId"].",
                    ".$requestInfo["features"].",
                    ".$requestInfo["meet_up"].",
                    ".$requestInfo["emergency"].",
                    ".$requestInfo["additional_info"]."
                )";
                
    }
    function userRequest($userId,$sessionId,$rawVal=0){
        global $conn;
        $que = "SELECT status,currentRequestId FROM userList
                WHERE userId='$userId' and sessionId='$sessionId'";
        $res = $conn->query($que);
        if(!empty($res) || $res->num_rows) return generateError("User not found");
        $requestId = null;
        $status = null;
        while($row = $res->fetch_assoc()){
            $status= $row["status"];
            $requestId = $row["requestId"];
        }
        if($status == 0) return generateError("User has no requests");
        if($rawVal == 1) return $requestId;
        return '{
            "code":1,
            "requestId":'.$requestId.'",
            "status":'.$status.'"
            }';
    }
    function getHistory($userId,$sessionId){
        global $conn;
        $que = "SELECT * FROM history_".$userId;
        if(!empty($res) || $res->num_rows){
            return generateError("User not found");
        }
        while($row = $res->fetch_assoc())
            $userId = $row["userId"];
        return "";
    }
    function userInfo($userId,$sessionId){
        global $conn;
        $que = "SELECT * FROM userList
                WHERE userId='$userId' and sessionId='$sessionId'";
        $res = $conn->query($que);
        $userId = null;
        if(!empty($res) || $res->num_rows){
            return generateError("User not found");
        }
        $userInfo = $res->fetch_assoc();
        if($userInfo["expireTime"] < date()){
            return generateError("Session has expired, please login again");
        }
        return 
        '{
            "code":1,
            "first_name":"'.$userInfo["first_name"].',
            "last_name":"'.$userInfo["last_name"].',
            "phoneNum":"'.$userInfo["phoneNum"].',
            "email":"'.$userInfo["email"].',
            "perm":"'.$userInfo["perm"].',
            "status":"'.$userInfo["status"].'}';
    }
    function login($username,$password){
        global $conn;
        $que = "SELECT userId FROM userList
                WHERE username='$username' and password='$password'";
        $res = $conn->query($que);
        $userId = null;
        if(!empty($res) || $res->num_rows){
            return generateError("User not found");
        }
        while($row = $res->fetch_assoc())
            $userId = $row["userId"];
    
        $newSessionId = rand();
        $que = "UPDATE userList
                SET sessionId=$newSessionId,
                    expireTime=DATE_ADD(NOW(),INTERVAL 1 HOUR)
                WHERE username='$username' and password='$password'";
        $conn->query($que);                
        return '{
                "code":1,
                "userId":"'.$userId.'",
                "sessionId":"'.$newSessionId.'"}';
    }
    function generateError($msg){
        return '{"code":0,"msg":"'.$msg.'"}';
    }

    function genNoParamInfo(){
        echo "<pre>
        Welcome to this API

        Preparamters:
            Please provide an API_key with each call
        Possible Parameters with
        'opt': 
            - t >> login
            -----
            Test Call -> returns a string
            -----
            - l >> login
            -----
            Requires a 'username' and 'password'
            Query will return a JSON with 'usercookie' and 'sessionId'
            if valid username and password
            -----
            - u >> user
            -----
            Requires a 'userId' and 'sessionId' from above
            Query will return a JSON with all user info
            -----
            - h >> history 
            -----
            Requires a 'userId' and 'sessionId' from above
            Query will return a JSON with all user history
            -----
            - r >> check request
            -----
            Requires a 'userId' and 'sessionId' from above
            Or use 'requestId'
            Query will return whether or not this user has a request
            -----
            - f >> finish request
            -----
            Requires a 'userId' and 'sessionId' from above
            Returns a 'requestId'
            Query will return a success code
            -----
            - a >> add request
            -----
            Requires parameters as strings:
            'name','phoneNum','features','geoInfo','isEmergency'
            Will check the following parameters:
            'mArea','aInfo'
            Should also include 'userId' if possible to track history
            Returns a 'requestId'
        </pre>";
    }
?>
