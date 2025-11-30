// Відкриття пошуку
const searchIcon = document.getElementById("search");
const searchModal = document.getElementById("searchModal");
const searchInput = document.getElementById("searchInput");
const searchResults = document.getElementById("searchResults");

searchIcon.addEventListener("click", () => {
searchModal.style.display = "flex";
searchInput.focus();
});

// Закриття при кліку поза вікном або натисканні Esc
window.addEventListener("click", (event) => {
if (event.target === searchModal) {
searchModal.style.display = "none";
}
});
window.addEventListener("keydown", (event) => {
if (event.key === "Escape") {
searchModal.style.display = "none";
}
});

// Пошук (імітація AJAX-запиту)
searchInput.addEventListener("input", () => {
    let query = searchInput.value.trim();
    searchResults.innerHTML = "";

    if (query.length > 2) {
        fetch("search.php?q=" + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                searchResults.innerHTML = data;
            })
            .catch(error => {
                searchResults.innerHTML = "<div>Помилка пошуку.</div>";
            });
    }
});
function navigateTo(url) {
    window.location.href = url;
}

