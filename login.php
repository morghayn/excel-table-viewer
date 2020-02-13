<!DOCTYPE html>
<html>
	<head>
		<title>EAMs</title>
		<link rel="stylesheet" href="css/login.css" type="text/css" media="screen">
	</head>
	
	<body>
		<div class="logo-area">
			<img src="logo.jpg" alt="Pfizer" width="300px" height="195px"> 		 
			<div class="link-box">
				<a href="index.php">Home</a>			
				<a href="table.php">View Table</a>	
			</div>
		</div>

		<form class="align-center" method="post"> <!--action="login_query.php" method="post">-->
			<div class="login_container">
				<p>Admin Login</p>
				<label>Username</label>
				<input type="text" name="username" placeholder="Input Username">	
				<label>Password</label>			
				<input type="password" name="password" placeholder="Input Password">
				<input type="submit" value="Continue" name="login">
			</div>
		</form>
		
	<?php
	
		if(ISSET($_POST['login']))
		{
			require_once('functionality/login_query.php');
			$login = new Login_Query();
		}
		
	?>
	
	</body>
</html>