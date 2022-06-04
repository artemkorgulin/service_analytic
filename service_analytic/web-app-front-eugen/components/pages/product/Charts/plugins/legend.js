const charts = [];
let isLegendHidden = true;

const getOrCreateLegendList = (chart, id) => {
    const legendContainer = document.getElementById(id);
    const { type } = chart.config;
    if (legendContainer) {
        let listContainer = legendContainer.querySelector('ul');

        if (!listContainer) {
            listContainer = document.createElement('ul');
            listContainer.style.display = 'grid';

            if (type === 'pie' || type === 'doughnut') {
                listContainer.style.gridTemplateColumns = '1fr';
                listContainer.style.gap = '8px';
            } else {
                listContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
                listContainer.style.gap = '16px';
            }

            listContainer.style.gridTemplateRows = 'auto';
            listContainer.style.margin = '16px 0';
            listContainer.style.padding = '0 40px';

            legendContainer.appendChild(listContainer);
        }

        return listContainer;
    }
};

export const htmlLegendPlugin = {
    id: 'htmlLegend',
    afterUpdate(chart, args, options) {
        const ul = getOrCreateLegendList(chart, options.containerID);
        const { type } = chart.config;

        if (!charts.some(({ id }) => chart.id === id)) {
            charts.push(chart);
        }

        if (ul) {
            // Remove old legend items
            while (ul.firstChild) {
                ul.firstChild.remove();
            }

            // Reuse the built-in legendItems generator
            const items = chart.options.plugins.legend.labels.generateLabels(chart);

            // Show legend for 10 items only
            if ((type !== 'pie' || type !== 'doughnut') && isLegendHidden) {
                items.forEach(item => {
                    if (item.datasetIndex > 9) {
                        chart.setDatasetVisibility(item.datasetIndex, false);
                    }
                });
                isLegendHidden = false;
            }

            items.forEach(item => {
                const li = document.createElement('li');
                li.style.alignItems = 'center';
                li.style.cursor = 'pointer';
                li.style.display = 'flex';
                li.style.flexDirection = 'row';
                li.style.marginLeft = '10px';

                li.onclick = () => {
                    if (type === 'pie' || type === 'doughnut') {
                        // Pie and doughnut charts only have a single dataset and visibility is per item
                        charts.forEach(chartItem => {
                            chartItem.toggleDataVisibility(item.index);
                            chartItem.update();
                        });
                    } else {
                        charts.forEach(chartItem => {
                            chartItem.setDatasetVisibility(
                                item.datasetIndex,
                                !chartItem.isDatasetVisible(item.datasetIndex)
                            );
                        });
                    }
                    chart.update();
                };

                // Color box
                const boxSpan = document.createElement('span');
                boxSpan.style.background = item.fillStyle;
                boxSpan.style.borderColor = item.strokeStyle;
                boxSpan.style.borderWidth = item.lineWidth + 'px';
                boxSpan.style.display = 'inline-block';

                if (type === 'pie' || type === 'doughnut') {
                    boxSpan.style.height = '12px';
                    boxSpan.style.width = '12px';
                    boxSpan.style.minWidth = '12px';
                    boxSpan.style.borderRadius = '50%';
                } else {
                    boxSpan.style.height = '12px';
                    boxSpan.style.width = '32px';
                    boxSpan.style.minWidth = '32px';
                    boxSpan.style.borderRadius = '4px';
                }

                boxSpan.style.marginRight = '8px';

                // Text
                const textContainer = document.createElement('p');
                textContainer.style.color = item.fontColor;
                textContainer.style.fontSize = '12px';
                textContainer.style.lineHeight = '16px';
                if (type === 'pie' || type === 'doughnut') {
                    textContainer.style.maxWidth = '328px';
                }
                textContainer.style.margin = 0;
                textContainer.style.padding = 0;
                textContainer.style.textDecoration = item.hidden ? 'line-through' : '';
                textContainer.style.textDecorationThickness = '2px';

                const text = document.createTextNode(item.text);
                textContainer.appendChild(text);

                li.appendChild(boxSpan);
                li.appendChild(textContainer);
                ul.appendChild(li);
            });
        }
    },
};
