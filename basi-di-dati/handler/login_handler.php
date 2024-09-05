<?php
session_start();
require '../helper/connessione_mongodb.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
function loginHandler()
{

    try {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $password = $_POST["password"];

            $db = connectToDatabaseMYSQL();

            try {
                $stmt = $db->prepare("CALL authenticateUser(:email, :password, @authenticated, @tipo_utente, @nome, @cognome)");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);

                $stmt->execute();
            } catch (\Throwable $th) {
                echo "<p>Errore nell'autenticazione dell'utente: " . $th->getMessage() . "</p>";
            }
            // Recupero dei valori dei parametri di output
            $stmt = $db->query("SELECT @authenticated, @tipo_utente, @nome, @cognome");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $authenticated = $result['@authenticated'];
            $tipo_utente = $result['@tipo_utente'];
            $nome = $result['@nome'];
            $cognome = $result['@cognome'];

            if ($authenticated) {
                // echo "<script>alert('Login effettuato con successo')</script>";

                $_SESSION['email'] = $email;
                $_SESSION['nome'] = $nome;
                $_SESSION['cognome'] = $cognome;

                if ($tipo_utente == 'STUDENTE') {
                    try {
                        $sql = "CALL GetMatricola(:email)";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->execute();
                        $studente = $stmt->fetch(PDO::FETCH_ASSOC);
                        $stmt->closeCursor();
                        $_SESSION['matricola'] = $studente['matricola'];
                        $_SESSION['ruolo'] = 'STUDENTE';
                        echo "<script>window.location.href = './studente/studente.php';</script>";
                    } catch (\Throwable $th) {
                        echo "<script>alert('Errore numero di matricola')</script>";
                    }
                } elseif ($tipo_utente  == 'PROFESSORE') {

                    $_SESSION['ruolo'] = 'PROFESSORE';
                    echo "<script>window.location.href = './professore/professore.php';</script>";
                }
            } else {
                echo "<script>alert('Credenziali errate'); window.location.href = '../pages/login.php'</script>";
            }
        }
    } catch (\Throwable $th) {
        //  throw $th;
        echo "<script>alert('Errore generico')</script>";
    }
}
