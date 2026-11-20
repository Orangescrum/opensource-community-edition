/*
 * Company-wide date display format, client side.
 *
 * window.COMPANY_DATE_FORMAT is set by the layout from App\Service\DateFormatService
 * (clientConfig). This helper formats with the same date()-style pattern the server
 * uses, so PHP and JavaScript always agree. Only tokens d, m, M, Y and literal
 * separators are used by the configured formats.
 *
 * Dates are passed as calendar parts (year, zero-based month, day) so no timezone
 * conversion happens here; callers apply their own offsets first, as they already do.
 */
(function (window) {
    'use strict';

    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var FALLBACK = { name: 'MMM DD, YYYY', php: 'M d, Y', dayMonth: 'M d', datepicker: 'M dd, yyyy' };

    function config() {
        return window.COMPANY_DATE_FORMAT || FALLBACK;
    }

    function pad2(n) {
        return (n < 10 ? '0' : '') + n;
    }

    // Renders a pattern from the server's vocabulary: d (day), m (month), M (short month name), Y (year).
    function render(pattern, year, monthIndex, day) {
        var out = '';
        for (var i = 0; i < pattern.length; i++) {
            var c = pattern.charAt(i);
            if (c === 'd') out += pad2(day);
            else if (c === 'm') out += pad2(monthIndex + 1);
            else if (c === 'M') out += MONTHS[monthIndex];
            else if (c === 'Y') out += String(year);
            else out += c;
        }
        return out;
    }

    // Accepts a 'YYYY-MM-DD' prefix of a DB date string and returns its parts, or null.
    function parseParts(value) {
        var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(value || '').trim());
        if (!m || m[1] === '0000') return null;
        return { y: parseInt(m[1], 10), m: parseInt(m[2], 10) - 1, d: parseInt(m[3], 10) };
    }

    var CompanyDate = {
        /** Full date in the company format, e.g. "17-11-2026". */
        formatParts: function (year, monthIndex, day) {
            return render(config().php, year, monthIndex, day);
        },
        /** Day and month only, e.g. "17-11" or "Nov 17". */
        formatDayMonthParts: function (year, monthIndex, day) {
            return render(config().dayMonth, year, monthIndex, day);
        },
        /** Formats a 'YYYY-MM-DD' (optionally with time) string; returns '' when unparseable. */
        format: function (value) {
            var p = parseParts(value);
            return p ? this.formatParts(p.y, p.m, p.d) : '';
        },
        /** Format string for bootstrap-datepicker, e.g. "dd-mm-yyyy". */
        datepickerFormat: function () {
            return config().datepicker;
        },
        /**
         * Format string for moment().format(), e.g. "DD-MMM-YYYY". The configured
         * names are already moment tokens, so this is the name itself.
         */
        momentFormat: function () {
            return config().name;
        },
        /**
         * Reads a date typed into or written by a date field. Tries the company
         * format first, then ISO and the older display formats, so values from
         * before the setting existed still parse. Returns an invalid moment when
         * nothing matches, which callers already check for.
         */
        parse: function (value) {
            var v = String(value == null ? '' : value).trim();
            var formats = [config().name, 'YYYY-MM-DD', 'YYYY-MM-DD HH:mm:ss', 'MMM DD, YYYY', 'MMM D, YYYY', 'DD-MM-YYYY', 'MM-DD-YYYY', 'DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY/MM/DD', 'DD-MMM-YYYY'];
            var m = moment(v, formats, true);
            return m.isValid() ? m : moment(v);
        }
    };

    window.CompanyDate = CompanyDate;

    // Every datepicker without an explicit format now shows and reads the company format.
    // Pages that pass a literal format call CompanyDate.datepickerFormat() themselves.
    // This script is deferred, so the plugin and the inline config are already in place.
    if (window.jQuery && jQuery.fn && jQuery.fn.datepicker && jQuery.fn.datepicker.defaults) {
        jQuery.fn.datepicker.defaults.format = CompanyDate.datepickerFormat();
    }
})(window);
