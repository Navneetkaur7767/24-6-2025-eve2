<?php
// Set timezone
date_default_timezone_set('UTC');

// Get month and year from URL, or use current
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Calculate the number of days in the month and the first day
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = strtotime("$year-$month-01");
$firstDayOfWeek = date('w', $firstDayOfMonth); // 0 = Sunday

// Determine navigation months
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

// Array of weekday names
$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Calendar</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 600px;
        }
        th, td {
            width: 14.28%;
            border: 1px solid #999;
            text-align: center;
            padding: 10px;
        }
        th {
            background-color: #eee;
        }
        td.today {
            background-color: yellow;
        }
    </style>
</head>
<body>

<h1>Calendar for <?= date('F Y', $firstDayOfMonth) ?></h1>

<!-- Navigation -->
<p>
    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">← Previous Month</a> |
    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">Next Month →</a>
</p>

<table>
    <thead>
        <tr>
            <?php foreach ($daysOfWeek as $day): ?>
                <th><?= $day ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <?php
            // Print empty cells before the first day
            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                echo "<td></td>";
            }

            // Print the days of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                $class = $isToday ? " class='today'" : "";
                echo "<td$class>$day</td>";

                if (($day + $firstDayOfWeek) % 7 == 0) {
                    echo "</tr><tr>";
                }
            }

            // Fill remaining cells to complete the last week
            $remaining = (7 - (($day + $firstDayOfWeek - 1) % 7)) % 7;
            for ($i = 0; $i < $remaining; $i++) {
                echo "<td></td>";
            }
            ?>
        </tr>
    </tbody>
</table>

</body>
</html>