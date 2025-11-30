
// файл update_work.js
// function format(command) {
//     document.execCommand(command, false, null);
// }

function format(command) {
    document.execCommand(command, false, null);
}

document.getElementById('editForm').addEventListener('submit', function (e) {
    const editor = document.getElementById('editor');
    const contentField = document.getElementById('content');
    contentField.value = editor.innerHTML;
});

// function beforeSubmit() {
//     const editor = document.getElementById('editor');
//     const contentField = document.getElementById('content');
//     contentField.value = editor.innerHTML;
// }


function beforeSubmit() {
    const editor = document.getElementById('editor');
    const textarea = document.getElementById('content');

    // Очистити від зайвих div'ів і зробити простішу структуру
    const cleanedHTML = editor.innerHTML
        .replace(/<div>(.*?)<\/div>/gi, '<p>$1</p>')  // div -> p
        .replace(/<div style="text-align: (.*?)">(.*?)<\/div>/gi, '<p style="text-align: $1">$2</p>')
        .replace(/<p><\/p>/gi, ''); // прибрати порожні параграфи

    textarea.value = cleanedHTML;
}


