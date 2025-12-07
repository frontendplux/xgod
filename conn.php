<?php
define('db_host', 'localhost');
define('db_user', 'root');
define('db_pass', '');
define('db_name', 'xgod');
define('app_name', 'XGod');
$conn=new mysqli(db_host,db_user,db_pass) or die('unable to connect');
$conn->query("CREATE DATABASE IF NOT EXISTS ".db_name);
$conn->select_db(db_name);