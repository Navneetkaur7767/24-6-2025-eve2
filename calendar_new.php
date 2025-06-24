
<?php
// print_r($_SESSION);
session_start();

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
// it calculate days in month 
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

//it converts english output to number
$firstDayOfMonth = strtotime("$year-$month-01");

//its tell on that 2025-2-1 was which day of the week 
$firstDayOfWeek = date('w', $firstDayOfMonth);
// Create an array of days of the week

$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// function to get just weeks structure in an array 
function getWeeks($year, $month) {
    $weeks = [];
    $currentWeek = [];

    // it calculate days in month 
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    //it converts english output to number
    $firstDayOfMonth = strtotime("$year-$month-01");

    //its tell on that 2025-2-1 was which day of the week 
    $firstDayOfWeek = date('w', $firstDayOfMonth);

    // Add previous month dates to fill first week
    if ($firstDayOfWeek > 0) {
        for ($i = $firstDayOfWeek; $i > 0; $i--) {
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


// Determine navigation months
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

$daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

// database coneection for events table
$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

// DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create events table if not exists
$createTableSql = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_title VARCHAR(255) NOT NULL,
    startdate DATE DEFAULT NULL,
    enddate DATE DEFAULT NULL,
    adddate DATETIME DEFAULT NULL,
    editdate DATETIME DEFAULT NULL,
    user_email VARCHAR(100) NOT NULL
)";

$conn->query($createTableSql);

// Get current user's email safely
$userEmail = $_SESSION['email'] ?? '';
$safeEmail = $conn->real_escape_string($userEmail);
// echo "<!-- Session email: $safeEmail -->";

// Logic Get all events for the selected month
$monthStart = "$year-$month-01";
$monthEnd = date("Y-m-t", strtotime($monthStart));

 

$query = "SELECT event_title, startdate ,id FROM events 
          WHERE user_email = '$safeEmail'
          AND startdate BETWEEN '$monthStart' AND '$monthEnd'";

$result = $conn->query($query);

// Group events by date for the specific month
$eventsByDate = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $eventsByDate[$row['startdate']][$row['id']] = $row['event_title'];
    }
}

// echo "<pre>";
// print_r($eventsByDate);
// echo "</pre>";
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
    <link rel="stylesheet" type="text/css" href="assests/css/style.css">
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

				   <!-- date selection auto like year and month by scrolling-->
					    <form method="GET" class=" col-5 d-flex gap-2 align-items-center"> 
					    	<!-- month selection dropdown-->
					    	<select name="month" class="form-select" style="width: auto;">
					    		<!--loop for month-->
					    		<?php
					    			for($m=1; $m<=12 ;$m++)
					    			{
					    				// we will check if selected month is equal to the loop in current month 
					    				$selected = ($m == $month) ? "selected" : "";
					    				echo "<option value='$m' $selected>" . date('M', mktime(0, 0, 0, $m, 10)) . "</option>";
					    			}

					    		?>
					    	</select>
					    	<select name="year" class="form-select" style="width: auto;" size="1">
								<?php
						        $currentYear = date('Y');
						        // we are making dropdown of year 20 month before and after
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


					<!-- making different tables for each row earlier everthing was inside one table  -->
					<!--calender header row -->
					
					<table class="table table-bordered w-100 table-fixed head-table">
							<thead>
								<tr>
									<!-- first show the days of week with loop -->
									 <?php foreach ($daysOfWeek as $day): ?>
						     		 <th style="background-color: antiquewhite;"><?= htmlspecialchars($day) ?></th>
						    		 <?php endforeach; ?>
								</tr>
							</thead>
					</table>

					<!-- calender body seperate table-->
					<!-- Weeks container -->
					<div class="calendar-container">
					
					 <!-- here we are actualy using the function -->
					  <?php

					   
					  $weeks = getWeeks($year, $month);
					  foreach ($weeks as $weekNumber => $weekDays): ?>
					    <div class="calendar-row" id="week-<?= $weekNumber ?>" style="position: relative;">
					      <!-- Week days table -->
					      <table class="calendar-table table table-bordered table-fixed w-100 body-table">
					        <tbody>
					          <tr>
					            <?php foreach ($weekDays as $fullDate):
					              $day = date('j', strtotime($fullDate));
					              $isToday = ($fullDate === date('Y-m-d'));
					              $dayOfWeek = date('w', strtotime($fullDate));
					              $style = "";
					              $isCurrentMonth = (int)date('n', strtotime($fullDate)) === $month;
								  if (!$isCurrentMonth) {
   								  $style .= "color: #ccc;";
   								  }
					              elseif($dayOfWeek == 0 || $dayOfWeek == 6) {
					                  $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
					              }
					              $class = $isToday ? "today" : "";
					            ?>
					              <td id="cell-<?= $fullDate ?>" style="<?= $style ?>" class="<?= $class ?>" onclick="promptForEvent('<?= $fullDate ?>')">

					              	<!-- it will show the event day and event-number -->
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
			</div>
		</div>	
	<section>

<!-- Script for add event -->
<script>
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
    .then(res => res.json())
    .then(data => {
    	// here it is boolean check if it false or true 
        if (!data.success) {
        	//we can show the message in json by data.message here can directly show in alert simply data.message 
            alert("Failed to insert event.");
            return;
        }

        // Build HTML for the new event
        const eventId = data.event_id;
        const eventTitle = data.event_title;
        const fullDate = data.event_date;

        // as php wont do it so to do live we are again executing this for javascript code 
        const eventHTML = `
            <div class="event-strip" id="event-${eventId}">
                <span class="event-text" id="title-${eventId}">${eventTitle}</span>
                <span class="event-actions">
                    <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${eventId})">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${eventId})">
                        <i class="fa fa-remove"></i>
                    </button>
                </span>
            </div>
        `;

        // Insert into the correct cell's event container
        const container = document.getElementById(`events-${fullDate}`);
        if (container) {
            container.insertAdjacentHTML('beforeend', eventHTML);
        }

        // alert("Event inserted successfully!");
    })
    .catch(err => {
        alert("Error saving event.");
        console.error(err);
    });
}
</script>


<!-- script for edit event-->
<script>
	function promptEditEvent(eventId)
	{	
		// we are taking value from the dom live updating because earlier was issue as promt value was still old
		 const titleElement = document.getElementById(`title-${eventId}`);
   		 const currentTitle = titleElement ? titleElement.textContent.trim() : '';

    	 const newTitle = prompt("Update the event title", currentTitle);
   		 if (!newTitle || newTitle === currentTitle) return;

		const formData =new FormData();
		formData.append('event_id',eventId);
		formData.append('new_title', newTitle);

		console.log("Editing event ID:", eventId, "New title:", newTitle);

		fetch('edit_event.php',{
			method:'POST',
			body:formData,

		})
		.then(res=>res.text())
		.then(msg=>{
        // Update the title in the DOM
        titleElement.textContent = newTitle;
        console.log("Updated:", msg);

			// alert("Event updated successfully!");
		})
		.catch(err=>{
			alert("Error in Updating event");
			console.error(err);
		});
	}
</script>




<!-- Script for DELETE event -->
<script >
function deleteEvent(eventId) {
	
	if(!confirm("Do you want to delete event?")) return;
	
	const formData = new FormData();
	formData.append('event_id',eventId);

	fetch('delete_event.php',
	{
		method:'POST',
		body: formData,
	})
	.then(res =>res.text())
	.then(msg =>{
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
	
}
</script>


</body>
</html>