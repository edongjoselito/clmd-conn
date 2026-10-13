(function (root) {
    'use strict';
    function calculate(total, answered, evident) {
        var value = total ? 100 * evident / total : 0;
        var description = 'Not assessed';
        if (total && answered < total) description = 'Incomplete';
        else if (total) {
            if (value < 50) description = 'Needs Improvement';
            else if (value >= 51 && value <= 62) description = 'Fairly Meets Expectation';
            else if (value >= 63 && value <= 75) description = 'Satisfactorily Meets Expectation';
            else if (value >= 76 && value <= 88) description = 'Very Satisfactory';
            else if (value >= 89) description = 'Outstanding';
            else description = 'Not defined in source scale';
        }
        return {total: total, answered: answered, evident: evident,
            score: total && answered > 0 ? value.toFixed(2) + '%' : '—', description: description};
    }
    root.MonitoringRating = {calculate: calculate};
}(typeof window !== 'undefined' ? window : globalThis));
