import { plural } from '~utils/text.utils';

export const externalTooltipHandler = context => {
    // Tooltip Element
    let tooltipEl = document.getElementById('chart-tooltip');

    // Create element on first render
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chart-tooltip';
        tooltipEl.innerHTML = '<section></section>';
        document.body.appendChild(tooltipEl);
    }

    // Hide if no tooltip
    const tooltipModel = context.tooltip;
    if (tooltipModel.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }

    // Set caret Position
    tooltipEl.classList.remove('above', 'below', 'no-transform');
    if (tooltipModel.yAlign) {
        tooltipEl.classList.add(tooltipModel.yAlign);
    } else {
        tooltipEl.classList.add('no-transform');
    }

    function getBody(bodyItem) {
        return bodyItem.lines;
    }

    function splitText(bodyLine) {
        return bodyLine.map(line => {
            const lineParts = line.split(':');
            const text = lineParts[0].trim();
            const value = lineParts[1].trim().replace(/,/g, ' ');
            return {
                text,
                value,
            };
        });
    }

    // Define chart type
    const { type } = context.chart.config;

    // Define if chart for dashboard
    const dashboardTooltip = tooltipModel.dataPoints[0].dataset.dashboard;

    // Set Content
    if (tooltipModel.body) {
        const titleLines = tooltipModel.title || [];
        const bodyLines = tooltipModel.body.map(getBody);

        let innerHtml = '';

        if (titleLines && titleLines.length > 0) {
            titleLines.forEach(function (title) {
                innerHtml += `<div style="color: #fff; margin-top: 8px; font-weight: 500">${title}</div>`;
            });
        }

        bodyLines.forEach(function (body, i) {
            const colors = tooltipModel.labelColors[i];
            const text = splitText(body)[0].text;
            const value = splitText(body)[0].value;
            const sum = Math.round(value * 100 / text.split(' ')[0]);

            let style = `background: ${colors.backgroundColor};`;
            style += `border-color: ${colors.borderColor};`;
            style += 'display: block;';
            style += 'border-width: 2px;';
            style += 'border-radius: 50%;';
            style += 'width: 12px; height: 12px;';
            style += 'margin-right: 10px;';

            innerHtml += `<div style="padding: 8px 0; display: flex; color: #fff; align-items: center"><span style="${style}"></span>`;
            if (!dashboardTooltip) {
                innerHtml += `<div><p style="font-size: 14px; font-weight: 500">${value}</p>`;
                innerHtml += `<p style="font-size: 12px; font-weight: 400; max-width: 340px">${text}</p></div>`;
            } else {
                const valueType = dashboardTooltip[0].split('_')[0];
                let pluralValue = '';
                switch (valueType) {
                    case 'brand':
                        pluralValue = plural(value, ['бренд', 'бренда', 'брендов']);
                        break;
                    case 'category':
                        pluralValue = plural(value, ['категория', 'категории', 'категорий']);
                        break;
                    default:
                        pluralValue = plural(value, ['товар', 'товара', 'товаров']);
                }
                innerHtml += `<div><p style="font-size: 12px; font-weight: 700; max-width: 340px">${value} ${pluralValue} из ${sum} (${text})</p></div>`;
            }
            innerHtml += '</div>';

            let spanStyle = 'position: absolute;';
            spanStyle += 'display: inline-block;';
            spanStyle += 'transform: rotate(-90deg);';
            spanStyle += 'top: 40%;';
            spanStyle += 'left: -12px;';
            spanStyle += 'width: 12px;';
            spanStyle += 'height: 12px;';
            spanStyle += 'border-right: 10px solid transparent;';
            spanStyle += 'border-left: 10px solid transparent;';
            spanStyle += 'border-bottom: 10px solid #2F3640;';

            const spanPointerElement = `<span style="${spanStyle}"></span>`;

            if (type === 'doughnut') {
                innerHtml += spanPointerElement;
            }
        });

        const divRoot = tooltipEl.querySelector('section');
        divRoot.innerHTML = innerHtml;
    }

    // Display, position, and set section styles
    const position = context.chart.canvas.getBoundingClientRect();

    tooltipEl.style.opacity = 1;
    tooltipEl.style.position = 'absolute';
    tooltipEl.style.left = position.left + tooltipModel.caretX + 16 + 'px';
    if (type !== 'doughnut' && tooltipModel.caretX >= context.chart.width / 2) {
        tooltipEl.style.left = position.left + tooltipModel.caretX - tooltipModel.width - 16 + 'px';
    }

    tooltipEl.style.top = position.top + window.scrollY + tooltipModel.caretY + 'px';
    tooltipEl.style.font = 'Manrope';
    tooltipEl.style.padding = '0 16px';
    tooltipEl.style.backgroundColor = '#2F3640';
    tooltipEl.style.borderRadius = '8px';
    tooltipEl.style.display = 'flex';
    tooltipEl.style.flexDirection = 'column';
    tooltipEl.style.pointerEvents = 'none';
    tooltipEl.style.zIndex = '10';
    tooltipEl.style.transform = 'translateY(-50%)';

    return tooltipEl;
};

