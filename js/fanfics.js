function toggleMenu(button) {
    const menu = button.nextElementSibling;
    const isVisible = menu.style.display === "block";
    document.querySelectorAll('.dropdown-menu').forEach((menu) => menu.style.display = 'none'); // Закриваємо інші меню
    menu.style.display = isVisible ? "none" : "block"; // Відкриваємо/закриваємо поточне меню
}


// Закриваємо меню при кліку за межами
document.addEventListener('click', (event) => {
    if (!event.target.closest('.menu-container')) {
        document.querySelectorAll('.dropdown-menu').forEach((menu) => (menu.style.display = 'none'));
    }
});


function addToNamedCollection(workId, collectionName) {
    const params = new URLSearchParams();
    params.append("work_id", workId);
    params.append("collection_name", collectionName);

    fetch("add_to_collection.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: params
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Додано до «" + collectionName + "»!");
                updateMenu(workId, collectionName, true); // оновлюємо меню
            } else {
                alert("Помилка: " + data.error);
            }
        })
        .catch(err => {
            alert("Запит не вдалось виконати");
            console.error(err);
        });
}


function removeFromNamedCollection(workId, collectionName) {
    if (!confirm(`Видалити роботу зі збірки "${collectionName}"?`)) return;

    const params = new URLSearchParams();
    params.append("work_id", workId);
    params.append("collection_name", collectionName);

    fetch("remove_from_collection.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: params
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Видалено зі «" + collectionName + "»!");
                // updateMenu(workId, collectionName, false); // оновлюємо меню
                location.reload();
            } else {
                alert("Помилка: " + data.error);
            }
        })
        .catch(err => {
            alert("Помилка запиту");
            console.error(err);
        });
}


function updateMenu(workId, collectionName, added) {
    document.querySelectorAll('.fanfic').forEach(fanfic => {
        if (fanfic.innerHTML.includes(`addToNamedCollection(${workId}, '${collectionName}')`) ||
            fanfic.innerHTML.includes(`removeFromNamedCollection(${workId}, '${collectionName}')`)) {

            const items = fanfic.querySelectorAll('.menu-item1');
            items.forEach(item => {
                if (item.textContent.includes(collectionName)) {
                    item.textContent = added
                        ? `Видалити з ${collectionName}`
                        : `${collectionName}`;
                    item.setAttribute('onclick',
                        added
                            ? `removeFromNamedCollection(${workId}, '${collectionName}')`
                            : `addToNamedCollection(${workId}, '${collectionName}')`
                    );
                }
            });
        }
    });
}







let currentWorkId = null;

function addToCollection(workId) {
    currentWorkId = workId;
    fetch('get_collections.php')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('collection-list');
            list.innerHTML = '';

            if (data.length === 0) {
                list.innerHTML = '<p>Немає збірок. Створіть нову нижче.</p>';
            } else {
                data.forEach(col => {
                    const btn = document.createElement('button');
                    btn.textContent = col.name;
                    btn.onclick = () => addToExistingCollection(col.id);
                    list.appendChild(btn);
                    list.appendChild(document.createElement('br'));
                });
            }

            document.getElementById('collectionModal').style.display = 'flex';
        });
}

function closeModal() {
    document.getElementById('collectionModal').style.display = 'none';
    currentWorkId = null;
}

function addToExistingCollection(collectionId) {
    fetch('add_to_collection_by_id.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'work_id=' + encodeURIComponent(currentWorkId) + '&collection_id=' + encodeURIComponent(collectionId)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Додано до збірки');
                closeModal();
            } else {
                alert('Помилка: ' + data.error);
            }
        });
}


function createAndAddCollection() {
    const name = document.getElementById('newCollectionName').value.trim();
    if (!name) {
        alert('Введіть назву збірки');
        return;
    }

    fetch('create_collection.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'name=' + encodeURIComponent(name)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.collection_id) {
                addToExistingCollection(data.collection_id);
                // location.reload();
            } else {
                alert('Помилка: ' + data.error);
            }
        });
}


function openCollectionModal(workId, collections, inCollections) {
    const modal = document.getElementById('collectionModal');
    const list = document.getElementById('collection-list');
    list.innerHTML = '';

    collections.forEach(col => {
        const name = col.name;
        const nameLower = name.toLowerCase();
        const div = document.createElement('div');
        div.className = 'collection-item';

        const nameSpan = document.createElement('span');
        nameSpan.className = 'collection-name';
        nameSpan.textContent = name;
        nameSpan.onclick = () => addToNamedCollection(workId, name);
        div.appendChild(nameSpan);

        if (inCollections.includes(nameLower) && !['прочитано', 'улюблене'].includes(nameLower)) {
            const removeBtn = document.createElement('span');
            removeBtn.className = 'remove-btn';
            removeBtn.textContent = '✖';
            removeBtn.onclick = () => {
                if (confirm(`Видалити роботу зі збірки "${name}"?`)) {
                    removeFromNamedCollection(workId, name);
                    modal.style.display = "none";
                }
            };
            div.appendChild(removeBtn);
        }

        list.appendChild(div);
    });

    modal.style.display = 'block';
}

function confirmDelete(workId) {
    if (confirm('Ви дійсно хочете остаточно видалити цей твір? Цю дію неможливо скасувати.')) {
        document.getElementById('delete-form-' + workId).submit();
    }
}

function toggleLike(button, workId) {
    const liked = button.dataset.liked === '1';

    fetch('toggle_like.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ work_id: workId, action: liked ? 'unlike' : 'like' })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                button.dataset.liked = liked ? '0' : '1';
                button.querySelector('.heart').textContent = liked ? '♡' : '❤️';
                button.querySelector('.like-count').textContent = data.new_count;
            }
        })
        .catch(console.error);
}


