<?php
require_once 'connessione_mysql.php';
require_once 'connessione_mongodb.php';
require_once '../composer/vendor/autoload.php';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['titolo_test'])) {
    $titolo_test = $_POST['titolo_test'];

    $db = connectToDatabaseMYSQL();
    $sql = "CALL EliminaTest(:titolo)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo', $titolo_test);
    $stmt->execute();
    $stmt->closeCursor();

    // Verifica se l'eliminazione è avvenuta con successo
    $eliminato = $stmt->rowCount() > 0;

    if ($eliminato) {

        insertOnMONGODB(
            "eliminazione_test",
            "Il test " . $titolo_test . " è stato eliminato.",
            'Il professore ' . $_SESSION['cognome'] . ' ' . $_SESSION['nome'] . ' ha eliminato il test ' . $titolo_test . '.'
        );

        // Se l'eliminazione è avvenuta con successo, reindirizza l'utente alla pagina precedente o ad una specifica pagina
        echo "<script>alert('Test eliminato con successo!'); window.location.href = '/pages/professore/professore.php';</script>";
    } else {
        // Se c'è stato un errore nell'eliminazione, mostra un messaggio di errore
        echo "Errore nell'eliminazione del quesito.";
    }
} else {
    // Se la richiesta non è una POST o manca il parametro id_quesito, restituisci un errore
    echo "<script>alert('Errore nell\'eliminazione del test!'); window.location.href = '/pages/professore/professore.php';</script>";
}
