<?php

require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!(isset($_POST['email']) && isset($_POST['password']))) {
        $_SESSION['message'] = 'Please fill in all fields';
        header('Location: /login.php');
        die();
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $db->query("SELECT * FROM teachers WHERE email = '$email' AND password = '$password'");
    if (!$user || $user->num_rows == 0) {
        // $_SESSION['message'] = 'Invalid email or password';
        $_SESSION['message'] = $db->error;
        header('Location: /login.php');
        die();
    }

    $user = $user->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    unset($user['id']);
    $_SESSION = array_merge($_SESSION, $user);
    $_SESSION['message'] = "Logged in. Hi $email!";
    header('Location: /');
    die();
}

require_once 'header.php';
?>

<div class="container my-5 d-flex flex-column align-items-center gap-3">
    <h1 class="h1">Login</h1>

    <form action="/login.php" method="post" class="d-flex flex-column gap-3">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" required>
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
