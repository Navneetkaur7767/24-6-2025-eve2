<script>
document.addEventListener('DOMContentLoaded', () => {
  let isSelecting = false;
  let startCell = null;
  let selectedCells = new Set();

  function clearSelection() {
    selectedCells.forEach(cell => cell.classList.remove('selected'));
    selectedCells.clear();
  }

  // Start cell selection
  document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('mousedown', (e) => {
      if (e.target.closest('.event-strip')) return;
      e.preventDefault();
      clearSelection();
      isSelecting = true;
      startCell = cell;
      cell.classList.add('selected');
      selectedCells.add(cell);
    });
  });

  // While dragging, highlight selection
  document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('mouseenter', () => {
      if (!isSelecting || !startCell) return;
      clearSelection();

      const allCells = Array.from(document.querySelectorAll('td[data-date]'));
      const startIndex = allCells.indexOf(startCell);
      const currentIndex = allCells.indexOf(cell);
      const [from, to] = startIndex < currentIndex ? [startIndex, currentIndex] : [currentIndex, startIndex];

      for (let i = from; i <= to; i++) {
        allCells[i].classList.add('selected');
        selectedCells.add(allCells[i]);
      }
    });
  });

  // On mouseup: prompt and create event
  document.addEventListener('mouseup', () => {
    if (!isSelecting) return;
    isSelecting = false;

    const dates = Array.from(selectedCells).map(cell => cell.dataset.date);
    if (dates.length === 0) return;

    dates.sort();
    const startDate = dates[0];
    const endDate = dates[dates.length - 1];
    const title = prompt(`Enter event title from ${startDate} to ${endDate}:`);
    if (!title) return clearSelection();

    fetch('save_event.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ event_title: title, start_date: startDate, end_date: endDate })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) return alert("Error: " + data.message);

      const eventId = data.event_id;
      const start = new Date(startDate);
      const end = new Date(endDate);

      document.querySelectorAll('.calendar-row').forEach(row => {
        const weekStartCell = row.querySelector('td[data-date]');
        if (!weekStartCell) return;

        const weekStart = new Date(weekStartCell.dataset.date);
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekEnd.getDate() + 6);

        if (end < weekStart || start > weekEnd) return;

        const actualStart = start < weekStart ? weekStart : start;
        const actualEnd = end > weekEnd ? weekEnd : end;

        const offsetDays = Math.floor((normalize(actualStart) - normalize(weekStart)) / 86400000);
        const duration = Math.floor((normalize(actualEnd) - normalize(actualStart)) / 86400000) + 1;
        const left = offsetDays * 185.5;
        const width = duration * 185.5;

        const overlay = row.querySelector('.event-overlay-container');

        // Lane stacking
        const existing = overlay.querySelectorAll('.event-strip');
        let lane = 0;
        while (true) {
          let conflict = false;
          for (let strip of existing) {
            if (parseFloat(strip.style.top) !== lane * 28) continue;
            const stripLeft = parseFloat(strip.style.left);
            const stripRight = stripLeft + parseFloat(strip.style.width);
            if (!(left + width <= stripLeft || left >= stripRight)) {
              conflict = true;
              break;
            }
          }
          if (!conflict) break;
          lane++;
        }

        const top = lane * 28;

        const eventDiv = document.createElement('div');
        eventDiv.className = 'event-strip';
        eventDiv.id = `event-${eventId}`;
        eventDiv.setAttribute('draggable', 'true');
        eventDiv.style.cssText = `position:absolute;top:${top}px;left:${left}px;width:${width}px;`;
        eventDiv.title = title;
        eventDiv.innerHTML = `
          <span class="event-text" id="title-${eventId}">${title}</span>
          <span class="event-actions">
            <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${eventId})"><i class="fa fa-pencil"></i></button>
            <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${eventId})"><i class="fa fa-remove"></i></button>
          </span>`;

        overlay.appendChild(eventDiv);
        bindDragEvents(eventDiv, duration); // make it draggable
      });

      clearSelection();
    })
    .catch(err => {
      alert("Error creating event");
      console.error(err);
    });
  });

  // Prevent text selection
  document.body.style.userSelect = 'none';

  // Normalizer
  function normalize(date) {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
  }

  // Drag logic setup — call for each .event-strip
  function bindDragEvents(eventDiv, duration = null) {
    if (eventDiv.dataset.bound === "1") return; // prevent duplicate
    eventDiv.dataset.bound = "1";

    const id = eventDiv.id.split('-')[1];
    duration = duration || Math.round(parseFloat(eventDiv.style.width) / 185.5);

    eventDiv.addEventListener('dragstart', e => {
      e.dataTransfer.setData('text/plain', JSON.stringify({ eventId: id, duration }));
    });
  }

  // Initial bind for all strips
  document.querySelectorAll('.event-strip').forEach(div => bindDragEvents(div));

  // Drag targets
  document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('dragover', e => e.preventDefault());

    cell.addEventListener('drop', e => {
      e.preventDefault();
      const droppedDate = cell.dataset.date;
      const data = JSON.parse(e.dataTransfer.getData('text/plain'));
      const newStart = new Date(droppedDate);
      const newEnd = new Date(newStart);
      newEnd.setDate(newStart.getDate() + data.duration - 1);

      fetch('move_event.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          event_id: data.eventId,
          new_start: droppedDate,
          new_end: newEnd.toISOString().slice(0,10)
        })
      })
      .then(res => res.json())
      .then(response => {
        if (!response.success) return alert("Move failed: " + response.message);

        const old = document.getElementById(`event-${data.eventId}`);
        old?.remove();

        const row = cell.closest('.calendar-row');
        const overlay = row.querySelector('.event-overlay-container');

        const weekStart = new Date(row.querySelector('td[data-date]').dataset.date);
        const offset = Math.floor((normalize(newStart) - normalize(weekStart)) / 86400000);
        const width = data.duration * 185.5;
        const left = offset * 185.5;

        // Lane stacking
        const existing = overlay.querySelectorAll('.event-strip');
        let lane = 0;
        while (true) {
          let conflict = false;
          for (let strip of existing) {
            if (parseFloat(strip.style.top) !== lane * 28) continue;
            const l = parseFloat(strip.style.left);
            const r = l + parseFloat(strip.style.width);
            if (!(left + width <= l || left >= r)) {
              conflict = true;
              break;
            }
          }
          if (!conflict) break;
          lane++;
        }

        const top = lane * 28;

        const eventDiv = document.createElement('div');
        eventDiv.className = 'event-strip';
        eventDiv.id = `event-${data.eventId}`;
        eventDiv.setAttribute('draggable', 'true');
        eventDiv.style.cssText = `position:absolute;top:${top}px;left:${left}px;width:${width}px;`;
        eventDiv.title = "Moved Event";
        eventDiv.innerHTML = `
          <span class="event-text" id="title-${data.eventId}">Moved Event</span>
          <span class="event-actions">
            <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${data.eventId})"><i class="fa fa-pencil"></i></button>
            <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${data.eventId})"><i class="fa fa-remove"></i></button>
          </span>`;

        overlay.appendChild(eventDiv);
        bindDragEvents(eventDiv, data.duration);
      })
      .catch(err => {
        alert("Error during drop");
        console.error(err);
      });
    });
  });
});
</script>
