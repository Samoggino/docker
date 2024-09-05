<?php
require_once 'connessione_mysql.php';

function printQuesitiDiTest($test_associato, $test_aperto = false)
{
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetQuesitiAssociatiAlTest(:test_associato)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':test_associato', $test_associato);
    $stmt->execute();
    $quesiti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (empty($quesiti)) {
        return;
    }

    echo '<div id="quesiti-test" style="';
    if ($test_aperto) {
        echo "max-width: min-content;";
    } else {
        echo "max-width: 100%;";
    }
    echo '" class="widget-classifica">';

    echo "<table>";
    echo "<tr>";
    echo "<th>Numero quesito</th>";
    echo "<th>Descrizione</th>";
    echo "<th>Difficoltà</th>";
    echo "<th>Tipo</th>";
    echo "<th>Tabelle di riferimento</th>";
    echo "<th>Elimina quesito</th>";
    echo "</tr>";

    foreach ($quesiti as $quesito) {
        $sql = "CALL GetTabelleQuesitiNum(:id_quesito)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_quesito', $quesito['ID']);
        $stmt->execute();
        $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $tabelle_di_riferimento = implode(", ", array_map(function ($tabella) {
            return $tabella['nome_tabella'];
        }, $tabelle)) ?: "-";

        echo "<tr>";
        echo "<td>" . $quesito['numero_quesito'] . "</td>";
        echo "<td>" . $quesito['descrizione'] . "</td>";
        echo "<td>" . $quesito['livello_difficolta'] . "</td>";
        echo "<td>" . $quesito['tipo_quesito'] . "</td>";
        echo "<td>" . $tabelle_di_riferimento . "</td>";
        echo "<td><a class='bin' title='Elimina quesito' onclick='deleteQuesito(" . $quesito['ID'] . ")'></a></td>";
        echo "</tr>";
    }

    echo "</table>";
    echo "</div>";
    $db = null;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/global.css">

    <style>
        .bin {
            background-image: url('/images/icons/bin.svg');
            display: inline-block;
            background-size: contain;
            background-repeat: no-repeat;
            width: 30px;
            height: 30px;
        }
    </style>
</head>

<body>

    <script>
        function deleteQuesito(id_quesito) {
            if (confirm("Sei sicuro di voler eliminare questo quesito?")) {
                fetch('/helper/elimina_quesito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_quesito=' + id_quesito
                }).then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert("Errore nella cancellazione del quesito");
                    }
                });
            }
        }
    </script>
</body>

</html>