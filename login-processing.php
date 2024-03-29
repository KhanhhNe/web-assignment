<?php
session_start();

if (!$_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: /login.php');
    die();
}

if (isset($_GET['logout'])) {
    $_SESSION['authed'] = false;
    unset($_SESSION['name']);
    $_SESSION['message'] = 'Logout success';
    header('Location: /login.php');
    die();
}

$db = mysqli_connect('localhost', 'root', 'root', 'lab23');
if (!$db) {
    die('Could not connect db');
}

$username = $_POST['username'];
$password = $_POST['password'];

$query = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$query->bind_param('ss', $username, $password);
$query->execute();

$result = $query->get_result();
if ($result->num_rows > 0) {
    $_SESSION['authed'] = true;
    $_SESSION['name'] = $result->fetch_assoc()['name'];
    $_SESSION['message'] = 'Login success';
} else {
    $_SESSION['authed'] = false;
    unset($_SESSION['name']);
    $_SESSION['message'] = 'Login failed';
}

header('Location: /login.php');
die();
