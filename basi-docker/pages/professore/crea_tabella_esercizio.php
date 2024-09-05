<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}
if (isset($_POST)) {
    echo "<script>console.log('POST: " . json_encode($_POST) . "');</script>";
    unset($_POST);
}

// Query per recuperare gli attributi di tutte le tabelle
$db = connectToDatabaseMYSQL();
$query = "CALL GetTabelleCreate()";
$stmt = $db->query($query);
$tabelle = array();
while ($quesito = $stmt->fetch(PDO::FETCH_NUM)) {
    $tabelle[] = $quesito[0];
}

$attributi = array();
foreach ($tabelle as $tabella) {
    $sql = "CALL GetPrimaryKey(:tabella)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':tabella', $tabella);
    $stmt->execute();

    while ($quesito = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Creazione di un array associativo con il nome e il tipo dell'attributo
        $attributo = [
            'tabella' => $tabella, // 'tabella' => 'NOME_TABELLA
            'nome' => $quesito['NOME_ATTRIBUTO'],
            'tipo' => $quesito['TIPO_ATTRIBUTO']
        ];

        // Aggiunta dell'array associativo all'array $attributi
        $attributi[$tabella][] = $attributo;
    }
}



// Includi l'array di attributi come parte del codice JavaScript
echo "<script>var attributiPerTabella = " . json_encode($attributi) . ";</script>";

?>


<!DOCTYPE html>

<head>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
    <link rel="stylesheet" href="../../styles/creaTabella.css">
    <title>Crea tabella</title>
</head>

