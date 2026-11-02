<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function lg_proficiency_levels()
{
    return array(
        array('minimum' => 90, 'label' => 'Highly Proficient'),
        array('minimum' => 75, 'label' => 'Proficient'),
        array('minimum' => 50, 'label' => 'Nearly Proficient'),
        array('minimum' => 25, 'label' => 'Low Proficient'),
        array('minimum' => 0, 'label' => 'Not Proficient'),
    );
}

function lg_proficiency_level($percentage)
{
    if (!is_numeric($percentage) || $percentage < 0 || $percentage > 100) {
        return '';
    }
    foreach (lg_proficiency_levels() as $level) {
        if ($percentage >= $level['minimum']) {
            return $level['label'];
        }
    }
    return '';
}

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

/**
 * Building blocks for the analytics drill-down. CPL is weighted by assessed
 * learners, the same rule as Page_model::learning_gap_term_performance().
 */
if (!function_exists('lg_unique_records')) {
    /** A duplicate school profile multiplies joined rows, so each record is kept once. */
    function lg_unique_records(array $records)
    {
        $unique = array();
        foreach ($records as $record) {
            $unique[(string) $record->id] = $record;
        }
        return array_values($unique);
    }
}

if (!function_exists('lg_rollup_row')) {
    function lg_rollup_row(array $fields = array())
    {
        return $fields + array(
            'record_count' => 0,
            'learners_assessed' => 0,
            'learners_with_gap' => 0,
            'cpl_weighted' => 0.0,
            'cpl_learners' => 0,
            'cpl_record_count' => 0,
        );
    }
}

if (!function_exists('lg_rollup_add')) {
    function lg_rollup_add(array &$row, $record)
    {
        $assessed = (int) $record->learners_assessed;
        $row['record_count']++;
        $row['learners_assessed'] += $assessed;
        $row['learners_with_gap'] += (int) $record->learners_with_gap;
        if (isset($record->class_proficiency_level) && $record->class_proficiency_level !== '') {
            $weight = max(1, $assessed);
            $row['cpl_weighted'] += (float) $record->class_proficiency_level * $weight;
            $row['cpl_learners'] += $weight;
            $row['cpl_record_count']++;
        }
    }
}

if (!function_exists('lg_rollup_finish')) {
    function lg_rollup_finish(array $row)
    {
        $row['gap_rate'] = $row['learners_assessed'] > 0
            ? ($row['learners_with_gap'] / $row['learners_assessed']) * 100
            : null;
        $row['class_proficiency_level'] = $row['cpl_learners'] > 0
            ? round($row['cpl_weighted'] / $row['cpl_learners'], 2)
            : null;
        unset($row['cpl_weighted'], $row['cpl_learners']);
        return (object) $row;
    }
}

