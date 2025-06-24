<?php
// Get the current month and year or use the provided month and year from the URL
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n'); // Current month
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y'); // Current year

// Calculate the number of days in the month and the first day of the month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = strtotime("$year-$month-01");
$firstDayOfWeek = date('w', $firstDayOfMonth); // 0 (for Sunday) through 6 (for Saturday)



// Create an array of days of the week
$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Start the HTML output
echo "<h1>Calendar for " . date('F Y', $firstDayOfMonth) . "</h1>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr>";

// Display the days of the week
foreach ($daysOfWeek as $day) {
    echo "<th>$day</th>";
}
echo "</tr><tr>";

// Fill in the empty cells before the first day of the month
for ($i = 0; $i < $firstDayOfWeek; $i++) {
    echo "<td></td>";
}

// Display the days of the month
for ($day = 1; $day <= $daysInMonth; $day++) {
    // Highlight today's date
    if ($day == date('j') && $month == date('n') && $year == date('Y')) {
        echo "<td style='background-color: yellow;'>$day</td>";
    } else {
        echo "<td>$day</td>";
    }

    // Start a new row after Saturday
    if (($day + $firstDayOfWeek) % 7 == 0) {
        echo "</tr><tr>";
    }
}
// $remaining = (7 - (($day + $firstDayOfWeek - 1) % 7)) % 7;
// for ($i = 0; $i < $remaining; $i++) {
//     echo "<td></td>";
// }
// Fill in the empty cells after the last day of the month
while (($day + $firstDayOfWeek) % 7 != 0) {
    echo "<td></td>";
    $day++;
}

echo "</tr>";
echo "</table>";

// Navigation links for previous and next month
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

echo "<br>";
echo "<a href='?month=$prevMonth&year=$prevYear'>Previous Month</a> | ";
echo "<a href='?month=$nextMonth&year=$nextYear'>Next Month</a>";
?>



<?php
// PHP section: Prepare dates and variables
$month = 8;
$year = 2023;
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = strtotime("$year-$month-01");
$firstDayOfWeek = date('w', $firstDayOfMonth);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Calendar</title>
    <style>
        /* Your CSS styling here */
    </style>
</head>
<body>
    <h1>Calendar for <?= date('F Y', $firstDayOfMonth) ?></h1>
    <table>
        <thead>
            <tr>
                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <?php
            // PHP section: Output calendar cells
            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                echo "<td></td>";
            }
            for ($day = 1; $day <= $daysInMonth; $day++) {
                echo "<td>$day</td>";
                if (($day + $firstDayOfWeek) % 7 == 0) {
                    echo "</tr><tr>";
                }
            }
            ?>
            </tr>
        </tbody>
    </table>
</body>
</html>



<?php
// Approach 1: Using DateTime and DatePeriod for calendar generation

// Get month and year from URL or set to current month/year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

try {
    // Create DateTime object for the first day of the month
    $firstDay = new DateTime("$year-$month-01");
} catch (Exception $e) {
    // Fallback to current date if invalid
    $firstDay = new DateTime();
    $month = (int)$firstDay->format('n');
    $year = (int)$firstDay->format('Y');
}

// Number of days in the month
$daysInMonth = (int)$firstDay->format('t');

// Get the weekday of the first day of month (1 = Monday ... 7 = Sunday)
$startDayOfWeek = (int)$firstDay->format('N'); 

// Calculate total number of days including previous month padding
$totalCells = $daysInMonth + $startDayOfWeek - 1;
if ($totalCells % 7 != 0) {
    $totalCells += 7 - ($totalCells % 7);
}

$daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// Navigation months
$prev = clone $firstDay;
$prev->modify('-1 month');
$next = clone $firstDay;
$next->modify('+1 month');

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Calendar Approach 1 - DateTime & DatePeriod</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 400px; margin: 20px auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; text-align: center; border: 1px solid #ccc; }
    th { background: #f0f0f0; }
    .today { background-color: #ffeb3b; font-weight: bold; }
    .nav { text-align: center; margin-top: 15px; }
    a { text-decoration: none; margin: 0 10px; color: blue; }
    a:hover { text-decoration: underline; }
</style>
</head>
<body>
<h1>Calendar for <?= $firstDay->format('F Y') ?></h1>
<table>
    <thead>
        <tr>
            <?php foreach ($daysOfWeek as $day): ?>
            <th><?= $day ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php
    $currentDay = 1;
    $today = date('Y-m-d');
    for ($row = 0; $row < $totalCells / 7; $row++) {
        echo '<tr>';
        for ($col = 1; $col <= 7; $col++) {
            $cellIndex = $row * 7 + $col;
            $dateStr = '';
            if ($cellIndex >= $startDayOfWeek && $currentDay <= $daysInMonth) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
                $isToday = ($dateStr === $today);
                echo '<td' . ($isToday ? ' class="today"' : '') . '>' . $currentDay . '</td>';
                $currentDay++;
            } else {
                echo '<td></td>';
            }
        }
        echo '</tr>';
    }
    ?>
    </tbody>
</table>
<div class="nav">
    <a href="?month=<?= $prev->format('n') ?>&year=<?= $prev->format('Y') ?>">Previous Month</a> |
    <a href="?month=<?= $next->format('n') ?>&year=<?= $next->format('Y') ?>">Next Month</a>
</div>
</body>
</html>
?>
</content>
</create_file>

<create_file>
<path>calendar_approach_2.php</path>
<content>
<?php
// Approach 2: Using mktime and manual calculations

// Get month and year from URL or default to current 
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Number of days in the month
$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));

// First day of the month (0 = Sunday, 6 = Saturday)
$firstDayOfWeek = date('w', mktime(0, 0, 0, $month, 1, $year));

