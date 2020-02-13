<?php

	session_start();

	class Login_Query {
		
		private $database;
		
		public function __construct() 
		{
			$this->database = new PDO('sqlite:db/EAMs.db');
			$this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->execute_login();
		}
		
		public function execute_login() 
		{
			$this->redirect($this->is_valid_login($_POST['username'], $this->encrypt_password_post()));
		}

		public function encrypt_password_post()
        {
            return hash('sha256', $_POST['password'] . "encrypted");
        }

        public function is_valid_login($username, $password)
        {
            $stmt = $this->database->prepare("SELECT COUNT(*) as count FROM `Login` WHERE `username` = :username AND `password` = :password");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row['count'];
        }
		
		public function redirect($is_valid_login)
		{
			$_SESSION['username'] = ($is_valid_login > 0 ? "admin" : "error");
			header("location:".($_SESSION['username'] == "admin" ? "admin" : "login"));			
		}

	}