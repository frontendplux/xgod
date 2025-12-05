<?php
include __DIR__.'/mailserver.php';
 function login($conn,$email){
      if(filter_var($email,FILTER_VALIDATE_EMAIL)){
        $email=mysqli_real_escape_string($conn,trim($email));
        $query=$conn->prepare("select email, status from member where email=?");
        $query->bind_param('s',$email);
        $query->execute();
        $result=$query->get_result();
        $code=rand(10000,99999);
        if($result->num_rows){
            $row=$result->fetch_assoc();
            if($row['status'] === 'blocked'){
                echo json_encode([
                    "status" => false,
                    "message" => "these email address has been blocked and cannot be used anymore",
                  ]);
            }
            elseif($row['status'] === 'pending'){
                $query=$conn->prepare("update member set uids=? where email=? and status='pending'");
                $query->bind_param('is',$code,$email);
                $query->execute();
                mailserver($email,$code);
                echo json_encode([
                    "status" => true,
                    "message" => "welcome back lets complete your registration",
                    "type" => 0
                  ]);
            }
            else{
                echo json_encode([
                    "status" => true,
                    "message" => "welcome back",
                    "type" => 1
                  ]);
            }
        }
        else{
            $query=$conn->prepare("insert into member(uids,email) values(?,?)");
                $query->bind_param('ss',$code,$email);
                $query->execute();
                mailserver($email,$code);
                echo json_encode([
                    "status" => true,
                    "message" => "email registered successfully",
                    "type" => 0
                  ]);
        }
      }
      else{
        echo json_encode([
            "status" => false,
            "message" => "invalid email address try again"
          ]);
      }
   }


function cp($conn,$data){
       $code=mysqli_real_escape_string($conn,trim($data->code)) ?? "";
       $email=mysqli_real_escape_string($conn,trim($data->email)) ?? "";
       if(filter_var($email,FILTER_VALIDATE_EMAIL)){
          $query=$conn->prepare("select id, uids, email, status from member where uids=? and email=? and status ='pending'  limit 1");
          $query->bind_param('is',$code,$email);
          $query->execute();
          $result=$query->get_result();
          $uids=md5($email.rand(100000,999999).time());
          if($result->num_rows){
            $user_id=$result->fetch_assoc()['id'];
            $query=$conn->prepare("update member set uids=? where id=? limit 1");
            $query->bind_param('ss',$uids,$user_id);
            $query->execute();
            $_SESSION['user']=$email;
            $_SESSION['_id']=$uids;
            echo json_encode([
              'status' => true,
              'email' => $email,
              "_id" => $uids,
              "message" => "successfully: lets create password for you",
            ]);
          }
          else{
            echo json_encode([
              'status' => false,
              'type' => 1,
              "message" => "invalid passcode try again",
            ]);
          }
       }
       else {
        echo json_encode([
          'status' => false,
          'type' => 0,
          "message" => "session already expired try again",
        ]);
       }
   }



function createPassword($conn,$data){
  $email=mysqli_real_escape_string($conn,trim($data->email)) ?? "";
  $pass=mysqli_real_escape_string($conn,trim($data->pass));
  $_id=mysqli_real_escape_string($conn,trim($data->_id));
  $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
  if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass)) {
        $pass=password_hash($pass,PASSWORD_DEFAULT);
    } else {
      echo json_encode([
        'status' => false,
        "message" => "
        Has at least 8 characters <br />
        Contains at least one uppercase letter <br />
        Contains at least one lowercase letter <br />
        Contains at least one digit <br />
        Contains at least one special character (e.g. !@#$%^&*())",
      ]);
      exit;
    }

  if(filter_var($email,FILTER_VALIDATE_EMAIL)){
      $query=$conn->prepare("select * from member where uids=? and email=? and status='pending'  limit 1");
      $query->bind_param('ss',$_id,$email);
      $query->execute();
      $result=$query->get_result();
      $uids=md5($email.$_id.time().rand(2,999999));
      if($result->num_rows){
        $row=$result->fetch_assoc();
        $user_id=$row['id'];
        $query=$conn->prepare("update member set uids=?, pass=?, status='active' where id=? limit 1");
        $query->bind_param('ssi',$uids,$pass,$user_id);
        $query->execute();
        $_SESSION['user']=$email;
        $_SESSION['_id']=$uids;
        echo json_encode([
          'status' => true,
          'email' => $email,
          "_id" => $uids,
          "message" => "successfully logged in!",
        ]);
      }
      else{
        echo json_encode([
          'status' => false,
          "message" => "invalid: session already expired try again!",
        ]);
      }
  }
  else{
    echo json_encode([
      'status' => false,
      "message" => "invalid: email address cannot be validated",
    ]);
  }
}

function enterPass($conn,$data){
  $email=mysqli_real_escape_string($conn,trim($data->email)) ?? "";
  $pass=mysqli_real_escape_string($conn,trim($data->pass)) ?? "";
  if(filter_var($email,FILTER_VALIDATE_EMAIL)){
      $query=$conn->prepare("select * from member where email=? and (status !='blocked' or status !='pending')  limit 1");
      $query->bind_param('s',$email);
      $query->execute();
      $result=$query->get_result();
      $uids=md5($email.rand(2,9999999999).time());
      if($result->num_rows){
        $row=$result->fetch_assoc();
        $user_id=$row['id'];
        if(password_verify($pass,$row['pass'])){
          $query=$conn->prepare("update member set uids=? where id=? limit 1");
          $query->bind_param('ss',$uids,$user_id);
          $query->execute();
          $_SESSION['user']=$email;
          $_SESSION['_id']=$uids;
          echo json_encode([
            'status' => true,
            'email' => $email,
            "_id" => $uids,
            "message" => "successfully logged in!",
          ]);
        }
        else{
          echo json_encode([
            'status' => false,
            "message" => "invalid: password cannot be validated!",
          ]);
        }
      }
      else{
        echo json_encode([
          'status' => false,
          "message" => "invalid: invalid Email address or password!",
        ]);
      }
    }
    else{
      echo json_encode([
        'status' => false,
        "message" => "invalid: email cannot be validated!",
      ]);
    }
}


