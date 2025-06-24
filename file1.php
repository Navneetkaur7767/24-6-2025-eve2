<?php
session_start();
require 'myformdatabasee.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM formDATA WHERE email='$email' AND password='$password' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $_SESSION['user'] = $result->fetch_assoc(); // save user info
        header("Location: update.php"); // go to update form
        exit;
    } else {
        echo "<p style='color:red;'>Invalid login credentials.</p>";
    }
}
?>

<?php
session_start();
require 'myformdatabasee.php';

if (!isset($_SESSION['user'])) {
    die("Access denied. Please <a href='login.php'>login</a>.");
}

$user = $_SESSION['user'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $cfpassword = trim($_POST['cfpassword']);

    // Optional updates only if field is not empty
    $updates = [];

    if (!empty($new_name) && $new_name !== $user['Name']) {
        if (!preg_match("/^[a-zA-Z-' ]*$/", $new_name)) {
            $errors['name'] = "Only letters and white space allowed.";
        } else {
            $updates[] = "Name = '$new_name'";
            $user['Name'] = $new_name;
        }
    }

    if (!empty($new_email) && $new_email !== $user['email']) {
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format.";
        } else {
            // Check for existing email
            $check = $conn->query("SELECT * FROM formDATA WHERE email = '$new_email' AND id != {$user['id']}");
            if ($check->num_rows > 0) {
                $errors['email'] = "Email already in use.";
            } else {
                $updates[] = "email = '$new_email'";
                $user['email'] = $new_email;
            }
        }
    }

    if (!empty($new_password)) {
        if (strlen($new_password) < 8 || 
            !preg_match("#[0-9]+#", $new_password) ||
            !preg_match("#[A-Z]+#", $new_password) ||
            !preg_match("#[a-z]+#", $new_password)) {
            $errors['password'] = "Password must include 8+ characters with upper, lower, and a number.";
        } elseif ($new_password !== $cfpassword) {
            $errors['cfpassword'] = "Passwords do not match.";
        } else {
            $updates[] = "password = '$new_password'";
        }
    }

    // If there are updates and no errors
    if (empty($errors) && !empty($updates)) {
        $update_sql = "UPDATE formDATA SET " . implode(", ", $updates) . " WHERE id = {$user['id']}";
        if ($conn->query($update_sql)) {
            $_SESSION['user'] = $user; // update session
            echo "<p style='color:green;'>Profile updated successfully!</p>";
        } else {
            echo "<p style='color:red;'>Update failed: {$conn->error}</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Update Profile</title></head>
<body>
<h2>Update Profile</h2>
<form method="post">
    <label>Name: </label><input type="text" name="name" value=""><span style="color:red;"><?php echo $errors['name'] ?? ''; ?></span><br>
    <label>Email: </label><input type="email" name="email" value=""><span style="color:red;"><?php echo $errors['email'] ?? ''; ?></span><br>
    <label>New Password: </label><input type="password" name="password"><span style="color:red;"><?php echo $errors['password'] ?? ''; ?></span><br>
    <label>Confirm Password: </label><input type="password" name="cfpassword"><span style="color:red;"><?php echo $errors['cfpassword'] ?? ''; ?></span><br>
    <input type="submit" value="Update">
</form>
</body>
</html>



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
    <td><input type="submit" class="buttons-new" name="update" value="Update"></td>
    <td>
        <form method="POST" action="myformlogout.php">
            <button type="submit" class="buttons">LOGOUT</button>
        </form>
    </td>
</tr>

<tr>
    <td><input type="submit" class="buttons-new" name="update" value="Update"></td>
    <td>
        <form method="POST" action="myformlogout.php">
            <button type="submit" class="buttons">LOGOUT</button>
        </form>
    </td>
</tr>

<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // Or your homepage
exit();

// New status update
if (isset($_POST['status'])) {
    $new_status = (int)$_POST['status'];
    if ($new_status !== (int)$user['status']) {
        $updates[] = "status = $new_status";
        $user['status'] = $new_status; // Update in local user array
    }
}


if (empty($errors) && !empty($updates)) {
    $update_sql = "UPDATE formDATA SET " . implode(", ", $updates) . " WHERE id = $user_id";



?>
    <!-- locic to show dates-->
                                    <!-- empty cells before the actual dates like star pattern -->
                                    <!-- we will print empty cell till first day of week  -->
                                    <!-- $firstDayOfWeek = 3; // Means the month starts on Wednesday -->   



 <tr>

    <?php 
        for($i=0;$i<$firstDayOfWeek;$i++)
            {
                echo "<td></td>";
            }

// <!-- actual cells will be printed-->
    
        for($day=1;$day<=$daysInMonth;$day++)
        {
        // check if it is Today's date
            $isToday=($day==date('j') && $month==date('n') && $year==date('Y'));
            $class=$isToday?"class='today'":"";
            echo "<td$class>$day</td>";
            
            if(($day+$firstDayOfWeek)%7==0  && $day != $daysInMonth)
                {
                    echo "</tr><tr>";
                }
        }

    // <!-- empty cells after the actual date-->
         $remaining = (7 - (($day + $firstDayOfWeek - 1) % 7)) % 7;
        for ($i = 0; $i < $remaining; $i++) {
            echo "<td></td>";  // Print empty cells
        }


    ?>

     </tr>













<tbody>
                                    <tr> <!-- Start the first row -->

                                    <!-- Print empty cells before the first day -->
                                    <?php 

                                    for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                        $prevDate = $daysInPrevMonth - $firstDayOfWeek + 1 + $i;
                                        echo "<td style='color: #ccc;'>$prevDate</td>";
                                    }

                                    // main cell logic
                                    for ($day = 1; $day <= $daysInMonth; $day++) {
                                        // check if date is today's
                                        $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                                        $class = $isToday ? "class='today'" : "";

                                        // new added to check the date when this was addded as my sql will only work fine if this is added here becouse day is defined first otherwise it will give 00 FOR DATE
                                        $fullDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

                                        // Check if the current day is a Saturday or Sunday
                                        $dayOfWeek = date('w', strtotime("$year-$month-$day"));

                                       // Start opening <td> with styles 
                                        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                                            // Weekend styling
                                            $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
                                            echo "<td id='cell-$fullDate' style='$style' $class onclick=\"promptForEvent('$fullDate')\">";
                                        } else {
                                            // Weekday
                                            echo "<td id='cell-$fullDate' $class onclick=\"promptForEvent('$fullDate')\">";
                                        }

                                        // Show day number for each cell
                                        // echo $day;
                                        echo "<div class='day-number'>$day</div>";
                                        echo "<div class='event-container' id='events-$fullDate'>";

                                        // show events if there exist any in same cell div
                                        if (isset($eventsByDate[$fullDate])) {
                                            // here first is key and inside it also contain key value pairs 
                                            foreach ($eventsByDate[$fullDate] as $eventId =>$event) {
                                                // first div for strip and inside it are span elements for text and two buttons
                                                echo "<div class='event-strip' id='event-$eventId' >";
                                                echo "<span class='event-text' id='title-$eventId'>". htmlspecialchars($event) ."</span>";
                                                echo "<span class='event-actions'>
                                                     <button class='edit-btn' onclick='event.stopPropagation(); 
                                                     promptEditEvent($eventId)'>
                                                     <i class='fa fa-pencil'></i></button>
                                                     <button class='dlt-btn' onclick=\" event.stopPropagation(); deleteEvent($eventId)\">
                                                     <i class='fa fa-remove'></i></button>
                                                    </span>";
                                                echo"</div>";
                                            }
                                        }
                                        // Close the event container and <td>
                                        echo "</div>"; // end of event-container
                                        echo "</td>";

                                       // Close and open a new row every 7 cells
                                        if (($day + $firstDayOfWeek) % 7 == 0 && $day != $daysInMonth) {
                                            echo "</tr><tr>"; // properly handle row change
                                        }
                                    }

                                    // Fill remaining cells to complete the last row
                                    
                                    // Fill in next month dates
                                    $remainingCells = (7 - (($daysInMonth + $firstDayOfWeek) % 7)) % 7;
                                    for ($i = 1; $i <= $remainingCells; $i++) {
                                        echo "<td style='color: #ccc;'>$i</td>";
                                    }
                                    ?>

                                    </tr> <!-- Close the last row -->
                                </tbody>

                        </table>
                        <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div> now what changes i need to do here i have written the getweeks function and i put thead in seperate table but confused about thead like i do not want my cells styling to change and onclick thing also for insert delete edit 
     