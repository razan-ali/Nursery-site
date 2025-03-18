<?php
session_start();
// Include database connection
include('config.php');

// Check if the parent is logged in
if (!isset($_SESSION['parent_id'])) {
    header('Location: sign_in.php');
    exit();
}

$parent_id = $_SESSION['parent_id']; // Get the logged-in parent ID

// Fetch all reservations for the logged-in parent
$reservations_query = "SELECT r.id, at.date AS reservation_date, at.start_time AS reservation_time, c.name AS caregiver_name, r.duration
                       FROM reservations r
                       JOIN caregivers c ON r.caregiver_id = c.id
                       JOIN available_times at ON r.available_time_id = at.id
                       WHERE r.parent_id = ?";
$stmt = $conn->prepare($reservations_query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$reservations_result = $stmt->get_result();

// Handle cancellation of reservations
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reservation_id']) && isset($_POST['action']) && $_POST['action'] == 'cancel') {
    $reservation_id = $_POST['reservation_id'];

    // Make sure the reservation ID is valid
    if (!empty($reservation_id) && is_numeric($reservation_id)) {
        // Optionally, mark the time slot as available again
        $update_query = "UPDATE available_times SET available = 1 WHERE id = (SELECT available_time_id FROM reservations WHERE id = ?)";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        // Cancel the reservation
        $cancel_query = "DELETE FROM reservations WHERE id = ?";
        $stmt = $conn->prepare($cancel_query);

        if ($stmt === false) {
            die("Error in preparing the delete statement: " . $conn->error);
        }

        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();  // Add this line to execute the query



        header("Location: my_reservation.php");  // Redirect to the reservations page after successful deletion
        exit();  // Ensure no further code is executed
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حجوزاتي</title>
    <link rel="stylesheet" href="styles.css">
    <style> 
/* Unique Styling for Reservations Table */
.reservations-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-bottom:100% ;
}

.reservations-table th, 
.reservations-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

.reservations-table th {
    background: #6a0dad;
    color: white;
    font-size: 16px;
}

.reservations-table tr:nth-child(even) {
    background: #f9f9f9;
}

.reservations-table tr:hover {
    background: #f1f1f1;
}

.cancel-btn {
    background: #d9534f;
    color: white;
    border: none;
    padding: 8px 12px;
    font-size: 14px;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
}

.cancel-btn:hover {
    background: #c9302c;
}

.no-reservations {
    text-align: center;
    font-size: 18px;
    color: #555;
    margin-top: 20px;
}
</style>
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
    <section class="reservations">
        <h1 class="reservations-title">حجوزاتي</h1>
        <?php if ($reservations_result->num_rows > 0): ?>
            <table class="reservations-table">
                <thead>
                    <tr>
                        <th>المعلمة</th>
                        <th>التاريخ</th>
                        <th>الوقت</th>
                        <th>المدة</th>
                        <th>التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($reservation = $reservations_result->fetch_assoc()): ?>
                        <tr>
                            <td style="color:rgb(0, 0, 0);" ><?= $reservation['caregiver_name'] ?></td>
                            <td style="color:rgb(0, 0, 0);"><?= $reservation['reservation_date'] ?></td>
                            <td style="color:rgb(0, 0, 0);"><?= $reservation['reservation_time'] ?></td>
                            <td style="color:rgb(0, 0, 0);"><?= $reservation['duration'] ?> دقيقة</td>
                            <td>
                                <!-- Delete button for each reservation -->
                                <form method="POST">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" name="action" value="cancel" class="cancel-btn" onclick="return confirm('هل أنت متأكد أنك تريد إلغاء هذا الموعد؟')">إلغاء</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-reservations">لا توجد حجوزات في الوقت الحالي.</p>
        <?php endif; ?>
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

</html>