$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Navigation
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year -1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Calendar Approach 2 - mktime</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 420px; margin: 20px auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; text-align: center; padding: 10px; }
    th { background-color: #ddd; }
    .today { background-color: #ffeb3b; font-weight: bold; }
    .nav { text-align: center; margin: 15px 0; }
    a { margin: 0 15px; text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
</style>
</head>
<body>
<h1>Calendar for <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></h1>
<table>
    <thead>
        <tr>
        <?php foreach ($daysOfWeek as $day): ?>
            <th><?= $day ?></th>
        <?php endforeach;?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <?php
            // Blank cells before first day
            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                echo "<td></td>";
            }

            $dayCount = 1;
            $cellCount = $firstDayOfWeek;
            $today = date('Y-m-d');

            while ($dayCount <= $daysInMonth) {
                if ($cellCount % 7 == 0) echo "</tr><tr>";

                $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $dayCount);
                $class = ($currentDate == $today) ? 'today' : '';

                echo "<td class='$class'>$dayCount</td>";
                $dayCount++;
                $cellCount++;
            }

            // Blank cells after last day
            while ($cellCount % 7 != 0) {
                echo "<td></td>";
                $cellCount++;
            }
            ?>
        </tr>
    </tbody>
</table>

<div class="nav">
    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">Previous Month</a> |
    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">Next Month</a>
</div>
</body>
</html>
?>
</content>
</create_file>

<attempt_completion>
Here are two alternative complete PHP calendar implementations using different approaches:

1. **calendar_approach_1.php** utilizes PHP's DateTime object and formatted output along with a DatePeriod concept for generating calendar cells aligned with Monday as the first day.
2. **calendar_approach_2.php** uses `mktime()` and manual calculations for days and weeks with Sunday as the first day.

Both files produce a clean, styled, and navigable monthly calendar highlighting today's date and allowing navigation to previous and next months.

You can save each PHP file and run them on your PHP-enabled web server. Access them via browser with optional `?month=&year=` URL parameters to navigate.

Let me know if you want me to help with explanations on these or more approaches!
</attempt_completion>





<!-- First write html code  -->
<?php

// Get the current month and year or use the provided month and year from the URL
$rawmonth = $_GET['month'] ?? date('n');
$rawyear = $_GET['year'] ?? date('Y');
// Validate that both are numeric
// Initialize error
$invalid_error = null;

// Validate numeric input
if (!is_numeric($rawmonth) || !is_numeric($rawyear)) {
    $invalid_error = "Error: Month and year must be numeric.";
    $month = date('n');
    $year = date('Y');
} else

{
// Convert to integer only after confirming they are numeric
$month = (int)$rawmonth;
$year = (int)$rawyear;

    // logic for invalid month
    if ($month < 1 || $month > 12) {
        $invalid_error = "Error: Invalid month selected. Select range 1–12.";
         // fallback to current month to prevent fatal error so that there and current dates and year remain selected
        $month = date('n');
        $year = date('Y');
    }
    //  logic for invalid year range
    elseif ($year < 2005 || $year > 2045) {
        $invalid_error = "Error: Year must be between 2005 and 2045.";
        $month = date('n');
        $year = date('Y');
    }
}

// PHP section: Prepare dates and variables
// $month = 4;
// $year = 2025;
// it calculate days in month 
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

//it converts english output to number
$firstDayOfMonth = strtotime("$year-$month-01");

//its tell on that 2025-2-1 was which day of the week 
$firstDayOfWeek = date('w', $firstDayOfMonth);
// Create an array of days of the week
$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Determine navigation months
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

$daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

// new added Database connection
$conn = new mysqli("localhost", "localhost", "NAVneet345@", "myForm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create events table
$createTableSql = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_title VARCHAR(255) NOT NULL,
    startdate DATE NOT NULL,
    enddate DATE DEFAULT NULL,
    adddate DATETIME DEFAULT CURRENT_TIMESTAMP,
    editdate DATETIME DEFAULT NULL,
    user_email VARCHAR(100) NOT NULL
)";
$conn->query($createTableSql);

// Get current user's email from session
session_start();
$userEmail = $_SESSION['email'] ?? '';
$safeEmail = $conn->real_escape_string($userEmail);

// Get all events for the selected month
$monthStart = "$year-$month-01";
$monthEnd = date("Y-m-t", strtotime($monthStart));

$query = "SELECT event_title, startdate FROM events 
          WHERE user_email = '$safeEmail'
          AND startdate BETWEEN '$monthStart' AND '$monthEnd'";

$result = $conn->query($query);

// Group events by date
$eventsByDate = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $eventsByDate[$row['startdate']][] = $row['event_title'];
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CALENDAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="style1.css">
</head>
<body>
    <section class="cal-section">
        <div class="container">
            <div class="cal-outer">
                <h1 class="text-center">Calendar for <?= date('F Y', $firstDayOfMonth) ?></h1>

                    <?php if ($invalid_error): ?>
                        <div class="error-message text-danger text-center my-2 fw-bold">
                            <?= htmlspecialchars($invalid_error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- to show the name of month and year -->
                    <div class="row d-flex justify-content-between align-items-center">
                        <div class="col-5">
                            <h4><?= date('F Y', $firstDayOfMonth) ?></h4>
                        </div>

                   <!-- date selection auto -->
                        <form method="GET" class=" col-5 d-flex gap-2 align-items-center"> 
                            <!-- month selection dropdown-->
                            <select name="month" class="form-select" style="width: auto;">
                                <!--loop for month-->
                                <?php
                                    for($m=0; $m<=12 ;$m++)
                                    {
                                        // we will check if selected month is equal to th eloop in current month 
                                        $selected = ($m == $month) ? "selected" : "";
                                        echo "<option value='$m' $selected>" . date('M', mktime(0, 0, 0, $m, 10)) . "</option>";
                                    }

                                ?>
                            </select>
                            <select name="year" class="form-select" style="width: auto;" size="1">
                                <?php
                                $currentYear = date('Y');
                                for ($y = $currentYear - 20; $y <= $currentYear + 20; $y++) {
                                    $selected = ($y == $year) ? "selected" : "";
                                    echo "<option value='$y' $selected>$y</option>";
                                }
                                ?>
                            </select>
    
                            <button type="submit" class="btn btn-primary btn-sm">select</button>

                       </form>

                        <!-- it will put into the query previous month and year -->
                       <div class="col-2 text-end d-flex justify-content-end gap-2">
                            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">
                                <i class="bi bi-chevron-up" style="font-size: 40px; color: black;"></i>
                            </a>
                            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">
                                <i class="bi bi-chevron-down" style="font-size: 40px; color: black;"></i>
                            </a>
                        </div>

                    </div>

                    <div class="row table-box">
                        <table class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <!-- first show the days of week with loop -->
                                         <?php foreach ($daysOfWeek as $day): ?>
                                         <th style="background-color: antiquewhite;"><?= htmlspecialchars($day) ?></th>
                                         <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr> <!-- Start the first row -->

                                    <!-- Print empty cells before the first day -->
                                    <?php 
                                    // for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                    //     echo "<td></td>";
                                    // }

                                    for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                        $prevDate = $daysInPrevMonth - $firstDayOfWeek + 1 + $i;
                                        echo "<td style='color: #ccc;'>$prevDate</td>";
                                    }



                                    /*for ($day = 1; $day <= $daysInMonth; $day++) {
                                        // check if date is today's
                                        $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                                        $class = $isToday ? "class='today'" : "";
                                        // new added to check the date when this was addded as my swl will only work fine if this is added here becouse day is defined first otherwise it will give 00 FOR DATE
                                        $fullDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

                                        // Check if the current day is a Saturday or Sunday
                                        $dayOfWeek = date('w', strtotime("$year-$month-$day"));
                                        if ($dayOfWeek == 0 || $dayOfWeek == 6) {  // Sunday (0) or Saturday (6)
                                             $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
                                              // echo "<td style='$style' $class onclick=\"promptForEvent('$fullDate')\">$day</td>";

                                        } else {
                                            echo "<td $class onclick=\"promptForEvent('$fullDate')\">$day</td>";
                                               }

                                       // Close and open a new row every 7 cells
                                        if (($day + $firstDayOfWeek) % 7 == 0 && $day != $daysInMonth) {
                                            echo "</tr><tr>"; // properly handle row change
                                        }
                                    }*/

                                    // new added code 

                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                // Check if date is today's
                                $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                                $class = $isToday ? "class='today'" : "";

                                // Full date for DB comparison
                                $fullDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

                                // Determine if it's weekend
                                $dayOfWeek = date('w', strtotime("$year-$month-$day"));

                                // Start opening <td> with styles
                                if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                                    // Weekend styling
                                    $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
                                    echo "<td style='$style' $class onclick=\"promptForEvent('$fullDate')\">";
                                } else {
                                    // Weekday
                                    echo "<td $class onclick=\"promptForEvent('$fullDate')\">";
                                }

                                // Show day number
                                echo $day;

                                // ✅ SHOW EVENTS if any
                                if (isset($eventsByDate[$fullDate])) {
                                    foreach ($eventsByDate[$fullDate] as $event) {
                                        echo "<div style='font-size:12px; color:green;'>📌 " . htmlspecialchars($event) . "</div>";
                                    }
                                }

                                echo "</td>";

                                // Close and open a new row every 7 cells
                                if (($day + $firstDayOfWeek) % 7 == 0 && $day != $daysInMonth) {
                                    echo "</tr><tr>"; // properly handle row change
                                }
                            }




                                    // // Fill remaining cells to complete the last row
                                    // $remaining = (7 - (($day + $firstDayOfWeek - 1) % 7)) % 7;
                                    // for ($i = 0; $i < $remaining; $i++) {
                                    //     echo "<td></td>";
                                    // }
                                    // Fill in next month dates
                                    $remainingCells = (7 - (($daysInMonth + $firstDayOfWeek) % 7)) % 7;
                                    for ($i = 1; $i <= $remainingCells; $i++) {
                                        echo "<td style='color: #ccc;'>$i</td>";
                                    }
                                    ?>

                                    </tr> <!-- Close the last row -->
                                </tbody>

                        </table>
                    </div>
            </div>
        </div>  
    <section>
<!-- <script>
function promptForEvent(date) {
    let title = prompt("Enter event title for " + date + ":");
    if (title) {
        fetch('save_event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'event_date=' + encodeURIComponent(date) + '&event_title=' + encodeURIComponent(title)
        }).then(response => {
            if (response.ok) {
                location.reload(); // Refresh the page to show the new event
            } else {
                alert("Error saving event.");
            }
        });
    }
}
</script> -->
<script type="text/javascript">
function promptForEvent(date) {
    const title = prompt("Enter event title for " + date + ":");
    if (!title) return;

    const formData = new FormData();
    formData.append('event_title', title);
    formData.append('event_date', date);

    fetch('save_event.php', {
        method: 'POST',
        body: formData,
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        location.reload(); // Refresh to show new event
    })
    .catch(err => {
        alert("Error saving event.");
        console.error(err);
    });
}
</script>
</body>
</html>




<!-- save events php -->

<?php
session_start();
print_r($_SESSION);
$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

// Ensure user is logged in
$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

// Sanitize input
$eventTitle = $conn->real_escape_string($_POST['event_title'] ?? '');
$eventDate = $conn->real_escape_string($_POST['event_date'] ?? '');

if (!$eventTitle || !$eventDate) {
    http_response_code(400);
    echo "Missing event title or date.";
    exit;
}

$sql = "INSERT INTO events (event_title, startdate, user_email)
        VALUES ('$eventTitle', '$eventDate', '$userEmail')";

if ($conn->query($sql) === TRUE) {
    echo "Event saved.";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}
?>




<?php
session_start();

$servername = "localhost";
$username = "root";  // or your DB username
$password = "NAVneet345@"; // your DB password
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

// Ensure user is logged in
$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

// Get event_id from POST
$eventId = $_POST['event_id'] ?? '';
$eventId = (int) $eventId; // Make sure it's an integer

if (!$eventId) {
    http_response_code(400);
    echo "Missing or invalid event ID.";
    exit;
}

// Delete event (only if it belongs to logged-in user)
$sql = "DELETE FROM events WHERE id = $eventId AND user_email = '$userEmail'";

if ($conn->query($sql) === TRUE) {
    echo "Event deleted.";
} else {
    http_response_code(500);
    echo "Error deleting event: " . $conn->error;
}

$conn->close();
?>

<script>
function deleteEvent(eventId) {
    if (!confirm("Are you sure you want to delete this event?")) return;

    const formData = new FormData();
    formData.append("event_id", eventId);

    fetch("delete_event.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        console.log(msg);
        // Option 1: Reload to update UI
        location.reload();

        // Option 2: OR remove element manually if you have data-id or other reference
        // document.querySelector(`[data-id='${eventId}']`)?.remove();
    })
    .catch(err => {
        alert("Error deleting event.");
        console.error(err);
    });
}
</script>
<?php 
if (isset($eventsByDate[$fullDate])) {
    // Now you get both event ID and event title
    foreach ($eventsByDate[$fullDate] as $eventId => $event) {
        echo "<div class='event-strip'>";
        echo "<span class='event-text'>" . htmlspecialchars($event) . "</span>";

        // Removed incorrect single quotes and used the correct $eventId
        echo "<span class='event-actions'>
                <button class='edit-btn'><i class='fa fa-pencil'></i></button>
                <button class='dlt-btn' onclick=\"deleteEvent($eventId)\">
                    <i class='fa fa-remove'></i>
                </button>
              </span>";
        echo "</div>";
    }
}
echo "</td>";


echo "<div class='event-strip' data-id='{$eventId}'>
        <span class='event-text'>" . htmlspecialchars($eventTitle) . "</span>
        <span class='event-actions'>
            <button class='delete-btn' onclick='deleteEvent($eventId)'>
                <i class='fa fa-trash'></i>
            </button>
        </span>
      </div>";

?>


<?php

<?php
session_start();

// Connect to database
$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Database connection failed: " . $conn->connect_error;
    exit;
}

// Check if user is logged in
$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

// Get posted data
$eventId = (int)($_POST['event_id'] ?? 0);
$newTitle = trim($_POST['event_title'] ?? '');
$editDate = $_POST['edit_date'] ?? '';

// Basic validation
if (!$eventId || $newTitle === '' || !$editDate) {
    http_response_code(400);
    echo "Missing or invalid data";
    exit;
}

$safeEmail = $conn->real_escape_string($userEmail);
$safeTitle = $conn->real_escape_string($newTitle);
$safeEditDate = $conn->real_escape_string($editDate);

// Update query
$updateQuery = "
    UPDATE events 
    SET event_title = '$safeTitle', editdate = '$safeEditDate' 
    WHERE id = $eventId AND user_email = '$safeEmail'
";

if ($conn->query($updateQuery) === TRUE) {
    echo "Event updated successfully";
} else {
    http_response_code(500);
    echo "Error updating event: " . $conn->error;
}

$conn->close();
?>

?>


<!-- Script for editing an event -->
<script>
function promptEditEvent(eventId, currentTitle) {
    const title = prompt("Update the event title:", currentTitle); // Pre-fill current title
    if (!title) return;

    const formData = new FormData();
    formData.append("event_id", eventId);
    formData.append("event_title", title);
    formData.append("edit_date", new Date().toISOString().split('T')[0]); // e.g. 2025-05-20

    fetch("edit_event.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        console.log(msg);
        location.reload(); // Refresh to see updated event
    })
    .catch(err => {
        alert("Error updating event.");
        console.error(err);
    });
}
</script>