if (!function_exists('lg_scope_rollup')) {
    /**
     * Rolls records up per school and per district. Schools with no submissions
     * stay listed so coverage is visible. Schools outside the listed districts
     * collect under 'unassigned', which keeps the district rows adding up to the
     * totals. The totals count real districts only, not that bucket.
     */
    function lg_scope_rollup(array $districts, array $schools, array $records)
    {
        $district_rows = array();
        foreach ($districts as $district) {
            $district_rows[(string) $district->id] = lg_rollup_row(array(
                'key' => (string) $district->id,
                'name' => (string) $district->description,
                'school_total' => 0,
                'schools_reporting' => 0,
            ));
        }

        $school_rows = array();
        $add_school = function ($id, $name, $district_key, $missing_profile) use (&$school_rows, &$district_rows) {
            if (isset($school_rows[$id])) return;
            if (!isset($district_rows[$district_key])) {
                $district_key = 'unassigned';
                if (!isset($district_rows[$district_key])) {
                    $district_rows[$district_key] = lg_rollup_row(array(
                        'key' => $district_key,
                        'name' => 'Unassigned district',
                        'school_total' => 0,
                        'schools_reporting' => 0,
                    ));
                }
            }
            $school_rows[$id] = lg_rollup_row(array(
                'key' => $id,
                'name' => $name !== '' ? $name : 'School ID ' . $id,
                'district_key' => $district_key,
                'district_name' => $district_rows[$district_key]['name'],
                'missing_profile' => $missing_profile,
                'latest_submission' => null,
            ));
            $district_rows[$district_key]['school_total']++;
        };

        foreach ($schools as $school) {
            $add_school((string) $school->schoolID, trim((string) $school->schoolName), (string) $school->district_id, false);
        }

        $totals = lg_rollup_row();
        foreach ($records as $record) {
            $id = (string) $record->school_id;
            // Imported submissions stay visible even when the school profile is missing.
            $add_school($id, trim((string) $record->schoolName), !empty($record->district_name) ? (string) $record->district_id : 'unassigned', true);
            lg_rollup_add($school_rows[$id], $record);
            lg_rollup_add($district_rows[$school_rows[$id]['district_key']], $record);
            lg_rollup_add($totals, $record);
            if ($record->created_at > $school_rows[$id]['latest_submission']) {
                $school_rows[$id]['latest_submission'] = $record->created_at;
            }
        }

        foreach ($school_rows as $row) {
            if ($row['record_count'] > 0) {
                $district_rows[$row['district_key']]['schools_reporting']++;
            }
        }

        $totals['school_total'] = count($school_rows);
        $totals['schools_reporting'] = array_sum(array_column($district_rows, 'schools_reporting'));
        $named_districts = array_diff_key($district_rows, array('unassigned' => true));
        $totals['district_total'] = count($named_districts);
        $totals['districts_reporting'] = count(array_filter($named_districts, function ($row) {
            return $row['record_count'] > 0;
        }));

        $by_name = function ($a, $b) {
            // Keep 'Unassigned district' last.
            if ($a->key === 'unassigned' xor $b->key === 'unassigned') {
                return $a->key === 'unassigned' ? 1 : -1;
            }
            return strnatcasecmp($a->name, $b->name);
        };
        $district_rows = array_map('lg_rollup_finish', $district_rows);
        $school_rows = array_map('lg_rollup_finish', $school_rows);
        uasort($district_rows, $by_name);
        uasort($school_rows, $by_name);

        return array('districts' => $district_rows, 'schools' => $school_rows, 'totals' => lg_rollup_finish($totals));
    }
}

if (!function_exists('lg_term_rollup')) {
    /** Per-term rows in the shape lg_term_interpretation() reads. */
    function lg_term_rollup(array $records)
    {
        $rows = array();
        foreach (array('Term 1', 'Term 2', 'Term 3') as $term) {
            $rows[$term] = lg_rollup_row(array('term' => $term));
        }
        foreach ($records as $record) {
            $term = (string) $record->term;
            if (isset($rows[$term])) {
                lg_rollup_add($rows[$term], $record);
            }
        }
        return array_values(array_map('lg_rollup_finish', $rows));
    }
}

if (!function_exists('lg_group_rollup')) {
    /** Rolls records up by one field, such as grade_level or learning_area. */
    function lg_group_rollup(array $records, $field)
    {
        $rows = array();
        foreach ($records as $record) {
            $name = trim((string) $record->$field);
            if ($name === '') continue;
            if (!isset($rows[$name])) {
                $rows[$name] = lg_rollup_row(array('name' => $name));
            }
            lg_rollup_add($rows[$name], $record);
        }
        uksort($rows, function ($a, $b) {
            // Kindergarten comes before Grade 1.
            if ($a === 'Kindergarten' xor $b === 'Kindergarten') {
                return $a === 'Kindergarten' ? -1 : 1;
            }
            return strnatcasecmp($a, $b);
        });
        return array_values(array_map('lg_rollup_finish', $rows));
    }
}

