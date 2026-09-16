<?php

require_once dirname(__FILE__) . '/../includes/auth.php';

$pageTitle = 'Kurs löschen';
$currentNav = 'courses';

$courseId = getInt('id');
if ($courseId <= 0) {
    $courseId = postInt('id');
}

$stmt = $pdo->prepare(
    'SELECT course_id, course_name
     FROM courses
     WHERE course_id = ?
     LIMIT 1'
);
$stmt->execute(array($courseId));
$course = $stmt->fetch();

if (!$course) {
    setFlash('error', 'Der Kurs wurde nicht gefunden.');
    redirect('/courses/index.php');
}

if (!canEditCourse($pdo, $courseId)) {
    setFlash('error', 'Sie dürfen diesen Kurs nicht löschen.');
    redirect('/courses/index.php');
}

if (courseHasFutureBookings($pdo, $courseId)) {
    setFlash('error', 'Der Kurs kann nicht gelöscht werden, weil noch zukünftige Raumbuchungen existieren.');
    redirect('/courses/index.php');
}

if (courseHasAnyBookings($pdo, $courseId)) {
    setFlash('error', 'Der Kurs kann nicht gelöscht werden, weil noch Buchungen existieren. Deaktivieren Sie den Kurs, sofern keine zukünftigen Buchungen vorhanden sind.');
    redirect('/courses/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf('/courses/delete.php?id=' . $courseId);

    try {
        $stmt = $pdo->prepare('DELETE FROM courses WHERE course_id = ?');
        $stmt->execute(array($courseId));
        setFlash('success', 'Der Kurs wurde gelöscht.');
        redirect('/courses/index.php');
    } catch (PDOException $e) {
        error_log('Kurs löschen fehlgeschlagen: ' . $e->getMessage());
        setFlash('error', 'Der Kurs konnte nicht gelöscht werden.');
        redirect('/courses/index.php');
    }
}

require_once APP_ROOT . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Kurs löschen</h1>
        <p class="muted">Diese Aktion kann nicht rückgängig gemacht werden.</p>
    </div>
</div>

<div class="card">
    <p>
        Soll der Kurs
        <strong><?php echo e($course['course_name']); ?></strong>
        wirklich gelöscht werden?
    </p>

    <form method="post" action="<?php echo e(BASE_URL); ?>/courses/delete.php?id=<?php echo (int) $courseId; ?>">
        <?php echo csrfField(); ?>
        <input type="hidden" name="id" value="<?php echo (int) $courseId; ?>">
        <div class="form-actions">
            <button type="submit" class="btn btn-danger">Endgültig löschen</button>
            <a class="btn btn-secondary" href="<?php echo e(BASE_URL); ?>/courses/index.php">Abbrechen</a>
        </div>
    </form>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
