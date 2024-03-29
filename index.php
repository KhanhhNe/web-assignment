<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MAMP</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reset-css@5.0.2/reset.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
  <nav class="navbar navbar-expand navbar-light bg-light justify-content-center">
    <li class="navbar-nav">
      <a class="nav-link" href="/?page=home">Home</a>
    </li>
    <li class="navbar-nav">
      <a class="nav-link" href="/?page=products">Products</a>
    </li>
    <li class="navbar-nav">
      <a class="nav-link" href="/?page=login">Login</a>
    </li>
    <li class="navbar-nav">
      <a class="nav-link" href="/?page=register">Register</a>
    </li>
  </nav>

  <div class="container p-5" style="max-width: 40rem;">
  <?php
  $page = $_GET['page'];
  if (!$page) $page = 'home';
  require_once $page . '.php';
  ?>
  </div>

  <!-- footer with copyright 2024 -->
  <footer class="bg-dark text-white text-center p-3">
    <p>&copy; 2024 MAMP</p>
  </footer>
</body>

</html>