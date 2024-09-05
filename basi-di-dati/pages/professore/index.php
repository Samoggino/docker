<?php
session_start();
require '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Location: ./professore.php');