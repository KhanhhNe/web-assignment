<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$db = mysqli_connect('localhost', 'root', 'root', 'assignment');
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
