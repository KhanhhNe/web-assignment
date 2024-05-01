<?php

require_once 'init.php';

require_once 'header.php';

?>

<style>
    body {
        height: 100vh;
        width: 100vw;
        background-image: url("/assets/homepage-background.jpg");
        background-size: 90% 90%;
        background-position: bottom right;
        background-repeat: no-repeat;
    }
</style>

<div class="flex-center flex-column gap-3 pb-5" style="width: 40%; height: 100vh;">
    <img src="/assets/logo.png" alt="Logo" width="100" height="100">
    <h5 class="h5 text-center">Ho Chi Minh<br>University of Technology</h5>
    <h1 class="h1">Quiz Website</h1>

    <?php if (isset($_SESSION['user_id'])) : ?>
        <a href="/dashboard.php" class="btn btn-primary">Dashboard</a>
    <?php else : ?>
        <a href="/login.php" class="btn btn-primary">Login</a>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
