
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

        // If we filled 7 days, push the week and reset
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
        $eventsByDate[$row['startdate']][$row['id']] = $row;
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
    <link rel="stylesheet" type="text/css" href="style.css">
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

					  // we run an function to get year and month 
					  $weeks = getWeeks($year, $month);

					  // START: Loop through each date in the current week 
					  // foreach loop will loop through weeks array containing weeknumber=[array of 7 days]
					  foreach ($weeks as $weekNumber => $weekDays): ?>

					  	<!-- div for each week row -->
					    <div class="calendar-row" id="week-<?= $weekNumber ?>" style="position: relative;">
					      <!-- Week days table  table with table row for each week will be created here by the loop above-->
					      <table class="calendar-table table table-bordered table-fixed w-100 body-table">
					        <tbody>
					          <tr>
					          	<!-- loop for each will loop for each date in week number and full date means extract full date from the weekdays -->
					            <?php foreach ($weekDays as $fullDate):

					              $day = date('j', strtotime($fullDate)); // first it will check if its today's date starttotime is used because $fulldate give string but date accept timestamp as seconf arrgument

 					              $isToday = ($fullDate === date('Y-m-d')); //date y-m-d will give you string of today's date so checked today condition

					              $dayOfWeek = date('w', strtotime($fullDate));//extracting day of week 

					              $style = ""; //intialize style 

					              // now check if its current month by comaparing both 
					              $isCurrentMonth = (int)date('n', strtotime($fullDate)) === $month;
								  if (!$isCurrentMonth) { //it is checking for the same month that previous and next dates of diiferent month and making them light grey
   								  $style .= "color: #ccc;";
   								  }
					              elseif($dayOfWeek == 0 || $dayOfWeek == 6) { //checking week days styling also giving styling if it is todays date 
					                  $style = $isToday ? "background-color: #ffeb3b; color: #000;" : "background-color: #f2f2f2; color: #ff0000;";
					              }
					              // used this class for later designing
					              $class = $isToday ? "today" : "";
					              ?>

					              <!-- Render one cell for the date -->
					              <td id="cell-<?= $fullDate ?>" data-date="<?= $fullDate ?>" style="<?= $style ?>" class="<?= $class ?>" >

					              	<!-- it will show the event day and event-number -->
					              	<!-- Show the day number -->
					                <div class="day-number"><?= $day ?></div>

					                <!-- Container for events on this date -->
					                <div class="event-container" id="events-<?= $fullDate ?>">

										
									<!-- If there are events on this day, loop through and show them -->
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
					                </div> <!-- div of events conatiner is closed-->
					              </td>  <!--close td for each cell -->

					              <!-- END: Loop through each date in the current week  -->
					            <?php endforeach; ?> 
					          </tr>
					        </tbody>
					      </table>

					      <!-- Event overlay container for multi-day events it is for event showing only -->
					      <!-- This is the overlay container for this specific week (by week number) -->
					      <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>" style="position: absolute; top: 0; left: 0; width: 100%; pointer-events: none;">
					        <!-- JS will add multi-day event strips here, absolutely positioned -->
					        <?php
							// For multi-day events display in this week:
							// Loop through all dates and their events  NOTE:it is loop inside loop and contain associative array
							foreach ($eventsByDate as $date => $events) { // Outer loop: for each date
							    foreach ($events as $eventId => $event) { // Inner loop: for each event on that date ,  (behindthescenceprint) everthing in eventID

							    	// Your event rendering logic here

							        // 1. Check if event has startdate and enddate (for multi-day) beacuse we created events array and now accessing data from it  
							        $startDate = $event['startdate'];
							        $endDate = $event['enddate'] ?? $startDate; // If no enddate, single day event

							        //2.  Convert to timestamps for comparison
							        $startTimestamp = strtotime($startDate);
							        $endTimestamp = strtotime($endDate);

							        //3. Find the week range dates (first and last date in the $weekDays array)
							        $weekStart = strtotime($weekDays[0]);
							        $weekEnd = strtotime($weekDays[6]);

							        // 4. Skip if this event does NOT overlap with this week at all (Skip events outside this week)
							        if ($endTimestamp < $weekStart || $startTimestamp > $weekEnd) {
							            continue;
							        }

							        // 5. Figure out which part of the event falls inside this week (Calculate the event strip start and end within this week)
							        $eventStripStart = max($startTimestamp, $weekStart);
							        $eventStripEnd = min($endTimestamp, $weekEnd);

							        //  // 6. Calculate how many days from the start of this week the event starts (Calculate positions (number of days offset from week start))
							        $daysFromWeekStart = ($eventStripStart - $weekStart) / 86400; // seconds per day
							        $eventDurationDays = (($eventStripEnd - $eventStripStart) / 86400) + 1;

							        // 7. How many days does this visible part of event last in this week --Calculate width for the event strip (using your cell width approx 136.78px)--
							        $cellWidth = 136.78;
							        $stripWidth = $eventDurationDays * $cellWidth;

							        // Left offset in pixels 8. UI logic: one day = ~136.78px, calculate width and position
							        $leftPos = $daysFromWeekStart * $cellWidth;

							        // Output multi-day event strip div with inline style
							        echo "<div class='event-strip' style='position: absolute; top: 0; left: {$leftPos}px; width: {$stripWidth}px;' title='" . htmlspecialchars($event['event_title']) . "'>";
							        echo "<span class='event-text'>" . htmlspecialchars($event['event_title']) . "</span>";
							        echo "<span class='event-actions'>";
							        echo "<button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent({$eventId})'><i class='fa fa-pencil'></i></button>";
							        echo "<button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent({$eventId})'><i class='fa fa-remove'></i></button>";
							        echo "</span>";
							        echo "</div>";
							    }
							}
							?>
					      </div> <!--div of overlay container-->
					    </div> <!-- div of calender row -->
					  <?php endforeach; ?> <!--loop end of events-->
					</div><!--calender row div -->
			</div> <!-- cal-outer div-->
		</div>	<!-- container div-->
	<section> 

