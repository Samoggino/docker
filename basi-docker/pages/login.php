<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/favicon/favicon.ico" type="image">
    <link rel="stylesheet" href="/styles/global.css">
    <link rel="stylesheet" href="/styles/login.css">

    <title>Login</title>
</head>

<body>

    <div class="wrapper">
        <div class="card-switch">
            <label class="switch">
                <input type="checkbox" class="toggle">
                <span class="slider"></span>
                <span class="card-side"></span>
                <div class="flip-card__inner">
                    <div class="flip-card__front">
                        <div class="title">Log in</div>
                        <form method="POST" action="" class="flip-card__form">
                            <input for="email" class="flip-card__input" name="email" placeholder="Email" type="text" required>
                            <input for="password" class="flip-card__input" name="password" placeholder="Password" type="password" required>
                            <input type="hidden" name="action" value="login"> <!-- Campo nascosto per indicare login -->
                            <button class="flip-card__btn" name="login">Login!</button> <!-- Pulsante per eseguire il login -->
                        </form>
                    </div>
                    <div class="flip-card__back">
                        <div class="title">Sign up</div>
                        <form method="POST" action="" class="flip-card__form" id="signup-form">
                            <input for="nome" name="nome" class="flip-card__input" placeholder="Nome" type="text" value="nome" required>
                            <input for="cognome" name="cognome" class="flip-card__input" placeholder="Cognome" type="text" value="cognome" required>
                            <input for="email" name="email" class="flip-card__input" placeholder="Email" type="email" autocomplete="off" required>
                            <input for="password" name="password" class="flip-card__input" placeholder="Password" type="password" autocomplete="off" required>

                            <input for="telefono" name="telefono" class="flip-card__input" placeholder="Telefono" type="number" min="1111111111" max="9999999999">
                            <input type="hidden" name="action" value="registrazione"> <!-- Campo nascosto per indicare registrazione -->
                            <div>
                                <div class="checkbox-container">
                                    <label for="studente-checkbox" class="flip-card__label">Studente</label>
                                    <input type="checkbox" id="studente-checkbox" class="flip-card__checkbox" name="studente">
                                </div>
                                <div id="studente" style="display: none;">
                                    <input for="anno_immatricolazione" name="anno_immatricolazione" class="flip-card__input" placeholder="Anno di immatricolazione" type="number" min="0" max="<?php echo date('Y') ?>">
                                    <input for="matricola" name="matricola" class="flip-card__input" placeholder="Codice" type="text">
                                </div>
                            </div>
                            <div>
                                <div class="checkbox-container">
                                    <label for="professore-checkbox" class="flip-card__label">Professore</label>
                                    <input type="checkbox" id="professore-checkbox" class="flip-card__checkbox" name="professore">
                                </div>
                                <div id="professore" style="display: none;">
                                    <input for="dipartimento" name="dipartimento" class="flip-card__input" placeholder="Dipartimento" type="text">
                                    <input for="corso" name="corso" class="flip-card__input" placeholder="Corso" type="text">
                                </div>
                            </div>
                            <input type="hidden" for="tipo_utente" name="tipo_utente" id="tipo_utente" value="">
                            <button class="flip-card__btn" name="registrazione">Registrami!</button> <!-- Pulsante per eseguire la registrazione -->
                        </form>
                    </div>
                </div>
            </label>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var studenteCheckbox = document.getElementById("studente-checkbox");
            var professoreCheckbox = document.getElementById("professore-checkbox");
            var studente_div = document.getElementById("studente");
            var professore_div = document.getElementById("professore");
            var tipoUtenteInput = document.getElementById("tipo_utente"); // Definizione della variabile tipoUtenteInput

            studenteCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    tipoUtenteInput.value = "studente";
                    studente_div.style.display = "block";
                    professoreCheckbox.checked = false; // Disabilita la checkbox professore quando selezioni studente
                    professore_div.style.display = "none"; // Nasconde il campo dipartimento quando selezioni studente
                } else {
                    studente_div.style.display = "none";
                }
            });

            professoreCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    tipoUtenteInput.value = "professore";
                    professore_div.style.display = "block";
                    studenteCheckbox.checked = false; // Disabilita la checkbox studente quando selezioni professore
                    studente_div.style.display = "none"; // Nasconde il campo anno immatricolazione quando selezioni professore
                } else {
                    professore_div.style.display = "none";
                }
            });

            var form = document.getElementById("signup-form");

            form.addEventListener("submit", function(event) {
                if (!studenteCheckbox.checked && !professoreCheckbox.checked) {
                    event.preventDefault(); // Impedisce l'invio del modulo se nessuna checkbox è selezionata
                    alert("Seleziona almeno una delle opzioni: Studente o Professore.");
                }
            });
        });
    </script>


    <?php
    require_once '../handler/login_handler.php';
    require_once '../handler/registrazione_handler.php';

    echo "<script>console.log(" . json_encode($_POST) . ")</script>";
    if (isset($_POST)) {
        if (isset($_POST['login'])) {
            loginHandler();
        } elseif (isset($_POST['registrazione'])) {
            registrazione();
        }
    }
    ?>



</body>

</html>