 document.addEventListener('DOMContentLoaded', function() {
            // Loading Screen
            setTimeout(function() {
                document.querySelector('.loading-screen').classList.add('hidden');
            }, 2000);

            // Connection Status Check
            function updateOnlineStatus() {
                const connectionStatus = document.querySelector('.connection-status');
                if (navigator.onLine) {
                    connectionStatus.classList.remove('show');
                    // If it was previously offline and now online, show success message
                    if (connectionStatus.classList.contains('was-offline')) {
                        connectionStatus.classList.add('success');
                        connectionStatus.querySelector('span').textContent = 'Your connection has been restored!';
                        connectionStatus.querySelector('button').textContent = 'Great!';
                        connectionStatus.classList.add('show');
                        setTimeout(() => {
                            connectionStatus.classList.remove('show');
                            connectionStatus.classList.remove('success');
                            connectionStatus.classList.remove('was-offline');
                        }, 3000);
                    }
                } else {
                    connectionStatus.classList.add('show');
                    connectionStatus.classList.add('was-offline');
                    connectionStatus.classList.remove('success');
                    connectionStatus.querySelector('span').textContent = 'You are currently offline. Please check your internet connection.';
                    connectionStatus.querySelector('button').textContent = 'Retry';
                }
            }

            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            document.querySelector('.connection-status button').addEventListener('click', function() {
                updateOnlineStatus();
            });

            // Initialize
            updateOnlineStatus();

            // Theme Toggle
            const themeToggle = document.querySelector('.theme-toggle');
            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                if (currentTheme === 'dark') {
                    document.documentElement.removeAttribute('data-theme');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                } else {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                }
            });

            // Cart Functionality
            const cartBtn = document.querySelector('.cart-btn');
            const cartModal = document.querySelector('.cart-modal');
            const cartCount = document.querySelector('.cart-count');
            const cartItemsContainer = document.querySelector('.cart-items');
            const cartTotalAmount = document.querySelector('.cart-total-amount');
            const closeCartBtn = document.querySelector('.close-cart');
            const checkoutBtn = document.querySelector('.checkout-btn');

            let cart = [];

            // Add to Cart buttons
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const bookCard = this.closest('.book-card, .scrolling-book');
                    const bookTitle = bookCard.querySelector('h3, h4').textContent;
                    const bookPrice = parseInt(bookCard.querySelector('.book-price, .scrolling-book-price').textContent.replace(/\D/g, ''));
                    const bookImg = bookCard.querySelector('img').src;

                    // Check if item already in cart
                    const existingItem = cart.find(item => item.title === bookTitle);
                    if (existingItem) {
                        existingItem.quantity += 1;
                    } else {
                        cart.push({
                            title: bookTitle,
                            price: bookPrice,
                            img: bookImg,
                            quantity: 1
                        });
                    }

                    updateCart();
                    cartModal.classList.add('active');
                });
            });

            // Update Cart
            function updateCart() {
                // Update count
                const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
                cartCount.textContent = totalItems;

                // Update items list
                if (cart.length === 0) {
                    cartItemsContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty</p>';
                } else {
                    cartItemsContainer.innerHTML = '';
                    cart.forEach(item => {
                        const cartItem = document.createElement('div');
                        cartItem.className = 'cart-item';
                        cartItem.innerHTML = `
                            <div class="cart-item-img">
                                <img src="${item.img}" alt="${item.title}">
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-title">${item.title}</div>
                                <div class="cart-item-price">${item.price.toLocaleString()} XAF × ${item.quantity}</div>
                            </div>
                            <button class="cart-item-remove">&times;</button>
                        `;
                        cartItemsContainer.appendChild(cartItem);

                        // Add event listener to remove button
                        cartItem.querySelector('.cart-item-remove').addEventListener('click', function() {
                            cart = cart.filter(cartItem => cartItem.title !== item.title);
                            updateCart();
                        });
                    });
                }

                // Update total
                const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                cartTotalAmount.textContent = total.toLocaleString() + ' XAF';
            }

            // Cart Modal Toggle
            cartBtn.addEventListener('click', function() {
                cartModal.classList.add('active');
            });

            closeCartBtn.addEventListener('click', function() {
                cartModal.classList.remove('active');
            });

            // Checkout Process
            checkoutBtn.addEventListener('click', function() {
                if (cart.length === 0) {
                    alert('Your cart is empty!');
                    return;
                }
                cartModal.classList.remove('active');
                document.querySelector('.checkout-modal-step1').classList.add('active');
            });

            // Checkout Step 1 to Step 2
            const continueToPayment = document.querySelector('.continue-to-payment');
            continueToPayment.addEventListener('click', function() {
                const form = document.getElementById('checkout-form-step1');
                if (form.checkValidity()) {
                    document.querySelector('.checkout-modal-step1').classList.remove('active');
                    document.querySelector('.checkout-modal-step2').classList.add('active');
                } else {
                    form.reportValidity();
                }
            });

            // Back from Step 2 to Step 1
            const backToInfo = document.querySelector('.back-to-info');
            backToInfo.addEventListener('click', function() {
                document.querySelector('.checkout-modal-step2').classList.remove('active');
                document.querySelector('.checkout-modal-step1').classList.add('active');
            });

            // Payment Method Selection
            const paymentOptions = document.querySelectorAll('input[name="payment-method"]');
            const paymentDetails = document.querySelectorAll('.payment-details');

            paymentOptions.forEach(option => {
                option.addEventListener('change', function() {
                    paymentDetails.forEach(detail => detail.style.display = 'none');
                    document.querySelector(`.${this.value}-details`).style.display = 'block';
                });
            });

            // Place Order
            const placeOrderBtn = document.querySelector('.place-order-btn');
           
