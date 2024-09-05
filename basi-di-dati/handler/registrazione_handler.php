<?php
require_once '../helper/connessione_mysql.php';
require_once '../helper/connessione_mongodb.php';
require_once '../composer/vendor/autoload.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


function registrazione()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = $_POST["nome"];
        $cognome = $_POST["cognome"];
        $email = $_POST["email"];
        $PASSWORD = $_POST["password"];
        $tipoUtente = $_POST["tipo_utente"];
        $telefono = $_POST["telefono"];

        // controlla se l'email è già presente nel database
        $db = connectToDatabaseMYSQL();
        $sql = "CALL CercaUtente(:email)";
        $query = $db->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        if ($result) {
            echo "<script>alert('Email già presente nel database'); window.location.href = '../pages/login.php' </script>";
        }


        $db = connectToDatabaseMYSQL();
        if ($tipoUtente == "studente") {
            $annoImmatricolazione = $_POST["anno_immatricolazione"];
            $matricola = $_POST["matricola"];

            if ($annoImmatricolazione == "" || $matricola == "") {
                echo "<script>alert('Inserisci l\'anno di immatricolazione e la matricola'); window.location.href = '../pages/login.php' </script>";
            }

            insertNewStudent($db, $email, $nome, $cognome, $PASSWORD, $telefono, $annoImmatricolazione, $matricola);
        } elseif ($tipoUtente == "professore") {
            $dipartimento = $_POST["dipartimento"];
            $corso = $_POST["corso"];

            if ($dipartimento == "" || $corso == "") {
                echo "<script>alert('Inserisci il dipartimento e il corso di appartenenza'); window.location.href = '../pages/login.php' </script>";
            }

            insertNewProfessor($db, $email, $nome, $cognome, $PASSWORD, $telefono, $dipartimento, $corso);
        } elseif ($tipoUtente == "" || $tipoUtente == null) {
            echo "<script>alert('Seleziona almeno una delle opzioni: Studente o Professore.'); </script>";
        }
    }
}

function insertNewStudent($db, $email, $nome, $cognome, $password, $telefono, $annoImmatricolazione, $matricola)
{


    try {
        $sql = "CALL InserisciNuovoStudente(:email, :nome, :cognome, :password, :matricola, :annoImmatricolazione, :telefono)";
        $query = $db->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':nome', $nome, PDO::PARAM_STR);
        $query->bindParam(':cognome', $cognome, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':annoImmatricolazione', $annoImmatricolazione, PDO::PARAM_STR);
        $query->bindParam(':matricola', $matricola, PDO::PARAM_STR);

        if ($telefono != null || $telefono != "") {
            $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        } else {
            $query->bindParam(':telefono', $telefono, PDO::PARAM_NULL);
        }

        if ($query->execute()) {

            echo "<script>alert('TEST')</script>";

            $_SESSION['nome'] = $nome;
            $_SESSION['cognome'] = $cognome;
            $_SESSION['email'] = $email;
            $_SESSION['ruolo'] = 'STUDENTE';
            $_SESSION['matricola'] = $matricola;

            try {

                insertOnMONGODB(
                    'registrazione_studente',
                    [
                        'ruolo' => 'STUDENTE',
                        'email' => $email,
                        'nome' => $nome,
                        'cognome' => $cognome,
                        'anno_immatricolazione' => $annoImmatricolazione,
                        'matricola' => $matricola,
                        'telefono' => $telefono ?? ""
                    ],
                    'Lo studente ' .  $cognome . " " . $nome  .  ' è stato registrato con successo! La sua email è: ' . $email
                );
                echo "<script>alert('Benvenuto $nome $cognome'); window.location.href = './studente/studente.php';</script>";
            } catch (\Throwable $th) {
                echo "<script>alert('Errore di mongoDB: " . $th->getMessage() . "'); window.location.href = '../pages/login.php' </script>";
            }
        }
    } catch (\Throwable $th) {
        echo "<script>alert('Errore: " . $th->getMessage() . "'); window.location.href = '../pages/login.php' </script>";
    }
}

function insertNewProfessor($db, $email, $nome, $cognome, $password, $telefono, $dipartimento, $corso)
{
    try {
        $sql = "CALL InserisciNuovoProfessore(:email, :nome, :cognome, :password, :dipartimento, :corso, :telefono)";
        $query = $db->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':nome', $nome, PDO::PARAM_STR);
        $query->bindParam(':cognome', $cognome, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':dipartimento', $dipartimento, PDO::PARAM_STR);
        $query->bindParam(':corso', $corso, PDO::PARAM_STR);

        if ($telefono != null || $telefono != "") {
            $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        } else {
            $query->bindParam(':telefono', $telefono, PDO::PARAM_NULL);
        }

        if ($query->execute()) {
            $_SESSION['nome'] = $nome;
            $_SESSION['cognome'] = $cognome;
            $_SESSION['email'] = $email;
            $_SESSION['ruolo'] = 'PROFESSORE';

            try {
                insertOnMONGODB(
                    'registrazione_professore',
                    [
                        'ruolo' => 'PROFESSORE',
                        'email' => $email,
                        'nome' => $nome,
                        'cognome' => $cognome,
                        'dipartimento' => $dipartimento,
                        'corso' => $corso,
                        'telefono' => $telefono ?? ""
                    ],
                    'Il professore ' .  $cognome . " " . $nome  .  ' è stato registrato con successo! La sua email è: ' . $email
                );
                echo "<script>alert('Benvenuto professor $nome $cognome'); window.location.href = './professore/professore.php';</script>";
            } catch (\Throwable $th) {
                echo "<script>alert('Errore di mongoDB: " . $th->getMessage() . "'); window.location.href = '../pages/login.php' </script>";
            }
        }
    } catch (\Throwable $th) {
        echo "<script>alert('Errore: " . $th->getMessage() . "'); </script>";
    }
}