export const externalTooltipMultitableHandler = context => {
    const { allChartLines, getAllChartData, keyToColorMatch } = context.chart.config.data.context;

    // Tooltip Element
    let tooltipEl = document.getElementById('chart-tooltip');

    // Create element on first render
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chart-tooltip';
        tooltipEl.innerHTML = '<section></section>';
        document.body.appendChild(tooltipEl);
    }

    // Hide if no tooltip
    const tooltipModel = context.tooltip;
    if (tooltipModel.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }

    // Set caret Position
    tooltipEl.classList.remove('above', 'below', 'no-transform');
    if (tooltipModel.yAlign) {
        tooltipEl.classList.add(tooltipModel.yAlign);
    } else {
        tooltipEl.classList.add('no-transform');
    }

    // Define chart type
    const { type } = context.chart.config;

    // Set Content
    if (tooltipModel.body) {
        const titleLines = tooltipModel.title || [];
        const { dataIndex } = tooltipModel.dataPoints[0];

        let innerHtml = '';

        if (titleLines && titleLines.length > 0) {
            titleLines.forEach(function (title) {
                innerHtml += `<div style="color: #fff; font-weight: 500; font-size: 16px; line-height: 22px; border-bottom: 8px;">${title}</div>`;
            });
        }

        const allChartsData = getAllChartData();
        const troubles = allChartsData.troubles[dataIndex] || [];

        allChartLines.forEach(function (body, i) {
            if (body.active) {
                innerHtml += getLineHtml(
                    allChartsData[body.key]?.[dataIndex] || 0,
                    body.color,
                    body.label
                );
            }
        });

        if (troubles.length) {
            const troublePlural = plural(troubles.length, ['проблема', 'проблемы', 'проблем']);
            innerHtml +=
                '<div style="width: 100%; height: 1px; background-color: #505965; margin: 17px 0 15px;"></div>';
            innerHtml += `<div style="color: #fff; font-weight: 500; font-size: 16px; line-height: 22px; border-bottom: 8px;">${troubles.length} ${troublePlural}</div>`;

            troubles.forEach(el => {
                innerHtml += getLineHtml(el.value || 0, keyToColorMatch[el.key] || '#505965', el.text);
            });
        }

        const divRoot = tooltipEl.querySelector('section');
        divRoot.innerHTML = innerHtml;
    }

    // Display, position, and set section styles
    const position = context.chart.canvas.getBoundingClientRect();

    tooltipEl.style.opacity = 1;
    tooltipEl.style.position = 'absolute';
    tooltipEl.style.left = position.left + tooltipModel.caretX + 16 + 'px';
    if (type !== 'doughnut' && tooltipModel.caretX >= context.chart.width / 2) {
        tooltipEl.style.left = position.left + tooltipModel.caretX - tooltipModel.width - 16 + 'px';
    }

    tooltipEl.style.top = position.top + window.scrollY + tooltipModel.caretY + 'px';
    tooltipEl.style.font = 'Manrope';
    tooltipEl.style.padding = '8px';
    tooltipEl.style.backgroundColor = '#2F3640';
    tooltipEl.style.borderRadius = '8px';
    tooltipEl.style.display = 'flex';
    tooltipEl.style.flexDirection = 'column';
    tooltipEl.style.pointerEvents = 'none';
    tooltipEl.style.zIndex = '10';
    tooltipEl.style.transform = 'translateY(-50%)';

    return tooltipEl;

    function getLineHtml(value, color, text) {
        let innerHtml = '';
        let style = `background: ${color};`;
        style += 'display: block;';
        style += 'border-width: 2px;';
        style += 'border-radius: 50%;';
        style += 'width: 12px; height: 12px;';
        style += 'margin-right: 10px;';

        innerHtml +=
            '<div style="padding: 3px 0; display: grid; grid-template-columns: 61px 8px 220px; align-items: center; gap: 8px; color: #fff;">';
        innerHtml += `<p style="justify-self: end; font-size: 14px; font-weight: 500">${value}</p><span style="${style}"></span>`;
        innerHtml += `<p style="justify-self: start; font-size: 12px; font-weight: 400; max-width: 340px">${text}</p></div>`;

        let spanStyle = 'position: absolute;';
        spanStyle += 'display: inline-block;';
        spanStyle += 'transform: rotate(-90deg);';
        spanStyle += 'top: 40%;';
        spanStyle += 'left: -12px;';
        spanStyle += 'width: 12px;';
        spanStyle += 'height: 12px;';
        spanStyle += 'border-right: 10px solid transparent;';
        spanStyle += 'border-left: 10px solid transparent;';
        spanStyle += 'border-bottom: 10px solid #2F3640;';

        const spanPointerElement = `<span style="${spanStyle}"></span>`;

        if (type === 'doughnut') {
            innerHtml += spanPointerElement;
        }

        return innerHtml;
    }
};