<body>

    <div id="intestazione">
        <div class="icons-container">
            <a class="logout" href="/pages/logout.php"></a>
            <a class="home" href="/pages/professore/professore.php"></a>
        </div>
        <h1>Crea una tabella di esercizio</h1>
    </div>

    <form id="crea_tabella_form" action="../../handler/factory_tabella.php" method="POST">
        <div class="grid-container">

            <div class="widget-professore">
                <div class="lable-input-container">
                    <input type="text" id="nome_tabella" name="nome_tabella" placeholder="Nome tabella" required><br><br>
                </div>
                <div>
                    <input type="number" id="numero_attributi" placeholder="Numero di attributi" name="numero_attributi" min="1" required><br><br>
                </div>
                <button type="submit">Crea</button>
            </div>

            <div id="attributi_container"></div><br><br>
        </div>

    </form>

    <script>
        // se attributi_container è vuoto non mostrarlo
        var container = document.getElementById("attributi_container");
        container.style.display = "none";

        document.getElementById("numero_attributi").addEventListener("change", function() {
            var numeroAttributi = parseInt(this.value);
            var container = document.getElementById("attributi_container");

            // rimuoi display none e metti quello di #attributi_container
            container.style.display = "grid";
            container.className = "grid-container";
            container.innerHTML = '';

            for (var i = 0; i < numeroAttributi; i++) {
                var div = document.createElement("div");
                creaAttributoContainer(i, container, div);
            }
        });

        function creaAttributoContainer(i, container, div) {
            div.className = "widget-professore";
            div.innerHTML = '<input type="text" id="nome_attributo_' + i + '" placeholder= "Nome Attributo ' + (i + 1) + '"name="nome_attributo[]" required>' +
                '<div class="lable-input-container">' +
                '<label for="tipo_attributo_' + i + '">Tipo Attributo ' + (i + 1) + ':</label>' +
                '<select id="tipo_attributo_' + i + '" name="tipo_attributo[]" required onchange="populateAttributi(' + i + ')">' +
                '<option value="INT">INT</option>' +
                '<option value="VARCHAR">VARCHAR</option>' +
                '<option value="DATE">DATE</option>' +
                '<option value="DECIMAL">DECIMAL</option>' +
                '<option value="FLOAT">FLOAT</option>' +
                '</select>' +
                '</div>' +
                '<div class="checkbox-container">' +
                '<label for="primary_key_' + i + '">Primary Key</label>' +
                '<input type="checkbox" id="primary_key_' + i + '" name="primary_key[]" value="' + i + '">' +
                '</div>' +
                '<div class="checkbox-container">' +
                '<label for="foreign_key_' + i + '">Foreign Key</label>' +
                '<input type="checkbox" id="foreign_key_' + i + '" name="foreign_key[]" onchange="foreingKeyChecked(' + i + ')" value="' + i + '">' +
                '</div>' +
                '<div id="foreign_key_options_' + i + '" class="foreign-key-container" style="display: none;">' +
                '<label for="tabella_vincolata_' + i + '">Tabella esterna:</label>' +
                '<select id="tabella_vincolata_' + i + '" name="tabella_vincolata[]" onchange="populateAttributi(' + i + ')">' +
                '</select><br><br>' +
                '<label for="attributo_vincolato_' + i + '">Attributo esterno:</label>' +
                '<select id="attributo_vincolato_' + i + '" name="attributo_vincolato[]"></select><br><br>' +
                '</div>' +
                '<br><br>';
            container.appendChild(div);

            // Popola le opzioni per la tabella vincolata
            var tabellaVincolataSelect = document.getElementById("tabella_vincolata_" + i);

            <?php
            $db = connectToDatabaseMYSQL();
            $query = "CALL GetTabelleCreate()";
            $stmt = $db->query($query);
            while ($quesito = $stmt->fetch(PDO::FETCH_NUM)) {
                echo 'var option = document.createElement("option");';
                echo 'option.value = "' . $quesito[0] . '";';
                echo 'option.textContent = "' . $quesito[0] . '";';
                echo 'tabellaVincolataSelect.appendChild(option);';
            }
            ?>
        }

        // Mostra gli attributi corrispondenti alla tabella selezionata per la foreign key
        function populateAttributi(index) {

            var tabellaVincolata = document.getElementById("tabella_vincolata_" + index).value;
            var attributoVincolatoSelect = document.getElementById("attributo_vincolato_" + index);
            attributoVincolatoSelect.innerHTML = '';

            var attributi = attributiPerTabella[tabellaVincolata];
            if (attributi == null) {
                return;
            }
            for (var j = 0; j < attributi.length; j++) {
                var option = document.createElement("option");
                // se l'attributo è dello stesso tipo di quello che si sta creando
                // lo mostro, altrimenti no
                if (document.getElementById("tipo_attributo_" + index).value == attributi[j].tipo) {
                    option.value = attributi[j].nome;
                    option.textContent = attributi[j].nome;
                    attributoVincolatoSelect.appendChild(option);
                }
            }

            // se non ci sono attributi dello stesso tipo disabilito la foreign key
            if (attributoVincolatoSelect.innerHTML == '') {
                alert("Non ci sono attributi dello stesso tipo in altre tabelle.\nLa foreign key verrà disabilitata.");
                document.getElementById("foreign_key_" + index).checked = false;
                document.getElementById("foreign_key_options_" + index).style.display = "none";
            } else {
                document.getElementById("foreign_key_options_" + index).style.display = "flex";
            }
        }

        // Aggiungi un gestore di eventi per il submit del modulo
        document.getElementById("crea_tabella_form").addEventListener("submit", function(event) {
            // Recupera tutte le checkbox delle chiavi primarie
            var checkboxes = document.getElementsByName("primary_key[]");
            var isChecked = false;

            // Verifica se almeno una checkbox è stata selezionata
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    isChecked = true;
                    break;
                }
            }

            // Se nessuna checkbox è ,selezionata impedisci l'invio del modulo
            if (!isChecked) {
                event.preventDefault();
                alert("È necessario selezionare almeno una chiave primaria.");
            }
        });

        function foreingKeyChecked(index) {
            var foreignKeyCheckbox = document.getElementById("foreign_key_" + index);
            var optionsDiv = document.getElementById("foreign_key_options_" + index);
            if (foreignKeyCheckbox.checked) {
                optionsDiv.style.display = "flex";
            } else {
                optionsDiv.style.display = "none";
            }
        }

        // Mostra le opzioni per la foreign key quando la checkbox è selezionata
        var foreignKeyCheckboxes = document.getElementsByName("foreign_key[]");
        for (var i = 0; i < foreignKeyCheckboxes.length; i++) {
            foreignKeyCheckboxes[i].addEventListener("change", function() {
                var index = parseInt(this.id.split("_")[1]); // Ottieni l'indice dall'ID dell'elemento
                var optionsDiv = document.getElementById("foreign_key_options_" + index);
                if (this.checked) {
                    optionsDiv.style.display = "flex";
                } else {
                    optionsDiv.style.display = "none";
                }
            });
        }



        document.getElementById("crea_tabella_form").addEventListener("submit", function(event) {
            var nomeTabella = document.getElementById("nome_tabella").value;
            var attributi = document.getElementsByName("nome_attributo[]");
            var tipoAttributi = document.getElementsByName("tipo_attributo[]");

            for (var i = 0; i < attributi.length; i++) {
                for (var j = i + 1; j < attributi.length; j++) {
                    if (attributi[i].value.toUpperCase() == attributi[j].value.toUpperCase()) {
                        event.preventDefault();
                        alert("Non possono esserci attributi con lo stesso nome.");
                        return;
                    }
                }
            }
        });

        document.getElementById("crea_tabella_form").addEventListener("submit", function(event) {
            // controlla che il nome della tabella non sia uguale a quello di una tabella già creata, ignorando le maiuscole
            var nomeTabella = document.getElementById("nome_tabella").value;
            var tabelle = <?php echo json_encode($tabelle); ?>;
            for (var i = 0; i < tabelle.length; i++) {
                if (tabelle[i].toUpperCase() == nomeTabella.toUpperCase()) {
                    event.preventDefault();
                    alert("Esiste già una tabella con questo nome.");
                    return;
                }
            }
        });

        document.getElementById("crea_tabella_form").addEventListener("submit", function(event) {
            // non possono essere presenti spazi o caratteri speciali nei nomi degli attributi e delle tabelle
            var nomeTabella = document.getElementById("nome_tabella").value;
            var attributi = document.getElementsByName("nome_attributo[]");
            var regex = /^[a-zA-Z0-9_]+$/; // solo lettere, numeri e underscore

            if (!regex.test(nomeTabella)) {
                event.preventDefault();
                alert("Il nome della tabella non può contenere spazi o caratteri speciali.");
                return;
            }

            for (var i = 0; i < attributi.length; i++) {
                if (!regex.test(attributi[i].value)) {
                    event.preventDefault();
                    alert("Il nome degli attributi non può contenere spazi o caratteri speciali.");
                    return;
                }
            }
        });


        document.getElementById("crea_tabella_form").addEventListener("submit", function(event) {
            // il nome della tabella o di un attributo non può essere una parola speciale per MYSQL 

            var nomeTabella = document.getElementById("nome_tabella").value;
            var attributi = document.getElementsByName("nome_attributo[]");

            var parole_speciali = [
                "ADD", "ALL", "ALTER", "ANALYZE", "AND", "AS", "ASC", "ASENSITIVE", "BEFORE",
                "BETWEEN", "BIGINT", "BINARY", "BLOB", "BOTH", "BY", "CALL", "CASCADE", "CASE",
                "CHANGE", "CHAR", "CHARACTER", "CHECK", "COLLATE", "COLUMN", "CONDITION", "CONSTRAINT",
                "CONTINUE", "CONVERT", "CREATE", "CROSS", "CURRENT_DATE", "CURRENT_TIME", "CURRENT_TIMESTAMP",
                "CURRENT_USER", "CURSOR", "DATABASE", "DATABASES", "DAY_HOUR", "DAY_MICROSECOND", "DAY_MINUTE",
                "DAY_SECOND", "DEC", "DECIMAL", "DECLARE", "DEFAULT", "DELAYED", "DELETE", "DESC", "DESCRIBE",
                "DETERMINISTIC", "DISTINCT", "DISTINCTROW", "DIV", "DOUBLE", "DROP", "DUAL", "EACH", "ELSE", "ELSEIF",
                "ENCLOSED", "ESCAPED", "EXISTS", "EXIT", "EXPLAIN", "FALSE", "FETCH", "FLOAT", "FOR",
                "FORCE", "FOREIGN", "FROM", "FULLTEXT", "GENERAL", "GRANT", "GROUP", "HAVING", "HIGH_PRIORITY", "HOUR_MICROSECOND",
                "HOUR_MINUTE", "HOUR_SECOND", "IF", "IGNORE", "IN", "INDEX", "INFILE", "INNER", "INOUT", "INSENSITIVE", "INSERT",
                "INT", "INT1", "INT2", "INT3", "INT4", "INT8", "INTEGER", "INTERVAL", "INTO", "IS", "ITERATE", "JOIN", "KEY",
                "KEYS", "KILL", "LEADING", "LEAVE", "LEFT", "LIKE", "LIMIT", "LINEAR", "LINES", "LOAD", "LOCALTIME", "LOCALTIMESTAMP",
                "LOCK", "LONG", "LONGBLOB", "LONGTEXT", "LOOP", "LOW_PRIORITY", "MATCH", "MEDIUMBLOB", "MEDIUMINT", "MEDIUMTEXT", "MIDDLEINT",
                "MINUTE_MICROSECOND", "MINUTE_SECOND", "MOD", "MODIFIES", "NATURAL", "NOT", "NO_WRITE_TO_BINLOG", "NULL", "NUMERIC", "ON",
                "OPTIMIZE", "OPTION", "OPTIONALLY", "OR", "ORDER", "OUT", "OUTER", "OUTFILE", "PRECISION", "PRIMARY", "PROCEDURE", "PURGE",
                "READ", "READS", "REAL", "REFERENCES", "REGEXP", "RELEASE", "RENAME", "REPEAT", "REPLACE", "REQUIRE", "RESTRICT", "RETURN",
                "REVOKE", "RIGHT", "RLIKE", "SCHEMA", "SCHEMAS", "SECOND_MICROSECOND", "SELECT", "SENSITIVE", "SEPARATOR", "SET", "SHOW",
                "SMALLINT", "SONAME", "SPATIAL", "SPECIFIC", "SQL", "SQLEXCEPTION", "SQLSTATE", "SQLWARNING", "SQL_BIG_RESULT", "SQL_CALC_FOUND_ROWS",
                "SQL_SMALL_RESULT", "SSL", "STARTING", "STRAIGHT_JOIN", "TABLE", "TERMINATED", "THEN", "TINYBLOB", "TINYINT", "TINYTEXT", "TO",
                "TRAILING", "TRIGGER", "TRUE", "UNDO", "UNION", "UNIQUE", "UNLOCK", "UNSIGNED",
                "UPDATE", "USAGE", "USE", "USING", "UTC_DATE", "UTC_TIME", "UTC_TIMESTAMP", "VALUES", "VARBINARY", "VARCHAR", "VARCHARACTER",
                "VARYING", "WHEN", "WHERE", "WHILE", "WITH", "WRITE", "XOR", "YEAR_MONTH", "ZEROFILL"
            ];

            if (parole_speciali.includes(nomeTabella.toUpperCase())) {
                event.preventDefault();
                alert("Il nome della tabella non può essere una parola speciale di MYSQL.");
                return;
            }

            for (var i = 0; i < attributi.length; i++) {
                if (parole_speciali.includes(attributi[i].value.toUpperCase())) {
                    event.preventDefault();
                    alert("Il nome degli attributi non può essere una parola speciale di MYSQL.");
                    return;
                }
            }
        });
    </script>

</body>

</html>