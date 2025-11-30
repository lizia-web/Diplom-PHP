<?php include "session.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    die("Доступ заборонено. Ви не адміністратор.");
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";
global $user_id;

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (PDOException $e) {

}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/users.css">
    <title>Користувачі</title>
</head>
<body>

<div class="container">
    <?php include "menu.php" ?>

    <div class="content">
        <div class="users-table">
            <h2>Користувачі</h2>

            <table>
                <tr>
                    <th>Ім'я користувача</th>
                    <th>Роль</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td>
                            <select onchange="updateUserRole(<?= $user['id'] ?>, this.value)">
                                <option value="user" <?= $user['user_type'] === 'user' ? 'selected' : '' ?>>user
                                </option>
                                <option value="admin" <?= $user['user_type'] === 'admin' ? 'selected' : '' ?>>admin
                                </option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php include "footer.php" ?>

    </div>


</div>
<script src="../js/users.js"></script>
</body>
</html>
