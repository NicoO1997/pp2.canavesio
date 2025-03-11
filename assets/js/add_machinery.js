document.addEventListener('DOMContentLoaded', function() {
    const brandInput = document.querySelector('input[name="used_machinery[brand]"]');
    const brandSuggestions = document.querySelector('.brand-suggestions');
    const localityInput = document.querySelector('input[name="used_machinery[locality]"]');
    const localitySuggestions = document.querySelector('.locality-suggestions');
    const provinciaInput = document.querySelector('input[name="used_machinery[provincia]"]');
    const provinciaSuggestions = document.querySelector('.provincia-suggestions');
    const isNewRadios = document.querySelectorAll('input[name="used_machinery[isNew]"]');
    const usedFieldsContainer = document.querySelector('.used-fields-container');
    const categorySelect = document.querySelector('select[name="used_machinery[category]"]');
    const loadCapacityField = document.querySelector('.load-capacity-field');
    const taxpayerSelect = document.querySelector('select[name="used_machinery[taxpayerType]"]');
    const priceInput = document.querySelector('input[name="used_machinery[price]"]');
    const pricePreview = document.getElementById('pricePreview');
    const basePriceSpan = document.getElementById('basePrice');
    const ivaAmountSpan = document.getElementById('ivaAmount');
    const finalPriceSpan = document.getElementById('finalPrice');

    let brandSelectedIndex = -1;
    let localitySelectedIndex = -1;
    let provinciaSelectedIndex = -1;

    function capitalizeWords(str) {
        return str
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }

    function filterBrands(query) {
        query = query.toLowerCase();
        return machineryBrands.filter(brand => 
            brand.toLowerCase().startsWith(query)
        );
    }

    function showBrandSuggestions(suggestions) {
        brandSuggestions.innerHTML = '';
        brandSelectedIndex = -1;

        if (suggestions.length > 0) {
            suggestions.forEach((brand, index) => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = brand;
                div.dataset.index = index;
                div.addEventListener('click', () => {
                    brandInput.value = brand;
                    brandSuggestions.style.display = 'none';
                });
                div.addEventListener('mouseover', () => {
                    brandSelectedIndex = index;
                    updateBrandSelectedSuggestion();
                });
                brandSuggestions.appendChild(div);
            });
            brandSuggestions.style.display = 'block';
        } else {
            brandSuggestions.style.display = 'none';
        }
    }

    function updateBrandSelectedSuggestion() {
        const items = brandSuggestions.querySelectorAll('.suggestion-item');
        items.forEach(item => item.classList.remove('selected'));
        if (brandSelectedIndex >= 0 && items[brandSelectedIndex]) {
            items[brandSelectedIndex].classList.add('selected');
            items[brandSelectedIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    function filterLocalities(query) {
        query = query.toLowerCase();
        
        // Si hay una provincia seleccionada, filtrar solo localidades de esa provincia
        const selectedProvincia = provinciaInput.value;
        
        if (selectedProvincia && localitiesByProvince && localitiesByProvince[selectedProvincia]) {
            return localitiesByProvince[selectedProvincia].filter(locality => 
                locality.toLowerCase().startsWith(query)
            );
        } else {
            // Si no hay provincia seleccionada, filtrar todas las localidades
            return locationsList.filter(locality => 
                locality.toLowerCase().startsWith(query)
            );
        }
    }

    function showLocalitySuggestions(suggestions) {
        localitySuggestions.innerHTML = '';
        localitySelectedIndex = -1;

        if (suggestions.length > 0) {
            suggestions.forEach((locality, index) => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = locality;
                div.dataset.index = index;
                div.addEventListener('click', () => {
                    localityInput.value = locality;
                    localitySuggestions.style.display = 'none';
                });
                div.addEventListener('mouseover', () => {
                    localitySelectedIndex = index;
                    updateLocalitySelectedSuggestion();
                });
                localitySuggestions.appendChild(div);
            });
            localitySuggestions.style.display = 'block';
        } else {
            localitySuggestions.style.display = 'none';
        }
    }

    function updateLocalitySelectedSuggestion() {
        const items = localitySuggestions.querySelectorAll('.suggestion-item');
        items.forEach(item => item.classList.remove('selected'));
        if (localitySelectedIndex >= 0 && items[localitySelectedIndex]) {
            items[localitySelectedIndex].classList.add('selected');
            items[localitySelectedIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }
    
    function filterProvincias(query) {
        query = query.toLowerCase();
        return provinciasList.filter(provincia => 
            provincia.toLowerCase().startsWith(query)
        );
    }

    function showProvinciaSuggestions(suggestions) {
        provinciaSuggestions.innerHTML = '';
        provinciaSelectedIndex = -1;

        if (suggestions.length > 0) {
            suggestions.forEach((provincia, index) => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = provincia;
                div.dataset.index = index;
                div.addEventListener('click', () => {
                    provinciaInput.value = provincia;
                    provinciaSuggestions.style.display = 'none';
                    
                    // Al seleccionar una provincia, limpiar el campo de localidad
                    localityInput.value = '';
                    
                    // Mostrar las localidades de la provincia seleccionada
                    if (localitiesByProvince && localitiesByProvince[provincia]) {
                        // Enfocar en el campo de localidad
                        setTimeout(() => {
                            localityInput.focus();
                            // Mostrar todas las localidades de esta provincia como sugerencias
                            showLocalitySuggestions(localitiesByProvince[provincia]);
                        }, 100);
                    }
                });
                div.addEventListener('mouseover', () => {
                    provinciaSelectedIndex = index;
                    updateProvinciaSelectedSuggestion();
                });
                provinciaSuggestions.appendChild(div);
            });
            provinciaSuggestions.style.display = 'block';
        } else {
            provinciaSuggestions.style.display = 'none';
        }
    }

    function updateProvinciaSelectedSuggestion() {
        const items = provinciaSuggestions.querySelectorAll('.suggestion-item');
        items.forEach(item => item.classList.remove('selected'));
        if (provinciaSelectedIndex >= 0 && items[provinciaSelectedIndex]) {
            items[provinciaSelectedIndex].classList.add('selected');
            items[provinciaSelectedIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    function calculatePrices() {
        if (priceInput.value) {
            const basePrice = parseFloat(priceInput.value);
            let ivaRate;
            let ivaText;
            
            switch(taxpayerSelect.value) {
                case 'responsable_inscripto':
                    ivaRate = 0.21;
                    ivaText = '21%';
                    break;
                case 'consumidor_final':
                    ivaRate = 0.10;
                    ivaText = '10%';
                    break;
                case 'monotributista':
                case 'exento':
                    ivaRate = 0;
                    ivaText = '0%';
                    break;
                default:
                    ivaRate = 0;
                    ivaText = '0%';
            }

            const ivaAmount = basePrice * ivaRate;
            const finalPrice = basePrice + ivaAmount;

            pricePreview.style.display = 'block';
            basePriceSpan.textContent = `$${basePrice.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            ivaAmountSpan.textContent = `$${ivaAmount.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (${ivaText})`;
            finalPriceSpan.textContent = `$${finalPrice.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else {
            pricePreview.style.display = 'none';
        }
    }

    function toggleLoadCapacityField() {
        const category = categorySelect.value;
        const loadCapacityInput = loadCapacityField.querySelector('input');
        if (['sembradora', 'pulverizadora', 'tolva'].includes(category)) {
            loadCapacityField.style.display = 'block';
            loadCapacityInput.removeAttribute('required');
        } else {
            loadCapacityField.style.display = 'none';
            if (loadCapacityInput) {
                loadCapacityInput.value = '';
            }
        }
    }

    function toggleFields() {
        const selectedRadio = document.querySelector('input[name="used_machinery[isNew]"]:checked');
        if (selectedRadio) {
            const isNew = selectedRadio.value === '1';
            
            usedFieldsContainer.style.display = isNew ? 'none' : 'grid';
            usedFieldsContainer.style.opacity = isNew ? '0' : '1';
            
            const usedFields = usedFieldsContainer.querySelectorAll('input, select');
            usedFields.forEach(field => {
                if (isNew) {
                    field.removeAttribute('required');
                    if (field.type === 'datetime-local' || field.type === 'date') {
                        field.value = '';
                    } else if (field.type === 'number') {
                        field.value = '';
                    }
                } else {
                    if (field.hasAttribute('data-required')) {
                        field.setAttribute('required', 'required');
                    }
                }
            });
        }
        toggleLoadCapacityField();
    }

    brandInput.addEventListener('input', function(e) {
        const query = e.target.value;
        if (query.length > 0) {
            const suggestions = filterBrands(query);
            showBrandSuggestions(suggestions);
        } else {
            brandSuggestions.style.display = 'none';
        }
    });

    brandInput.addEventListener('keydown', function(e) {
        const items = brandSuggestions.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                brandSelectedIndex = Math.min(brandSelectedIndex + 1, items.length - 1);
                updateBrandSelectedSuggestion();
                break;
            case 'ArrowUp':
                e.preventDefault();
                brandSelectedIndex = Math.max(brandSelectedIndex - 1, 0);
                updateBrandSelectedSuggestion();
                break;
            case 'Enter':
                e.preventDefault();
                if (brandSelectedIndex >= 0 && items[brandSelectedIndex]) {
                    brandInput.value = items[brandSelectedIndex].textContent;
                    brandSuggestions.style.display = 'none';
                }
                break;
            case 'Escape':
                brandSuggestions.style.display = 'none';
                break;
        }
    });

    brandInput.addEventListener('blur', function() {
        setTimeout(() => {
            if (brandInput.value) {
                const formattedBrand = capitalizeWords(brandInput.value);
                brandInput.value = formattedBrand;
                
                if (!machineryBrands.includes(formattedBrand)) {
                    machineryBrands.push(formattedBrand);
                    machineryBrands.sort();
                    localStorage.setItem('machineryBrands', JSON.stringify(machineryBrands));
                }
            }
        }, 200);
    });

    // Eventos para el campo de localidad
    localityInput.addEventListener('input', function(e) {
        const query = e.target.value;
        if (query.length > 0) {
            const suggestions = filterLocalities(query);
            showLocalitySuggestions(suggestions);
        } else {
            localitySuggestions.style.display = 'none';
        }
    });

    localityInput.addEventListener('keydown', function(e) {
        const items = localitySuggestions.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                localitySelectedIndex = Math.min(localitySelectedIndex + 1, items.length - 1);
                updateLocalitySelectedSuggestion();
                break;
            case 'ArrowUp':
                e.preventDefault();
                localitySelectedIndex = Math.max(localitySelectedIndex - 1, 0);
                updateLocalitySelectedSuggestion();
                break;
            case 'Enter':
                e.preventDefault();
                if (localitySelectedIndex >= 0 && items[localitySelectedIndex]) {
                    localityInput.value = items[localitySelectedIndex].textContent;
                    localitySuggestions.style.display = 'none';
                }
                break;
            case 'Escape':
                localitySuggestions.style.display = 'none';
                break;
        }
    });

    localityInput.addEventListener('blur', function() {
        setTimeout(() => {
            if (localityInput.value) {
                const formattedLocality = capitalizeWords(localityInput.value);
                localityInput.value = formattedLocality;
                
                // Guardar la localidad en la lista general
                if (!locationsList.includes(formattedLocality)) {
                    locationsList.push(formattedLocality);
                    locationsList.sort();
                    localStorage.setItem('locationsList', JSON.stringify(locationsList));
                }
                
                // Si hay una provincia seleccionada, también guardar la localidad en la lista de esa provincia
                const selectedProvincia = provinciaInput.value;
                if (selectedProvincia && localitiesByProvince) {
                    if (!localitiesByProvince[selectedProvincia]) {
                        localitiesByProvince[selectedProvincia] = [];
                    }
                    
                    if (!localitiesByProvince[selectedProvincia].includes(formattedLocality)) {
                        localitiesByProvince[selectedProvincia].push(formattedLocality);
                        localitiesByProvince[selectedProvincia].sort();
                        localStorage.setItem('localitiesByProvince', JSON.stringify(localitiesByProvince));
                    }
                }
            }
        }, 200);
    });

    provinciaInput.addEventListener('input', function(e) {
        const query = e.target.value;
        if (query.length > 0) {
            const suggestions = filterProvincias(query);
            showProvinciaSuggestions(suggestions);
        } else {
            provinciaSuggestions.style.display = 'none';
        }
    });

    provinciaInput.addEventListener('change', function() {
        // Cuando se selecciona una nueva provincia, limpiar el campo de localidad
        localityInput.value = '';
    });

    provinciaInput.addEventListener('keydown', function(e) {
        const items = provinciaSuggestions.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                provinciaSelectedIndex = Math.min(provinciaSelectedIndex + 1, items.length - 1);
                updateProvinciaSelectedSuggestion();
                break;
            case 'ArrowUp':
                e.preventDefault();
                provinciaSelectedIndex = Math.max(provinciaSelectedIndex - 1, 0);
                updateProvinciaSelectedSuggestion();
                break;
            case 'Enter':
                e.preventDefault();
                if (provinciaSelectedIndex >= 0 && items[provinciaSelectedIndex]) {
                    const selectedProvincia = items[provinciaSelectedIndex].textContent;
                    provinciaInput.value = selectedProvincia;
                    provinciaSuggestions.style.display = 'none';
                    
                    // Al seleccionar una provincia, limpiar el campo de localidad
                    localityInput.value = '';
                    
                    // Mostrar las localidades de la provincia seleccionada
                    if (localitiesByProvince && localitiesByProvince[selectedProvincia]) {
                        // Enfocar en el campo de localidad
                        setTimeout(() => {
                            localityInput.focus();
                            // Mostrar todas las localidades de esta provincia como sugerencias
                            showLocalitySuggestions(localitiesByProvince[selectedProvincia]);
                        }, 100);
                    }
                }
                break;
            case 'Escape':
                provinciaSuggestions.style.display = 'none';
                break;
        }
    });

    provinciaInput.addEventListener('blur', function() {
        setTimeout(() => {
            if (provinciaInput.value) {
                const formattedProvincia = capitalizeWords(provinciaInput.value);
                provinciaInput.value = formattedProvincia;
                
                if (!provinciasList.includes(formattedProvincia)) {
                    provinciasList.push(formattedProvincia);
                    provinciasList.sort();
                    localStorage.setItem('provinciasList', JSON.stringify(provinciasList));
                    
                    // Inicializar el array de localidades para esta nueva provincia
                    if (localitiesByProvince && !localitiesByProvince[formattedProvincia]) {
                        localitiesByProvince[formattedProvincia] = [];
                        localStorage.setItem('localitiesByProvince', JSON.stringify(localitiesByProvince));
                    }
                }
            }
        }, 200);
    });

    document.addEventListener('click', function(e) {
        if (!brandInput.contains(e.target) && !brandSuggestions.contains(e.target)) {
            brandSuggestions.style.display = 'none';
        }
        if (!localityInput.contains(e.target) && !localitySuggestions.contains(e.target)) {
            localitySuggestions.style.display = 'none';
        }
        if (!provinciaInput.contains(e.target) && !provinciaSuggestions.contains(e.target)) {
            provinciaSuggestions.style.display = 'none';
        }
    });

    isNewRadios.forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });
    
    categorySelect.addEventListener('change', toggleLoadCapacityField);
    taxpayerSelect.addEventListener('change', calculatePrices);
    priceInput.addEventListener('input', calculatePrices);

    const usedFields = usedFieldsContainer.querySelectorAll('input, select');
    usedFields.forEach(field => {
        if (field.hasAttribute('required')) {
            field.setAttribute('data-required', 'true');
        }
    });

    const savedBrands = localStorage.getItem('machineryBrands');
    if (savedBrands) {
        machineryBrands = JSON.parse(savedBrands);
    }

    const savedLocations = localStorage.getItem('locationsList');
    if (savedLocations) {
        locationsList = JSON.parse(savedLocations);
    }
    
    const savedProvincias = localStorage.getItem('provinciasList');
    if (savedProvincias) {
        provinciasList = JSON.parse(savedProvincias);
    }
    
    // Verificar si existe la estructura localitiesByProvince en localStorage
    if (!localStorage.getItem('localitiesByProvince') && typeof localitiesByProvince === 'undefined') {
        // Si no existe en localStorage pero está definida como variable global, guardarla
        if (typeof localitiesByProvince !== 'undefined') {
            localStorage.setItem('localitiesByProvince', JSON.stringify(localitiesByProvince));
        } else {
            // Si no existe ni como variable global, crear una estructura vacía
            window.localitiesByProvince = {};
            for (const provincia of provinciasList) {
                window.localitiesByProvince[provincia] = [];
            }
            localStorage.setItem('localitiesByProvince', JSON.stringify(window.localitiesByProvince));
        }
    } else if (localStorage.getItem('localitiesByProvince')) {
        // Si existe en localStorage, cargarla
        window.localitiesByProvince = JSON.parse(localStorage.getItem('localitiesByProvince'));
    }

    toggleFields();
    toggleLoadCapacityField();
    calculatePrices();

    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            const parent = this.parentElement;
            const existingFileList = parent.querySelector('.file-list');
            
            if (existingFileList) {
                parent.removeChild(existingFileList);
            }
            
            if (files.length > 0) {
                const fileList = document.createElement('div');
                fileList.className = 'file-list';
                fileList.style.marginTop = '0.5rem';
                fileList.style.fontSize = '0.8rem';
                
                files.forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.textContent = file.name;
                    fileList.appendChild(fileItem);
                });
                
                parent.appendChild(fileList);
            }
        });
    }
});