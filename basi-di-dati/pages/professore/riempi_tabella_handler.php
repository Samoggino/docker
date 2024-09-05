<?php
session_start();
require_once '../../helper/connessione_mysql.php';
require_once '../../helper/connessione_mongodb.php';
require_once '../../composer/vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = connectToDatabaseMYSQL();
    $nome_tabella = $_GET['nome_tabella'];
    // Prendi i valori inviati dal modulo
    echo "<script>console.log('POST: " . json_encode($_POST) . "');</script>";

    $valori_inviati = $_POST;

    // se il post include un valore "" vuol dire che l'utente non ha inserito un valore, sostituisci con NULL
    foreach ($valori_inviati as $key => $value) {
        if ($value == "") {
            $valori_inviati[$key] = NULL;
        }
    }

    // Costruisci la query di inserimento
    $column_names = implode(', ', array_keys($valori_inviati));
    $column_placeholders = implode(', ', array_fill(0, count($valori_inviati), '?'));
    $sql = "INSERT INTO $nome_tabella ($column_names) VALUES ($column_placeholders)";

    // Esegui la query preparata
    try {
        $stmt = $db->prepare($sql);

        // Verifica se l'inserimento è riuscito
        if ($stmt->execute(array_values($valori_inviati))) {

            // Inserisci i valori nel database MongoDB
            insertOnMONGODB(
                'inserimento_in_tabella',
                [
                    'tabella' => $nome_tabella,
                    'valori' => $valori_inviati
                ],
                'Inserimento di una riga nella tabella ' . $nome_tabella,
            );

            echo "<script>window.location.replace('/pages/professore/riempi_tabella.php?nome_tabella=$nome_tabella')</script>";
        }
    } catch (PDOException $e) {
        $errorCode = $e->errorInfo[1];
        if ($errorCode == 1062) {
            redirect("Errore: Chiave primaria duplicata.");
        } else if ($errorCode == 1451 || $errorCode == 1452) {
            redirect("Errore: Violazione vincolo di chiave esterna.");
        } else {
            redirect('Errore: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        redirect('Errore: ' . $e->getMessage());
    }
}

function redirect($messaggio)
{
    $nome_tabella = $_GET['nome_tabella'];
    echo "<script>alert('$messaggio'); window.location.replace('/pages/professore/riempi_tabella.php?nome_tabella=$nome_tabella')</script>";
}