<!-- Script for add event -->
<script>
let isSelecting = false; //isSelecting tracks if the user is currently dragging/selecting
let selectedCells = []; //selectedCells stores all <td> cells selected during the drag

// This selects all <td> elements in the calendar that have a data-date attribute — basically all valid day cells. and loop through them
document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('mousedown', (e) => { //When user presses mouse button down on a date cell:
        e.preventDefault();  // Prevent browser from selecting text accidentally
        isSelecting = true; // We're starting to select cells
        selectedCells = [cell];  // Start new selection list with the clicked cell
        cell.classList.add('selected'); // Visually highlight this cell
    });

    cell.addEventListener('mouseover', (e) => { //When the mouse moves over another date cell (while the button is still down):
        if (isSelecting && !selectedCells.includes(cell)) { //If this cell hasn't already been selected:
            selectedCells.push(cell); //Add to selection if not already added
            cell.classList.add('selected'); // Highlight it
        }
    });
});

// When the mouse button is released anywhere on the page, this function runs.
document.addEventListener('mouseup', () => {
    if (!isSelecting || selectedCells.length === 0) return;  //isSelecting is false → no selection in progress or selectedCells is empty → no cells were dragged
    isSelecting = false; //so to prevent errors we are again saying isSelecting to falsw   Stops the selection mode now that the mouse is released.

    // Get the start and end dates of the selected cells:
    const startDate = selectedCells[0].dataset.date; //dataset.date accesses the data-date="YYYY-MM-DD" from the <td> it reads data-* attributes 
    const endDate = selectedCells[selectedCells.length - 1].dataset.date; //selecting last data in the selected cells 
    const title = prompt(`Enter event title for ${startDate} to ${endDate}:`); //Prompt the user to enter an event title for the selected date range.
    if (!title) {  //If the user cancels the prompt or enters nothing:
        selectedCells.forEach(cell => cell.classList.remove('selected')); //It removes the "selected" class (so the cells go back to normal)
        return; //Then exits the function
    }


    const formData = new FormData();
    formData.append('event_title', title);
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);

    fetch('save_event.php', {
        method: 'POST',
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || "Failed to save event.");
        } else {

            // Get cell width dynamically
            const cellWidth = selectedCells[0].offsetWidth; //calculating first cell width in selected cells and offWidth is DOM property to calculate width in px
            const totalDays = selectedCells.length; // selected cell is array of td so we are counting the count of selected cells
            const stripWidth = totalDays * cellWidth; // calculating strip width by 4 ( tolaldays)* 137(width of one cell) 

            //style="width: ${stripWidth}px;" in this we are dynamically adding the calculated stripWidth
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

            // Insert the strip into the first selected cell only
            //The event strip is a single wide <div> (with a calculated width like 548px) that is designed to stretch across the other days using CSS.
            //It's not copied into every selected day — just the first one, and from there, its wide width visually makes it look like it continues over the next dates.
            const firstCell = selectedCells[0];
            const container = firstCell.querySelector('.event-container');
            if (container) {
                container.insertAdjacentHTML('beforeend', eventHTML);
            }
        }
    })
    .catch(err => { // Catches any network or server-side errors and shows a fallback error message.
        console.error(err);
        alert("Error saving event.");
    })
    .finally(() => { // Removes the selected visual style and resets the selection, whether the event was saved successfully or not.
        selectedCells.forEach(cell => cell.classList.remove('selected'));
        selectedCells = [];
    });
});
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



