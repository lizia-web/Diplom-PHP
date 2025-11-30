document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("taste-form");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch("search_by_taste.php", {
            method: "POST",
            body: formData
        })
            .then(resp => resp.text())
            .then(html => {
                document.getElementById("search-results").innerHTML = html;
            })
            .catch(error => {
                console.error("Помилка при пошуку:", error);
            });
    });
});
