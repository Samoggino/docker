<?php
session_start();
require_once '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

function crea_tabella_logica($nome_tabella, $numero_attributi, $nome_attributo, $tipo_attributo, $primary_keys, $foreign_keys)

{
    // query che poi verrà eseguita per creare la tabella fisica
    $query_corrente = "CREATE TABLE IF NOT EXISTS $nome_tabella (";

    $query_corrente = attributi(
        $query_corrente,
        $numero_attributi,
        $nome_attributo,
        $tipo_attributo,
    );

    $query_corrente = primary_key($query_corrente, $primary_keys, $nome_attributo);

    if (isset($foreign_keys) && count($foreign_keys) > 0) {
        $query_corrente = foreign_key($foreign_keys, $query_corrente);
    }

    $query_corrente .= ");";

    return $query_corrente;
}
function primary_key($query_corrente, $primary_keys, $nome_attributo)
{
    try {
        // Aggiungi le chiavi primarie
        if (count($primary_keys) > 0) {
            $query_corrente .= ", PRIMARY KEY (";
            foreach ($primary_keys as $key) {
                $query_corrente .= $nome_attributo[$key] . ", ";
            }
            $query_corrente = rtrim($query_corrente, ", "); // Rimuove l'ultima virgola
            $query_corrente .= ")";
        }
    } catch (\Throwable $th) {
        echo "<script>alert('PRIMARY KEY PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }

    return $query_corrente;
}


function foreign_key($foreign_keys_raw, $query_corrente)
{

    try {
        // prendi gli indici delle chiavi esterne

        echo "<script>console.log(" . json_encode($foreign_keys_raw) . ");</script>";

        $json = convertToJSONFormat($foreign_keys_raw); // Converto l'array in formato JSON per facilitare la manipolazione
        $decodedJson = json_decode($json, true);


        // Esempio di utilizzo
        if ($decodedJson['foreign_keys'] > 0)
            foreach ($decodedJson['foreign_keys'] as $tableName => $attributes) {

                $db = connectToDatabaseMYSQL();
                $sql = "CALL GetPrimaryKey(:tableName)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':tableName', $tableName);
                $stmt->execute();
                $attributi_ordinati = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = null;

                $attributes = verificaOrdine($attributi_ordinati, $attributes);

                $query_corrente .= ", FOREIGN KEY ("
                    . implode(", ", array_column($attributes, 'attributo')) . ") REFERENCES $tableName("
                    . implode(", ", array_column($attributes, 'attributo_riferimento')) . ") ON DELETE CASCADE ";
            }
    } catch (\Throwable $th) {
        echo "<script>alert('FOREIGN KEY PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }
    return $query_corrente;
}

function convertToJSONFormat($input)
{
    try {
        $output = array();

        echo "<script>console.log(" . json_encode($input) . ");</script>";

        foreach ($input as $key => $value) {
            $tableName = $value['tabella_riferimento'];

            if (!isset($output['foreign_keys'][$tableName])) {
                $output['foreign_keys'][$tableName] = array();
            }

            $output['foreign_keys'][$tableName][] = array(
                'attributo' => $value['attributo'],
                'attributo_riferimento' => $value['attributo_riferimento']
            );
        }
    } catch (\Throwable $th) {
        echo "<script>alert('CONVERSION PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }

    return json_encode($output, JSON_PRETTY_PRINT);
}

function verificaOrdine($attributi_ordinati, $attributes)
{

    // verifica che l'attributo sia chiave della tabella di riferimento, se non lo è rimuovilo da attributes 
    foreach ($attributes as $key => $value) {
        if (!in_array($value['attributo_riferimento'], array_column($attributi_ordinati, 'NOME_ATTRIBUTO'))) {
            unset($attributes[$key]);
        }
    }

    // Creare una matrice associativa con il nome dell'attributo come chiave e l'indice come valore
    $indiceAttributiOrdinati = array();
    foreach ($attributi_ordinati as $attributo) {
        $indiceAttributiOrdinati[$attributo['NOME_ATTRIBUTO']] = $attributo['INDICE'];
    }

    // Ordinare gli attributi locali in base all'indice ottenuto dalla matrice associativa
    usort($attributes, function ($a, $b) use ($indiceAttributiOrdinati) {
        $indiceA = isset($indiceAttributiOrdinati[$a['attributo_riferimento']]) ? $indiceAttributiOrdinati[$a['attributo_riferimento']] : PHP_INT_MAX;
        $indiceB = isset($indiceAttributiOrdinati[$b['attributo_riferimento']]) ? $indiceAttributiOrdinati[$b['attributo_riferimento']] : PHP_INT_MAX;
        return $indiceA - $indiceB;
    });

    // controllo l'ordine degli attributi, se manca un elemento della chiave esterna allora lancia un'eccezione
    for ($i = 0; $i < count($attributes); $i++) {
        if ($attributes[$i]['attributo_riferimento'] != $attributi_ordinati[$i]['NOME_ATTRIBUTO']) {
            throw new Exception("Non sono presenti tutti gli elementi necessari per fare la chiave esterna.");
        }
    }

    return $attributes;
}


function inserisciTriggerNumeroRighe($query_corrente, $nome_tabella)
{
    $query_corrente .= " CREATE TRIGGER IF NOT EXISTS after_insert_$nome_tabella AFTER
    INSERT
        ON $nome_tabella FOR EACH ROW BEGIN
        -- Incrementa il numero di righe nella tabella
    UPDATE TABELLA_DELLE_TABELLE
    SET
        num_righe = num_righe + 1
    WHERE
        nome_tabella = '$nome_tabella';
    
    END;";
    return $query_corrente;
}


function attributi(
    $query_corrente,
    $numero_attributi,
    $nome_attributo,
    $tipo_attributo,
) {

    try {
        // Aggiunge gli attributi dinamici alla query di creazione
        for ($i = 0; $i < $numero_attributi; $i++) {
            $query_corrente .= $nome_attributo[$i] . " ";
            // Se il tipo è VARCHAR, aggiungi la grandezza specificata
            if ($tipo_attributo[$i] == 'VARCHAR') {
                $query_corrente .= $tipo_attributo[$i] . "(20)";
            } else {
                $query_corrente .= $tipo_attributo[$i];
            }
            // Aggiunge virgola se non è l'ultimo attributo
            if ($i < $numero_attributi - 1) {
                $query_corrente .= ", ";
            }
        }
    } catch (\Throwable $th) {
        echo "<script>alert('ATTRIBUTES PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }
    return $query_corrente;
}
