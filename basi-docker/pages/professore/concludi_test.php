<?php
session_start();
require_once '../../helper/connessione_mysql.php';
require_once '../../helper/connessione_mongodb.php';
require_once '../../composer/vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SESSION['ruolo'] != 'PROFESSORE' || !isset($_SESSION['email'])) {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['test_associato'])) {
    $test_associato = $_GET['test_associato'];
    $db = connectToDatabaseMYSQL();
    $sql = "CALL MostraRisultati(:titolo);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo', $test_associato, PDO::PARAM_STR);

    if ($stmt->execute()) {
        try {
            insertOnMONGODB(
                'conclusione_test',
                $test_associato . ' è stato chiuso',
                'Il professore ' . $_SESSION['email'] .  ' ha chiuso il test ' . $test_associato
            );
        } catch (\Throwable $th) {
            echo "<script>alert('Errore in mongodb:" .  $th->getMessage() . "')</script>";
        }
    }
    $stmt->closeCursor();
    echo "<script>alert('$test_associato ora è chiuso!');window.location.replace('professore.php')</script>";
}
