<?php
// FILE: admin/manage_users.php - Admin User Management Panel

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit;
}

$current_admin_id = $_SESSION['user_ID'] ?? 0; 

if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];

    if ($user_id == $current_admin_id) {
        $_SESSION['error'] = "ERROR: You cannot change the role of your own account!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET user_role = ? WHERE user_ID = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Role for User ID #{$user_id} updated to '{$new_role}' successfully.";
        } else {
            $_SESSION['error'] = "Error updating user role: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: manage_users.php");
    exit;
}

$sql = "SELECT user_ID, username, fullname, user_email_address, user_role, date_joined FROM users WHERE user_ID != ? ORDER BY user_ID DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_admin_id);
$stmt->execute();
$result = $stmt->get_result();
$conn->close();

$roles = ['user', 'admin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
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
        }

        .main-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--color-primary);
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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

        .user-table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .user-table th, .user-table td { 
            padding: 12px 15px;
            text-align: left; 
            border-bottom: 1px solid #eee; 
            font-size: 0.9em; 
        }

        .user-table th { 
            background-color: var(--color-light); 
            color: var(--color-primary); 
            font-weight: bold;
        }

        .user-table tr:hover { 
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
        
        select { 
            padding: 6px; 
            border-radius: 5px; 
            border: 1px solid #ced4da; 
            margin-right: 5px; 
        }

        .btn-update { 
            background-color: #b08968; 
            color: white; 
            border: none; 
            padding: 6px 12px; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: background-color 0.3s;
        }

        .btn-update:hover { background-color: #9c6644; }

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
            <a href="manage_users.php" class="active">👥 Manage Users</a>
            <a href="manage_orders.php">📦 Orders</a>
            <a href="manage_messages.php">✉️ Messages</a>
        </div>

        <div class="sidebar-logout">
            <a href="../logout.php" onclick="return confirm('Are you sure you want to log out?')"><b>Logout</b></a>
        </div>
    </div>

    <div class="content-area">
        
        <div class="header">
            <h1>Manage Customer Accounts</h1>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th> 
                        <th>Role</th>
                        </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['user_ID']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($user['user_email_address']); ?></td> 
                            <td>
                                <form method="POST" style="margin: 0; display: flex; align-items: center;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_ID']; ?>">
                                    <select name="new_role">
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role; ?>" <?php echo (strtolower($user['user_role']) === $role) ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($role); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_role" class="btn-update">Update</button>
                                </form>
                            </td>
                            </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #7F5539; padding: 20px;">No other user Registered.</p>
        <?php endif; ?>

    </div>
</div>

</body>
</html>