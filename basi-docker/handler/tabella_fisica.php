<?php
session_start();
require_once '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
function crea_tabella_fisica($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella, $foreign_keys, $primary_keys)
{
    inserisciInTabellaDelleTabelle($nome_tabella);
    inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella);
    inserisciForeignKey($nome_tabella, $foreign_keys);
    inserisciPrimaryKey($nome_tabella, $primary_keys, $nome_attributo);
}

function inserisciInTabellaDelleTabelle($nome_tabella)
{
    try {
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare("CALL InserisciTabellaDiEsercizio(:nome_tabella, :creatore)");
        $stmt->bindParam(':nome_tabella', $nome_tabella);
        $stmt->bindParam(':creatore', $_SESSION['email']);
        $stmt->execute();
        $stmt->closeCursor();
        $db = null;
    } catch (\Throwable $th) {
        echo "<script>alert('TABLE^2 PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }
}
function inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella)
{
    try {
        $db = connectToDatabaseMYSQL();
        for ($i = 0; $i < $numero_attributi; $i++) {
            $stmt = $db->prepare("CALL InserisciAttributo(:nome_tabella, :nome_attributo, :tipo_attributo)");
            $stmt->bindParam(':nome_tabella', $nome_tabella);
            $stmt->bindParam(':nome_attributo', $nome_attributo[$i]);
            $stmt->bindParam(':tipo_attributo', $tipo_attributo[$i]);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $db = null;
    } catch (\Throwable $th) {
        echo "<script>alert('ATTRIBUTES PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }
}

function inserisciForeignKey($nome_tabella, $foreign_keys)
{
    try {
        $db = connectToDatabaseMYSQL();
        foreach ($foreign_keys as $key) {
            $stmt = $db->prepare("CALL InserisciChiaveEsterna(:nome_tabella, :nome_attributo, :tabella_riferimento, :attributo_riferimento)");
            $stmt->bindParam(':nome_tabella', $nome_tabella);
            $stmt->bindParam(':nome_attributo', $key['attributo']);
            $stmt->bindParam(':tabella_riferimento', $key['tabella_riferimento']);
            $stmt->bindParam(':attributo_riferimento', $key['attributo_riferimento']);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $db = null;
    } catch (\Throwable $th) {
        echo "<script>alert('FOREIGN KEY INSERT PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }
}
function inserisciPrimaryKey($nome_tabella, $primary_keys, $nome_attributo)
{
    try {
        $db = connectToDatabaseMYSQL();
        foreach ($primary_keys as $key) {
            $sql = "CALL AggiungiChiavePrimaria(:nome_tabella, :nome_attributo);";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nome_tabella', $nome_tabella);
            $stmt->bindParam(':nome_attributo', $nome_attributo[$key]);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $db = null;
    } catch (\Throwable $th) {
        echo "<script>alert('PRIMARY KEY INSERT PROBLEM' <br>" . $th->getMessage() . ")</script>";
    }
}
