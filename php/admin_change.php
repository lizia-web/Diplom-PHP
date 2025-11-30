<?php include "session.php"?>

<?php

session_start();

// Перевіряємо, чи користувач — адміністратор
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    die("Доступ заборонено. Ви не адміністратор.");
}

$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Додавання фандому з перевіркою унікальності
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_fandom'])) {
        $fandom_name = trim($_POST['fandom_name']);
        if (!empty($fandom_name)) {
            $fandom_name_lower = mb_strtolower($fandom_name, 'UTF-8');

            $stmt = $pdo->prepare("SELECT id FROM fandoms WHERE LOWER(name COLLATE \"und-x-icu\") = LOWER(:name COLLATE \"und-x-icu\")");
            $stmt->execute(['name' => $fandom_name_lower]);
            $existing_fandom = $stmt->fetch();

            if ($existing_fandom) {
                $error = "Цей фандом вже існує!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO fandoms (name) VALUES (:name)");
                $stmt->execute(['name' => $fandom_name]);
                $success = "Фандом успішно доданий!";
            }
        }
    }

    // Редагування фандому
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_fandom'])) {
        $fandom_id = $_POST['fandom_id'];
        $new_name = trim($_POST['new_fandom_name']);
        if (!empty($new_name)) {
            $stmt = $pdo->prepare("UPDATE fandoms SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $new_name, 'id' => $fandom_id]);
            $success = "Фандом успішно оновлено!";
        }
    }

    // Редагування персонажа
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_character'])) {
        $character_id = $_POST['character_id'];
        $new_name = trim($_POST['new_character_name']);
        if (!empty($new_name)) {
            $stmt = $pdo->prepare("UPDATE characters SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $new_name, 'id' => $character_id]);
            $success = "Персонаж успішно оновлено!";
        }
    }

    // Отримання списку фандомів
    $stmt = $pdo->query("SELECT * FROM fandoms ORDER BY name");
    $fandoms = $stmt->fetchAll();

    // Отримання списку персонажів
    $stmt = $pdo->query("SELECT characters.id, characters.name, fandoms.name AS fandom_name 
                         FROM characters 
                         JOIN fandoms ON characters.fandom_id = fandoms.id 
                         ORDER BY fandoms.name, characters.name");
    $characters = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Помилка підключення: " . $e->getMessage());
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
    <link rel="stylesheet" href="../css/admin_change.css">
    <link rel="stylesheet" href="../css/footer.css">
    <title>Редагувати фандом</title>
</head>
<body>

<div class="container">

    <?php include "menu.php"?>

    <div class="content">


        <div class="forms">

            <h1>Редагування фандомів</h1>
            <form method="POST">
                <select name="fandom_id" required>
                    <option value="">Оберіть фандом</option>
                    <?php foreach ($fandoms as $fandom): ?>
                        <option value="<?= $fandom['id'] ?>"><?= htmlspecialchars($fandom['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Нова назва:</label>
                <input type="text" name="new_fandom_name" required>
                <button type="submit" name="edit_fandom">Оновити</button>
            </form>

            <h1>Редагування персонажів</h1>
            <form method="POST">
                <select name="character_id" required>
                    <option value="">Оберіть персонажа</option>
                    <?php foreach ($characters as $character): ?>
                        <option value="<?= $character['id'] ?>"><?= htmlspecialchars($character['name']) ?> (<?= htmlspecialchars($character['fandom_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <label>Нове ім'я:</label>
                <input type="text" name="new_character_name" required>
                <button type="submit" name="edit_character">Оновити</button>
            </form>

        </div>

        <?php include "footer.php"?>
    </div>

</div>

</body>
</html>
