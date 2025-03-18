<?php
// Check if the user has selected a role (parent or caregiver)
if (isset($_POST['role'])) {
    $role = $_POST['role'];
    
    if ($role == 'parent') {
        // Redirect to the parent sign-in page
        header('Location: sign_in.php');
        exit();
    } elseif ($role == 'caregiver') {
        // Redirect to the caregiver sign-in page
        header('Location: caregiver-sign-in.php');
        exit();
    } else {
        // If the role is invalid, redirect back to the selection page
        header('Location: sign_in_selection.html');
        exit();
    }
} else {
    // If no role is selected, redirect back to the selection page
    header('Location: sign_in_selection.html');
    exit();
}
?>
