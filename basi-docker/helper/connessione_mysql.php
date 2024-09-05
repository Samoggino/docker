<?php

/**
 * Funzione per connettersi al database, sostituire i valori con quelli corretti
 */
function connectToDatabaseMYSQL()
{

    $hostname = "localhost";
    $dbname = "POKEDB";

    $dsn = "mysql:host=$hostname;dbname=$dbname";
    $username = 'root';
    $password = 'fbyhm3J#pmE%6g2%7d1@';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        echo "Errore di connessione al database: " . $e->getMessage();
        return null;
    }
}
