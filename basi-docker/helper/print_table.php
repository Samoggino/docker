<?php

function generateTable($tabella)
{
    try {
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare("SELECT * FROM " . $tabella);
        $stmt->execute();
        $valori_tabella_fisica = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $e) {
        $errorCode = $e->errorInfo[1];
        // se la tabella non esiste, non continuare l'esecuzione ma return
        if ($errorCode == 1146) {
            echo "<script> alert('Tabella non esistente'); window.location.href = '" . strtolower($_SESSION['ruolo']) . ".php'; </script>";
            return;
        }
    }
    try {
        $stmt = $db->prepare("CALL GetAttributiTabella(:nome_tabella)");
        $stmt->bindParam(':nome_tabella', $tabella, PDO::PARAM_STR);
        $stmt->execute();
        $attributi = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (\Throwable $th) {
        echo "<script>alert('Errore nel prendere gli attributi della tabella <br>" . $th->getMessage() . ")</script>";
    }
?>


    <h3><?php echo "Tabella: " . $tabella ?></h3>
    <table>
        <tr>
            <?php foreach ($attributi as $attributo) { ?>
                <th style="color:<?php if ($attributo['is_key'] == "TRUE") {
                                        echo "red";
                                    } else {
                                        echo "black";
                                    }; ?>">
                    <?php echo $attributo['nome_attributo']; ?>
                </th>
            <?php } ?>
        </tr>
        <tbody>
            <?php foreach ($valori_tabella_fisica as $valore) { ?>
                <tr>
                    <?php foreach ($attributi as $attributo) { ?>
                        <td><?php echo $valore[$attributo['nome_attributo']]; ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    <?php } ?>