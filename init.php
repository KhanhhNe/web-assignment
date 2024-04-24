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