echo "<div class='event-strip'>
        <span class='event-text'>" . htmlspecialchars($eventTitle) . "</span>
        <span class='event-actions'>
            <button class='edit-btn' onclick=\"promptEditEvent($eventId, '" . htmlspecialchars(addslashes($eventTitle)) . "')\">
                <i class='fa fa-pencil'></i>
            </button>
            <button class='dlt-btn' onclick=\"deleteEvent($eventId)\">
                <i class='fa fa-trash'></i>
            </button>
        </span>
      </div>";









      <script>
function promptEditEvent(eventId, currentTitle) {
    const newTitle = prompt("Update the event title:", currentTitle);
    if (!newTitle) return;

    const formData = new FormData();
    formData.append("event_id", eventId);
    formData.append("new_title", newTitle);

    fetch("edit_event.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        console.log(msg);
        location.reload(); // Refresh the calendar after update
    })
    .catch(err => {
        alert("Error updating event.");
        console.error(err);
    });
}
</script>


<button class='edit-btn' onclick="promptEditEvent(<?php echo $eventId; ?>, '<?php echo addslashes($eventTitle); ?>')">
    <i class='fa fa-pencil'></i>
</button>


<?php
session_start();
$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

// Ensure user is logged in
$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

// Get data from POST
$eventId = (int)($_POST['event_id'] ?? 0);
$newTitle = $conn->real_escape_string($_POST['new_title'] ?? '');

if (!$eventId || !$newTitle) {
    http_response_code(400);
    echo "Missing data.";
    exit;
}

// Update title and editdate
$query = "UPDATE events SET event_title = '$newTitle', editdate = NOW() 
          WHERE id = $eventId AND user_email = '$userEmail'";

if ($conn->query($query) === TRUE) {
    echo "Event updated";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}

$conn->close();
?>
<? php

echo "<button class='edit-btn' onclick=\"event.stopPropagation(); promptEditEvent($eventId, '" . addslashes($eventTitle) . "')\">
        <i class='fa fa-pencil'></i>
      </button>";
?>


<!-- for updating by selecting  -->
<div id="update-msg" style="color: green; font-weight: bold; display: none;"></div>
.then(res=>res.text())
        .then(msg=>{
        const msgDiv = document.getElementById('update-msg');
        msgDiv.textContent = "Event updated successfully!";
        msgDiv.style.display = "block";

        setTimeout(() => {
            msgDiv.style.display = "none";
            location.reload();  // optional
        }, 2000); // hide after 2 seconds
        })
        .catch(err=>{
            alert("Error in Updating event");
            console.error(err);
        });
    }
</script>

<?
if (isset($eventsByDate[$fullDate])) {
    foreach ($eventsByDate[$fullDate] as $eventId => $event) {
        echo "<div class='event-strip' id='event-$eventId'>";
        echo "<span class='event-text' id='title-$eventId'>" . htmlspecialchars($event) . "</span>";
        echo "<span class='event-actions'>
             <button class='edit-btn' onclick=\"event.stopPropagation(); promptEditEvent($eventId, '" . addslashes($event) . "')\">
                 <i class='fa fa-pencil'></i>
             </button>
             <button class='dlt-btn' onclick=\"event.stopPropagation(); deleteEvent($eventId)\">
                 <i class='fa fa-remove'></i>
             </button>
             </span>";
        echo "</div>";
    }
}
echo "</td>";
?>

<script>
    function promptEditEvent(eventId, currentTitle) {
        const newTitle = prompt("Update the event title", currentTitle);
        if (!newTitle) return;

        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('new_title', newTitle);

        console.log("Editing event ID:", eventId, "New title:", newTitle);

        fetch('edit_event.php', {
            method: 'POST',
            body: formData,
        })
        .then(res => res.text())
        .then(msg => {
            console.log("Server response:", msg);

            // Update the event title in the DOM
            const titleElement = document.getElementById(`title-${eventId}`);
            if (titleElement) {
                titleElement.textContent = newTitle;
            }
            alert("Event updated successfully!");
            // No page reload needed
        })
        .catch(err => {
            alert("Error in updating event");
            console.error(err);
        });
    }
</script>

everything getting updated but not the value in prompt it is still getting old value 
<button onclick="promptEditEvent(<?php echo $eventId; ?>)">


function promptEditEvent(eventId) {
    const titleElement = document.getElementById(`title-${eventId}`);
    if (!titleElement) return;

    const currentTitle = titleElement.textContent.trim();
    const newTitle = prompt("Update the event title", currentTitle);
    if (!newTitle || newTitle === currentTitle) return;

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('new_title', newTitle);

    fetch('edit_event.php', {
        method: 'POST',
        body: formData,
    })
    .then(res => res.text())
    .then(msg => {
        // Update the title in the DOM
        titleElement.textContent = newTitle;
        console.log("Updated:", msg);
    })
    .catch(err => {
        alert("Error updating event");
        console.error(err);
    });
}



function promptEditEvent(eventId) {
    const titleElement = document.getElementById(`title-${eventId}`);
    const currentTitle = titleElement ? titleElement.textContent.trim() : '';

    const newTitle = prompt("Update the event title", currentTitle);
    if (!newTitle || newTitle === currentTitle) return;

    // Continue with FormData, fetch, and update the DOM
}

<!-- logic for delete withou reloading the content  -->
<script>
function deleteEvent(eventId) {
    console.log("Deleting event with ID:", eventId); //Debug line
    if (!confirm("Do you want to delete this event?")) return;

    const formData = new FormData();
    formData.append('event_id', eventId);

    fetch('delete_event.php', {
        method: 'POST',
        body: formData,
    })
    .then(res => res.text())
    .then(msg => {
        alert("Event deleted successfully!");

        // Remove the event from the DOM
        const eventElement = document.getElementById(`event-${eventId}`);
        if (eventElement) {
            eventElement.remove(); // This removes the event block from the page
        }
    })
    .catch(err => {
        alert("Error deleting event. Please try again!");
        console.error(err);
    });
}
</script>


<?
echo "<td id='cell-$fullDate' onclick=\"promptForEvent('$fullDate')\">";
echo "<div class='day-number'>$day</div>";
echo "<div class='event-container' id='events-$fullDate'>";
?>

// Add the event to the right cell
const eventBox = document.getElementById('events-' + date);
if (eventBox) {
    const eventId = msg; // Or parse JSON if msg is more complex
    const eventHTML = `
        <div class='event-strip' id='event-${eventId}'>
            <span class='event-text' id='title-${eventId}'>${title}</span>
            <span class='event-actions'>
                <button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent(${eventId})'>
                    <i class='fa fa-pencil'></i>
                </button>
                <button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent(${eventId})'>
                    <i class='fa fa-remove'></i>
                </button>
            </span>
        </div>`;
    eventBox.insertAdjacentHTML('beforeend', eventHTML);
}


echo $conn->insert_id;








<!-- onclick event code  -->
// Start opening <td> with styles
if ($dayOfWeek == 0 || $dayOfWeek == 6) {
    // Weekend styling
    $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
    echo "<td id='cell-$fullDate' style='$style' $class onclick=\"promptForEvent('$fullDate')\">";
} else {
    // Weekday
    echo "<td id='cell-$fullDate' $class onclick=\"promptForEvent('$fullDate')\">";
}

// Show day number and container for events
echo "<div class='day-number'>$day</div>";
echo "<div class='event-container' id='events-$fullDate'>";

// Show events (if any)
if (isset($eventsByDate[$fullDate])) {
    foreach ($eventsByDate[$fullDate] as $eventId => $event) {
        echo "<div class='event-strip' id='event-$eventId'>";
        echo "<span class='event-text' id='title-$eventId'>" . htmlspecialchars($event) . "</span>";
        echo "<span class='event-actions'>
                <button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent($eventId)'>
                    <i class='fa fa-pencil'></i>
                </button>
                <button class='dlt-btn' onclick=\"event.stopPropagation(); deleteEvent($eventId)\">
                    <i class='fa fa-remove'></i>
                </button>
              </span>";
        echo "</div>";
    }
}

// Close the event container and <td>
echo "</div>"; // end of event-container
echo "</td>";


<!-- save event php  -->

<?php
session_start();

$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed."]);
    exit;
}

// Ensure user is logged in
$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Sanitize input
$eventTitle = $conn->real_escape_string($_POST['event_title'] ?? '');
$eventDate = $conn->real_escape_string($_POST['event_date'] ?? '');

if (!$eventTitle || !$eventDate) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing event title or date."]);
    exit;
}

// Insert into DB
$sql = "INSERT INTO events (event_title, startdate, enddate, user_email, adddate)
        VALUES ('$eventTitle', '$eventDate', '$eventDate', '$userEmail', NOW())";

