<?php

// Inizia la sessione
session_start();

session_unset(); // Rimuove tutte le variabili di sessione
session_destroy(); // Elimina completamente la sessione

// Reindirizza l'utente alla pagina di login
header("Location: /pages/login.php");
exit();
