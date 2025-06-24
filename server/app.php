<?php
   include 'pages/conn.php';
   $data=json_decode(file_get_contents('php://input'));
   $router=explode('/',$_GET['url']);
   switch ($router[0]) {
    case 'auth':
        include 'pages/auth.php';
        break;
    
    default:
        # code...
        break;
   }
   $conn->close();