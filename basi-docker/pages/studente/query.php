<?php
session_start();
require "../../helper/connessione_mysql.php";

function getRisposte($test_associato, $email_studente)
{
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetRisposteQuesiti(:test_associato, :email_studente);";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':test_associato', $test_associato);
    $stmt->bindParam(':email_studente', $email_studente);
    $stmt->execute();
    $risposte = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    return $risposte;
}
