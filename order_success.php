<?php
require 'auth_check.php';
require 'db.php';

$txn = $_GET['txn'] ?? null;

if (!$txn) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Order Successful";
include 'includes/header.php';
?>

<div class="container text-center" style="margin-top: 4rem; margin-bottom: 4rem;">
    <div class="card p-8" style="max-width: 500px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
        <div style="font-size: 5rem; color: var(--success-color); margin-bottom: 1.5rem; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">✅</div>
        <h1 class="mb-sm">Order Confirmed!</h1>
        <p class="text-muted mb-lg">Thank you for your purchase. Your order has been securely processed and sent to the respective farmers for fulfillment.</p>
        
        <div class="p-6 mb-lg w-full" style="background: #f8fbf8; border-radius: var(--radius-md); border: 2px dashed #c8e6c9;">
            <p class="text-xs text-muted mb-sm" style="text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Transaction Reference</p>
            <h3 class="m-0" style="font-family: 'JetBrains Mono', 'Courier New', monospace; color: var(--primary-dark);"><?php echo htmlspecialchars($txn); ?></h3>
        </div>
        
        <div class="grid-2 w-full">
            <a href="my_orders.php" class="btn btn-outline">My Orders</a>
            <a href="user_dashboard.php" class="btn btn-primary">Marketplace</a>
        </div>
    </div>
</div>

<!-- Post-Order Feedback Modal -->
<div id="feedbackModal" class="modal-backdrop">
    <div class="modal-content">
        <button class="modal-close" onclick="closeFeedbackModal()">&times;</button>
        <div style="text-align:center; font-size:2.5rem; margin-bottom:0.5rem;">🌟</div>
        <h2 class="text-center" style="margin-bottom:0.25rem;">Rate Your Experience</h2>
        <p class="text-muted text-center mb-lg">How was your ordering experience today? Your feedback helps us grow.</p>
        
        <form action="submit_feedback.php" method="POST" id="modalFeedbackForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="redirect" value="order_success.php?txn=<?php echo htmlspecialchars($txn); ?>">
            
            <div class="star-rating">
                <input type="radio" id="m_star5" name="rating" value="5">
                <label for="m_star5" title="5 stars">&#9733;</label>
                <input type="radio" id="m_star4" name="rating" value="4">
                <label for="m_star4" title="4 stars">&#9733;</label>
                <input type="radio" id="m_star3" name="rating" value="3">
                <label for="m_star3" title="3 stars">&#9733;</label>
                <input type="radio" id="m_star2" name="rating" value="2">
                <label for="m_star2" title="2 stars">&#9733;</label>
                <input type="radio" id="m_star1" name="rating" value="1">
                <label for="m_star1" title="1 star">&#9733;</label>
            </div>

            <div class="form-group">
                <label for="modalMessage">Tell us more <span style="color:#aaa; font-weight:400;">(optional)</span></label>
                <textarea id="modalMessage" name="message" class="form-control" rows="3" placeholder="E.g. Easy to use, great variety of crops..."></textarea>
            </div>

            <div style="display:flex; gap:1rem;">
                <button type="button" class="btn btn-outline" onclick="closeFeedbackModal()" style="flex:1;">Skip</button>
                <button type="submit" class="btn btn-primary" id="modalFeedbackSubmit" style="flex:2;">Submit Feedback</button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes popIn {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<script>
// Auto-open feedback modal after 1.2 seconds so user can see the success message first
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        if (typeof openFeedbackModal === 'function') {
            openFeedbackModal();
        }
    }, 1200);
});

// Loading state on submit (specific to this form)
var form = document.getElementById('modalFeedbackForm');
if (form) {
    form.addEventListener('submit', function() {
        var btn = document.getElementById('modalFeedbackSubmit');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Sending...';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>

