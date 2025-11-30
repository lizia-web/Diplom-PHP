<?php include "session.php";

?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/fanfics.css">
    <title>Головна</title>
</head>
<body>
<div class="container">
    <?php include "menu.php" ?>


    <div class="content">
        <div class="title">
            <p>Фанфіки за твоїми улюбленими творами, фільмами, аніме, іграми, коміксами</p>
        </div>

        <div class="content-columns">

            <?php
            $dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
            $db_username = "postgres";
            $db_password = "1131";

            $fandom_images = [
                'Гаррі Поттер' => 'harrypotter.jpg',
                'Місто кісток' => 'cityofbones.jpg',
                'Наруто' => 'naruto.jpg',
                'Тор' => 'thor.jpg',
                'Коти Вояки' => 'Koti-voyaki.jpg',
                'Сутінки' => 'twilight.jpg',
                'Мавка. Лісова пісня' => 'mavka.png',
                'Оverwatch' => 'overwatch.jpg',
                'Мортал Комбат' => 'mortal_combat.jpg',
                'Моя провина' => 'moja-provina.jpg',
            ];

            try {
                $pdo = new PDO($dsn, $db_username, $db_password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                foreach ($fandom_images as $name => $img) {
                    $stmt = $pdo->prepare("SELECT id FROM fandoms WHERE name = :name");
                    $stmt->execute(['name' => $name]);
                    $id = $stmt->fetchColumn();

                    if ($id) {
                        echo <<<HTML
            <div class="column" onclick="navigateTo('details.php?table=fandoms&id=$id')">
                <img src="../img/$img" alt="$name" title="$name">
            </div>
HTML;
                    } else {
                        echo "<!-- Не знайдено фандом: $name -->";
                    }
                }

            } catch (PDOException $e) {
                echo "Помилка бази даних: " . $e->getMessage();
            }
            ?>


        </div>


        <?php
        global $user_id;

        // Підключення до БД
        $dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
        $db_username = "postgres";
        $db_password = "1131";

        try {
            $pdo = new PDO($dsn, $db_username, $db_password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $stmt = $pdo->query("SELECT * FROM works WHERE draft = 1 ORDER BY id DESC LIMIT 3");

            $works = $stmt->fetchAll();

//    $works = array_slice($all_works, 0, 3);

            include "fanf_menu.php";


        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
            exit;
        }
        global $works;
        global $works_in;
        $block_title = "Нові історії на сайті";
        include "fanfics.php"
        ?>


        <?php include "footer.php" ?>
    </div>
</div>

<script src="../js/main.js"></script>

</body>
</html>
