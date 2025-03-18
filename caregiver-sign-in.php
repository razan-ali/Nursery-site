<?php
// Include the database connection
include('config.php');

// Initialize the error message
$error_message = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the input values from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare a query to fetch the user from the database
    $sql = "SELECT * FROM caregivers WHERE email = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters
        $stmt->bind_param('s', $email);
        
        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check if the user exists
        if ($result->num_rows > 0) {
            // Fetch the user data
            $row = $result->fetch_assoc();

            // Directly match the password without hashing (not recommended in production)
            if ($password == $row['password']) {
                // Start a session and store caregiver data for future use
                session_start();
                $_SESSION['caregiver_id'] = $row['id'];
                $_SESSION['caregiver_name'] = $row['name'];

                // Redirect to caregiver's dashboard
                header('Location: caregiver_dashboard.php');
                exit();
            } else {
                $error_message = "كلمة المرور غير صحيحة";
            }
        } else {
            $error_message = "البريد الإلكتروني غير مسجل";
        }

        // Close the statement
        $stmt->close();
    } else {
        $error_message = "حدث خطأ في الاتصال بقاعدة البيانات";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول حاضن</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
        <nav>
            <div class="nav-container">
                <div class="logo">
                    <img src="logo.png" alt="Qurrah Day Care">
                </div>
            
                <div class="nav-controls">
                    <a class="account" href="sign_in_selection.html">تسجيل الدخول</a>
                </div>
                
            </div>
        </nav>
    </header>
    
    <main>
        <section class="auth-container">
            <div class="auth-box">
                <h2>تسجيل الدخول</h2>

                <!-- Show the error message if exists -->
                <?php if (!empty($error_message)): ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <!-- The form to submit email and password -->
                <form action="caregiver-sign-in.php" method="POST">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required>
                    
                    <button type="submit" class="auth-btn">تسجيل الدخول</button>
                    <p>ليس لديك حساب؟ <a href="sign_up.html">إنشاء حساب</a></p>
                </form>
            </div>
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
