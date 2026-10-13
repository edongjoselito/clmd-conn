<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Plain-language interpretations for the Learning Gap dashboards.
 * Every function returns a list of HTML-safe sentences for lg_interpretation_box().
 */

if (!function_exists('lg_cpl_band')) {
    /** CPL descriptor based on the DepEd MPS descriptive equivalents (DM 160, s. 2012). */
    function lg_cpl_band($cpl)
    {
        $bands = array(96 => 'Mastered', 86 => 'Closely Approximating Mastery', 66 => 'Moving Towards Mastery', 35 => 'Average', 15 => 'Low', 5 => 'Very Low');
        foreach ($bands as $floor => $label) {
            if ($cpl >= $floor) return $label;
        }
        return 'Absolutely No Mastery';
    }
}

if (!function_exists('lg_count_label')) {
    function lg_count_label($count, $word, $plural = null)
    {
        return number_format($count) . ' ' . ((int) $count === 1 ? $word : ($plural !== null ? $plural : $word . 's'));
    }
}

if (!function_exists('lg_interpretation_box')) {
    function lg_interpretation_box(array $lines)
    {
        if (empty($lines)) return '';
        return '<div class="lg-interp"><i class="mdi mdi-lightbulb-on-outline" aria-hidden="true"></i><ul aria-label="Interpretation"><li>' . implode('</li><li>', $lines) . '</li></ul></div>';
    }
}

if (!function_exists('lg_term_interpretation')) {
    /** Reads the per-term gap rate and CPL rows from Page_model::learning_gap_term_performance(). */
    function lg_term_interpretation(array $term_performance, $record_count)
    {
        $lines = array();
        $terms = array_values(array_filter($term_performance, function ($row) {
            return (int) $row->learners_assessed > 0 || $row->class_proficiency_level !== null;
        }));
        $rate = function ($row) { return ((int) $row->learners_with_gap / (int) $row->learners_assessed) * 100; };

        foreach ($terms as $row) {
            $parts = array();
            if ((int) $row->learners_assessed > 0) {
                $gap_rate = $rate($row);
                $ratio = $gap_rate > 0 ? ' — about 1 in ' . max(1, (int) round(100 / $gap_rate)) . ' learners' : '';
                $parts[] = 'recorded gap rate is <strong>' . number_format($gap_rate, 1) . '%</strong> of ' . lg_count_label((int) $row->learners_assessed, 'assessed learner') . $ratio;
            }
            if ($row->class_proficiency_level !== null) {
                $cpl = (float) $row->class_proficiency_level;
                $parts[] = 'CPL is <strong>' . number_format($cpl, 1) . '%</strong>, which falls under <strong>' . lg_cpl_band($cpl) . '</strong>';
            }
            if (isset($row->cpl_summative_avg) && $row->cpl_summative_avg !== null) {
                $parts[] = 'summative CPL average is <strong>' . number_format((float) $row->cpl_summative_avg, 1) . '%</strong>';
            }
            $lines[] = '<strong>' . html_escape($row->term) . ':</strong> The ' . implode('; ', $parts) . '.';
        }

        if (count($terms) >= 2) {
            $first = $terms[0];
            $last = $terms[count($terms) - 1];
            $span = html_escape($first->term) . ' to ' . html_escape($last->term);
            if ((int) $first->learners_assessed > 0 && (int) $last->learners_assessed > 0) {
                $delta = $rate($last) - $rate($first);
                $lines[] = abs($delta) < 0.1
                    ? 'The gap rate is unchanged from ' . $span . '.'
                    : 'From ' . $span . ', the gap rate ' . ($delta < 0 ? 'decreased' : 'increased') . ' by <strong>' . number_format(abs($delta), 1) . ' percentage points</strong>' . ($delta < 0 ? ', which suggests interventions are reducing learning gaps.' : ', so more learners need support than in the earlier term.');
            }
            if ($first->class_proficiency_level !== null && $last->class_proficiency_level !== null) {
                $cpl_delta = (float) $last->class_proficiency_level - (float) $first->class_proficiency_level;
                if (abs($cpl_delta) >= 0.1) {
                    $lines[] = 'CPL ' . ($cpl_delta > 0 ? 'improved' : 'declined') . ' by <strong>' . number_format(abs($cpl_delta), 1) . ' percentage points</strong> over the same period.';
                }
            }
        } elseif (count($terms) === 1) {
            $lines[] = 'Only ' . html_escape($terms[0]->term) . ' has submitted data so far, so term-to-term trends will appear once later terms are encoded.';
        }

        $cpl_record_count = array_sum(array_map(function ($row) { return (int) $row->cpl_record_count; }, $term_performance));
        if ($record_count > 0 && $cpl_record_count < $record_count) {
            $lines[] = 'CPL is based on only ' . number_format($cpl_record_count) . ' of ' . lg_count_label((int) $record_count, 'record') . ', so treat it as indicative until more schools encode their CPL.';
        }
        return $lines;
    }
}

