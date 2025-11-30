function confirmDelete(collectionId) {
    if (confirm("Ви дійсно хочете видалити цю збірку разом з усіма роботами в ній?")) {
        fetch("delete_collection.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "collection_id=" + encodeURIComponent(collectionId)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Збірку видалено.");
                    location.reload();
                } else {
                    alert("Помилка: " + data.error);
                }
            })
            .catch(err => {
                alert("Сталася помилка.");
                console.error(err);
            });
    }
}