<?php
$dsn = "pgsql:host=localhost;dbname=postgres;port=5432";
$db_username = "postgres";
$db_password = "1131";

include "session.php";
global $user_id;
$fav_collection_id = null;

if ($user_id) {
    try {
        $pdo = new PDO($dsn, $db_username, $db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $pdo->prepare("SELECT id FROM collections WHERE user_id = :user_id AND name = 'Улюблене' LIMIT 1");
        $stmt->execute(['user_id' => $user_id]);
        $fav_collection_id = $stmt->fetchColumn();

    } catch (PDOException $e) {

    }
}
?>

<link rel="stylesheet" href="../css/menu.css">


<div id="searchModal" class="search-modal">
    <div class="search-modal-content">
        <input type="text" id="searchInput" placeholder="Введіть пошуковий запит...">
        <div id="searchResults" class="search-results"></div>
    </div>
</div>


<div class="menu">
    <div class=" menu-item">
        <img id="search" class="search" src="../img/search.png">
    </div>
    <div class="menu1 menu-item" onclick="navigateTo('main.php')">
        Головна
    </div>

    <?php if ($_SESSION['user_type'] === 'user'): ?>
        <div class="menu1 menu-item">Профіль
            <div class="submenu">
                <div class="menu1 menu-item" onclick="navigateTo('account.php')">Мій профіль</div>
                <div class="menu1 menu-item" onclick="navigateTo('logout.php')">Вийти</div>
            </div>

        </div>

    <?php elseif ($_SESSION['user_type'] === 'admin'): ?>
        <!-- Якщо користувач адміністратор -->
        <div class="menu1 menu-item">Адміністраторка
            <div class="submenu">
                <div class="submenu-item" onclick="navigateTo('admin_add.php')">Додати фандом</div>
                <div class="submenu-item" onclick="navigateTo('admin_change.php')">Редагувати фандом</div>
                <div class="submenu-item" onclick="navigateTo('users.php')">Користувачі</div>
                <div class="menu1 menu-item" onclick="navigateTo('logout.php')">Вийти</div>

            </div>

        </div>

    <?php else: ?>
        <div class="menu1 menu-item" onclick="navigateTo('login_form.php')">Авторизація</div>
    <?php endif; ?>


    <div class="menu1 menu-item">Читачка
        <div class="submenu">
            <div class="submenu-item"
                 onclick="<?= $fav_collection_id
                     ? "navigateTo('collection_works.php?collection_id=$fav_collection_id')"
                     : "alert('Збірка не знайдена')" ?>">
                Улюблені твори
            </div>

            <div class="submenu-item" onclick="navigateTo('favourite_authors.php')">Улюблені авторки</a></div>
            <div class="submenu-item" onclick="navigateTo('my_collections.php')">Мої збірки</a></div>
        </div>
    </div>

    <div class="menu1 menu-item">
        Письменниця
        <div class="submenu">
            <div class="submenu-item" onclick="navigateTo('my_works.php')">Мої твори</div>
            <div class="submenu-item" onclick="navigateTo('create_work.php')">Створити твір</a></div>
        </div>
    </div>
    <div class="menu1 menu-item" onclick="navigateTo('help.php')">Допомога</div>
</div>
<script src="../js/menu.js"></script>


