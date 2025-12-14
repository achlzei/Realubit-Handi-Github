<?php
// FILE: cart.php - Shopping Cart Page

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID'])) {
    header("Location: login.html");
    exit;
}

$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['item_id'])) {
    $add_item_id = (int)$_GET['item_id'];
    $item_name = "Item";
    $current_quantity = 0;
    
    if ($conn) {
        $stmt_name = $conn->prepare("SELECT items_name FROM items WHERE Item_ID = ?");
        $stmt_name->bind_param("i", $add_item_id);
        $stmt_name->execute();
        $result_name = $stmt_name->get_result();
        
        if ($row_name = $result_name->fetch_assoc()) {
            $item_name = htmlspecialchars($row_name['items_name']);
        }
        $stmt_name->close();
    } 
    
    if (isset($_SESSION['cart'][$add_item_id])) {
        $_SESSION['cart'][$add_item_id]++;
    } else {
        $_SESSION['cart'][$add_item_id] = 1;
    }
    
    $current_quantity = $_SESSION['cart'][$add_item_id];

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'item_id' => $add_item_id,
            'item_name' => $item_name,
            'quantity' => $current_quantity,
            'message' => 'Successfully added to cart.'
        ]);
        exit; 
    }

    header("Location: cart.php");
    exit;
}

