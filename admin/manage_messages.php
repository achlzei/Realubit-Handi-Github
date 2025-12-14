<?php
// FILE: admin/manage_messages.php - Admin Message Management Panel

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit;
}

$admin_name = htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Admin User');

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $stmt = $conn->prepare("DELETE FROM messages WHERE message_ID = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Message ID #{$delete_id} successfully deleted.";
    } else {
        $_SESSION['error'] = "Error deleting message: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: manage_messages.php");
    exit;
}

$sql = "SELECT message_ID, sender_name, sender_email, subject, message_content, date_sent FROM messages ORDER BY date_sent DESC";
$result = $conn->query($sql);

if (!$result) {
    $_SESSION['error'] = "Database Error: " . $conn->error;
    $result = null; 
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Messages</title>
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
            background-color: var(--color-bg); 
            margin: 0;
            padding: 0;
            color: var(--color-text);
            overflow-y: scroll; /* Prevents scrollbar jumping */
        }
        
        .main-layout {
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR STYLES (Standardized) --- */
        .sidebar {
            width: 250px;
            background-color: var(--color-primary);
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .sidebar h2 {
            font-size: 1.8em;
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 2px solid var(--color-secondary);
            padding-bottom: 15px;
        }
        
        .sidebar h3 {
            font-size: 1.3em;
            text-align: center;
            margin-top: 5px;
            color: var(--color-secondary);
        }

        .sidebar-nav {
            margin-top: 30px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            margin-bottom: 10px;
            text-decoration: none;
            color: var(--color-bg);
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: #9C6644; 
            color: white;
            font-weight: bold;
        }

        .sidebar-logout {
            position: absolute;
            bottom: 40px;
            width: 290px;
            text-align: center;
        }

        .sidebar-logout a {
            display: block;
            padding: 12px;
            background-color: var(--color-secondary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-right: 40px; 
        }

        .sidebar-logout a:hover {
            background-color: #9C6644;
        }

        .content-area {
            flex-grow: 1;
            padding: 30px;
            background-color: var(--color-bg);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #e3d5ca;
            padding-bottom: 10px;
        }

        .header h1 {
            color: var(--color-primary);
            font-size: 1.4em;
            margin: 0;
        }

        .message-table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .message-table th, .message-table td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
            font-size: 0.9em; 
            vertical-align: top; 
        }

        .message-table th { 
            background-color: var(--color-light); 
            color: var(--color-primary); 
            font-weight: bold;
        }

        .message-table tr:hover { 
            background-color: #f9f9f9; 
        }
        
        .btn { 
            padding: 5px 10px; 
            border: none; 
            border-radius: 5px; 
            color: white; 
            text-decoration: none; 
            cursor: pointer; 
            display: inline-block;
        }

        .delete { background: #f44336; }
        .delete:hover { background: #d32f2f; }
        
        .message-preview { 
            max-height: 50px; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
        }

        .message { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            font-weight: bold; 
            text-align: center;
        }

        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="main-layout">

    <div class="sidebar">
        <h2>A Local Handicraft Botique</h2>
        <h3>ADMIN</h3>

        <div class="sidebar-nav">
            <a href="index.php">🏠 Dashboard</a>
            <a href="manage_products.php">🧺 Manage Products</a>
            <a href="manage_users.php">👥 Manage Users</a>
            <a href="manage_orders.php">📦 Orders</a>
            <a href="manage_messages.php" class="active">✉️ Messages</a>
        </div>

        <div class="sidebar-logout">
            <a href="../logout.php" onclick="return confirm('Are you sure you want to log out?')"><b>Logout</b></a>
        </div>
    </div>

    <div class="content-area">
        
        <div class="header">
            <h1>Manage Customer Messages</h1>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="message-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Sender</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message Preview</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($msg = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $msg['message_ID']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($msg['date_sent'])); ?></td>
                            <td><?php echo htmlspecialchars($msg['sender_name']); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($msg['sender_email']); ?>" style="color: var(--color-primary);"><?php echo htmlspecialchars($msg['sender_email']); ?></a></td>
                            <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                            <td>
                                <div class="message-preview" title="<?php echo htmlspecialchars($msg['message_content']); ?>">
                                    <?php echo htmlspecialchars($msg['message_content']); ?>
                                </div>
                            </td>
                            <td>
                                <a href="manage_messages.php?delete_id=<?php echo $msg['message_ID']; ?>" 
                                   class="btn delete" 
                                   onclick="return confirm('Are you sure you want to delete this message?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #7F5539;">No messages available.</p>
        <?php endif; ?>

    </div>
</div>

</body>
</html>