/**
 * Handles our chart actions.
 */
import randomColor from 'randomcolor';
import { ajax, hide, next, prev, setHeight, show } from '../utils';

interface ChartDataSet {
    label: string;
    data: Array<number | string>;
}

interface ChartData {
    labels: string[];
    dataSet: ChartDataSet;
}

interface ChartPayload {
    normal: ChartData;
    weighted: ChartData;
}

function emptyPayload(): ChartPayload {
    const empty = (): ChartData => ({ labels: [], dataSet: { label: '', data: [] } });
    return { normal: empty(), weighted: empty() };
}

class Charts {
    /** Active Chart.js instances, keyed by container id. */
    private instances: Record<string, any> = {};

    /** Chart data, keyed by container id. */
    private data: Record<string, ChartPayload> = {};

    /** Latest filtered posts, populated by the filter component. */
    public posts: unknown = null;

    public initialize(): void {
        // Initial set-up for every chart on the page.
        document.querySelectorAll<HTMLElement>('.wfr-charts').forEach((container) => {
            const canvas = container.querySelector<HTMLCanvasElement>('.wfr-charts-chart');
            const id = container.id;

            this.data[id] = emptyPayload();
            this.instances[id] = false;

            // Draw the chart when the container has an id with localized data.
            if (id && canvas && typeof window[`chart${id}`] !== 'undefined') {
                this.data[id] = window[`chart${id}`] as ChartPayload;
                this.renderChart(this.data[id].normal, canvas, id);

                if (this.data[id].weighted.dataSet.data.length > 0) {
                    const weight = container.querySelector<HTMLElement>('.wfr-charts-weight');
                    if (weight) {
                        show(weight);
                    }
                }
            }
        });

        // Draw a new chart whenever the selector changes.
        document
            .querySelectorAll<HTMLSelectElement>('.wfr-chart-selector select')
            .forEach((select) => {
                select.addEventListener('change', () => this.listener(select));
            });

        // Prevent submitting of the weight form.
        document.querySelectorAll<HTMLFormElement>('.wfr-charts-weight').forEach((form) => {
            form.addEventListener('submit', (event) => event.preventDefault());
        });

        // Select the normal (unweighted) chart.
        document.querySelectorAll<HTMLElement>('.wfr-charts-normal').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();

                const container = button.closest<HTMLElement>('.wfr-charts');
                const canvas = container?.querySelector<HTMLCanvasElement>('.wfr-charts-chart');
                if (!container || !canvas) {
                    return;
                }

                button.classList.add('active');
                next(button, '.wfr-charts-weighted')?.classList.remove('active');
                this.renderChart(this.data[container.id].normal, canvas, container.id);
            });
        });

        // Select the weighted chart.
        document.querySelectorAll<HTMLElement>('.wfr-charts-weighted').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();

                const container = button.closest<HTMLElement>('.wfr-charts');
                const canvas = container?.querySelector<HTMLCanvasElement>('.wfr-charts-chart');
                if (!container || !canvas) {
                    return;
                }

                button.classList.add('active');
                prev(button, '.wfr-charts-normal')?.classList.remove('active');
                this.renderChart(this.data[container.id].weighted, canvas, container.id);
            });
        });
    }

    /**
     * Creates our form listener - upon changes it will load a new chart.
     *
     * @param selector The select element defining the context of our chart.
     */
    public listener(selector: HTMLSelectElement | null): void {
        // A key should be defined.
        if (!selector || !selector.value) {
            return;
        }

        const container = selector.closest<HTMLElement>('.wfr-charts');
        const chartSelector = selector.closest<HTMLElement>('.wfr-chart-selector');
        if (!container) {
            return;
        }

        const canvas = container.querySelector<HTMLCanvasElement>('.wfr-charts-chart');
        const id = container.id;
        const weight = container.querySelector<HTMLElement>('.wfr-charts-weight');

        // Reset and hide the weighted display.
        if (weight) {
            weight.querySelector('.wfr-charts-normal')?.classList.add('active');
            weight.querySelector('.wfr-charts-weighted')?.classList.remove('active');
            hide(weight);
        }

        void ajax({
            beforeSend: () => {
                canvas?.closest('.wfr-charts-wrapper')?.classList.add('components-loading');
            },
            complete: () => {
                canvas?.closest('.wfr-charts-wrapper')?.classList.remove('components-loading');
            },
            data: {
                action: 'get_chart_data',
                categories: chartSelector?.dataset.categories,
                key: selector.value,
                include: chartSelector?.dataset.include,
                tags: chartSelector?.dataset.tags,
            },
            success: (response) => {
                if (wfr.debug) {
                    console.log(response);
                }

                if (!response.success) {
                    return;
                }

                this.data[id] = response.data;
                container.classList.add('wfr-charts-loaded');

                if (weight && this.data[id].weighted.dataSet.data.length > 0) {
                    show(weight);
                }

                if (canvas) {
                    this.renderChart(response.data.normal, canvas, id);
                }
            },
        });
    }

    /**
     * Displays the chart.
     *
     * @param data   The data object with unformatted data sets.
     * @param canvas The canvas to render the chart in.
     * @param id     The id of the rendered chart.
     */
    public renderChart(data: ChartData, canvas: HTMLCanvasElement, id: string): void {
        if (typeof data.dataSet === 'undefined') {
            return;
        }

        // Format our data set with random colors.
        const dataSet = {
            backgroundColor: [] as string[],
            barThickness: 30,
            data: [] as number[],
            label: data.dataSet.label,
        };

        for (const value of data.dataSet.data) {
            dataSet.backgroundColor.push(randomColor());
            dataSet.data.push(parseFloat(String(value)));
        }

        const dataSets = [dataSet];

        // Redefine the chart data if our chart already exists.
        const existing = this.instances[id];
        if (existing) {
            existing.data = { datasets: dataSets, labels: data.labels };
            existing.options.title.text = dataSet.label;
            existing.update();
            this.setChartHeight(dataSets, dataSet.barThickness, canvas);
            return;
        }

        // Set up the chart.
        this.instances[id] = new Chart(canvas, {
            data: {
                datasets: dataSets,
                labels: data.labels,
            },
            options: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: dataSet.label,
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [
                        {
                            ticks: {
                                beginAtZero: true,
                            },
                        },
                    ],
                },
            },
            type: 'horizontalBar',
        });

        this.setChartHeight(dataSets, dataSet.barThickness, canvas);
    }

    /**
     * Sets the minimum height of a chart based upon the amount of data sets.
     *
     * @param dataSets  The data sets passed to the chart.
     * @param thickness The thickness of each bar.
     * @param canvas    The canvas the height needs to be applied to.
     */
    private setChartHeight(
        dataSets: Array<{ data: number[] }>,
        thickness: number,
        canvas: HTMLCanvasElement,
    ): void {
        let height = canvas.height;

        if (typeof dataSets[0] !== 'undefined' && typeof dataSets[0].data !== 'undefined') {
            height = dataSets[0].data.length * (thickness + 10) + 64;
        }

        if (height < 500) {
            height = 500;
        }

        const wrapper = canvas.closest<HTMLElement>('.wfr-charts-wrapper');
        if (wrapper) {
            setHeight(wrapper, height);
        }
    }
}

export const charts = new Charts();
