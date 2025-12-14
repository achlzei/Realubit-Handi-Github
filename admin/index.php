<?php
// FILE: admin/index.php - Admin Dashboard/Landing Page

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db_connect.php'; 
session_start(); 

if (!isset($_SESSION['user_ID']) || strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit;
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Admin');
$dashboard_data = [
    'total_products' => 0, 
    'orders_today' => 0,  
    'registered_users' => 0, 
    'pending_messages' => 0 
];

$result = $conn->query("SELECT COUNT(Item_ID) as count FROM items");
$dashboard_data['total_products'] = $result->fetch_assoc()['count'] ?? 0;

$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(Order_ID) as count FROM orders WHERE DATE(order_date) = ? AND order_status = 'Pending'");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data['orders_today'] = $result->fetch_assoc()['count'] ?? 0;
$stmt->close();

$result = $conn->query("SELECT COUNT(user_ID) as count FROM users WHERE user_role = 'user'");
$dashboard_data['registered_users'] = $result->fetch_assoc()['count'] ?? 0;

$dashboard_data['pending_messages'] = 4; 
$sql_sales = "
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') AS sale_month, 
        SUM(total_price) AS monthly_sales   /* Using 'total_price' */
    FROM 
        orders 
    WHERE 
        order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY 
        sale_month
    ORDER BY 
        sale_month ASC
";

$result_sales = $conn->query($sql_sales);
$monthly_data = [];

if ($result_sales) {
    while ($row = $result_sales->fetch_assoc()) {
        $timestamp = strtotime($row['sale_month'] . '-01');
        $label = date('M Y', $timestamp);
        $monthly_data[$label] = (float)$row['monthly_sales'];
    }
}

$chart_labels = json_encode(array_keys($monthly_data));
$chart_sales = json_encode(array_values($monthly_data));

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        .sidebar-nav a:hover, .sidebar-nav .active {
            background:  #9C6644; 
            font-weight: bold;
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
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        
        .top-bar h1 {
            color: var(--color-primary);
            font-size: 1.5em;
            margin: 0;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .dashboard-card-link {
            text-decoration: none; 
            color: inherit; 
            display: block; 
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .dashboard-card-link:hover {
            transform: translateY(-5px); 
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            text-align: center;
            border-left: 5px solid var(--color-secondary);
        }

        .card-value {
            font-size: 2.5em;
            color: var(--color-primary);
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-title {
            font-size: 1em;
            color: var(--color-text);
            margin: 0;
        }
        
        .chart-container {
            width: 100%;
            max-width: 1000px;
            margin-top: 40px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-left: auto;
            margin-right: auto;
        }
        .chart-container h2 {
            color: var(--color-primary);
            font-size: 1.5em;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="main-layout">
    
    <div class="sidebar">
        <h2>A Local Handicraft Botique</h2>
        <h3>ADMIN</h3>
        
        <div class="sidebar-nav">
            <a href="index.php" class="active">🏠 Dashboard</a>
            <a href="manage_products.php">🧺 Manage Products</a> 
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
            <h1>Admin Dashboard</h1>
            <p>Welcome, <strong><?php echo $fullname; ?></strong></p>
        </div>

        <div class="dashboard-grid">
            
            <a href="manage_products.php" class="dashboard-card-link"> <div class="card">
                    <div class="card-value"><?php echo $dashboard_data['total_products']; ?></div>
                    <p class="card-title">Total Products</p>
                </div>
            </a>
            
            <a href="manage_orders.php?status=Pending" class="dashboard-card-link">
                <div class="card">
                    <div class="card-value"><?php echo $dashboard_data['orders_today']; ?></div>
                    <p class="card-title">Pending Orders Today</p>
                </div>
            </a>
            
            <a href="manage_users.php" class="dashboard-card-link">
                <div class="card">
                    <div class="card-value"><?php echo $dashboard_data['registered_users']; ?></div>
                    <p class="card-title">Registered Users</p>
                </div>
            </a>

            <a href="manage_messages.php" class="dashboard-card-link">
                <div class="card">
                    <div class="card-value"><?php echo $dashboard_data['pending_messages']; ?></div>
                    <p class="card-title">Pending Messages</p>
                </div>
            </a>
            
        </div>
        
        <div class="chart-container">
            <h2>Monthly Sales Report (Last 6 Months)</h2>
            <canvas id="monthlySalesChart"></canvas>
        </div>
        
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const labels = <?php echo $chart_labels; ?>;
    const data = <?php echo $chart_sales; ?>;

    if (data.length === 0) {
        const chartDiv = document.getElementById('monthlySalesChart').parentNode;
        chartDiv.innerHTML = '<h2>Monthly Sales Report (Last 6 Months)</h2><p style="text-align:center; color: #7F5539;">No sales data available for the last 6 months.</p>';
        return;
    }

    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: labels, 
            datasets: [{
                label: 'Total Sales (₱)',
                data: data, 
                backgroundColor: 'rgba(127, 85, 57, 0.8)', 
                borderColor: 'rgba(127, 85, 57, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Sales (₱)'
                    },
                    ticks: {
                        callback: function(value, index, values) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += '₱' + context.parsed.y.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>

</body>
</html>