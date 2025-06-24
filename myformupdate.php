<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Update form </title>
	<meta charset="utf-8">
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	 <link rel="stylesheet" type="text/css" href="assests/css/style1.css">
	<style type="text/css">
		span
		{
			color: red;
		}
	</style>
</head>
<body>


<?php
require 'myformdatabase.php';
// print_r($_SESSION);
// print_r($_SESSION['user']['status']);
// $user = $_SESSION['user'] ?? ['Name' => '', 'email' => ''];
?>
<section class="main-section">
	<div class="container">
		
		<!-- events button we are requesting event.php file to run when events button gets clicked --> 
		<div class="events-link">
			<a href ="events.php" class="buttons" name ="events">EVENTS</a>
		</div>

		<div class="form-outer">



			<form method="post" action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				  <input type='hidden' name="action" value="update-form"/>
				  <h2 class="text-center">UPDATE FORM</h2>

				<div class="messages"> 
				   <?php echo $session_message; ?>
				   <?php echo $database_message; ?>
				</div>

				   <!-- Display message within form -->
				  <?php if ($message): ?>
					<p style="color: <?= htmlspecialchars($message_color) ?>; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
				  <?php endif; ?>

				  <!-- Actual Form Starts here -->
				 <table>
				 <tr>
				   <div class=" class="form-group col-lg-6 mb-4 mb-md-3 mb-sm-3 col-md-6 col-sm-12 mb-xs-3>
						<td><label for="name">Name</label></td>
						<td><input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_SESSION['user']['Name'] ?? ''); ?>"><span class="error">*
						<?php echo $errors['name'] ?? ''; ?>
					 </span></td>
				   </div>
				 </tr>
				 <tr>
					<td><label for="email">Email</label></td>
					<td><input type="Email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>"> <span class="error">*<?php echo $errors['email'] ?? ''; ?></span></td>
				 </tr>
				 <tr>
					<td><label for="password">Password</label></td>
					<td><input type="password"  class="form-control" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"> <span class="error" >* <?php echo $errors['password'] ?? ''; ?></span></td>
				 </tr>
				 <tr>
					<td><label for="cfpassword">Confirm Password</label></td>
					<td><input type="password" class="form-control" name="cfpassword" value="<?php echo htmlspecialchars($_POST['cfpassword'] ?? ''); ?>"> <span class="error">* <?php echo $errors['cfpassword'] ?? ''; ?></span></td>
				 </tr>
				 <tr>
					<td><label for="status">Status</label></td>
					<td>
						 <?php 
							$status = $_SESSION['user']['status'] ?? 0;
						 ?>
						<select name="status" class="form-control" id="status">
							<option value="1" <?= $status == 1 ? 'selected' : '' ?>>Active</option>
							<option value="0" <?= $status == 0 ? 'selected' : '' ?>>Inactive</option>
						</select>
					</td>
				 </tr>
				 <tr>
					<td><input type="submit" class="buttons-new" name="update"></td>
					<td> <a href = "myformlogout.php" class="buttons" name ="logout">LOGOUT</a></td> 
				 </tr>

				</table>
				
			</form>
		</div>
	</div>
</section>
</body>
</html>