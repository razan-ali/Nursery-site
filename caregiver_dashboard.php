<?php
session_start();
// Include database connection
include('config.php');

// Check if the caregiver is logged in
if (!isset($_SESSION['caregiver_id'])) {
    // If not logged in, redirect to the sign-in page
    header('Location: sign_in_caregiver.php');
    exit();
}

// Fetch the caregiver's ID from the session
$caregiver_id = $_SESSION['caregiver_id']; // Get the caregiver ID from the session

// Initialize the appointments array for each day
$appointments = [
    "الأحد" => [],
    "الإثنين" => [],
    "الثلاثاء" => [],
    "الأربعاء" => [],
    "الخميس" => [],
    "الجمعة" => []
];

// Prepare the query to fetch the caregiver's bookings from the database, along with parent names
$sql = "SELECT at.date, at.start_time, at.end_time, p.name AS parent_name 
        FROM reservations r
        JOIN available_times at ON r.available_time_id = at.id
        JOIN parents p ON r.parent_id = p.id 
        WHERE r.caregiver_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $caregiver_id); // Bind the caregiver's ID to the query
$stmt->execute();
$result = $stmt->get_result();

// Organize the bookings by day
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get the day of the week from the reservation time
        $reservation_date = strtotime($row['date']);
        $day_of_week = date('l', $reservation_date); // Get the day of the week (e.g., Sunday, Monday)

        // Map the days in English to Arabic
        $days_map = [
            'Sunday' => 'الأحد',
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت'
        ];

        // Format the appointment as a string and include the parent's name
        $appointment_time = date('g:i A', strtotime($row['start_time'])); // Convert to 12-hour format (e.g., 3:00 PM)
        $appointment = $appointment_time . ' - ' . $row['parent_name']; // Example: "3:00 PM - John Doe"

        // Add the booking to the appropriate day
        $appointments[$days_map[$day_of_week]][] = [
            'appointment' => $appointment, 
            'color' => '#6a86c7'  // Set a color for reserved slots
        ];
    }
} else {
    echo "لا توجد حجوزات لهذا اليوم.";
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المعلمة</title>
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
            
            <div class="nav-controls">
                <a href="logout.php" class="account">تسجيل الخروج</a>
            </div>
        </div>
    </nav>
</header>

<main>
    <section class="dashboard">
        <h1>جدول المواعيد المحجوزة</h1>
        <div class="calendar" id="calendar"></div>
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

<!-- The JavaScript code -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const appointments = <?php echo json_encode($appointments); ?>;
        
        const days = ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة"];
        const calendar = document.getElementById("calendar");

        days.forEach(day => {
            const dayDiv = document.createElement("div");
            dayDiv.classList.add("day");
            dayDiv.innerHTML = `<h3>${day}</h3>`;

            if (appointments[day].length > 0) {
                appointments[day].forEach(slotData => {
                    const slotDiv = document.createElement("div");
                    slotDiv.classList.add("slot", "reserved");  // Add reserved class for blue background
                    slotDiv.textContent = slotData.appointment;
                    dayDiv.appendChild(slotDiv);
                });
            } else {
                const emptySlot = document.createElement("p");
                emptySlot.textContent = "لا توجد حجوزات";
                emptySlot.style.color = "red";
                dayDiv.appendChild(emptySlot);
            }

            calendar.appendChild(dayDiv);
        });
    });
</script>

</body>
</html>
