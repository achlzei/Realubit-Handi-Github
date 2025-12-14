<?php
// FILE: admin/manage_products.php - Manage Products/Items CRUD Interface

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit;
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Admin');
$message = '';
$error = ''; 

if (isset($_GET['delete_id'])) {
    $item_id_to_delete = (int)$_GET['delete_id'];
    
    $stmt_img = $conn->prepare("SELECT item_img FROM items WHERE Item_ID = ?");
    $stmt_img->bind_param("i", $item_id_to_delete);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    $item_data = $result_img->fetch_assoc();
    $stmt_img->close();

    $image_to_delete = $item_data['item_img'] ?? null;
    
    $stmt_delete = $conn->prepare("DELETE FROM items WHERE Item_ID = ?");
    $stmt_delete->bind_param("i", $item_id_to_delete);
    
    if ($stmt_delete->execute()) {
        if (!empty($image_to_delete)) {
            $file_path = __DIR__ . '/../uploads/' . $image_to_delete; 
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['message'] = "Item #$item_id_to_delete deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting item: " . $conn->error;
    }
    
    header("Location: manage_products.php");
    exit;
}

$sql = "
    SELECT 
        i.Item_ID, 
        i.items_name, 
        i.item_price, 
        i.item_img, 
        i.stock_quantity, 
        c.category_name 
    FROM 
        items i
    LEFT JOIN 
        category c ON i.category_ID = c.category_ID 
    ORDER BY 
        i.Item_ID DESC
";

$result = $conn->query($sql);

$items = [];
if (!$result) {
    $error = "Database Error: " . $conn->error;
} else {
    $items = $result->fetch_all(MYSQLI_ASSOC);
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <style>
        :root { 
            --color-primary: #7F5539; 
            --color-secondary: #B08968; 
            --color-bg: #F5F3F1; 
        }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--color-bg); 
            margin: 0; 
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
       .sidebar a:hover, .sidebar .active {
            background:  #946547ff; 
            font-weight: bold;
       }
         .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            margin-bottom: 10px;
            text-decoration: none;
            color: var(--color-light);
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar-logout { 
            position: absolute; 
            bottom: 40px; 
            width: 250px; 
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
        }
        .sidebar-logout a:hover { 
            background-color: #e0cabdff; 
        }
        .content-area { 
            flex-grow: 1; 
            padding: 30px; 
            background-color: white; 
        }
        .top-bar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px;
        }
        .top-bar h1 { 
            color: var(--color-primary);
             margin: 0; 
             font-size: 1.5em;
        }
        .add-link { 
            text-decoration: none; 
            background-color: #cdb8a6ff; 
            color: white; 
            padding: 10px 15px; 
            border-radius: 5px; 
            font-weight: bold; 
            transition: background-color 0.3s;
        }
        .add-link:hover { 
            background-color: #9d725bff; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background-color: var(--color-secondary); 
            color: white; 
        }
        tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        .item-img { 
            width: 50px; 
            height: 50px; 
            object-fit: cover; 
            border-radius: 5px; 
            display: block;
        }
        .action-link { 
            text-decoration: none; 
            padding: 6px 12px; 
            border-radius: 4px; 
            margin-right: 5px; 
            display: inline-block;
        }
        .edit-btn { 
            background-color: #B08968; 
            color: white; 
        }
        .delete-btn { 
            background-color: #e65c5c; 
            color: white; 
        }
        .delete-btn:hover { 
            background-color: #cc0000; 
        }
        .edit-btn:hover { 
            background-color: #7F5539; 
        }
        .message { 
            padding: 15px;
            margin-bottom: 20px; 
            border-radius: 5px; 
            font-weight: bold; 
        }
        .success { 
            background-color: #D4EDDA; 
            color: #e98e60ff; 
            border: 1px solid #C3E6CB; 
        }
        .error { 
            background-color: #F8D7DA; 
            color: #721C24; 
            border: 1px solid #F5C6CB; 
        }
    </style>
</head>
<body>

<div class="main-layout">
    
    <div class="sidebar">
        <h2>A Local Handicraft Botique</h2>
        <h3>ADMIN</h3>
        
        <div class="sidebar-nav">
            <a href="index.php">🏠 Dashboard</a>
            <a href="manage_products.php" class="active">🧺 Manage Products</a> 
            <a href="manage_users.php">👥 Manage Users</a>
            <a href="manage_orders.php">📦 Orders</a>
            <a href="manage_messages.php">✉️ Messages</a>
        </div>
        
        <div class="sidebar-logout">
            <a href="../logout.php"><b>Logout</b></a>
        </div>
    </div>

    <div class="content-area">

        <div class="top-bar">
            <h1> Product Management</h1>
            <a href="add_item.php" class="add-link">✚ Add New Item</a>
        </div>

        <?php 

        if (!empty($message)) { 
            echo "<div class='message success'>" . htmlspecialchars($message) . "</div>";
        }
        
        if (!empty($error)) {
             echo "<div class='message error'>" . htmlspecialchars($error) . "</div>";
        }
        ?>

        <?php if (empty($items)): ?>
            <p>No products found. Please add a new item.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th> 
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo $item['Item_ID']; ?></td>
                            
                            <td>
                                <?php if (!empty($item['item_img'])): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($item['item_img']); ?>" 
                                         alt="Item Image" 
                                         class="item-img">
                                <?php else: ?>
                                    <p>No Image</p>
                                <?php endif; ?>
                            </td>
                            
                            <td><?php echo htmlspecialchars($item['items_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></td>
                            <td>₱<?php echo number_format($item['item_price'], 2); ?></td>
                            <td><?php echo $item['stock_quantity']; ?></td> 
                            <td>
                                <a href="edit_item.php?id=<?php echo $item['Item_ID']; ?>" class="action-link edit-btn">Edit</a>
                                
                                <a href="manage_products.php?delete_id=<?php echo $item['Item_ID']; ?>" class="action-link delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this item: <?php echo htmlspecialchars($item['items_name']); ?>? This will also delete the uploaded image.');">Delete</a>
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