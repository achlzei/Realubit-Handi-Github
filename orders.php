<?php
// FILE: orders.php - User's Order History

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? 'user') !== 'user') {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_ID'];
$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Customer');
$orders = [];
$error = '';

$order_success_message = '';
if (isset($_SESSION['order_success'])) {
    $order_success_message = $_SESSION['order_success'];
    unset($_SESSION['order_success']); 
}

$selected_tab = $_GET['status'] ?? 'All'; 
$where_clause = 'o.user_ID = ?';

// FILTERS for tabs
switch ($selected_tab) {
    case 'To Ship': 
        $where_clause .= " AND o.order_status IN ('Pending', 'Preparing')"; 
        break;
    case 'To Receive': 
        $where_clause .= " AND o.order_status = 'Shipping'"; 
        break;
    case 'Completed': 
        $where_clause .= " AND o.order_status = 'Delivered'"; 
        break;
    case 'Cancelled': 
        $where_clause .= " AND o.order_status = 'Cancelled'"; 
        break;
    // All = no extra conditions
}

// FETCH ORDERS
if ($conn && $conn->ping()) {
    $stmt_orders = $conn->prepare("
        SELECT o.Order_ID, o.order_date, o.order_status, o.quantity, o.total_price, 
               i.items_name, i.item_img, o.shipping_address 
        FROM orders o 
        LEFT JOIN items i ON o.item_ID = i.Item_ID 
        WHERE $where_clause 
        ORDER BY o.Order_ID DESC
    ");

    if ($user_id > 0) {
        $stmt_orders->bind_param("i", $user_id);
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();
        while ($row = $result_orders->fetch_assoc()) { $orders[] = $row; }
        $stmt_orders->close();
    }
} else { 
    $error = "Database connection error."; 
}

if ($conn) { $conn->close(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Order History</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

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
            margin: 0; padding: 0;
        }
        
        header {
            background-color: var(--color-primary);
            color: white;
            padding: 4.5px 20px;
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
        }

        nav { display: flex; gap: 20px; }

        nav a {
            color: white;
            text-decoration: none;
            padding: 3px 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        nav a:hover, nav a.active { background-color: #9C6644; }

        .container {
            max-width: 1000px; 
            margin: 40px auto; 
            padding: 20px; 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 { 
            color: var(--color-primary); 
            text-align: center; 
            margin-bottom: 20px; 
            padding-bottom: 15px;
        }

        .back-link { 
            display: inline-block; 
            margin-bottom: 20px; 
            color: var(--color-secondary); 
            font-weight: bold;
            text-decoration: none;
        }

        .status-tabs { 
            display: flex; 
            justify-content: space-around; 
            border-bottom: 2px solid var(--color-light); 
            margin-bottom: 20px; 
        }
        .tab-link { 
            padding: 10px 15px; 
            text-decoration: none; 
            color: var(--color-text); 
            font-weight: 600; 
            border-bottom: 3px solid transparent; 
        }
        .tab-link:hover { color: var(--color-primary); }
        .tab-link.active { 
            color: var(--color-primary); 
            border-bottom: 3px solid var(--color-primary); 
        }

        .order-card { 
            border: 1px solid var(--color-light); 
            padding: 15px; 
            margin-bottom: 25px; 
            border-radius: 8px; 
            background-color: #fcfcfc; 
        }

        .order-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 10px; 
            border-bottom: 1px dashed var(--color-light); 
            padding-bottom: 10px; 
        }

        .item-row { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            margin-bottom: 10px; 
        }

        .item-img { 
            width: 60px; 
            height: 60px; 
            object-fit: cover; 
            border-radius: 5px; 
            border: 1px solid #ddd; 
        }

        .item-details { flex-grow: 1; }

        .item-name { 
            font-weight: bold; 
        }

        .item-price { 
            min-width: 100px; 
            font-weight: bold; 
            text-align: right; 
        }

        .status-footer { 
            text-align: right; 
            padding-top: 10px; 
            border-top: 1px solid var(--color-light); 
        }

        .status-Pending { color: #FFA500; }
        .status-Preparing { color: #007BFF; }
        .status-Shipping { color: #28A745; }
        .status-Delivered { color: #17A2B8; }
        .status-Cancelled { color: #DC3545; }
    </style>
</head>
<body>

<header>
    <h1><b>A Local Handicraft</b></h1>
    <nav>
        <a href="index.php">Home</a> 
        <a href="product.php">Products</a> 
        <a href="cart.php">Cart 🛒</a> 
        <a href="orders.php" class="active">My Orders</a> 
        <a href="contact.php">Contact</a>
        <a href="logout.php">Logout (<?php echo $fullname; ?>)</a> 
    </nav>
</header>

<div class="container">
    <a href="index.php" class="back-link">← Back to Dashboard</a>

    <!-- FIXED TITLE -->
    <h1>My Order History</h1>

    <?php if (!empty($error)): ?>
        <p style="color: red; text-align: center; font-weight: bold;">
            ⚠️ Error: <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <div class="status-tabs">
        <?php
        $tabs = ['All', 'To Ship', 'To Receive', 'Completed', 'Cancelled'];
        foreach ($tabs as $tab_name) {
            $is_active = ($selected_tab === $tab_name) ? 'active' : '';
            echo '<a href="orders.php?status=' . urlencode($tab_name) . '" class="tab-link ' . $is_active . '">' . $tab_name . '</a>';
        }
        ?>
    </div>
    
    <?php if (empty($orders)): ?>
        <p style="text-align: center; padding: 50px; color: #7F5539; font-size: 1.1em;">
            No orders found under this category.
        </p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <h2>Order #<?php echo $order['Order_ID']; ?></h2>
                <p>Date Placed: <?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
            </div>
            
            <div class="item-row">
                <?php 
                    $image_name = htmlspecialchars($order['item_img'] ?? 'default.jpg');
                    $final_image_src = file_exists(__DIR__ . '/uploads/' . $image_name) 
                        ? 'uploads/' . $image_name 
                        : 'uploads/placeholder.jpg';
                ?>
                <img src="<?php echo $final_image_src; ?>" alt="Item Image" class="item-img">
                
                <div class="item-details">
                    <p class="item-name"><?php echo htmlspecialchars($order['items_name'] ?? 'Item Not Found'); ?></p>
                    <p>Quantity: x<?php echo $order['quantity']; ?></p>
                    <p>Ship To: <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                </div>

                <p class="item-price">₱<?php echo number_format($order['total_price'], 2); ?></p>
            </div>

            <div class="status-footer">
                <span class="status-label">Total Item Price: ₱<?php echo number_format($order['total_price'], 2); ?></span>
                <?php $status_class = str_replace(' ', '', htmlspecialchars($order['order_status'])); ?>
                <span class="status-label">Current Status: 
                    <span class="status-<?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($order['order_status']); ?>
                    </span>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Success Modal -->
<div id="orderSuccessModal" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-top" style="max-width: 450px;">
        <header class="w3-container w3-padding" style="background-color: #c4ebcdff;">
            <h3>✅ Order Placed Successfully!</h3>
        </header>
        <div class="w3-container w3-padding">
            <p id="successMessageContent"></p>
        </div>
        <footer class="w3-container w3-padding w3-light-grey w3-right-align">
            <button class="w3-button w3-green" onclick="closeOrderSuccessModal()">Close</button>
        </footer>
    </div>
</div>

<script>
    const successMessage = "<?php echo $order_success_message; ?>";
    const successModal = document.getElementById('orderSuccessModal');
    function closeOrderSuccessModal() { successModal.style.display = 'none'; }

    document.addEventListener('DOMContentLoaded', function() {
        if (successMessage) {
            document.getElementById('successMessageContent').innerText = successMessage;
            successModal.style.display = 'block';
        }
    });

    window.onclick = function(event) { 
        if (event.target == successModal) 
            closeOrderSuccessModal(); 
    }
</script>

</body>
</html>
