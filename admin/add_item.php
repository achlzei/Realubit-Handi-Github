<?php
// FILE: admin/add_item.php - Add New Product Form (UPDATED WITH SIDEBAR LAYOUT)

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db_connect.php'; 
session_start();

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? '') != 'admin') {
    header("Location: ../login.html");
    exit;
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Admin');
$error = '';

// --- Handle form submission (Image Upload and Database Insert) ---
if(isset($_POST['add'])) {
    
    $name = trim($_POST['items_name']);
    $price = $_POST['item_price'];
    $description = trim($_POST['item_description']);
    $category_id = $_POST['category_ID']; 
    $stock_quantity = $_POST['stock_quantity']; 
    $img_name = null;

    if (isset($_FILES['item_img']) && $_FILES['item_img']['error'] == 0) {
        $raw_img_name = basename($_FILES['item_img']['name']);
        $img_name = uniqid('item_') . '_' . time() . '_' . $raw_img_name;
        $img_tmp = $_FILES['item_img']['tmp_name'];
        
        // ⭐ Path for upload: Mula sa admin/ papuntang uploads/
        $upload_path = '../uploads/' . $img_name; 
        
        if (!move_uploaded_file($img_tmp, $upload_path)) {
            $error = "File upload failed. Check FOLDER PERMISSIONS (dapat 777) of the '../uploads/' directory. PHP Error: " . $_FILES['item_img']['error'];
            $img_name = null; 
        }
    } else {
        if ($_FILES['item_img']['error'] !== 4) { 
             $error = "Image upload failed with error code: " . $_FILES['item_img']['error'];
        }
    }

    if (empty($error)) {
        $sql_insert = "
            INSERT INTO items 
            (items_name, item_description, item_price, item_img, category_ID, stock_quantity) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("ssdsii", $name, $description, $price, $img_name, $category_id, $stock_quantity);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "New item added successfully!"; // Gamitin ang flash message
            header("Location: manage_products.php");
            exit;
        } else {
            $error = "Database Error: " . $conn->error;
            if (!empty($img_name) && file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
        $stmt->close();
    }
}

// Fetch Categories for the dropdown menu
$categories_result = $conn->query("SELECT category_ID, category_name FROM category ORDER BY category_name ASC");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item - Admin</title>
    <style>
        /* I-KOPYA ang CSS ng manage_products.php para sa CONSISTENCY at LAYOUT (Sidebar) */
        :root {
            --color-primary: #7F5539; 
            --color-secondary: #B08968; 
            --color-light: #E3D5CA; 
            --color-text: #4B2E18; 
            --color-bg: #F5F3F1; 
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--color-bg); color: var(--color-text); margin: 0; padding: 0; }
        
        /* --- Layout (Sidebar) --- */
        .main-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: var(--color-primary); color: white; padding: 20px; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); flex-shrink: 0;}
        .sidebar h2 { font-size: 1.5em; text-align: center; margin-bottom: 5px; border-bottom: 2px solid var(--color-secondary); padding-bottom: 15px; }
        .sidebar h3 { font-size: 1.3em; text-align: center; margin-top: 5px; color: var(--color-secondary); }
        .sidebar-nav { margin-top: 30px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 12px 15px; margin-bottom: 10px; text-decoration: none; color: var(--color-light); border-radius: 5px; transition: background-color 0.3s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background-color: #9C6644; color: white; }
        .sidebar-nav a.active { font-weight: bold; }
        .sidebar-logout { margin-top: 50px; text-align: center; } /* Adjusted position */
        .sidebar-logout a { display: block; padding: 10px; background-color: var(--color-secondary); color: white; border-radius: 5px; text-decoration: none; font-weight: bold; transition: background-color 0.3s; }
        .sidebar-logout a:hover { background-color: #9C6644; }

        .content-area { flex-grow: 1; padding: 30px; background-color: var(--color-bg); display: flex; justify-content: center; align-items: flex-start; }
        
        /* --- Form Specific Styles --- */
        .container { background: white; padding: 30px 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 450px; margin-top: 50px; }
        h2 { text-align: center; color: var(--color-primary); margin-bottom: 25px; }
        form label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: bold; color: #555; }
        form input[type="text"], form input[type="number"], form textarea, form select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        form textarea { resize: vertical; height: 80px; }
        form input[type="file"] { margin-bottom: 20px; border: none; padding: 0; }
        button { width: 100%; padding: 12px; background: var(--color-secondary); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #9C6644; }
        .error-message { color: red; text-align: center; margin-bottom: 15px; font-weight: bold; border: 1px solid red; padding: 10px; border-radius: 4px; background-color: #F8D7DA; }
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
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="content-area">
        <div class="container">
            <h2>Add New Item</h2>
            
            <?php if (!empty($error)): ?>
                <p class="error-message">Error: <?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data"> 
                <label>Item Name:</label>
                <input type="text" name="items_name" placeholder="Item Name" required>
                
                <label>Description:</label>
                <textarea name="item_description" placeholder="Description"></textarea>
                
                <label>Price (₱):</label>
                <input type="number" step="0.01" name="item_price" placeholder="Price (e.g., 120.00)" required>
                
                <label>Stock Quantity:</label>
                <input type="number" name="stock_quantity" placeholder="Quantity in Stock" required min="0">

                <label>Item Image (required):</label>
                <input type="file" name="item_img" accept="image/*" required> 
                
                <label>Category:</label>
                <select name="category_ID" required>
                    <option value="">Select Category</option>
                    <?php 
                    if ($categories_result && $categories_result->num_rows > 0) {
                        while($cat = $categories_result->fetch_assoc()): 
                        ?>
                        <option value="<?= $cat['category_ID'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endwhile; 
                    }
                    ?>
                </select>
                
                <button type="submit" name="add">Add Item</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>