<?php
    function check_credits($from){
        $email=$_POST['email'];
        if($from=="reduce_credits"){
            $query="SELECT * FROM `credits_table` WHERE `email`='$email' ORDER by UNIX_TIMESTAMP(subscr_end) ASC FOR UPDATE";
        }
        elseif($from=="main"){
            $query="SELECT * FROM `credits_table` WHERE `email`='$email' LOCK in SHARE MODE";
        }
        $query_handle2=mysql_query($query);
        if(!mysql_error()){			
            if(mysql_num_rows($query_handle2)>=1){
                if($from=="reduce_credits"){
                    return $query_handle2;
                }
                elseif($from=="main"){
                    $reply=mysql_fetch_assoc($query_handle2);
                    $today = date("Y-m-d");
                    if($reply['subscr_end']>$today&&$reply['status']==1){
                        $data = array('Status' => "Success",'credits_left' => $reply['credits_left']);
                        header('Content-Type: application/json');
                        echo json_encode($data, JSON_PRETTY_PRINT);
                    }
                    elseif($reply['status']==0){
                        $data = array('Status' => "Failed",'Reason' => "inactive");
                        header('Content-Type: application/json');
                        echo json_encode($data, JSON_PRETTY_PRINT);
                        mysql_query("ROLLBACK");	
                        return;
                    }
                    elseif(!($reply['subscr_end']>$today)){
                        $data = array('Status' => "Failed",'Reason' => "subscription expired");
                        header('Content-Type: application/json');                        
                        echo json_encode($data, JSON_PRETTY_PRINT);
                        mysql_query("ROLLBACK");	
                        return;
                    }
                }
            }
            else{
                if(mysql_num_rows($query_handle2)>1){
                    $data = array('Status' => "Failed",'Reason' => "Duplicate email");
                    header('Content-Type: application/json');
                    echo json_encode($data, JSON_PRETTY_PRINT);         			
                    error_log("duplicate found for $email", 0);
                }
                else{
                    $data = array('Status' => "Failed",'Reason' => "Invalid mailID");
                    header('Content-Type: application/json');
                    echo json_encode($data, JSON_PRETTY_PRINT);    
                    // echo "Either subscription is expired or there is no user with this email";
                }
                mysql_query("ROLLBACK");
                return;
            }
        }
        else{
            $data = array('Status' => "Failed",'Reason' => "Internal Error");
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);            
            error_log(mysql_error(), 0);
            mysql_query("ROLLBACK");    
            return;        
        }
    }   
?>