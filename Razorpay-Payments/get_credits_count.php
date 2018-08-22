<?php
    $_POST['email']="2015csb1009@iitrpr.ac.in";
    require 'database_connect.php';
    require 'check_session.php';
    $valid=check_session("2e560d7878");
    if($valid){
        require 'check_credits.php';
        check_credits("main");
    } 
?>