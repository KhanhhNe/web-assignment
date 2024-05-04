<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

if ($_SERVER['SERVER_NAME'] != 'localhost') {
    $db = mysqli_connect('localhost', 'id22121819_root', 'Root@123', 'id22121819_assigment');
} else {
    $db = mysqli_connect('localhost', 'root', 'root', 'assignment');
}
if (!$db) {
    die('Could not connect db');
}

function fetch_assoc_all($result)
{
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function die_json($data)
{
    echo json_encode($data);
    die();
}

function require_login()
{
    if ($_SESSION['user_id'] == 0) {
        die_json(['success' => false, 'error' => 'Please log in']);
    }

    return $_SESSION['user_id'];
}

function required_keys($keys)
{
    $values = [];
    foreach ($keys as $key) {
        if (!isset($_REQUEST[$key])) {
            die_json(['success' => false, 'error' => "Missing `$key`"]);
        }
        $values[] = $_REQUEST[$key];
    }
    return $values;
}

function required_at_least_one_key($keys)
{
    $values = [];
    foreach ($keys as $key) {
        $values[] = $_REQUEST[$key] ?? NULL;
    }
    // Error if all are null
    if (count(array_filter($values, 'strlen')) == 0) {
        $all_keys = implode(', ', $keys);
        die_json(['success' => false, 'error' => "Require at least one of `$all_keys`"]);
    }
    return $values;
}

$no_image_url = 'https://via.assets.so/img.jpg?w=320&h=180&tc=%23f8f9fa&bg=%23343a40&t=No%20Image';
