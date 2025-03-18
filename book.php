<?php
session_start();
// Include database connection
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['parent_id'])) {
    header('Location: sign_in.php');
    exit();
}

$parent_id = $_SESSION['parent_id']; // Get the logged-in parent ID

// Fetch caregivers from the database
$caregivers_query = "SELECT * FROM caregivers";
$caregivers_result = $conn->query($caregivers_query);

// Fetch available dates for each caregiver
$available_dates_query = "SELECT DISTINCT date, caregiver_id FROM available_times WHERE available = 1";
$available_dates_result = $conn->query($available_dates_query);

$available_dates = [];
while ($row = $available_dates_result->fetch_assoc()) {
    $available_dates[$row['caregiver_id']][] = $row['date'];
}

// Fetch available time slots for caregivers
$available_times = [];
$available_times_query = "SELECT * FROM available_times WHERE available = 1";
$available_times_result = $conn->query($available_times_query);

while ($row = $available_times_result->fetch_assoc()) {
    $available_times[$row['caregiver_id']][$row['date']][] = [
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
        'available' => $row['available']
    ];
}

// Handle booking logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['caregiver_id']) && isset($_POST['selected_time']) && isset($_POST['selected_date'])) {
    $caregiver_id = $_POST['caregiver_id'];
    $reservation_time = $_POST['selected_time'];
    $reservation_date = $_POST['selected_date'];
    $duration = 60; // Default duration, change if needed

    // Check if the time slot is still available before booking
    $check_query = "SELECT * FROM available_times WHERE caregiver_id = ? AND start_time = ? AND date = ? AND available = 1";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("iss", $caregiver_id, $reservation_time, $reservation_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Insert reservation into the database using the logged-in parent's ID
        $insert_query = "INSERT INTO reservations (parent_id, caregiver_id, available_time_id, duration) 
                         VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);

        // Get the available_time_id from available_times
        $available_time_id = $result->fetch_assoc()['id'];

        $stmt->bind_param("iiii", $parent_id, $caregiver_id, $available_time_id, $duration);
        
        if ($stmt->execute()) {
            // Mark the selected time as unavailable
            $update_query = "UPDATE available_times SET available = 0 WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("i", $available_time_id);
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'تم الحجز بنجاح!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطأ أثناء الحجز، يرجى المحاولة مجدداً.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'الموعد غير متاح']);
    }

    exit;
}
?>



<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حجز المعلمات</title>
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>
</head>

<body>
    <header>
        <nav>
            <div class="nav-container">
                <div class="logo">
                    <img src="logo.png" alt="Qurrah Day Care">
                </div>
                <ul class="nav-links">
                    <li><a href="book.php">حجز موعد</a></li>
                    <li><a href="my_reservation.php">حجوزاتي</a></li>
                  
                </ul>
                <div class="nav-controls">
                <a href="logout.php" class="account">تسجيل الخروج</a>
            </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="booking">
            <h1 style="color:rgb(0, 0, 0);">حجز موعد مع المعلمات</h1>
            <label for="teacher-select" class="choose">اختر المعلمة:</label>
            <select id="teacher-select">
                <option value="">-- اختر معلمة --</option>
                <?php while ($caregiver = $caregivers_result->fetch_assoc()) { ?>
                    <option value="<?= $caregiver['id'] ?>"><?= $caregiver['name'] ?></option>
                <?php } ?>
            </select>

            <div id="available-days" class="hidden">
            <h2 style="color:rgb(0, 0, 0);">اختر اليوم</h2> <!-- Change #ff6600 to your desired color -->

                <div id="day-slots"></div>
            </div>

            <div id="available-times" class="hidden">
                <h2 style="color:rgb(0, 0, 0);">الأوقات المتاحة</h2>
                <div id="time-slots"></div>
            </div>

            <button id="book-button" class="hidden">حجز الموعد</button>
        </section>
    </main>
    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="logo-text.png" alt="خطوات صغيرة">
            </div>
            <div class="footer-section services">
                <h3>خدماتنا</h3>
                <ul>
                    <li><a href="#">خطوات صغيرة في بيئات العمل</a></li>
                    <li><a href="#">حضانات خطوات صغيرة</a></li>
                    <li><a href="#">حدائق خطوات صغيرة</a></li>
                </ul>
            </div>
            <div class="footer-section resources">
                <h3>المصادر</h3>
                <ul>
                    <li><a href="#">الأسئلة الشائعة</a></li>
                    <li><a href="#">المدونة</a></li>
                </ul>
            </div>
            <div class="footer-section contact">
                <h3>تواصل معنا</h3>
                <p>info@little_steps.com.sa</p>
            </div>
        </div>
    </footer>
