<?php
require_once 'connessione_mysql.php';
function stampaVincoli($nome_tabella)
{
    $db = connectToDatabaseMYSQL();
    $stmt = $db->prepare("CALL GetChiaviEsterne(:nome_tabella)");
    $stmt->bindParam(':nome_tabella', $nome_tabella);
    $stmt->execute();
    $chiavi_est = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    echo "<div>";
    foreach ($chiavi_est as $chiave) {
        echo strtoupper($chiave['nome_tabella']) . ". <span style='text-decoration:underline'>" . $chiave['nome_attributo'] . "</span> -> " .
            strtoupper($chiave['tabella_vincolata']) . ". <span style='text-decoration:underline'>" . $chiave['attributo_vincolato'] . "</span><br>";
    }
    echo "</div>";
}