if (isset($_GET['action']) && isset($_GET['item_id']) && !$is_ajax) {
    $item_id = (int)$_GET['item_id'];
    $action = $_GET['action'];

    if (isset($_SESSION['cart'][$item_id])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$item_id]++;
        } elseif ($action === 'decrease') {
            if ($_SESSION['cart'][$item_id] > 1) {
                $_SESSION['cart'][$item_id]--;
            } else {
                unset($_SESSION['cart'][$item_id]);
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    
    header("Location: cart.php");
    exit;
}

$cart_items = [];
$total_cart_price = 0;

if (!empty($_SESSION['cart'])) {
    $item_ids = array_keys($_SESSION['cart']);
    $valid_item_ids = array_filter($item_ids, 'is_numeric'); 
    
    if (!empty($valid_item_ids) && $conn) {
        $placeholders = implode(',', array_fill(0, count($valid_item_ids), '?'));
        
        $stmt = $conn->prepare("SELECT Item_ID, items_name, item_price, item_img FROM items WHERE Item_ID IN ($placeholders)");
        $types = str_repeat('i', count($valid_item_ids)); 
        
        $stmt->bind_param($types, ...$valid_item_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $item_id = $row['Item_ID'];
            if (isset($_SESSION['cart'][$item_id])) {
                $quantity = $_SESSION['cart'][$item_id];
                $subtotal = $quantity * $row['item_price'];
                $row['quantity'] = $quantity;
                $row['subtotal'] = $subtotal;
                $cart_items[] = $row;
                $total_cart_price += $subtotal;
            }
        }
        $stmt->close();
    }
}

if ($conn) {
    $conn->close();
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'User');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shopping Cart</title>
    <style>
        :root {
            --color-primary: #7F5539; 
            --color-secondary: #B08968; 
            --color-light: #E3D5CA; 
            --color-text: #4B2E18; 
            --color-bg: #F5F3F1; 
        }
        
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--color-bg); 
            color: var(--color-text);
            margin: 0; padding: 0; 
        }
        
        header { 
            background-color: var(--color-primary); 
            color: white; 
            padding: 13px 21px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);  
        }
        header h1 { 
            font-size: 1.5em; 
            margin: 0; 
        }

        nav { 
            display: flex; 
            gap: 20px; 
        }
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
        nav a:hover, nav a[href="cart.php"] { 
            background-color: #9C6644; 
        }

        .container { 
            max-width: 900px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #fffaf6; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
        }
        h2 { 
            color: #7F5539; 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .cart-item { 
            display: flex; 
            align-items: center; 
            border-bottom: 1px solid #E3D5CA; 
            padding: 15px 0; 
        }
        .cart-item:last-child { 
            border-bottom: none; 
        }
        .item-info { 
            flex-grow: 1; 
            margin-left: 20px; 
        }
        .item-info h3 { 
            margin: 0 0 5px 0; 
            color: #9C6644; 
        }
        .item-info p { 
            margin: 0; 
            font-size: 0.9em; 
        }
        .item-image { 
            width: 80px; 
            height: 80px; 
            object-fit: cover; 
            border-radius: 6px; 
            border: 1px solid #D6CCC2; 
        }
        
        .quantity-control { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .quantity-btn {
            display: inline-block; 
            width: 28px; 
            height: 28px; 
            line-height: 28px; 
            text-align: center;
            background-color: var(--color-light);
            color: var(--color-primary); 
            text-decoration: none;
            border-radius: 50%; 
            font-weight: bold; 
            border: 1px solid var(--color-secondary);
            transition: background-color 0.2s; 
            user-select: none; 
            font-size: 1.1em;
        }
        .quantity-btn:hover { 
            background-color: var(--color-secondary); 
            color: white; 
        }
        .quantity-display { 
            padding: 0 10px; 
            font-weight: bold; 
            color: var(--color-text); 
            min-width: 20px; 
            text-align: center; 
        }

        .item-quantity { 
            width: 120px; 
            text-align: center; 
        }
        .item-subtotal { 
            width: 100px; 
            text-align: right; 
            font-weight: bold; 
            font-size: 1.1em; 
        }
        .item-actions { 
            width: 80px; text-align: center; 
        }
        .remove-btn { 
            color: #f44336; 
            text-decoration: none; 
            font-size: 1.5em; 
            transition: color 0.2s; 
        }
        .remove-btn:hover { 
            color: #d32f2f; 
        }
        .empty-cart { 
            text-align: center; 
            padding: 50px; 
            color: #7F5539; 
            font-size: 1.1em; 
        }

        .cart-summary { 
            text-align: right; 
            padding-top: 20px; 
            border-top: 2px solid #9C6644; 
            margin-top: 20px; 
        }
        .total-price { 
            font-size: 20px; 
            color: #7F5539; 
            font-weight: bold; 
        }
        
        .checkout-btn { 
            display: inline-block; 
            padding: 12px 25px; 
            background-color: #B08968; 
            color: white; 
            text-decoration: none; 
            border-radius: 50px; 
            margin-top: 15px; 
            font-weight: bold; 
            transition: background-color 0.3s;
        }
        .checkout-btn:hover { 
            background-color: #7F5539; 
        }
        .back-link { 
            display: inline-block; 
            margin-right: 20px; 
            color: #7F5539; 
            text-decoration: none; 
        }
    </style>
</head>
<body>

<header>
    <h1>A Local Handicraft</h1>
    <nav>
        <a href="index.php">Home</a> 
        <a href="product.php">Products</a>
        <a href="cart.php">Cart 🛒</a> 
        <a href="orders.php">My Orders</a> 
        <a href="contact.php">Contact</a>
        <a href="logout.php">Logout (<?php echo $fullname; ?>)</a> 
    </nav>
</header>

<div class="container">
    <h2>Your Shopping Cart 🛒</h2>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            Your cart is empty. <a href="product.php" class="back-link">Start Shopping!</a>
        </div>
    <?php else: ?>
        
        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                
                <?php 
                    $image_name = $item['item_img'];
                    $image_path_url = 'uploads/' . $image_name;
                    $placeholder_path = 'uploads/placeholder.jpg'; 
                    $image_path_server = __DIR__ . '/uploads/' . $image_name; 

                    if (!empty($image_name) && file_exists($image_path_server)) {
                        $final_image_src = $image_path_url;
                    } else {
                        $final_image_src = $placeholder_path;
                    }
                ?>
                <img src="<?php echo $final_image_src; ?>" alt="<?php echo htmlspecialchars($item['items_name']); ?>" class="item-image">
                
                <div class="item-info">
                    <h3><?php echo htmlspecialchars($item['items_name']); ?></h3>
                    <p>Unit Price: ₱<?php echo number_format($item['item_price'], 2); ?></p>
                </div>
                
                <div class="item-quantity">
                    <div class="quantity-control">
                        <a href="cart.php?action=decrease&item_id=<?php echo $item['Item_ID']; ?>" class="quantity-btn">-</a>
                        <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                        <a href="cart.php?action=increase&item_id=<?php echo $item['Item_ID']; ?>" class="quantity-btn">+</a>
                    </div>
                </div>
                
                <div class="item-subtotal">
                    ₱<?php echo number_format($item['subtotal'], 2); ?>
                </div>
                
                <div class="item-actions">
                    <a href="cart.php?action=remove&item_id=<?php echo $item['Item_ID']; ?>" class="remove-btn" title="Remove Item">×</a>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="cart-summary">
            <div class="total-price">
                Total (Before Shipping): ₱<?php echo number_format($total_cart_price, 2); ?>
            </div>
            
            <a href="product.php" class="back-link">← Continue Shopping</a>
            <a href="checkout.php" class="checkout-btn">BUY NOW / Proceed to Checkout</a> 
        </div>

    <?php endif; ?>
</div>

</body>
</html>