</body>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const teacherSelect = document.getElementById("teacher-select");
        const availableDaysDiv = document.getElementById("available-days");
        const daySlotsDiv = document.getElementById("day-slots");
        const availableTimesDiv = document.getElementById("available-times");
        const timeSlotsDiv = document.getElementById("time-slots");
        const bookButton = document.getElementById("book-button");

        // Dynamic PHP variable passed to JavaScript
        const availableDates = <?= json_encode($available_dates); ?> || {};
        const availableTimes = <?= json_encode($available_times); ?> || {}; // Use the updated available times

        let selectedDate = null;
        let selectedTime = null;

        teacherSelect.addEventListener("change", function() {
            const selectedTeacher = teacherSelect.value;
            daySlotsDiv.innerHTML = "";
            timeSlotsDiv.innerHTML = "";
            if (selectedTeacher && availableDates[selectedTeacher]) {
                // Display available dates for the selected caregiver
                const dates = availableDates[selectedTeacher];
                dates.forEach(date => {
                    const dateBtn = document.createElement("button");
                    dateBtn.textContent = date;
                    dateBtn.classList.add("day-slot");
                    dateBtn.style.margin = "5px";
                    dateBtn.style.padding = "10px";
                    dateBtn.style.backgroundColor = "#6a86c7";
                    dateBtn.style.color = "white";
                    dateBtn.style.border = "none";
                    dateBtn.style.borderRadius = "5px";
                    dateBtn.style.cursor = "pointer";

                    // Add event listener to select the date
                    dateBtn.addEventListener("click", function() {
                        // Deselect previous date
                        const prevSelectedDate = document.querySelector(".day-slot.selected");
                        if (prevSelectedDate) {
                            prevSelectedDate.classList.remove("selected");
                        }

                        // Highlight the selected date
                        dateBtn.classList.add("selected");
                        selectedDate = dateBtn.textContent;

                        // Clear previously displayed time slots
                        timeSlotsDiv.innerHTML = "";

                        // Fetch the time slots for the selected caregiver and date
                        const timeSlotsForDate = availableTimes[selectedTeacher] && availableTimes[selectedTeacher][selectedDate] || [];

                        // Loop through the time slots and display them
                        timeSlotsForDate.forEach(slot => {
                            const timeBtn = document.createElement("button");
                            timeBtn.textContent = slot.start_time;
                            timeBtn.classList.add("time-slot");
                            timeBtn.style.margin = "5px";
                            timeBtn.style.padding = "10px";
                            timeBtn.style.backgroundColor = "#6a86c7";
                            timeBtn.style.color = "white";
                            timeBtn.style.border = "none";
                            timeBtn.style.borderRadius = "5px";
                            timeBtn.style.cursor = "pointer";

                            if (slot.available === 0) {
                                timeBtn.classList.add("booked");
                                timeBtn.disabled = true; // Disable booked time slots
                                timeBtn.style.backgroundColor = "#333";
                                timeBtn.style.cursor = "not-allowed";
                            } else {
                                timeBtn.addEventListener("click", function() {
                                    // Deselect previous time
                                    const prevSelectedTime = document.querySelector(".time-slot.selected");
                                    if (prevSelectedTime) {
                                        prevSelectedTime.classList.remove("selected");
                                    }

                                    // Highlight the selected time
                                    timeBtn.classList.add("selected");
                                    selectedTime = timeBtn.textContent;

                                    // Show the book button
                                    bookButton.classList.remove("hidden");
                                });
                            }
                            timeSlotsDiv.appendChild(timeBtn);
                        });

                        // Show the time slots section
                        availableTimesDiv.classList.remove("hidden");
                    });

                    // Add the date button to the day slots section
                    daySlotsDiv.appendChild(dateBtn);
                });

                // Show the available days section
                availableDaysDiv.classList.remove("hidden");
            } else {
                availableDaysDiv.classList.add("hidden");
                availableTimesDiv.classList.add("hidden");
                bookButton.classList.add("hidden");
            }
        });

        bookButton.addEventListener("click", function() {
            const selectedTeacher = teacherSelect.value;

            if (selectedTime && selectedTeacher && selectedDate) {
                console.log("Selected Teacher:", selectedTeacher);
                console.log("Selected Date:", selectedDate);
                console.log("Selected Time:", selectedTime);

                // Send the booking data via AJAX
                const data = new FormData();
                data.append("caregiver_id", selectedTeacher);
                data.append("selected_time", selectedTime);
                data.append("selected_date", selectedDate);

                fetch(window.location.href, { // Sending request to the same file
                        method: "POST",
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            alert("تم حجز الموعد بنجاح");
                            location.reload(); // Reload page to update the availability
                        } else {
                            alert("خطأ: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Network error:", error);
                        alert("حدث خطأ في الاتصال بالسيرفر.");
                    });

            } else {
                alert("يرجى اختيار الوقت والتاريخ أولاً.");
            }
        });

    });
</script>

</html>