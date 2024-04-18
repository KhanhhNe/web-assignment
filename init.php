<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$db = mysqli_connect('localhost', 'root', 'root', 'lab');
if (!$db) {
    die('Could not connect db');
}
