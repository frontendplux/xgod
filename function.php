<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$path=strtolower($path);
$path=explode("/", $path);
define('db_host','localhost');
define('db_user','root');
define('db_pass','');
define('db_name','air9');
$conn=new mysqli(db_host, db_user, db_pass) or die("unable to connect");
$conn->query("create database if not exists ". db_name);
$conn->select_db(db_name);
include 'main.function.php';
$data=json_decode(file_get_contents('php://input'), true);
// print_r($path);
switch ($path[1] ?? '') {
    case '':
    case 'home':
        include 'main/index.php';
        # code...
        break;
    
    case 'create-password':
        include 'main/create-password.php';
        break;

    case 'api':
        $auth=new authentication($conn);
        switch ($path[2] ?? '') {
            case 'login':
                echo json_encode($auth->login($data));
                break;
            
            default:
                # code...
                break;
        }
        # code...
        break;
    
    default:
        # code...
        break;
}
?>