document.addEventListener('DOMContentLoaded', function () {
    // Payment method selector toggle
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const mobileBankingDetails = document.getElementById('mobile-banking-details');
    const paymentCards = document.querySelectorAll('.payment-card');

    if (paymentRadios.length > 0) {
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                paymentCards.forEach(card => card.classList.remove('selected'));
                this.closest('.payment-card').classList.add('selected');

                if (this.value.includes('Mobile Banking')) {
                    if (mobileBankingDetails) mobileBankingDetails.style.display = 'block';
                } else {
                    if (mobileBankingDetails) mobileBankingDetails.style.display = 'none';
                }
            });
        });
    }

    // Client-side Add to Cart Form Size Check
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function (e) {
            const sizeInputs = document.querySelectorAll('input[name="selected_size"]');
            let sizeSelected = false;

            if (sizeInputs.length === 0) {
                // Products like accessories with no size radio or pre-selected
                return true;
            }

            sizeInputs.forEach(input => {
                if (input.checked) sizeSelected = true;
            });

            if (!sizeSelected) {
                e.preventDefault();
                alert('⚠️ Validation Error: You MUST select a size (S, M, L, or XL) before adding this item to your cart!');
                return false;
            }
        });
    }
});
