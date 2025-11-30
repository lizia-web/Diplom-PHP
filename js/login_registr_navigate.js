function navigateTo(url) {
    window.location.href = url;
}

document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("toast");
    if (toast) {
        toast.classList.add("show");
        setTimeout(() => {
            toast.classList.remove("show");
        }, 4000);
    }
});