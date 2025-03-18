<?php
// Database credentials
$host = 'localhost';  
$dbname = 'nurs_database';  
$username = 'root';  
$password = ''; 

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
