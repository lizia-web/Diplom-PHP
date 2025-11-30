<?php include "session.php"?>

<?php
$faq = [
    "Як створити нову роботу?" => "Перейдіть до свого профілю та натисніть кнопку 'Створити твір'. Заповніть усі необхідні поля та натисніть 'Опублікувати'.",
    "Як змінити фото профілю?" => "На сторінці профілю натисніть на зображення профілю, виберіть нове фото, і воно автоматично оновиться.",
    "Як додати твір до збірки?" => "У меню твору натисніть на кнопку 'Додати до збірки' і оберіть або створіть потрібну збірку.",
    "Чому мій твір не видно на головній сторінці?" => "Можливо, твір ще не пройшов модерацію або був збережений як чернетка. Перевірте статус у профілі.",
];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/help.css">
    <link rel="stylesheet" href="../css/footer.css">
    <title>Допомога</title>
</head>
<body>

<div class="container">

    <?php include "menu.php"?>

    <div class="content">

        <div class="faq-section">
            <h2 style="color:white; text-align:center;">Поширені запитання</h2>

            <?php foreach ($faq as $question => $answer): ?>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <?= htmlspecialchars($question) ?>
                    </div>
                    <div class="faq-answer">
                        <?= htmlspecialchars($answer) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


        <?php include "footer.php"?>

    </div>


</div>
<script src="../js/help.js"></script>
</body>
</html>
