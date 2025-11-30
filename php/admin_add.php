<?php

session_start();

// Перевіряємо, чи користувач — адміністратор
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    die("Доступ заборонено. Ви не адміністратор.");
    header("Location: login_form.php");
    exit;
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

    // Додавання персонажа
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_character'])) {
        $fandom_id = $_POST['fandom_id'];
        $character_name = trim($_POST['character_name']);

        if (!empty($character_name) && !empty($fandom_id)) {
            // Приводимо ім'я персонажа до нижнього регістру з підтримкою всіх мов
            $character_name_lower = mb_strtolower($character_name, 'UTF-8');

            $stmt = $pdo->prepare("SELECT id FROM characters WHERE fandom_id = :fandom_id AND LOWER(name COLLATE \"und-x-icu\") = LOWER(:name COLLATE \"und-x-icu\")");
            $stmt->execute([
                'fandom_id' => $fandom_id,
                'name' => $character_name_lower
            ]);
            $existing_character = $stmt->fetch();

            if ($existing_character) {
                $error = "Цей персонаж вже існує у вибраному фандомі!";
            } else {
                try {
                    // Додаємо персонажа (оригінальний регістр зберігається)
                    $stmt = $pdo->prepare("INSERT INTO characters (fandom_id, name) VALUES (:fandom_id, :name)");
                    $stmt->execute([
                        'fandom_id' => $fandom_id,
                        'name' => $character_name
                    ]);
                    $success = "Персонаж успішно доданий!";
                } catch (PDOException $e) {
                    if ($e->getCode() == '23505') {
                        $error = "Цей персонаж вже існує у вибраному фандомі!";
                    } else {
                        $error = "Помилка бази даних: " . $e->getMessage();
                    }
                }
            }
        }
    }

    // Видалення фандому
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_fandom'])) {
        $fandom_id = $_POST['delete_fandom'];
        $stmt = $pdo->prepare("DELETE FROM fandoms WHERE id = :id");
        $stmt->execute(['id' => $fandom_id]);
    }

    // Видалення персонажа
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_character'])) {
        $character_id = $_POST['delete_character'];
        $stmt = $pdo->prepare("DELETE FROM characters WHERE id = :id");
        $stmt->execute(['id' => $character_id]);
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
    <link rel="stylesheet" href="../css/admin_add.css">
    <link rel="stylesheet" href="../css/footer.css">
    <title>Додати фандом</title>
</head>
<body>
<div class="container">

    <?php include "menu.php" ?>

    <div class="content">

        <div class="forms">

            <?php if (isset($error)): ?>
                <p class="error-message"> <?= $error ?> </p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success-message"> <?= $success ?> </p>
            <?php endif; ?>

            <h2>Додати фандом</h2>
            <form method="POST">
                <label>Назва фандому:</label>
                <input type="text" name="fandom_name" required>
                <button type="submit" name="add_fandom">Додати фандом</button>
            </form>

            <h2>Додати персонажа</h2>
            <form method="POST">
                <label>Фандом:</label>
                <select name="fandom_id" required>
                    <option value="">Оберіть фандом</option>
                    <?php foreach ($fandoms as $fandom): ?>
                        <option value="<?= $fandom['id'] ?>"><?= htmlspecialchars($fandom['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Ім'я персонажа:</label>
                <input type="text" name="character_name" required>
                <button type="submit" name="add_character">Додати персонажа</button>
            </form>

            <h2>Фандоми</h2>
            <div class="list">
                <?php foreach ($fandoms as $fandom): ?>
                    <div class="item">
                        <span class="label"><?= htmlspecialchars($fandom['name']) ?></span>
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="delete_fandom" value="<?= $fandom['id'] ?>" class="delete-btn"
                                    onclick="return confirm('Ви дійсно хочете видалити фандом?')">❌
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2>Персонажі</h2>
            <div class="list">
                <?php foreach ($characters as $character): ?>
                    <div class="item">
                        <?= htmlspecialchars($character['name']) ?> (<?= htmlspecialchars($character['fandom_name']) ?>)
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="delete_character" value="<?= $character['id'] ?>"
                                    class="delete-btn">❌
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>


        <?php include "footer.php" ?>
    </div>

</div>

</body>
</html>
