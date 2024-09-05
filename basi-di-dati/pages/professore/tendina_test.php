<?php
require_once "../../helper/connessione_mysql.php";
session_start();

if ($_SESSION['ruolo'] != 'PROFESSORE' || !isset($_SESSION['email'])) {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}

function tendinaTest()
{
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetTestsDelProfessore(:email_professore);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email_professore', $_SESSION['email']);
    try {
        $stmt->execute();
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<option hidden value=0>Seleziona un test</option>";
        foreach ($tests as $test) {
            if (isset($_SESSION['modifica'])) {
                echo "<option value='" . $test['titolo'] . "'required>" . $test['titolo'] . "</option>";
            } elseif ($test['VisualizzaRisposte'] == 0) {
                echo "<option value='" . $test['titolo'] . "'required>" . $test['titolo'] . "</option>";
            }
        }

        if (count($tests) == 0 || $tests == null) {
            echo "<option hidden value=0>Non hai test da chiudere</option>";
            $stmt->closeCursor();
            $db = null;
            return 1;
        }
    } catch (\Throwable $th) {
        echo "<script>console.log('Errore: " . $th . "');</script>";
    }
    $stmt->closeCursor();
    $db = null;

    return 0;
}
