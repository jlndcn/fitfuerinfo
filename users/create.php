<?php

require_once dirname(__FILE__) . '/../includes/auth.php';
requireAdmin();

$pageTitle = 'Mitarbeiter anlegen';
$currentNav = 'users';

$error = '';
$username = '';
$firstName = '';
$lastName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrf()) {
        $error = 'Die Anfrage konnte nicht bestätigt werden. Bitte das Formular erneut absenden.';
    } else {
        $username = postValue('username', '');
        $firstName = postValue('first_name', '');
        $lastName = postValue('last_name', '');
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $passwordError = validatePassword($password);

        if ($username === '' || $firstName === '' || $lastName === '' || $password === '') {
            $error = 'Bitte alle Felder ausfüllen.';
        } elseif ($passwordError !== '') {
            $error = $passwordError;
        } else {
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
            $stmt->execute(array($username));

            if ($stmt->fetch()) {
                $error = 'Dieser Benutzername ist bereits vergeben.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO users
                            (username, first_name, last_name, password_hash, role, active)
                         VALUES
                            (?, ?, ?, ?, 'employee', 1)"
                    );
                    $stmt->execute(array($username, $firstName, $lastName, $passwordHash));
                    setFlash('success', 'Der Mitarbeiter wurde angelegt.');
                    redirect('/users/index.php');
                } catch (PDOException $e) {
                    error_log('Mitarbeiter anlegen fehlgeschlagen: ' . $e->getMessage());
                    $error = 'Der Mitarbeiter konnte nicht angelegt werden.';
                }
            }
        }
    }
}

require_once APP_ROOT . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Mitarbeiter anlegen</h1>
        <p class="muted">Neue Benutzer erhalten die Rolle Mitarbeiter.</p>
    </div>
</div>

<div class="card">
    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <p class="hint">
        Das Passwort muss mindestens 4 Zeichen lang sein und mindestens einen Kleinbuchstaben sowie eine Zahl enthalten.
    </p>

    <form method="post" action="<?php echo e(BASE_URL); ?>/users/create.php">
        <?php echo csrfField(); ?>

        <label for="username">Benutzername</label>
        <input type="text" id="username" name="username" value="<?php echo e($username); ?>" required>

        <label for="first_name">Vorname</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo e($firstName); ?>" required>

        <label for="last_name">Nachname</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo e($lastName); ?>" required>

        <label for="password">Passwort</label>
        <input type="password" id="password" name="password" required>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Speichern</button>
            <a class="btn btn-secondary" href="<?php echo e(BASE_URL); ?>/users/index.php">Abbrechen</a>
        </div>
    </form>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
