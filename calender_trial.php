
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

 

$query = "SELECT event_title, startdate , enddate,id FROM events 
          WHERE user_email = '$safeEmail'
          AND startdate BETWEEN '$monthStart' AND '$monthEnd'";

$result = $conn->query($query);

// Group events by date for the specific month
// $eventsByDate = [];
// if ($result) {
//     while ($row = $result->fetch_assoc()) {
//         $eventsByDate[$row['startdate']][$row['id']] = $row;
//     }
// }

$eventsByDate = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row['startdate']);
        $end = isset($row['enddate']) && $row['enddate'] ? new DateTime($row['enddate']) : clone $start;

        // Loop over all dates the event spans
        for ($date = clone $start; $date <= $end; $date->modify('+1 day')) {
            $dateStr = $date->format('Y-m-d');
            if (!isset($eventsByDate[$dateStr])) {
                $eventsByDate[$dateStr] = [];
            }
            $eventsByDate[$dateStr][$row['id']] = $row;
        }
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
    <link rel="stylesheet" type="text/css" href="assests/css/style_trial.css">
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
					              <td id="cell-<?= $fullDate ?>"data-date="<?= $fullDate ?>" style="<?= $style ?>" class="<?= $class ?>" >

					              	<!-- it will show the event day and event-number -->
					                <div class="day-number"><?= $day ?></div>
					                <!-- here i commented the strip div because it was showing two time the same event -->
					                <?php /*
									<div class="event-container" id="events-<?= $fullDate ?>">
									  <?php
									    if (isset($eventsByDate[$fullDate])) {
									      foreach ($eventsByDate[$fullDate] as $eventId => $event) {
									        ?>
									        <div class="event-strip" id="event-<?= $eventId ?>">
									          <span class="event-text" id="title-<?= $eventId ?>"><?= htmlspecialchars($event['event_title']) ?></span>
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
									*/ ?>
					              </td>
					            <?php endforeach; ?>
					          </tr>
					        </tbody>
					      </table>

					      <!-- Event overlay container for multi-day events -->
					      <div class="event-overlay-container" id="overlay-week-<?= $weekNumber ?>" style="position: absolute; top: 30px; left: 0; width: 100%; pointer-events: none;">
							
							<?php
							$seenEventIds = []; // Prevent duplicate render

							$lanes = []; // NEW: lanes array to track occupied time slots
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

							         //  logic to calculate top offset so that events should not overlap 
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

							        //debug line 
							       // echo "<!-- event: {$event['event_title']} | start={$startDate}, end={$endDate}, left={$leftPos}, width={$stripWidth}, days={$eventDurationDays} -->";

							        echo "<div class='event-strip' id='event-{$eventId}' style='position: absolute; top: {$topOffset}px; left: {$leftPos}px; width: {$stripWidth}px;' title='" . htmlspecialchars($event['event_title']) . "'>";
							        echo "<span class='event-text'  id='title-{$eventId}'>" . htmlspecialchars($event['event_title']) . "</span>";
							        echo "<span class='event-actions'>";
							        echo "<button class='edit-btn' onclick='event.stopPropagation(); promptEditEvent({$eventId})'><i class='fa fa-pencil'></i></button>";
							        echo "<button class='dlt-btn' onclick='event.stopPropagation(); deleteEvent({$eventId})'><i class='fa fa-remove'></i></button>";
							        echo "</span></div>";
							    }
							}
							?>

							</div>
					    </div>
					  <?php endforeach; ?>
					</div>
			</div>
		</div>	
	<section>

