<?php

require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!(isset($_POST['username']) && isset($_POST['password']))) {
        $_SESSION['message'] = 'Please fill in all fields';
        header('Location: /login.php');
        die();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = $db->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");
    if ($user->num_rows == 0) {
        $_SESSION['message'] = 'Invalid username or password';
        header('Location: /login.php');
        die();
    }

    $_SESSION['username'] = $username;
    $_SESSION['message'] = "Logged in. Hi $username!";
    header('Location: /');
    die();
}

require_once 'header.php';
?>

<div class="container my-5 d-flex flex-column align-items-center gap-3">
    <h1 class="h1">Login</h1>

    <form action="/login.php" method="post" class="d-flex flex-column gap-3">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php
require_once 'footer.php';
