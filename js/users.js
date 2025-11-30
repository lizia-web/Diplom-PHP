function updateUserRole(userId, newRole) {
    fetch('update_user_role.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'user_id=' + encodeURIComponent(userId) + '&user_type=' + encodeURIComponent(newRole)
    })
        .then(response => response.text())
        .then(data => {
            if (data !== 'OK') {
                alert('Помилка: ' + data);
            }
            else {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Помилка запиту:', error);
        });
}