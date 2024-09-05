<?php

function insertOnMONGODB($collection_name, $document, $log_testuale)
{
    // Connessione al server MongoDB
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");

    $database = $mongoClient->selectDatabase('logs');

    $collectionExists = false;
    $collections = $database->listCollections();
    foreach ($collections as $collectionInfo) {
        if ($collectionInfo->getName() === $collection_name) {
            // echo "<script>alert('La collezione " . $collection_name . " esiste già')</script>";
            $collectionExists = true;
            break;
        }
    }

    // Se la collezione non esiste, creala
    if (!$collectionExists) {
        $database->createCollection($collection_name);
        echo "<script>alert('La collezione " . $collection_name . " è stata creata')</script>";
    }

    // Seleziona la collezione
    $collection = $database->selectCollection($collection_name);

    // Inserisce il documento nella collezione
    $collection->insertOne([
        'descrizione' => $log_testuale,
        'oggetto' => $document,
        'data_creazione' => new MongoDB\BSON\UTCDateTime()
    ]);
    echo "<script>alert('Il documento è stato inserito nella collezione " . $collection_name . "')</script>";
}
