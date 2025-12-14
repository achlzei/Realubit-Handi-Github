<?php
// FILE: index.php - Dynamic User/Admin Landing Page

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); 

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$user_role = strtolower($_SESSION['user_role'] ?? 'user');
$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Guest');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A Local HandiCraft - Dashboard</title>
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
            line-height: 1.6;
        }

        header {
            background-color: var(--color-primary);
            color: white;
            padding: 10px 20px;
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
        }

        nav a:hover {
            background-color: #9C6644;
        }

        .hero {
            background-image: url(https://tinyurl.com/exzdjrz6);
            background-size: 210vh;
            padding: 120px 20px;
            border-bottom: 5px solid var(--color-secondary);
            max-width: 2000px;
            margin: 0 auto;
            text-align: center; 
        }
        
        .hero-content-wrap {
            max-width: 800px; 
            margin: 0 auto 15px auto; 
        }
        
        .hero h2 {
            font-family: "Great Vibes", cursive;
            font-size: 3em;
            color: #fefafaff;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 1.3em;
            color:  #fffcfcff;
        }

        .hero-visuals-wrap {
            display: flex;
            justify-content: center; 
            align-items: center;    
            max-width: 1000px;
            margin: 0 auto;
            gap: 30px;
        }
        .hero-image-display {
            max-width: 45%; 
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .hero-image-display img {
            width: 100%;
            height: auto;
            display: block;
        }


        .hero-quote-box {
            max-width: 65%;
            padding: 20px;
            border: 2px solid var(--color-secondary);
            border-radius: 10px;
            background-color: #FFF9F4;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            font-style: italic;
            margin: 0 auto; 
        }

        .hero-quote-box::before {
            content: '“';
            font-size: 3em;
            color: var(--color-primary);
            position: absolute;
            top: -10px;
            left: 10px;
            line-height: 1;
        }
        
        .hero-quote-box .quote-text {
            font-size: 1.1em;
            color: var(--color-text);
            margin-left: 20px;
        }

        .hero-quote-box .quote-author {
            display: block;
            margin-top: 15px;
            text-align: right;
            font-style: normal;
            font-weight: bold;
            color: var(--color-secondary);
        }

        .content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            padding: 60px 20px;
            max-width: 1200px;
            margin: auto;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 30px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 5px solid var(--color-secondary);
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .card .icon {
            font-size: 3em;
            color: var(--color-primary);
            margin-bottom: 10px;
            display: block;
        }

        .card h3 {
            color: var(--color-text);
            font-size: 1.6em;
            margin-top: 0;
            margin-bottom: 5px;
        }

        .card p {
            color: #7F5539;
            font-size: 1em;
            min-height: 40px; 
            margin-bottom: 20px;
        }

        .card-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--color-secondary); 
            color: white;
            text-decoration: none;
            border-radius: 50px; 
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .card-button:hover {
            background-color: var(--color-primary);
        }
        
        @media (max-width: 768px) {
            .hero-visuals-wrap {
                flex-direction: column; 
                gap: 20px;
            }
            .hero-content-wrap {
                margin-bottom: 20px;
            }
            .hero-image-display, .hero-quote-box {
                max-width: 100%; 
            }
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
        
        <?php if ($user_role === 'admin'): ?>
            <a href="admin/manage_orders.php">Manage Orders</a>
        <?php else: ?>
            <a href="orders.php">My Orders</a>
        <?php endif; ?>
        
        <a href="contact.php">Contact</a>
        <a href="logout.php">Logout (<?php echo $fullname; ?>)</a> 
    </nav>
</header>

<section class="hero">
    
    <div class="hero-content-wrap">
        <h2>Welcome, <?php echo $fullname; ?>! 👋🏼</h2>
       <p style="font-size: 15px;">Your portal to managing and exploring beautiful, handcrafted items made with passion and Filipino heart.</p>
    </div>
    
    <div class="hero-visuals-wrap">
        
        <div class="hero-quote-box">
            
            <p class="quote-text">Handicraft is a purpose-driven enterprise dedicated to preserving and promoting the rich legacy of Filipino craftsmanship. Specializing in high-quality, ethically sourced goods like woven bags, abaniko, and tsinelas, the company champions local Filipino artisans, particularly those inspired by the Bicol region, by ensuring fair compensation and sustaining traditional crafting techniques. Customers receive authentic, handcrafted products while directly investing in cultural preservation and local livelihoods.</p>
            <span class="quote-author">— Handi Craft Team</span>
        </div>
        
    </div>
</section>

<section class="content">

    <div class="card">
        <span class="icon">🧺</span>
        <h3>View Our Crafts</h3>
        <p>Browse through all available handmade products across all categories.</p>
        <a href="product.php" class="card-button">Browse Products</a>
    </div>

    <?php if ($user_role === 'user'): ?>
    <div class="card">
        <span class="icon">🛍️</span>
        <h3>Track My Orders</h3>
        <p>View the history, status, and details of your previous purchases.</p>
        <a href="orders.php" class="card-button">View History</a>
    </div>
    <?php endif; ?>

    <?php if ($user_role === 'admin'): ?>
    <div class="card">
        <span class="icon">📦</span>
        <h3>Manage Orders</h3>
        <p>Process, track, and update customer orders efficiently from one dashboard.</p>
        <a href="admin/manage_orders.php" class="card-button">Go to Dashboard</a> 
    </div>

    <div class="card">
        <span class="icon">➕</span>
        <h3>Add New Craft</h3>
        <p>Upload new items, set prices, and manage your handicraft inventory.</p>
        <a href="admin/add_item.php" class="card-button">Add New Item</a>
    </div>
    <?php endif; ?>
    
</section>

</body>
</html>