<?php
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 2rem; text-align: center;">
        <h1 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">Contact UrbanFit BD</h1>
        <p style="color: var(--text-muted);">Get in touch with our customer service and local shipping department.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem;">
            <h3 style="font-family: var(--font-heading); font-weight: 700; margin-bottom: 1.25rem; color: #fff;">Store Contact Details</h3>
            
            <div style="margin-bottom: 1.25rem;">
                <strong style="color: var(--accent-emerald); display: block; font-size: 0.9rem;">📍 Flagship Store Address</strong>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Level 4, Urban Tower, Banani Road 11, Dhaka-1213, Bangladesh</p>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <strong style="color: var(--accent-cyan); display: block; font-size: 0.9rem;">📞 Customer Helpline</strong>
                <p style="color: var(--text-muted); font-size: 0.95rem;">+880 1700-000000 / +880 1800-000000</p>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <strong style="color: var(--accent-amber); display: block; font-size: 0.9rem;">✉️ Support Email</strong>
                <p style="color: var(--text-muted); font-size: 0.95rem;">support@urbanfit.bd</p>
            </div>

            <div>
                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">🚚 EMS Local Courier Shipping</strong>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Flat Rate ৳120 across all 64 districts in Bangladesh.</p>
            </div>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem;">
            <h3 style="font-family: var(--font-heading); font-weight: 700; margin-bottom: 1.25rem; color: #fff;">Send Us a Message</h3>
            
            <form onsubmit="alert('Thank you! Your message has been submitted to UrbanFit BD customer support.'); return false;">
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" required class="form-control" placeholder="Md. Golam Maula">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" required class="form-control" placeholder="user@domain.com">
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea required class="form-control" rows="4" placeholder="How can we help you?"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    Submit Inquiry →
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
