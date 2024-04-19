<?php
include 'includes/db.php';

// Fetch products based on search, specify columns if necessary
$products = fetchData('products', $_GET['search'] ?? '', 'id, name, image, description, barcode, company_name, article_link');

// Fetch countries for the dropdown, specify columns explicitly
$countries = fetchData('countries', '', 'code, name');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Human Rights - Boycott Products</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="js/scripts.js"></script>
</head>
<body>
<header>
    <h1>Support Human Rights by Boycotting Unethical Products</h1>
    <p>Search and discover products to avoid.</p>
</header>
<section id="search">
    <form id="search-form">
        <input type="text" id="search-input" placeholder="Search products..." aria-label="Search Products">
        <button type="submit">Search</button>
    </form>
    <select id="country-selector" style="width: 100%;" aria-label="Select Country">
        <option value="">Select a country</option>
        <?php
        foreach ($countries as $country) {
            echo "<option value='{$country['code']}' class='flag-icon flag-icon-" . strtolower($country['code']) . "'>" . htmlspecialchars($country['name']) . "</option>";
        }
        ?>
    </select>
</section>
<section id="product-gallery">
    <?php foreach ($products as $product): ?>
        <div class='product'>
            <img src='img/<?= htmlspecialchars($product['image']) ?>' alt='<?= htmlspecialchars($product['name']) ?>'>
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p>Barcode: <?= htmlspecialchars($product['barcode']) ?></p>
            <p>Company: <?= htmlspecialchars($product['company_name']) ?></p>
            <a href='<?= htmlspecialchars($product['article_link']) ?>' target='_blank'>Why we boycott this</a>
        </div>
    <?php endforeach; ?>
</section>
</body>
</html>
