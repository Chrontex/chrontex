<?php

require_once "config/database.php";

$stmt = $pdo->query("SHOW TABLES");

$tables = $stmt->fetchAll();

echo "<h1>Database Connection Successful</h1>";

echo "<h2>Tables:</h2>";

foreach ($tables as $table) {

    echo "<p>" . htmlspecialchars($table[array_key_first($table)]) . "</p>";

}

?>