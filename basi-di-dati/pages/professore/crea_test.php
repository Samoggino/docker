<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "../../helper/connessione_mysql.php";
require  '../../composer/vendor/autoload.php'; // include Composer's autoloader
require_once "../../helper/connessione_mongodb.php";


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php');</script>";
}

try {

    if (isset($_POST['titolo_test_creato'])) {
        echo "<script>console.log('SONO DENTRO');</script>";
        // Connessione al database
        $db = connectToDatabaseMYSQL();

        echo "<script>console.log('Test associato POST: " . $_POST['titolo_test_creato'] . "');</script>";
        $test_associato = $_POST['titolo_test_creato'];

        // se c'è un apostrofo nel titolo del test, sostituiscilo con uno spazio
        if (strpos($test_associato, "'") !== false) {
            $test_associato = str_replace("'", " ", $test_associato);
        }


        // controllo se il test è già presente
        $sql = "CALL GetTest(:test_associato)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test_associato);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($result != null) {
            echo "<script> alert('Test $test_associato già presente!'); 
                            window.location.replace('/pages/professore/crea_test.php');</script>";
        }

        $sql = "CALL InserisciNuovoTest(:test_associato, :email_professore)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test_associato);
        $stmt->bindParam(':email_professore', $_SESSION['email']);

        if ($stmt->execute()) {
            $stmt->closeCursor();

            // Salva il titolo del test nella sessione
            $_SESSION['test_associato'] = $test_associato;

            // Verifica se è stata selezionata un'immagine
            if (isset($_FILES["file_immagine"]) && $_FILES["file_immagine"]["error"] == UPLOAD_ERR_OK) {
                // Leggi il file dell'immagine
                $dati_immagine = file_get_contents($_FILES["file_immagine"]["tmp_name"]);

                // Prepara la query per l'inserimento dell'immagine
                $sql = "CALL InserisciNuovaFotoTest(:dati_immagine, :test_associato)";
                $stmt = $db->prepare($sql);

                // Associa i dati dell'immagine e il titolo del test alla query
                $stmt->bindParam(':dati_immagine', $dati_immagine, PDO::PARAM_LOB);
                $stmt->bindParam(':test_associato', $test_associato);

                // Esegui la query
                if ($stmt->execute()) {
                    echo "<script>alert('Test e immagine caricati con successo.');<script>";
                } else {
                    echo "script>alert('Errore in inserimento dell'immagine');<script>";
                }
            }


            // inserisci su mongodb
            insertOnMONGODB(
                'test',
                [
                    'titolo' => $test_associato,
                    'professore' => $_SESSION['email'],
                    'foto' => isset($_FILES["file_immagine"]) ? $_FILES["file_immagine"]["name"] : null
                ],
                'Test inserito da ' . $_SESSION['email'] . ' con titolo ' . $test_associato
            );


            echo "<script> alert('Test inserito con successo!');</script>";
        } else {
            echo "<script>console.log('Test associato fallito: " . $test_associato . "');</script>";
            echo "<script> alert('Errore durante l'inserimento del test!');</script>";
        }


        unset($_POST['titolo_test_creato']);
        echo '<script>window.location.replace("/pages/professore/crea_test.php?test_associato=' . $test_associato . '");</script>';
    }
} catch (\Throwable $th) {
    echo "ERRORE:<br>" . $th->getMessage() . "";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <title>Creazione test</title>
    <link rel="stylesheet" href="../../styles/global.css">
    <link rel="stylesheet" href="../../styles/creaTest.css">

    <style>

    </style>

</head>

<body>

    <!-- Se sto creando il test -->
    <?php
    if (!isset($_GET['test_associato']) && !isset($_POST['titolo_test_creato'])) { ?>
        <form id="uploadForm" method="post" action="" enctype="multipart/form-data">
            <div id="intestazione">
                <div class="icons-container">
                    <a class="logout" href='/pages/logout.php'></a>
                    <a class="home" href='/pages/<?php echo strtolower($_SESSION['ruolo']) . "/" . strtolower($_SESSION['ruolo']) . "php" ?>'></a>
                </div>
                <h1>Crea test</h1>
            </div>

            <div class="widget-professore crea">
                <div style="display: flex;align-items: center;">
                    <input for="titolo_test_creato" name="titolo_test_creato" placeholder="Titolo" type="text" required>
                    <div id="select-image">
                        <label for="file_immagine" class="custom-file-input-label"></label>
                        <input id="file_immagine" type="file" name="file_immagine" accept="image/*" class="custom-file-input" style="display:none">
                    </div>
                </div>
                <button type="submit"> Crea </button>
            </div>
        </form>
    <?php } ?>
    <!-- Se ho già il test ma devo riempirlo con i quesiti -->
    <?php if (isset($_GET['test_associato']) || isset($_POST['titolo_test_creato'])) {
        $test_associato = $_GET['test_associato'] ?? $_POST['titolo_test_creato'];

        $sql = "CALL GetTest(:test_associato)";
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test_associato);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($result == null) {
            echo "<script>window.location.replace('/pages/professore/crea_test.php');</script>";
        }

    ?>
        <div id="intestazione" class="homepage">
            <div class="icons-container">
                <a class="logout" href="/pages/logout.php"></a>
                <a class="home" href="/pages/<?php echo strtolower($_SESSION['ruolo']) . '/' . strtolower($_SESSION['ruolo']) . ".php" ?>"></a>
            </div>
            <?php
            echo "<h1> $test_associato</h1>";
            ?>
            <a class="bin" title="Elimina test" onclick="deleteTest('<?php echo $test_associato ?> ');"></a>
        </div>
        <input hidden id='test_associato' value='<?php echo $test_associato ?>'></input>

        <div id="quesiti" class="on">

            <?php
            $db = connectToDatabaseMYSQL();
            $sql = "CALL GetTestsDelProfessoreAperti(:email_professore)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':email_professore', $_SESSION['email']);
            $stmt->execute();
            $tests_aperti_del_prof = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // se il test non è aperto, non permettere di aggiungere quesiti
            $test_aperto = false;
            foreach ($tests_aperti_del_prof as $test) {
                if ($test['titolo'] == $test_associato) {
                    $test_aperto = true;
                    break;
                }
            }

            if ($test_aperto) {
            ?>
                <div style="display: flex;">
                    <?php
                    $db = connectToDatabaseMYSQL();
                    // Recupera l'immagine dal database
                    $sql = "CALL RecuperaFotoTest(:test_associato)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':test_associato', $test_associato);
                    $stmt->execute();
                    $quesito = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Se è stata trovata un'immagine, visualizzala
                    if ($quesito != null && count($quesito) > 0) {
                    ?>
                        <div class="widget-professore immagine-test">
                            <h2>Immagine del test</h2>
                            <?php
                            $immagine = $quesito['foto'];
                            echo "<img src='data:image/jpeg;base64," . base64_encode($immagine) . "' alt='Errore'>";
                            ?>
                        </div>
                    <?php
                    }
                    ?>

                    <div class="widget-professore">
                        <h2 style="margin-bottom:0">Aggiungi quesito</h2>
                        <div class="scrollable-widget">
                            <!-- crea dei quesiti per il test, il quesito è fatto con un enum per la difficoltà e un campo per la descrizione -->
                            <form method="POST" action="crea_quesito.php?test_associato=<?php echo $test_associato ?>" id="form-quesito">
                                <div class="lable-input-container" style="flex-direction: row; gap:5px; align-items:flex-start">
                                    <div style="flex-direction: column;display:flex;align-items:center">
                                        <label for="descrizione" name="descrizione">Descrizione:</label>
                                        <input for="descrizione" name="descrizione" placeholder="Descrizione" type="text" required>
                                    </div>
                                    <div style="flex-direction: column;display:flex;">
                                        <label for="difficolta" name="difficolta">Difficoltà:</label>
                                        <select for="difficolta" name="difficolta" id="difficolta" required>
                                            <option value="BASSO">Basso</option>
                                            <option value="MEDIO">Medio</option>
                                            <option value="ALTO">Alto</option>
                                        </select>
                                    </div>
                                </div>



                                <div>
                                    <div class="checkbox-container">
                                        <label for="quesito-aperto-checkbox">Aperto</label>
                                        <input type="checkbox" id="quesito-aperto-checkbox" name="APERTO">
                                    </div>
                                    <div id="APERTO" style="display: none;">
                                        <div class="add-remove-container">
                                            <button type="button" id="aggiungi_soluzione">Aggiungi soluzione</button><br>
                                            <button type="button" id="rimuovi_soluzione">Rimuovi soluzione</button><br>
                                        </div>
                                        <div id="soluzione_aperto">
                                            <div class="quesito-aperto">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="checkbox-container">
                                        <label for="quesito-chiuso-checkbox">Chiuso</label>
                                        <input type="checkbox" id="quesito-chiuso-checkbox" name="CHIUSO">
                                    </div>
                                    <div id="CHIUSO" style="display: none;">
                                        <div class="add-remove-container">
                                            <button type="button" id="aggiungi_opzione">Aggiungi opzione</button><br>
                                            <button type="button" id="rimuovi_opzione">Rimuovi opzione</button><br>
                                        </div>
                                        <div id="opzioni_chiuso">
                                            <div class="quesito-chiuso">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- seleziona le tabelle di esercizio a cui fare riferimento -->

                                <div class="lable-input-container">
                                    <label for="tabelleRiferimento">Tabelle di riferimento:</label>
                                    <select id="tabelleRiferimento" name="tabelle[]" multiple>
                                        <?php
                                        $sql = "CALL GetTabelleCreate()";
                                        $db = connectToDatabaseMYSQL();
                                        $stmt = $db->prepare($sql);
                                        $stmt->execute();
                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($result as $row) {
                                            echo "<option value='" . $row['nome_tabella'] . "'>" . $row['nome_tabella'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <input type="hidden" for="tipo_quesito" name="tipo_quesito" id="tipo_quesito" value="">
                        </div>

                        <button type="submit" value="crea il test" style="width:fit-content;">Aggiungi quesito</button>
                        </form>
                    </div>
                </div>
            <?php } ?>


            <?php
            require "../../helper/print_quesiti_di_test.php";
            if (isset($_GET['test_associato']) || isset($_POST['titolo_test_creato'])) {
                // stampa i quesiti associati al test
                $test_associato = isset($_GET['test_associato']) ? $_GET['test_associato'] : $_POST['titolo_test_creato'];
                printQuesitiDiTest($test_associato, $test_aperto);
            }
            ?>
        </div>
    <?php } ?>
</body>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var quesito_aperto_checkbox = document.getElementById("quesito-aperto-checkbox");
        var quesito_chiuso_checkbox = document.getElementById("quesito-chiuso-checkbox");
        var aperto_div = document.getElementById("APERTO");
        var chiuso_div = document.getElementById("CHIUSO");
        var tipoUtenteInput = document.getElementById("tipo_quesito"); // Definizione della variabile tipoUtenteInput



        quesito_aperto_checkbox.addEventListener("change", function() {
            if (this.checked) {
                tipoUtenteInput.value = "APERTO";
                aperto_div.style.display = "block";
                quesito_chiuso_checkbox.checked = false; // Disabilita la checkbox chiuso quando selezioni aperto
                chiuso_div.style.display = "none";
                //  svuota chiuso_div
                var div_questo_chiuso = document.getElementById('opzioni_chiuso');
                div_questo_chiuso.innerHTML = '';


                // <input name="soluzione[]" placeholder="Soluzione" type="text" required>
                var quesito_aperto = document.createElement('div');
                quesito_aperto.className = 'quesito-aperto';

                if (document.getElementById('soluzione_aperto').children.length == 1) {
                    quesito_aperto.innerHTML = `<textarea name="soluzione[]" placeholder="Soluzione" type="text" required></textarea>`;
                    document.getElementById('soluzione_aperto').appendChild(quesito_aperto);
                }

            } else {
                aperto_div.style.display = "none";
            }
        });

        quesito_chiuso_checkbox.addEventListener("change", function() {
            if (this.checked) {
                tipoUtenteInput.value = "CHIUSO";
                chiuso_div.style.display = "block";
                quesito_aperto_checkbox.checked = false; // Disabilita la checkbox aperto quando selezioni chiuso
                aperto_div.style.display = "none"; // Nasconde il campo anno immatricolazione quando selezioni chiuso

                var quesito_chiuso = document.createElement('div');
                quesito_chiuso.className = 'quesito-chiuso';

                if (document.getElementById('opzioni_chiuso').children.length == 1) {
                    quesito_chiuso.innerHTML = `
                    <input name="opzione[]" placeholder="Opzione" type="text" required>
                    <div style="display:flex;flex-direction:column;margin-left: 25px;align-items: center;"> 
                        <label for="opzione_vera">Corretta</label>
                        <input name="opzione_vera[]" type="checkbox">
                    </div>
                `;
                    document.getElementById('opzioni_chiuso').appendChild(quesito_chiuso);
                }


                //  svuota aperto_div
                var div_questo_aperto = document.getElementById('soluzione_aperto');
                div_questo_aperto.innerHTML = '';
            } else {
                chiuso_div.style.display = "none";
            }
        });

        var form = document.getElementById("form-quesito");

        form.addEventListener("submit", function(event) {
            if (!quesito_aperto_checkbox.checked && !quesito_chiuso_checkbox.checked) {
                event.preventDefault(); // Impedisce l'invio del modulo se nessuna checkbox è selezionata
                alert("Seleziona almeno una delle opzioni: aperto o chiuso.");
            }
        });

    });



    // setta a FALSE le opzioni non flaggate come corrette
    var form = document.getElementById("form-quesito");

    form.addEventListener("submit", function(event) {
        var opzioni_vera = document.querySelectorAll('input[name="opzione_vera[]"]');
        opzioni_vera.forEach(function(opzione_vera) {
            if (!opzione_vera.checked) {
                var falsaInput = document.createElement('input');
                falsaInput.type = 'hidden';
                falsaInput.name = opzione_vera.name;
                falsaInput.value = 'FALSE';
                opzione_vera.parentNode.appendChild(falsaInput);
            }
        });
    });

    // se il quesito è chiuso almeno una risposta del quesito chiuso deve essere flaggata come corretta 


    var form = document.getElementById("form-quesito");

    form.addEventListener("submit", function(event) {

        if (document.getElementById("quesito-chiuso-checkbox").checked) {

            var opzioni_vera = document.querySelectorAll('input[name="opzione_vera[]"]');
            console.log(opzioni_vera);
            var almenoUnaVera = false;
            opzioni_vera.forEach(function(opzione_vera) {
                if (opzione_vera.checked) {
                    almenoUnaVera = true;
                }
            });
            if (!almenoUnaVera) {
                event.preventDefault();
                alert("Seleziona almeno una risposta corretta.");
            }
        }
    });


    // aggiunge righe per le opzioni del quesito chiuso
    document.addEventListener("DOMContentLoaded", function() {

        var quesito_chiuso_button = document.getElementById("aggiungi_opzione");
        quesito_chiuso_button.addEventListener('click', function() {
            var quesito_chiuso = document.createElement('div');
            quesito_chiuso.className = 'quesito-chiuso';
            quesito_chiuso.innerHTML = `
                    <input name="opzione[]" placeholder="Opzione" type="text" required>
                    <div style="display:flex;flex-direction:column;margin-left: 25px;align-items: center;"> 
                        <label for="opzione_vera">Corretta</label>
                        <input name="opzione_vera[]" type="checkbox">
                    </div>
                `;
            document.getElementById('opzioni_chiuso').appendChild(quesito_chiuso);
        });


        var rimuovi_opzione_button = document.getElementById("rimuovi_opzione");
        rimuovi_opzione_button.addEventListener('click', function() {
            var opzioni_chiuso = document.getElementById('opzioni_chiuso');
            if (opzioni_chiuso.children.length > 1) {
                opzioni_chiuso.removeChild(opzioni_chiuso.lastChild);
            }
        });
    });

    // aggiunge righe per il quesito aperto
    document.addEventListener("DOMContentLoaded", function() {
        var quesito_aperto_button = document.getElementById("aggiungi_soluzione");
        quesito_aperto_button.addEventListener('click', function() {
            var quesito_aperto = document.createElement('div');
            quesito_aperto.className = 'quesito-aperto';
            quesito_aperto.innerHTML = `
                <textarea type="text" name="soluzione[]" placeholder="Soluzione" required></textarea>`;
            document.getElementById('soluzione_aperto').appendChild(quesito_aperto);
        });

        var rimuovi_opzione_button = document.getElementById("rimuovi_soluzione");
        rimuovi_opzione_button.addEventListener('click', function() {
            var opzioni_chiuso = document.getElementById('soluzione_aperto');
            if (opzioni_chiuso.children.length > 1) {
                opzioni_chiuso.removeChild(opzioni_chiuso.lastChild);
            }
        });

    });

    // non permettere inserimenti di apostrofi in uploadForm
    document.getElementById('uploadForm').addEventListener('submit', function(event) {
        var titolo_test_creato = document.getElementById('titolo_test_creato');
        if (titolo_test_creato.value.includes("'")) {
            event.preventDefault();
            alert("Il titolo del test non può contenere apostrofi.");
            console.log(titolo_test_creato.value);
            return;
        }
    });

    function deleteTest(titolo_test) {
        if (confirm("Sei sicuro di voler eliminare questo test?")) {
            fetch('/helper/elimina_test.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'titolo_test=' + titolo_test
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

</html>