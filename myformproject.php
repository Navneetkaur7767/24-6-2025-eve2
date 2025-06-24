<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>my form project </title>
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
?>
<section class="main-section">
<div class="container">
<div class="form-outer">

<form method="post" action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
      <input type='hidden' name="action" value="register-form"/>
     <h2 class="text-center">REGISTRATION FORM</h2>

        <div class="messages"> 
             <?php echo $session_message; ?>
             <?php echo $database_message; ?>
       </div>

     <!-- Display message within form -->
      <?php if ($message): ?>
        <p style="color: <?= htmlspecialchars($message_color) ?>; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

      <!-- Actual form start here  -->
     <table>
     <tr>
     	<td><label for="name">Name</label></td>
     	<td><input type="text"  class="form-control" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"><span class="error">*
            <?php echo $errors['name'] ?? ''; ?>
     	 </span></td>

     </tr>
     <tr>
     	<td><label for="email">Email</label></td>
     	<td><input type="Email"  class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"> <span class="error">*<?php echo $errors['email'] ?? ''; ?></span></td>
     </tr>
     <tr>
     	<td><label for="password">Password</label></td>
     	<td><input type="password"  class="form-control" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"> <span class="error" >*   <?php echo $errors['password'] ?? ''; ?></span></td>
     </tr>
     <tr>
     	<td><label for="cfpassword">Confirm Password</label></td>
     	<td><input type="password"  class="form-control" name="cfpassword" value="<?php echo htmlspecialchars($_POST['cfpassword'] ?? ''); ?>"> <span class="error">* <?php echo $errors['cfpassword'] ?? ''; ?></span></td>
     </tr>
     <tr>
     	<td><input type="submit" class="buttons-new" name="submit"></td>
        <td> <a href = "myformlogin.php" class="buttons" title ="login">LOGIN</a></td>
     </tr>
	</table>
	
</form>
 <!-- <p style="margin-left: 2rem;"> 
      <a href = "myformlogin.php" class="buttons" title ="login">LOGIN</a>
   </p> -->
</div>
</div>
</section>

</body>
</html>