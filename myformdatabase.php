<?php
//start the session

session_start(); // Start the session


$message = ''; // Initialize message variable
$message_color = ''; // Initialize message color variable

//to check if session is active or not 
if (session_status() === PHP_SESSION_ACTIVE) {
    // echo "Session is active.";
    $session_message ="Session is active.";
} else {
    // echo "Session is not active.";
    $session_message ="Session is not active.";

}


$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$errors=[];
// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else
{
    $database_message ="<p>Connected with Database</p>";
}

//Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($sql)) {
    die("Database creation failed: " . $conn->error);
}



// Select the database
$conn->select_db($dbname);
// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS formDATA (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(60) NOT NULL
)";

$conn->query($sql);


 if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action']) && $_POST['action']=='register-form'){   // register form post data

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $cfpassword = trim($_POST['cfpassword'] ?? '');

    //name validation
   
     if(isset($_POST['name']) && empty($name)){
     	 $errors["name"]="name is required!";
     }
     elseif (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
        $errors['name'] = "Only letters and white space allowed.";
    }

    //email validation
     if(isset($_POST['email']) && empty($email)){
     	 $errors["email"]="email is required!";
     }
     elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
     	 $errors["email"]="Please enter valid email address";
     }


     //password validation
     if(isset($_POST['password']) && empty($password)){
     	 $errors["password"]="password is required!";
     }
      elseif (strlen($password) < 8) {
        $errors["password"] = "Password must be at least 8 characters.";
    } elseif (!preg_match("#[0-9]+#", $password)) {
        $errors["password"] = "Password must include at least one number.";
    } elseif (!preg_match("#[A-Z]+#", $password)) {
        $errors["password"] = "Password must include at least one uppercase letter.";
    } elseif (!preg_match("#[a-z]+#", $password)) {
        $errors["password"] = "Password must include at least one lowercase letter.";
    }

    // confirm password
     if(isset($_POST['cfpassword']) && empty($cfpassword)){
     	 $errors["cfpasword"]="confirm password is required!";
     }

     elseif($_POST['cfpassword']!=$_POST['password']){
     	 $errors['password']="password does not matched!";
     }

    if(empty($errors))
      // If no errors, check if email exists and insert
    {
        // Check if email already exists
        $sql = "SELECT * FROM formDATA WHERE email = '$email' LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $errors['email'] = "Email already exists, try another.";
        } else {
            // Insert record
            $hashed_password = password_hash($cfpassword, PASSWORD_DEFAULT);
            $sql = "INSERT INTO formDATA (Name, email, password ,adddate,status) VALUES ('$name', '$email', '$hashed_password',NOW() ,0)";
            if ($conn->query($sql) === TRUE) {
                // echo "<p style='color:green;'>Registration successful</p>";
                $message = "Resistration Successful"; // Success message
                $message_color = "green"; // Color for success
                $_POST = [];
            } else {
                   $message = "Error with Database Connection"; // Error message
                   $message_color = "red"; // Color for success
                echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
            }
        }
    }
    else
    {
        // echo "<p style='color:red;'>please enter all  the fields</p>";
        $message = "Enter all the fields"; // Error message
        $message_color = "red"; // Color for success
    }




 }