if ($conn->query($sql) === TRUE) {
    $newEventId = $conn->insert_id;

    echo json_encode([
        "success" => true,
        "event_id" => $newEventId,
        "event_title" => htmlspecialchars($eventTitle),
        "event_date" => $eventDate
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error"]);
}

$conn->close();
?>


<!-- js updation  -->

.then(res => res.json())
.then(data => {
    if (data.success) {
        const container = document.getElementById(`events-${data.event_date}`);
        if (container) {
            const eventDiv = document.createElement('div');
            eventDiv.className = 'event-strip';
            eventDiv.id = `event-${data.event_id}`;
            eventDiv.innerHTML = `
                <span class="event-text" id="title-${data.event_id}">${data.event_title}</span>
                <span class="event-actions">
                    <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${data.event_id})">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${data.event_id})">
                        <i class="fa fa-remove"></i>
                    </button>
                </span>
            `;
            container.appendChild(eventDiv);
        }
        alert("Event inserted successfully!");
    } else {
        alert("Error saving event.");
        console.error(data.message);
    }
})










<!-- same code with reload  -->


<?php
session_start();
print_r($_SESSION);
$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

// Ensure user is logged in
$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

// Sanitize input
$eventTitle = $conn->real_escape_string($_POST['event_title'] ?? '');
$eventDate = $conn->real_escape_string($_POST['event_date'] ?? '');

if (!$eventTitle || !$eventDate) {
    http_response_code(400);
    echo "Missing event title or date.";
    exit;
}

$sql = "INSERT INTO events (event_title, startdate ,enddate , user_email ,adddate)
        VALUES ('$eventTitle', '$eventDate','$eventDate', '$userEmail',NOW())";

if ($conn->query($sql) === TRUE) {
    echo "Event saved.";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}
?>

<script type="text/javascript">
function promptForEvent(date) {
    const title = prompt("Enter event title for " + date + ":");
    if (!title) return;

    const formData = new FormData();
    formData.append('event_title', title);
    formData.append('event_date', date);

    fetch('save_event.php', {
        method: 'POST',
        body: formData,
    })
    .then(res => res.text())
    .then(msg => {
        alert("Event inserted successfully!");
        location.reload(); // Refresh to show new event
    })
    .catch(err => {
        alert("Error saving event.");
        console.error(err);
    });
}

</script>



<div class="calendar-row" id="week-1">
    <table class="calendar-table">
        <tr>
            <td id="cell-2025-05-01"></td>
            <td id="cell-2025-05-02"></td>
            <td id="cell-2025-05-03"></td>
            <td id="cell-2025-05-04"></td>
            <td id="cell-2025-05-05"></td>
            <td id="cell-2025-05-06"></td>
            <td id="cell-2025-05-07"></td>
        </tr>
    </table>
    <div class="event-overlay-container"></div>
</div>


.calendar-row {
    position: relative;
}

.event-overlay-container {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    pointer-events: none; /* allows click-through to calendar cells */
}

.event-strip {
    position: absolute;
    top: 5px;
    height: 20px;
    background: yellow;
    color: black;
    border-radius: 4px;
    font-weight: bold;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    padding: 0 6px;
    pointer-events: auto; /* enables interaction with strip buttons */
}

<script>
function renderMultiDayStrip(startDate, endDate, title) {
    const startCell = document.getElementById(`cell-${startDate}`);
    const endCell = document.getElementById(`cell-${endDate}`);
    if (!startCell || !endCell) return;

    const row = startCell.closest('.calendar-row');
    const container = row.querySelector('.event-overlay-container');

    const startRect = startCell.getBoundingClientRect();
    const endRect = endCell.getBoundingClientRect();
    const rowRect = row.getBoundingClientRect();

    const left = startRect.left - rowRect.left;
    const width = endRect.right - startRect.left;

    const strip = document.createElement('div');
    strip.className = 'event-strip';
    strip.style.left = `${left}px`;
    strip.style.width = `${width}px`;
    strip.innerHTML = title;

    container.appendChild(strip);
}
</script>



<!-- multi day event code changes implementing now date 22-5-2025 -->

<div class="row table-box">

  <!-- For each week -->
  <?php foreach ($weeks as $weekNumber => $weekDays): ?>
    <div class="calendar-row" id="week-<?= $weekNumber ?>">
      <table class="calendar-table table-fixed w-100">
        <tr>
          <?php foreach ($weekDays as $fullDate): ?>
            <td id="cell-<?= $fullDate ?>" onclick="promptForEvent('<?= $fullDate ?>')">
              <div class="day-number"><?= date('j', strtotime($fullDate)) ?></div>
              <div class="event-container" id="events-<?= $fullDate ?>"></div>
            </td>
          <?php endforeach; ?>
        </tr>
      </table>
      <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div>
    </div>
  <?php endforeach; ?>

</div>


.calendar-row {
    position: relative;
    margin-bottom: 4px; /* optional spacing between weeks */
}

.event-overlay-container {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    pointer-events: none; /* allows you to click through to the cells */
}

.event-strip {
    position: absolute; /* This is critical */
    top: 5px;
    height: 24px;
    z-index: 10;
    pointer-events: auto; /* so buttons can be clicked */
}


<!-- Month header -->
<table class="table table-bordered w-100 table-fixed">
  <thead>
    <tr>
      <?php foreach ($daysOfWeek as $day): ?>
        <th style="background-color: antiquewhite;"><?= htmlspecialchars($day) ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
</table>

<div class="row table-box">
  <?php foreach ($weeks as $weekNumber => $weekDays): ?>
    <div class="calendar-row" id="week-<?= $weekNumber ?>">
      <table class="calendar-table table-fixed w-100">
        <tr>
          <?php foreach ($weekDays as $fullDate): ?>
            <td id="cell-<?= $fullDate ?>" onclick="promptForEvent('<?= $fullDate ?>')">
              <div class="day-number"><?= date('j', strtotime($fullDate)) ?></div>
              <div class="event-container" id="events-<?= $fullDate ?>">
                <!-- single day events here -->
              </div>
            </td>
          <?php endforeach; ?>
        </tr>
      </table>
      <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div>
    </div>
  <?php endforeach; ?>
</div>



function getWeeks($year, $month) {
    $weeks = [];
    $currentWeek = [];

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $firstDayOfWeek = date('w', strtotime("$year-$month-01"));

    // Add previous month dates to fill first week
    if ($firstDayOfWeek > 0) {
        for ($i = $firstDayOfWeek - 1; $i >= 0; $i--) {
            $prevDate = date('Y-m-d', strtotime("$year-$month-01 -$i days"));
            $currentWeek[] = $prevDate;
        }
    }

    // Fill current month
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $currentWeek[] = $date;

        if (count($currentWeek) == 7) {
            $weeks[] = $currentWeek;
            $currentWeek = [];
        }
    }

    // Fill last week with next month days if needed
    if (!empty($currentWeek)) {
        $lastDate = end($currentWeek);
        $daysToAdd = 7 - count($currentWeek);
        for ($i = 1; $i <= $daysToAdd; $i++) {
            $nextDate = date('Y-m-d', strtotime("$lastDate +$i days"));
            $currentWeek[] = $nextDate;
        }
        $weeks[] = $currentWeek;
    }

    return $weeks;
}


<?php
$weeks = getWeeks($year, $month);
$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>

<!-- Calendar header row -->
<table class="table table-bordered w-100 table-fixed">
  <thead>
    <tr>
      <?php foreach ($daysOfWeek as $day): ?>
        <th style="background-color: antiquewhite;"><?= htmlspecialchars($day) ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
</table>

<!-- Calendar body -->
<div class="row table-box">
  <?php foreach ($weeks as $weekNumber => $weekDays): ?>
    <div class="calendar-row" id="week-<?= $weekNumber ?>">
      <table class="calendar-table table-fixed w-100">
        <tr>
          <?php foreach ($weekDays as $fullDate): ?>
            <td id="cell-<?= $fullDate ?>" onclick="promptForEvent('<?= $fullDate ?>')">
              <div class="day-number"><?= date('j', strtotime($fullDate)) ?></div>
              <div class="event-container" id="events-<?= $fullDate ?>">
                <!-- Insert single-day event strips here via PHP or JS -->
              </div>
            </td>
          <?php endforeach; ?>
        </tr>
      </table>

      <!-- Multi-day event overlay for this week -->
      <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div>
    </div>
  <?php endforeach; ?>
</div>


<!-- again writing  -->

function getWeeks($year, $month) {
    $weeks = [];
    $currentWeek = [];

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $firstDayOfWeek = date('w', strtotime("$year-$month-01"));

    // Add previous month dates to fill first week
    if ($firstDayOfWeek > 0) {
        for ($i = $firstDayOfWeek - 1; $i >= 0; $i--) {
            $prevDate = date('Y-m-d', strtotime("$year-$month-01 -$i days"));
            $currentWeek[] = $prevDate;
        }
    }

    // Fill current month
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $currentWeek[] = $date;

        if (count($currentWeek) == 7) {
            $weeks[] = $currentWeek;
            $currentWeek = [];
        }
    }

    // Fill last week with next month days if needed
    if (!empty($currentWeek)) {
        $lastDate = end($currentWeek);
        $daysToAdd = 7 - count($currentWeek);
        for ($i = 1; $i <= $daysToAdd; $i++) {
            $nextDate = date('Y-m-d', strtotime("$lastDate +$i days"));
            $currentWeek[] = $nextDate;
        }
        $weeks[] = $currentWeek;
    }

    return $weeks;
}


<?php
$weeks = getWeeks($year, $month);
$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>

<!-- Calendar header row -->
<table class="table table-bordered w-100 table-fixed">
  <thead>
    <tr>
      <?php foreach ($daysOfWeek as $day): ?>
        <th style="background-color: antiquewhite;"><?= htmlspecialchars($day) ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
</table>

<!-- Calendar body -->
<div class="row table-box">
  <?php foreach ($weeks as $weekNumber => $weekDays): ?>
    <div class="calendar-row" id="week-<?= $weekNumber ?>">
      <table class="calendar-table table-fixed w-100">
        <tr>
          <?php foreach ($weekDays as $fullDate): ?>
            <td id="cell-<?= $fullDate ?>" onclick="promptForEvent('<?= $fullDate ?>')">
              <div class="day-number"><?= date('j', strtotime($fullDate)) ?></div>
              <div class="event-container" id="events-<?= $fullDate ?>">
                <!-- Insert single-day event strips here via PHP or JS -->
              </div>
            </td>
          <?php endforeach; ?>
        </tr>
      </table>

      <!-- Multi-day event overlay for this week -->
      <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div>
    </div>
  <?php endforeach; ?>
</div>



<?php foreach ($weeks as $weekNumber => $weekDays): ?>
  <div class="calendar-row" id="week-<?= $weekNumber ?>">
    <table class="calendar-table table-fixed w-100">
      <tr>
        <?php foreach ($weekDays as $fullDate): ?>
          <td id="cell-<?= $fullDate ?>" onclick="promptForEvent('<?= $fullDate ?>')">
            <div class='day-number'><?= date('j', strtotime($fullDate)) ?></div>
            <div class='event-container' id='events-<?= $fullDate ?>'>
              <?php
              if (isset($eventsByDate[$fullDate])) {
                  foreach ($eventsByDate[$fullDate] as $eventId => $event) {
                      echo "<div class='event-strip' id='event-$eventId'>";
                      echo "<span class='event-text' id='title-$eventId'>" . htmlspecialchars($event) . "</span>";
                      echo "<span class='event-actions'>
                              <button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent($eventId)'>
                                <i class='fa fa-pencil'></i>
                              </button>
                              <button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent($eventId)'>
                                <i class='fa fa-remove'></i>
                              </button>
                            </span>";
                      echo "</div>";
                  }
              }
              ?>
            </div>
          </td>
        <?php endforeach; ?>
      </tr>
    </table>
    <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div>
  </div>
<?php endforeach; ?>



