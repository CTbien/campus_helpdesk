<?php
// app/config/db.php
declare(strict_types=1);

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    // --- Configuration AlwaysData (commentée) ---
    // $dsn = "mysql:host=mysql-ctbien.alwaysdata.net;dbname=ctbien_db;charset=utf8mb4";
    // $pdo = new PDO($dsn, "ctbien", "clementbla77", [
    //   PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //   PDO::ATTR_EMULATE_PREPARES => false,
    // ]);

    // --- Configuration Locale XAMPP ---
    $dsn = "mysql:host=localhost;dbname=campus_helpdesk;charset=utf8mb4"; // Modifiez le nom de la base si besoin
    $pdo = new PDO($dsn, "root", "", [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
  }
  return $pdo;
}