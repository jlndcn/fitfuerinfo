<?php

session_start();

require_once 'config/database.php';

$error = '';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = isset($_POST['username'])
        ? trim($_POST['username'])
        : '';

    $password = isset($_POST['password'])
        ? $_POST['password']
        : '';

    if ($username === '' || $password === '') {

        $error = 'Bitte Benutzername und Passwort eingeben.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT
                user_id,
                username,
                password_hash,
                role,
                active
             FROM users
             WHERE username = ?
             LIMIT 1'
        );

        $stmt->execute(array($username));

        $user = $stmt->fetch();

        if (
            $user &&
            $user['active'] == 1 &&
            password_verify($password, $user['password_hash'])
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: dashboard.php');
            exit;

        } else {

            $error = 'Benutzername oder Passwort ist nicht korrekt.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="de">

<head>

    <meta charset="UTF-8">

    <title>FitFuerInfo - Login</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

    <div class="login-container">

        <h1>FitFuerInfo</h1>

        <h2>Anmeldung</h2>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?php echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </div>

        <?php endif; ?>

        <form method="post" action="login.php">

            <label for="username">
                Benutzername
            </label>

            <input
                type="text"
                id="username"
                name="username"
                required
                autocomplete="username"
            >

            <label for="password">
                Passwort
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
            >

            <button type="submit">
                Anmelden
            </button>

        </form>

    </div>

</body>

</html>