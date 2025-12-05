<?php
 class authentication{
    public $conn;
    public function __construct($conn){
    if (!$conn) {
        die("Database connection failed");
    }
    $this->conn = $conn;
}

    public function login($data){
        $email=$data["email"] ?? NULL;
        if ($email === NULL){
            return [false, 0, 'invalid email address'];
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $smt=$this->conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
            $smt->bind_param('s', $email);
            $smt->execute();
            $result=$smt->get_result();
            if ($result->num_rows > 0) {
                return [true, 1, "successfully logged in: user exists"];
            }
            else{
                $smt=$this->conn->prepare("INSERT INTO users (email)VALUES (?)");
                $smt->bind_param('s', $email);
                $smt->execute(); 
                return [true, 2, "Successfully logged in: created successfully"];
            }
        }
        return [false, 0, 'invalid email address'];
    }
 }
?>