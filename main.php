<?php
  class auth{
    public function __construct($conn){
        $this->conn=$conn;
    }

    public function login($email){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return [false,"message" => "Provide a valid Email Address"];
        }
       $smt=$this->conn->prepare("select * from users where email=? limit 1");
       $smt->bind_param('s',$email);
       $smt->execute();
       $result=$smt->get_result();
       $uids =rand(1000,9999);
       if($result->num_rows){
           $row=$result->fetch_assoc();
           $smt=$this->conn->prepare("update users set uids=? where id=? limit 1");
           $smt->bind_param('ii',$row['id']);
          $smt->execute();
          sendmail($email,'welcome to xgod a world of anonymous transactions',$uids);
          return[true, 'message' => "welcome back users!", 'status' => 1];
       }
       $smt=$this->conn->prepare("insert into users (uids,email) values(?,?)");
       $smt->bind_param('is',$uids,$email);
       $smt->execute();
       sendmail($email,'welcome to xgod a world of anonymous transactions',$uids);
        return[true, 'message' => "welcome back users!", 'status' => 1];
    }
  }