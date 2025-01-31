document.addEventListener('DOMContentLoaded', function() {
    const scaleButtons = document.querySelectorAll('.scale-btn');
    const amounts = document.querySelectorAll('.amount');

    function formatAmount(value) {
        // Convert to fraction if needed
        if (value === 0.25) return '¼';
        if (value === 0.5) return '½';
        if (value === 0.75) return '¾';
        if (value === 0.33) return '⅓';
        if (value === 0.67) return '⅔';
        if (value === 1.25) return '1¼';
        if (value === 1.5) return '1½';
        if (value === 1.75) return '1¾';
        
        // For other values, round to 2 decimal places if needed
        return Number.isInteger(value) ? value.toString() : value.toFixed(2);
    }

    function updateAmounts(scale) {
        amounts.forEach(amount => {
            const baseAmount = parseFloat(amount.dataset.base);
            const scaledAmount = baseAmount * scale;
            amount.textContent = formatAmount(scaledAmount);
        });
    }

    scaleButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            scaleButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get scale value and update amounts
            const scale = parseFloat(this.dataset.scale);
            updateAmounts(scale);
        });
    });
});