<?php
foreach ($weeks as $weekNumber => $weekDays): ?>
  <div class="calendar-row" id="week-<?= $weekNumber ?>">
    <table class="calendar-table table-fixed w-100">
      <tr>
        <?php foreach ($weekDays as $fullDate):
          $day = date('j', strtotime($fullDate)); // Extract day number
          $dayOfWeek = date('w', strtotime($fullDate)); // 0=Sunday, 6=Saturday
          $isToday = ($fullDate === date('Y-m-d'));

          $class = $isToday ? "today" : "";

          // Apply your custom styling
          if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
            echo "<td id='cell-$fullDate' class='$class' style='$style' onclick=\"promptForEvent('$fullDate')\">";
          } else {
            echo "<td id='cell-$fullDate' class='$class' onclick=\"promptForEvent('$fullDate')\">";
          }

          echo "<div class='day-number'>$day</div>";
          echo "<div class='event-container' id='events-$fullDate'></div>";
          echo "</td>";
        endforeach; ?>
      </tr>
    </table>
    <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div>
  </div>
<?php endforeach; ?>



<div class="row table-box">
  <table class="table table-bordered w-100 table-fixed">
    <thead>
      <tr>
        <?php foreach ($daysOfWeek as $day): ?>
          <th style="background-color: antiquewhite;"><?= htmlspecialchars($day) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($weeks as $weekNumber => $weekDays): ?>
        <tr>
          <?php foreach ($weekDays as $fullDate): ?>
            <?php
              $timestamp = strtotime($fullDate);
              $day = date('j', $timestamp);
              $monthOfCell = date('n', $timestamp);
              $yearOfCell = date('Y', $timestamp);
              
              // is today check
              $isToday = (date('Y-m-d') === date('Y-m-d', $timestamp));
              $class = $isToday ? "class='today'" : "";

              // weekend check
              $dayOfWeek = date('w', $timestamp);
              
              if ($monthOfCell != $month) {
                  // faded style for prev/next month days
                  $style = "color: #ccc;";
                  $clickable = ""; // optionally disable click or keep it
              } else {
                  $style = "";
                  $clickable = "onclick=\"promptForEvent('$fullDate')\"";
              }

              if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                  // weekend styling
                  if ($isToday) {
                      $style = "background-color: #ffeb3b; color: #000;";
                  } elseif ($style === "") {
                      $style = "background-color: #f2f2f2; color: #ff0000;";
                  }
              }
            ?>
            <td id="cell-<?= $fullDate ?>" style="<?= $style ?>" <?= $class ?> <?= $clickable ?>>
              <div class="day-number"><?= $day ?></div>
              <div class="event-container" id="events-<?= $fullDate ?>">
                <?php
                  if (isset($eventsByDate[$fullDate])) {
                    foreach ($eventsByDate[$fullDate] as $eventId => $event) {
                      ?>
                      <div class="event-strip" id="event-<?= $eventId ?>">
                        <span class="event-text" id="title-<?= $eventId ?>"><?= htmlspecialchars($event) ?></span>
                        <span class="event-actions">
                          <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(<?= $eventId ?>)">
                            <i class="fa fa-pencil"></i>
                          </button>
                          <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(<?= $eventId ?>)">
                            <i class="fa fa-remove"></i>
                          </button>
                        </span>
                      </div>
                      <?php
                    }
                  }
                ?>
              </div>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"></div>
</div>




<!-- Days header table -->
<table class="table-fixed w-100">
  <thead>
    <tr>
      <?php foreach ($daysOfWeek as $day): ?>
        <th><?= htmlspecialchars($day) ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
</table>

<!-- Weeks container -->
<div class="calendar-container">
  <?php foreach ($weeks as $weekNumber => $weekDays): ?>
    <div class="calendar-row" id="week-<?= $weekNumber ?>" style="position: relative;">
      <!-- Week days table -->
      <table class="calendar-table table-fixed w-100">
        <tbody>
          <tr>
            <?php foreach ($weekDays as $fullDate):
              $day = date('j', strtotime($fullDate));
              $isToday = ($fullDate === date('Y-m-d'));
              $dayOfWeek = date('w', strtotime($fullDate));
              $style = "";
              if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                  $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
              }
              $class = $isToday ? "today" : "";
            ?>
              <td id="cell-<?= $fullDate ?>" style="<?= $style ?>" class="<?= $class ?>" onclick="promptForEvent('<?= $fullDate ?>')">
                <div class="day-number"><?= $day ?></div>
                <div class="event-container" id="events-<?= $fullDate ?>">
                  <?php
                    if (isset($eventsByDate[$fullDate])) {
                      foreach ($eventsByDate[$fullDate] as $eventId => $event) {
                        ?>
                        <div class="event-strip" id="event-<?= $eventId ?>">
                          <span class="event-text"><?= htmlspecialchars($event) ?></span>
                          <span class="event-actions">
                            <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(<?= $eventId ?>)"><i class="fa fa-pencil"></i></button>
                            <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(<?= $eventId ?>)"><i class="fa fa-remove"></i></button>
                          </span>
                        </div>
                        <?php
                      }
                    }
                  ?>
                </div>
              </td>
            <?php endforeach; ?>
          </tr>
        </tbody>
      </table>

      <!-- Event overlay container for multi-day events -->
      <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>" style="position: absolute; top: 0; left: 0; width: 100%; pointer-events: none;">
        <!-- JS will add multi-day event strips here, absolutely positioned -->
      </div>
    </div>
  <?php endforeach; ?>
</div>



<?php
$weeks = getWeeks($year, $month);

foreach ($weeks as $weekNumber => $weekDays) {
    echo "<tr>";
    foreach ($weekDays as $fullDate) {
        $day = (int)date('j', strtotime($fullDate));
        $monthOfCell = (int)date('n', strtotime($fullDate));
        $yearOfCell = (int)date('Y', strtotime($fullDate));

        // Determine styling based on if day is in current month
        $isCurrentMonth = ($monthOfCell === $month);

        // Determine if today
        $isToday = ($day == date('j') && $monthOfCell == date('n') && $yearOfCell == date('Y'));

        // Your weekend check & styles here...
        $dayOfWeek = date('w', strtotime($fullDate));

        $style = '';
        if (!$isCurrentMonth) {
            $style = "color: #ccc;";  // faded style for prev/next month days
        } elseif ($dayOfWeek == 0 || $dayOfWeek == 6) {
            $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
        } elseif ($isToday) {
            $style = "background-color: yellow;";  // or your today style
        }

        echo "<td id='cell-$fullDate' style='$style' onclick=\"promptForEvent('$fullDate')\">";
        echo "<div class='day-number'>$day</div>";
        echo "<div class='event-container' id='events-$fullDate'>";
        // your event rendering here...
        echo "</div></td>";
    }
    echo "</tr>";
}
?>



<tr>
  <!-- START: Loop through each date in the current week -->
  <?php foreach ($weekDays as $fullDate): ?>

    <?php
      // 1. Extract just the day (1–31)
      $day = date('j', strtotime($fullDate));

      // 2. Check if this day is today
      $isToday = ($fullDate === date('Y-m-d'));

      // 3. Get the day of the week (0 = Sunday, 6 = Saturday)
      $dayOfWeek = date('w', strtotime($fullDate));

      // 4. Prepare inline style string
      $style = "";

      // 5. Check if this date belongs to the current month
      $isCurrentMonth = (int)date('n', strtotime($fullDate)) === $month;

      // 6. If it's NOT from the current month, fade it (gray)
      if (!$isCurrentMonth) {
        $style .= "color: #ccc;";
      }

      // 7. If it's a weekend (Sunday or Saturday), apply different style
      elseif ($dayOfWeek == 0 || $dayOfWeek == 6) {
        $style = $isToday
          ? "background-color: #ffeb3b; color: #000;"  // Highlight today specially
          : "background-color: #f2f2f2; color: #ff0000;"; // Weekend style
      }

      // 8. Add "today" class for today’s cell
      $class = $isToday ? "today" : "";
    ?>

    <!-- Render one cell for the date -->
    <td id="cell-<?= $fullDate ?>" data-date="<?= $fullDate ?>" style="<?= $style ?>" class="<?= $class ?>">
      
      <!-- Show the day number -->
      <div class="day-number"><?= $day ?></div>

      <!-- Container for events on this date -->
      <div class="event-container" id="events-<?= $fullDate ?>">

        <!-- If there are events on this day, loop through and show them -->
        <?php if (isset($eventsByDate[$fullDate])): ?>
          <?php foreach ($eventsByDate[$fullDate] as $eventId => $event): ?>
            <div class="event-strip" id="event-<?= $eventId ?>">
              <span class="event-text" id="title-<?= $eventId ?>">
                <?= htmlspecialchars($event['event_title']) ?>
              </span>
              <span class="event-actions">
                <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(<?= $eventId ?>)">
                  <i class="fa fa-pencil"></i>
                </button>
                <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(<?= $eventId ?>)">
                  <i class="fa fa-remove"></i>
                </button>
              </span>
            </div>
          <?php endforeach; ?> <!-- END inner foreach (events) -->
        <?php endif; ?> <!-- END if events exist -->

      </div> <!-- END event-container -->

    </td> <!-- END one <td> for this date -->

  <?php endforeach; ?> <!-- END outer foreach (weekDays) -->
</tr> <!-- END <tr> row for this week -->


<!-- This is the overlay container for this specific week (by week number) -->
<div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>"
     style="position: absolute; top: 0; left: 0; width: 100%; pointer-events: none;">

  <?php
  // Loop through all dates and their events
  foreach ($eventsByDate as $date => $events) {
    foreach ($events as $eventId => $event) {

      // 1. Get event start and end date
      $startDate = $event['startdate'];
      $endDate = $event['enddate'] ?? $startDate; // If no end date, treat it as a single-day event

      // 2. Convert dates to timestamps for comparison
      $startTimestamp = strtotime($startDate);
      $endTimestamp = strtotime($endDate);

      // 3. Get the start and end of this week (first and last date in the $weekDays array)
      $weekStart = strtotime($weekDays[0]);
      $weekEnd = strtotime($weekDays[6]);

      // 4. Skip if this event does NOT overlap with this week at all
      if ($endTimestamp < $weekStart || $startTimestamp > $weekEnd) {
        continue;
      }

      // 5. Figure out which part of the event falls inside this week
      $eventStripStart = max($startTimestamp, $weekStart);
      $eventStripEnd = min($endTimestamp, $weekEnd);

      // 6. Calculate how many days from the start of this week the event starts
      $daysFromWeekStart = ($eventStripStart - $weekStart) / 86400; // 86400 = seconds per day

      // 7. How many days does this visible part of event last in this week
      $eventDurationDays = (($eventStripEnd - $eventStripStart) / 86400) + 1;

      // 8. UI logic: one day = ~136.78px, calculate width and position
      $cellWidth = 136.78;
      $stripWidth = $eventDurationDays * $cellWidth;
      $leftPos = $daysFromWeekStart * $cellWidth;

      // 9. Draw the multi-day strip absolutely inside the overlay container
      echo "<div class='event-strip' style='position: absolute; top: 0; left: {$leftPos}px; width: {$stripWidth}px;' title='" . htmlspecialchars($event['event_title']) . "'>";

        // Show event title inside the strip
        echo "<span class='event-text'>" . htmlspecialchars($event['event_title']) . "</span>";

        // Show action buttons (edit, delete)
        echo "<span class='event-actions'>";
          echo "<button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent({$eventId})'><i class='fa fa-pencil'></i></button>";
          echo "<button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent({$eventId})'><i class='fa fa-remove'></i></button>";
        echo "</span>";

      echo "</div>";
    }
  }
  ?>
