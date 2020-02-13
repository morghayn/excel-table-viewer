<?php

	class model {
		
		private $database;
		
		public function __construct($pdo)
		{
			$this->database = $pdo;
		}
		
		public function count_records($table) 
		{
			$stmt = $this->database->prepare("SELECT COUNT(*) FROM $table");
			$stmt->execute();
            return $stmt->fetchColumn();
		}
		
		public function get_excluded_columns()
		{
			$stmt = $this->database->prepare("SELECT `Name` FROM `Excluded_Columns`");
			$stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
		}
		
		public function get_columns() 
		{
			$stmt = $this->database->prepare("SELECT name FROM PRAGMA_TABLE_INFO('EAMS')");
			$stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
		}
	}
