<?php

require_once dirname(__FILE__) . '/../includes/auth.php';
requireAdmin();

$pageTitle = 'Mitarbeiter bearbeiten';
$currentNav = 'users';

$userId = getInt('id');
$error = '';

$stmt = $pdo->prepare(
    'SELECT user_id, username, first_name, last_name, role, active
     FROM users
     WHERE user_id = ?
     LIMIT 1'
);
$stmt->execute(array($userId));
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'Der Benutzer wurde nicht gefunden.');
    redirect('/users/index.php');
}

$firstName = $user['first_name'];
$lastName = $user['last_name'];
$active = (int) $user['active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrf()) {
        $error = 'Die Anfrage konnte nicht bestätigt werden. Bitte das Formular erneut absenden.';
    } else {
        $firstName = postValue('first_name', '');
        $lastName = postValue('last_name', '');
        $active = postInt('active') === 1 ? 1 : 0;
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if ($firstName === '' || $lastName === '') {
            $error = 'Bitte Vorname und Nachname ausfüllen.';
        } elseif ($password !== '') {
            $passwordError = validatePassword($password);
            if ($passwordError !== '') {
                $error = $passwordError;
            }
        }

        if ($error === '' && $active === 0 && (int) $user['user_id'] === currentUserId()) {
            $error = 'Sie können das eigene Konto nicht deaktivieren.';
        }

        if ($error === '' && $active === 0 && $user['role'] === 'admin') {
            $stmt = $pdo->query(
                "SELECT COUNT(*) FROM users WHERE role = 'admin' AND active = 1"
            );
            if ((int) $stmt->fetchColumn() <= 1) {
                $error = 'Der letzte aktive Administrator kann nicht deaktiviert werden.';
            }
        }

        if ($error === '') {
            try {
                if ($password !== '') {
                    $stmt = $pdo->prepare(
                        'UPDATE users
                         SET first_name = ?, last_name = ?, active = ?, password_hash = ?
                         WHERE user_id = ?'
                    );
                    $stmt->execute(
                        array(
                            $firstName,
                            $lastName,
                            $active,
                            password_hash($password, PASSWORD_DEFAULT),
                            $userId
                        )
                    );
                } else {
                    $stmt = $pdo->prepare(
                        'UPDATE users
                         SET first_name = ?, last_name = ?, active = ?
                         WHERE user_id = ?'
                    );
                    $stmt->execute(array($firstName, $lastName, $active, $userId));
                }

                setFlash('success', 'Der Mitarbeiter wurde aktualisiert.');
                redirect('/users/index.php');
            } catch (PDOException $e) {
                error_log('Mitarbeiter aktualisieren fehlgeschlagen: ' . $e->getMessage());
                $error = 'Der Mitarbeiter konnte nicht gespeichert werden.';
            }
        }
    }
}

require_once APP_ROOT . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Mitarbeiter bearbeiten</h1>
        <p class="muted">Benutzername und Rolle werden nur angezeigt, nicht geändert.</p>
    </div>
</div>

<div class="card">
    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(BASE_URL); ?>/users/edit.php?id=<?php echo (int) $userId; ?>">
        <?php echo csrfField(); ?>

        <label>Benutzername</label>
        <input type="text" value="<?php echo e($user['username']); ?>" disabled>

        <label>Rolle</label>
        <input type="text" value="<?php echo e(roleLabel($user['role'])); ?>" disabled>

        <label for="first_name">Vorname</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo e($firstName); ?>" required>

        <label for="last_name">Nachname</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo e($lastName); ?>" required>

        <label for="active">Status</label>
        <select id="active" name="active">
            <option value="1"<?php echo $active === 1 ? ' selected' : ''; ?>>aktiv</option>
            <option value="0"<?php echo $active === 0 ? ' selected' : ''; ?>>deaktiviert</option>
        </select>

        <label for="password">Neues Passwort (optional)</label>
        <input type="password" id="password" name="password">
        <p class="hint">Leer lassen, wenn das bisherige Passwort gültig bleiben soll.</p>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Speichern</button>
            <a class="btn btn-secondary" href="<?php echo e(BASE_URL); ?>/users/index.php">Abbrechen</a>
        </div>
    </form>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
