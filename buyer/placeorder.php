<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

$query = "SELECT c.product_id, c.quantity, p.name, p.price, p.image_url 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.buyer_id = $buyer_id";
$result = mysqli_query($conn, $query);

$cart_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cart_items[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - AgriTrade</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .cart-container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .cart-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd; }
        .cart-item img { width: 60px; height: 60px; object-fit: cover; margin-right: 10px; border-radius: 4px; }
        .cart-details { flex-grow: 1; }
        .place-order-btn { background-color: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 20px; font-size: 16px; border-radius: 4px; }
        .place-order-btn:hover { background-color: #218838; }
        .message { margin-top: 20px; font-weight: bold; }
        /* Modal Styles */
        #paymentModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 999;
        }
        #paymentModal .modal-content {
            background: white;
            width: 400px;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            position: relative;
        }
        #paymentModal input {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        #paymentModal button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #submitBtn { background: #28a745; color: white; }
        #cancelBtn { margin-left: 10px; background: #ccc; }
    </style>
</head>
<body>

<div class="cart-container">
    <h2><i class="fas fa-shopping-cart"></i> Review Your Cart</h2>

    <?php if (count($cart_items) === 0): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <img src="../<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                <div class="cart-details">
                    <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                    Price: Rs.<?= number_format($item['price'], 2) ?><br>
                    Quantity: <?= (int)$item['quantity'] ?>
                </div>
                <div>Subtotal: Rs.<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
            </div>
        <?php endforeach; ?>

        <button class="place-order-btn" onclick="openModal()">Checkout</button>
        <div id="response" class="message"></div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal">
  <div class="modal-content">
    <h3>Enter Payment Details</h3>
    <label>Payment Method:</label>
    <input type="text" id="payment_method" placeholder="e.g. Bank Transfer">
    <label>Payment Reference:</label>
    <input type="text" id="payment_reference" placeholder="e.g. TXN12345678">
    <button id="submitBtn" onclick="submitPayment()">Submit Payment</button>
    <button id="cancelBtn" onclick="closeModal()">Cancel</button>
  </div>
</div>

<script>
function openModal() {
    document.getElementById('paymentModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function submitPayment() {
    const method = document.getElementById('payment_method').value.trim();
    const reference = document.getElementById('payment_reference').value.trim();

    if (!method || !reference) {
        alert("Please fill in both payment method and reference.");
        return;
    }

    fetch('checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            buyer_id: <?= json_encode($buyer_id) ?>,
            payment_method: method,
            payment_reference: reference
        })
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('response');
        if (data.success) {
            msg.innerHTML = "✅ " + data.message + " (Order ID: " + data.order_id + ")";
            msg.style.color = "green";
            setTimeout(() => window.location.href = 'orders.php', 2000);
        } else {
            msg.innerHTML = "❌ " + data.message;
            msg.style.color = "red";
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('response').innerHTML = "❌ Error placing order. Please try again.";
    });

    closeModal();
}
</script>

</body>
</html>
