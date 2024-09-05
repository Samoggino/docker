<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);


function getNumeroNuovoQuesito($test_associato)
{
    try {
        $db = connectToDatabaseMYSQL();


        $sql = "CALL GetNumeroNuovoQuesito(:test_associato)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test_associato, PDO::PARAM_STR);
        $stmt->execute();
        $ultimo_quesito = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($ultimo_quesito == null) {
            return 1;
        } else {
            return $ultimo_quesito['numero_quesito'] + 1;
        }
    } catch (\Throwable $th) {
        echo "<script>console.log('Errore: " . $th->getMessage() . "');</script>";
        // throw $th;
    }
}