// LOGIN FORM LOGIC
 if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action']) && $_POST['action']=='login-form')
{
 
     $email = trim($_POST['email'] ?? '');
     $password = trim($_POST['password'] ?? '');
 
    //email validation
     if (isset($_POST['email'])) {
    $email = $_POST['email']; // Get the email from the form submission

    if (empty($email)) {
        $errors["email"] = "Email is required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Please enter a valid email address.";
    }
    }

     //password validation
     if(isset($_POST['password']) && empty($password)){
         $errors["password"]="password is required!";
     }
      elseif (strlen($password) < 8) {
        $errors["password"] = "Password must be at least 8 characters.";
    } elseif (!preg_match("#[0-9]+#", $password)) {
        $errors["password"] = "Password must include at least one number.";
    } elseif (!preg_match("#[A-Z]+#", $password)) {
        $errors["password"] = "Password must include at least one uppercase letter.";
    } elseif (!preg_match("#[a-z]+#", $password)) {
        $errors["password"] = "Password must include at least one lowercase letter.";
    }

    if(empty($errors))
    {

    // Create SQL query
        $sql = "SELECT * FROM formDATA WHERE email = '$email'";
        $result = $conn->query($sql);

   if ($result->num_rows === 1) {
        // Fetch the hashed password and status from the database
        $row = $result->fetch_assoc();

        // print_r($row);die;
        $hashed_password = $row['password'];
        $status = $row['status']; // Get the user's status

        // Check if the user is active
        if ($status == 1) { // Assuming 1 means active
            if (password_verify($password, $hashed_password)) {
                // Password is correct, create session variables
                $_SESSION['valid'] = true;
                $_SESSION['email'] = $email;
                // $_SESSION['timeout'] = time();
                $_SESSION['id'] = $row['id']; // Save user ID from the fetched row
                // set the session variable before redirection so that update gets prefilled values
                $_SESSION['user'] = [
                    'Name' => $row['Name'], // Assuming you have a 'Name' field in your database
                    'email' => $row['email'],
                    'status' => $row['status'],
                ];

                // Redirect or proceed further
                 header("Location: myformupdate.php");
                    exit();
            } else {
                 $errors['password'] = "Invalid Password";
                // $msg = "Invalid password.";
                // echo "$msg";
            }
        } else {
            // $msg = "Your account is not active. Please activate your account.";
            // echo "$msg";
            $message = "Your account is not active. Please activate your account."; // Error message
            $message_color = "red"; // Color for Error
        }
    } else {
        $errors['email'] = "Email not found";
        // $msg = "Email not found.";
        // echo "$msg";
    }
    }
/*
<?php if (!empty($errors['general'])): ?>
    <div class="message"><?php echo htmlspecialchars($errors['general']); ?></div>
<?php endif; ?>
*/
}


// UPDATE FORM LOGIC 
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action']) && $_POST['action']=='update-form')
{
    $user_id = $_SESSION['id'] ?? null; // Retrieve user ID from session
    if (!$user_id) {
    die("User  not logged in."); // Prevent unauthorized access
    // header("Location: myformlogin.php");  Redirect to login page
    // exit();  Always exit after a redirect
    }

     // Fetch the current user data from the database
    $query = "SELECT Name, email, password ,status FROM formDATA WHERE id = $user_id";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch the user data
        // print_r($user);die;
    } else {
        die("User  not found."); // Handle appropriately
    }

    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $cfpassword = trim($_POST['cfpassword']);

    $updates = [];
    $updates[] = "editdate = NOW()"; // Add the current date and time

    // New name update
    if (!empty($new_name) && $new_name !== $user['Name']) {
        if (!preg_match("/^[a-zA-Z-' ]*$/", $new_name)) {
            $errors['name'] = "Only letters and white space allowed.";
        } else {
            $updates[] = "Name = '$new_name'";
            $user['Name'] = $new_name; // Update the local user array
        }
    }


     // New email update
    if (!empty($new_email) && $new_email !== $user['email']) {
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format.";
        } else {
            // Check for existing email
            $check = $conn->query("SELECT * FROM formDATA WHERE email = '$new_email' AND id != $user_id");
            if ($check->num_rows > 0) {
                $errors['email'] = "Email already in use.";
            } else {
                $updates[] = "email = '$new_email'";
                $user['email'] = $new_email; // Update the local user array
            }
        }
    }

     // New password update
    if (!empty($new_password)) {
        if (strlen($new_password) < 8 || 
            !preg_match("#[0-9]+#", $new_password) ||
            !preg_match("#[A-Z]+#", $new_password) ||
            !preg_match("#[a-z]+#", $new_password)) {
            $errors['password'] = "Password must include 8+ characters with upper, lower, and a number.";
        } elseif ($new_password !== $cfpassword) {
            $errors['cfpassword'] = "Passwords do not match.";
        } else {
            // Hash the password before storing it
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updates[] = "password = '$hashed_password'";
        }
    }
     

    // New status update
    if (isset($_POST['status'])) {
    $new_status = (int)$_POST['status'];
        if ($new_status !== (int)$user['status']) {
        $updates[] = "status = $new_status";
        $user['status'] = $new_status; // Update in local user array
        }
    }


    // If there are updates and no errors
    if (empty($errors) && !empty($updates)) {
        $update_sql = "UPDATE formDATA SET " . implode(", ", $updates) . " WHERE id = $user_id";
        if ($conn->query($update_sql)) {
            $_SESSION['user'] = $user; // Update session
            $_POST = [];
            // echo "<p style='color:green;'>Profile updated successfully!</p>";
              $message = "Profile updated successfully!"; // Success message
              $message_color = "green"; // Color for success
        } else {
            // echo "<p style='color:red;'>Update failed: {$conn->error}</p>";
            $message = "Update failed: {$conn->error}"; // Error message
            $message_color = "red"; // Color for error
        }
    }
}

// $conn->close();


?>
