<?php
    function check_session($id){
        $email=$_POST['email'];
        $query="SELECT * FROM `sessions` WHERE `email`='$email' AND `session_id`='$id'";
        $query_handle3=mysql_query($query);
        if(!mysql_error()){	
			if(mysql_num_rows($query_handle3)==1){
                $today = date("Y-m-d H:i:s");
                $reply=mysql_fetch_assoc($query_handle3);
                if($reply['expire_time']>$today){
                    return 1;
                }
                else{
                    $data = array('Status' => "Failed",'Reason' => "session expired");
                    header('Content-Type: application/json');
                    echo json_encode($data, JSON_PRETTY_PRINT);         			
                    return 0;
                }                
            }
            else{
                if(mysql_num_rows($query_handle3)>1){
                    $data = array('Status' => "Failed",'Reason' => "Duplicate email");
                    header('Content-Type: application/json');                    
                    echo json_encode($data, JSON_PRETTY_PRINT);         			
                    error_log("duplicate found for $email", 0);
                }
                else{
                    $data = array('Status' => "Failed",'Reason' => "No session found");
                    header('Content-Type: application/json');                    
                    echo json_encode($data, JSON_PRETTY_PRINT);    
                    // echo "Either subscription is expired or there is no user with this email";
                }
                return 0;
            }
        }
        else{
            $data = array('Status' => "Failed",'Reason' => "Internal Error");
            header('Content-Type: application/json');                    
            echo json_encode($data, JSON_PRETTY_PRINT);    
        }
    }
?>