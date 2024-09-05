DROP DATABASE IF EXISTS POKEDB;

CREATE DATABASE IF NOT EXISTS POKEDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE POKEDB;

-- Tabella Utente
CREATE TABLE IF NOT EXISTS
    UTENTE (
        email VARCHAR(100) PRIMARY KEY                                        ,
        nome VARCHAR(50) NOT NULL                                             ,
        cognome VARCHAR(50) NOT NULL                                          ,
        PASSWORD VARCHAR(100) NOT NULL                                        ,
        tipo_utente ENUM('STUDENTE', 'PROFESSORE') DEFAULT 'STUDENTE' NOT NULL,
        telefono VARCHAR(20)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

-- Tabella Studente
CREATE TABLE IF NOT EXISTS
    STUDENTE (
        email_studente VARCHAR(100) PRIMARY KEY                                ,
        matricola VARCHAR(16) NOT NULL                                         ,
        anno_immatricolazione INT NOT NULL                                     ,
        FOREIGN KEY (email_studente) REFERENCES UTENTE (email) ON DELETE CASCADE
    );

-- Tabella Professore
CREATE TABLE IF NOT EXISTS
    PROFESSORE (
        email_professore VARCHAR(100) PRIMARY KEY                                ,
        dipartimento VARCHAR(100) NOT NULL                                       ,
        corso VARCHAR(100) NOT NULL                                              ,
        FOREIGN KEY (email_professore) REFERENCES UTENTE (email) ON DELETE CASCADE
    );

-- crea tabella delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    TABELLA_DELLE_TABELLE (
        nome_tabella VARCHAR(20) PRIMARY KEY                                            ,
        data_creazione DATETIME DEFAULT NOW() NOT NULL                                  ,
        num_righe INT DEFAULT 0 NOT NULL                                                ,
        creatore VARCHAR(100) NOT NULL                                                  ,
        FOREIGN KEY (creatore) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE
    );

-- Inserimento dati nella tabella UTENTE
INSERT INTO
    UTENTE (
        email     ,
        nome      ,
        cognome   ,
        PASSWORD  ,
        telefono  ,
        tipo_utente
    )
VALUES
    (
        'studente1@unibo.it',
        'Mario'             ,
        'Rossi'             ,
        '1234'              ,
        '12324567'          ,
        'STUDENTE'
    ),
    (
        'studente2@unibo.it',
        'Luca'              ,
        'Bianchi'           ,
        '1234'              ,
        '12324567'          ,
        'STUDENTE'
    ),
    (
        'vincenzo.scollo@unibo.it',
        'Anna'                    ,
        'Verdi'                   ,
        '1234'                    ,
        '12324567'                ,
        'PROFESSORE'
    ),
    (
        'mariagrazia.fabbri@unibo.it',
        'Carlo'                      ,
        'Neri'                       ,
        '1234'                       ,
        '12324567'                   ,
        'PROFESSORE'
    ),
    (
        'simosamoggia@gmail.com',
        'Simone'                ,
        'Samoggia'              ,
        '1234'                  ,
        '12324567'              ,
        'STUDENTE'
    ),
    (
        'professore@unibo.it',
        'Professor'          ,
        'Oak'                ,
        '1234'               ,
        '12324567'           ,
        'PROFESSORE'
    ),
    (
        'studente@unibo.it',
        'Ash'              ,
        'Ketchum'          ,
        '1234'             ,
        '12324567'         ,
        'STUDENTE'
    );

-- Inserimento dati nella tabella Studente
INSERT INTO
    STUDENTE (email_studente, anno_immatricolazione, matricola)
VALUES
    ('studente1@unibo.it', 2019, '00123456')    ,
    ('studente2@unibo.it', 2020, '00987654')    ,
    ('simosamoggia@gmail.com', 2020, '00970758'),
    ('studente@unibo.it', 2020, '00567890');

-- Inserimento dati nella tabella Professore
INSERT INTO
    PROFESSORE (email_professore, dipartimento, corso)
VALUES
    (
        'vincenzo.scollo@unibo.it',
        'Informatica'             ,
        'scienze applicate'
    ),
    (
        'mariagrazia.fabbri@unibo.it',
        'Matematica'                 ,
        'scienze applicate'
    ),
    (
        'professore@unibo.it',
        'Matematica'         ,
        'Scienze applicate'
    );

-- Procedura di registrazione studente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS authenticateUser (
    IN p_email VARCHAR(100)                         ,
    IN p_password VARCHAR(100)                      ,
    OUT p_authenticated TINYINT(1)                  ,
    OUT p_tipo_utente ENUM('STUDENTE', 'PROFESSORE'),
    OUT p_nome_utente VARCHAR(50)                   ,
    OUT p_cognome_utente VARCHAR(50)
) BEGIN DECLARE user_count INT;

DECLARE tipo ENUM('STUDENTE', 'PROFESSORE');

DECLARE nome_utente VARCHAR(50);

DECLARE cognome_utente VARCHAR(50);

-- Controlla se le credenziali sono valide e recupera il tipo di utente, nome e cognome
SELECT
    COUNT(*)                 ,
    tipo_utente              ,
    nome                     ,
    cognome INTO   user_count,
    tipo                     ,
    nome_utente              ,
    cognome_utente
FROM
    UTENTE
WHERE
    email = p_email
    AND PASSWORD = p_password
GROUP BY
    tipo_utente,
    nome       ,
    cognome;

-- Se user_count è maggiore di 0, significa che le credenziali sono valide
IF user_count > 0 THEN
SET
    p_authenticated = 1;

SET
    p_tipo_utente = tipo;

SET
    p_nome_utente = nome_utente;

SET
    p_cognome_utente = cognome_utente;

ELSE
SET
    p_authenticated = 0;

SET
    p_tipo_utente = NULL;

SET
    p_nome_utente = NULL;

SET
    p_cognome_utente = NULL;

END IF;

END $$ DELIMITER;

-- Procedura di registrazione studente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoStudente (
    IN p_email VARCHAR(100)       ,
    IN p_nome VARCHAR(50)         ,
    IN p_cognome VARCHAR(50)      ,
    IN p_password VARCHAR(100)    ,
    IN p_matricola VARCHAR(16)    ,
    IN p_anno_immatricolazione INT,
    IN p_telefono VARCHAR(20)
) BEGIN
START TRANSACTION;

-- Inserisce l'utente nella tabella Utente
INSERT INTO
    UTENTE (email, nome, cognome, PASSWORD, telefono)
VALUES
    (
        p_email   ,
        p_nome    ,
        p_cognome ,
        p_password,
        p_telefono
    );

-- Inserisce lo studente nella tabella Studente
INSERT INTO
    STUDENTE (email_studente, matricola, anno_immatricolazione)
VALUES
    (p_email, p_matricola, p_anno_immatricolazione);

COMMIT;

END $$ DELIMITER;

-- Procedura di registrazione professore
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoProfessore (
    IN p_email VARCHAR(100)       ,
    IN p_nome VARCHAR(50)         ,
    IN p_cognome VARCHAR(50)      ,
    IN p_password VARCHAR(100)    ,
    IN p_dipartimento VARCHAR(100),
    IN p_corso VARCHAR(100)       ,
    IN p_telefono VARCHAR(20)
) BEGIN
START TRANSACTION;

-- Inserisce l'utente nella tabella Utente
INSERT INTO
    UTENTE (email, nome, cognome, PASSWORD, telefono)
VALUES
    (
        p_email   ,
        p_nome    ,
        p_cognome ,
        p_password,
        p_telefono
    );

-- Inserisce il professore nella tabella Professore
INSERT INTO
    PROFESSORE (email_professore, dipartimento, corso)
VALUES
    (p_email, p_dipartimento, p_corso);

COMMIT;

END $$ DELIMITER;

-- crea tabella dei TEST
CREATE TABLE IF NOT EXISTS
    TEST (
        titolo VARCHAR(100) PRIMARY KEY                                                      ,
        dataCreazione DATETIME DEFAULT NOW() NOT NULL                                        ,
        VisualizzaRisposte TINYINT(1) DEFAULT 0 NOT NULL                                     ,
        email_professore VARCHAR(100) NOT NULL                                               ,
        -- TINYINT viene utilizzato come BOOLEAN, dato che MySQL non supporta il tipo BOOLEAN 
        FOREIGN KEY (email_professore) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE
    );

-- crea tabella delle foto dei test
CREATE TABLE IF NOT EXISTS
    FOTO_TEST (
        foto LONGBLOB NOT NULL                                                ,
        test_associato VARCHAR(100) NOT NULL                                  ,
        PRIMARY KEY (test_associato)                                          ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE
    );

-- crea procedura per inserire un nuovo TEST
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoTest (
    IN p_titolo VARCHAR(100)         ,
    IN p_email_professore VARCHAR(100)
) BEGIN
START TRANSACTION;

-- Inserisce il test nella tabella Test
INSERT INTO
    TEST (titolo, email_professore)
VALUES
    (p_titolo, p_email_professore);

COMMIT;

END $$ DELIMITER;

-- crea procedura per inserire una nuova foto di un test
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovaFotoTest (
    IN p_foto LONGBLOB             ,
    IN p_test_associato VARCHAR(100)
) BEGIN
START TRANSACTION;

-- Inserisce la foto del test nella tabella Foto_test
INSERT INTO
    FOTO_TEST (foto, test_associato)
VALUES
    (p_foto, p_test_associato);

COMMIT;

END $$ DELIMITER;

-- procedura per restituire l'immagine di un TEST
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS RecuperaFotoTest (IN p_test_associato VARCHAR(100)) BEGIN
SELECT
    foto
FROM
    FOTO_TEST
WHERE
    test_associato = p_test_associato;

END $$ DELIMITER;

-- crea tabella dei QUESITI
CREATE TABLE IF NOT EXISTS
    QUESITO (
        ID INT AUTO_INCREMENT NOT NULL                                            ,
        numero_quesito INT NOT NULL                                               ,
        test_associato VARCHAR(100) NOT NULL                                      ,
        descrizione TEXT NOT NULL                                                 ,
        livello_difficolta ENUM('BASSO', 'MEDIO', 'ALTO') DEFAULT 'BASSO' NOT NULL,
        numero_risposte INT NOT NULL DEFAULT 0                                    ,
        tipo_quesito ENUM('APERTO', 'CHIUSO') DEFAULT 'APERTO' NOT NULL           ,
        PRIMARY KEY (ID)                                                          ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE   ,
        CONSTRAINT quesito_test UNIQUE (test_associato, numero_quesito)
    );

-- OPZIONE QUESITO CHIUSO
CREATE TABLE IF NOT EXISTS
    QUESITO_CHIUSO_OPZIONE (
        id_quesito INT NOT NULL                                            ,
        numero_opzione INT NOT NULL, -- opzione a, opzione b ... opzione z  
        testo TEXT NOT NULL                                              ,
        is_corretta ENUM('TRUE', 'FALSE') DEFAULT 'FALSE' NOT NULL       ,
        PRIMARY KEY (numero_opzione, id_quesito)                         ,
        FOREIGN KEY (id_quesito) REFERENCES QUESITO (ID) ON DELETE CASCADE
    );

-- crea tabella quesito aperto
CREATE TABLE IF NOT EXISTS
    QUESITO_APERTO_SOLUZIONE (
        id_quesito INT NOT NULL                                                             ,
        id_soluzione INT AUTO_INCREMENT NOT NULL, -- soluzione 1, soluzione 2 ... soluzione n
        soluzione_professore TEXT NOT NULL                               ,
        PRIMARY KEY (id_soluzione)                                       ,
        FOREIGN KEY (id_quesito) REFERENCES QUESITO (ID) ON DELETE CASCADE
    );

CREATE TABLE IF NOT EXISTS
    RISPOSTA (
        ID INT AUTO_INCREMENT NOT NULL                                                     ,
        id_quesito INT NOT NULL                                                            ,
        email_studente VARCHAR(100) NOT NULL                                               ,
        TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL                             ,
        tipo_risposta ENUM('APERTA', 'CHIUSA') DEFAULT 'APERTA' NOT NULL                   ,
        esito ENUM('GIUSTA', 'SBAGLIATA') DEFAULT 'SBAGLIATA' NOT NULL                     ,
        PRIMARY KEY (ID)                                                                   ,
        FOREIGN KEY (id_quesito) REFERENCES QUESITO (ID) ON DELETE CASCADE                 ,
        FOREIGN KEY (email_studente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE,
        CONSTRAINT risposta_quesito UNIQUE (id_quesito, TIMESTAMP, email_studente)
    );

-- crea tabelle delle risposte chiuse
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_CHIUSO (
        id_risposta INT NOT NULL                                                                        ,
        opzione_scelta INT NOT NULL                                                                     ,
        PRIMARY KEY (id_risposta)                                                                       ,
        FOREIGN KEY (id_risposta) REFERENCES RISPOSTA (ID) ON DELETE CASCADE                            ,
        FOREIGN KEY (opzione_scelta) REFERENCES QUESITO_CHIUSO_OPZIONE (numero_opzione) ON DELETE CASCADE
    );

-- crea tabella delle risposte aperte
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_APERTO (
        id_risposta INT NOT NULL                                           ,
        risposta TEXT NOT NULL                                             ,
        PRIMARY KEY (id_risposta)                                          ,
        FOREIGN KEY (id_risposta) REFERENCES RISPOSTA (ID) ON DELETE CASCADE
    );

-- crea la stored procedure per inserire una nuova opzione per un quesito chiuso
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovaOpzioneQuesitoChiuso (
    IN p_numero_opzione INT              ,
    IN p_id_quesito INT                  ,
    IN p_testo TEXT                      ,
    IN p_is_corretta ENUM('TRUE', 'FALSE')
) BEGIN
-- Inserisce l'opzione nella tabella QUESITO_CHIUSO_OPZIONE
INSERT INTO
    QUESITO_CHIUSO_OPZIONE (id_quesito, numero_opzione, testo, is_corretta)
VALUES
    (
        p_id_quesito    ,
        p_numero_opzione,
        p_testo         ,
        p_is_corretta
    );

COMMIT;

END $$ DELIMITER;

-- aggiorna il numero_risposte di un quesito quando viene aggiunto una nuova opzione
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS update_numero_risposte_al_quesito AFTER
INSERT
    ON RISPOSTA FOR EACH ROW BEGIN
UPDATE QUESITO
SET
    numero_risposte = numero_risposte + 1
WHERE
    QUESITO.ID = NEW.id_quesito;

END $$ DELIMITER;

-- crea la stored procedure per inserire una nuova soluzione per un quesito aperto
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovaSoluzioneQuesitoAperto (
    IN p_id_quesito INT          ,
    IN p_soluzione_professore TEXT
) BEGIN
START TRANSACTION;

-- Inserisce la soluzione nella tabella Quesito_aperto
INSERT INTO
    QUESITO_APERTO_SOLUZIONE (id_quesito, soluzione_professore)
VALUES
    (p_id_quesito, p_soluzione_professore);

COMMIT;

END $$ DELIMITER;

-- procedura per inserire un nuovo QUESITO
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoQuesito (
    IN p_numero_quesito INT                               ,
    IN p_test_associato VARCHAR(100)                      ,
    IN p_descrizione TEXT                                 ,
    IN p_livello_difficolta ENUM('BASSO', 'MEDIO', 'ALTO'),
    IN p_tipo_quesito ENUM('APERTO', 'CHIUSO')            ,
    OUT id_nuovo_quesito INT
) BEGIN
START TRANSACTION;

-- Inserisce il quesito nella tabella Quesito
INSERT INTO
    QUESITO (
        numero_quesito    ,
        test_associato    ,
        descrizione       ,
        livello_difficolta,
        tipo_quesito
    )
VALUES
    (
        p_numero_quesito    ,
        p_test_associato    ,
        p_descrizione       ,
        p_livello_difficolta,
        p_tipo_quesito
    );

-- Ottiene l'ID del quesito appena inserito
SELECT
    LAST_INSERT_ID() INTO id_nuovo_quesito;

COMMIT;

END $$ DELIMITER;

-- -- crea tabella degli ATTRIBUTI delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    TAB_ATT (
        ID INT AUTO_INCREMENT                                                                      ,
        nome_tabella VARCHAR(20) NOT NULL                                                          ,
        nome_attributo VARCHAR(100) NOT NULL                                                       ,
        tipo_attributo VARCHAR(15) NOT NULL                                                        ,
        key_part ENUM('TRUE', 'FALSE') DEFAULT 'FALSE' NOT NULL                                    ,
        PRIMARY KEY (ID)                                                                           ,
        CONSTRAINT UNIQUE_TAB_ATT UNIQUE (nome_tabella, nome_attributo)                            ,
        FOREIGN KEY (nome_tabella) REFERENCES TABELLA_DELLE_TABELLE (nome_tabella) ON DELETE CASCADE
    );

-- -- crea tabella delle CHIAVI ESTERNE delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    VINCOLI (
        nome_tabella VARCHAR(20) NOT NULL        ,
        nome_attributo VARCHAR(100) NOT NULL     ,
        tabella_vincolata VARCHAR(20) NOT NULL   ,
        attributo_vincolato VARCHAR(100) NOT NULL,
        PRIMARY KEY (
            nome_tabella      ,
            nome_attributo    ,
            tabella_vincolata ,
            attributo_vincolato
        )                                                                                                                      ,
        FOREIGN KEY (nome_tabella, nome_attributo) REFERENCES TAB_ATT (nome_tabella, nome_attributo) ON DELETE CASCADE         ,
        FOREIGN KEY (tabella_vincolata, attributo_vincolato) REFERENCES TAB_ATT (nome_tabella, nome_attributo) ON DELETE CASCADE
    );

create table
    `TIPI_POKEMON` (
        `TIPO` varchar(255) not null,
        primary key (`TIPO`)
    );

create table
    `POKEMON` (
        `PS` int not null                                                       ,
        `nome` varchar(255) not null                                            ,
        `tipo` varchar(255) not null                                            ,
        `is_legendary` INT default 0 not null                                   ,
        primary key (`nome`)                                                    ,
        foreign key (`tipo`) references `TIPI_POKEMON` (`TIPO`) ON DELETE CASCADE
    );

INSERT INTO
    `TIPI_POKEMON` (`TIPO`)
VALUES
    ('Dragon')  ,
    ('Electric'),
    ('Fighting'),
    ('Fire')    ,
    ('Flying')  ,
    ('Grass')   ,
    ('Ground')  ,
    ('Ice')     ,
    ('Normal')  ,
    ('Poison')  ,
    ('Water');

INSERT INTO
    `POKEMON` (`PS`, `nome`, `tipo`, `is_legendary`)
VALUES
    (15, 'Chimchar', 'Fire', 0)      ,
    (30, 'Dragonite', 'Dragon', 0)   ,
    (30, 'Electabuzz', 'Electric', 0),
    (20, 'Machop', 'Fighting', 0)    ,
    (10, 'Pikachu', 'Electric', 0)   ,
    (8, 'Piplup', 'Water', 0)        ,
    (14, 'Staraptor', 'Flying', 0)   ,
    (8, 'Turtwig', 'Grass', 0);

-- CREA tabella SVOLGIMENTO TEST
CREATE TABLE IF NOT EXISTS
    SVOLGIMENTO_TEST (
        titolo_test VARCHAR(100) NOT NULL                                                 ,
        email_studente VARCHAR(100) NOT NULL                                              ,
        data_inizio TIMESTAMP                                                             ,
        data_fine TIMESTAMP                                                               ,
        stato ENUM('APERTO', 'IN_COMPLETAMENTO', 'CONCLUSO') DEFAULT 'APERTO' NOT NULL    ,
        PRIMARY KEY (titolo_test, email_studente)                                         ,
        FOREIGN KEY (titolo_test) REFERENCES TEST (titolo) ON DELETE CASCADE              ,
        FOREIGN KEY (email_studente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE
    );

-- tabella dei messaggi
CREATE TABLE IF NOT EXISTS
    MESSAGGIO (
        id INT AUTO_INCREMENT                                                 ,
        titolo VARCHAR(100) NOT NULL                                          ,
        testo TEXT NOT NULL                                                   ,
        data_inserimento DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL          ,
        test_associato VARCHAR(100) NOT NULL                                  ,
        PRIMARY KEY (id)                                                      ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE
    );

-- messaggio che invia uno studente al professore
CREATE TABLE IF NOT EXISTS
    MESSAGGIO_PRIVATO (
        id_messaggio INT NOT NULL                                                           ,
        mittente VARCHAR(100) NOT NULL                                                      ,
        destinatario VARCHAR(100) NOT NULL                                                  ,
        PRIMARY KEY (id_messaggio)                                                          ,
        FOREIGN KEY (id_messaggio) REFERENCES MESSAGGIO (id) ON DELETE CASCADE              ,
        FOREIGN KEY (mittente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE       ,
        FOREIGN KEY (destinatario) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE
    );

-- messaggio che invia un professore a tutti gli studenti
CREATE TABLE IF NOT EXISTS
    BROADCAST (
        id_messaggio INT NOT NULL                                                       ,
        mittente VARCHAR(100) NOT NULL                                                  ,
        PRIMARY KEY (id_messaggio)                                                      ,
        FOREIGN KEY (id_messaggio) REFERENCES MESSAGGIO (id) ON DELETE CASCADE          ,
        FOREIGN KEY (mittente) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE
    );

-- crea tabella dei quesiti-tabella
CREATE TABLE IF NOT EXISTS
    QUESITI_TABELLA (
        ID INT AUTO_INCREMENT                                                                       ,
        id_quesito INT NOT NULL                                                                     ,
        nome_tabella VARCHAR(20) NOT NULL                                                           ,
        PRIMARY KEY (ID)                                                                            ,
        FOREIGN KEY (id_quesito) REFERENCES QUESITO (ID) ON DELETE CASCADE                          ,
        FOREIGN KEY (nome_tabella) REFERENCES TABELLA_DELLE_TABELLE (nome_tabella) ON DELETE CASCADE,
        CONSTRAINT UNIQUE_quesito_tabella UNIQUE (id_quesito, nome_tabella)
    );

-- procedura per prendere il numero del nuovo quesito
DELIMITER $$
CREATE PROCEDURE GetNumeroNuovoQuesito (IN p_test_associato VARCHAR(100)) BEGIN
SELECT
    numero_quesito
FROM
    QUESITO
WHERE
    test_associato = p_test_associato
ORDER BY
    numero_quesito DESC
LIMIT
    1;

END $$ DELIMITER;

-- riparti da qui
-- crea procedure che verifica se lo studente ha già concluso quel test
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS VerificaTestConcluso (
    IN p_email_studente VARCHAR(100),
    IN p_titolo_test VARCHAR(100)   ,
    OUT is_closed INT
) BEGIN
SELECT
    COUNT(*) INTO is_closed
FROM
    SVOLGIMENTO_TEST
WHERE
    email_studente = p_email_studente
    AND titolo_test = p_titolo_test
    AND stato = 'CONCLUSO';

END $$ DELIMITER;

-- inserisci risposta a quesito chiuso 
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciRispostaQuesitoChiuso (
    IN p_id_quesito INT                  ,
    IN p_email_studente VARCHAR(100)     ,
    IN p_opzione_scelta INT              ,
    IN p_esito ENUM('GIUSTA', 'SBAGLIATA')
) BEGIN DECLARE id_risposta INT;

DECLARE test_closed INT;

DECLARE p_test_associato VARCHAR(100);

START TRANSACTION;

-- Ottiene il test associato al quesito
SELECT
    `QUESITO`.test_associato INTO p_test_associato
FROM
    QUESITO
WHERE
    ID = p_id_quesito;

-- Controlla se il test è già concluso
CALL VerificaTestConcluso (p_email_studente, p_test_associato, test_closed);

IF test_closed < 1 THEN
-- inserisce la risposta nella tabella Risposta
INSERT INTO
    RISPOSTA (id_quesito, email_studente, tipo_risposta, esito)
VALUES
    (p_id_quesito, p_email_studente, 'CHIUSA', p_esito);

-- Ottiene l'id della risposta appena inserita
SELECT
    LAST_INSERT_ID() INTO id_risposta;

-- Inserisce la risposta nella tabella Risposta_quesito_chiuso
INSERT INTO
    RISPOSTA_QUESITO_CHIUSO (id_risposta, opzione_scelta)
VALUES
    (id_risposta, p_opzione_scelta);

END IF;

-- Esegue il commit della transazione
COMMIT;

END $$ DELIMITER;

DELIMITER $$
-- inserisci risposta a quesito aperto
CREATE PROCEDURE IF NOT EXISTS InserisciRispostaQuesitoAperto (
    IN p_id_quesito INT                  ,
    IN p_email_studente VARCHAR(100)     ,
    IN p_risposta TEXT                   ,
    IN p_esito ENUM('GIUSTA', 'SBAGLIATA')
) BEGIN DECLARE id_risposta INT;

DECLARE test_closed INT;

DECLARE p_test_associato VARCHAR(100);

START TRANSACTION;

-- Ottiene il test associato al quesito
SELECT
    `QUESITO`.test_associato INTO p_test_associato
FROM
    QUESITO
WHERE
    ID = p_id_quesito;

CALL VerificaTestConcluso (p_email_studente, p_test_associato, test_closed);

IF test_closed < 1 THEN
-- inserisce la risposta nella tabella Risposta
INSERT INTO
    RISPOSTA (id_quesito, email_studente, tipo_risposta, esito)
VALUES
    (p_id_quesito, p_email_studente, 'APERTA', p_esito);

-- Ottiene l'id della risposta appena inserita
SELECT
    LAST_INSERT_ID() INTO id_risposta;

-- Inserisce la risposta nella tabella Risposta_quesito_aperto
INSERT INTO
    RISPOSTA_QUESITO_APERTO (id_risposta, risposta)
VALUES
    (id_risposta, p_risposta);

END IF;

COMMIT;

END $$ DELIMITER;

DELIMITER $$
CREATE PROCEDURE GetRisposteQuesiti (
    IN p_test_associato VARCHAR(100),
    IN p_email_studente VARCHAR(100)
) BEGIN
SELECT
    r.id_quesito                ,
    q.numero_quesito            ,
    DATE(r.TIMESTAMP) AS in_data,
    r.esito                     ,
    r.tipo_risposta             ,
    q.ID              AS `ID_Q`
FROM
    RISPOSTA AS r
    JOIN QUESITO q ON r.id_quesito = q.ID
WHERE
    r.ID IN (
        SELECT
            MAX(r1.ID)
        FROM
            RISPOSTA r1
            JOIN QUESITO q1 ON r1.id_quesito = q1.ID
        WHERE
            q1.test_associato = p_test_associato
            AND r1.email_studente = p_email_studente
        GROUP BY
            q1.numero_quesito
    )
GROUP BY
    ID_Q            ,
    q.numero_quesito,
    r.TIMESTAMP     ,
    r.esito         ,
    r.tipo_risposta
ORDER BY
    q.numero_quesito ASC;

END $$ DELIMITER;

-- getAllTests
DELIMITER $$
CREATE PROCEDURE GetAllTests () BEGIN
SELECT
    *
FROM
    TEST;

END $$ DELIMITER;

-- get quesiti del test
DELIMITER $$
CREATE PROCEDURE GetQuesitiTest (IN p_test_associato VARCHAR(100)) BEGIN
SELECT
    *
FROM
    QUESITO
WHERE
    test_associato = p_test_associato;

END $$ DELIMITER;

-- get opzioni quesito chiuso del test
DELIMITER $$
CREATE PROCEDURE GetOpzioniQuesitoChiuso (IN p_id_quesito INT) BEGIN
SELECT
    *
FROM
    QUESITO_CHIUSO_OPZIONE
WHERE
    QUESITO_CHIUSO_OPZIONE.id_quesito = p_id_quesito;

END $$ DELIMITER;

-- prendi opzioni quesito chiuso vere
DELIMITER $$
CREATE PROCEDURE GetOpzioniCorrette (IN p_id_quesito INT) BEGIN
SELECT
    *
FROM
    QUESITO_CHIUSO_OPZIONE
WHERE
    id_quesito = p_id_quesito
    AND is_corretta = 'TRUE';

END $$ DELIMITER;

-- prendi test del docente
DELIMITER $$
CREATE PROCEDURE GetTestsDelProfessore (IN p_email_professore VARCHAR(100)) BEGIN
SELECT
    *
FROM
    TEST
WHERE
    email_professore = p_email_professore;

END $$ DELIMITER;

-- get risposta aperta from risposta
DELIMITER $$
DROP PROCEDURE IF EXISTS GetRispostaQuesitoAperto $$
CREATE PROCEDURE GetRispostaQuesitoAperto (
    IN p_id_quesito INT            ,
    IN p_email_studente VARCHAR(100)
) BEGIN
SELECT
    id_risposta,
    risposta   ,
    esito      ,
    TIMESTAMP
FROM
    RISPOSTA_QUESITO_APERTO as ra
    JOIN RISPOSTA as r on ra.id_risposta = r.ID
WHERE
    id_risposta = (
        SELECT
            ID
        FROM
            RISPOSTA
        WHERE
            id_quesito = p_id_quesito
            AND email_studente = p_email_studente
        GROUP BY
            ID
        ORDER BY
            ID DESC
        LIMIT
            1
    );

END $$ DELIMITER;

-- get scelta quesito chiuso from risposta
DELIMITER $$
CREATE PROCEDURE GetSceltaQuesitoChiuso (
    IN p_id_quesito INT            ,
    IN p_email_studente VARCHAR(100)
) BEGIN
SELECT
    *
FROM
    RISPOSTA_QUESITO_CHIUSO ra
    JOIN RISPOSTA r ON r.ID = ra.id_risposta
WHERE
    id_risposta = (
        SELECT
            ID
        FROM
            RISPOSTA
        WHERE
            id_quesito = p_id_quesito
            AND email_studente = p_email_studente
        GROUP BY
            ID
        ORDER BY
            ID DESC
        LIMIT
            1
    );

END $$ DELIMITER;

-- inserisci il nuovo test creato all'interno di svolgimento_test per ogni studente con lo stato APERTO
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_test_creation AFTER
INSERT
    ON TEST FOR EACH ROW BEGIN
    -- Inserire i record per tutti gli studenti nella tabella SVOLGIMENTO_TEST
INSERT INTO
    SVOLGIMENTO_TEST (titolo_test, email_studente)
SELECT
    NEW.titolo   ,
    email_studente
FROM
    STUDENTE;

END $$ DELIMITER;

-- SONO QUI
-- trigger che si aziona quando un utente inserisce una risposta
-- la tabella svolgimento_test viene aggiornata e viene settata la data di inizio e lo stato IN_COMPLETAMENTO
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS update_svolgimento_test AFTER
INSERT
    ON RISPOSTA FOR EACH ROW BEGIN DECLARE is_prima_risposta INT;

-- prendi il test associato alla risposta 
DECLARE p_test_associato VARCHAR(100);

SELECT
    test_associato INTO p_test_associato
FROM
    QUESITO
WHERE
    ID = NEW.id_quesito;

SELECT
    COUNT(*) INTO is_prima_risposta
FROM
    RISPOSTA
    JOIN QUESITO ON RISPOSTA.id_quesito = QUESITO.ID
WHERE
    QUESITO.test_associato = p_test_associato
    AND email_studente = NEW.email_studente;

IF is_prima_risposta = 1 THEN
UPDATE SVOLGIMENTO_TEST
SET
    data_inizio = NOW()      ,
    stato = 'IN_COMPLETAMENTO'
WHERE
    titolo_test = p_test_associato
    AND email_studente = NEW.email_studente;

END IF;

-- Se tutte le ultime risposte inserite sono giuste, setta lo stato a CONCLUSO e la data di fine del test
IF(
    SELECT
        count(*)
    FROM
        RISPOSTA r
    WHERE
        r.esito = 'GIUSTA'
        AND r.ID in (
            SELECT
                MAX(r1.ID)
            FROM
                RISPOSTA r1
                JOIN QUESITO q ON r1.id_quesito = q.ID
            WHERE
                q.test_associato = p_test_associato
                AND r1.email_studente = NEW.email_studente
            GROUP BY
                r1.id_quesito
        )
) = (
    SELECT
        COUNT(*)
    FROM
        QUESITO
    WHERE
        test_associato = p_test_associato
) THEN
UPDATE SVOLGIMENTO_TEST
SET
    stato = 'CONCLUSO'      ,
    data_fine = NEW.TIMESTAMP
WHERE
    titolo_test = p_test_associato
    AND email_studente = NEW.email_studente;

END IF;

END $$ DELIMITER;

-- show results 
DELIMITER $$
CREATE PROCEDURE MostraRisultati (IN p_test_associato VARCHAR(100)) BEGIN
UPDATE TEST
SET
    VisualizzaRisposte = 1
WHERE
    titolo = p_test_associato;

END $$ DELIMITER;

-- se il prof modifica il test impostanto visualizzatest come true, allora contrassegna tutti i test come conclusi
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS update_test_conclusi AFTER
UPDATE ON TEST FOR EACH ROW BEGIN
-- Se il professore ha impostato VisualizzaRisposte a 1, contrassegna tutti i test come CONCLUSI
IF NEW.VisualizzaRisposte = 1 THEN
UPDATE SVOLGIMENTO_TEST
SET
    stato = 'CONCLUSO',
    data_fine = NOW()
WHERE
    titolo_test = NEW.titolo;

END IF;

END $$ DELIMITER;

-- studente invia messaggio
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InviaMessaggioDaStudente (
    IN p_titolo VARCHAR(100)        ,
    IN p_testo TEXT                 ,
    IN p_test_associato VARCHAR(100),
    IN p_mittente VARCHAR(100)      ,
    IN p_destinatario VARCHAR(100)
) BEGIN
START TRANSACTION;

-- Inserisce il messaggio nella tabella MESSAGGIO
INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (p_titolo, p_testo, p_test_associato);

-- Inserisce il messaggio nella tabella MESSAGGIO_PRIVATO
INSERT INTO
    MESSAGGIO_PRIVATO (id_messaggio, mittente, destinatario)
VALUES
    (LAST_INSERT_ID(), p_mittente, p_destinatario);

COMMIT;

END $$ DELIMITER;

-- docente invia messaggio a tutti gli studenti
DELIMITER $$
CREATE PROCEDURE InviaMessaggioDaDocente (
    IN p_mittente VARCHAR(100)     ,
    IN p_titolo VARCHAR(100)       ,
    IN p_testo TEXT                ,
    IN p_test_associato VARCHAR(100)
) BEGIN DECLARE last_id INT;

START TRANSACTION;

INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (p_titolo, p_testo, p_test_associato);

SELECT
    LAST_INSERT_ID() INTO last_id;

INSERT INTO
    BROADCAST (id_messaggio, mittente)
VALUES
    (last_id, p_mittente);

COMMIT;

END $$ DELIMITER;

-- get messaggi studente
DELIMITER $$
CREATE PROCEDURE GetMessaggiStudente (IN p_email_studente VARCHAR(100)) BEGIN
SELECT
    m.id              ,
    m.titolo          ,
    m.testo           ,
    m.data_inserimento,
    m.test_associato  ,
    b.mittente
FROM
    MESSAGGIO as m
    JOIN BROADCAST as b on b.id_messaggio = m.id;

END $$ DELIMITER;

-- get messaggi professore
DELIMITER $$
CREATE PROCEDURE GetMessaggiProfessore (IN p_email_professore VARCHAR(100)) BEGIN
SELECT
    m.id              ,
    m.titolo          ,
    m.testo           ,
    m.data_inserimento,
    m.test_associato  ,
    DM.mittente
FROM
    MESSAGGIO as m        ,
    MESSAGGIO_PRIVATO as DM
WHERE
    m.id = DM.id_messaggio
    AND DM.destinatario = p_email_professore;

END $$ DELIMITER;

-- classifica test conclusi dagli studenti
CREATE VIEW
    Classifica_test_completati AS
SELECT
    s.matricola,
    (
        SELECT
            COUNT(*)
        FROM
            SVOLGIMENTO_TEST st
        WHERE
            st.email_studente = s.email_studente
            AND st.stato = 'CONCLUSO'
    ) AS Test_conclusi
FROM
    STUDENTE s
GROUP BY
    s.email_studente,
    Test_conclusi
ORDER BY
    Test_conclusi DESC;

# Visualizzare	la	classifica	degli	studenti,	sulla	base	del	numero	di	risposte	corrette	inserite	
# rispetto	al	numero	 totale	di	risposte	inserite.	Nella	classifica	NON	devono	apparire	i	dati	
# sensibili	dello	studente	(nome,	cognome,	email)	ma	solo	il	codice	alfanumerico.
DROP VIEW IF EXISTS Classifica_risposte_giusta;

CREATE VIEW
    Classifica_risposte_giusta AS
SELECT
    s.matricola,
    (
        CASE
            WHEN (
                SELECT
                    count(*) as tot_risposte
                FROM
                    RISPOSTA r1
                WHERE
                    r1.email_studente = s.email_studente
                    AND r1.TIMESTAMP IN (
                        SELECT
                            MAX(TIMESTAMP)
                        FROM
                            RISPOSTA r2
                        WHERE
                            r2.email_studente = s.email_studente
                    )
            ) > 0 THEN (
                SELECT
                    COUNT(*) as risp_giuste
                FROM
                    RISPOSTA r
                WHERE
                    r.email_studente = s.email_studente
                    AND r.esito = 'GIUSTA'
                    AND r.TIMESTAMP IN (
                        SELECT
                            MAX(TIMESTAMP)
                        FROM
                            RISPOSTA r2
                        WHERE
                            r2.email_studente = s.email_studente
                    )
            ) / (
                SELECT
                    count(*) as tot_risposte
                FROM
                    RISPOSTA r1
                WHERE
                    r1.email_studente = s.email_studente
                    AND r1.TIMESTAMP IN (
                        SELECT
                            MAX(TIMESTAMP)
                        FROM
                            RISPOSTA r2
                        WHERE
                            r2.email_studente = s.email_studente
                    )
            )
            ELSE 0
        END
    ) AS Risposte_corrette
FROM
    STUDENTE s
GROUP BY
    s.matricola     ,
    Risposte_corrette
ORDER BY
    Risposte_corrette DESC;

DELIMITER $$
CREATE PROCEDURE GetClassificaRisposteGiuste () BEGIN
SELECT
    rg.matricola                   ,
    rg.Risposte_corrette as Rapporto
FROM
    Classifica_risposte_giusta as rg;

END $$ DELIMITER;

-- get classifica test completati
DELIMITER $$
CREATE PROCEDURE GetClassificaTestCompletati () BEGIN
SELECT
    c.matricola   ,
    c.Test_conclusi
FROM
    Classifica_test_completati as c;

END $$ DELIMITER;

-- stored procedure per avere l'ordine delle chiavi primarie di una tabella
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetPrimaryKey (IN p_table_name VARCHAR(100)) BEGIN
SELECT
    ID             AS INDICE        ,
    nome_attributo AS NOME_ATTRIBUTO,
    tipo_attributo AS TIPO_ATTRIBUTO
FROM
    TAB_ATT AS TA
WHERE
    TA.nome_tabella = p_table_name
    AND TA.key_part = "TRUE"
ORDER BY
    ID ASC;

END $$ DELIMITER;

-- get tabelle delle tabelle
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTabelleCreate () BEGIN
SELECT
    *
FROM
    TABELLA_DELLE_TABELLE;

END $$ DELIMITER;

-- crea GetSoluzioneQuesitoAperto
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetSoluzioneQuesitoAperto (IN p_id_quesito INT) BEGIN
SELECT
    *
FROM
    QUESITO_APERTO_SOLUZIONE
WHERE
    QUESITO_APERTO_SOLUZIONE.id_quesito = p_id_quesito;

END $$ DELIMITER;

-- INSERT INTO TAB_ATT
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciAttributo (
    IN p_nome_tabella VARCHAR(20)   ,
    IN p_nome_attributo VARCHAR(100),
    IN p_tipo_attributo VARCHAR(15)
) BEGIN
INSERT INTO
    TAB_ATT (nome_tabella, nome_attributo, tipo_attributo)
VALUES
    (
        p_nome_tabella  ,
        p_nome_attributo,
        p_tipo_attributo
    );

END $$ DELIMITER;

-- INSERT INTO TABELLA_DELLE_TABELLE
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciTabellaDiEsercizio (
    IN p_nome_tabella VARCHAR(20),
    IN p_creatore VARCHAR(100)
) BEGIN
INSERT INTO
    TABELLA_DELLE_TABELLE (nome_tabella, creatore)
VALUES
    (p_nome_tabella, p_creatore);

END $$ DELIMITER;

-- INSERT INTO VINCOLI
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciChiaveEsterna (
    IN p_nome_tabella VARCHAR(20)       ,
    IN p_nome_attributo VARCHAR(100)    ,
    IN p_tabella_vincolata VARCHAR(20)  ,
    IN p_attributo_vincolato VARCHAR(100)
) BEGIN
-- Inserisce la chiave esterna nella tabella VINCOLI
INSERT INTO
    VINCOLI (
        nome_tabella      ,
        nome_attributo    ,
        tabella_vincolata ,
        attributo_vincolato
    )
VALUES
    (
        p_nome_tabella      ,
        p_nome_attributo    ,
        p_tabella_vincolata ,
        p_attributo_vincolato
    );

END $$ DELIMITER;

-- aggiungi chiave
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS AggiungiChiavePrimaria (
    IN p_nome_tabella VARCHAR(20),
    IN p_pezzo_chiave VARCHAR(100)
) BEGIN
UPDATE TAB_ATT
SET
    key_part = "TRUE"
WHERE
    nome_tabella = p_nome_tabella
    AND nome_attributo = p_pezzo_chiave;

END $$ DELIMITER;

-- get attributi tabella
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetAttributiTabella (IN p_nome_tabella VARCHAR(20)) BEGIN
SELECT
    nome_attributo            ,
    TAB_ATT.key_part AS is_key,
    tipo_attributo
FROM
    TAB_ATT
WHERE
    nome_tabella = p_nome_tabella
ORDER BY
    TAB_ATT.ID;

END $$ DELIMITER;

-- get chiavi esterne
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetChiaviEsterne (IN p_nome_tabella VARCHAR(20)) BEGIN
SELECT
    *
FROM
    VINCOLI
WHERE
    nome_tabella = p_nome_tabella;

END $$ DELIMITER;

-- trigger per una tabella di prova preinserita
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_insert_tipi_pokemon AFTER
INSERT
    ON TIPI_POKEMON FOR EACH ROW BEGIN
    -- Incrementa il numero di righe nella tabella
UPDATE TABELLA_DELLE_TABELLE
SET
    num_righe = num_righe + 1
WHERE
    nome_tabella = 'TIPI_POKEMON';

END $$ DELIMITER;

-- trigger per una tabella di prova preinserita
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_insert_pokemon AFTER
INSERT
    ON POKEMON FOR EACH ROW BEGIN
    -- Incrementa il numero di righe nella tabella
UPDATE TABELLA_DELLE_TABELLE
SET
    num_righe = num_righe + 1
WHERE
    nome_tabella = 'POKEMON';

END $$ DELIMITER;

-- get ID quesito
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetQuesitoTest (
    IN p_test_associato VARCHAR(100),
    IN p_numero_quesito INT
) BEGIN
SELECT
    *
FROM
    QUESITO
WHERE
    test_associato = p_test_associato
    AND numero_quesito = p_numero_quesito;

END $$ DELIMITER;

-- inserisci quesito-tabella
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciQuesitoTabella (
    IN p_id_quesito INT         ,
    IN p_nome_tabella VARCHAR(20)
) BEGIN
INSERT INTO
    QUESITI_TABELLA (id_quesito, nome_tabella)
VALUES
    (p_id_quesito, p_nome_tabella);

END $$ DELIMITER;

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTabelleQuesito (IN p_test_associato VARCHAR(100)) BEGIN
SELECT DISTINCT
    (nome_tabella)
FROM
    QUESITI_TABELLA as QT
    JOIN QUESITO as Q ON QT.id_quesito = Q.ID
WHERE
    Q.test_associato = p_test_associato;

END $$ DELIMITER;

INSERT INTO
    `TEST` (
        `VisualizzaRisposte`,
        `dataCreazione`     ,
        `email_professore`  ,
        `titolo`
    )
VALUES
    (
        0                    ,
        '2024-05-01 14:23:55',
        'professore@unibo.it',
        'Test pokemon'
    );

INSERT INTO
    `QUESITO` (
        `descrizione`       ,
        `livello_difficolta`,
        `numero_quesito`    ,
        `numero_risposte`   ,
        `test_associato`    ,
        `tipo_quesito`
    )
VALUES
    (
        'Pikachu è tipo acqua',
        'BASSO'               ,
        1                     ,
        0                     ,
        'Test pokemon'        ,
        'CHIUSO'
    ),
    (
        'Seleziona il NOME dei pokemon di tipo \'Electric\'',
        'BASSO'                                             ,
        2                                                   ,
        0                                                   ,
        'Test pokemon'                                      ,
        'APERTO'
    ),
    (
        'Seleziona il nome di tutti i tipo Water oppure Fire',
        'MEDIO'                                              ,
        3                                                    ,
        1                                                    ,
        'Test pokemon'                                       ,
        'APERTO'
    );

INSERT INTO
    `QUESITO_CHIUSO_OPZIONE` (
        `id_quesito`    ,
        `is_corretta`   ,
        `numero_opzione`,
        `testo`
    )
VALUES
    (1, 'FALSE', 1, 'Vero'),
    (1, 'TRUE', 2, 'Falso');

INSERT INTO
    `QUESITO_APERTO_SOLUZIONE` (
        `id_quesito`         ,
        `id_soluzione`       ,
        `soluzione_professore`
    )
VALUES
    (
        2                                                        ,
        1                                                        ,
        "SELECT nome FROM POKEMON WHERE tipo = ciao Electric ciao"
    ),
    (
        2                                                        ,
        2                                                        ,
        'SELECT nome FROM POKEMON WHERE tipo = ciao Electric ciao'
    ),
    (
        3                                                                                     ,
        3                                                                                     ,
        'SELECT nome FROM POKEMON WHERE (tipo =  ciao Water ciao  || tipo =  ciao Fire ciao ) '
    );

INSERT INTO
    `TABELLA_DELLE_TABELLE` (
        `creatore`      ,
        `data_creazione`,
        `nome_tabella`  ,
        `num_righe`
    )
VALUES
    (
        'professore@unibo.it',
        '2024-05-01 14:11:59',
        'POKEMON'            ,
        8
    ),
    (
        'professore@unibo.it',
        '2024-05-01 14:01:59',
        'TIPI_POKEMON'       ,
        12
    );

INSERT INTO
    `TAB_ATT` (
        `ID`            ,
        `key_part`      ,
        `nome_attributo`,
        `nome_tabella`  ,
        `tipo_attributo`
    )
VALUES
    (1, 'TRUE', 'TIPO', 'TIPI_POKEMON', 'VARCHAR'),
    (2, 'TRUE', 'nome', 'POKEMON', 'VARCHAR')     ,
    (3, 'FALSE', 'tipo', 'POKEMON', 'VARCHAR')    ,
    (4, 'FALSE', 'PS', 'POKEMON', 'INT')          ,
    (5, 'FALSE', 'is_legendary', 'POKEMON', 'INT');

INSERT INTO
    `VINCOLI` (
        `attributo_vincolato`,
        `nome_attributo`     ,
        `nome_tabella`       ,
        `tabella_vincolata`
    )
VALUES
    ('TIPO', 'tipo', 'POKEMON', 'TIPI_POKEMON');

INSERT INTO
    `QUESITI_TABELLA` (`ID`, `id_quesito`, `nome_tabella`)
VALUES
    (1, 1, 'POKEMON')     ,
    (2, 1, 'TIPI_POKEMON'),
    (3, 2, 'POKEMON')     ,
    (4, 2, 'TIPI_POKEMON');

-- get matricola
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetMatricola (IN p_email_studente VARCHAR(100)) BEGIN
SELECT
    matricola
FROM
    STUDENTE
WHERE
    email_studente = p_email_studente;

END $$ DELIMITER;

-- crea classifica (view) dei quesiti ordinati per numero di risposte 
DROP VIEW IF EXISTS Classifica_quesitiPerNumeroRisposte;

CREATE VIEW
    Classifica_quesitiPerNumeroRisposte AS
SELECT
    ID            ,
    test_associato,
    numero_quesito,
    numero_risposte
FROM
    QUESITO
ORDER BY
    numero_risposte DESC;

-- get classifica quesiti
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetClassificaQuesitiPerNumeroRisposte () BEGIN
SELECT
    test_associato,
    numero_quesito,
    numero_risposte
FROM
    Classifica_quesitiPerNumeroRisposte;

END $$ DELIMITER;

-- elimina tabella
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS EliminaTabella (IN p_nome_tabella VARCHAR(20)) BEGIN
DELETE FROM TABELLA_DELLE_TABELLE
WHERE
    nome_tabella = p_nome_tabella;

END $$ DELIMITER;

-- GetQuesitiAssociatiAlTest
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetQuesitiAssociatiAlTest (IN p_test_associato VARCHAR(100)) BEGIN
SELECT
    *
FROM
    QUESITO
WHERE
    test_associato = p_test_associato
ORDER BY
    numero_quesito ASC;

END $$ DELIMITER;

-- GetTabelleQuesitiNum
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTabelleQuesitiNum (IN p_id_quesito INT) BEGIN
SELECT
    nome_tabella
FROM
    QUESITI_TABELLA as QT
WHERE
    QT.id_quesito = p_id_quesito;

END $$ DELIMITER;

-- get test
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTest (IN p_titolo VARCHAR(100)) BEGIN
SELECT
    *
FROM
    TEST
WHERE
    titolo = p_titolo;

END $$ DELIMITER;

-- get professori
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetProfessori () BEGIN
SELECT
    email_professore
FROM
    PROFESSORE;

END $$ DELIMITER;

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS CheckRisultatiStudente (IN p_email_studente VARCHAR(100)) BEGIN
SELECT
    COUNT(*) as 'check'
FROM
    SVOLGIMENTO_TEST st
where
    st.email_studente = p_email_studente
    and stato = "CONCLUSO";

END $$ DELIMITER;

-- get GetTestDelloStudente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTestDelloStudente (IN p_email_studente VARCHAR(100)) BEGIN
SELECT
    *
FROM
    SVOLGIMENTO_TEST
    JOIN TEST ON SVOLGIMENTO_TEST.titolo_test = TEST.titolo
WHERE
    email_studente = p_email_studente;

END $$ DELIMITER;

-- cerca utente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS CercaUtente (IN p_email VARCHAR(100)) BEGIN
SELECT
    email
FROM
    UTENTE
WHERE
    email = p_email;

END $$ DELIMITER;

-- GetTabelleRiferite
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTabelleRiferite (IN p_nome_tabella VARCHAR(20)) BEGIN
SELECT DISTINCT
    (tabella_vincolata)
FROM
    VINCOLI
WHERE
    nome_tabella = p_nome_tabella;

END $$ DELIMITER;

-- GetTestsDelProfessoreAperti
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTestsDelProfessoreAperti (IN p_email_professore VARCHAR(100)) BEGIN
SELECT
    *
FROM
    TEST
WHERE
    email_professore = p_email_professore
    AND VisualizzaRisposte = 0;

END $$ DELIMITER;

-- getInfoProfessore
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetInfoProfessore (IN p_email_professore VARCHAR(100)) BEGIN
SELECT
    *
FROM
    PROFESSORE
    JOIN UTENTE ON PROFESSORE.email_professore = UTENTE.email
WHERE
    email_professore = p_email_professore;

END $$ DELIMITER;

-- GetInfoStudente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetInfoStudente (IN p_email_studente VARCHAR(100)) BEGIN
SELECT
    *
FROM
    STUDENTE
    JOIN UTENTE ON STUDENTE.email_studente = UTENTE.email
WHERE
    email_studente = p_email_studente;

END $$ DELIMITER;

-- EliminaQuesito
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS EliminaQuesito (IN p_id_quesito INT) BEGIN
DELETE FROM QUESITO
WHERE
    ID = p_id_quesito;

END $$ DELIMITER;

-- EliminaTest 
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS EliminaTest (IN p_titolo VARCHAR(100)) BEGIN
DELETE FROM TEST
WHERE
    titolo = p_titolo;

END $$ DELIMITER;