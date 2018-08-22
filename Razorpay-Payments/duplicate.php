<?php
    function update_credits($email, $subscr_strt, $subscr_end, $final_credits){
        $query="UPDATE `credits_table` SET `credits_left`=$final_credits WHERE `email`='$email' AND subscr_strt='$subscr_strt' AND subscr_end='$subscr_end' ";
        $query_handle=mysql_query($query);
        if(mysql_error()){	
            return 0;			
        }
        else{
            return 1;
        }
    }
    function delete_row($email, $subscr_strt, $subscr_end){
        $query="DELETE FROM `credits_table` WHERE email='$email' AND subscr_strt='$subscr_strt' AND subscr_end='$subscr_end' ";
        mysql_query($query);
        
    }

    function log_transaction($requested_credits, $credits_deducted){
        date_default_timezone_set('Asia/Kolkata');
        $time = date("Y-m-d", time());
        $filename="transaction_".$time.".log";
        $myfile = fopen($filename, "a") or die("Unable to open file!");
        $txt = $_POST['session_id'].' '.date("H:i:s").' '.$requested_credits.' '.$credits_deducted;
        fwrite($myfile, $txt.PHP_EOL);
        fclose($myfile);
    }

    function search_for_new($email){
        $query="UPDATE credits_table SET status=1 WHERE email='$email' ORDER BY UNIX_TIMESTAMP(subscr_end) ASC LIMIT 1";
        mysql_query($query);
    }

    function reduce_credits(){
        $requested_units=$_POST['requested_units'];
        $factor=$_POST['factor'];
        $email=$_POST['email'];       

        $requested_credits=$requested_units*$factor;
        $handler=check_credits("reduce_credits");
        $today = date("Y-m-d");

        $credits_left=0;
        $required_credits=$requested_credits;
        $counter=0;
        while ($reply = mysql_fetch_array($handler, MYSQL_NUM)) {
            $counter++;
            if($reply[3]>$today){
                if(((int)$reply[1])>=$required_credits){
                    $updated_credits=((int)$reply[1])-$required_credits;
                    if($updated_credits==0){
                        delete_row($email, $reply[2], $reply[3]);
                        search_for_new($email);
                    }
                    else{
                        update_credits($email, $reply[2],$reply[3], $updated_credits);
                    }
                    $required_credits=0;
                    break;
                }
                elseif($counter==MYSQL_NUM){
                    $updated_credits=$reply[1]-((int)($reply[1]/$factor)*$factor);
                    if($updated_credits==0){
                        delete_row($email, $reply[2], $reply[3]);
                    }
                    else{
                        update_credits($email, $reply[2],$reply[3], $updated_credits);
                    }
                    $required_credits-=((int)($reply[1]/$factor)*$factor);
                }
                else{
                    $required_credits-=((int)$reply[1]);
                    delete_row($email, $reply[2], $reply[3]);
                }
            }
        }

        if($required_credits>0){
            $units_satisfied=(int)(($requested_credits-$required_credits)/$factor);
        }
        else{
            $units_satisfied=$requested_units;
        }
        if($units_satisfied>0){           
            $data = array('Status' => "Success",'units_satisfied' => $units_satisfied, 'credits_deducted' => $requested_credits-$required_credits);
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);	
            log_transaction($requested_credits, $requested_credits-$required_credits);	
            }
        else{
            $data = array('Status' => "Failed",'Reason' => "Insufficient credits");
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
        }

    }

    $_POST['factor']=3;
    $_POST['email']="2015csb1009@iitrpr.ac.in";
    $_POST['requested_units']=100;
    // $function_num=$_POST['function_num'];
    $_POST['session_id']="2e560d7878";

    require 'database_connect.php';
    require 'check_credits.php';  
    require 'check_session.php';  
    $valid=check_session("2e560d7878");
    if($valid){
        mysql_query("START TRANSACTION");
        reduce_credits();
        mysql_query("COMMIT");
    }
?>