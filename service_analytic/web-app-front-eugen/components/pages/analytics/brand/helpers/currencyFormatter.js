export function currencyFormatter(currency, sign) {
    const sansDec = Number(currency).toFixed(0);
    const formatted = sansDec.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    return `${formatted} ${sign}`;
}
