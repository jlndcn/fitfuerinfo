<?php

require_once 'config/database.php';

$message = '';
$error = '';


// Prüfen, ob bereits ein Administrator existiert
$stmt = $pdo->prepare(
    "SELECT user_id
     FROM users
     WHERE role = 'admin'
     LIMIT 1"
);

$stmt->execute();

$adminExists = $stmt->fetch();


if ($adminExists) {

    $error = 'Es existiert bereits ein Systemverwalter.';

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = isset($_POST['username'])
        ? trim($_POST['username'])
        : '';

    $firstName = isset($_POST['first_name'])
        ? trim($_POST['first_name'])
        : '';

    $lastName = isset($_POST['last_name'])
        ? trim($_POST['last_name'])
        : '';

    $password = isset($_POST['password'])
        ? $_POST['password']
        : '';


    // Pflichtfelder prüfen
    if (
        $username === '' ||
        $firstName === '' ||
        $lastName === '' ||
        $password === ''
    ) {

        $error = 'Bitte alle Felder ausfüllen.';

    // Passwortlänge prüfen
    } elseif (strlen($password) < 4) {

        $error = 'Das Passwort muss mindestens vier Zeichen lang sein.';

    // Mindestens ein Kleinbuchstabe
    } elseif (!preg_match('/[a-z]/', $password)) {

        $error = 'Das Passwort muss mindestens einen Kleinbuchstaben enthalten.';

    // Mindestens eine Zahl
    } elseif (!preg_match('/[0-9]/', $password)) {

        $error = 'Das Passwort muss mindestens eine Zahl enthalten.';

    } else {

        // Passwort sicher hashen
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        // Administrator speichern
        $stmt = $pdo->prepare(
            "INSERT INTO users
                (
                    username,
                    first_name,
                    last_name,
                    password_hash,
                    role,
                    active
                )
             VALUES
                (?, ?, ?, ?, 'admin', 1)"
        );

        try {

            $stmt->execute(
                array(
                    $username,
                    $firstName,
                    $lastName,
                    $passwordHash
                )
            );

            $message = 'Systemverwalter wurde erfolgreich angelegt.';

        } catch (PDOException $e) {

            $error = 'Der Systemverwalter konnte nicht angelegt werden.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="de">

<head>

    <meta charset="UTF-8">

    <title>FitFuerInfo - Einrichtung</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

    <div class="login-container">

        <h1>FitFuerInfo</h1>

        <h2>Systemverwalter einrichten</h2>


        <?php if ($error !== ''): ?>

            <div class="error">

                <?php
                echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>

            </div>

        <?php endif; ?>


        <?php if ($message !== ''): ?>

            <p>
                <?php
                echo htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </p>

            <p>
                <a href="login.php">
                    Zum Login
                </a>
            </p>

        <?php elseif (!$adminExists): ?>

            <form
                method="post"
                action="setup_admin.php"
            >

                <label for="username">
                    Benutzername
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                >


                <label for="first_name">
                    Vorname
                </label>

                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    required
                >


                <label for="last_name">
                    Nachname
                </label>

                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    required
                >


                <label for="password">
                    Passwort
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >


                <button type="submit">
                    Systemverwalter anlegen
                </button>

            </form>

        <?php endif; ?>

    </div>

</body>

</html>