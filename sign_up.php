<?php
// Include database connection
include('config.php');

// Initialize the error and success messages
$error_message = '';
$success_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input fields to prevent SQL injection and XSS
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $phone_1 = mysqli_real_escape_string($conn, $_POST['phone_1']);
    $phone_2 = mysqli_real_escape_string($conn, $_POST['phone_2']);

    // Check if passwords match
    if ($password != $confirm_password) {
        $error_message = "كلمات المرور لا تتطابق";
    } else {
        // Check if email already exists
        $email_check = "SELECT * FROM parents WHERE email = ?";
        $stmt = $conn->prepare($email_check);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "البريد الإلكتروني مستخدم بالفعل";
        } else {
            // Insert the new parent data into the database (without password hashing)
            $insert_query = "INSERT INTO parents (name, email, password, phone_1, phone_2) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sssss", $name, $email, $password, $phone_1, $phone_2);

            if ($stmt->execute()) {
                $success_message = "تم إنشاء الحساب بنجاح!";
                header("Location: sign_in.php");
                exit();
            } else {
                $error_message = "حدث خطأ أثناء التسجيل";
            }

            // Close the statement
            $stmt->close();
        }
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
    <title>إنشاء حساب</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-container">
                <div class="logo">
                    <img src="logo.png" alt="Qurrah Day Care">
                </div>
                <ul class="nav-links">
                    <li><a href="index.html">الرئيسية</a></li>
                    <li><a href="#">الحاضنات</a></li>
                    <li><a href="#">تواصل معنا</a></li>
                    <li><a href="#">التوظيف</a></li>
                </ul>
                <div class="nav-controls">
                    <a class="account" href="sign_in.html">تسجيل الدخول</a>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <section class="auth-container">
            <div class="auth-box">
                <h2>التسجيل</h2>
                
                <!-- Show success or error message at the top -->
                <?php if (!empty($error_message)): ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php elseif (!empty($success_message)): ?>
                    <p style="color: green;"><?php echo $success_message; ?></p>
                <?php endif; ?>

                <form action="#" method="POST">
                    <label for="name">اسم المستخدم</label>
                    <input type="text" id="name" name="name" required> 

                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required>

                    <label for="password">تأكيد كلمة المرور  </label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    
                    <label for="phone_1">رقم الهاتف 1</label>
                    <input type="text" id="phone_1" name="phone_1" required>

                    <label for="phone_2">رقم الهاتف 2</label>
                    <input type="text" id="phone_2" name="phone_2" required>
                    
                    <button type="submit" class="auth-btn">التسجيل</button>
                    <p> لديك حساب؟ <a href="sign_in.php">تسجيل الدخول</a></p>
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
                    <li><a href="#">قرة في بيئات العمل</a></li>
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
