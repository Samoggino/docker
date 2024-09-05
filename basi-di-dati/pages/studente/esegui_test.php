<?php
session_start();
require '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (isset($_SESSION['email']) == false) {
    header('Location: ../index.php');
}

// Assicurati che il parametro test_associato sia stato passato tramite GET
if (isset($_GET['test_associato'])) {
    $test = $_GET['test_associato'];

    echo "<script> console.log('test scelto: " . $test . "');</script>";


    try {
        $db = connectToDatabaseMYSQL();
        test_gia_svolto($test);

        // Prepara la query per selezionare i quesiti associati al test
        $sql = "CALL GetQuesitiAssociatiAlTest(:test_associato);";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test);
        $stmt->execute();
        $quesiti = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();


        // Chiudi la connessione al database
        $db = null;
    } catch (PDOException $e) {
        // Gestisci eventuali eccezioni
        echo "Errore di connessione al database: " . $e->getMessage();
    }
} else {
    // Messaggio di errore se il parametro test_associato non è stato passato
    echo "Errore: il parametro test_associato non è stato fornito.";
}


function build_view_quesito($quesito, $db)
{
    if ($quesito['tipo_quesito'] == 'APERTO') {
        q_aperto($quesito);
    } elseif ($quesito['tipo_quesito'] == 'CHIUSO') {
        q_chiuso($quesito, $db);
    }
}

function q_chiuso($quesito, $db)
{
    echo "<div class='chiusi'>";
    echo "<h3>" . $quesito["numero_quesito"] . ". " . $quesito['descrizione'] . "</h3>";

    $sql_opzioni = "CALL GetOpzioniQuesitoChiuso(:id_quesito);";
    $statement_opzioni = $db->prepare($sql_opzioni);
    $statement_opzioni->bindParam(':id_quesito', $quesito['ID']);
    $statement_opzioni->execute();
    $opzioni = $statement_opzioni->fetchAll(PDO::FETCH_ASSOC);
    $statement_opzioni->closeCursor();

    // aggiungi il default checked
    foreach ($opzioni as $opzione) {
        echo "<input type='radio' name='quesito" . $quesito['numero_quesito'] . "' value='" . $opzione['numero_opzione'] . "' required>" . " " . $opzione['testo'] . "<br>";
    }

    echo "</div>";
}

function q_aperto($quesito)
{
    echo "<div class='aperti'>";
    echo "<h3>"  . $quesito["numero_quesito"] . ". " . $quesito['descrizione'] . "</h3>";
    echo "<textarea name='quesito" . $quesito['numero_quesito'] . "'> </textarea><br>";
    echo "</div>";
}


function test_gia_svolto($test)
{
    require '../../helper/check_closed.php';
    if (check_svolgimento($test, $_SESSION['email']) == 1) {
        header("Location: ../../pages/studente/risultati_test.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Test - <?php echo $_GET['test_associato']; ?></title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
    <link rel="stylesheet" href="../../styles/eseguiTest.css">

    <style>
        .widget-professore {
            margin: 0;
        }

        #tabelle-esterne {
            display: grid;
            justify-content: center;
            align-items: center;
            justify-items: center;
            align-content: center;
            width: 900px;
        }

        #quesiti.widget-professore {
            width: 500px;
            height: 700px;
        }

        .container {
            display: flex;
            flex-direction: row;
            grid-gap: <?php if (isset($tabelle) && $tabelle != null && count($tabelle) > 0) echo "4dw" ?>;
            justify-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Stile dei quesiti e delle tabelle */
        .chiusi,
        .aperti {
            margin-bottom: 20px;
            padding: 0 60px;
        }

        #vincoli.widget-professore {
            width: 800px;
            height: fit-content;
            padding: 20px 0;
        }

        #vincoli h2 {
            margin-top: 0;
        }

        input[type='radio'] {
            margin-right: 10px;
            width: auto;
            height: auto;
        }

        form {
            display: flex;
            flex-direction: column;
            align-content: center;
            justify-content: center;
            align-items: flex-start;
        }

        #tables {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
            align-content: center;
            margin-top: 1dvh;
            justify-content: center;
            width: 800px;
        }

        #tables .widget-classifica {
            max-height: 560px;
            width: 320px;
        }
    </style>
</head>