</div> <!-- END of event overlay container -->


<script>
let isSelecting = false; // Tracks if user is dragging to select
let selectedCells = []; // Stores all selected date cells

// Loop all calendar date cells
document.querySelectorAll('td[data-date]').forEach(cell => {

    // When mouse is pressed on a cell
    cell.addEventListener('mousedown', (e) => {
        e.preventDefault(); // Stop text selection
        isSelecting = true;
        selectedCells = [cell]; // Begin new selection
        cell.classList.add('selected'); // Highlight cell
    });

    // While dragging over other cells
    cell.addEventListener('mouseover', (e) => {
        if (isSelecting && !selectedCells.includes(cell)) {
            selectedCells.push(cell);
            cell.classList.add('selected');
        }
    });
});

// When mouse button is released
document.addEventListener('mouseup', () => {
    if (!isSelecting || selectedCells.length === 0) return;
    isSelecting = false;

    const startDate = selectedCells[0].dataset.date;
    const endDate = selectedCells[selectedCells.length - 1].dataset.date;

    const title = prompt(`Enter event title for ${startDate} to ${endDate}:`);
    if (!title) {
        selectedCells.forEach(cell => cell.classList.remove('selected'));
        return;
    }

    // Prepare form data
    const formData = new FormData();
    formData.append('event_title', title);
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);

    // Send to backend
    fetch('save_event.php', {
        method: 'POST',
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || "Failed to save event.");
        } else {
            const cellWidth = selectedCells[0].offsetWidth;
            const totalDays = selectedCells.length;
            const stripWidth = totalDays * cellWidth;

            const eventHTML = `
                <div class="event-strip" style="width: ${stripWidth}px;">
                    <span class="event-text">${title}</span>
                    <span class="event-actions">
                        <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${data.event_id})">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${data.event_id})">
                            <i class="fa fa-remove"></i>
                        </button>
                    </span>
                </div>
            `;

            // Only insert into the first cell (visual trick)
            const firstCell = selectedCells[0];
            const container = firstCell.querySelector('.event-container');
            if (container) {
                container.insertAdjacentHTML('beforeend', eventHTML);
            }
        }
    })
    .catch(err => {
        console.error(err);
        alert("Error saving event.");
    })
    .finally(() => {
        selectedCells.forEach(cell => cell.classList.remove('selected'));
        selectedCells = [];
    });
});

</script>

cell.addEventListener('mousedown', (e) => {
    e.preventDefault(); // Prevent text selection
    isSelecting = true;
    selectedCells = [cell]; // Start new selection
    cell.classList.add('selected'); // Add highlight
});





<!-- my logic of php for dates span i put it here becuse i used js like code for php -->
 <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>" style="position: absolute; top: 30px; left: 0; width: 100%; pointer-events: none;">
                            <!-- JS will add multi-day event strips here, absolutely positioned -->
                            <?php
                            // For multi-day events display in this week:
                            foreach ($eventsByDate as $date => $events) {
                                foreach ($events as $eventId => $event) {
                                    // Check if event has startdate and enddate (for multi-day)
                                    $startDate = $event['startdate'];
                                    $endDate = $event['enddate'] ?? $startDate; // If no enddate, single day event

                                    // Convert to timestamps for comparison
                                    $startTimestamp = strtotime($startDate);
                                    $endTimestamp = strtotime($endDate);

                                    // Find the week range dates
                                    $weekStart = strtotime($weekDays[0]);
                                    $weekEnd = strtotime($weekDays[6]);

                                    // Skip events outside this week
                                    if ($endTimestamp < $weekStart || $startTimestamp > $weekEnd) {
                                        continue;
                                    }

                                    // Calculate the event strip start and end within this week
                                    $eventStripStart = max($startTimestamp, $weekStart);
                                    $eventStripEnd = min($endTimestamp, $weekEnd);

                                    // Calculate positions (number of days offset from week start)
                                    $daysFromWeekStart = ($eventStripStart - $weekStart) / 86400; // seconds per day
                                    $eventDurationDays = (($eventStripEnd - $eventStripStart) / 86400) + 1;

                                    // Calculate width for the event strip (using your cell width approx 136.78px)
                                    $cellWidth = 185.5;
                                    $stripWidth = $eventDurationDays * $cellWidth;

                                    // Left offset in pixels
                                    $leftPos = $daysFromWeekStart * $cellWidth;

                                    // Output multi-day event strip div with inline style
                                    echo "<div class='event-strip' style='position: absolute; top: 0px; left: {$leftPos}px; width: {$stripWidth}px;' title='" . htmlspecialchars($event['event_title']) . "'>";
                                    echo "<span class='event-text'>" . htmlspecialchars($event['event_title']) . "</span>";
                                    echo "<span class='event-actions'>";
                                    echo "<button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent({$eventId})'><i class='fa fa-pencil'></i></button>";
                                    echo "<button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent({$eventId})'><i class='fa fa-remove'></i></button>";
                                    echo "</span>";
                                    echo "</div>";
                                }
                            }
                            ?>
                          </div>


<!-- on date 26-5-2025 // solving calender multi day span overlapping issue --> 
<div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>" style="position: absolute; top: 30px; left: 0; width: 100%; pointer-events: none;">
<?php
$seenEventIds = []; // Prevent duplicate render

$lanes = []; // NEW: lanes array to track occupied time slots

foreach ($eventsByDate as $events) {
    foreach ($events as $eventId => $event) {

        // Skip already seen
        if (in_array($eventId, $seenEventIds)) continue;
        $seenEventIds[] = $eventId;

        $startDate = $event['startdate'];
        $endDate = $event['enddate'] ?? $startDate;

        $startTS = strtotime(date('Y-m-d', strtotime($startDate)));
        $endTS = strtotime(date('Y-m-d', strtotime($endDate)));
        $weekStart = strtotime(date('Y-m-d', strtotime($weekDays[0])));
        $weekEnd = strtotime(date('Y-m-d', strtotime($weekDays[6])));

        if ($endTS < $weekStart || $startTS > $weekEnd) continue;

        $stripStart = max($startTS, $weekStart);
        $stripEnd = min($endTS, $weekEnd);

        $daysFromStart = ($stripStart - $weekStart) / 86400;
        $duration = (($stripEnd - $stripStart) / 86400) + 1;

        $cellWidth = 185.5;
        $stripWidth = $duration * $cellWidth;
        $left = $daysFromStart * $cellWidth;

        // ---------------------------
        // ✅ Lane Calculation Logic
        // ---------------------------
        $laneIndex = 0;
        while (true) {
            if (!isset($lanes[$laneIndex])) {
                $lanes[$laneIndex] = [];
                break;
            }

            // Check if this lane has conflicts
            $conflict = false;
            foreach ($lanes[$laneIndex] as [$existingStart, $existingEnd]) {
                if (!($stripEnd < $existingStart || $stripStart > $existingEnd)) {
                    $conflict = true;
                    break;
                }
            }

            if (!$conflict) break;
            $laneIndex++;
        }

        // Register this event in its lane
        $lanes[$laneIndex][] = [$stripStart, $stripEnd];

        $top = $laneIndex * 28; // 28px per lane (adjust as needed)

        echo "<!-- event: {$event['event_title']} | start=$startDate, end=$endDate, left=$left, width=$stripWidth, top=$top, lane=$laneIndex -->";

        echo "<div class='event-strip' style='position: absolute; top: {$top}px; left: {$left}px; width: {$stripWidth}px;' title='" . htmlspecialchars($event['event_title']) . "'>";
        echo "<span class='event-text'>" . htmlspecialchars($event['event_title']) . "</span>";
        echo "<span class='event-actions'>";
        echo "<button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent($eventId)'><i class='fa fa-pencil'></i></button>";
        echo "<button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent($eventId)'><i class='fa fa-remove'></i></button>";
        echo "</span></div>";
    }
}
?>
</div>



<div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>" style="position: absolute; top: 30px; left: 0; width: 100%; pointer-events: none;">
<?php
$seenEventIds = []; // Prevent duplicate render
$lanes = []; // NEW: For vertical stacking

foreach ($eventsByDate as $events) {
    foreach ($events as $eventId => $event) {

        echo "<!-- DEBUG: Event ID: $eventId, Title: {$event['event_title']}, Start: {$event['startdate']}, End: {$event['enddate']} -->";

        if (in_array($eventId, $seenEventIds)) continue;
        $seenEventIds[] = $eventId;

        $startDate = $event['startdate'];
        $endDate = $event['enddate'] ?? $startDate;

        $startTimestamp = strtotime(date('Y-m-d', strtotime($startDate)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($endDate)));
        $weekStart = strtotime(date('Y-m-d', strtotime($weekDays[0])));
        $weekEnd = strtotime(date('Y-m-d', strtotime($weekDays[6])));

        if ($endTimestamp < $weekStart || $startTimestamp > $weekEnd) {
            continue;
        }

        $eventStripStart = max($startTimestamp, $weekStart);
        $eventStripEnd = min($endTimestamp, $weekEnd);

        $daysFromWeekStart = ($eventStripStart - $weekStart) / 86400;
        $eventDurationDays = (($eventStripEnd - $eventStripStart) / 86400) + 1;

        $cellWidth = 185.5;
        $stripWidth = $eventDurationDays * $cellWidth;
        $leftPos = $daysFromWeekStart * $cellWidth;

        // 🧠 Lane logic starts here
        $laneIndex = 0;
        while (true) {
            if (!isset($lanes[$laneIndex])) {
                $lanes[$laneIndex] = [];
                break;
            }

            $conflict = false;
            foreach ($lanes[$laneIndex] as [$existingStart, $existingEnd]) {
                if (!($eventStripEnd < $existingStart || $eventStripStart > $existingEnd)) {
                    $conflict = true;
                    break;
                }
            }

            if (!$conflict) break;
            $laneIndex++;
        }

        $lanes[$laneIndex][] = [$eventStripStart, $eventStripEnd];
        $topOffset = $laneIndex * 28; // vertical space between events

        // Debug info
        echo "<!-- event: {$event['event_title']} | start={$startDate}, end={$endDate}, left={$leftPos}, width={$stripWidth}, days={$eventDurationDays}, top={$topOffset} -->";

        echo "<div class='event-strip' style='position: absolute; top: {$topOffset}px; left: {$leftPos}px; width: {$stripWidth}px;' title='" . htmlspecialchars($event['event_title']) . "'>";
        echo "<span class='event-text'>" . htmlspecialchars($event['event_title']) . "</span>";
        echo "<span class='event-actions'>";
        echo "<button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent({$eventId})'><i class='fa fa-pencil'></i></button>";
        echo "<button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent({$eventId})'><i class='fa fa-remove'></i></button>";
        echo "</span></div>";
    }
}
?>
</div>


