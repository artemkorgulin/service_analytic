export default {
    beforeDraw: chart => {
        if (chart.tooltip?._active?.length) {
            const x = chart.tooltip._active[0].element.x;
            const yAxis = chart.scales.y;
            const ctx = chart.ctx;
            ctx.save();
            ctx.beginPath();
            ctx.moveTo(x, yAxis.top);
            ctx.lineTo(x, yAxis.bottom);
            ctx.lineWidth = 1;
            ctx.setLineDash([5, 3]);
            ctx.strokeStyle = 'rgba(0, 0, 0, 1)';
            ctx.stroke();
            ctx.restore();
        }
    },
};
