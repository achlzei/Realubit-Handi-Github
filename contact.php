<?php
// FILE: contact.php

include 'db_connect.php'; 
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL); 

$message = '';
$error = '';
$name = htmlspecialchars($_POST['sender_name'] ?? '');
$email = htmlspecialchars($_POST['sender_email'] ?? '');
$subject = htmlspecialchars($_POST['subject'] ?? '');
$content = htmlspecialchars($_POST['message_content'] ?? '');

if (isset($_POST['send_message'])) {
    if (empty($name) || empty($email) || empty($content)) {
        $error = "ERROR: Please fill in all required fields (Name, Email, Message).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "ERROR: Invalid email format.";
    } else {
        $date_sent = date('Y-m-d H:i:s'); 
        $stmt = $conn->prepare("INSERT INTO messages (sender_name, sender_email, subject, message_content, date_sent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $subject, $content, $date_sent); 
        if ($stmt->execute()) {
            $message = "Thank you! Your message was sent successfully.";
            $name = $email = $subject = $content = '';
        } else {
            $error = "Database Error: " . $stmt->error; 
        }
        $stmt->close();
    }
    $conn->close(); 
}

$is_logged_in = isset($_SESSION['username']);
$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Guest');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Handi Craft</title>
    <style>
        :root {
            --color-primary: #7F5539; 
            --color-secondary: #B08968; 
            --color-light: #E3D5CA; 
            --color-text: #4B2E18; 
            --color-bg: #F5F3F1; 
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #F5F3F1; 
            color: #4B2E18; 
            margin: 0; 
            padding: 0; 
        }

        header {
            background-color: var(--color-primary);
            color: white;
            padding: 13px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 1.5em;
            margin: 0;
        }
        nav { display: flex; gap: 20px; }

        nav a {
            color: white;
            text-decoration: none;
            padding: 3px 8px; 
            border-radius: 4px;
            transition: background-color 0.3s;
            font-weight: 500;
            font-size: 0.95em; 
            margin: 0;
        }
        nav a:hover, nav a.active { background-color: #9C6644; }

        .contact-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fffaf6;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #7f5539;
            text-align: center;
            margin-bottom: 25px;
        }
        input[type="text"], input[type="email"], textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #d6ccc2;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1em;
        }
        textarea {
            resize: vertical;
            height: 150px;
        }
        .form-message.success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .form-message.error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #b08968;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background: #9c6644;
        }
    </style>
</head>
<body>

<header>
    <h1><b>A Local Handicraft</b></h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="product.php">Products</a>
        <a href="cart.php">Cart 🛒</a> 
        <?php if ($is_logged_in): ?>
            <a href="orders.php">My Orders</a>
        <?php endif; ?>
        <a href="contact.php" class="active">Contact</a>
        <a href="logout.php">Logout (<?php echo $fullname; ?>)</a> 
    </nav>
</header>

<div class="contact-container">
    <h2>Send Us a Message</h2>

    <?php if ($message): ?>
        <div class="form-message success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="form-message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="sender_name" placeholder="Your Full Name" value="<?php echo $name; ?>" required>
        <input type="email" name="sender_email" placeholder="Your Email Address" value="<?php echo $email; ?>" required>
        <input type="text" name="subject" placeholder="Subject (Optional)" value="<?php echo $subject; ?>">
        <textarea name="message_content" placeholder="Your Message" required><?php echo $content; ?></textarea>
        <button type="submit" name="send_message">Send Message</button>
    </form>
</div>

</body>
</html>