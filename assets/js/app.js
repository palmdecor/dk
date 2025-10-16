$(function () {
    const calculatorForm = $('#calculator-form');
    if (!calculatorForm.length) {
        return;
    }

    const resultContainer = $('#calculator-result');
    const resultTable = $('#calculator-table');
    const monthlyRate = parseFloat(calculatorForm.data('monthly-rate')) || 0;
    const feeRate = parseFloat(calculatorForm.data('fee-rate')) || 0;
    const fieldMap = {
        interest: resultTable.find('[data-field="interest-rate"]'),
        totalInterest: resultTable.find('[data-field="total-interest"]'),
        monthlyPayment: resultTable.find('[data-field="monthly-payment"]'),
        totalPayment: resultTable.find('[data-field="total-payment"]'),
        annualRate: resultTable.find('[data-field="annual-rate"]'),
        feeAmount: resultTable.find('[data-field="fee-amount"]'),
    };

    const currencyFormatter = new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        maximumFractionDigits: 2,
    });

    const percentFormatter = (value) => `${(value * 100).toFixed(2)}%`;

    const updateTable = () => {
        const amount = parseFloat($('#loanAmount').val());
        const term = parseInt($('#loanTerm').val(), 10);

        if (isNaN(amount) || isNaN(term) || amount <= 0 || term <= 0) {
            resultContainer.addClass('d-none');
            resultTable.addClass('d-none');
            return;
        }

        const totalInterest = amount * monthlyRate * term;
        const totalPayment = amount + totalInterest;
        const monthlyPayment = totalPayment / term;
        const annualRate = monthlyRate * 12;
        const feeAmount = amount * feeRate;

        fieldMap.interest.text(percentFormatter(monthlyRate));
        fieldMap.totalInterest.text(currencyFormatter.format(totalInterest));
        fieldMap.monthlyPayment.text(currencyFormatter.format(monthlyPayment));
        fieldMap.totalPayment.text(currencyFormatter.format(totalPayment));
        fieldMap.annualRate.text(percentFormatter(annualRate));
        fieldMap.feeAmount.text(currencyFormatter.format(feeAmount));

        resultContainer.find('.result-value').text(currencyFormatter.format(monthlyPayment));
        resultContainer.removeClass('d-none');
        resultTable.removeClass('d-none');
    };

    calculatorForm.on('input change', updateTable);
    fieldMap.interest.text(percentFormatter(monthlyRate));
});
