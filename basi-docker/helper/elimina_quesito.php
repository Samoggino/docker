<?php
require_once 'connessione_mysql.php';
require_once 'connessione_mongodb.php';
require_once '../composer/vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_quesito'])) {
    $id_quesito = $_POST['id_quesito'];

    $db = connectToDatabaseMYSQL();
    $sql = "CALL EliminaQuesito(:id_quesito)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_quesito', $id_quesito);
    $stmt->execute();
    $stmt->closeCursor();

    // Verifica se l'eliminazione è avvenuta con successo
    $eliminato = $stmt->rowCount() > 0;

    if ($eliminato) {
        insertOnMONGODB(
            "eliminazione_quesito",
            "Il quesito " . $id_quesito . " è stato eliminato.",
            'Il professore ' . $_SESSION['cognome'] . ' ' . $_SESSION['nome'] . ' ha eliminato il quesito ' . $id_quesito . '.'
        );

        echo "<script>alert('Quesito eliminato con successo.'); window.location.href = '/pages/professore/professore.php';</script>";
    } else {
        // Se c'è stato un errore nell'eliminazione, mostra un messaggio di errore
        echo "<script>alert('Errore durante l'eliminazione del quesito.');</script>";
    }
} else {
    echo "<script>alert('Errore durante l'eliminazione del quesito.');</script>";
}