placeOrderBtn.addEventListener('click', function() {
    const form = document.getElementById('checkout-form-step2');
    const paymentMethod = document.querySelector('input[name="payment-method"]:checked').value;
    
    // Validation des informations de paiement
    let isValid = true;
    if (paymentMethod === 'mobile-money') {
        const provider = document.getElementById('mobile-provider');
        const number = document.getElementById('mobile-number');
        if (!provider.value || !number.value) isValid = false;
    }

    if (isValid) {
        // Récupérer les informations du client
        const firstName = document.getElementById('first-name').value;
        const lastName = document.getElementById('last-name').value;
        const phone = document.getElementById('phone').value;
        const address = document.getElementById('address').value;
        
        // Préparer le message WhatsApp
        let whatsappMessage = `*NOUVELLE COMMANDE - Vitrice&Joel Bookstore*%0A%0A`;
        whatsappMessage += `*Nom:* ${firstName} ${lastName}%0A`;
        whatsappMessage += `*Téléphone:* ${phone}%0A`;
        whatsappMessage += `*Adresse:* ${address}%0A%0A`;
        whatsappMessage += `*LIVRES COMMANDÉS:*%0A`;
        
        // Ajouter chaque livre au message
        cart.forEach(item => {
            whatsappMessage += `- ${item.title} (${item.quantity}x) : ${item.price.toLocaleString()} XAF%0A`;
        });
        
        whatsappMessage += `%0A*TOTAL:* ${cart.reduce((sum, item) => sum + (item.price * item.quantity), 0).toLocaleString()} XAF%0A%0A`;
        whatsappMessage += `*Méthode de paiement:* ${paymentMethod.toUpperCase()}`;
        
        // Ouvrir WhatsApp avec le message pré-rempli
        window.open(`https://wa.me/237672096673?text=${whatsappMessage}`, '_blank');
        
        // Vider le panier
        cart = [];
        updateCart();
        
        // Fermer le modal de commande
        document.querySelector('.checkout-modal-step2').classList.remove('active');
    } else {
        alert('Veuillez remplir tous les champs requis pour le paiement.');
    }
});


            // Close Order Confirmation
            document.querySelector('.close-confirmation').addEventListener('click', function() {
                document.querySelector('.order-confirmation-modal').classList.remove('active');
            });

            // Close Checkout
            document.querySelectorAll('.close-checkout').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelector('.checkout-modal-step1').classList.remove('active');
                });
            });

            // Close All Modals
            document.querySelectorAll('.modal-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.modal-overlay').classList.remove('active');
                });
            });

            // Click outside modal to close
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('active');
                    }
                });
            });

            // Auth Modal
            const authModal = document.querySelector('.auth-modal');
            const authTabs = document.querySelectorAll('.auth-tab');
            const authContents = document.querySelectorAll('.auth-content');
            const switchToSignup = document.querySelector('.switch-to-signup');
            const switchToLogin = document.querySelector('.switch-to-login');

            // User button opens auth modal
            document.querySelector('.user-btn').addEventListener('click', function() {
                authModal.classList.add('active');
            });

            // Tab switching
            authTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Update tabs
                    authTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update contents
                    authContents.forEach(c => c.classList.remove('active'));
                    document.getElementById(`${tabId}-content`).classList.add('active');
                });
            });

            // Switch between login and signup
            switchToSignup.addEventListener('click', function(e) {
                e.preventDefault();
                authTabs[1].click();
            });

            switchToLogin.addEventListener('click', function(e) {
                e.preventDefault();
                authTabs[0].click();
            });

            // Signup Form
            document.getElementById('signup-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const firstName = document.getElementById('signup-fname').value;
                const lastName = document.getElementById('signup-lname').value;
                const email = document.getElementById('signup-email').value;
                const password = document.getElementById('signup-password').value;
                const confirmPassword = document.getElementById('signup-confirm-password').value;
                
                if (password !== confirmPassword) {
                    alert('Passwords do not match!');
                    return;
                }
                
                // In a real app, you would send this data to your server
                console.log('Signup data:', { firstName, lastName, email, password });
                
                // Show success message
                authModal.classList.remove('active');
                document.querySelector('.account-created-modal').classList.add('active');
                document.querySelector('.user-name').textContent = firstName;
                document.querySelector('.welcome-message').textContent = `Welcome to Vitrice&Joel, ${firstName}!`;
            });

            // Login Form
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('login-email').value;
                const password = document.getElementById('login-password').value;
                
                // In a real app, you would send this data to your server
                console.log('Login data:', { email, password });
                
                // For demo, just close the modal
                authModal.classList.remove('active');
                alert('Login successful! (This is a demo)');
            });

            // Close Account Created Modal
            document.querySelector('.close-account-created').addEventListener('click', function() {
                document.querySelector('.account-created-modal').classList.remove('active');
            });

            // Welcome Modal (shows after 10 seconds)
            setTimeout(function() {
                document.querySelector('.welcome-modal').classList.add('active');
            }, 10000);

            // Welcome Modal Buttons
            document.querySelector('.login-from-welcome').addEventListener('click', function() {
                document.querySelector('.welcome-modal').classList.remove('active');
                authModal.classList.add('active');
                authTabs[0].click();
            });

            document.querySelector('.signup-from-welcome').addEventListener('click', function() {
                document.querySelector('.welcome-modal').classList.remove('active');
                authModal.classList.add('active');
                authTabs[1].click();
            });

            // Promo Modal (shows after 20 seconds)
            setTimeout(function() {
                document.querySelector('.promo-modal').classList.add('active');
            }, 20000);

            // Promo Slider
            const promoSlides = document.querySelector('.promo-slides');
            const promoSlideItems = document.querySelectorAll('.promo-slide');
            const promoPrevBtn = document.querySelector('.promo-prev');
            const promoNextBtn = document.querySelector('.promo-next');
            const promoDots = document.querySelectorAll('.promo-dot');
            
            let currentPromoSlide = 0;
            const totalPromoSlides = promoSlideItems.length;
            
            function updatePromoSlider() {
                promoSlides.style.transform = `translateX(-${currentPromoSlide * 100}%)`;
                
                // Update dots
                promoDots.forEach((dot, index) => {
                    if (index === currentPromoSlide) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }
            
            promoNextBtn.addEventListener('click', function() {
                currentPromoSlide = (currentPromoSlide + 1) % totalPromoSlides;
                updatePromoSlider();
            });
            
            promoPrevBtn.addEventListener('click', function() {
                currentPromoSlide = (currentPromoSlide - 1 + totalPromoSlides) % totalPromoSlides;
                updatePromoSlider();
            });
            
            promoDots.forEach(dot => {
                dot.addEventListener('click', function() {
                    currentPromoSlide = parseInt(this.getAttribute('data-slide'));
                    updatePromoSlider();
                });
            });
            
            // Auto advance promo slides
            setInterval(function() {
                currentPromoSlide = (currentPromoSlide + 1) % totalPromoSlides;
                updatePromoSlider();
            }, 5000);

            // Testimonial Slider
            const testimonialSlides = document.querySelectorAll('.testimonial-slide');
            const testimonialDots = document.querySelectorAll('.testimonial-dot');
            
            let currentTestimonialSlide = 0;
            const totalTestimonialSlides = testimonialSlides.length;
            
            function updateTestimonialSlider() {
                testimonialSlides.forEach((slide, index) => {
                    if (index === currentTestimonialSlide) {
                        slide.classList.add('active');
                    } else {
                        slide.classList.remove('active');
                    }
                });
                
                // Update dots
                testimonialDots.forEach((dot, index) => {
                    if (index === currentTestimonialSlide) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }
            
            testimonialDots.forEach(dot => {
                dot.addEventListener('click', function() {
                    currentTestimonialSlide = parseInt(this.getAttribute('data-slide'));
                    updateTestimonialSlider();
                });
            });
            
            // Auto advance testimonial slides
            setInterval(function() {
                currentTestimonialSlide = (currentTestimonialSlide + 1) % totalTestimonialSlides;
                updateTestimonialSlider();
            }, 6000);

            // Chat Widget
            const chatButton = document.querySelector('.chat-button');
            const chatBox = document.querySelector('.chat-box');
            const chatClose = document.querySelector('.chat-close');
            const chatInput = document.querySelector('.chat-input input');
            const chatSend = document.querySelector('.chat-input button');
            const chatMessages = document.querySelector('.chat-messages');
            
            chatButton.addEventListener('click', function() {
                chatBox.classList.toggle('active');
            });
            
            chatClose.addEventListener('click', function() {
                chatBox.classList.remove('active');
            });
            
            chatSend.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            function sendMessage() {
                const message = chatInput.value.trim();
                if (message) {
                    // Add user message
                    addMessage(message, 'user');
                    chatInput.value = '';
                    
                    // Simulate bot response after a delay
                    setTimeout(() => {
                        const responses = [
                            "I can help you with any questions about our books or your order.",
                            "Our customer service team is available 24/7 to assist you.",
                            "You can find more information about shipping on our website.",
                            "Thank you for contacting Vitrice&Joel support!",
                            "Is there anything else I can help you with?"
                        ];
                        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                        addMessage(randomResponse, 'bot');
                    }, 1000);
                }
            }
            
            function addMessage(text, sender) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}`;
                messageDiv.innerHTML = `<div class="message-content">${text}</div>`;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Buy Now buttons
            document.querySelectorAll('.buy-now').forEach(button => {
                button.addEventListener('click', function() {
                    const bookCard = this.closest('.book-card, .scrolling-book');
                    const bookTitle = bookCard.querySelector('h3, h4').textContent;
                    const bookPrice = parseInt(bookCard.querySelector('.book-price, .scrolling-book-price').textContent.replace(/\D/g, ''));
                    const bookImg = bookCard.querySelector('img').src;

                    // Clear cart and add this single item
                    cart = [{
                        title: bookTitle,
                        price: bookPrice,
                        img: bookImg,
                        quantity: 1
                    }];

                    updateCart();
                    document.querySelector('.checkout-modal-step1').classList.add('active');
                });
            });

            // Animate elements on scroll
            function animateOnScroll() {
                const elements = document.querySelectorAll('.category-card, .book-card');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (elementPosition < screenPosition) {
                        element.classList.add('animate');
                    }
                });
            }

            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on load

            // Animate stats counting
            function animateStats() {
                const statNumbers = document.querySelectorAll('.stat-number');
                const duration = 2000; // Animation duration in ms
                const startTime = Date.now();
                
                statNumbers.forEach(stat => {
                    const target = parseInt(stat.getAttribute('data-count'));
                    const start = 0;
                    
                    function updateNumber() {
                        const now = Date.now();
                        const elapsed = now - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const current = Math.floor(progress * target);
                        
                        stat.textContent = current.toLocaleString();
                        
                        if (progress < 1) {
                            requestAnimationFrame(updateNumber);
                        } else {
                            stat.textContent = target.toLocaleString();
                        }
                    }
                    
                    updateNumber();
                });
            }

            // Intersection Observer for stats animation
            const statsSection = document.querySelector('.stats');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateStats();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            observer.observe(statsSection);

            // Newsletter Form
            document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const email = this.querySelector('input').value;
                alert(`Thank you for subscribing with ${email}!`);
                this.querySelector('input').value = '';
            });

            // Scrolling Books Animation
            const scrollingTrack = document.querySelector('.scrolling-track');
            let scrollPosition = 0;
            
            function animateScrollingBooks() {
                scrollPosition -= 1;
                if (scrollPosition < -scrollingTrack.scrollWidth / 2) {
                    scrollPosition = 0;
                }
                scrollingTrack.style.transform = `translateX(${scrollPosition}px)`;
                requestAnimationFrame(animateScrollingBooks);
            }
            
            animateScrollingBooks();
        });

        // Menu Mobile Toggle
document.querySelector('.menu-toggle').addEventListener('click', function() {
    document.querySelector('nav').classList.toggle('active');
});

// Animation au Scroll améliorée
function animateOnScroll() {
    const elements = document.querySelectorAll('[data-animate]');
    const windowHeight = window.innerHeight;
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementPosition < windowHeight - elementVisible) {
            element.classList.add('animate');
        }
    });
}

window.addEventListener('scroll', animateOnScroll);
animateOnScroll();
