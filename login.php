<?php
session_start();
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

  <?php if (!$_SESSION['authed']) { ?>
    <div class="row">
      <div class="col-md-6">
        <h2 class="mb-3">Login</h2>
        <form action="/login-processing.php" method="post" class="mb-3">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="email" class="form-control" id="username" name="username">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>
          <button type="submit" class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  <?php } else { ?>
    <div class="col-md-6">
      <form action="/login-processing.php?logout=true" method="post" class="d-flex justify-content-end">
        <button type="submit" class="btn btn-danger">Logout</button>
      </form>
    </div>
    <h1>Hello <?php echo $_SESSION['name'] ?></h1>
  <?php } ?>

  <script>
    function validatePassword(e) {
      const password = e.target.value;
      const errors = [
        [/^(?=.*[a-z])/, 'lowercase letter'],
        [/^(?=.*[A-Z])/, 'uppercase letter'],
        [/^(?=.*\d)/, 'digit'],
        [/^(?=.{8,})/, '8 characters'],
      ].filter(([regex, error]) => !password.match(regex)).map(([regex, error]) => error);
      if (errors.length) {
        e.target.setCustomValidity(`Password must contain ${errors.join(', ')}`);
      } else {
        e.target.setCustomValidity('');
      }
      e.target.reportValidity();
    }
    $('#password').on('input', validatePassword);
    $('#password').on('focus', validatePassword);
  </script>
</body>

</html>