// Открытие/закрытие мобильного меню
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }
    
    // Обработка количества товаров в корзине
    const quantityInputs = document.querySelectorAll('.item-quantity input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
    
    // Обработка выбора размера
    const sizeOptions = document.querySelectorAll('.size-option');
    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            const input = this.querySelector('input');
            if (input) {
                input.checked = true;
                sizeOptions.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
            }
        });
    });
    
    // Подтверждение удаления
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        if (button.type === 'submit') {
            button.addEventListener('click', function(e) {
                if (!confirm('Вы уверены?')) {
                    e.preventDefault();
                }
            });
        }
    });
});

// Управление корзиной для неавторизованных пользователей
class CartManager {
    constructor() {
        this.cartKey = 'cart';
        this.cart = this.getCart();
    }
    
    getCart() {
        const cart = localStorage.getItem(this.cartKey);
        return cart ? JSON.parse(cart) : { items: [] };
    }
    
    saveCart() {
        localStorage.setItem(this.cartKey, JSON.stringify(this.cart));
    }
    
    addItem(productId, sizeId, quantity = 1) {
        const existingItem = this.cart.items.find(
            item => item.product_id === productId && item.size_id === sizeId
        );
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.items.push({
                product_id: productId,
                size_id: sizeId,
                quantity: quantity
            });
        }
        
        this.saveCart();
        this.updateCartCount();
    }
    
    removeItem(productId, sizeId) {
        this.cart.items = this.cart.items.filter(
            item => !(item.product_id === productId && item.size_id === sizeId)
        );
        this.saveCart();
        this.updateCartCount();
    }
    
    updateQuantity(productId, sizeId, quantity) {
        const item = this.cart.items.find(
            item => item.product_id === productId && item.size_id === sizeId
        );
        
        if (item) {
            item.quantity = quantity;
            if (item.quantity <= 0) {
                this.removeItem(productId, sizeId);
            } else {
                this.saveCart();
            }
        }
        
        this.updateCartCount();
    }
    
    clearCart() {
        this.cart.items = [];
        this.saveCart();
        this.updateCartCount();
    }
    
    updateCartCount() {
        const count = this.cart.items.reduce((total, item) => total + item.quantity, 0);
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
        }
        
        // Обновляем сессию для сервера
        document.cookie = `cart=${JSON.stringify(this.cart)}; path=/`;
    }
    
    getCartCount() {
        return this.cart.items.reduce((total, item) => total + item.quantity, 0);
    }
}

// Инициализация менеджера корзины
if (typeof cartManager === 'undefined') {
    const cartManager = new CartManager();
    
    // Обновляем счетчик при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        cartManager.updateCartCount();
    });
    
    // Экспортируем для использования в других файлах
    window.cartManager = cartManager;
}