<?php
include 'db_connect.php';

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['user_email_address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['user_address'];
    $contact = $_POST['user_contact_number'];
    $role = "user";

    $sql = "INSERT INTO users (fullname, username, user_email_address, password, user_address, user_contact_number, user_role, date_joined)
            VALUES ('$fullname', '$username', '$email', '$password', '$address', '$contact', '$role', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location='login.html';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>