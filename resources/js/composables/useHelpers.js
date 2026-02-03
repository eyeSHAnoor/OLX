import { add, addDays, addMonths, addWeeks, format, parseISO } from 'date-fns';

// import { useForm } from '@inertiajs/vue3';
// import { computed } from 'vue';

export default function useHelpers() {
    function titleCase(text) {

        if (!text) return ;
        // Convert underscores and dashes to spaces
        text = text.replace(/[_-]/g, ' ');

        // Convert to lowercase and split into words
        var words = text.toLowerCase().split(' ');

        // Capitalize the first letter of each word
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
        }

        // Join the words back together
        return words.join(' ');
    }

    function slugCase(str) {
        if (!str) return;

        return str
            .toLowerCase()                   // Convert to lowercase
            .replace(/[()]/g, '')            // Remove parentheses
            .replace(/[^\w\s-]/g, '')        // Remove all non-word chars (except dash and space)
            .trim()                          // Trim whitespace from both ends
            .replace(/\s+/g, '-')            // Replace spaces with -
            .replace(/-+/g, '-');            // Replace multiple - with single -
    }

    function formatDate(date, withTime = true, formatString = '') {
        if (!date) return;

        // Handle both Date objects and ISO strings
        const isoDate = date instanceof Date ? date : parseISO(date);

        if (withTime) {
            const formatStr = formatString ? formatString : 'dd-MM-yyyy | HH:mm';
            return format(isoDate, formatStr);
        }

        const formatStr = formatString ? formatString : 'dd-MM-yyyy';
        return format(isoDate, formatStr);
    }

    function getImage(url = '') {
        if (!url) return '/images/default-image-placeholder.png';
        return url;
    }

    function formatNumber(number, showDecimals = true) {
        // Handle non-numbers and null/undefined
        if (typeof number !== 'number' || isNaN(number)) {
            return showDecimals ? '0.00' : '0';
        }

        const options = {
            style: 'decimal',
            useGrouping: true // This adds the comma separators
        };

        if (showDecimals) {
            options.minimumFractionDigits = 2;
            options.maximumFractionDigits = 2;
        } else {
            options.minimumFractionDigits = 0;
            options.maximumFractionDigits = 0;
        }

        return number.toLocaleString('en-US', options);
    }

    function getInitialsWithLastFull(name) {
        if (!name) return;
        const words = name.trim().split(/\s+/);
        if (words.length === 0) return '';

        const lastWord = words.pop(); // Remove and store the last word
        const initials = words.map(word => word[0].toUpperCase()).join(' ');

        return initials ? `${initials} ${lastWord}` : lastWord;
    }

    function numberToWords(num) {
        const units = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        const teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        const tens = ['', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        if (num === 0) return 'zero';

        function convertLessThanOneThousand(n) {
            if (n === 0) return '';
            let result = '';

            if (n >= 100) {
                result += units[Math.floor(n / 100)] + ' hundred ';
                n %= 100;
            }

            if (n >= 20) {
                result += tens[Math.floor(n / 10)] + ' ';
                n %= 10;
            } else if (n >= 10) {
                result += teens[n - 10] + ' ';
                n = 0;
            }

            if (n > 0) {
                result += units[n] + ' ';
            }

            return result.trim();
        }

        const scales = ['', 'thousand', 'million', 'billion', 'trillion'];
        let words = '';
        let scaleIndex = 0;

        while (num > 0) {
            const chunk = num % 1000;
            if (chunk !== 0) {
                let chunkWords = convertLessThanOneThousand(chunk);
                if (scaleIndex > 0) {
                    chunkWords += ' ' + scales[scaleIndex];
                }
                words = chunkWords + ' ' + words;
            }
            num = Math.floor(num / 1000);
            scaleIndex++;
        }

        return words.trim();
    }

    function formatMobileNumber(number) {
        if (!number) return;

        number = number.trim();

        if (!number.startsWith('+')) {
            return '+' + number;
        }

        return number;
    }


    return {
        titleCase,
        slugCase,
        formatDate,
        formatNumber,
        getImage,
        getInitialsWithLastFull,
        numberToWords,
        formatMobileNumber,
    };
}
