<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function check_svolgimento($test, $email_studente)
{
    try {
        $db = connectToDatabaseMYSQL();
        // Preparazione e esecuzione della stored procedure
        $stmt = $db->prepare("CALL VerificaTestConcluso(?, ?, @is_closed)");
        $stmt->bindParam(1, $email_studente, PDO::PARAM_STR);
        $stmt->bindParam(2, $test, PDO::PARAM_STR);
        $stmt->execute();

        // Recupero del valore del parametro di output
        $stmt = $db->query("SELECT @is_closed AS is_closed");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_closed = $result['is_closed'];
        // $db = null;
    } catch (\Throwable $th) {
        throw $th;
    }

    return $is_closed;
}
