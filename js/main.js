document.querySelectorAll('.column img').forEach(img => {
    const altText = img.getAttribute('alt');
    if (altText) {
        const altElement = document.createElement('p');
        altElement.textContent = altText;
        altElement.className = 'image-alt';
        img.parentNode.appendChild(altElement);
    }
});


