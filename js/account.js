document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('photo-input').addEventListener('change', function () {
        uploadImage('photo-input', 'profile');
    });
    document.getElementById('background-input').addEventListener('change', function () {
        uploadImage('background-input', 'cover');
    });
});

function uploadImage(inputId, type) {

    console.log("uploadImage called for", type); // 🔍 DEBUG

    const input = document.getElementById(inputId);
    const file = input.files[0];

    if (!file) return;

    const formData = new FormData();
    formData.append("image", file);
    formData.append("type", type);


    fetch("upload_photo.php", {
        method: "POST",
        body: formData

    })

        .then(res => res.json())
        .then(data => {

            console.log("response:", data); // 🔍 DEBUG
            if (data.success && data.image) {
                if (type === "profile") {
                    document.getElementById("profile-img").src = data.image;

                } else {
                    document.getElementById("background-img").src = data.image;

                }
            } else {
                alert("Помилка: " + (data.error || "невідомо"));

            }
        })
        .catch(err => alert("Помилка запиту: " + err));
}




function toggleAboutEdit() {
    document.getElementById('about-display').style.display = 'none';
    document.getElementById('about-form').style.display = 'block';
}

function cancelAboutEdit() {
    document.getElementById('about-form').style.display = 'none';
    document.getElementById('about-display').style.display = 'block';
}