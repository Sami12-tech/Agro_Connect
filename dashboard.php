<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../index.php"); exit();
}
$farmer_id = $_SESSION['user_id'];

// Total products
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE farmer_id = ?");
$stmt->execute([$farmer_id]);
$total_products = $stmt->fetch()['total'];

// Total sales count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sales WHERE farmer_id = ?");
$stmt->execute([$farmer_id]);
$total_sales = $stmt->fetch()['total'];

// Total earnings
$stmt = $pdo->prepare("SELECT SUM(amount_earned) as total FROM sales WHERE farmer_id = ?");
$stmt->execute([$farmer_id]);
$total_earned = $stmt->fetch()['total'] ?? 0;

// Recent sales
$stmt = $pdo->prepare("
    SELECT s.*, p.name as product_name, u.name as buyer_name
    FROM sales s
    JOIN products p ON s.product_id = p.id
    JOIN orders o ON s.order_id = o.id
    JOIN users u ON o.buyer_id = u.id
    WHERE s.farmer_id = ?
    ORDER BY s.sale_date DESC
    LIMIT 10
");
$stmt->execute([$farmer_id]);
$recent_sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Dashboard — AgroConnect</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav>
    <span class="brand">🌾 AgroConnect</span>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="my_products.php">My Products</a></li>
        <li><a href="hire_transport.php">Hire Transport</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2 style="margin-bottom:20px;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👨‍🌾</h2>

    <div class="stats">
        <div class="stat-box">
            <h3><?= $total_products ?></h3>
            <p>Total Products Listed</p>
        </div>
        <div class="stat-box">
            <h3><?= $total_sales ?></h3>
            <p>Total Sales Made</p>
        </div>
        <div class="stat-box">
            <h3>৳<?= number_format($total_earned, 2) ?></h3>
            <p>Total Earnings</p>
        </div>
    </div>

    <div class="card">
        <h2>Recent Sales</h2>
        <?php if (empty($recent_sales)): ?>
            <p style="color:#888;">No sales yet. <a href="add_product.php" style="color:#2e7d32;">Add a product</a> to get started.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Qty Sold</th>
                    <th>Amount (৳)</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent_sales as $sale): ?>
                <tr>
                    <td><?= htmlspecialchars($sale['product_name']) ?></td>
                    <td><?= htmlspecialchars($sale['buyer_name']) ?></td>
                    <td><?= $sale['quantity_sold'] ?></td>
                    <td>৳<?= number_format($sale['amount_earned'], 2) ?></td>
                    <td><?= date('d M Y', strtotime($sale['sale_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>


<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../index.php"); exit();
}

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price    = $_POST['price_per_unit'];
    $unit     = $_POST['unit'];
    $quantity = $_POST['quantity'];
    $desc     = trim($_POST['description']);

    if (empty($name) || empty($price) || empty($quantity)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, category, price_per_unit, unit, quantity_available, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $name, $category, $price, $unit, $quantity, $desc]);
        $success = "Product added successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product — AgroConnect</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav>
    <span class="brand">🌾 AgroConnect</span>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="my_products.php">My Products</a></li>
        <li><a href="hire_transport.php">Hire Transport</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</nav>
<div class="container">
    <div class="card" style="max-width:600px; margin:auto;">
        <h2>Add New Product</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" required placeholder="e.g. Rice, Wheat, Tomato">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Grain">Grain</option>
                    <option value="Vegetable">Vegetable</option>
                    <option value="Fruit">Fruit</option>
                    <option value="Spice">Spice</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price per Unit (৳) *</label>
                <input type="number" name="price_per_unit" step="0.01" required placeholder="e.g. 50.00">
            </div>
            <div class="form-group">
                <label>Unit</label>
                <select name="unit">
                    <option value="kg">kg</option>
                    <option value="ton">ton</option>
                    <option value="piece">piece</option>
                    <option value="dozen">dozen</option>
                    <option value="litre">litre</option>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity Available *</label>
                <input type="number" name="quantity" step="0.01" required placeholder="e.g. 100">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Optional product details"></textarea>
            </div>
            <button type="submit" class="btn btn-green">Add Product</button>
            <a href="my_products.php" class="btn btn-blue" style="margin-left:10px;">View My Products</a>
        </form>
    </div>
</div>
</body>
</html>