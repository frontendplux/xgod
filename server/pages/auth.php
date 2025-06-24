<?php
// login, signup:email, signup:user, signup:pass, signup:passcode
  if($data->type === 'login'){
        $user=mysqli_real_escape_string($conn,$data->user);
        $pass=mysqli_real_escape_string($conn,$data->pass);
        $smt=$conn->prepare("select * from member where (user=? or email=?) and pass=? and status='active'  limit 1");
        $smt->bind_param('sss',$user,$user,$pass);
        $smt->execute();
        $result=$smt->get_result();
        if($result->num_rows){
            $row=$result->fetch_assoc();
            $_id=md5(md5($user.time().md5($user)));
            $smt=$conn->prepare("update member set _id=? where (user=? or email=?) and pass=? and status='active' limit 1");
            $smt->bind_param('ssss',$_id,$user,$user,$pass);
            $smt->execute();
            echo json_encode([
                'status' => true,
                'message' => 'successful login',
                'email' => $row['email'],
                '_id' => $_id
            ]);
        }
        else{
            echo json_encode([
                'status' => false,
                'message' => 'Invalid Credencials'
            ]);
        }
  }
// --------------------------------------
if($data->type === 'signup'){
    $email=mysqli_real_escape_string($conn,$data->email);
    $smt=$conn->prepare("select * from member where email=? imit 1");
    $smt->bind_param('s',$email);
    $smt->execute();
    $result=$smt->get_result();
    if($result->num_rows){
        $row=$result->fetch_assoc();
        $_id=md5(md5($user.time().md5($user)));
        $smt=$conn->prepare("update member set _id=? where (user=? or email=?) and pass=? and status='active' limit 1");
        $smt->bind_param('ssss',$_id,$user,$user,$pass);
        $smt->execute();
        echo json_encode([
            'status' => true,
            'message' => 'successful login',
            'email' => $row['email'],
            '_id' => $_id
        ]);
    }
    else{
        echo json_encode([
            'status' => true,
            'message' => 'Email'
        ]);
    }
}
