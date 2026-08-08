/*
 * Chart adapter — Highcharts option shape in, Chart.js 4 out.
 *
 * Highcharts is commercially licensed and cannot ship in an AGPL product, so
 * the Community Edition renders with Chart.js (MIT). Every call site in the app
 * was already written against the Highcharts option object, so rather than
 * rewrite 23 of them this translates the handful of shapes the app actually
 * uses: pie/donut, column and bar.
 *
 * Usage mirrors what the call sites already do:
 *
 *     $('#some_pie').osChart({ ...highcharts options... });
 *     osChart('some_container', { ...highcharts options... });
 *
 * Anything Highcharts-only (exporting menus, credits, plotShadow) is ignored
 * rather than emulated — the app never exposed those buttons in the OSS build.
 */
(function (window, $) {
    'use strict';

    var PALETTE = [
        '#7cb5ec', '#434348', '#90ed7d', '#f7a35c', '#8085e9',
        '#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1'
    ];

    /** Chart.js needs a <canvas>; the app's containers are <div>s. */
    function canvasFor(el) {
        var existing = el.querySelector('canvas');
        if (existing) {
            var prior = window.Chart && window.Chart.getChart(existing);
            if (prior) {
                prior.destroy();
            }
            return existing;
        }
        var canvas = document.createElement('canvas');
        el.innerHTML = '';
        el.appendChild(canvas);
        return canvas;
    }

    function resolveElement(target) {
        if (!target) {
            return null;
        }
        if (typeof target === 'string') {
            return document.getElementById(target.replace(/^#/, ''));
        }
        if (target.jquery) {
            return target[0];
        }
        return target.nodeType ? target : null;
    }

    /**
     * Highcharts series data comes in three shapes across the call sites:
     * [5, 3], [['Open', 5]] and [{name: 'Open', y: 5, color: '#f00'}].
     */
    function normalisePoints(data) {
        var labels = [];
        var values = [];
        var colours = [];

        (data || []).forEach(function (point, i) {
            if (Array.isArray(point)) {
                labels.push(point[0]);
                values.push(point[1]);
                colours.push(PALETTE[i % PALETTE.length]);
            } else if (point && typeof point === 'object') {
                labels.push(point.name != null ? point.name : '');
                values.push(point.y != null ? point.y : 0);
                colours.push(point.color || PALETTE[i % PALETTE.length]);
            } else {
                labels.push('');
                values.push(point);
                colours.push(PALETTE[i % PALETTE.length]);
            }
        });

        return { labels: labels, values: values, colours: colours };
    }

    function titleText(options) {
        var t = options.title && options.title.text;
        if (!t) {
            return null;
        }
        // Several sites put markup in the title; Chart.js renders plain text.
        return String(t).replace(/<[^>]*>/g, '').trim() || null;
    }

    function percentSize(value, fallback) {
        if (typeof value === 'string' && value.indexOf('%') !== -1) {
            return parseFloat(value) + '%';
        }
        return fallback;
    }

    function buildPie(options, series) {
        var points = normalisePoints(series.data);
        var cutout = percentSize(series.innerSize, 0);
        var title = titleText(options);

        return {
            type: 'doughnut',
            data: {
                labels: points.labels,
                datasets: [{
                    data: points.values,
                    backgroundColor: points.colours,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // Highcharts' half-donut (startAngle -90 / endAngle 90).
                circumference: series.endAngle != null && series.startAngle != null
                    ? Math.abs(series.endAngle - series.startAngle)
                    : 360,
                rotation: series.startAngle != null ? series.startAngle : 0,
                cutout: cutout,
                plugins: {
                    legend: {
                        display: !(options.legend && options.legend.enabled === false),
                        position: 'bottom'
                    },
                    title: { display: !!title, text: title || '' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) {
                                    return a + (Number(b) || 0);
                                }, 0);
                                var pct = total ? (ctx.parsed / total * 100).toFixed(1) : '0.0';
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        };
    }

    /** Highcharts line family; `spline` is a line with tension. */
    var LINE_KINDS = ['line', 'spline', 'area', 'areaspline'];

    function buildLines(options, seriesList, kind) {
        var categories = (options.xAxis && options.xAxis.categories) || [];
        var title = titleText(options);
        var filled = kind === 'area' || kind === 'areaspline';

        var datasets = seriesList.map(function (s, i) {
            var points = normalisePoints(s.data);
            if (!categories.length && points.labels.some(Boolean)) {
                categories = points.labels;
            }
            var colour = s.color || PALETTE[i % PALETTE.length];
            return {
                label: s.name || '',
                data: points.values,
                borderColor: colour,
                backgroundColor: colour,
                fill: filled,
                // Highcharts' spline curve; a plain line stays straight.
                tension: (kind === 'spline' || kind === 'areaspline') ? 0.4 : 0,
                pointRadius: 3
            };
        });

        var stacked = !!(options.plotOptions
            && options.plotOptions.series
            && options.plotOptions.series.stacking);

        return {
            type: 'line',
            data: { labels: categories, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: stacked },
                    y: { stacked: stacked, beginAtZero: true }
                },
                plugins: {
                    legend: {
                        display: !(options.legend && options.legend.enabled === false)
                            && datasets.length > 1,
                        position: 'bottom'
                    },
                    title: { display: !!title, text: title || '' }
                }
            }
        };
    }

    function buildBars(options, seriesList, horizontal) {
        var categories = (options.xAxis && options.xAxis.categories) || [];
        var title = titleText(options);

        var datasets = seriesList.map(function (s, i) {
            var points = normalisePoints(s.data);
            if (!categories.length && points.labels.some(Boolean)) {
                categories = points.labels;
            }
            return {
                label: s.name || '',
                data: points.values,
                backgroundColor: s.color || PALETTE[i % PALETTE.length],
                borderWidth: 0
            };
        });

        var stacked = !!(options.plotOptions
            && options.plotOptions.series
            && options.plotOptions.series.stacking);

        return {
            type: 'bar',
            data: { labels: categories, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: horizontal ? 'y' : 'x',
                scales: {
                    x: {
                        stacked: stacked,
                        title: {
                            display: !!(options.xAxis && options.xAxis.title && options.xAxis.title.text),
                            text: (options.xAxis && options.xAxis.title && options.xAxis.title.text) || ''
                        }
                    },
                    y: {
                        stacked: stacked,
                        beginAtZero: true,
                        title: {
                            display: !!(options.yAxis && options.yAxis.title && options.yAxis.title.text),
                            text: (options.yAxis && options.yAxis.title && options.yAxis.title.text) || ''
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: !(options.legend && options.legend.enabled === false)
                            && datasets.length > 1,
                        position: 'bottom'
                    },
                    title: { display: !!title, text: title || '' }
                }
            }
        };
    }

    /**
     * Render a Highcharts-shaped options object into `target`.
     * Returns the Chart.js instance, or null when the target is missing.
     */
    function osChart(target, options) {
        var el = resolveElement(target);
        if (!el || !window.Chart) {
            return null;
        }

        options = options || {};
        var seriesList = Array.isArray(options.series) ? options.series : [];
        if (!seriesList.length) {
            return null;
        }

        var kind = seriesList[0].type
            || (options.chart && options.chart.type)
            || 'column';

        var config;
        if (kind === 'pie') {
            config = buildPie(options, seriesList[0]);
        } else if (LINE_KINDS.indexOf(kind) !== -1) {
            config = buildLines(options, seriesList, kind);
        } else {
            config = buildBars(options, seriesList, kind === 'bar');
        }

        // Highcharts sizes via chart.height; Chart.js follows its container.
        var height = options.chart && options.chart.height;
        if (height) {
            el.style.height = (typeof height === 'number' ? height + 'px' : height);
        }

        return new window.Chart(canvasFor(el), config);
    }

    window.osChart = osChart;

    /*
     * Compatibility shim for the two non-jQuery forms the app uses:
     *
     *     Highcharts.chart('container', options)
     *     new Highcharts.Chart({ chart: { renderTo: 'container' }, ... })
     *
     * Providing these means the swap touches only the <script> tags — no call
     * site has to change, so the diff stays reviewable.
     */
    function HighchartsChart(options) {
        var target = options && options.chart && options.chart.renderTo;
        return osChart(target, options);
    }

    window.Highcharts = {
        chart: function (target, options) {
            return osChart(target, options);
        },
        Chart: HighchartsChart,
        // Several sites call this at load to set global theme defaults.
        setOptions: function () {},
        getOptions: function () {
            return { colors: PALETTE.slice() };
        }
    };

    /** The live Chart.js instance in `el`, if there is one. */
    function instanceIn(el) {
        if (!el || !window.Chart) {
            return null;
        }
        var canvas = el.tagName === 'CANVAS' ? el : el.querySelector('canvas');
        return canvas ? (window.Chart.getChart(canvas) || null) : null;
    }

    if ($ && $.fn) {
        /*
         * Highcharts' jQuery plugin is both a setter and a getter:
         *
         *     $(sel).highcharts(opts)              // render
         *     if ($(sel).highcharts()) { ... }     // does a chart exist?
         *     $(sel).highcharts().destroy();       // tear it down
         *
         * Returning `this` for the no-argument call would be truthy for every
         * element and would have no destroy(), so the teardown idiom in
         * script_v1.js would throw. Return the instance instead.
         */
        $.fn.osChart = function (options) {
            if (options === undefined) {
                return instanceIn(this[0]);
            }
            return this.each(function () {
                osChart(this, options);
            });
        };
        // The call sites were written against $(sel).highcharts(); keeping the
        // name means the swap is a library change, not 23 rewrites.
        $.fn.highcharts = $.fn.osChart;
    }
}(window, window.jQuery));
