function setupAutocomplete(input, itemsList, storageKey) {
    if (!input) return;

    const wrapper = input.closest('.input-wrapper');
    const suggestionContainer = document.createElement('div');
    suggestionContainer.className = `${input.classList.contains('material-input') ? 'material' : 'brand'}-suggestions`;
    wrapper.appendChild(suggestionContainer);

    let selectedIndex = -1;
    let tempValue = ''; // Almacena temporalmente el valor formateado

    function filterItems(query) {
        query = query.toLowerCase();
        
        if (query.length === 1) {
            return itemsList.filter(item => 
                item.toLowerCase().startsWith(query)
            );
        }
        
        const firstChar = query.charAt(0);
        const restOfQuery = query.slice(1);
        
        return itemsList.filter(item => {
            const itemLower = item.toLowerCase();
            if (!itemLower.startsWith(firstChar)) {
                return false;
            }
            
            if (restOfQuery) {
                const restOfItem = itemLower.slice(1);
                return restOfItem.includes(restOfQuery);
            }
            
            return true;
        });
    }

    function showSuggestions(suggestions) {
        suggestionContainer.innerHTML = '';
        selectedIndex = -1;

        if (suggestions.length > 0) {
            suggestions.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = item;
                div.addEventListener('click', () => {
                    input.value = item;
                    suggestionContainer.style.display = 'none';
                });
                div.addEventListener('mouseover', () => {
                    selectedIndex = index;
                    updateSelectedSuggestion();
                });
                suggestionContainer.appendChild(div);
            });
            suggestionContainer.style.display = 'block';
        } else {
            suggestionContainer.style.display = 'none';
        }
    }

    function updateSelectedSuggestion() {
        const items = suggestionContainer.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('selected');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('selected');
            }
        });
    }

    function handleInput(e) {
        const query = e.target.value;
        if (query.length > 0) {
            const suggestions = filterItems(query);
            showSuggestions(suggestions);
        } else {
            suggestionContainer.style.display = 'none';
        }
    }

    function handleKeydown(e) {
        const items = suggestionContainer.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelectedSuggestion();
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, 0);
                updateSelectedSuggestion();
                break;
            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    input.value = items[selectedIndex].textContent;
                    suggestionContainer.style.display = 'none';
                }
                break;
            case 'Escape':
                suggestionContainer.style.display = 'none';
                break;
        }
    }

    function handleBlur() {
        setTimeout(() => {
            if (input.value) {
                const formattedValue = capitalizeWords(input.value);
                input.value = formattedValue;
                
                if (!itemsList.includes(formattedValue)) {
                    itemsList.push(formattedValue);
                    itemsList.sort();
                    try {
                        const savedItems = JSON.parse(localStorage.getItem(storageKey) || '[]');
                        if (!savedItems.includes(formattedValue)) {
                            savedItems.push(formattedValue);
                            localStorage.setItem(storageKey, JSON.stringify(savedItems));
                        }
                    } catch (e) {
                        console.error(`Error saving ${storageKey}:`, e);
                    }
                }
            }
            suggestionContainer.style.display = 'none';
        }, 200);
    }

    input.addEventListener('input', handleInput);
    input.addEventListener('keydown', handleKeydown);
    input.addEventListener('blur', handleBlur);

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !suggestionContainer.contains(e.target)) {
            suggestionContainer.style.display = 'none';
        }
    });
}

function capitalizeWords(str) {
    return str.split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
}