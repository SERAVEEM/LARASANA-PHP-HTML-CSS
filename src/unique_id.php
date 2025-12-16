<?php

// function generateUniqueId($pdo, $table, $prefix)
// {
//     $stmt = $pdo->query("SELECT id FROM $table ORDER BY id DESC LIMIT 1");
//     $lastId = $stmt->fetchColumn();

//     $next = ($lastId) ? $lastId + 1 : 1;

    
//     return $prefix . str_pad($next, 3, "0", STR_PAD_LEFT);
// }
