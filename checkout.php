<?php
// FILE: checkout.php 

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_ID'];
$error = '';
$cart_items_data = [];
$total_cart_price = 0;

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$item_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($item_ids), '?'));

if ($conn) {
    if ($conn->ping()) {
        $stmt = $conn->prepare("SELECT Item_ID, item_price, items_name FROM items WHERE Item_ID IN ($placeholders)");
        $types = str_repeat('i', count($item_ids)); 
        $stmt->bind_param($types, ...$item_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $item_id = $row['Item_ID'];
            $quantity = $_SESSION['cart'][$item_id];
            $subtotal = $quantity * $row['item_price'];
            
            $row['quantity'] = $quantity;
            $row['subtotal'] = $subtotal;
            $cart_items_data[] = $row;
            $total_cart_price += $subtotal;
        }
        $stmt->close();
    } else {
        $error = "Database connection lost during item fetching.";
    }
} else {
    $error = "Database connection error at start.";
}

if (isset($_POST['place_order']) && empty($error) && !empty($cart_items_data)) {
    
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    
    if (empty($shipping_address) || empty($contact_number)) {
        $error = "Shipping address and contact number are required.";
    } elseif ($conn && $conn->ping()) { 
        
        $conn->begin_transaction(); 
        $order_successful = true;

        $stmt_insert = $conn->prepare("
            INSERT INTO orders (user_ID, item_ID, quantity, total_price, order_date, order_status, shipping_address)
            VALUES (?, ?, ?, ?, NOW(), 'Pending', ?)
        ");
        
        foreach ($cart_items_data as $item) {
            $item_id = $item['Item_ID'];
            $quantity = $item['quantity'];
            $subtotal = $item['subtotal'];
            
            if (!$stmt_insert->bind_param("iiids", $user_id, $item_id, $quantity, $subtotal, $shipping_address)) {
                 $order_successful = false;
                 break; 
            }
            
            if (!$stmt_insert->execute()) {
                $order_successful = false;
                break; 
            }
        }
        
        $stmt_insert->close();
        
        if ($order_successful) {
            $conn->commit();
            
            unset($_SESSION['cart']); 
            $_SESSION['order_success'] = "Order placed successfully! Your orders can now be tracked in the 'My Orders' page.";
            
            header("Location: orders.php"); 
            exit;
            
        } else {
            $conn->rollback();
            $error = "An error occurred while placing the order. Please try again. (Database Error: " . $conn->error . ")";
            error_log("Order Placement Failed for User $user_id: " . $conn->error);
        }
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
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Checkout</title>
    <style>
        :root { --color-primary: #7F5539; --color-secondary: #B08968; --color-text: #4B2E18; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #F5F3F1; 
            color: var(--color-text); 
            margin: 0; 
            padding: 0;
            overflow-y: scroll; 
        }

        header {
            background-color: var(--color-primary);
            color: white;
            padding: 12.5px 20px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky; top: 0; z-index: 1000;
        }

        header h1 { 
            font-size: 1.5em; 
            margin: 0; 
            color: white !important; 
            text-shadow: none;
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
        nav a:hover, nav a.active { 
            background-color: #9C6644; 
        }

        .container { 
            max-width: 650px; 
            margin: 20px auto; 
            padding: 20px; 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
        }

        h2 { 
            color: var(--color-primary); 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .order-summary { 
            border: 1px solid #E3D5CA; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
        }
        .total { 
            font-size: 15px; 
            color: var(--color-primary); 
            font-weight: bold; 
            text-align: right; 
            margin-top: 10px; 
        }
        label { 
            display: block; 
            margin-top: 15px; 
            margin-bottom: 5px; 
            font-weight: bold; 
            color: var(--color-primary); 
        }
        textarea, input[type="text"] { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            box-sizing: border-box; 
        }
        .place-order-btn { 
            width: 100%; 
            padding: 15px; 
            background-color: #B08968; 
            color: white; 
            border: none; 
            border-radius: 50px; 
            margin-top: 5px; 
            font-weight: bold; 
            transition: background-color 0.3s; 
        }
        .place-order-btn:hover { 
            background-color: rgba(172, 224, 165, 0.97);
        }
        .error-message { 
            color: red; 
            text-align: center; 
            font-weight: bold; 
            margin-bottom: 15px; 
        }

    </style>
</head>
<body>

<header>
    <h1><b>A Local Handicraft</b></h1>
    <nav>
        <a href="index.php">Home</a> 
        <a href="product.php">Products</a> 
        <a href="cart.php">Cart</a> 
        <a href="orders.php">My Orders</a> 
        <a href="contact.php">Contact</a>
        <a href="logout.php">Logout (<?php echo $fullname; ?>)</a> 
    </nav>
</header>

<div class="container">
    <h2>Finalize Your Order</h2>
    
    <?php if (!empty($error)): ?>
        <p class="error-message">❌ <?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST">
        
        <h3><b>Shipping Details</b></h3>
        
        <label for="shipping_address">Shipping Address:</label>
        <textarea id="shipping_address" name="shipping_address" required rows="4" placeholder="Enter your full shipping address"><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
        
        <label for="contact_number">Contact Number:</label>
        <input type="text" id="contact_number" name="contact_number" required placeholder="e.g., 09xxxxxxxxx" value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
        
        <h3 style="margin-top: 10px;"><b>Order Summary</b></h3>
        <div class="order-summary">
            <?php foreach ($cart_items_data as $item): ?>
                <p><?php echo htmlspecialchars($item['items_name']); ?> (x<?php echo $item['quantity']; ?>) <span style="float:right;">₱<?php echo number_format($item['subtotal'], 2); ?></span></p>
            <?php endforeach; ?>
            <hr>
            <div class="total">
                TOTAL: ₱<?php echo number_format($total_cart_price, 2); ?>
            </div>
        </div>
        <div>
            <button type="button" id="placeOrderBtn" class="place-order-btn">Place order & Pay via Cash on Delivery</button>
        </div>

    </form>
</div>

<div id="checkoutConfirmModal" class="w3-modal" aria-hidden="true">
    <div class="w3-modal-content w3-card-4 w3-animate-top" role="dialog" style="max-width: 450px;">
        <header class="w3-container w3-padding" style="background-color: var(--color-primary); color: white;">
            <h3>Confirm Your Order</h3>
        </header>

        <div class="w3-container w3-padding">
            <p>You are about to place your order using Cash on Delivery (COD).</p>
            <p>Please review your shipping details and order summary before confirming.</p>
        </div>

        <footer class="w3-container w3-padding w3-light-grey w3-right-align">
            <button class="w3-button w3-border w3-margin-right" 
                    style="background-color: rgba(249, 160, 160, 0.97); color: #4B2E18;" 
                    onclick="closeModal()">Cancel</button>
            
            <button class="w3-button" id="confirmSubmitBtn" style="background-color: rgba(217, 252, 213, 0.97); color: #4B2E18;">Yes, Place Order Now</button>
        </footer>
    </div>
</div>

<script>
    const form = document.querySelector('form');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    const modal = document.getElementById('checkoutConfirmModal');

    function openModal() {
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    placeOrderBtn.addEventListener('click', function(e) {
        if (!form.reportValidity()) {
            return; 
        }
        
        openModal();
    });

    confirmSubmitBtn.addEventListener('click', function(e) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'place_order';  
        hiddenInput.value = '1';
        form.appendChild(hiddenInput);

        form.submit();
        closeModal();
    });

    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
</script>

</body>
</html>