if (!function_exists('lg_division_summary_interpretation')) {
    /** Reads rows from Page_model::learning_gap_division_summary(). */
    function lg_division_summary_interpretation(array $rows)
    {
        $lines = array();
        if (empty($rows)) return $lines;

        $reporting = array_values(array_filter($rows, function ($row) { return (int) $row->record_count > 0; }));
        $silent = array_values(array_filter($rows, function ($row) { return (int) $row->record_count === 0; }));
        $line = number_format(count($reporting)) . ' of ' . lg_count_label(count($rows), 'division') . (count($reporting) === 1 ? ' has' : ' have') . ' submitted records.';
        if (!empty($silent)) {
            $names = array_map(function ($row) { return html_escape($row->division_name); }, array_slice($silent, 0, 3));
            $line .= ' No submissions yet from ' . implode(', ', $names) . (count($silent) > 3 ? ' and ' . number_format(count($silent) - 3) . ' more' : '') . '.';
        }
        $lines[] = $line;

        $reporting_schools = array_sum(array_map(function ($row) { return (int) $row->school_count; }, $rows));
        $total_schools = array_sum(array_map(function ($row) { return (int) $row->total_school_count; }, $rows));
        if ($total_schools > 0) {
            $lines[] = number_format($reporting_schools) . ' of ' . lg_count_label($total_schools, 'registered school') . ' (' . number_format(($reporting_schools / $total_schools) * 100, 1) . '%) ' . ($reporting_schools === 1 ? 'is' : 'are') . ' reporting across the region.';
        }

        $assessed = array_values(array_filter($rows, function ($row) { return (int) $row->learners_assessed > 0; }));
        if (!empty($assessed)) {
            $rate = function ($row) { return ((int) $row->learners_with_gap / (int) $row->learners_assessed) * 100; };
            usort($assessed, function ($a, $b) use ($rate) { return $rate($b) <=> $rate($a); });
            $high = $assessed[0];
            $line = '<strong>' . html_escape($high->division_name) . '</strong> has the highest recorded gap rate at <strong>' . number_format($rate($high), 1) . '%</strong>';
            if (count($assessed) >= 2) {
                $low = $assessed[count($assessed) - 1];
                $line .= ', while <strong>' . html_escape($low->division_name) . '</strong> has the lowest at <strong>' . number_format($rate($low), 1) . '%</strong>';
            }
            $lines[] = $line . '. Prioritize regional technical assistance for divisions with the highest rates.';
        }
        return $lines;
    }
}

if (!function_exists('lg_ranking_interpretation')) {
    /** Reads rows from Page_model::learning_gap_competency_ranking() for one learning area and term. */
    function lg_ranking_interpretation(array $rows, $learning_area, $term)
    {
        $lines = array();
        if (empty($rows)) return $lines;

        $top = array_values(array_filter($rows, function ($row) { return (int) $row->rank === 1; }));
        $top_count = (int) $top[0]->submission_count;
        $context = html_escape($learning_area) . ', ' . html_escape($term);
        if (count($top) === 1) {
            $lines[] = 'The most frequently selected competency in ' . $context . ' is <strong>“' . html_escape($top[0]->competency) . '”</strong> (' . html_escape($top[0]->grade_level) . '), chosen in ' . lg_count_label($top_count, 'submission') . ' from ' . lg_count_label((int) $top[0]->school_count, 'school') . '.';
        } else {
            $lines[] = number_format(count($top)) . ' competencies are tied at #1 in ' . $context . ', each chosen in ' . lg_count_label($top_count, 'submission') . '.';
        }

        $recurring = count(array_filter($rows, function ($row) { return (int) $row->school_count >= 2; }));
        if ($recurring > 0) {
            $lines[] = lg_count_label($recurring, 'competency', 'competencies') . ' ' . ($recurring === 1 ? 'is' : 'are') . ' reported by two or more schools. These are good candidates for division-wide remediation materials or LAC sessions.';
        } else {
            $lines[] = 'Every listed competency comes from a single school, so support is best given school by school rather than division-wide.';
        }

        $grades = array_unique(array_map(function ($row) { return (string) $row->grade_level; }, array_slice($rows, 0, 3)));
        if (count($rows) >= 3 && count($grades) === 1) {
            $lines[] = 'The top three competencies are all in <strong>' . html_escape($grades[0]) . '</strong>, so support can focus on that grade level.';
        }
        return $lines;
    }
}
