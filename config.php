<?php
// honestly i used chat gpt to get this working locally so i dunno what this shit really means, but it works!

$host   = 'localhost';
$db     = 'online_bookstore';
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // create a new PDO instance to connect to the database
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // ff the connection fails, throw an exception with the error message
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}