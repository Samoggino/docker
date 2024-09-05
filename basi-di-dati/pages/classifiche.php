<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['email']) == false) {
    header('Location: ../index.php');
}

$db = connectToDatabaseMYSQL();
$sql = "CALL GetClassificaRisposteGiuste();";

$stmt = $db->prepare($sql);
$stmt->execute();
$classificaPrecisione = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$sql = "CALL GetClassificaTestCompletati();";
$stmt = $db->prepare($sql);
$stmt->execute();
$classificaTestCompletati = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$sql = "CALL GetClassificaQuesitiPerNumeroRisposte()";
$stmt = $db->prepare($sql);
$stmt->execute();
$classificaQuesitiPerNumeroRisposte = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SESSION['ruolo'] == 'STUDENTE') {
    $sql = "CALL GetMatricola(:email_studente);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email_studente', $_SESSION['email']);
    $stmt->execute();
    $matricola = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
}
// select matricola ed evidenzia i record di quello studente

$db = null;

function checkMatricola($row)
{
    global $matricola;
    if ($_SESSION['ruolo'] != 'STUDENTE' || isset($_SESSION['ruolo']) == false) {
        echo "<td>";
        echo $row['matricola'];
        return;
    } else {
        if ($row['matricola'] == $matricola['matricola']) {
            echo "<td style='color:green;'>";
            echo  $row['matricola'];
        } else {
            echo "<td>";
            echo $row['matricola'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classifiche</title>
    <link rel="icon" href="../images/favicon/favicon.ico">
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/eseguiTest.css">
    <link rel="stylesheet" href="../styles/classifiche.css">

</head>

<body>
    <!-- TODO: si può ancora cambiare il colore delle tabelle, ma per il resto va tutto bene -->
    <div id="intestazione">
        <div class="icons-container">
            <a class="logout" href='/pages/logout.php'></a>
            <a class="home" href='/pages/<?php echo strtolower($_SESSION['ruolo']) . "/" . strtolower($_SESSION['ruolo']) . "php" ?>'></a>
        </div>
        <h1>Classifiche</h1>
    </div>
    <div class="container-classifiche">

        <!-- TEST COMPLETATI -->
        <div class="widget-classifica">
            <h2>Test completati</h2>
            <table>
                <tr>
                    <th>Matricola</th>
                    <th>Test completati</th>
                </tr>
                <?php
                foreach ($classificaTestCompletati as $row) {
                    echo "<tr>";
                    checkMatricola($row);
                    echo "<td>" . $row['Test_conclusi'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <!-- PRECISIONE -->
        <div class="widget-classifica">
            <h2>Precisione delle risposte</h2>
            <table>
                <tr>
                    <th>Matricola</th>
                    <th>Percentuale risposte corrette</th>
                </tr>
                <?php
                foreach ($classificaPrecisione as $row) {
                    echo "<tr>";
                    checkMatricola($row);
                    echo "</td>";
                    echo "<td>" . $row['Rapporto'] * 100 . "%</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <!-- NUMERO RISPOSTE -->
        <div class="widget-classifica">
            <h2>Quesiti con maggior numero di risposte</h2>
            <table>
                <tr>
                    <th>Test</th>
                    <th>Quesito</th>
                    <th>Numero di risposte</th>
                </tr>
                <?php
                foreach ($classificaQuesitiPerNumeroRisposte as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['test_associato'] . "</td>";
                    echo "<td>" . $row['numero_quesito'] . "</td>";
                    echo "<td>" . $row['numero_risposte'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>

</body>

</html>