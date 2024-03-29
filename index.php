<?php
session_start();

$db = mysqli_connect('localhost', 'root', 'root', 'lab22');
if (!$db) {
  die('Could not connect db');
}

$user_authed = $_SESSION['authed'] ?? false;

if (!empty($_GET['action']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_GET['action'] === 'create') {
    $query = $db->prepare("INSERT INTO products (name) VALUES (?)");
    $query->bind_param('s', $_POST['name']);
    $query->execute();
    $_SESSION['message'] = 'Product created';
  }

  if ($_GET['action'] === 'update') {
    $query = $db->prepare("UPDATE products SET name = ? WHERE id = ?");
    $query->bind_param('si', $_POST['name'], $_POST['id']);
    $query->execute();
    $_SESSION['message'] = 'Product updated';
  }

  if ($_GET['action'] === 'delete') {
    $query = $db->prepare("DELETE FROM products WHERE id = ?");
    $query->bind_param('i', $_POST['id']);
    $query->execute();
    $_SESSION['message'] = 'Product deleted';
  }

  if ($_GET['action'] === 'login') {
    $query = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $query->bind_param('ss', $_POST['username'], $_POST['password']);
    $query->execute();
    $result = $query->get_result();
    $user_authed = $result->num_rows > 0;
    $_SESSION['authed'] = $user_authed;
    $_SESSION['message'] = $user_authed ? 'Login success' : 'Login failed';
  }

  if ($_GET['action'] === 'register') {
    $query = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $query->bind_param('ss', $_POST['username'], $_POST['password']);
    $query->execute();
    $_SESSION['message'] = 'Register success';
  }

  if ($_GET['action'] === 'logout') {
    $_SESSION['authed'] = false;
    $_SESSION['message'] = 'Logout success';
  }

  header('Location: /');
  die();
}
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

  <?php if (!$user_authed) { ?>
    <div class="row">
      <div class="col-md-6">
        <h2 class="mb-3">Login</h2>
        <form action="/?action=login" method="post" class="mb-3">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>
          <button type="submit" class="btn btn-primary">Login</button>
        </form>
      </div>
      <div class="col-md-6">
        <h2 class="mb-3">Register</h2>
        <form action="/?action=register" method="post" class="mb-3">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>
          <button type="submit" class="btn btn-primary">Register</button>
        </form>
      </div>
    </div>
  <?php } else { ?>
    <div class="row">
      <div class="col-md-6">
        <h2 class="mb-3">Create Product</h2>
        <form action="/?action=create" method="post" class="mb-3">
          <div class="mb-3">
            <label for="new-product-name" class="form-label">Name</label>
            <input type="text" class="form-control" id="new-product-name" name="name">
          </div>
          <button type="submit" class="btn btn-primary">Create</button>
        </form>
      </div>
      <div class="col-md-6">
        <form action="/?action=logout" method="post" class="d-flex justify-content-end">
          <button type="submit" class="btn btn-danger">Logout</button>
        </form>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <h2>Products</h2>
        <div class="list-group" style="width: fit-content;">
          <?php
          $result = mysqli_query($db, "SELECT * FROM products");
          while ($row = mysqli_fetch_assoc($result)) {
          ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <span class="me-2">ID: <?php echo $row['id']; ?></span>
              <span class="d-flex" style="gap: 0.5rem;">
                <form action="/?action=update" method="post" class="d-flex" style="gap: 0.5rem;">
                  <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                  <input type="text" name="name" value="<?php echo $row['name']; ?>" class="form-control form-control-sm">
                  <?php if ($user_authed) { ?>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                  <?php } ?>
                </form>
                <form action="/?action=delete" method="post">
                  <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                  <?php if ($user_authed) { ?>
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  <?php } ?>
                </form>
              </span>
            </div>
          <?php
          }
          ?>
        </div>
      </div>
    </div>
  <?php } ?>
</body>

</html>