<!-- save_evrnt.php -->

<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$title = $conn->real_escape_string($_POST['event_title'] ?? '');
$start = $conn->real_escape_string($_POST['start_date'] ?? '');
$end = $conn->real_escape_string($_POST['end_date'] ?? '');

if (!$title || !$start || !$end) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$sql = "INSERT INTO events (event_title, startdate, enddate, user_email)
        VALUES ('$title', '$start', '$end', '$userEmail')";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'success' => true,
        'event_id' => $conn->insert_id,
        'event_title' => $title,
        'start_date' => $start,
        'end_date' => $end
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>

<style>

*
{
	box-sizing: border-box;
	margin: 0;
	padding: 0;
}
.cal-section
{
	padding: 30px 0;
}
.cal-outer
{
	/*	max-width: 600px;*/
    margin: 0 auto;
}
.head-color{
   background-color: antiquewhite;
}
.faded
{
	color: #ccc;
}
.table-fixed
{
	table-layout: fixed !important;
}
.head-table
{
	margin-bottom: 0px;
}
.body-table
{
	margin-bottom: 0px;
}
td.today
{
	background-color: lightblue;
}
tr
{
	text-align: center;
}

table tbody td {
   /* padding: 20px;
    font-size: 18px;*/
    width: 136.781px;
    height: 112.828px;
    overflow: hidden;             /* prevents content from escaping */
    padding: 5px;                 /* adjust as needed */
    vertical-align: top;          /* aligns content at top of cell */
    box-sizing: border-box;
}


/*just added*/
.day-number {
   /* font-weight: bold;
    margin-bottom: 5px;*/
}

.event-container {
/*    margin-top: 5px;*/
}
/*just added*/
/*css for event strip and edit and delete button in it*/
/*.event-strip {

			display:flex;
			justify-content: center;
			align-content: center;
			width:100%;
			max-width:100%;
			background-color:yellow;
			border-radius:4px;
			margin-top: 4px;
			overflow: hidden;

}*/
.event-overlay-container {
    position: relative;
    height: 20px;
    pointer-events: none; /*container won't block clicks */
}
td.selected {
    background-color: rgba(255, 230, 150, 0.7);
}

 .event-strip {
 	pointer-events: auto; /*childs will get clicked*/
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute; 
    /* Key for positioning */
    background-color: yellow;
    border-radius: 4px;
    margin-top: 4px;
    overflow: hidden;
    height: 24px;
}
}
.event-text {

		 white-space: nowrap;
	    overflow: hidden;
	    text-overflow: ellipsis;
	    margin-right: 8px;
	    flex-grow: 1;
	    font-size: 14px;
		 font-weight: bold;
}

.event-actions	{
	display: flex;
    gap: 6px;
    flex-grow: 0;
}
.event-actions button {

    background: transparent;
    border: none;
}
.dlt-btn
{
	color: red;
}


</style>