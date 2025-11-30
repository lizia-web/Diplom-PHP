let selectedCharacters = [];
let selectedPairings = [];
let pairingBuffer = null;

function addCharacter() {
    const select = document.getElementById('character-select');
    const value = select.value;
    const name = select.options[select.selectedIndex].text;

    if (value && !selectedCharacters.includes(value)) {
        selectedCharacters.push(value);
        updateCharacterList();
    }
}

function updateCharacterList() {
    const list = document.getElementById('character-list');
    const hidden = document.getElementById('characters-hidden');
    list.innerHTML = '';
    hidden.value = selectedCharacters.join(',');

    selectedCharacters.forEach(id => {
        const char = allCharacters.find(c => c.id == id);
        if (char) {
            list.style.marginTop = '5px';
            list.style.marginBottom = '30px';
            list.style.padding = '5px';
            list.style.backgroundColor = '#333';
            const item = document.createElement('div');
            item.textContent = char.name;
            list.appendChild(item);
        }
    });
}

function addPairing() {
    const select = document.getElementById('pairing-select');
    const value = select.value;
    const name = select.options[select.selectedIndex].text;

    if (!value) return;

    const list = document.getElementById('pairing-list');
    const hidden = document.getElementById('pairing-hidden');

    // Вибір першого персонажа
    if (!pairingBuffer) {
        pairingBuffer = { id: value, name: name };

        const preview = document.createElement('div');
        preview.id = 'pairing-preview';
        preview.textContent = `${name}/`;
        list.style.marginTop = '5px';
        list.style.marginBottom = '30px';
        list.style.padding = '5px';
        list.style.backgroundColor = '#333';
        list.appendChild(preview);
        return;
    }

    // Завершення пари
    const first = pairingBuffer;
    const second = { id: value, name: name };
    const newId = `${first.id}/${second.id}`;
    const newLabel = `${first.name}/${second.name}`;

    // Перевірка на дублікати
    const isDuplicate = selectedPairings.some(p => p.id === newId);
    if (isDuplicate) {
        alert('Цей пейринг вже додано.');
        pairingBuffer = null;
        document.getElementById('pairing-preview')?.remove();
        return;
    }

    selectedPairings.push({ id: newId, label: newLabel });

    pairingBuffer = null;
    updatePairingList();
}

function updatePairingList() {
    const list = document.getElementById('pairing-list');
    const hidden = document.getElementById('pairing-hidden');
    list.innerHTML = '';
    hidden.value = selectedPairings.map(p => p.id).join(',');

    selectedPairings.forEach(p => {
        const item = document.createElement('div');
        item.textContent = p.label;
        list.appendChild(item);
    });

    // Якщо є незавершений пейринг — показати його знову
    if (pairingBuffer) {
        const preview = document.createElement('div');
        preview.id = 'pairing-preview';
        preview.textContent = `${pairingBuffer.name}/`;
        list.appendChild(preview);
    }
}



function filterCharactersByFandom() {
    const fandomId = document.getElementById('fandom-select').value;
    const charSelect = document.getElementById('character-select');
    const pairSelect = document.getElementById('pairing-select');

    charSelect.innerHTML = '<option value="">Оберіть персонажа</option>';
    pairSelect.innerHTML = '<option value="">Оберіть персонажа</option>';

    allCharacters
        .filter(c => c.fandom_id == fandomId)
        .forEach(c => {
            const option1 = new Option(c.name, c.id);
            const option2 = new Option(c.name, c.id);
            charSelect.add(option1);
            pairSelect.add(option2);
        });
}
