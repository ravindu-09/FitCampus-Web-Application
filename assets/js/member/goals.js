// assets/js/member/goals.js

document.addEventListener('DOMContentLoaded', () => {
    const modalIds = [
        'edit-goal-modal',
        'goal-details-modal',
        'create-goal-modal',
        'update-progress-modal'
    ];
    const modals = modalIds
        .map(id => document.getElementById(id))
        .filter(Boolean);
    const createModal = document.getElementById('create-goal-modal');
    const detailsModal = document.getElementById('goal-details-modal');
    const progressModal = document.getElementById('update-progress-modal');
    const categorySelect = document.getElementById('create-goal-type');
    const progressMetricFields = document.getElementById('progress-metric-fields');
    const progressDate = document.getElementById('progress-update-date');

    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value == null ? '' : String(value);
        }
        return element;
    };

    const formatNumber = value => {
        if (value == null || value === '') {
            return '—';
        }
        return new Intl.NumberFormat(undefined, {
            maximumFractionDigits: 2
        }).format(Number(value));
    };

    const formatPerformance = (metrics, values) => metrics
        .filter(metric => values && Object.prototype.hasOwnProperty.call(values, metric.Metric_Key))
        .map(metric => `${metric.Metric_Label}: ${formatNumber(values[metric.Metric_Key])} ${metric.Unit}`)
        .join(' · ') || 'No performance recorded';

    const todayString = () => {
        const date = new Date();
        return [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');
    };

    window.closeModals = function () {
        modals.forEach(modal => modal.classList.remove('active'));
    };

    window.openCreateGoalModal = function () {
        const form = document.getElementById('create-goal-form');
        if (form) {
            form.reset();
        }
        updateCategoryFields();
        createModal?.classList.add('active');
    };

    window.openEditGoalModal = function (goalName) {
        const nameInput = document.getElementById('edit-goal-name');
        if (nameInput) {
            nameInput.value = goalName;
        }
        document.getElementById('edit-goal-modal')?.classList.add('active');
    };

    function updateCategoryFields() {
        const selectedCategory = categorySelect?.value || '';
        document.querySelectorAll('.gl-category-fields').forEach(group => {
            const isSelected = group.dataset.category === selectedCategory;
            group.hidden = !isSelected;
            group.querySelectorAll('[data-category-input]').forEach(input => {
                input.disabled = !isSelected;
                input.required = isSelected;
                if (!isSelected) {
                    input.value = '';
                }
            });
        });
    }

    categorySelect?.addEventListener('change', updateCategoryFields);

    function readPayload(button, attribute) {
        try {
            return JSON.parse(button.dataset[attribute] || '{}');
        } catch (error) {
            console.error('Unable to read Personal Goals data.', error);
            return null;
        }
    }

    function openProgressModal(button) {
        const goal = readPayload(button, 'goalMetrics');
        if (!goal || !Array.isArray(goal.metrics)) {
            return;
        }

        const goalId = document.getElementById('progress-goal-id');
        const goalName = document.getElementById('progress-goal-name');
        if (goalId) {
            goalId.value = goal.id;
        }
        if (goalName) {
            goalName.value = goal.title || '';
        }

        progressMetricFields.replaceChildren();
        goal.metrics.forEach(metric => {
            const group = document.createElement('div');
            group.className = 'gl-form-group';

            const label = document.createElement('label');
            const inputId = `progress-${goal.id}-${metric.Metric_Key}`;
            label.htmlFor = inputId;
            label.textContent = `LATEST ${metric.Metric_Label}`.toUpperCase();

            const unitWrap = document.createElement('div');
            unitWrap.className = 'gl-number-unit';

            const input = document.createElement('input');
            input.className = 'gl-input-dark';
            input.id = inputId;
            input.name = `metrics[${metric.Metric_Key}]`;
            input.type = 'number';
            input.min = metric.Metric_Key === 'repetitions' ? '1' : '0.01';
            input.max = '99999999.99';
            input.step = metric.Metric_Key === 'repetitions' ? '1' : '0.01';
            input.required = true;
            const latestValue = goal.latest?.Values?.[metric.Metric_Key];
            if (latestValue != null) {
                input.value = latestValue;
            }

            const suffix = document.createElement('span');
            suffix.textContent = metric.Unit;
            suffix.setAttribute('aria-hidden', 'true');

            unitWrap.append(input, suffix);
            group.append(label, unitWrap);
            progressMetricFields.append(group);
        });

        const today = todayString();
        if (progressDate) {
            progressDate.value = today;
            progressDate.max = today;
            progressDate.min = goal.startDate || '';
        }
        progressModal?.classList.add('active');
    }

    const svgElement = (name, attributes = {}) => {
        const element = document.createElementNS('http://www.w3.org/2000/svg', name);
        Object.entries(attributes).forEach(([key, value]) => {
            element.setAttribute(key, String(value));
        });
        return element;
    };

    function createMetricChart(metric, history, index) {
        const series = history
            .filter(entry => entry.Values && entry.Values[metric.Metric_Key] != null)
            .map(entry => ({
                date: entry.Performance_Date,
                value: Number(entry.Values[metric.Metric_Key])
            }));

        const section = document.createElement('section');
        section.className = 'gl-chart-card';
        const heading = document.createElement('h4');
        heading.textContent = `${metric.Metric_Label} (${metric.Unit})`;
        section.append(heading);

        if (!series.length) {
            const empty = document.createElement('p');
            empty.className = 'gl-history-empty';
            empty.textContent = 'No recorded values for this metric.';
            section.append(empty);
            return section;
        }

        const width = 620;
        const height = 235;
        const left = 58;
        const right = 18;
        const top = 18;
        const bottom = 48;
        const plotWidth = width - left - right;
        const plotHeight = height - top - bottom;
        let minValue = Math.min(...series.map(point => point.value));
        let maxValue = Math.max(...series.map(point => point.value));
        if (minValue === maxValue) {
            const padding = Math.max(Math.abs(minValue) * 0.05, 0.5);
            minValue = Math.max(0, minValue - padding);
            maxValue += padding;
        } else {
            const padding = (maxValue - minValue) * 0.1;
            minValue = Math.max(0, minValue - padding);
            maxValue += padding;
        }

        const xFor = pointIndex => series.length === 1
            ? left + plotWidth / 2
            : left + (pointIndex / (series.length - 1)) * plotWidth;
        const yFor = value => top + ((maxValue - value) / (maxValue - minValue)) * plotHeight;
        const svg = svgElement('svg', {
            viewBox: `0 0 ${width} ${height}`,
            role: 'img',
            'aria-label': `${metric.Metric_Label} history chart in ${metric.Unit}`
        });

        for (let tick = 0; tick <= 2; tick += 1) {
            const value = minValue + ((maxValue - minValue) * tick / 2);
            const y = yFor(value);
            svg.append(svgElement('line', {
                x1: left, y1: y, x2: width - right, y2: y,
                class: 'gl-chart-gridline'
            }));
            const label = svgElement('text', {
                x: left - 8, y: y + 4, 'text-anchor': 'end',
                class: 'gl-chart-axis-label'
            });
            label.textContent = formatNumber(value);
            svg.append(label);
        }

        svg.append(svgElement('line', {
            x1: left, y1: top, x2: left, y2: height - bottom,
            class: 'gl-chart-axis'
        }));
        svg.append(svgElement('line', {
            x1: left, y1: height - bottom, x2: width - right, y2: height - bottom,
            class: 'gl-chart-axis'
        }));

        const points = series.map((point, pointIndex) =>
            `${xFor(pointIndex)},${yFor(point.value)}`
        ).join(' ');
        svg.append(svgElement('polyline', {
            points,
            class: 'gl-chart-line'
        }));

        series.forEach((point, pointIndex) => {
            const x = xFor(pointIndex);
            const y = yFor(point.value);
            svg.append(svgElement('circle', {
                cx: x, cy: y, r: 4, class: 'gl-chart-point'
            }));
            if (
                series.length <= 5
                || pointIndex === 0
                || pointIndex === series.length - 1
                || pointIndex === Math.floor((series.length - 1) / 2)
            ) {
                const dateLabel = svgElement('text', {
                    x, y: height - bottom + 22, 'text-anchor': 'middle',
                    class: 'gl-chart-axis-label'
                });
                dateLabel.textContent = point.date.slice(5);
                svg.append(dateLabel);
            }
        });

        const yTitle = svgElement('text', {
            x: 14, y: top + plotHeight / 2,
            transform: `rotate(-90 14 ${top + plotHeight / 2})`,
            'text-anchor': 'middle',
            class: 'gl-chart-axis-label'
        });
        yTitle.textContent = metric.Unit;
        svg.append(yTitle);
        section.append(svg);
        return section;
    }

    function renderHistory(goal) {
        const chartList = document.getElementById('detail-chart-list');
        const historyHead = document.getElementById('detail-history-head');
        const historyBody = document.getElementById('detail-history-body');
        const historyWrap = document.getElementById('detail-history-wrap');
        const emptyMessage = document.getElementById('detail-history-empty');
        const entries = Array.isArray(goal.history) ? goal.history : [];
        const metrics = Array.isArray(goal.metrics) ? goal.metrics : [];

        chartList.replaceChildren();
        historyHead.replaceChildren();
        historyBody.replaceChildren();

        if (!entries.length) {
            historyWrap.hidden = true;
            emptyMessage.hidden = false;
            return;
        }
        historyWrap.hidden = false;
        emptyMessage.hidden = true;
        metrics.forEach((metric, index) => {
            chartList.append(createMetricChart(metric, entries, index));
        });

        const headerRow = document.createElement('tr');
        ['Date', ...metrics.map(metric => `${metric.Metric_Label} (${metric.Unit})`)]
            .forEach(labelText => {
                const cell = document.createElement('th');
                cell.scope = 'col';
                cell.textContent = labelText;
                headerRow.append(cell);
            });
        historyHead.append(headerRow);

        [...entries].reverse().forEach(entry => {
            const row = document.createElement('tr');
            const dateCell = document.createElement('td');
            dateCell.textContent = entry.Performance_Date;
            row.append(dateCell);
            metrics.forEach(metric => {
                const cell = document.createElement('td');
                const value = entry.Values?.[metric.Metric_Key];
                cell.textContent = value == null ? '—' : formatNumber(value);
                row.append(cell);
            });
            historyBody.append(row);
        });
    }

    function openDetailsModal(button) {
        const goal = readPayload(button, 'goalDetails');
        if (!goal || !Array.isArray(goal.metrics)) {
            return;
        }
        const badge = document.getElementById('detail-category-badge');
        if (badge) {
            badge.textContent = goal.category || 'Goal';
            badge.className = `gl-badge gl-badge-sm ${goal.category === 'Weight Loss'
                ? 'badge-bg-green txt-green'
                : goal.category === 'Endurance'
                    ? 'badge-bg-yellow txt-yellow'
                    : 'badge-bg-purple txt-purple'}`;
        }
        setText('detail-title', goal.title);
        setText('detail-activity', goal.activity);
        const status = setText('detail-status', goal.status);
        if (status) {
            status.className = `gl-status-pill gl-status-${String(goal.status || 'active').toLowerCase()}`;
        }

        const latestValues = goal.latest?.Values || {};
        setText('detail-target-performance', formatPerformance(
            goal.metrics,
            Object.fromEntries(goal.metrics.map(metric => [metric.Metric_Key, metric.Target_Value]))
        ));
        setText('detail-latest-performance', formatPerformance(goal.metrics, latestValues));
        setText(
            'detail-latest-date',
            goal.latest?.Performance_Date ? `Recorded ${goal.latest.Performance_Date}` : ''
        );
        const distanceMetric = goal.metrics.find(metric => metric.Metric_Key === 'distance');
        const durationMetric = goal.metrics.find(metric => metric.Metric_Key === 'duration');
        const targetValues = Object.fromEntries(
            goal.metrics.map(metric => [metric.Metric_Key, metric.Target_Value])
        );
        if (distanceMetric && durationMetric && Number(targetValues.distance) > 0) {
            setText(
                'detail-target-pace',
                `Target pace: ${formatNumber(Number(targetValues.duration) / Number(targetValues.distance))} min/km`
            );
            const distance = Number(latestValues.distance);
            const duration = Number(latestValues.duration);
            setText(
                'detail-latest-pace',
                distance > 0 && duration > 0
                    ? `Pace: ${formatNumber(duration / distance)} min/km`
                    : ''
            );
        } else {
            setText('detail-target-pace', '');
            setText('detail-latest-pace', '');
        }
        const baseline = goal.metrics.find(metric => metric.Baseline_Value != null);
        setText(
            'detail-baseline',
            baseline
                ? `Starting ${baseline.Metric_Label.toLowerCase()}: ${formatNumber(baseline.Baseline_Value)} ${baseline.Unit}`
                : ''
        );

        const bestSection = document.getElementById('detail-best-section');
        if (goal.category === 'Strength' && goal.best) {
            bestSection.hidden = false;
            setText('detail-best-performance', formatPerformance(goal.metrics, goal.best.Values || {}));
            setText('detail-best-date', goal.best.Performance_Date || '');
        } else {
            bestSection.hidden = true;
        }

        renderHistory(goal);
        detailsModal?.classList.add('active');
    }

    document.querySelectorAll('.gl-edit-goal-btn').forEach(button => {
        button.addEventListener('click', () => {
            window.openEditGoalModal(button.dataset.goalName || '');
        });
    });

    document.querySelectorAll('[data-goal-action="update-progress"]').forEach(button => {
        button.addEventListener('click', () => openProgressModal(button));
    });

    document.querySelectorAll('[data-goal-action="details"]').forEach(button => {
        button.addEventListener('click', () => openDetailsModal(button));
    });

    modals.forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                window.closeModals();
            }
        });
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            window.closeModals();
        }
    });
});
