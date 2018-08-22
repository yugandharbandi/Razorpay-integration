<?php
    // $email=$_POST['email'];
    $email="2015csb1009@iitrpr.ac.in";
    // $client=$_POST['client'];
    $client="app";
    require 'database_connect.php';
    $session_id = substr(md5(time()-mt_rand()), 0, 10);
    date_default_timezone_set('Asia/Kolkata');
    $expire_time = date("Y-m-d H:i:s", time() + 86400);
    $query= "INSERT INTO `sessions` (`session_id`,  `expire_time`, `client`, `email`) VALUES ('$session_id',  '$expire_time', '$client', '$email')";
	mysql_query($query);
	if(!mysql_error()){
        $data = array('Status' => "success",'id' => $session_id);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);   
    }
    else{
        $data = array('Status' => "Failed",'Reason' => "Internal Error");
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);   
    }

?>