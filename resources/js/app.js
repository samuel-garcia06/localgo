const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
})[character]);

const feedbackTimers = new Map();

const resetTimer = (key, callback, delay) => {
    window.clearTimeout(feedbackTimers.get(key));
    feedbackTimers.set(key, window.setTimeout(() => {
        feedbackTimers.delete(key);
        callback();
    }, delay));
};

const showCartToast = ({ cartTotal }) => {
    const existingToast = document.querySelector('[data-cart-toast]');
    existingToast?.remove();

    const toast = document.createElement('div');
    toast.dataset.cartToast = 'true';
    toast.className = 'cart-toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.innerHTML = `
        <span class="cart-toast-icon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
        <span class="min-w-0">
            <span class="block truncate text-sm font-bold text-[var(--fastbite-ink)]">Producto añadido al carrito</span>
            <span class="block truncate text-xs text-[var(--fastbite-muted)]">Total actual: ${escapeHtml(cartTotal || '')}</span>
        </span>
    `;

    document.body.appendChild(toast);

    window.setTimeout(() => {
        toast.classList.add('cart-toast-exit');
        window.setTimeout(() => toast.remove(), 220);
    }, 2400);
};

// Animates the product card and cart header elements after server confirms the add.
// The "Añadido" button state is handled instantly by Alpine.js on click.
const animateCartFeedback = ({ productId, cartTotal }) => {
    const card = document.querySelector(`[data-product-card="${productId}"]`);
    const cartButton = document.querySelector('[data-cart-button]');
    const cartBadge = document.querySelector('[data-cart-count]');
    const cartTotalElement = document.querySelector('[data-cart-total]');

    card?.classList.remove('product-card-added');
    cartButton?.classList.remove('cart-button-highlight');
    cartBadge?.classList.remove('cart-badge-bounce');
    cartTotalElement?.classList.remove('cart-total-flash');

    requestAnimationFrame(() => {
        card?.classList.add('product-card-added');
        cartButton?.classList.add('cart-button-highlight');
        cartBadge?.classList.add('cart-badge-bounce');
        cartTotalElement?.classList.add('cart-total-flash');
    });

    showCartToast({ cartTotal });

    resetTimer(`product-${productId}`, () => {
        card?.classList.remove('product-card-added');
    }, 950);

    resetTimer('cart-feedback', () => {
        cartButton?.classList.remove('cart-button-highlight');
        cartBadge?.classList.remove('cart-badge-bounce');
        cartTotalElement?.classList.remove('cart-total-flash');
    }, 950);
};

// Handle Livewire's dispatched 'product-added' event.
// detail can be an object or, in some Livewire versions, an array with one object.
window.addEventListener('product-added', (event) => {
    const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
    if (detail) animateCartFeedback(detail);
});
