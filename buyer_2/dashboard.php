<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../index.php"); exit();
}
$buyer_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE buyer_id = ?");
$stmt->execute([$buyer_id]);
$total_orders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM orders WHERE buyer_id = ?");
$stmt->execute([$buyer_id]);
$total_spent = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE buyer_id = ? AND status='delivered'");
$stmt->execute([$buyer_id]);
$delivered = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, u.name as farmer_name
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users u ON p.farmer_id = u.id
    WHERE o.buyer_id = ?
    ORDER BY o.created_at DESC
    LIMIT 10
");
$stmt->execute([$buyer_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Dashboard — AgroConnect</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav>
    <span class="brand">🌾 AgroConnect</span>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="products.php">Browse Products</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</nav>
<div class="container">
    <h2 style="margin-bottom:20px;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 🛒</h2>
    <div class="stats">
        <div class="stat-box">
            <h3><?= $total_orders ?></h3>
            <p>Total Orders</p>
        </div>
        <div class="stat-box">
            <h3><?= $delivered ?></h3>
            <p>Delivered</p>
        </div>
        <div class="stat-box">
            <h3>৳<?= number_format($total_spent, 2) ?></h3>
            <p>Total Spent</p>
        </div>
    </div>

    <div class="card">
        <h2>My Orders</h2>
        <?php if (empty($orders)): ?>
            <p style="color:#888;">No orders yet. <a href="products.php" style="color:#2e7d32;">Browse products</a>.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>Product</th><th>Farmer</th><th>Qty</th><th>Total</th><th>Payment</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['product_name']) ?></td>
                    <td><?= htmlspecialchars($o['farmer_name']) ?></td>
                    <td><?= $o['quantity'] ?></td>
                    <td>৳<?= number_format($o['total_price'], 2) ?></td>
                    <td>
                        <span class="badge <?= $o['payment_status']==='paid' ? 'badge-green' : 'badge-orange' ?>">
                            <?= ucfirst($o['payment_status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $map = ['pending'=>'badge-orange','confirmed'=>'badge-blue','shipped'=>'badge-blue','delivered'=>'badge-green','cancelled'=>'badge-red'];
                        ?>
                        <span class="badge <?= $map[$o['status']] ?? 'badge-gray' ?>"><?= ucfirst($o['status']) ?></span>
                    </td>
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
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../index.php"); exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $stmt = $pdo->prepare("SELECT p.*, u.name as farmer_name FROM products p JOIN users u ON p.farmer_id = u.id WHERE p.status='available' AND p.name LIKE ? ORDER BY p.created_at DESC");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("SELECT p.*, u.name as farmer_name FROM products p JOIN users u ON p.farmer_id = u.id WHERE p.status='available' ORDER BY p.created_at DESC");
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Products — AgroConnect</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav>
    <span class="brand">🌾 AgroConnect</span>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="products.php">Browse Products</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</nav>
<div class="container">
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Available Products</h2>
        <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:6px;">
            <button type="submit" class="btn btn-green">Search</button>
            <?php if ($search): ?><a href="products.php" class="btn btn-blue">Clear</a><?php endif; ?>
        </form>
        <?php if (empty($products)): ?>
            <p style="color:#888;">No products available.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>Product</th><th>Category</th><th>Farmer</th><th>Price</th><th>Available</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td><?= $p['category'] ?></td>
                    <td><?= htmlspecialchars($p['farmer_name']) ?></td>
                    <td>৳<?= number_format($p['price_per_unit'], 2) ?>/<?= $p['unit'] ?></td>
                    <td><?= $p['quantity_available'] ?> <?= $p['unit'] ?></td>
                    <td><a href="payment.php?product_id=<?= $p['id'] ?>" class="btn btn-green" style="padding:6px 14px; font-size:13px;">Buy Now</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>