<body>

    <div id="intestazione">
        <div class="icons-container">
            <a class="logout" href='/pages/logout.php'></a>
            <a class="home" href='/pages/studente/studente.php'></a>
        </div>
        <h1>Esegui: <span style="color: red;"><?php echo strtoupper($_GET['test_associato']); ?></span></h1>
    </div>


    <div class="container">
        <div id="quesiti" class="widget-professore">
            <?php
            $db = connectToDatabaseMYSQL();

            // Se non ci sono quesiti per questo test, mostra un messaggio
            if (count($quesiti) == 0) {
                echo "<div class='vuoto'><h1>Non ci sono quesiti per questo test</h1></div>";
            } else {
            ?>
                <div style="overflow-y: scroll; width: 100%;">
                    <form method='post' action='../../helper/elabora_risposte.php'>
                        <input type='hidden' name='test_associato' value='<?php echo $_GET['test_associato'] ?>'>
                        <?php
                        // Mostra i quesiti nel form
                        foreach ($quesiti as $quesito) {
                            build_view_quesito($quesito, $db);
                            echo "<br>";
                        }
                        ?>
                        <div style="position: relative;left: 40%;">
                            <button type='submit'>Invia risposte</button>
                        </div>
                    </form>
                </div>
            <?php
            }
            $db = null;
            ?>
        </div>

        <?php
        include '../../helper/print_table.php';
        // mostra le tabelle a cui fa riferimento questo quesito
        $test = $_GET['test_associato'];

        $db = connectToDatabaseMYSQL();
        $sql = "CALL GetTabelleQuesito(:test_associato);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test);
        $stmt->execute();
        $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        ?>



        <?php
        include '../../helper/print_vincoli.php';
        if (count($tabelle) > 0 && $tabelle != null) {
        ?>
            <div id="tabelle-esterne">
                <div id="vincoli" class="widget-professore">
                    <h2>Vincoli di integrità</h2>
                <?php }
            foreach ($tabelle as $tabella) {
                stampaVincoli($tabella['nome_tabella']);
            }
                ?>
                </div>
                <div id="tables">
                    <?php
                    foreach ($tabelle as $tabella) {
                        echo "<div class='widget-classifica'>";
                        generateTable($tabella['nome_tabella']);
                        echo "</table>";
                        echo "</div>";
                        echo "<br>";
                    }
                    ?>
                </div>
            </div>
    </div>


    <script>
        // controlla che textarea non sia vuoto e contenga almeno 6 caratteri
        var form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            var textareas = document.querySelectorAll('textarea');
            for (var i = 0; i < textareas.length; i++) {
                if (textareas[i].value.length < 6) {
                    alert('Risposta non data');
                    event.preventDefault();
                    return;
                }
            }
        });

        // non inviare il form se sono presenti parole pericolose come DELETE, DROP, ecc. ignorando maiuscole e minuscole
        form.addEventListener('submit', function(event) {
            var textareas = document.querySelectorAll('textarea');
            for (var i = 0; i < textareas.length; i++) {
                var risposta = textareas[i].value;
                var rispostaOriginale = risposta; // Salva il testo originale
                risposta = risposta.toUpperCase(); // Modifica il testo solo per il controllo

                // Esegui la verifica solo sul testo modificato
                if (risposta.includes('DELETE') || risposta.includes('DROP') || risposta.includes('TRUNCATE') || risposta.includes('ALTER') || risposta.includes('UPDATE') || risposta.includes('INSERT') || risposta.includes('DATABASE') || risposta.includes('PROCEDURE')) {
                    alert('Non puoi modificare il database con la tua risposta!');
                    event.preventDefault();
                    return;
                }
            }
        });

        // rimuovi gli accapo da textarea
        form.addEventListener('submit', function(event) {
            var textareas = document.querySelectorAll('textarea');
            for (var i = 0; i < textareas.length; i++) {
                textareas[i].value = textareas[i].value.replace(/\n/g, ' ');
            }
        });


        // le virgolette doppie vengono sostituite con quelle singole
        form.addEventListener('submit', function(event) {
            var textareas = document.querySelectorAll('textarea');
            for (var i = 0; i < textareas.length; i++) {
                textareas[i].value = textareas[i].value.replace(/"/g, " ciao ");
                textareas[i].value = textareas[i].value.replace(/'/g, " ciao ");
            }
        });
    </script>

</body>

</html>