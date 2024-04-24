$(document).ready(function() {
    const searchForm = $('#search-form');
    const searchInput = $('#search-input');
    const productGallery = $('#product-gallery');
    const countrySelector = $('#country-selector');

    function initializeSelect2(countryCode) {
        countrySelector.select2({
            templateResult: formatCountry,
            templateSelection: formatCountry
        }).val(countryCode).trigger('change');
        console.log("Select2 initialized with country code: ", countryCode);
    }

    function formatCountry(country) {
        if (!country.id) {
            return country.text;
        }
        return $(`<span class="flag-icon flag-icon-${country.id.toLowerCase()}"></span><span>${country.text}</span>`);
    }

    function handleGeolocationError(error) {
        console.warn("Geolocation error: ", error.message);
        fetchCountryByIP();
    }



    function fetchCountryByIP() {
        fetch('./includes/location.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Country code fetched by IP: ", data.country_code);
                if (data.country_code && data.country_code !== 'Unknown') {
                    initializeSelect2(data.country_code);
                } else {
                    initializeSelect2WithoutCountry();
                }
            })
            .catch(error => {
                console.error("Failed to fetch country by IP:", error);
                initializeSelect2WithoutCountry();
            });
    }

// Function to fetch country from Nominatim API using coordinates
    async function fetchCountryFromCoordinates(latitude, longitude) {
        console.log(typeof(latitude));
        const apiUrl = `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`;

        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            // Extract country from response data
            const country = data.address.country;

            return country;
        } catch (error) {
            console.error('Error fetching country:', error);
            return null;
        }
    }

    function getCountry(){
        let latitude
        let longitude
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(position => {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;
                fetchCountryFromCoordinates(latitude, longitude)
                    .then(country => {
                        if (country) {
                            console.log('Country:', country);
                        } else {
                            console.log('Country not found.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }, handleGeolocationError);

        }
    }

    function initializeSelect2WithoutCountry() {
        countrySelector.select2({
            templateResult: formatCountry,
            templateSelection: formatCountry
        });
        console.log("Initialized Select2 without country data.");
    }

    if (navigator.geolocation) {
        getCountry();

        navigator.geolocation.getCurrentPosition(position => {
            console.log("Latitude: ", position.coords.latitude);
            console.log("Longitude: ", position.coords.longitude); // Log latitude and longitude

            fetch(`./includes/location.php?lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Country code fetched by geolocation: ", data.country_code);
                    if (data.country_code !== 'Unknown') {
                        initializeSelect2(data.country_code);
                    } else {
                        fetchCountryByIP();
                    }
                })
                .catch(handleGeolocationError);
        }, handleGeolocationError);
    } else {
        console.log("Geolocation is not supported by this browser.");
        fetchCountryByIP();
    }

    searchForm.submit(function(event) {
        event.preventDefault();
        fetchProducts(searchInput.val(), countrySelector.val());
    });

    countrySelector.on('change', function() {
        fetchProducts(searchInput.val(), $(this).val());
    });

    function fetchProducts(searchTerm, country) {
        fetch(`search.php?search=${encodeURIComponent(searchTerm)}&country=${encodeURIComponent(country)}`)
            .then(response => response.json())
            .then(updateProductGallery)
            .catch(error => console.error('Error fetching products:', error));
    }

    function updateProductGallery(data) {
        productGallery.empty();
        if (data.length > 0) {
            data.forEach(product => {
                const productDiv = $('<div>', {class: 'product'}).html(`
                    <img src="img/${product.image}" alt="${product.name}">
                    <h3>${product.name}</h3>
                    <p>${product.description}</p>
                    <p>Barcode: ${product.barcode}</p>
                    <p>Company: ${product.company_name}</p>
                    <a href="${product.article_link}" target='_blank'>Why we boycott this</a>
                `);
                productGallery.append(productDiv);
            });
        } else {
            productGallery.html('<p>No products found.</p>');
        }
    }
});