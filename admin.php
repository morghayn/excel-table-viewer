<!DOCTYPE html>

<?php

	session_start();
	
	if($_SESSION['username'] != "admin") 
	{
		header("location: login.php");
	}
	
?>

<html>
	<head>
		<title>EAMs</title>
		<link rel="stylesheet" href="css/admin.css" type="text/css" media="screen">
	</head>

	<body>
		<div class="logo-area">
			<img src="logo.jpg" alt="Pfizer" width="300px" height="195px"> 
			<div class="link-box">
				<a href="index">Home</a>			
				<a href="table">View Table</a>	
			</div>
		</div>
		
<?php

    // :: password change
	if (isset($_POST['request']) && $_POST['request'] == "Change") 
	{
		$dbh = new PDO('sqlite:db/EAMs.db');
		$stmt = $dbh->prepare("SELECT * FROM Login WHERE username ='admin'");
		$stmt->execute();			
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		$old_password = hash('sha256', $_POST['old_password'] . "encrypted");
		$new_password = hash('sha256', $_POST['new_password'] . "encrypted");

		if ($result['password'] == old_password)
		{
			$stmt = $dbh->prepare("UPDATE Login SET password='$new_password' WHERE username='admin'");
			$stmt->execute();
			unset($_SESSION['username']);
			header("location:login.php");
		}
		
		echo ($result['password'] == old_password ? '' : '<div class="align-center"><h2>wrong password</h2></div>');
	}

	// :: toggle columns
	require_once('functionality/toggle_columns.php');
	$database = new PDO('sqlite:db/EAMs.db');
	$column_toggle = new toggle_columns($database);
	$column_toggle->print_toggle_function();
	
	if (isset($_POST['column'])) 
	{
		$column_toggle->on_click_update_status();
	}

?>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<!-- Credit & Reference: https://artisansweb.net/drag-drop-file-upload-using-javascript-php/ --->
		
		<div class = "align-center">
			<div id="drop_file_zone" ondrop="upload_file(event)" ondragover="return false">
                <h2>Drop Excel Document in Box or Select File</h2>
				<div id="drag_upload_file">
					<p><input type="button" value="Select File" onclick="file_explorer();"></p>
					<input type="file" id="selectfile">
				</div>
			</div>	
		</div>


		<script type="text/javascript">
			let file;

			function upload_file(e)
			{
				e.preventDefault();
				ajax_file_upload(e.dataTransfer.files[0]);
			}

			function file_explorer() 
			{
				document.getElementById('selectfile').click();
				document.getElementById('selectfile').onchange = function() 
				{
					file = document.getElementById('selectfile').files[0];
					ajax_file_upload(file);
				};
			}

			function ajax_file_upload(file_obj) 
			{
				if(file_obj !== undefined) 
				{
					var form_data = new FormData();                  
					form_data.append('file', file_obj);
					$.ajax
					({
						type: 'POST',
						url: 'functionality/upload_and_update_table.php',
						contentType: false,
						processData: false,
						data: form_data,
						success:function(response) 
						{
							alert(response);
							$('#selectfile').val('');
						}
					});
				}
			}
		</script>

        <form class="align-center" method="post">
            <div class="login_container">
                <p>Change Password</p>
                <label>Old Password</label>
                <input type="password" name="old_password" placeholder="Old Password">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="New password">
                <input type="submit" value="Change" name="request" class="button">
            </div>
        </form>

    </body>
</html>