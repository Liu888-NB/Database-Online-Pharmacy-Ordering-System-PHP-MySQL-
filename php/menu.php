<?php
session_start();
include "connectdb.php";

// Get filter and search parameters
$pharmacyFilter = $_GET['pharmacy'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$itemsPerPage = 5;
$offset = ($page - 1) * $itemsPerPage;

// Get pharmacy list (for filtering)
$pharmacyStmt = $conn->query("SELECT pharmacy_id, pharmacy_name FROM Pharmacy");
$pharmacies = $pharmacyStmt->fetchAll(PDO::FETCH_ASSOC);

// Build base query
$sql = "
    SELECT 
        p.product_id, p.product_name, p.description, p.price, p.is_prescription_required,
        ph.pharmacy_id, ph.pharmacy_name, ph.p_address, i.stock_quantity
    FROM Product p
    JOIN Inventory i ON p.product_id = i.product_id
    JOIN Pharmacy ph ON i.pharmacy_id = ph.pharmacy_id
    WHERE i.stock_quantity > 0
";
$params = [];

// Apply filter conditions
if (!empty($pharmacyFilter)) {
    $sql .= " AND ph.pharmacy_id = :pharmacy_id";
    $params[':pharmacy_id'] = $pharmacyFilter;
}
if (!empty($searchQuery)) {
    $sql .= " AND p.product_name LIKE :search";
    $params[':search'] = "%" . $searchQuery . "%";
}

// Get total record count
$countStmt = $conn->prepare(str_replace(
    "SELECT 
        p.product_id, p.product_name, p.description, p.price, p.is_prescription_required,
        ph.pharmacy_id, ph.pharmacy_name, ph.p_address, i.stock_quantity",
    "SELECT COUNT(*) as total",
    $sql
));
$countStmt->execute($params);
$totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination
$sql .= " LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>Product List</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
            background-image: url('back_picture.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed; 
        }


        header {
            background-color: #009879;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 50px;
            margin-right: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            margin-left: 15px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .content {
            padding: 20px;
            max-width: 960px;
            margin: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
        }

        .filters {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filters form {
            display: flex;
            gap: 10px;
            flex: 1;
        }

        .filters input[type="text"],
        .filters select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            flex: 1;
            font-size: 14px;
            box-sizing: border-box;
            background-color: #f1f1f1;
        }

        .filters input[type="submit"] {
            padding: 12px 18px;
            background-color: #009879;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filters input[type="submit"]:hover {
            background-color: #007b63;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        .product-card h3 {
            margin-top: 0;
            color: #009879;
            font-size: 20px;
        }

        .product-card p {
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .product-card form {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .product-card input[type="number"],
        .product-card input[type="file"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            width: 100px;
            box-sizing: border-box;
            background-color: #f1f1f1;
        }

        .product-card input[type="submit"] {
            padding: 10px 16px;
            background-color: #009879;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .product-card input[type="submit"]:hover {
            background-color: #007b63;
        }

        .pagination {
            text-align: center;
            margin-top: 30px;
        }

        .pagination a,
        .pagination strong {
            display: inline-block;
            padding: 8px 14px;
            margin: 0 4px;
            text-decoration: none;
            border-radius: 6px;
            background-color: #f1f1f1;
            color: #009879;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #ccc;
        }

        .pagination strong {
            background-color: #009879;
            color: white;
        }
    </style>
</head>
<body>
<header>
    <div class="logo-container">
        <a href="index.php"><img src="qiao_logo.svg" class="logo" style="height:50px;" alt="Logo"></a>
        <nav><a href="user_home.php" class="button">Home</a></nav>
        <nav><a href="cart.php" class="button">🛒 Cart</a></nav>
        <nav><a href="my_order.php" class="button">Order</a></nav>
    </div>
</header>

<div class="content">
    <h2>Product List</h2>

    <div class="filters">
        <form method="get">
            <select name="pharmacy" onchange="this.form.submit()">
                <option value="">All Pharmacies</option>
                <?php foreach ($pharmacies as $pharmacy): ?>
                    <option value="<?= $pharmacy['pharmacy_id'] ?>" <?= $pharmacyFilter == $pharmacy['pharmacy_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pharmacy['pharmacy_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="get">
            <input type="text" name="search" placeholder="Search Products" value="<?= htmlspecialchars($searchQuery) ?>">
            <input type="submit" value="Search">
        </form>
    </div>

    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <h3><?= htmlspecialchars($product['product_name']) ?></h3>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p>Price: ￥<?= number_format($product['price'], 2) ?></p>
            <p>Prescription Required: <?= $product['is_prescription_required'] ? 'Yes' : 'No' ?></p>
            <p>Pharmacy: <?= htmlspecialchars($product['pharmacy_name']) ?> (<?= htmlspecialchars($product['p_address']) ?>)</p>
            <p>Stock: <?= (int)$product['stock_quantity'] ?></p>

            <?php if ($product['is_prescription_required']): ?>
                <form method="post" action="submit_prescription.php" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                    <input type="hidden" name="pharmacy_id" value="<?= (int)$product['pharmacy_id'] ?>">
                    <input type="hidden" name="pharmacy_name" value="<?= htmlspecialchars($product['pharmacy_name']) ?>">

                    <label for="quantity_<?= $product['product_id'] ?>">Quantity:</label>
                    <input type="number" id="quantity_<?= $product['product_id'] ?>" name="quantity" value="1" min="1" max="<?= (int)$product['stock_quantity'] ?>" required>

                    <label for="prescription_<?= $product['product_id'] ?>">Upload Prescription:</label>
                    <input type="file" name="prescription" id="prescription_<?= $product['product_id'] ?>" accept="image/*" required>

                    <input type="submit" value="Upload Prescription">
                </form>
            <?php else: ?>
                <form method="post" action="add_cart.php">
                    <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                    <input type="hidden" name="pharmacy_id" value="<?= (int)$product['pharmacy_id'] ?>">
                    <input type="hidden" name="pharmacy_name" value="<?= htmlspecialchars($product['pharmacy_name']) ?>">

                    <label for="quantity_<?= $product['product_id'] ?>">Quantity:</label>
                    <input type="number" id="quantity_<?= $product['product_id'] ?>" name="quantity" value="1" min="1" max="<?= (int)$product['stock_quantity'] ?>" required>

                    <input type="submit" value="Add to Cart">
                </form>
            <?php endif; ?>        
        </div>
    <?php endforeach; ?>

    <div class="pagination">
        <?php if ($totalPages > 1): ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                    $queryParams = $_GET;
                    $queryParams['page'] = $i;
                    $queryString = http_build_query($queryParams);
                ?>
                <?php if ($i == $page): ?>
                    <strong><?= $i ?></strong>
                <?php else: ?>
                    <a href="?<?= htmlspecialchars($queryString) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function validateForm(form) {
    const quantity = form.quantity.value;
    if (!quantity || quantity < 1) {
        alert("Please enter a valid quantity (at least 1).");
        return false;
    }
    const maxQty = parseInt(form.quantity.max);
    if (quantity > maxQty) {
        alert("Quantity exceeds available stock.");
        return false;
    }

    const prescriptionRequired = <?= json_encode(array_column($products, 'is_prescription_required')) ?>;
    // We can't check prescription upload by PHP here; the form is per product
    // Instead, require prescription file if prescription required field is present in the form
    if (form.prescription) {
        if (form.prescription.required && !form.prescription.value) {
            alert("Please upload your prescription.");
            return false;
        }
    }
    return true;
}
</script>

</body>
</html>



