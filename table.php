<!DOCTYPE html>

<html>
	<head>
		<title>EAMs</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"> 
			<link rel="stylesheet" type="text/css" href="css/table.css">
	</head>
	
	<body>
	
		<div class="logo-area">
			<img src="logo.jpg" alt="Pfizer" width="300px" height="195px"> 
			 
			<div class="link-box">
				<a href="index.php">Home</a>			
				<a href="admin.php">Admin</a>	
			</div>
		</div>
			

	<?php

		require_once('functionality/print_table.php');

		// object instantiation
		$database = new PDO('sqlite:db/EAMs.db');
		$table = new print_table($database);
		
		// attribute gathering
		$select_query = $table->get_selection_query();
		$where_clause = (isset($_GET['action']) ? $table->get_where_clause() : ""); // Controller

		// execution
		$table->attempt_print_table_and_navigator($where_clause, $select_query);

	?>
		
	</body>
</html>