<?php
  include __dir__.'/conn.php';
  include __DIR__.'/main_function.php';
  $auth=new auth($conn);
  if($_SERVER['REQUEST_METHOD'] == 'post'){
     if($_POST['action'] === 'login'){
        json_encode($auth->login(['user' => $_POST['user'], 'pass' => $_POST['pass']]));
     }

  }
  