if (!function_exists('lg_priority_records')) {
    /** A school's records with the highest recorded gap rate come first. */
    function lg_priority_records(array $records, $limit = 10)
    {
        $records = array_values(array_filter($records, function ($record) {
            return (int) $record->learners_assessed > 0;
        }));
        $rate = function ($record) { return (int) $record->learners_with_gap / (int) $record->learners_assessed; };
        usort($records, function ($a, $b) use ($rate) {
            return $rate($b) <=> $rate($a) ?: (int) $b->learners_with_gap <=> (int) $a->learners_with_gap;
        });
        return array_slice($records, 0, $limit);
    }
}

if (!function_exists('lg_term_coverage_caution')) {
    /** Warns when one term has far fewer records than another, which makes their comparison unreliable. */
    function lg_term_coverage_caution(array $term_performance)
    {
        $terms = array_values(array_filter($term_performance, function ($row) { return (int) $row->record_count > 0; }));
        if (count($terms) < 2) return array();
        usort($terms, function ($a, $b) { return (int) $a->record_count <=> (int) $b->record_count; });
        $fewest = $terms[0];
        $most = $terms[count($terms) - 1];
        if ((int) $fewest->record_count * 4 >= (int) $most->record_count) return array();
        return array(html_escape($fewest->term) . ' has only ' . lg_count_label((int) $fewest->record_count, 'record') . ' against ' . number_format((int) $most->record_count) . ' in ' . html_escape($most->term) . ', so treat the term-to-term change as provisional until more schools encode that term.');
    }
}

if (!function_exists('lg_analytics_interpretation')) {
    /** Reads district or school rows from lg_scope_rollup(); $noun names what is compared. */
    function lg_analytics_interpretation(array $rows, $noun)
    {
        $lines = array();
        if (empty($rows)) return $lines;

        $silent = array_values(array_filter($rows, function ($row) { return (int) $row->record_count === 0; }));
        $reporting = count($rows) - count($silent);
        $line = number_format($reporting) . ' of ' . lg_count_label(count($rows), $noun) . ($reporting === 1 ? ' has' : ' have') . ' submitted records.';
        if (!empty($silent)) {
            $names = array_map(function ($row) { return html_escape($row->name); }, array_slice($silent, 0, 3));
            $line .= ' No submissions yet from ' . implode(', ', $names) . (count($silent) > 3 ? ' and ' . number_format(count($silent) - 3) . ' more' : '') . '.';
        }
        $lines[] = $line;

        $assessed = array_values(array_filter($rows, function ($row) { return $row->gap_rate !== null; }));
        if (count($assessed) >= 2) {
            usort($assessed, function ($a, $b) { return $b->gap_rate <=> $a->gap_rate; });
            $high = $assessed[0];
            $low = $assessed[count($assessed) - 1];
            if (number_format($high->gap_rate, 1) !== number_format($low->gap_rate, 1)) {
                $lines[] = '<strong>' . html_escape($high->name) . '</strong> has the highest recorded gap rate at <strong>' . number_format($high->gap_rate, 1) . '%</strong>, while <strong>' . html_escape($low->name) . '</strong> has the lowest at <strong>' . number_format($low->gap_rate, 1) . '%</strong>. Prioritize technical assistance where the rates are highest.';
            }
        }

        $with_cpl = array_values(array_filter($rows, function ($row) { return $row->class_proficiency_level !== null; }));
        if (count($with_cpl) >= 2) {
            usort($with_cpl, function ($a, $b) { return $a->class_proficiency_level <=> $b->class_proficiency_level; });
            $lowest = $with_cpl[0];
            $lines[] = '<strong>' . html_escape($lowest->name) . '</strong> has the lowest CPL at <strong>' . number_format((float) $lowest->class_proficiency_level, 1) . '%</strong>, which falls under <strong>' . lg_cpl_band((float) $lowest->class_proficiency_level) . '</strong>.';
        }
        return $lines;
    }
}
