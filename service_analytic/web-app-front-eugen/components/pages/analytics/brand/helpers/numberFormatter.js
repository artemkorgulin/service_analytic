export function numberFormatter(value) {
    const sansDec = Number(value).toFixed(0);
    return sansDec.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}
