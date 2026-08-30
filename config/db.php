<?php

$config = require __DIR__ . "/database.local.php";

try {

    $dsn =
        "mysql:host=" . $config["host"] .
        ";port=" . $config["port"] .
        ";dbname=" . $config["dbname"] .
        ";charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $config["username"],
        $config["password"]
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die("Database connection failed.");
}