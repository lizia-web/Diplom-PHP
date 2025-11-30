<?php if (!isset($works)) {
    echo '<p style="color:white;">Помилка: не передано список творів.</p>';
    return;
}

global $works_in;
global $user_id;
global $current_collection_id;
global $collection;
global $is_my_works;

include "fanf_menu.php";

?>

<link rel="stylesheet" href="../css/fanfics.css">

<div id="collectionModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Оберіть збірку:</h3>
        <div id="collection-list"></div>

        <hr>

        <h4>Нова збірка:</h4>
        <label>Назва нової збірки:</label>
        <input type="text" id="newCollectionName">
        <button onclick="createAndAddCollection()">Створити і додати</button>
    </div>
</div>

<div class="fanfics">
    <div class="title">
        <p><?= $block_title ?? 'Список творів' ?></p>
    </div>

    <?php if (empty($works)): ?>
        <p class="without">Немає творів для відображення.</p>
    <?php else: ?>
        <?php foreach ($works as $work): ?>
            <?php if ((!isset($work['draft']) || $work['draft'] == 0) && (!$is_my_works)) continue; ?>

            <div class="fanfic">

                <?php
                global $dsn;
                global $db_username;
                global $db_password;
                global $author;

                $author_id = $work['user_id'];
                $likes_map = [];
                $user_likes = [];
                try {
                    $pdo = new PDO($dsn, $db_username, $db_password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);

                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                    $stmt->execute(['id' => $author_id]);
                    $author = $stmt->fetch();

                    // Отримати імена персонажів твору
                    $character_names = [];
                    $stmt = $pdo->prepare("
    SELECT c.name 
    FROM characters c 
    INNER JOIN work_characters wc ON c.id = wc.character_id 
    WHERE wc.work_id = ?
");
                    $stmt->execute([$work['id']]);
                    $character_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $characters_str = $character_names ? implode(', ', $character_names) : '-';


                    $work_ids = array_column($works, 'id');
                    $placeholders = implode(',', array_fill(0, count($work_ids), '?'));

                    // Кількість лайків
                    $stmt = $pdo->prepare("SELECT work_id, COUNT(*) as count FROM likes WHERE work_id IN ($placeholders) GROUP BY work_id");
                    $stmt->execute($work_ids);
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $likes_map[$row['work_id']] = $row['count'];
                    }

                    // Чи користувач лайкнув
                    if ($user_id) {
                        $stmt = $pdo->prepare("SELECT work_id FROM likes WHERE user_id = ? AND work_id IN ($placeholders)");
                        $stmt->execute(array_merge([$user_id], $work_ids));
                        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                            $user_likes[$row['work_id']] = true;
                        }
                    }

                } catch (PDOException $e) {
                    echo "Помилка бази даних: " . $e->getMessage();
                    exit;
                }
                ?>
                <div class="text">
                    <p class="title-fanfic">
                        <a class="title-fanfic" href="read.php?id=<?= $work['id'] ?>">
                            <?= htmlspecialchars($work['title']) ?>
                        </a>
                    </p>
                    <?php
                    $like_count = $likes_map[$work['id']] ?? 0;
                    $user_liked = isset($user_likes[$work['id']]);
                    ?>
                    <div class="like-container">
                        <button class="like-button" onclick="toggleLike(this, <?= $work['id'] ?>)"
                                data-liked="<?= $user_liked ? '1' : '0' ?>">
                            <span class="heart"><?= $user_liked ? '❤️' : '♡' ?></span>
                            <span class="like-count"><?= $like_count ?></span>
                        </button>
                    </div>

                    <p><a style="color: #ff7fa5">Автор:</a> <a
                                href="guest_profile.php?id=<?= $work['user_id'] ?>"><?= htmlspecialchars($author['name']) ?> </a>
                    </p>
                    <p><a style="color: #ff7fa5">Фандом:</a> <?= htmlspecialchars($work['fandom']) ?></p>
                    <p><a style="color: #ff7fa5">Пейринг:</a> <?= htmlspecialchars($work['pairing']) ?></p>
                    <p><a style="color: #ff7fa5">Персонажі:</a> <?= htmlspecialchars($characters_str) ?></p>
                    <p><a style="color: #ff7fa5">Напрямок:</a> <?= htmlspecialchars($work['direction']) ?></p>
                    <p><a style="color: #ff7fa5">Жанр:</a> <?= htmlspecialchars($work['genre']) ?></p>
                    <p><a style="color: #ff7fa5">Опис:</a> <?= htmlspecialchars($work['description']) ?></p>
                    <p><a style="color: #ff7fa5">Дата
                            публікації:</a> <?= date("d.m.Y", strtotime($work['created_at'])) ?></p>
                </div>

                <div class="menu-container">
                    <button class="menu-button" onclick="toggleMenu(this)">☰</button>
                    <div class="dropdown-menu">
                        <div class="menu-item1" onclick="addToCollection(<?= $work['id'] ?>)">Додати до збірки</div>

                        <?php
                        $isRead = in_array($work['id'], $works_in['прочитано']);
                        $isFav = in_array($work['id'], $works_in['улюблене']);
                        ?>

                        <div class="menu-item1"
                             onclick="<?= $isRead
                                 ? "removeFromNamedCollection({$work['id']}, 'Прочитано')"
                                 : "addToNamedCollection({$work['id']}, 'Прочитано')" ?>">
                            <?= $isRead ? "Видалити з Прочитаного" : "Прочитано" ?>
                        </div>

                        <div class="menu-item1"
                             onclick="<?= $isFav
                                 ? "removeFromNamedCollection({$work['id']}, 'Улюблене')"
                                 : "addToNamedCollection({$work['id']}, 'Улюблене')" ?>">
                            <?= $isFav ? "Видалити з Улюбленого" : "Улюблене" ?>
                        </div>
                    </div>
                </div>
                <?php if (isset($show_remove_button) && $show_remove_button && isset($current_collection_name)): ?>
                    <div class="remove-from-collection">
                        <button onclick="removeFromNamedCollection(<?= $work['id'] ?>, '<?= htmlspecialchars($current_collection_name, ENT_QUOTES) ?>')">
                            Видалити зі збірки
                        </button>
                    </div>


                <?php endif; ?>
                <?php
                // Якщо користувач є власником твору, показати кнопки
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $work['user_id']):
                    if (isset($is_my_works)):
                        ?>
                        <div class="owner-actions" style="margin-top: 10px;">
                            <form method="post" action="delete_work.php" id="delete-form-<?= $work['id'] ?>"
                                  style="display:inline;">
                                <input type="hidden" name="work_id" value="<?= $work['id'] ?>">
                                <button type="button" class="delete-button" onclick="confirmDelete(<?= $work['id'] ?>)">
                                    Видалити твір
                                </button>
                            </form>

                            <a href="update_work.php?id=<?= $work['id'] ?>">
                                <button class="edit-button">Редагувати твір</button>
                            </a>

                            <a href="change_header_work.php?id=<?= $work['id'] ?>">
                                <button class="edit-button">Редагувати шапку твору</button>
                            </a>

                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>


        <?php endforeach; ?>
    <?php endif; ?>


</div>

<script src="../js/fanfics.js"></script>
