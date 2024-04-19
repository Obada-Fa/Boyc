document.addEventListener("DOMContentLoaded", function () {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const productGallery = document.getElementById('product-gallery');
    const countrySelector = document.getElementById('country-selector');

    function initializeSelect2(countryCode) {
        $(countrySelector).select2({
            templateResult: formatCountry,
            templateSelection: formatCountry
        }).val(countryCode).trigger('change');
    }

    function formatCountry(country) {
        if (!country.id) {
            return country.text;
        }
        return $(`<span class="flag-icon flag-icon-${country.id.toLowerCase()}"></span><span>${country.text}</span>`);
    }

    function handleGeolocationError(error) {
        console.warn("Geolocation error: ", error.message);
        fetchCountryByIP();  // Fallback to IP-based location
    }

    function fetchCountryByIP() {
        fetch('./includes/location.php')
            .then(response => response.json())
            .then(data => {
                if (data.country_code && data.country_code !== 'Unknown') {
                    initializeSelect2(data.country_code);
                } else {
                    initializeSelect2WithoutCountry();
                }
            })
            .catch(() => initializeSelect2WithoutCountry());
    }

    function initializeSelect2WithoutCountry() {
        $(countrySelector).select2({
            templateResult: formatCountry,
            templateSelection: formatCountry
        });
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            fetch(`./includes/location.php?lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
                .then(response => response.json())
                .then(data => initializeSelect2(data.country_code))
                .catch(handleGeolocationError);
        }, handleGeolocationError);
    } else {
        console.log("Geolocation is not supported by this browser.");
        fetchCountryByIP();
    }

    searchForm.addEventListener('submit', event => {
        event.preventDefault();
        fetchProducts(searchInput.value, $(countrySelector).val());
    });

    countrySelector.on('change', () => {
        fetchProducts(searchInput.value, $(countrySelector).val());
    });

    function fetchProducts(searchTerm, country) {
        fetch(`search.php?search=${encodeURIComponent(searchTerm)}&country=${encodeURIComponent(country)}`)
            .then(response => response.json())
            .then(updateProductGallery)
            .catch(error => console.error('Error fetching products:', error));
    }

    function updateProductGallery(data) {
        productGallery.innerHTML = '';
        if (data.length > 0) {
            data.forEach(product => {
                const productDiv = document.createElement('div');
                productDiv.className = 'product';
                productDiv.innerHTML = `
                    <img src="img/${product.image}" alt="${product.name}">
                    <h3>${product.name}</h3>
                    <p>${product.description}</p>
                    <p>Barcode: ${product.barcode}</p>
                    <p>Company: ${product.company_name}</p>
                    <a href="${product.article_link}" target='_blank'>Why we boycott this</a>
                `;
                productGallery.appendChild(productDiv);
            });
        } else {
            productGallery.innerHTML = '<p>No products found.</p>';
        }
    }
});
