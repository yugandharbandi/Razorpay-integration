<?php
    function insert_into_table($email, $credits, $subscr_strt, $subscr_end, $status){
        $query2= "INSERT INTO `credits_table` (`email`,  `credits_left`, `subscr_strt`, `subscr_end`, `status`) VALUES ('$email',  '$credits', '$subscr_strt', '$subscr_end', '$status')";  
        mysql_query($query2);
        if(!mysql_error()){
            return 1;  
        }
        else{
            return 0;    
        } 
    }
    function update_table($email, $subscr_strt, $subscr_end){
        $query3= "UPDATE `credits_table` SET `status`=0 WHERE email='$email' AND subscr_strt='$subscr_strt' AND subscr_end='$subscr_end'";  
        mysql_query($query3);
        if(!mysql_error()){
            return 1;   
        }
        else{
            return 0;    
        } 
    }

    function add_credits(){

        // $email=$_POST['email'];
        $email="2015csb1009@iitrpr.ac.in";
        // $credits=$_POST['credits'];
        $credits=100;;
        date_default_timezone_set('Asia/Kolkata');
        $subscr_strt = date("Y-m-d", time());
        // $subscr_end=$_POST['end_date'];
        $date=date_create("2013-03-15");
        $subscr_end= date_format($date,"Y-m-d");        
        $query="SELECT * FROM `credits_table` WHERE email='$email' ORDER by UNIX_TIMESTAMP(subscr_end) ASC FOR UPDATE";
        $query_handler1=mysql_query($query);
        if(!(mysql_error())){
            if(mysql_num_rows($query_handler1)==0){
                $a1=insert_into_table($email, $credits, $subscr_strt, $subscr_end, 1);          
                if($a1){
                    $data = array('Status' => "success");
                    header('Content-Type: application/json');
                    echo json_encode($data, JSON_PRETTY_PRINT);  
                    return;
                }
                else{
                    $data = array('Status' => "Failed",'Reason' => "Internal Error");
                    header('Content-Type: application/json');
                    echo json_encode($data, JSON_PRETTY_PRINT);
                    return;   
                }
            }   
            else{         
                $counter=0;
                while ($row = mysql_fetch_array($query_handler1, MYSQL_NUM)) {
                    if($row[2]==$subscr_strt&&$row[3]==$subscr_end){
                        if($counter==0&&$row[4]==0){
                            insert_into_table($email,$credits, $subscr_strt, $subscr_end, 1);                            
                            return;
                        }
                        else{
                            $data = array('Status' => "Failed",'Reason' => "Same Plan Exists");
                            header('Content-Type: application/json');
                            echo json_encode($data, JSON_PRETTY_PRINT); 
                            $counter=-10;
                            return;
                        }
                    }
                    if($counter==0){
                        if($row[3]>$subscr_end){
                            $a1=insert_into_table($email,$credits, $subscr_strt, $subscr_end, 1);
                            $a2=update_table($email, $row[2],$row[3]);
                            if($a1&&$a2){
                                $data = array('Status' => "success");
                                header('Content-Type: application/json');
                                echo json_encode($data, JSON_PRETTY_PRINT);  
                                return; 
                            }
                            else{
                                $data = array('Status' => "Failed",'Reason' => "Internal Error");
                                header('Content-Type: application/json');
                                echo json_encode($data, JSON_PRETTY_PRINT);
                                return;   
                            }
                            return;
                        }
                    }
                    $counter=1;
                }
                if($counter!=-10){
                    insert_into_table($email, $credits, $subscr_strt, $subscr_end, 0);
                }

            }
        }
        else{
            $data = array('Status' => "Failed", 'Reason' => "Internal Error");
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);	
            error_log(mysql_error(), 0);
            mysql_query("ROLLBACK");	
            return;
        }
    }
    $_POST['email']="2015csb1009@iitrpr.ac.in";
    $_POST['session_id']="2e560d7878";
    require 'database_connect.php';
    require 'check_session.php';    
    // $valid=check_session("2e560d7878");
    $valid=1;
    if($valid){
        mysql_query("START TRANSACTION");
        add_credits();
        mysql_query("COMMIT");
    }
?>