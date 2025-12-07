<?php
 class auth{
    public function __construct($conn){
        $this->conn=$conn;
    }
    public function login($data){
        $user=$data['user'] ?? null;
        $pass=$data['pass'] ?? null;
        if($user === null || $pass === null) return ['status' => false, 'message' => "enter all field"];
    }
 }