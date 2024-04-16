<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MAMP</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reset-css@5.0.2/reset.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>

<body class="container p-5">
  <?php if (!empty($_SESSION['message'])) { ?>
    <div class="alert alert-primary" role="alert">
      <?php echo $_SESSION['message']; ?>
    </div>
  <?php
    unset($_SESSION['message']);
  } ?>

  <h1 class="h1">File content</h1>
  <div id="file-content" class="mb-5"></div>

  <h1 class="h1">Products</h1>
  <form action="/products.php" method="get" class="mb-3">
    <?php if (!empty($_GET['q'])) { ?>
      <input type="text" class="form-control mb-3" name="q" placeholder="Product Name" autofocus value="<?php echo $_GET['q']; ?>">
    <?php } else { ?>
      <input type="text" class="form-control mb-3" name="q" placeholder="Product Name" autofocus>
    <?php } ?>
    <div id="search-hint" class="mb-3"></div>
    <button type=" submit" class="btn btn-primary">Search</button>
  </form>

  <?php
  $db = mysqli_connect('localhost', 'root', 'root', 'lab32');
  if (!$db) {
    die('Could not connect db');
  }

  if (!empty($_GET['q'])) {
    $q = $_GET['q'];
  } else {
    $q = '';
  }
  $query = $db->prepare("SELECT * FROM products WHERE name LIKE '%$q%'");
  $query->execute();
  $result = $query->get_result();
  $products = $result->fetch_all(MYSQLI_ASSOC);

  ?>
  <div class="alert alert-primary" role="alert">
    <?php echo count($products); ?> products found
  </div>
  <?php

  foreach ($products as $product) {
  ?>
    <div class="card mb-3">
      <div class="card-body">
        <?php echo $product['id'] ?>. <?php echo $product['name']; ?>
      </div>
    </div>
  <?php
  }
  ?>

  <script>
    <?php $all_products = $db->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC); ?>
    const productNames = <?php echo json_encode(array_column($all_products, 'name')); ?>;
    console.log(productNames)

    $.get('/data.txt', function(data) {
      $('#file-content').text(data);
    });

    $('input[name=q]').on('input', function() {
      const q = $(this).val();
      const hint = productNames.filter(name => name.toLowerCase().includes(q.toLowerCase())).slice(0, 5);
      $('#search-hint').html(hint.map(name => `<a class="text-muted" href="/products.php?q=${name}">${name}</a><br>`));
    });
  </script>
</body>

</html>