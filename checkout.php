<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('Checkout', 'Complete your custom clothing order and send details to our WhatsApp studio.');
require_once __DIR__ . '/includes/header.php';

$shipping_tn = (float) getSetting('shipping_tn', '50.00');
$shipping_other = (float) getSetting('shipping_other', '100.00');
$cod_charge = (float) getSetting('cod_charge', '40.00');
?>
<section class="page-hero compact">
    <div class="container">
        <p class="eyebrow">Checkout details</p>
        <h1>Complete Order</h1>
        <p>Enter your shipping address and select payment to submit your order enquiry directly to our WhatsApp studio.</p>
    </div>
</section>

<section class="section">
    <div class="container cart-layout" style="align-items: start;">
        <form class="contact-form reveal" id="checkout-form" style="gap: 20px;">
            <h2>Delivery Address</h2>
            
            <div class="form-row">
                <label for="name">Customer Name</label>
                <input id="name" name="name" type="text" placeholder="Your full name" required>
            </div>
            
            <div class="form-row">
                <label for="phone">Phone Number</label>
                <input id="phone" name="phone" type="tel" placeholder="e.g. +91 98765 43210" required>
            </div>
            
            <div class="form-row">
                <label for="address">Street Address</label>
                <textarea id="address" name="address" rows="3" placeholder="Apartment, building, street..." required></textarea>
            </div>
            
            <div class="form-row">
                <label for="city">City</label>
                <input id="city" name="city" type="text" placeholder="Your city" required>
            </div>
            
            <div class="form-row">
                <label for="state">State</label>
                <select id="state" name="state" required style="width: 100%; border: 1px solid var(--border); border-radius: 14px; padding: 14px 16px; background: var(--secondary); outline: none;">
                    <option value="" disabled selected>Select State</option>
                    <option value="Tamil Nadu">Tamil Nadu</option>
                    <option value="Andhra Pradesh">Andhra Pradesh</option>
                    <option value="Karnataka">Karnataka</option>
                    <option value="Kerala">Kerala</option>
                    <option value="Maharashtra">Maharashtra</option>
                    <option value="Delhi">Delhi</option>
                    <option value="Other">Other State</option>
                </select>
            </div>
            
            <div class="form-row">
                <label for="pincode">Pincode</label>
                <input id="pincode" name="pincode" type="text" placeholder="6-digit pincode" required>
            </div>
            
            <div class="form-row" style="margin-top: 10px;">
                <label>Payment Method</label>
                <div style="display: flex; gap: 20px; margin-top: 5px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="radio" name="payment_method" value="COD" checked style="width: 18px; height: 18px;">
                        Cash on Delivery (COD)
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="radio" name="payment_method" value="Online" style="width: 18px; height: 18px;">
                        Pay Online (0 Extra Fee)
                    </label>
                </div>
            </div>
            
            <button class="btn btn-dark" type="submit" style="margin-top: 15px; min-height: 52px; font-size: 1rem;">
                Place Order via WhatsApp
            </button>
            <p class="form-status" id="checkout-status" style="font-weight: 600; text-align: center; margin-top: 10px;"></p>
        </form>

        <aside class="cart-summary" style="height: auto;">
            <h2>Order Summary</h2>
            <div style="display: flex; flex-direction: column; gap: 12px; margin: 20px 0; max-height: 240px; overflow-y: auto; padding-right: 8px;" id="checkout-items-list">
                <!-- Javascript builds list -->
            </div>
            <hr style="border: 0; border-top: 1px solid var(--border); margin: 15px 0;">
            <div class="summary-line">
                <span>Subtotal</span>
                <strong id="summary-subtotal">$0.00</strong>
            </div>
            <div class="summary-line">
                <span>Shipping Charge</span>
                <strong id="summary-shipping">$0.00</strong>
            </div>
            <div class="summary-line" id="cod-fee-row">
                <span>COD Charge</span>
                <strong id="summary-cod">$0.00</strong>
            </div>
            <hr style="border: 0; border-top: 1px solid var(--border); margin: 15px 0;">
            <div class="summary-total">
                <span>Grand Total</span>
                <strong id="summary-total">$0.00</strong>
            </div>
        </aside>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Dynamic settings from PHP
    const SHIPPING_TN = <?= $shipping_tn; ?>;
    const SHIPPING_OTHER = <?= $shipping_other; ?>;
    const COD_CHARGE = <?= $cod_charge; ?>;
    const currency = '<?= CURRENCY; ?>';

    const checkoutForm = document.getElementById('checkout-form');
    const itemsList = document.getElementById('checkout-items-list');
    const subtotalNode = document.getElementById('summary-subtotal');
    const shippingNode = document.getElementById('summary-shipping');
    const codNode = document.getElementById('summary-cod');
    const codRow = document.getElementById('cod-fee-row');
    const totalNode = document.getElementById('summary-total');
    const statusNode = document.getElementById('checkout-status');

    const stateSelect = document.getElementById('state');
    const paymentRadios = document.getElementsByName('payment_method');

    // Read cart
    function getCart() {
        try {
            return JSON.parse(localStorage.getItem('zetastyle_cart')) || [];
        } catch (e) {
            return [];
        }
    }

    const cart = getCart();
    if (cart.length === 0) {
        window.location.href = 'cart.php';
        return;
    }

    // Build items list in summary
    itemsList.innerHTML = '';
    let subtotal = 0;
    cart.forEach(item => {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.justifyContent = 'space-between';
        row.style.alignItems = 'center';
        row.style.fontSize = '0.9rem';
        row.style.color = 'var(--muted)';
        row.innerHTML = `
            <span>${item.name} <strong>x${item.quantity}</strong></span>
            <span>${currency}${(item.price * item.quantity).toFixed(2)}</span>
        `;
        itemsList.appendChild(row);
        subtotal += item.price * item.quantity;
    });

    // Calculate totals dynamically
    function calculateTotals() {
        const state = stateSelect.value;
        let shipping = 0;
        if (state) {
            shipping = (state === 'Tamil Nadu') ? SHIPPING_TN : SHIPPING_OTHER;
        }

        let cod = 0;
        let isCod = false;
        for (const radio of paymentRadios) {
            if (radio.checked && radio.value === 'COD') {
                cod = COD_CHARGE;
                isCod = true;
            }
        }

        if (isCod) {
            codRow.style.display = 'flex';
        } else {
            codRow.style.display = 'none';
        }

        const grandTotal = subtotal + shipping + cod;

        subtotalNode.textContent = `${currency}${subtotal.toFixed(2)}`;
        shippingNode.textContent = state ? `${currency}${shipping.toFixed(2)}` : 'Select State';
        codNode.textContent = `${currency}${cod.toFixed(2)}`;
        totalNode.textContent = `${currency}${grandTotal.toFixed(2)}`;
    }

    // Bind event listeners
    stateSelect.addEventListener('change', calculateTotals);
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', calculateTotals);
    });

    calculateTotals(); // Initial call

    // Handle form submit
    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!stateSelect.value) {
            statusNode.style.color = 'red';
            statusNode.textContent = 'Please select a shipping state.';
            return;
        }

        const formData = new FormData(checkoutForm);
        const name = formData.get('name');
        const phone = formData.get('phone');
        const address = formData.get('address');
        const city = formData.get('city');
        const state = formData.get('state');
        const pincode = formData.get('pincode');
        const payment_method = formData.get('payment_method');

        statusNode.style.color = 'var(--accent)';
        statusNode.textContent = 'Processing order details...';

        try {
            const response = await fetch('api/enquiry.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name,
                    phone,
                    address,
                    city,
                    state,
                    pincode,
                    payment_method,
                    cart
                })
            });

            const result = await response.json();
            if (result.success) {
                // Clear cart in local storage
                localStorage.removeItem('zetastyle_cart');
                
                statusNode.style.color = '#25d366';
                statusNode.textContent = 'Redirecting to WhatsApp to complete order...';
                
                // Open WhatsApp link in a new window/tab
                window.location.href = result.whatsapp_url;
            } else {
                statusNode.style.color = 'red';
                statusNode.textContent = result.message || 'An error occurred. Please try again.';
            }
        } catch (error) {
            statusNode.style.color = 'red';
            statusNode.textContent = 'Failed to submit order. Please check connection and try again.';
        }
    });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
