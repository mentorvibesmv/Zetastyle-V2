/* LocalStorage cart */
(function () {
    const storageKey = "zetastyle_cart";

    function readCart() {
        try {
            return JSON.parse(localStorage.getItem(storageKey)) || [];
        } catch (error) {
            return [];
        }
    }

    function writeCart(cart) {
        localStorage.setItem(storageKey, JSON.stringify(cart));
        updateCartCount();
    }

    function formatMoney(value) {
        return `$${Number(value).toFixed(2)}`;
    }

    function updateCartCount() {
        const count = readCart().reduce((sum, item) => sum + item.quantity, 0);
        document.querySelectorAll("[data-cart-count]").forEach((node) => {
            node.textContent = String(count);
        });
    }

    function addItem(product) {
        const cart = readCart();
        const existing = cart.find((item) => item.id === product.id);

        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({ ...product, quantity: 1 });
        }

        writeCart(cart);
        if (window.ZetaStyle && window.ZetaStyle.showToast) {
            window.ZetaStyle.showToast(`${product.name} added to cart`);
        }
    }

    function bindAddButtons() {
        document.querySelectorAll(".add-to-cart").forEach((button) => {
            button.addEventListener("click", () => {
                addItem({
                    id: button.dataset.id,
                    name: button.dataset.name,
                    price: Number(button.dataset.price),
                    image: button.dataset.image,
                });
            });
        });
    }

    function setQuantity(id, quantity) {
        const cart = readCart()
            .map((item) => item.id === id ? { ...item, quantity } : item)
            .filter((item) => item.quantity > 0);
        writeCart(cart);
        renderCartPage();
    }

    function renderCartPage() {
        const wrap = document.querySelector("[data-cart-items]");
        const subtotalNode = document.querySelector("[data-cart-subtotal]");
        const totalNode = document.querySelector("[data-cart-total]");

        if (!wrap) {
            return;
        }

        const cart = readCart();
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        wrap.innerHTML = "";

        if (cart.length === 0) {
            wrap.innerHTML = '<div class="empty-cart">Your cart is empty. Add premium custom pieces from the shop.</div>';
        } else {
            cart.forEach((item) => {
                const row = document.createElement("article");
                row.className = "cart-row";
                row.innerHTML = `
                    <img src="${item.image}" alt="${item.name}">
                    <div>
                        <h2>${item.name}</h2>
                        <p>${formatMoney(item.price)}</p>
                    </div>
                    <div class="qty-controls">
                        <button type="button" data-decrease="${item.id}" aria-label="Decrease quantity">-</button>
                        <strong>${item.quantity}</strong>
                        <button type="button" data-increase="${item.id}" aria-label="Increase quantity">+</button>
                        <button class="remove-item" type="button" data-remove="${item.id}">Remove</button>
                    </div>
                `;
                wrap.appendChild(row);
            });
        }

        if (subtotalNode) {
            subtotalNode.textContent = formatMoney(subtotal);
        }

        if (totalNode) {
            totalNode.textContent = formatMoney(subtotal);
        }

        wrap.querySelectorAll("[data-decrease]").forEach((button) => {
            button.addEventListener("click", () => {
                const item = readCart().find((cartItem) => cartItem.id === button.dataset.decrease);
                if (item) {
                    setQuantity(item.id, item.quantity - 1);
                }
            });
        });

        wrap.querySelectorAll("[data-increase]").forEach((button) => {
            button.addEventListener("click", () => {
                const item = readCart().find((cartItem) => cartItem.id === button.dataset.increase);
                if (item) {
                    setQuantity(item.id, item.quantity + 1);
                }
            });
        });

        wrap.querySelectorAll("[data-remove]").forEach((button) => {
            button.addEventListener("click", () => setQuantity(button.dataset.remove, 0));
        });
    }

    const clearButton = document.querySelector("[data-clear-cart]");
    if (clearButton) {
        clearButton.addEventListener("click", () => {
            writeCart([]);
            renderCartPage();
        });
    }

    bindAddButtons();
    updateCartCount();
    renderCartPage();
    window.bindAddButtons = bindAddButtons;
})();
