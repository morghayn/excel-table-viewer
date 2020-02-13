<?php

	require_once ("model.php");
	
	class toggle_columns extends model {
		
		private $database;		

		public function __construct($pdo)
		{
			parent::__construct($pdo);
			$this->database = $pdo;
		}

		public function on_click_update_status()
		{
			$clicked_column = $_POST['column'];
			$is_active = (in_array($_POST['column'], parent::get_excluded_columns()) ? false : true);
			
			$query = ($is_active === true ? "INSERT INTO Excluded_Columns (Name) VALUES ('$clicked_column')" : "DELETE FROM Excluded_Columns WHERE Name = ('$clicked_column')");
			$stmt = $this->database->prepare($query);
			$stmt->execute();
			
			// removed :: 02/02/2020 // added back 09/02/2020 // remove when you getify this post
			header("location:admin.php");
		}

		public function print_toggle_function() 
		{		
			$excluded_columns = parent::get_excluded_columns();
			
			echo '<form class="align-center" method="post"><div class="toggle_column_container">';
			echo '<p>Toggle Column Visibility</p>';

			foreach (parent::get_columns() as $column) 
			{
				echo '<input '.(in_array($column, $excluded_columns) ? 'class="inactive"' : 'class="active"').' type="submit" value="'.$column.'" name="column">';
			}

			echo '</div></form>';
		}
	}
