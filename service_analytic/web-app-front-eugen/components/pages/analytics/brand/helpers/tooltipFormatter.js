export function tooltipFormatter(params) {
    const {
        column: { actualWidth },
        value,
    } = params;
    if (value.length * 10 > actualWidth) {
        return value;
    }
}
