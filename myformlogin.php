<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login form</title>
	<meta charset="utf-8">
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assests/css/style1.css">
	<style type="text/css">

		span
		{
			color: red;
		}
        /*.container
        {
            display: flex;
            align-items: center;
            justify-content: center;
        }*/
	</style>
</head>
<body>

<?php
require 'myformdatabase.php';
?>
<section class="main-section">
	<div class="container">
		<div class="form-outer">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    	<input type='hidden' name="action" value="login-form"/>
   		<h2 class="text-center">LOGIN FORM</h2>

       <div class="messages"> 
             <?php echo $session_message; ?>
             <?php echo $database_message; ?>
       </div>

        <!-- Display message within form -->
      <?php if ($message): ?>
        <p style="color: <?= htmlspecialchars($message_color) ?>; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

     <table>
        <tr>
            <td><label for="email">email</label></td>
            <td><input type="Email"  class="form-control" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"><span class="error">*
            <?php echo $errors['email'] ?? ''; ?>
            </span></td>
        </tr>
        <tr>
          <tr>
        <td><label for="password">Password</label></td>
        <td><input type="password"  class="form-control" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"> <span class="error" >*   <?php echo $errors['password'] ?? ''; ?></span></td>
        </tr>
            <td><input type="submit" class="buttons-new" name="login"></td>
            <td> <a href = "myformproject.php" class="buttons" title="register">REGISTER</a></td>
        </tr>
    </table>
</form>
 <!-- <p style="margin-left: 2rem;"> 
      <a href = "myformproject.php" tite = "register">REGISTER</a>
   </p>	 -->	
		</div>
	</div>
</section>

</body>
</html>