<!-- Script for add event -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  let isSelecting = false;
  let startCell = null;
  let selectedCells = new Set();

  function clearSelection() {
    selectedCells.forEach(cell => cell.classList.remove('selected'));
    selectedCells.clear();
  }

  // --- Debugging: check cell width ---
  const sampleCell = document.querySelector('td[data-date]');
  if (sampleCell) {
    console.log('Sample cell width:', sampleCell.offsetWidth);
  } else {
    console.log('No calendar cell found!');
  }
  // --- Debugging ends ---

  // Mouse down on a cell - start selection
  document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('mousedown', (e) => {

    	// NEWW also changed here that if mouse is on event strip stop there
      if (e.target.closest('.event-strip')) return;	
      e.preventDefault();
      clearSelection();
      isSelecting = true;
      startCell = cell;
      cell.classList.add('selected');
      selectedCells.add(cell);
    });
  });

  // Mouse over cells while mouse is down - extend selection
  document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('mouseenter', () => {
      if (!isSelecting || !startCell) return;

      clearSelection();

      //Creates an array of all calendar date cells. This lets us calculate indexes of selected cells.
      const allCells = Array.from(document.querySelectorAll('td[data-date]')); 
      const startIndex = allCells.indexOf(startCell);
      const currentIndex = allCells.indexOf(cell);

      if (startIndex === -1 || currentIndex === -1) return; //means no cell is selected

      // Select all cells between startIndex and currentIndex inclusive
      //This ensures the loop goes from the smaller index to the larger one, no matter which direction you dragged.
      const [from, to] = startIndex < currentIndex ? [startIndex, currentIndex] : [currentIndex, startIndex];
      for (let i = from; i <= to; i++) { // now taking from and to index from above 
        allCells[i].classList.add('selected');
        selectedCells.add(allCells[i]);
      }
    });
  });

  // Mouse up anywhere - stop selection and prompt for event title
 document.addEventListener('mouseup', () => {
  if (isSelecting) {
    isSelecting = false;

    const dates = Array.from(selectedCells).map(cell => cell.getAttribute('data-date'));
    if (dates.length === 0) return;

    // Sort dates
    dates.sort();
    const startDate = dates[0]; //start date 
    const endDate = dates[dates.length - 1]; //enddate from dates array of strings 

    // Prompt for event title
    const eventTitle = prompt(`Enter event title for ${startDate} to ${endDate}:`);
    if (!eventTitle) {
    	clearSelection(); // clear the selection even if user cancels
    	return;
    }

    // Send to PHP to save in DB
    fetch('save_event.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        event_title: eventTitle,
        start_date: startDate,
        end_date: endDate
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // After saving, dynamically add the event strip in overlay
        const weekContainers = document.querySelectorAll('.calendar-row');

        weekContainers.forEach(week => {
          const weekStartCell = week.querySelector('td[data-date]');
          if (!weekStartCell) return;

          const weekStart = new Date(weekStartCell.dataset.date);
          const weekEnd = new Date(weekStart);
          weekEnd.setDate(weekEnd.getDate() + 6);

          const start = new Date(startDate);
          const end = new Date(endDate);

          if (end < weekStart || start > weekEnd) return;

          const actualStart = start < weekStart ? weekStart : start;
          const actualEnd = end > weekEnd ? weekEnd : end;

          // const offsetDays = (actualStart - weekStart) / (1000 * 60 * 60 * 24);
          // const duration = ((actualEnd - actualStart) / (1000 * 60 * 60 * 24)) + 1;

          // const cellWidth = 175.711;
          // const left = offsetDays * cellWidth;
          // const width = duration * cellWidth;

          function normalizeDate(d) {
		  return new Date(d.getFullYear(), d.getMonth(), d.getDate());
		  }

		  const actualStartNormalized = normalizeDate(actualStart);
		  const actualEndNormalized = normalizeDate(actualEnd);
		  const weekStartNormalized = normalizeDate(weekStart);

		  const offsetDays = (actualStartNormalized - weekStartNormalized) / (1000 * 60 * 60 * 24);
		  const duration = ((actualEndNormalized - actualStartNormalized) / (1000 * 60 * 60 * 24)) + 1;

		  const cellWidth = 185.5;
		  const left = offsetDays * cellWidth;
		  const width = duration * cellWidth;

		  // code dubugging
		  console.log('actualStart:', actualStart);
		  console.log('weekStart:', weekStart);
		  console.log('offsetDays:', offsetDays);
		  console.log('left:', left);


          const overlay = week.querySelector('.event-overlay-container');
          const eventDiv = document.createElement('div');
          eventDiv.className = 'event-strip';
          eventDiv.id = `event-${data.event_id}`; 

           // NEWWL
          eventDiv.setAttribute('draggable' ,'true');

          eventDiv.addEventListener('dragstart' ,(e) =>{
          	e.dataTransfer.setDate('text/plain',JSON.stringify({
          		eventId: data.event_id,
          		duration: duration,
          	}));
          });

          document.querySelectorAll('td[data-date]').forEach(cell=> {
          	cell.addEventListener('dragover', e => e.preventDefault());

          	cell.addEventListener('drop' ,e => {
          		e.preventDefault();
          		const droppedDate = cell.dataset.date;
          		const dragged = JSON.parse(e.dataTransfer.getData('text/plain'));

          		const newStart = new Date(droppedDate);
          		const newEnd = new Date(newStart);
          		newEnd.setDate(newStart.getDate() + dragged.duration -1);

          		// AJAX UPDATE
          		fetch('move_event.php' ,{
          		method: 'POST',
          		headers: {
          			'Content-Type': 'application/x-www-form-urlencoded'},
          			body: new URLSearchParams({
          				event_id: dragged.eventId,
          				new_start: droppedDate,
          				new_end: newEnd.toISOString().slice(0,10)
          			})

          	})
          	.then(res => res.json())
          	.then(response => {
          		if (response.success)
          		{
          			//Remove the old strip and re-render 
          			document.getElementById(`event-${dragged.eventId}`)?.remove();
                   //you can re-render it manually or rely in PHP reload

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
				eventDiv.title = eventTitle;
				eventDiv.innerHTML = `
				  <span class="event-text" id="title-${dragged.eventId}">Moved Event</span>
				  <span class="event-actions">
				    <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${dragged.eventId})"><i class="fa fa-pencil"></i></button>
				    <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${dragged.eventId})"><i class="fa fa-remove"></i></button>
				  </span>
				`;

				overlay.appendChild(eventDiv);

          		} else {
          			alert("failed to move event:" + response.message);
          		}
          	})
          	.catch(err => {
          		alert("Drag/drop error");
          		console.error(err);
          	});
          });
        });
           
        
          // NEWWTILL

          // overlapping logic for events handdling with js without reload
			let existingStrips = overlay.querySelectorAll('.event-strip');
			let laneIndex = 0;

			while (true) {
			  let conflict = false;
			  for (let strip of existingStrips) {
			    let stripLeft = parseFloat(strip.style.left); // 370
			    let stripWidth = parseFloat(strip.style.width); 
			    let stripRight = stripLeft + stripWidth; //555.5 
			    let newLeft = left; // eg 520
			    let newRight = left + width; //eg 705

			    let stripTop = parseFloat(strip.style.top);
			    if (stripTop !== laneIndex * 28) continue; // Only check events in this lane

			    // Check for horizontal overlap
			    if (!(newRight <= stripLeft || newLeft >= stripRight)) {
			      conflict = true;
			      break; // means you cannot put in lane 0 shift to lane 1 and break 
			    }
			  }

			  if (!conflict) break;
			  laneIndex++;
			}

			const topOffset = laneIndex * 28;

          eventDiv.style.cssText = `position:absolute;top:${topOffset}px;left:${left}px;width:${width}px;`;
          eventDiv.title = eventTitle;
          eventDiv.innerHTML = `
            <span class="event-text"  id="title-${data.event_id}">${eventTitle}</span>
            <span class="event-actions">
              <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${data.event_id})"><i class="fa fa-pencil"></i></button>
              <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${data.event_id})"><i class="fa fa-remove"></i></button>
            </span>
          `;
          overlay.appendChild(eventDiv);
        });
        clearSelection(); //clear cells after selection
      } else {
        alert('Failed to save event: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('Error saving event');
    });
  }
});
  // Prevent text selection during drag
  document.body.style.userSelect = 'none';
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

		fetch('edit_event.php', {
		  method: 'POST',
		  body: formData,
		})
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
	.then(res =>res.json())
	.then(data => {
	    if (data.success) {
	        const eventElement = document.getElementById(`event-${eventId}`);
	        if (eventElement) {
	            eventElement.remove();
	        }
	    } else {
	        alert(data.message);
	    }
		});

	
}	
</script>
</body>
</html>

<!-- move them outside of then -->
document.addEventListener('DOMContentLoaded', () => {
  // your existing code...

  // Move these OUTSIDE of .then()
  document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('dragover', e => e.preventDefault());

    cell.addEventListener('drop', e => {
      e.preventDefault();

      const droppedDate = cell.dataset.date;
      const dragged = JSON.parse(e.dataTransfer.getData('text/plain'));

      const newStart = new Date(droppedDate);
      const newEnd = new Date(newStart);
      newEnd.setDate(newStart.getDate() + dragged.duration - 1);

      fetch('move_event.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          event_id: dragged.eventId,
          new_start: droppedDate,
          new_end: newEnd.toISOString().slice(0, 10)
        })
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) {
          // Remove old event strip
          document.getElementById(`event-${dragged.eventId}`)?.remove();

          // Re-render logic goes here...
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

  // ...rest of your code
});
