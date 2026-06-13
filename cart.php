<?php
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta('Cart', 'Review your ZetaStyle shopping cart and update custom clothing quantities.');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <div class="container">
        <p class="eyebrow">Shopping bag</p>
        <h1>Your Cart</h1>
        <p>Review quantities before sending your custom clothing order to checkout.</p>
    </div>
</section>
<section class="section">
    <div class="container cart-layout">
        <div class="cart-items" data-cart-items></div>
        <aside class="cart-summary">
            <h2>Order Summary</h2>
            <div class="summary-line"><span>Subtotal</span><strong data-cart-subtotal>$0.00</strong></div>
            <div class="summary-line"><span>Estimated Shipping</span><strong>Calculated later</strong></div>
            <div class="summary-total"><span>Total</span><strong data-cart-total>$0.00</strong></div>
            <a class="btn btn-dark" href="checkout.php">Request Checkout</a>
            <button class="btn btn-light clear-cart" type="button" data-clear-cart>Empty Cart</button>
        </aside>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
