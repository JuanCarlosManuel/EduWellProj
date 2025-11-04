// Wait until the page is fully loaded
document.addEventListener("DOMContentLoaded", () => {
  // --- QUICK ACTION BUTTONS ---
  const addStudyBtn = document.querySelector(".quick-actions button:nth-child(1)");
  const viewTrackerBtn = document.querySelector(".quick-actions button:nth-child(2)");
  const profileBtn = document.querySelector(".quick-actions button:nth-child(3)");

  // Add Study Log → open modal
  if (addStudyBtn) addStudyBtn.addEventListener("click", openStudyModal);

  // View Tracker → open modal
  if (viewTrackerBtn) viewTrackerBtn.addEventListener("click", openTrackerModal);

  // Profile Button (go to profile page)
  if (profileBtn) profileBtn.addEventListener("click", () => {
    window.location.href = "profile.php";
  });

  // Initialize task filters
  initializeTaskFilters();
  
  // Initialize lists
  updateUpcomingTasks();
  updateTrackerList();
  
  // Initialize profile updates
  initializeProfileUpdates();
  
  // Check for profile updates on page load
  checkForProfileUpdates();
});


// ------------------
// STUDY MODAL LOGIC (modal is created by your existing openStudyModal)
function openStudyModal() {
  const modalHTML = `
    <div class="modal" id="studyModal">
      <div class="modal-content">
        <h3>Add Study Log</h3>
        <label>Date:</label><br>
        <input type="date" id="study-date"><br><br>
        <label>Task:</label><br>
        <textarea id="study-task" placeholder="What do you need to do?"></textarea>
        <div class="modal-buttons">
          <button id="save-study-btn">Save</button>
          <button class="cancel" id="cancel-study-btn">Cancel</button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", modalHTML);
  const modal = document.getElementById("studyModal");
  modal.style.display = "flex";

  // attach handlers for the created modal buttons
  document.getElementById("save-study-btn").addEventListener("click", saveStudyLog);
  document.getElementById("cancel-study-btn").addEventListener("click", closeStudyModal);
}

function closeStudyModal() {
  const modal = document.getElementById("studyModal");
  if (modal) modal.remove();
}


// ------------------
// Save study log into localStorage and update UI
async function saveStudyLog() {
  const dateEl = document.getElementById("study-date");
  const taskEl = document.getElementById("study-task");
  const date = dateEl?.value;
  const task = taskEl?.value.trim();

  if (!date || !task) {
    alert("Please fill out all fields.");
    return;
  }

  // Normalize date display as dd - mm - yyyy
  const d = new Date(date);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  const displayDate = `${dd} - ${mm} - ${yyyy}`;

  const logs = JSON.parse(localStorage.getItem("studyLogs")) || [];
  logs.push({ id: Date.now(), date: displayDate, task, completed: false });
  localStorage.setItem("studyLogs", JSON.stringify(logs));

  closeStudyModal();
  updateUpcomingTasks();
  updateTrackerList();
}


// ------------------
// Initialize task filters
function initializeTaskFilters() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      // Remove active class from all buttons
      filterBtns.forEach(b => b.classList.remove('active'));
      // Add active class to clicked button
      btn.classList.add('active');
      
      const filter = btn.getAttribute('data-filter');
      filterTasks(filter);
    });
  });
}

// ------------------
// Filter tasks based on status
function filterTasks(filter) {
  const taskItems = document.querySelectorAll('.task-item');
  taskItems.forEach(item => {
    const isCompleted = item.classList.contains('completed');
    let show = true;
    
    switch(filter) {
      case 'pending':
        show = !isCompleted;
        break;
      case 'completed':
        show = isCompleted;
        break;
      case 'all':
      default:
        show = true;
        break;
    }
    
    item.style.display = show ? 'flex' : 'none';
  });
}

// ------------------
// Render Upcoming Tasks with improved UI
function updateUpcomingTasks() {
  const taskList = document.getElementById("task-list");
  if (!taskList) return;

  const logs = JSON.parse(localStorage.getItem("studyLogs")) || [];
  taskList.innerHTML = "";

  // Update task count
  updateTaskCount(logs);

  if (logs.length === 0) {
    const emptyDiv = document.createElement("div");
    emptyDiv.className = "no-tasks-message";
    emptyDiv.innerHTML = "<p>No tasks yet. Add your first task!</p>";
    taskList.appendChild(emptyDiv);
    return;
  }

  // Sort tasks by date (newest first)
  const sortedLogs = logs.sort((a, b) => new Date(b.date.split(' - ').reverse().join('-')) - new Date(a.date.split(' - ').reverse().join('-')));

  sortedLogs.forEach(log => {
    const item = document.createElement("div");
    item.className = `task-item ${log.completed ? 'completed' : ''}`;
    item.setAttribute('data-task-id', log.id);

    // Task content container
    const contentDiv = document.createElement("div");
    contentDiv.className = "task-content";

    // Checkbox
    const cb = document.createElement("input");
    cb.type = "checkbox";
    cb.checked = !!log.completed;
    cb.className = "task-checkbox";

    // Task text container
    const textContainer = document.createElement("div");
    textContainer.className = "task-text-container";

    // Date span
    const dateSpan = document.createElement("span");
    dateSpan.className = "task-date";
    dateSpan.textContent = log.date;

    // Task text
    const text = document.createElement("span");
    text.className = "task-text";
    text.textContent = log.task;

    // Delete button
    const deleteBtn = document.createElement("button");
    deleteBtn.className = "delete-task-btn";
    deleteBtn.innerHTML = "🗑️";
    deleteBtn.title = "Delete task";

    // Attach event listeners
    cb.addEventListener("change", () => {
      toggleTaskComplete(log.id, cb.checked);
    });

    deleteBtn.addEventListener("click", () => {
      deleteTask(log.id);
    });

    // Assemble the task item
    textContainer.appendChild(dateSpan);
    textContainer.appendChild(text);
    contentDiv.appendChild(cb);
    contentDiv.appendChild(textContainer);
    item.appendChild(contentDiv);
    item.appendChild(deleteBtn);
    taskList.appendChild(item);
  });
}

// ------------------
// Update task count display
function updateTaskCount(logs) {
  const taskCount = document.getElementById("task-count");
  if (taskCount) {
    const total = logs.length;
    const completed = logs.filter(log => log.completed).length;
    const pending = total - completed;
    
    taskCount.textContent = `${total} tasks (${pending} pending, ${completed} completed)`;
  }
}


// Toggle completion: update localStorage and UI
function toggleTaskComplete(id, completed) {
  const logs = JSON.parse(localStorage.getItem("studyLogs")) || [];
  const updated = logs.map(l => l.id === id ? { ...l, completed: !!completed } : l);
  localStorage.setItem("studyLogs", JSON.stringify(updated));
  updateUpcomingTasks();
  updateTrackerList();
}

// ------------------
// Delete task functionality
function deleteTask(id) {
  if (confirm('Are you sure you want to delete this task?')) {
    const logs = JSON.parse(localStorage.getItem("studyLogs")) || [];
    const updated = logs.filter(l => l.id !== id);
    localStorage.setItem("studyLogs", JSON.stringify(updated));
    
    // Add smooth deletion animation
    const taskItem = document.querySelector(`[data-task-id="${id}"]`);
    if (taskItem) {
      taskItem.style.transition = 'all 0.3s ease';
      taskItem.style.transform = 'translateX(-100%)';
      taskItem.style.opacity = '0';
      
      setTimeout(() => {
        updateUpcomingTasks();
        updateTrackerList();
      }, 300);
    } else {
      updateUpcomingTasks();
      updateTrackerList();
    }
  }
}

// ------------------
// Initialize profile updates
function initializeProfileUpdates() {
  // Listen for profile updates from other pages
  window.addEventListener('storage', (e) => {
    if (e.key === 'profileUpdated') {
      updateProfileDisplay();
    }
  });
  
  // Check for profile updates periodically
  setInterval(checkProfileUpdates, 5000);
}

// ------------------
// Update profile display
function updateProfileDisplay() {
  // This would typically fetch updated profile data from the server
  // For now, we'll show a visual indicator that profile was updated
  const profilePicture = document.getElementById('profile-picture');
  const userName = document.getElementById('user-name');
  const updateIndicator = document.querySelector('.update-indicator');
  
  if (updateIndicator) {
    updateIndicator.style.display = 'block';
    updateIndicator.style.animation = 'pulse 0.5s ease-in-out';
    
    setTimeout(() => {
      updateIndicator.style.display = 'none';
    }, 2000);
  }
}

// ------------------
// Check for profile updates
function checkProfileUpdates() {
  // This could make an AJAX call to check for profile updates
  // For now, we'll just simulate the check
  const lastCheck = localStorage.getItem('lastProfileCheck') || 0;
  const now = Date.now();
  
  if (now - lastCheck > 30000) { // Check every 30 seconds
    localStorage.setItem('lastProfileCheck', now);
    // Here you would make an AJAX call to check for updates
  }
}

// ------------------
// Check for profile updates on page load
function checkForProfileUpdates() {
  // Check if we just came from a profile update
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('updated') === '1') {
    // Show success message and animate profile update
    showProfileUpdateSuccess();
    
    // Clean up URL
    window.history.replaceState({}, document.title, window.location.pathname);
  }
  
  // Check for profile updates from localStorage (cross-tab communication)
  const profileUpdateTime = localStorage.getItem('profileUpdateTime');
  const lastKnownUpdate = localStorage.getItem('lastKnownProfileUpdate') || 0;
  
  if (profileUpdateTime && parseInt(profileUpdateTime) > parseInt(lastKnownUpdate)) {
    // Profile was updated in another tab
    refreshProfileData();
    localStorage.setItem('lastKnownProfileUpdate', profileUpdateTime);
  }
}

// ------------------
// Show profile update success message
function showProfileUpdateSuccess() {
  const successMessage = document.querySelector('.update-success-message');
  if (successMessage) {
    successMessage.style.display = 'block';
    successMessage.style.animation = 'slideIn 0.5s ease';
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
      successMessage.style.animation = 'slideOut 0.5s ease';
      setTimeout(() => {
        successMessage.style.display = 'none';
      }, 500);
    }, 3000);
  }
  
  // Animate profile picture update
  const profilePicture = document.getElementById('profile-picture');
  const updateIndicator = document.querySelector('.update-indicator');
  
  if (profilePicture && updateIndicator) {
    // Add profile update animation
    profilePicture.style.animation = 'profileUpdate 1.2s ease-in-out';
    
    // Show update indicator
    updateIndicator.style.display = 'flex';
    updateIndicator.style.animation = 'pulse 0.5s ease-in-out';
    
    setTimeout(() => {
      updateIndicator.style.display = 'none';
      profilePicture.style.animation = '';
    }, 2000);
  }
}

// ------------------
// Refresh profile data (for cross-tab updates)
function refreshProfileData() {
  // This would typically make an AJAX call to get updated profile data
  // For now, we'll just show the update indicator
  const updateIndicator = document.querySelector('.update-indicator');
  if (updateIndicator) {
    updateIndicator.style.display = 'flex';
    updateIndicator.style.animation = 'pulse 0.5s ease-in-out';
    
    setTimeout(() => {
      updateIndicator.style.display = 'none';
    }, 2000);
  }
  
  // You could also reload the page or make an AJAX call to update the profile data
  // window.location.reload();
}


// ------------------
// Tracker modal
function openTrackerModal() {
  const modalHTML = `
    <div class="modal" id="trackerModal">
      <div class="modal-content">
        <h3>Study Tracker</h3>
        <div id="tracker-list" style="text-align:left; max-height:300px; overflow-y:auto;"></div>
        <div class="modal-buttons">
          <button class="cancel" id="close-tracker">Close</button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", modalHTML);
  const modal = document.getElementById("trackerModal");
  modal.style.display = "flex";
  document.getElementById("close-tracker").addEventListener("click", closeTrackerModal);
  updateTrackerList();
}

function closeTrackerModal() {
  const modal = document.getElementById("trackerModal");
  if (modal) modal.remove();
}

function updateTrackerList() {
  const trackerList = document.getElementById("tracker-list");
  if (!trackerList) return;

  const logs = JSON.parse(localStorage.getItem("studyLogs")) || [];
  if (logs.length === 0) {
    trackerList.innerHTML = "<p>No study logs found.</p>";
    return;
  }

  trackerList.innerHTML = logs
    .map(log => `
      <div class="post-card" style="text-align:left;">
        <b>${log.date}</b> – ${log.task}
        <p>Status: <span style="color:${log.completed ? 'green' : 'red'};">
        ${log.completed ? 'Completed' : 'Pending'}</span></p>
      </div>
    `).join("");
}