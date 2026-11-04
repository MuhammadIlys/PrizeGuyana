
export const formatMoney = (value, code = 'USD') => {
    const amount = Number(value ?? 0);
    const currencyCode = String(code || 'USD').toUpperCase();
    const safeAmount = Number.isFinite(amount) ? amount : 0;

    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currencyCode,
            maximumFractionDigits: 2,
        }).format(safeAmount);
    } catch {
        return `${currencyCode} ${safeAmount.toFixed(2)}`;
    }
};
