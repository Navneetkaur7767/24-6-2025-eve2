<script>
document.addEventListener('DOMContentLoaded', () => {
  let isSelecting = false;
  let startCell = null;
  let selectedCells = new Set();

  function clearSelection() {
    selectedCells.forEach(cell => cell.classList.remove('selected'));
    selectedCells.clear();
  }

  function bindDragAndDrop() {
    document.querySelectorAll('.event-strip').forEach(eventDiv => {
      if (eventDiv.dataset.bound === '1') return;
      eventDiv.dataset.bound = '1';

      eventDiv.setAttribute('draggable', 'true');

      eventDiv.addEventListener('dragstart', (e) => {
        const id = eventDiv.id.replace('event-', '');
        const duration = Math.round(parseFloat(eventDiv.style.width) / 185.5);
        e.dataTransfer.setData('text/plain', JSON.stringify({
          eventId: id,
          duration: duration
        }));
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
            document.getElementById(`event-${dragged.eventId}`)?.remove();

            const week = cell.closest('.calendar-row');
            const overlay = week.querySelector('.event-overlay-container');
            const weekStart = new Date(week.querySelector('td[data-date]').dataset.date);
            const offsetDays = (newStart - weekStart) / 86400000;
            const left = offsetDays * 185.5;
            const width = dragged.duration * 185.5;

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
            const eventDiv = document.createElement('div');
            eventDiv.className = 'event-strip';
            eventDiv.id = `event-${dragged.eventId}`;
            eventDiv.setAttribute('draggable', 'true');
            eventDiv.style.cssText = `position:absolute;top:${topOffset}px;left:${left}px;width:${width}px;`;
            eventDiv.innerHTML = `
              <span class="event-text" id="title-${dragged.eventId}">Moved Event</span>
              <span class="event-actions">
                <button class="edit-btn" onclick="event.stopPropagation(); promptEditEvent(${dragged.eventId})"><i class="fa fa-pencil"></i></button>
                <button class="dlt-btn" onclick="event.stopPropagation(); deleteEvent(${dragged.eventId})"><i class="fa fa-remove"></i></button>
              </span>
            `;
            overlay.appendChild(eventDiv);
            bindDragAndDrop(); // allow the new strip to be moved again
          } else {
            alert('Failed to move event: ' + response.message);
          }
        });
      });
    });
  }

  // Mouse down - start selection
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

  // Mouse over - extend selection
  document.querySelectorAll('td[data-date]').forEach(cell => {
    cell.addEventListener('mouseenter', () => {
      if (!isSelecting || !startCell) return;

      clearSelection();
      const allCells = Array.from(document.querySelectorAll('td[data-date]'));
      const startIndex = allCells.indexOf(startCell);
      const currentIndex = allCells.indexOf(cell);
      if (startIndex === -1 || currentIndex === -1) return;

      const [from, to] = startIndex < currentIndex ? [startIndex, currentIndex] : [currentIndex, startIndex];
      for (let i = from; i <= to; i++) {
        allCells[i].classList.add('selected');
        selectedCells.add(allCells[i]);
      }
    });
  });

  // Mouse up - stop selection and create event
  document.addEventListener('mouseup', () => {
    if (isSelecting) {
      isSelecting = false;
      const dates = Array.from(selectedCells).map(cell => cell.getAttribute('data-date'));
      if (dates.length === 0) return;

      dates.sort();
      const startDate = dates[0];
      const endDate = dates[dates.length - 1];
      const eventTitle = prompt(`Enter event title for ${startDate} to ${endDate}:`);
      if (!eventTitle) {
        clearSelection();
        return;
      }

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

            function normalizeDate(d) {
              return new Date(d.getFullYear(), d.getMonth(), d.getDate());
            }

            const offsetDays = (normalizeDate(actualStart) - normalizeDate(weekStart)) / 86400000;
            const duration = (normalizeDate(actualEnd) - normalizeDate(actualStart)) / 86400000 + 1;
            const cellWidth = 185.5;
            const left = offsetDays * cellWidth;
            const width = duration * cellWidth;

            const overlay = week.querySelector('.event-overlay-container');
            const existingStrips = overlay.querySelectorAll('.event-strip');
            let laneIndex = 0;

            while (true) {
              let conflict = false;
              for (let strip of existingStrips) {
                const stripLeft = parseFloat(strip.style.left);
                const stripWidth = parseFloat(strip.style.width);
                const stripRight = stripLeft + stripWidth;
                const newLeft = left;
                const newRight = left + width;
                const stripTop = parseFloat(strip.style.top);
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
            const eventDiv = document.createElement('div');
            eventDiv.className = 'event-strip';
            eventDiv.id = `event-${data.event_id}`;
            eventDiv.setAttribute('draggable', 'true');
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
            bindDragAndDrop(); // make this new one draggable too
          });

          clearSelection();
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

  bindDragAndDrop(); // ✅ Initial call for events rendered by PHP
  document.body.style.userSelect = 'none';
});
</script>
