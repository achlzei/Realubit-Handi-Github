<?php

// FILE: admin/edit_item.php - Edit an Existing Product (UPDATED with Stock Quantity)



ini_set('display_errors', 1);

error_reporting(E_ALL);



include '../db_connect.php'; 

session_start(); 



// 1. Security Check

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? '') !== 'admin') {

    header("Location: ../login.html");

    exit;

}



// Check if Item_ID is provided

if (!isset($_GET['id']) || empty($_GET['id'])) {

    header("Location: manage_products.php");

    exit;

}



$item_id = intval($_GET['id']);



// 2. Fetch Item Data (for pre-filling the form)

// ⭐ MODIFIED SELECT: Idinagdag ang stock_quantity

$stmt = $conn->prepare("

    SELECT 

        items_name, item_price, stock_quantity, item_description, item_img, category_ID 

    FROM 

        items 

    WHERE 

        Item_ID = ?

");

$stmt->bind_param("i", $item_id);

$stmt->execute();

$result = $stmt->get_result();



if ($result->num_rows === 0) {

    // If item not found

    $_SESSION['error'] = "Item not found.";

    header("Location: manage_products.php");

    exit;

}

$item_data = $result->fetch_assoc();

$stmt->close();





// 3. Handle Form Submission (UPDATE)

if (isset($_POST['update_item'])) {

    $name = trim($_POST['items_name']);

    $price = $_POST['item_price'];

    $stock_quantity = (int)$_POST['stock_quantity']; // ⭐ NEW: Kunin ang stock quantity

    $description = trim($_POST['item_description']);

    $category = $_POST['category_ID'];

    $current_img = $item_data['item_img']; // Keep current image name



    // Validation

    if ($stock_quantity < 0) {

        $_SESSION['error'] = "Stock quantity cannot be negative.";

        header("Location: edit_item.php?id=" . $item_id);

        exit;

    }



    // Handle Image Upload

    $img_name = $current_img; // Default to current image

    

    if (isset($_FILES['item_img']) && $_FILES['item_img']['error'] == 0) {

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (in_array($_FILES['item_img']['type'], $allowed_types)) {

            $img_name = uniqid('item_') . '_' . time() . '_' . basename($_FILES['item_img']['name']); // Used uniqid for better filename security/uniqueness

            // Tiyaking tama ang path: up one level (..) to get to 'uploads'

            $target_path = __DIR__ . '/../uploads/' . $img_name; 

            

            if (move_uploaded_file($_FILES['item_img']['tmp_name'], $target_path)) {

                // SUCCESS: New image uploaded. Delete old image if it exists.

                if (!empty($current_img) && $current_img !== $img_name) {

                    $old_file = __DIR__ . '/../uploads/' . $current_img;

                    if (file_exists($old_file)) {

                        unlink($old_file);

                    }

                }

            } else {

                $_SESSION['error'] = "Error uploading new image file.";

                header("Location: edit_item.php?id=" . $item_id);

                exit;

            }

        } else {

            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF, WEBP allowed.";

            header("Location: edit_item.php?id=" . $item_id);

            exit;

        }

    }



    // Update database

    // ⭐ MODIFIED UPDATE: Idinagdag ang stock_quantity

    $stmt = $conn->prepare("

        UPDATE items 

        SET items_name = ?, item_price = ?, stock_quantity = ?, item_description = ?, item_img = ?, category_ID = ? 

        WHERE Item_ID = ?

    ");

    // Binding parameters: s (name), d (price), i (stock), s (desc), s (img), i (category_ID), i (item_ID)

    $stmt->bind_param("sdisisi", $name, $price, $stock_quantity, $description, $img_name, $category, $item_id);



    if ($stmt->execute()) {

        $_SESSION['message'] = "Item updated successfully!";

        header("Location: manage_products.php");

        exit;

    } else {

        $_SESSION['error'] = "Database error: " . $stmt->error;

        header("Location: edit_item.php?id=" . $item_id);

        exit;

    }

    $stmt->close();

}



// Fetch categories for the dropdown menu

$categories = $conn->query("SELECT category_ID, category_name FROM category ORDER BY category_name ASC");

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Item - Admin</title>

    <style>

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F5F3F1; color: #4B2E18; margin: 0; padding: 20px; display: flex; justify-content: center; }

        .container { background: #FFFFFF; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }

        h2 { color: #7F5539; text-align: center; margin-bottom: 25px; }

        label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: bold; color: #7F5539; }

        input[type="text"], input[type="number"], textarea, select {

            width: 100%;

            padding: 10px;

            margin-bottom: 15px;

            border: 1px solid #D6CCC2;

            border-radius: 6px;

            box-sizing: border-box;

        }

        textarea { resize: vertical; height: 100px; }

        .current-image { text-align: center; margin-bottom: 20px; }

        .current-image img { max-width: 200px; height: auto; border: 1px solid #D6CCC2; padding: 5px; border-radius: 8px; }

        button { 

            width: 100%;

            padding: 12px;

            background: #b08968; 

            color: white; 

            border: none; 

            border-radius: 6px;

            cursor: pointer;

            font-weight: bold;

            font-size: 1.1em;

            transition: background 0.3s;

        }

        button:hover { background: #9c6644; }

        .back-link { display: block; margin-bottom: 20px; color: #7f5539; text-decoration: none; font-weight: bold; }

        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }

        .success { background-color: #D4EDDA; color: #155724; border: 1px solid #C3E6CB; }

        .error { background-color: #F8D7DA; color: #721C24; border: 1px solid #F5C6CB; }

    </style>

</head>

<body>

<div class="container">

    <a href="manage_products.php" class="back-link">← Back to Product Management</a>

    <h2>Edit Item: <?php echo htmlspecialchars($item_data['items_name']); ?></h2>



    <?php 

    if (isset($_SESSION['message'])) {

        echo "<div class='message success'>" . $_SESSION['message'] . "</div>";

        unset($_SESSION['message']);

    }

    if (isset($_SESSION['error'])) {

        echo "<div class='message error'>" . $_SESSION['error'] . "</div>";

        unset($_SESSION['error']);

    }

    ?>

    

    <div class="current-image">

        <p>Current Image:</p>

        <?php if (!empty($item_data['item_img'])): ?>

            <img src="../uploads/<?php echo htmlspecialchars($item_data['item_img']); ?>" alt="Current Item Image">

        <?php else: ?>

            <p>No Image Uploaded.</p>

        <?php endif; ?>

    </div>

    

    <form method="POST" enctype="multipart/form-data">

        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">

        

        <label for="items_name">Item Name:</label>

        <input type="text" name="items_name" value="<?php echo htmlspecialchars($item_data['items_name']); ?>" required>



        <label for="item_description">Description:</label>

        <textarea name="item_description"><?php echo htmlspecialchars($item_data['item_description']); ?></textarea>

        

        <label for="item_price">Price (e.g., 120.00):</label>

        <input type="number" step="0.01" name="item_price" value="<?php echo htmlspecialchars($item_data['item_price']); ?>" min="0" required>

        

                <label for="stock_quantity">Stock Quantity:</label>

        <input type="number" name="stock_quantity" value="<?php echo htmlspecialchars($item_data['stock_quantity']); ?>" min="0" required>

        

        <label for="category_ID">Category:</label>

        <select name="category_ID" required>

            <option value="">Select Category</option>

            <?php while($cat = $categories->fetch_assoc()): ?>

                <option value="<?php echo $cat['category_ID']; ?>" 

                    <?php echo ($item_data['category_ID'] == $cat['category_ID']) ? 'selected' : ''; ?>>

                    <?php echo htmlspecialchars($cat['category_name']); ?>

                </option>

            <?php endwhile; ?>

        </select>

        

        <label for="item_img">Replace Image (Optional):</label>

        <input type="file" name="item_img" accept="image/*">

        

        <button type="submit" name="update_item">Save Changes</button>

    </form>

</div>

</body>

</html>