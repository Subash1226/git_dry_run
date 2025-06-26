document.addEventListener("DOMContentLoaded", function () {
    function toggleRadio(radio) {
        var labels = document.querySelectorAll(".Address-label");
        labels.forEach(function (label) {
            label.classList.remove("checked");
        });
        var label = document.querySelector('label[for="' + radio.id + '"]');
        label.classList.add("checked");
    }
});
const swatch = document.querySelector("div[data-role='swatch-options']");
var offer = document.getElementById("off-ends");
if (swatch) {
    offer.style.display = "none";
}
function myFunction() {
    var popup = document.getElementById("myPopup");
    popup.classList.toggle("show");
}
function FeeAdd() {
    console.log("Fee added successfully!");
}
function openaccount() {
    var hiddenDiv = document.getElementById("hiddenDiv");
    hiddenDiv.style.display = "block";
}
function closeaccount() {
    var hiddenDiv = document.getElementById("hiddenDiv");
    hiddenDiv.style.display = "none";
}

function formatTextCase(text) {
    if (!text) return null;
    
    text = text.toLowerCase();    
    text = text.replace(/\b\w/g, letter => letter.toUpperCase());
    
    return text;
}

function formatProductName(maxNormalLength = 31, maxMediumLength = 38) {
    const productNameElement = document.querySelector(
        ".product-info-main .page-title-wrapper.product .page-title .base"
    );

    if (productNameElement) {
        let fullText = productNameElement.textContent;
        let textLength = fullText.length;
        fullText = formatTextCase(fullText);

        productNameElement.style.fontSize = '28px';
        productNameElement.textContent = fullText;
        
        if (textLength <= maxNormalLength) {
            return;
        }
        
        if (textLength > maxNormalLength && textLength <= maxMediumLength) {
            productNameElement.style.fontSize = '25px';
            return;
        }
        
        if (textLength > maxMediumLength) {
            productNameElement.style.fontSize = '24px';
            const truncatedText = fullText.substring(0, maxMediumLength) + "...";
            productNameElement.textContent = truncatedText;
            productNameElement.setAttribute("title", fullText);
        }
    }
}

function truncateDealsProductName(maxLength = 28) {
    const dealsProductElements = document.querySelectorAll(".cusl_product-title");

    dealsProductElements.forEach(element => {
        let fullText = element.textContent.trim();

        if (fullText.length > maxLength) {
            const truncatedText = fullText.substring(0, maxLength) + "...";
            element.textContent = truncatedText;
            element.setAttribute("title", fullText);
        }
    });
}

function truncateDealsLocationName(maxLength = 21) {
    const locationElements = document.querySelectorAll("#area");

    Array.from(locationElements).forEach(element => {
        let fullText = element.textContent.trim();

        if (fullText.length > maxLength) {
            const truncatedText = fullText.substring(0, maxLength) + "...";
            element.textContent = truncatedText;
            element.setAttribute("title", fullText);
        }
    });
}

function truncateCartProductName(maxLength = 26) {
    const dealsProductElements = document.querySelectorAll(".checkout-cart-index .checkoutCartProduct .product-item-name a");

    dealsProductElements.forEach(element => {
        let fullText = element.textContent.trim();

        if (fullText.length > maxLength) {
            const truncatedText = fullText.substring(0, maxLength) + "...";
            element.textContent = truncatedText;
            element.setAttribute("title", fullText);
        }
    });
}

function truncateCusrpDealsProductName(maxLength = 28) {
    const dealsProductElements = document.querySelectorAll(".cusrp-product-item-name");

    dealsProductElements.forEach(element => {
        let fullText = element.textContent.trim();
        
        if (fullText.length > maxLength) {
            const truncatedText = fullText.substring(0, maxLength) + "...";
            element.textContent = truncatedText;
            element.setAttribute("title", fullText);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    formatProductName();
    truncateDealsProductName();
    truncateDealsLocationName();
    truncateCartProductName();
    truncateCusrpDealsProductName();
    const logoImg = document.querySelector('.logo img');
    if (logoImg) {
        logoImg.setAttribute('data-bs-toggle', 'tooltip');
        logoImg.setAttribute('data-bs-placement', 'bottom');
        logoImg.setAttribute('title', 'Medizinhub');
    }

    const searchInput = document.querySelector('#search');
    if (searchInput) {
        searchInput.setAttribute('data-bs-toggle', 'tooltip');
        searchInput.setAttribute('data-bs-placement', 'top');
        searchInput.setAttribute('title', 'Search');
    }

    const searchBtnInput = document.querySelector('.search-btn');
    if (searchBtnInput) {
        searchBtnInput.setAttribute('data-bs-toggle', 'tooltip');
        searchBtnInput.setAttribute('data-bs-placement', 'top');
        searchBtnInput.setAttribute('title', 'Search');
    }

    const cartLink = document.querySelector('.action.showcart');
    if (cartLink) {
        cartLink.setAttribute('data-bs-toggle', 'tooltip');
        cartLink.setAttribute('data-bs-placement', 'bottom');
        cartLink.setAttribute('title', 'My Cart');
    }
});