<script>
const eventDiv = document.createElement('div');
eventDiv.className = 'event-strip';

// 🧠 LANE STACKING LOGIC — prevent overlap
//We collect all .event-strip elements already added to the current week’s overlay.

let existingStrips = overlay.querySelectorAll('.event-strip');
//We’ll try placing the new event in lane 0, and if there's a conflict, we’ll try lane 1, then lane 2, and so on.

let laneIndex = 0;

// This loop runs until we find a lane with no overlapping event strips
while (true) {
  let conflict = false; //Initially, we assume no conflict (conflict = false).
  for (let strip of existingStrips) {
  // We calculate its start (left) and end (right) positions.
  //Also get the top value to know which lane it’s in.
  //If it's not in the lane we're trying to use (e.g., lane 0, then lane 1...), skip it.
    let stripLeft = parseFloat(strip.style.left);
    let stripWidth = parseFloat(strip.style.width);
    let stripRight = stripLeft + stripWidth;
    let newLeft = left;
    let newRight = left + width;

    let stripTop = parseFloat(strip.style.top);
    if (stripTop !== laneIndex * 28) continue; // Only check events in this lane

    // Check for horizontal overlap
    if (!(newRight <= stripLeft || newLeft >= stripRight)) {
      conflict = true;
      break;
    }
  }

  if (!conflict) break;
  laneIndex++;
}

const topOffset = laneIndex * 28;

eventDiv.style.cssText = `position:absolute;top:${topOffset}px;left:${left}px;width:${width}px;`;
eventDiv.title = eventTitle;
eventDiv.innerHTML = `
  <span class="event-text">${eventTitle}</span>
  <span class="event-actions">
    <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${data.event_id})"><i class="fa fa-pencil"></i></button>
    <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${data.event_id})"><i class="fa fa-remove"></i></button>
  </span>
`;
overlay.appendChild(eventDiv);


</script>


<?php
// edit_event.php
header('Content-Type: application/json');
require 'db_connection.php';

$eventId = $_POST['event_id'] ?? '';
$newTitle = $_POST['new_title'] ?? '';

if ($eventId && $newTitle) {
    $stmt = $conn->prepare("UPDATE events SET event_title = ?, editdate = NOW() WHERE id = ?");
    if ($stmt->execute([$newTitle, $eventId])) {
        echo json_encode(['success' => true, 'message' => 'Event updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}


<?php
header('Content-Type: application/json');
require 'db_connection.php';

$eventId = $_POST['event_id'] ?? '';
$newTitle = $_POST['new_title'] ?? '';

// Basic validation
if ($eventId && $newTitle) {
    // Escape input to prevent SQL injection (not as secure as prepared statements)
    $eventId = intval($eventId);
    $safeTitle = mysqli_real_escape_string($conn, $newTitle);

    $sql = "UPDATE events SET event_title = '$safeTitle', editdate = NOW() WHERE id = $eventId";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Event updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB update failed: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
?>

<script>
fetch('edit_event.php', {
  method: 'POST',
  body: formData,
})
.then(res => {
  if (!res.ok) throw new Error("Network error");
  return res.json();
})
.then(data => {
  if (data.success) {
    titleElement.textContent = newTitle;
    console.log("Updated successfully:", data.message);
  } else {
    throw new Error(data.message || "Update failed");
  }
})
.catch(err => {
  alert("Error in Updating event");
  console.error(err);
});

</script>
<?php
header('Content-Type: application/json'); // Add this at the top

// Your existing code...
$eventId = $_POST['event_id'] ?? '';
$eventId = (int)$eventId;

$newTitle = $conn->real_escape_string($_POST['new_title'] ?? '');

if (!$eventId || !$newTitle) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$safeEmail = $conn->real_escape_string($userEmail);

$updateQuery = "UPDATE events SET event_title = '$newTitle', editdate = NOW() 
                WHERE id = $eventId AND user_email = '$safeEmail'";

if ($conn->query($updateQuery) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Event updated']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>



.then(res => res.json())
.then(data => {
  if (data.success) {
    titleElement.textContent = newTitle;
    console.log("Updated:", data.message);
  } else {
    throw new Error(data.message || "Update failed");
  }
})
.catch(err => {
  alert("Error in Updating event");
  console.error(err);
});


.then(data => {
    if (data.success) {
        const eventElement = document.getElementById(`event-${eventId}`);
        if (eventElement) {
            eventElement.remove();
        }
    } else {
        alert(data.message);
    }
})



.then(data =>{
        // alert("Event Deleted successfully!");
        
        // Remove the event from the DOM
        const eventElement = document.getElementById(`event-${eventId}`);
        if (eventElement) {
            eventElement.remove(); // This removes the event block from the page
        } 
    })
    .catch(err=>{
        alert("Error deleting event.Please try again!.");
        console.error(err);

    });


<script>

    function deleteEvent(eventId) {
  if (!confirm("Do you want to delete event?")) return;

  const formData = new FormData();
  formData.append('event_id', eventId);

  fetch('delete_event.php', {
    method: 'POST',
    body: formData,
  })
  .then(res => res.json())
  .then(data => {
    const eventElement = document.getElementById(`event-${eventId}`);
    if (eventElement) {
      const overlay = eventElement.parentElement; // get the .event-overlay-container
      eventElement.remove(); // Remove deleted event

      // ✅ Recalculate stacking for remaining strips
      const strips = Array.from(overlay.querySelectorAll('.event-strip'));

      // Sort by left position to handle left-to-right stacking
      strips.sort((a, b) => parseFloat(a.style.left) - parseFloat(b.style.left));

      let lanes = []; // each lane is an array of strips

      for (let strip of strips) {
        const left = parseFloat(strip.style.left);
        const width = parseFloat(strip.style.width);
        const right = left + width;

        let placed = false;
        for (let i = 0; i < lanes.length; i++) {
          const lane = lanes[i];
          const conflict = lane.some(existing => {
            const eLeft = parseFloat(existing.style.left);
            const eRight = eLeft + parseFloat(existing.style.width);
            return !(right <= eLeft || left >= eRight); // overlap
          });
          if (!conflict) {
            lane.push(strip);
            strip.style.top = `${i * 28}px`; // 28px per lane
            placed = true;
            break;
          }
        }

        if (!placed) {
          // New lane
          lanes.push([strip]);
          strip.style.top = `${(lanes.length - 1) * 28}px`;
        }
      }
    }
  })
  .catch(err => {
    alert("Error deleting event. Please try again.");
    console.error(err);
  });
}

</script>



<!--  -->
<script>
eventDiv.setAttribute('draggable', 'true');

eventDiv.addEventListener('dragstart', (e) => {
  e.dataTransfer.setData('text/plain', JSON.stringify({
    eventId: data.event_id,
    duration: duration // already calculated from start to end
  }));
});


</script>
<script>
<!-- make cells droapele -->
cell.addEventListener('dragover', (e) => e.preventDefault());

cell.addEventListener('drop', (e) => {
  e.preventDefault();
  const data = JSON.parse(e.dataTransfer.getData('text/plain'));
  const droppedDate = e.currentTarget.dataset.date;

  // Send AJAX request to update start and end date
  const newStart = new Date(droppedDate);
  const newEnd = new Date(newStart);
  newEnd.setDate(newStart.getDate() + data.duration - 1); // maintain duration

  fetch('move_event.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({
      event_id: data.eventId,
      new_start: droppedDate,
      new_end: newEnd.toISOString().slice(0, 10)
    })
  })
  .then(res => res.json())
  .then(response => {
    if (response.success) {
      // Reload events or re-render dynamically
      location.reload(); // or use DOM update like before
    } else {
      alert('Move failed: ' + response.message);
    }
  });
});
</script>

<!-- backend move event.php -->
<?php
session_start();
header('Content-Type: application/json');
require 'db.php'; // your DB connection

$userEmail = $_SESSION['user_email'] ?? '';
$eventId = (int)($_POST['event_id'] ?? 0);
$newStart = $_POST['new_start'] ?? '';
$newEnd = $_POST['new_end'] ?? '';

if (!$eventId || !$newStart || !$newEnd) {
    echo json_encode(['success' => false, 'message' => 'Missing input']);
    exit;
}

$safeEmail = $conn->real_escape_string($userEmail);
$newStart = $conn->real_escape_string($newStart);
$newEnd = $conn->real_escape_string($newEnd);

$query = "UPDATE events SET startdate='$newStart', enddate='$newEnd', editdate=NOW()
          WHERE id=$eventId AND user_email='$safeEmail'";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>


1. Make Event Div Draggable (in JS where you create .event-strip)
Inside your dynamic JS code where you create eventDiv:

js
Copy
Edit
<script>
eventDiv.id = `event-${data.event_id}`;
eventDiv.setAttribute('draggable', 'true');

eventDiv.addEventListener('dragstart', (e) => {
  e.dataTransfer.setData('text/plain', JSON.stringify({
    eventId: data.event_id,
    duration: duration // already calculated
  }));
});

</script>

If you're rendering this strip via PHP (on reload), also output draggable="true" and data-duration="X" like:

php
Copy
Edit
echo "<div class='event-strip' id='event-{$eventId}' draggable='true' data-duration='{$eventDurationDays}' style='...'>";


<!-- make td cells droaapele -->
<script>
document.querySelectorAll('td[data-date]').forEach(cell => {
  cell.addEventListener('dragover', (e) => e.preventDefault());

  cell.addEventListener('drop', (e) => {
    e.preventDefault();
    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
    const droppedDate = e.currentTarget.dataset.date;

    const newStart = new Date(droppedDate);
    const newEnd = new Date(newStart);
    newEnd.setDate(newStart.getDate() + data.duration - 1);

    fetch('move_event.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        event_id: data.eventId,
        new_start: droppedDate,
        new_end: newEnd.toISOString().slice(0, 10)
      })
    })
    .then(res => res.json())
    .then(response => {
      if (response.success) {
        location.reload(); // or re-render dynamically
      } else {
        alert('Failed to move event: ' + response.message);
      }
    })
    .catch(err => {
      console.error('Move failed', err);
      alert('Drag/drop error.');
    });
  });
});

