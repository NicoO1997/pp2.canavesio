
// Lista predefinida de materiales
let materialsList = [
    'Acero inoxidable',
    'Hierro fundido',
    'Caucho',
    'Plástico reforzado',
    'Aluminio',
    'Bronce',
    'Acero al carbono',
    'Polietileno',
    'PVC',
    'Titanio',
    'Cobre',
    'Latón',
    'Cerámica',
    'Fibra de carbono',
    'Poliuretano'
];

// Intentar cargar materiales personalizados del localStorage
try {
    const savedMaterials = localStorage.getItem('customMaterials');
    if (savedMaterials) {
        const parsedMaterials = JSON.parse(savedMaterials);
        parsedMaterials.forEach(material => {
            if (!materialsList.includes(material)) {
                materialsList.push(material);
            }
        });
        materialsList.sort();
    }
} catch (e) {
    console.error('Error loading saved materials:', e);
}

// Configuración del campo de material
const materialInput = document.querySelector('.material-input');
if (materialInput) {
    const materialInputWrapper = materialInput.closest('.input-wrapper');
    const materialSuggestionContainer = document.createElement('div');
    materialSuggestionContainer.className = 'material-suggestions';
    materialInputWrapper.appendChild(materialSuggestionContainer);

    let selectedMaterialIndex = -1;

    function capitalizeWords(str) {
        return str.split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
        }
    function filterMaterials(query) {
        query = query.toLowerCase();
        
        // Si es la primera letra, filtrar solo por coincidencia exacta al inicio
        if (query.length === 1) {
            return materialsList.filter(material => 
                material.toLowerCase().startsWith(query)
            );
        }
        
        // Si hay más de una letra, primero filtrar por la primera letra
        // y luego por coincidencia parcial en el resto del texto
        const firstChar = query.charAt(0);
        const restOfQuery = query.slice(1);
        
        return materialsList.filter(material => {
            const materialLower = material.toLowerCase();
            if (!materialLower.startsWith(firstChar)) {
                return false;
            }
            
            if (restOfQuery) {
                const restOfMaterial = materialLower.slice(1);
                return restOfMaterial.includes(restOfQuery);
            }
            
            return true;
        });
    }

    function showMaterialSuggestions(suggestions) {
        materialSuggestionContainer.innerHTML = '';
        selectedMaterialIndex = -1;

        if (suggestions.length > 0) {
            suggestions.forEach((material, index) => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = material;
                div.addEventListener('click', () => {
                    materialInput.value = material;
                    materialSuggestionContainer.style.display = 'none';
                });
                div.addEventListener('mouseover', () => {
                    selectedMaterialIndex = index;
                    updateSelectedMaterialSuggestion();
                });
                materialSuggestionContainer.appendChild(div);
            });
            materialSuggestionContainer.style.display = 'block';
        } else {
            materialSuggestionContainer.style.display = 'none';
        }
    }

    function updateSelectedMaterialSuggestion() {
        const items = materialSuggestionContainer.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            if (index === selectedMaterialIndex) {
                item.classList.add('selected');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('selected');
            }
        });
    }

    materialInput.addEventListener('input', function(e) {
        const query = e.target.value;
        if (query.length > 0) {
            const suggestions = filterMaterials(query);
            showMaterialSuggestions(suggestions);
        } else {
            materialSuggestionContainer.style.display = 'none';
        }
    });

    materialInput.addEventListener('keydown', function(e) {
        const items = materialSuggestionContainer.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedMaterialIndex = Math.min(selectedMaterialIndex + 1, items.length - 1);
                updateSelectedMaterialSuggestion();
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectedMaterialIndex = Math.max(selectedMaterialIndex - 1, 0);
                updateSelectedMaterialSuggestion();
                break;
            case 'Enter':
                e.preventDefault();
                if (selectedMaterialIndex >= 0 && items[selectedMaterialIndex]) {
                    materialInput.value = items[selectedMaterialIndex].textContent;
                    materialSuggestionContainer.style.display = 'none';
                }
                break;
            case 'Escape':
                materialSuggestionContainer.style.display = 'none';
                break;
        }
    });

    materialInput.addEventListener('blur', function() {
        setTimeout(() => {
            if (materialInput.value) {
                const formattedMaterial = capitalizeWords(materialInput.value);
                materialInput.value = formattedMaterial;
                
                if (!materialsList.includes(formattedMaterial)) {
                    materialsList.push(formattedMaterial);
                    materialsList.sort();
                    try {
                        const savedMaterials = JSON.parse(localStorage.getItem('customMaterials') || '[]');
                        if (!savedMaterials.includes(formattedMaterial)) {
                            savedMaterials.push(formattedMaterial);
                            localStorage.setItem('customMaterials', JSON.stringify(savedMaterials));
                        }
                    } catch (e) {
                        console.error('Error saving material:', e);
                    }
                }
            }
            materialSuggestionContainer.style.display = 'none';
        }, 200);
    });

    document.addEventListener('click', function(e) {
        if (!materialInput.contains(e.target) && !materialSuggestionContainer.contains(e.target)) {
            materialSuggestionContainer.style.display = 'none';
        }
    });
}