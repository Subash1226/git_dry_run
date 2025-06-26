require(['jquery', 'Magento_Customer/js/customer-data'], function($, customerData) {
    function checkSalableQuantity(sku, qty) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/ajaxshoppingcartupdate/product/getsalablequantity',
                type: 'GET',
                data: { sku: sku },
                showLoader: true,
                success: function(response) {
                    if (response.success) {
                        resolve(qty <= response.salable_quantity);
                    } else {
                        console.error(response.message);
                        reject(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ' ' + error);
                    console.log(xhr.responseText);
                    reject(error);
                }
            });
        });
    }

    $(document).on('click', '[id^="cusl_add-to-cart-btn"]', async function(e) {
        e.preventDefault();
        var $addToCartBtn = $(this);
        var productId = $addToCartBtn.data('product-id');
        var sku = $addToCartBtn.data('product-sku');
        var qty = 1;
        var formKey = $('input[name="form_key"]').val();

        try {
            const isSalable = await checkSalableQuantity(sku, qty);
            if (!isSalable) {
                alert('Sorry, this product is not available in the requested quantity.');
                return;
            }

            $.ajax({
                url: '/home/index/AddToCart',
                method: 'POST',
                showLoader: true,
                dataType: 'json',
                data: {
                    product_id: productId,
                    form_key: formKey,
                    qty: qty
                },
                success: function(response) {
                    if (response.success) {
                        reloadCart();
                        setTimeout(function() {
                            $('.showcart').click();
                        }, 7000);
                        $addToCartBtn.hide();
                        $addToCartBtn.siblings('.quantity-container').css('display', 'flex');
                        var cartItems = sessionStorage.getItem('CartItems') ?
                            JSON.parse(sessionStorage.getItem('CartItems')) : [];
                        if (!cartItems.includes(productId)) {
                            cartItems.push(productId);
                            sessionStorage.setItem('CartItems', JSON.stringify(cartItems));
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        } catch (error) {
            alert('Error checking product availability. Please try again later.');
            console.error(error);
        }
    });

    $(document).on('click', '.increment-btn', async function(e) {
        e.preventDefault();
        var $quantityContainer = $(this).closest('.quantity-container');
        var currentQuantity = parseInt($quantityContainer.find('.quantity-value').text());
        var $addToCartBtn = $quantityContainer.siblings('[id^="cusl_add-to-cart-btn"]');
        var productId = $addToCartBtn.data('product-id');
        var sku = $addToCartBtn.data('product-sku');
        var newQuantity = currentQuantity + 1;

        try {
            const isSalable = await checkSalableQuantity(sku, newQuantity);
            if (!isSalable) {
                alert('Sorry, this product is not available in the requested quantity.');
                return;
            }

            $quantityContainer.find('.quantity-value').text(newQuantity);
            updateQuantity(productId, newQuantity);
        } catch (error) {
            alert('Error checking product availability. Please try again later.');
            console.error(error);
        }
    });

    $(document).on('click', '.decrement-btn', function(e) {
        e.preventDefault();
        var $quantityContainer = $(this).closest('.quantity-container');
        var quantity = parseInt($quantityContainer.find('.quantity-value').text());
        var $addToCartBtn = $quantityContainer.siblings('[id^="cusl_add-to-cart-btn"]');
        var productId = $addToCartBtn.data('product-id');

        if (quantity > 1) {
            quantity -= 1;
            $quantityContainer.find('.quantity-value').text(quantity);
            if (productId) {
                updateQuantity(productId, quantity);
            } else {
                console.error("Invalid product ID:", productId);
            }
        } else {
            if (productId) {
                removeFromCart(productId);
            } else {
                console.error("Invalid product ID:", productId);
            }
        }
    });

    function removeFromCart(productId) {
        var formKey = $('input[name="form_key"]').val();
        $.ajax({
            url: '/home/index/RemoveFromCart',
            method: 'POST',
            showLoader: true,
            dataType: 'json',
            data: {
                product_id: productId,
                form_key: formKey
            },
            success: function(response) {
                reloadCart();
                $('[id^="cusl_add-to-cart-btn"][data-product-id="' + productId + '"]').show();
                $('[id^="cusl_add-to-cart-btn"][data-product-id="' + productId + '"]').siblings('.quantity-container').css('display', 'none');
            },
            error: function(xhr, status, error) {
                console.error("Error removing product from cart:", error);
            }
        });
    }

    function updateQuantity(productId, quantity) {
        var formKey = $('input[name="form_key"]').val();
        $.ajax({
            url: '/home/index/UpdatedCart',
            method: 'POST',
            showLoader: true,
            dataType: 'json',
            data: {
                product_id: productId,
                form_key: formKey,
                qty: quantity
            },
            success: function(response) {
                reloadCart();
                setTimeout(function() {
                    $('.showcart').click();
                }, 7000);
            },
            error: function(xhr, status, error) {
                console.error("Error updating quantity:", error);
            }
        });
    }

    function fetchAndUpdateCartData() {
        $.ajax({
            url: '/home/index/AjaxController',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.hasOwnProperty('items')) {
                    if (response.items.length === 0) {
                        sessionStorage.removeItem('CartItems');
                        $('.quantity-container').css('display', 'none');
                        $('[id^="cusl_add-to-cart-btn"]').show();
                    } else {
                        var cartProductIds = response.items.map(function(product) {
                            return product.product_id.toString();
                        });

                        $('[id^="cusl_add-to-cart-btn"]').each(function() {
                            var $addToCartBtn = $(this);
                            var productId = $addToCartBtn.data('product-id');
                            var $quantityContainer = $addToCartBtn.siblings('.quantity-container');

                            if (cartProductIds.includes(productId.toString())) {
                                var cartItem = response.items.find(function(item) {
                                    return item.product_id.toString() === productId.toString();
                                });

                                if (cartItem) {
                                    var cartQty = parseInt(cartItem.qty);
                                    $quantityContainer.find('.quantity-value').text(
                                        cartQty > 10 ? 11 : cartQty
                                    );
                                    $addToCartBtn.hide();
                                    $quantityContainer.css('display', 'flex');
                                }
                            } else {
                                $addToCartBtn.show();
                                $quantityContainer.css('display', 'none');
                            }
                        });
                    }
                } else {
                    console.log('No items found in the response');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function reloadCart() {
        var sections = ['cart'];
        customerData.reload(sections, true);
        setTimeout(fetchAndUpdateCartData, 1000);
    }

    setTimeout(reloadCart, 2000);
});


class ProductSlider {
    constructor(sliderContainer) {
        this.container = sliderContainer;
        this.track = this.container.querySelector('.cusl_slider-track');
        this.prevBtn = this.container.querySelector('.cusl_prev-btn');
        this.nextBtn = this.container.querySelector('.cusl_next-btn');
        this.dotsContainer = this.container.querySelector('.cusl_dots-container');
        this.originalCards = Array.from(this.track.querySelectorAll('.cusl_product-card'));
        // State management
        this.autoSlideInterval = null;
        this.isHovered = false;
        this.isTransitioning = false;
        this.isDragging = false;
        // Drag state
        this.dragStartX = 0;
        this.dragStartY = 0;
        this.currentX = 0;
        this.initialX = 0;
        this.xOffset = 0;
        this.startTime = null;
        this.isScrollingVertically = false;
        // Thresholds
        this.dragThreshold = 50; // Minimum distance to trigger slide change
        this.dragVelocityThreshold = 0.3; // Minimum velocity to trigger slide change
        // Calculate visible slides based on viewport
        this.visibleSlides = this.calculateVisibleSlides();
        this.cloneSlides();
        this.init();
    }
    calculateVisibleSlides() {
        const containerWidth = this.container.offsetWidth;
        if (containerWidth <= 576) return 2;
        if (containerWidth <= 768) return 3;
        if (containerWidth <= 992) return 4;
        if (containerWidth <= 1200) return 5;
        return 6;
    }
    initDragEvents() {
        // Touch Events
        this.track.addEventListener('touchstart', (e) => this.dragStart(e), { passive: true });
        this.track.addEventListener('touchmove', (e) => this.dragMove(e), { passive: false });
        this.track.addEventListener('touchend', () => this.dragEnd());
        // Mouse Events
        this.track.addEventListener('mousedown', (e) => this.dragStart(e));
        window.addEventListener('mousemove', (e) => this.dragMove(e));
        window.addEventListener('mouseup', () => this.dragEnd());
        // Prevent context menu
        this.track.addEventListener('contextmenu', (e) => e.preventDefault());
    }
    dragStart(e) {
        if (this.isTransitioning) return;
        this.isDragging = true;
        this.startTime = Date.now();
        this.isScrollingVertically = false;
        const point = e.touches ? e.touches[0] : e;
        this.dragStartX = point.clientX;
        this.dragStartY = point.clientY;
        // Get current transform value
        const transform = window.getComputedStyle(this.track).getPropertyValue('transform');
        const matrix = new DOMMatrix(transform);
        this.initialX = matrix.m41;
        this.currentX = this.initialX;
        this.track.style.transition = 'none';
        this.pauseAutoSlide();
        if (e.type === 'mousedown') {
            e.preventDefault();
        }
    }
    dragMove(e) {
        if (!this.isDragging) return;
        const point = e.touches ? e.touches[0] : e;
        const deltaX = point.clientX - this.dragStartX;
        const deltaY = point.clientY - this.dragStartY;
        // Check if scrolling vertically
        if (!this.isScrollingVertically && Math.abs(deltaY) > Math.abs(deltaX)) {
            this.isScrollingVertically = true;
        }
        if (this.isScrollingVertically) return;
        e.preventDefault();
        this.currentX = this.initialX + deltaX;
        this.track.style.transform = `translateX(${this.currentX}px)`;
    }
    dragEnd() {
        if (!this.isDragging) return;
        const dragDuration = Date.now() - this.startTime;
        const dragDistance = this.currentX - this.initialX;
        const velocity = Math.abs(dragDistance / dragDuration);
        this.isDragging = false;
        if (this.isScrollingVertically) {
            this.updateSliderPosition(true);
            return;
        }
        // Calculate number of slides to move based on drag distance
        const slideWidth = this.cards[0].offsetWidth + 20; // Including gap
        const slidesToMove = Math.round(Math.abs(dragDistance) / slideWidth);
        // Determine if we should change slides
        const shouldChangeSlide = Math.abs(dragDistance) > this.dragThreshold ||
                                velocity > this.dragVelocityThreshold;
        if (shouldChangeSlide) {
            if (dragDistance > 0) {
                this.slideMultiple('prev', slidesToMove);
            } else {
                this.slideMultiple('next', slidesToMove);
            }
        } else {
            // Return to original position
            this.updateSliderPosition(true);
        }
        this.resumeAutoSlide();
    }
    cloneSlides() {
        // Clear existing clones
        this.track.querySelectorAll('.clone').forEach(clone => clone.remove());
        const totalSlides = this.originalCards.length;
        const slidesToClone = Math.max(this.visibleSlides, Math.min(totalSlides, 5));
        // Clone beginning slides and append to end
        for (let i = 0; i < slidesToClone; i++) {
            const clone = this.originalCards[i].cloneNode(true);
            clone.classList.add('clone');
            this.track.appendChild(clone);
        }
        // Clone end slides and prepend to beginning
        for (let i = totalSlides - 1; i >= Math.max(0, totalSlides - slidesToClone); i--) {
            const clone = this.originalCards[i].cloneNode(true);
            clone.classList.add('clone');
            this.track.insertBefore(clone, this.track.firstChild);
        }
        this.currentIndex = slidesToClone;
        this.cards = this.track.querySelectorAll('.cusl_product-card');
        this.updateSliderPosition(false);
    }
    init() {
        this.createDots();
        this.initDragEvents();
        this.prevBtn.addEventListener('click', () => this.slide('prev'));
        this.nextBtn.addEventListener('click', () => this.slide('next'));
        this.container.addEventListener('mouseenter', () => this.pauseAutoSlide());
        this.container.addEventListener('mouseleave', () => this.resumeAutoSlide());
        this.track.addEventListener('transitionend', () => this.handleTransitionEnd());
        window.addEventListener('resize', () => {
            this.visibleSlides = this.calculateVisibleSlides();
            this.cloneSlides();
        });
        this.startAutoSlide();
    }
    slideMultiple(direction, count = 1) {
        if (this.isTransitioning) return;
        this.isTransitioning = true;
        this.track.style.transition = 'transform 0.3s ease-out';
        if (direction === 'next') {
            this.currentIndex += count;
        } else {
            this.currentIndex -= count;
        }
        this.updateSliderPosition(true);
        this.updateDots();
    }
    slide(direction) {
        if (this.isTransitioning) return;
        this.isTransitioning = true;
        this.slideMultiple(direction, 1);
        this.track.style.transition = 'transform 0.3s ease-out';
        if (direction === 'next') {
            this.currentIndex++;
        } else {
            this.currentIndex--;
        }
        this.updateSliderPosition(true);
        this.updateDots();
    }
    updateSliderPosition(animate = true) {
        const slideWidth = this.cards[0].offsetWidth + 20;
        const translateX = -slideWidth * this.currentIndex;
        this.track.style.transition = animate ? 'transform 0.3s ease-out' : 'none';
        this.track.style.transform = `translateX(${translateX}px)`;
    }
    handleTransitionEnd() {
        this.isTransitioning = false;
        this.track.style.transition = 'none';
        const totalSlides = this.cards.length;
        const slidesToClone = Math.max(this.visibleSlides, Math.min(this.originalCards.length, 5));
        if (this.currentIndex >= totalSlides - slidesToClone) {
            this.currentIndex = slidesToClone;
            this.updateSliderPosition(false);
        } else if (this.currentIndex <= slidesToClone - 1) {
            this.currentIndex = totalSlides - slidesToClone - 1;
            this.updateSliderPosition(false);
        }
    }
    createDots() {
        this.dotsContainer.innerHTML = '';
        const totalDots = this.originalCards.length;
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('div');
            dot.className = `cusl_dot ${i === 0 ? 'active' : ''}`;
            dot.addEventListener('click', () => this.goToSlide(i + this.visibleSlides));
            this.dotsContainer.appendChild(dot);
        }
    }
    updateDots() {
        const activeDotIndex = (this.currentIndex - this.visibleSlides) % this.originalCards.length;
        const dots = this.dotsContainer.querySelectorAll('.cusl_dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === activeDotIndex);
        });
    }
    goToSlide(index) {
        if (this.isTransitioning) return;
        this.track.style.transition = 'transform 0.3s ease-out';
        this.currentIndex = index;
        this.updateSliderPosition(true);
        this.updateDots();
    }
    startAutoSlide() {
        this.autoSlideInterval = setInterval(() => {
            if (!this.isHovered && !this.isDragging) {
                this.slide('next');
            }
        }, 3000);
    }
    pauseAutoSlide() {
        this.isHovered = true;
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }
    resumeAutoSlide() {
        this.isHovered = false;
        if (!this.autoSlideInterval) {
            this.startAutoSlide();
        }
    }
}
// Initialize all sliders
document.addEventListener('DOMContentLoaded', () => {
    const sliderContainers = document.querySelectorAll('.cusl_slider-container');
    sliderContainers.forEach(container => {
        new ProductSlider(container);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Add CSS for fade-in transition
    const style = document.createElement('style');
    style.textContent = `
        .lazy-image {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        .lazy-image.loaded {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);

    // Define the sections we want to observe
    const sections = [
        '.order-home',
        '.today-deals',
        '.shop-category',
        '.Health-category',
        '#medical-accessories',
        '.combo-category'
    ];

    // Create intersection observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // When section is visible, load all images within it
                const images = entry.target.querySelectorAll('img[data-src]');
                images.forEach(img => {
                    // Add lazy-image class for transition effect
                    img.classList.add('lazy-image');

                    // Create a new image object to preload
                    const newImg = new Image();
                    newImg.onload = function() {
                        // Once image is loaded, set the src and add loaded class
                        img.src = img.dataset.src;
                        setTimeout(() => {
                            img.classList.add('loaded');
                        }, 10); // Small delay to ensure CSS transition works
                        img.removeAttribute('data-src');
                    };
                    newImg.src = img.dataset.src;
                });

                // Once processed, no need to observe this section anymore
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '200px 0px', // Start loading when section is 200px from viewport
        threshold: 0.01 // Trigger when at least 1% of the section is visible
    });

    // Start observing each section
    sections.forEach(sectionSelector => {
        const section = document.querySelector(sectionSelector);
        if (section) {
            // Convert all images in this section to use data-src instead of src
            const images = section.querySelectorAll('img:not([data-src])');
            images.forEach(img => {
                if (img.src) {
                    img.dataset.src = img.src;
                    img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; // Tiny transparent placeholder
                }
            });

            // Start observing this section
            observer.observe(section);
        }
    });

    // Initialize first section's images immediately for above-the-fold content
    const firstSection = document.querySelector(sections[0]);
    if (firstSection) {
        const firstSectionImages = firstSection.querySelectorAll('img[data-src]');
        firstSectionImages.forEach(img => {
            img.classList.add('lazy-image');
            img.src = img.dataset.src;
            setTimeout(() => {
                img.classList.add('loaded');
            }, 10);
            img.removeAttribute('data-src');
        });
    }
});
