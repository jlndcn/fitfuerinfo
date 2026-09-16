<?php

require_once 'includes/auth.php';

?>

<!DOCTYPE html>
<html lang="de">

<head>

    <meta charset="UTF-8">

    <title>FitFuerInfo - Dashboard</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

    <header>

        <h1>FitFuerInfo</h1>

        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="courses/">Kurse</a>
            <a href="rooms/">Räume</a>
            <a href="bookings/">Buchungen</a>
            <a href="logout.php">Abmelden</a>
        </nav>

    </header>

    <main>

        <h2>Dashboard</h2>

        <p>
            Willkommen,
            <?php
            echo htmlspecialchars(
                $_SESSION['username'],
                ENT_QUOTES,
                'UTF-8'
            );
            ?>.
        </p>

    </main>

</body>

</html>