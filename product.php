<?php
// FILE: product.php (Customer View for Products)

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; 
session_start(); 

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'User');


$sql = "
    SELECT 
        i.Item_ID, 
        i.items_name, 
        i.item_price, 
        i.item_description, 
        i.item_img,
        c.category_name 
    FROM 
        items i
    JOIN 
        category c ON i.category_ID = c.category_ID
    ORDER BY 
        i.items_name ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error in product.php: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handi Craft - Products</title>
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
            background-image: url(https://tinyurl.com/2j9uxz7x);
            color: var(--color-text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            background-size: 210vh;
        }

        header {
            background-color: var(--color-primary);
            color: white;
            padding: 11px 22px;
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
            transition: 0.3s;
            font-weight: 500;
            font-size: 0.95em; 
            display: inline-block;
        }

        nav a:hover, nav a[href="product.php"] {
            background-color: #9C6644;
        }

        .product-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 30px;
            text-align: center;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 250px)); 
            gap: 25px; 
            justify-content: center; 
        }

        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            font-size: 1.2em;
            color: #C94C4C;
            border: 1px solid var(--color-light);
            background-color: white;
            border-radius: 10px;
        }

        .product-card {
            background: transparent;
            box-shadow: none; 
            position: relative;
            height: 400px; 
            cursor: pointer; 
            perspective: 1000px; 
            border: none; 
        }
        
        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.8s;
            transform-style: preserve-3d;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }
        
        .product-card.is-flipped .card-inner {
            transform: rotateY(180deg);
        }

        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden; 
            backface-visibility: hidden;
            border-radius: 15px;
            padding: 20px;
            box-sizing: border-box;
            background-color: white;
            display: flex; 
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .card-front img {
            width: 100%;
            max-height: 60%; 
            object-fit: contain; 
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .tap-hint {
            font-size: 0.8em;
            color: var(--color-secondary);
            margin-top: 10px;
            font-style: italic;
        }
        
        .card-back {
            transform: rotateY(180deg); 
            background-color: #f7f0e6; 
            justify-content: flex-start; 
            text-align: center;
            padding-top: 30px;
        }

        .card-back h4 {
            color: var(--color-primary);
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .card-back .description {
            font-size: 0.9em;
            color: #6D5643;
            max-height: 150px; 
            overflow-y: auto; 
            text-align: justify;
            padding: 0 5px;
            margin-bottom: 15px;
        }
        
        .add-to-cart-btn {
            display: block;
            width: 80%;
            margin-top: auto; 
            padding: 10px 15px; 
            background-color: var(--color-secondary);
            color: white;
            border: none; 
            border-radius: 50px; 
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .add-to-cart-btn:hover {
            background-color: var(--color-primary);
        }

        .category {
            margin-bottom: 0px; 
            font-size: 0.85em;
        }

        .price, .price-back {
            font-size: 1.2em; 
            color: #C94C4C; 
            font-weight: bold;
            margin: 5px 0;
        }
        
        .toast { 
            position: fixed; 
            right: 20px;
            bottom: 20px; 
            z-index: 9999; 
            min-width: 260px; 
        }
        .w3-modal-content .w3-button:focus { 
            outline: 3px solid rgba(0,0,0,0.12); 
        }

    </style>
</head>
<body class="w3-light-grey">

<header>
    <h1><b>A Local Handicraft</b></h1>
    <nav>
        <a href="index.php" title="Home">Home</a>
        <a href="product.php" title="Products">Products</a>
        <a href="cart.php" title="Cart">Cart 🛒</a> 
        <a href="orders.php" title="My Orders">My Orders</a> 
        <a href="contact.php" title="Contact">Contact</a>
        <a href="logout.php" title="Logout">Log out(<?php echo $fullname; ?>)</a> 
    </nav>
</header>

<div class="product-container">
    <h2><b>Available Handcrafted Items</b></h2>
    <div class="product-grid">

        <?php
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                
                $placeholder_path = 'img/placeholder.png'; 
                $image_path_server = __DIR__ . '/uploads/' . $row['item_img'];
                $image_path_url = 'uploads/' . $row['item_img'];

                if (!empty($row['item_img']) && file_exists($image_path_server)) {
                    $final_image_src = $image_path_url;
                } else {
                    $final_image_src = $placeholder_path;
                }
                
                ?>
                <div class="product-card">
                    <div class="card-inner"> 
                        
                        <div class="card-front"> 
                            <img src="<?php echo $final_image_src; ?>" alt="<?php echo htmlspecialchars($row['items_name']); ?>">
                            
                            <div style="text-align: center; width: 100%;">
                                <div class="category"><?php echo htmlspecialchars($row['category_name']); ?></div>
                                <h3><?php echo htmlspecialchars($row['items_name']); ?></h3>
                                <p class="price">₱<?php echo number_format($row['item_price'], 2); ?></p>
                                <span class="tap-hint">Tap/Click for Details ⓘ</span>
                            </div>
                        </div>
                        
                        <div class="card-back"> 
                            <span class="category"><?php echo htmlspecialchars($row['category_name']); ?></span>
                            <h4><?php echo htmlspecialchars($row['items_name']); ?></h4>
                            
                            <p class="description"><?php echo htmlspecialchars($row['item_description']); ?></p>
                            
                            <p class="price-back">Price: ₱<?php echo number_format($row['item_price'], 2); ?></p>
                            
                            <button 
                                class="add-to-cart-btn" 
                                data-item-id="<?php echo $row['Item_ID']; ?>"
                                data-item-name="<?php echo htmlspecialchars($row['items_name']); ?>"
                            >
                                Add to Cart
                            </button>
                        </div>
                        
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="no-products"</div>';
        }
        ?>
    </div>
</div>

<?php
mysqli_close($conn);
?>

<div id="confirmModal" class="w3-modal" aria-hidden="true">
  <div class="w3-modal-content w3-card-4 w3-animate-top" role="dialog" aria-labelledby="confirmTitle" aria-describedby="confirmDesc" style="max-width: 400px;">
    <header class="w3-container" style="background-color: var(--color-primary); color: white;">
      <h3 id="confirmTitle">✅ Item Added to Cart!</h3>
    </header>

    <div class="w3-container w3-padding" id="confirmDesc">
      <p><strong id="modalItemName">Item Name Here</strong> — added to your cart.</p>
      <p>Quantity: <span id="qtyText">1</span></p>
    </div>

    <footer class="w3-container w3-padding w3-light-grey">
      <button class="w3-button w3-white w3-border" id="continueBtn">Continue Shopping</button>
      <a href="cart.php" class="w3-button w3-right" style="background-color: var(--color-secondary); color: white;" id="checkoutBtn">Go to Checkout</a>
    </footer>
  </div>
</div>

<div id="toast" class="toast" role="status" aria-live="polite" style="display:none;">
  <div class="w3-container w3-card-4 w3-padding w3-round w3-white w3-border">
    <div class="w3-row w3-small">
      <div class="w3-col s2"><span class="w3-large">✅</span></div>
      <div class="w3-col s10">
        <strong>Added to cart</strong>
        <div id="toastText" class="w3-text-grey">1 item added</div>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.classList.contains('add-to-cart-btn')) {
                    return; 
                }
                
                this.classList.toggle('is-flipped');
            });
        });
        const modal = document.getElementById('confirmModal');
        const toast = document.getElementById('toast');
        const continueBtn = document.getElementById('continueBtn');
        const allAddButtons = document.querySelectorAll('.add-to-cart-btn');

        const modalItemName = document.getElementById('modalItemName');
        const qtyText = document.getElementById('qtyText');
        const toastText = document.getElementById('toastText');

        function openModal(itemName, quantity) {
            modalItemName.textContent = itemName;
            qtyText.textContent = quantity;
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden','false');
        }

        function closeModal() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden','true');
        }

        function showToast(itemName, quantity, timeout = 2800) {
            toastText.textContent = `${quantity}x ${itemName} added.`;
            toast.style.display = 'block';
            toast.classList.add('w3-animate-bottom');
            
            setTimeout(() => {
                toast.classList.remove('w3-animate-bottom');
                toast.style.display = 'none';
            }, timeout);
        }

        allAddButtons.forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault(); 
                
                const itemId = this.getAttribute('data-item-id');
                const itemName = this.getAttribute('data-item-name');
                const quantity = 1; 
                
                this.disabled = true;
                this.textContent = 'Adding...';

                try {
                    const response = await fetch(`cart.php?action=add&item_id=${itemId}&ajax=1`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'  
                        }
                    });

                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        openModal(result.item_name, result.quantity);
                        showToast(result.item_name, result.quantity);
                        
                    } else {
                        alert('Error adding item: ' + result.message);
                    }

                } catch (error) {
                    console.error('Fetch error:', error);
                    alert('An unexpected error occurred while adding the item.');
                } finally {
                    this.disabled = false;
                    this.textContent = 'Add to Cart';
                    
                    const card = this.closest('.product-card');
                    if(card && card.classList.contains('is-flipped')) {
                        card.classList.remove('is-flipped');
                    }
                }
            });
        });

        continueBtn.addEventListener('click', closeModal);
        window.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                closeModal();
            }
        });
    });
</script>

</body>
</html>