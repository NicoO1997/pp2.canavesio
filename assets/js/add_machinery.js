document.addEventListener('DOMContentLoaded', function() {
    const brandInput = document.querySelector('input[name="used_machinery[brand]"]');
    const brandSuggestions = document.querySelector('.brand-suggestions');
    const locationInput = document.querySelector('input[name="used_machinery[location]"]');
    const locationSuggestions = document.querySelector('.location-suggestions');
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
    let locationSelectedIndex = -1;

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

    function filterLocations(query) {
        query = query.toLowerCase();
        return locationsList.filter(location => 
            location.toLowerCase().startsWith(query)
        );
    }

    function showLocationSuggestions(suggestions) {
        locationSuggestions.innerHTML = '';
        locationSelectedIndex = -1;

        if (suggestions.length > 0) {
            suggestions.forEach((location, index) => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = location;
                div.dataset.index = index;
                div.addEventListener('click', () => {
                    locationInput.value = location;
                    locationSuggestions.style.display = 'none';
                });
                div.addEventListener('mouseover', () => {
                    locationSelectedIndex = index;
                    updateLocationSelectedSuggestion();
                });
                locationSuggestions.appendChild(div);
            });
            locationSuggestions.style.display = 'block';
        } else {
            locationSuggestions.style.display = 'none';
        }
    }

    function updateLocationSelectedSuggestion() {
        const items = locationSuggestions.querySelectorAll('.suggestion-item');
        items.forEach(item => item.classList.remove('selected'));
        if (locationSelectedIndex >= 0 && items[locationSelectedIndex]) {
            items[locationSelectedIndex].classList.add('selected');
            items[locationSelectedIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
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
    locationInput.addEventListener('input', function(e) {
        const query = e.target.value;
        if (query.length > 0) {
            const suggestions = filterLocations(query);
            showLocationSuggestions(suggestions);
        } else {
            locationSuggestions.style.display = 'none';
        }
    });

    locationInput.addEventListener('keydown', function(e) {
        const items = locationSuggestions.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                locationSelectedIndex = Math.min(locationSelectedIndex + 1, items.length - 1);
                updateLocationSelectedSuggestion();
                break;
            case 'ArrowUp':
                e.preventDefault();
                locationSelectedIndex = Math.max(locationSelectedIndex - 1, 0);
                updateLocationSelectedSuggestion();
                break;
            case 'Enter':
                e.preventDefault();
                if (locationSelectedIndex >= 0 && items[locationSelectedIndex]) {
                    locationInput.value = items[locationSelectedIndex].textContent;
                    locationSuggestions.style.display = 'none';
                }
                break;
            case 'Escape':
                locationSuggestions.style.display = 'none';
                break;
        }
    });

    locationInput.addEventListener('blur', function() {
        setTimeout(() => {
            if (locationInput.value) {
                const formattedLocation = capitalizeWords(locationInput.value);
                locationInput.value = formattedLocation;
                
                if (!locationsList.includes(formattedLocation)) {
                    locationsList.push(formattedLocation);
                    locationsList.sort();
                    localStorage.setItem('locationsList', JSON.stringify(locationsList));
                }
            }
        }, 200);
    });

    document.addEventListener('click', function(e) {
        if (!brandInput.contains(e.target) && !brandSuggestions.contains(e.target)) {
            brandSuggestions.style.display = 'none';
        }
        if (!locationInput.contains(e.target) && !locationSuggestions.contains(e.target)) {
            locationSuggestions.style.display = 'none';
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