</script>

<!-- moveevent.php -->
<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$userEmail = $_SESSION['user_email'] ?? '';
$eventId = (int)($_POST['event_id'] ?? 0);
$newStart = $_POST['new_start'] ?? '';
$newEnd = $_POST['new_end'] ?? '';

if (!$eventId || !$newStart || !$newEnd) {
    echo json_encode(['success' => false, 'message' => 'Missing input']);
    exit;
}

$safeEmail = $conn->real_escape_string($userEmail);
$newStart = $conn->real_escape_string($newStart);
$newEnd = $conn->real_escape_string($newEnd);

$query = "UPDATE events 
          SET startdate='$newStart', enddate='$newEnd', editdate=NOW()
          WHERE id=$eventId AND user_email='$safeEmail'";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>

<!-- lane adjusting logic -->

let existingStrips = overlay.querySelectorAll('.event-strip');
let laneIndex = 0;
while (true) {
  let conflict = false;
  for (let strip of existingStrips) {
    let stripLeft = parseFloat(strip.style.left);
    let stripWidth = parseFloat(strip.style.width);
    let stripRight = stripLeft + stripWidth;
    let newRight = left + width;
    let newLeft = left;

    let stripTop = parseFloat(strip.style.top);
    if (stripTop !== laneIndex * 28) continue;

    if (!(newRight <= stripLeft || newLeft >= stripRight)) {
      conflict = true;
      break;
    }
  }
  if (!conflict) break;
  laneIndex++;
}

const topOffset = laneIndex * 28;
eventDiv.style.cssText = `position:absolute;top:${topOffset}px;left:${left}px;width:${width}px;`;
<script>
<!-- where to add the stacking logic -->
cell.addEventListener('drop', function(e) {
  const eventId = e.dataTransfer.getData('text/plain');
  const eventDiv = document.getElementById(`event-${eventId}`);
  const originalWidth = parseFloat(eventDiv.style.width); // already set during drag

  // 1. Get overlay container of the current week
  const week = cell.closest('.calendar-row');
  const overlay = week.querySelector('.event-overlay-container');

  // 2. Compute new start date and left offset
  const newStartDate = cell.dataset.date;
  const weekStart = new Date(week.querySelector('td[data-date]').dataset.date);
  const newStart = new Date(newStartDate);
  const offsetDays = (newStart - weekStart) / (1000 * 60 * 60 * 24);
  const left = offsetDays * 185.5;
  const width = originalWidth;

<!-- then update lane detection logic like this -->
  let existingStrips = overlay.querySelectorAll('.event-strip');
  let laneIndex = 0;

  while (true) {
    let conflict = false;
    for (let strip of existingStrips) {
      let stripLeft = parseFloat(strip.style.left);
      let stripWidth = parseFloat(strip.style.width);
      let stripRight = stripLeft + stripWidth;
      let newRight = left + width;
      let newLeft = left;

      let stripTop = parseFloat(strip.style.top);
      if (stripTop !== laneIndex * 28) continue;

      if (!(newRight <= stripLeft || newLeft >= stripRight)) {
        conflict = true;
        break;
      }
    }
    if (!conflict) break;
    laneIndex++;
  }

  const topOffset = laneIndex * 28;


// full drop example
cell.addEventListener('drop', function(e) {
  const eventId = e.dataTransfer.getData('text/plain');
  const eventDiv = document.getElementById(`event-${eventId}`);
  const originalWidth = parseFloat(eventDiv.style.width);

  const week = cell.closest('.calendar-row');
  const overlay = week.querySelector('.event-overlay-container');

  const newStartDate = cell.dataset.date;
  const weekStart = new Date(week.querySelector('td[data-date]').dataset.date);
  const newStart = new Date(newStartDate);
  const offsetDays = (newStart - weekStart) / (1000 * 60 * 60 * 24);
  const left = offsetDays * 185.5;
  const width = originalWidth;

  // 📦 LANE DETECTION
  let existingStrips = overlay.querySelectorAll('.event-strip');
  let laneIndex = 0;

  while (true) {
    let conflict = false;
    for (let strip of existingStrips) {
      let stripLeft = parseFloat(strip.style.left);
      let stripWidth = parseFloat(strip.style.width);
      let stripRight = stripLeft + stripWidth;
      let newRight = left + width;
      let newLeft = left;

      let stripTop = parseFloat(strip.style.top);
      if (stripTop !== laneIndex * 28) continue;

      if (!(newRight <= stripLeft || newLeft >= stripRight)) {
        conflict = true;
        break;
      }
    }
    if (!conflict) break;
    laneIndex++;
  }

  const topOffset = laneIndex * 28;

  // 🟢 Finally, apply style
  eventDiv.style.cssText = `position:absolute;top:${topOffset}px;left:${left}px;width:${width}px;`;
});


// fix why the cell is selected cell logic stops when i add the other drag and drop logic 


document.querySelectorAll('td[data-date]').forEach(cell => {
  cell.addEventListener('mousedown', (e) => {
    // 🛑 Prevent starting selection when clicking on an event
    // so that it  stops selection only when it when event strip is selected otherwise skip it
    if (e.target.closest('.event-strip')) return;

    e.preventDefault();
    clearSelection();
    isSelecting = true;
    startCell = cell;
    cell.classList.add('selected');
    selectedCells.add(cell);
  });
});


document.querySelectorAll('td[data-date]').forEach(cell => {
  cell.addEventListener('dragover', e => e.preventDefault());

  cell.addEventListener('drop', e => {
    e.preventDefault();
    const droppedDate = cell.dataset.date;
    const dragged = JSON.parse(e.dataTransfer.getData('text/plain'));

    const newStart = new Date(droppedDate);
    const newEnd = new Date(newStart);
    newEnd.setDate(newStart.getDate() + dragged.duration - 1);

    // AJAX update
    fetch('move_event.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        event_id: dragged.eventId,
        new_start: droppedDate,
        new_end: newEnd.toISOString().slice(0, 10)
      })
    })
    .then(res => res.json())
    .then(response => {
      if (response.success) {
        // Remove the old strip and re-render (or reposition it here)
        document.getElementById(`event-${dragged.eventId}`)?.remove();
        // You can re-render it manually or rely on PHP reload
      } else {
        alert("Failed to move event: " + response.message);
      }
    })
    .catch(err => {
      alert("Drag/drop error");
      console.error(err);
    });
  });
});


//we are removing the old strip and we will rerender it using the position calcultion 

// Re-render the strip at the new position
const week = cell.closest('.calendar-row');
const overlay = week.querySelector('.event-overlay-container');

const weekStart = new Date(week.querySelector('td[data-date]').dataset.date);
const offsetDays = (newStart - weekStart) / (1000 * 60 * 60 * 24);
const left = offsetDays * 185.5;
const width = dragged.duration * 185.5;

// Stacking logic (lane calculation)
const existingStrips = overlay.querySelectorAll('.event-strip');
let laneIndex = 0;

while (true) {
  let conflict = false;
  for (let strip of existingStrips) {
    const stripLeft = parseFloat(strip.style.left);
    const stripWidth = parseFloat(strip.style.width);
    const stripRight = stripLeft + stripWidth;
    const newRight = left + width;
    const stripTop = parseFloat(strip.style.top);
    if (stripTop !== laneIndex * 28) continue;
    if (!(newRight <= stripLeft || left >= stripRight)) {
      conflict = true;
      break;
    }
  }
  if (!conflict) break;
  laneIndex++;
}

const topOffset = laneIndex * 28;

// Create and insert updated strip
const eventDiv = document.createElement('div');
eventDiv.className = 'event-strip';
eventDiv.id = `event-${dragged.eventId}`;
eventDiv.setAttribute('draggable', 'true');
eventDiv.style.cssText = `position:absolute;top:${topOffset}px;left:${left}px;width:${width}px;`;
eventDiv.title = "Moved Event";
eventDiv.innerHTML = `
  <span class="event-text" id="title-${dragged.eventId}">Moved Event</span>
  <span class="event-actions">
    <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${dragged.eventId})"><i class="fa fa-pencil"></i></button>
    <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${dragged.eventId})"><i class="fa fa-remove"></i></button>
  </span>
`;

overlay.appendChild(eventDiv);


// my own logic of php which i write 
 NEWWL
          eventDiv.setAttribute('draggable' ,'true');

          eventDiv.addEventListener('dragstart' ,(e) =>{
            e.dataTransfer.setDate('text/plain',JSON.stringify({
                eventId: data.event_id,
                duration: duration;
            }));
          });
           
          // cell.addEventListener('dragover' ,(e) => e.preventDefault());
          // cell.addEventListener('drop',(e) => {
          //    e.preventDefault();
          //    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
          //    const eventDiv = document.getElementById(`event-${eventId}`);
          //    const originalWidth = parseFloat(eventDiv.style.width);

          //    const week = cell.closest('.calendar-row');
          //    const overlay = week.querySelector('.event-overlay-container');

          //    const newStartData = cell.dataset.date;
          //    const weekStart = new Date(week.querySelector('td[data-date]').dataset.date);
          //    const newStart = new Date(newStartData);
          //    const offsetDays =(newStart - weekStart)/(1000 * 60 * 60 * 24);
          //    const left = offsetDays * 185.5;
          //    const width = originalWidth;




          //    const droppedDate = e.currentTarget.dataset.date;

          //    //send AJAX request to update start and end
          //    const newStart = new Date(droppedDate);
          //    const newEnd = new Date(newStart);
          //    newEnd.setData(newStart.getDate()+data.duration - 1);

          //    fetch('move_event.php' ,{
          //        method: 'POST',
          //        headers: {
          //            'Content-Type': 'application/x-www-form-urlencoded'
          //        },
          //        body: new URLSearchParams({
          //            event_id:data.eventId,
          //            new_start:droppedDate,
          //            new_end: newEnd.toISOSrring().slice(0,10)

          //        })
          //    })
          //    then(res =>res.json())
          //    .then(response => {
          //        if(response.success){

          //        }
          //        else{
          //            alert('Failed to move event' + response.message);

          //        }
          //    })
          //    .catch(err => {
          //        alert('drag/drop error');
          //    });

          // });

          // NEWWTILL
