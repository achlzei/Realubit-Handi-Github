<?php
// FILE: admin/manage_orders.php - Admin Order Management Panel

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit;
}

$message = '';
$error = '';
$available_statuses = ['Pending', 'Preparing', 'Shipping', 'Delivered', 'Cancelled'];

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id']; 
    $new_status = $_POST['new_status'];
    
    if (!in_array($new_status, $available_statuses)) {
        $error = "Invalid status selected.";
    } else {
        $stmt_update = $conn->prepare("UPDATE orders SET order_status = ? WHERE Order_ID = ?");
        $stmt_update->bind_param("si", $new_status, $order_id);
        
        if ($stmt_update->execute()) {
            $message = "Order #{$order_id} status successfully updated to '{$new_status}'.";
        } else {
            $error = "Error updating status: " . $conn->error;
        }
        $stmt_update->close();
    }
}

$orders = [];
$stmt_orders = $conn->prepare("
    SELECT 
        o.Order_ID, o.order_date, o.order_status, o.quantity, o.total_price,
        o.shipping_address, o.contact_number, 
        u.fullname,
        i.items_name
    FROM orders o
    JOIN users u ON o.user_ID = u.user_ID 
    LEFT JOIN items i ON o.item_ID = i.Item_ID
    ORDER BY o.Order_ID DESC
");

$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

while ($row = $result_orders->fetch_assoc()) {
    $orders[] = $row;
}

$stmt_orders->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
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
            color: var(--color-text); 
            margin: 0; 
            padding: 0; 
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
            width: 250px; 
            text-align: 
            center; 
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
        }
        .sidebar-logout a:hover { 
            background-color: #9C6644; 
        }
        .content-area { 
            flex-grow: 1; 
            padding: 30px; 
            background-color: var(--color-bg); 
        }
        .top-bar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px; 
            border-bottom: 2px solid #e3d5ca;
            padding-bottom: 10px; 
        }
        .top-bar h1 { 
            color: var(--color-primary); 
            font-size: 1.4em;
            margin: 0; 
        }
        .message { 
            padding: 10px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-weight: bold; 
            border-radius: 5px; 
        }
        .message-success { 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .message-error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        .orders-table-wrap { 
            overflow-x: auto; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
            font-size: 0.9em; 
        }
        th { 
            background-color: var(--color-light); 
            color: var(--color-primary); 
            font-weight: bold; 
        }
        tr:hover {
            background-color: #f9f9f9; 
        }
        .status-form { 
            display: flex; 
            align-items: center; 
        }
        .status-form select { 
            padding: 5px; 
            border-radius: 4px; 
            border: 1px solid #ccc; 
            font-size: 0.9em;
         }
        .status-form button { 
            padding: 5px 10px; 
            background-color: #7F5539; 
            color: white; border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin-left: 5px; 
            font-size: 0.9em; 
        }
        .status-form button:hover { 
            background-color: #4B2E18; 
        }

        .status-Pending { color: #FFA500; font-weight: bold; }
        .status-Preparing { color: #007BFF; font-weight: bold; }
        .status-Shipping { color: #28A745; font-weight: bold; }
        .status-Delivered { color: #17A2B8; font-weight: bold; }
        .status-Cancelled { color: #DC3545; font-weight: bold; }

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
            <a href="manage_orders.php" class="active">📦 Orders</a>
            <a href="manage_messages.php">✉️ Messages</a>
        </div>

        <div class="sidebar-logout">
            <a href="../logout.php"><b>Logout</b></a>
        </div>
    </div>

    <div class="content-area">
        <div class="top-bar">
            <h1>Manage Customer Orders</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message message-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="message message-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p style="text-align: center; padding: 30px; color: #7F5539;">No orders found.</p>
        <?php else: ?>
            <div class="orders-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Item Ordered</th>
                            <th>Qty</th>
                            <th>Total Price</th>
                            <th>Date</th>
                            <th>Shipping Address</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['Order_ID']; ?></td>
                            <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($order['items_name'] ?? 'Item Not Found'); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($order['order_date'])); ?></td>
                            <td><?php echo htmlspecialchars($order['shipping_address']) . ' (' . htmlspecialchars($order['contact_number']) . ')'; ?></td>
                            
                            <td class="status-<?php echo str_replace(' ', '', htmlspecialchars($order['order_status'])); ?>">
                                <strong><?php echo htmlspecialchars($order['order_status']); ?></strong>
                            </td>
                            
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['Order_ID']; ?>">
                                    <select name="new_status" required>
                                        <?php foreach ($available_statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" 
                                                <?php echo ($order['order_status'] === $status) ? 'selected' : ''; ?>>
                                                <?php echo $status; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>