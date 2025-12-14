<?php
// FILE: login.php - Robust Session and Authentication

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; 
session_start();

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT user_ID, password, fullname, user_role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password']) || $password === $row['password']) {
            
            $_SESSION['user_ID'] = $row['user_ID']; 
            $_SESSION['username'] = $username; 
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['user_role'] = strtolower($row['user_role']); 

            if ($_SESSION['user_role'] === 'admin') {
                header("Location: admin/index.php"); 
            } else {
                header("Location: index.php");
            }
            exit;
        } 
        
        else {
            echo "<script>alert('Invalid password!'); window.location='login.html';</script>";
        }
    } 
    
    else {
        echo "<script>alert('User not found!'); window.location='login.html';</script>";
    }
}
?>