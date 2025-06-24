<?php
  $conn=new mysqli('localhost','root','') or die('unable to connect');
  $conn->select_db('paysblog');
  $sqldataurlcreatedb=file_get_contents('pages/db.sql');
  $conn->multi_query($sqldataurlcreatedb);