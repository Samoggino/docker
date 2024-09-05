<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// TODO: gestire il caso in cui non ci siano valori nella tabella di riferimento

if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}
if (isset($_POST)) {
    echo "<script>console.log('POST: " . json_encode($_POST) . "');</script>";
    unset($_POST);
}
if (isset($_GET['nome_tabella'])) {
    $nome_tabella = $_GET['nome_tabella'];
    try {
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare("CALL GetAttributiTabella(:nome_tabella)");
        $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
        $stmt->execute();
        $attributi = $stmt->fetchAll();
        $stmt->closeCursor();


        try {
            // prendi i valori degli attributi
            $stmt = $db->prepare("SELECT * FROM $nome_tabella");
            $stmt->execute();
            $valori = $stmt->fetchAll();
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $errorCode = $e->errorInfo[1];
            // se la tabella non esiste, non continuare l'esecuzione ma return
            if ($errorCode == 1146) {
                // eliminare la tabella logica
                $db = connectToDatabaseMYSQL();
                $stmt = $db->prepare("CALL EliminaTabella(:nome_tabella)");
                $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->closeCursor();


                // prova ad eliminare la tabella fisica
                $stmt = $db->prepare("DROP TABLE IF EXISTS :nome_tabella");
                $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->closeCursor();


                insertOnMONGODB(
                    'eliminazione_tabella',
                    [
                        'tabella' => $nome_tabella
                    ],
                    'Eliminazione della tabella ' . $nome_tabella . ' a causa di un errore',
                );
                echo "<script> alert('Tabella non esistente'); window.location.href = '" . strtolower($_SESSION['ruolo']) . ".php'; </script>";
                return;
            }
        }

        try {
            $db = connectToDatabaseMYSQL();
            $stmt = $db->prepare("CALL GetChiaviEsterne(:nome_tabella)");
            $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
            $stmt->execute();
            $tabelle_riferite = $stmt->fetchAll();
            echo "<script>console.log('TABELLE RIFERITE: " . json_encode($tabelle_riferite) . "');</script>";
            $stmt->closeCursor();
        } catch (\Throwable $th) {
            echo "<script>alert('PROBLEM VINCOLI <br>" . $th->getMessage() . ")</script>";
        }


        if ($valori == null) {
            if (!isset($_GET['factory'])) {
                echo "<script>alert('La tabella è vuota, inserisci dei valori!');</script>";
            }
        }
    } catch (\Throwable $th) {
        echo "<script>alert('PROBLEM <br>" . $th->getMessage() . ")</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Inserisci valori</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
    <style>
        <?php
        if (isset($tabelle_riferite) && $tabelle_riferite != null) {

        ?>.tabelle {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc(33.33% - 20px), 1fr));
            grid-gap: 50px;
            justify-content: center;
        }

        <?php
        } else {

        ?>.tabelle {
            position: relative;
            justify-content: center;
            max-width: 50%;
            left: 25%;
        }

        <?php } ?>input {
            width: auto;
            height: auto;
            border-radius: 0;
            font-size: 15px;
            font-weight: 600;
            padding: 0;
            background-color: lightgray;
        }


        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .vincoli.widget-classifica.on {
            width: 97%;
            margin-bottom: 10px;
            box-shadow: 1px 1px black;
        }

        .vincoli.widget-classifica.off {
            display: none;
        }

        .mostra-vincoli {
            position: relative;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .widget-classifica {
            padding: 0px 20px 0px 20px;
            max-height: 751px;
        }

        .widget-classifica button {
            margin-top: 4px;
            margin-bottom: 8px;
        }

        #intestazione.homepage {
            display: grid;
            grid-template-columns: 0.1fr 0.8fr 0.1fr;
            justify-items: center;
            align-items: center;
        }
    </style>
</head>

<body>

    <!-- FIXME: sistema il bottone del "mostra-vincoli" che ho modificato aggiungendo la grid di intestazione -->

    <div id="intestazione" class="homepage">
        <div class="icons-container">
            <a class="logout" href="/pages/logout.php"></a>
            <a class="home" href="/pages/<?php echo strtolower($_SESSION['ruolo']) . '/' . strtolower($_SESSION['ruolo']) . ".php" ?>"></a>
        </div>
        <h1>Inserisci valori</h1>
        <?php
        if ($tabelle_riferite != null) {
            echo ' <button class="mostra-vincoli" onclick="mostraVincoli()">Mostra vincoli</button>';
        } ?>
    </div>
    <div id="riempi">

        <?php
        if (isset($tabelle_riferite) && $tabelle_riferite != null) {
        ?>
            <div class="vincoli widget-classifica off">
                <table>
                    <tr>
                        <th>Attributo in <?php echo $nome_tabella ?></th>
                        <th>Reference </th>
                    </tr>
                    <tbody>
                        <?php foreach ($tabelle_riferite as $tabella) { ?>
                            <tr>
                                <td><?php echo strtoupper($tabella['nome_tabella']) . "." .  $tabella['nome_attributo'] . " ===> "; ?></td>
                                <td><?php echo strtoupper($tabella['tabella_vincolata']) . "." . $tabella['attributo_vincolato']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        <div class="tabelle">
            <div class="widget-classifica">
                <form style="margin-bottom:0" method='post' action='/pages/professore/riempi_tabella_handler.php?nome_tabella=<?php echo $nome_tabella; ?>'>

                    <?php
                    require_once '../../helper/print_table.php';
                    generateTable($nome_tabella);
                    ?>
                    <tbody>
                        <tr>
                            <?php foreach ($attributi as $attributo) {
                                $tipo_placeholder = $attributo['tipo_attributo'];
                                $placeholder = $attributo['nome_attributo'];

                                echo "<td><input name='$placeholder' placeholder='Inserisci *$placeholder*'";

                                if (strtoupper($tipo_placeholder) == 'INT') {
                                    echo "type='number' step='1'";
                                } else if (strtoupper($tipo_placeholder) == 'FLOAT') {
                                    echo "type='number' step='0.01'";
                                } else if (strtoupper($tipo_placeholder) == 'DATE') {
                                    echo "type='date'";
                                } else if (strtoupper($tipo_placeholder) == 'DECIMAL') {
                                    echo "type='number' step='0.01'";
                                } else {
                                    echo "type='text'";
                                }
                                if ($attributo['is_key'] == "TRUE") {
                                    echo  "required>";
                                }
                                echo "</td>";
                            } ?>
                        </tr>
                    </tbody>
                    </table>
                    <button type='submit'>Aggiungi riga</button>
                </form>
            </div>

            <!-- mostra anche le tabelle a cui la tabella in get fa reference se ne ha-->
            <?php

            if (isset($tabelle_riferite) && $tabelle_riferite != null) {
            ?>
                <?php
                require_once '../../helper/print_table.php';
                require_once '../../helper/connessione_mysql.php';

                $db = connectToDatabaseMYSQL();

                $sql = "CALL GetTabelleRiferite(:nome_tabella)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
                $stmt->execute();
                $tabelle_riferite = $stmt->fetchAll();
                $stmt->closeCursor();

                foreach ($tabelle_riferite as $tabella) {

                    echo '<div class="widget-classifica">';
                    generateTable($tabella['tabella_vincolata']);
                    echo "</table> </div>";
                }

                ?>

            <?php } ?>
        </div>


        <script>
            function mostraVincoli() {
                var vincoli = document.querySelector('.vincoli');
                vincoli.classList.toggle('on');
                vincoli.classList.toggle('off');
            }
        </script>
</body>

</html>