$(function () {
    const calculatorForm = $('#calculator-form');
    const resultContainer = $('#calculator-result');
    const monthlyRate = parseFloat(calculatorForm.data('monthly-rate'));

    calculatorForm.on('input change', function () {
        const amount = parseFloat($('#loanAmount').val());
        const term = parseInt($('#loanTerm').val(), 10);

        if (!isNaN(amount) && !isNaN(term) && amount > 0 && term > 0) {
            const total = amount * (1 + monthlyRate * term);
            const monthlyPayment = total / term;
            resultContainer.find('.result-value').text(monthlyPayment.toFixed(2));
            resultContainer.removeClass('d-none');
        } else {
            resultContainer.addClass('d-none');
        }
    });
});
