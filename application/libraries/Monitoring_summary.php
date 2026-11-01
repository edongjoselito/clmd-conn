<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring_summary
{
    public static function calculate($items)
    {
        $total = count($items); $answered = 0; $evident = 0;
        foreach ($items as $item) {
            if (in_array($item['rating'], array('evident', 'not_evident'), true)) { $answered++; }
            if ($item['rating'] === 'evident') { $evident++; }
        }
        $score = $total ? 100 * $evident / $total : null;
        $description = 'Not assessed';
        if ($total && $answered < $total) { $description = 'Incomplete'; }
        elseif ($total) {
            // Preserve the source ranges; do not silently classify its uncovered scores.
            if ($score < 50) { $description = 'Needs Improvement'; }
            elseif ($score >= 51 && $score <= 62) { $description = 'Fairly Meets Expectation'; }
            elseif ($score >= 63 && $score <= 75) { $description = 'Satisfactorily Meets Expectation'; }
            elseif ($score >= 76 && $score <= 88) { $description = 'Very Satisfactory'; }
            elseif ($score >= 89) { $description = 'Outstanding'; }
            else { $description = 'Not defined in source scale'; }
        }
        return array('total' => $total, 'answered' => $answered, 'evident' => $evident,
            'score' => $total && $answered > 0 ? number_format($score, 2) . '%' : '—', 'description' => $description);
    }

    private static function rated($items)
    {
        $result = self::calculate($items);
        $result['percent'] = $result['answered'] > 0 ? round(100 * $result['evident'] / $result['total'], 2) : null;
        return $result;
    }

    /**
     * Rolls saved assessment payloads up to one summary per division. Ratings use
     * fully assessed forms only: calculate() scores an unanswered indicator like
     * Not Evident, which would understate a form that is still in progress.
     */
    public static function by_division($payloads, $groups, $divisions)
    {
        $rows = array();
        foreach ($divisions as $division) {
            $rows[(int) $division->id] = array('id' => (int) $division->id, 'name' => (string) $division->description,
                'assessed' => 0, 'in_progress' => 0, 'schools' => array(), 'items' => array(), 'forms' => array());
        }
        foreach ($payloads as $payload) {
            $id = isset($payload['division_id']) ? (int) $payload['division_id'] : 0;
            if (!isset($rows[$id]) || empty($payload['items'])) { continue; }
            $overall = self::rated($payload['items']);
            $complete = $overall['answered'] === $overall['total'];
            if ($complete) {
                $rows[$id]['assessed']++;
                $rows[$id]['schools'][(string) $payload['school_id']] = true;
                foreach ($payload['items'] as $item) { $rows[$id]['items'][] = $item; }
            } else {
                $rows[$id]['in_progress']++;
            }
            $details = isset($payload['details']) ? $payload['details'] : array();
            $form = array('record_id' => isset($payload['record_id']) ? (int) $payload['record_id'] : 0,
                'school_id' => (string) $payload['school_id'], 'school_name' => (string) $payload['school_name'],
                'district_name' => (string) $payload['district_name'], 'complete' => $complete, 'overall' => $overall);
            foreach (array('monitor_date', 'strengths', 'technical_assistance', 'recommendations') as $key) {
                $form[$key] = isset($details[$key]) ? (string) $details[$key] : '';
            }
            $rows[$id]['forms'][] = $form;
        }
        foreach ($rows as &$row) {
            $by_area = array_fill(0, count($groups), array());
            foreach ($row['items'] as $item) { $by_area[$item['area']][] = $item; }
            $row['areas'] = array();
            foreach ($groups as $area => $group) {
                $row['areas'][] = array('name' => $group['name']) + self::rated($by_area[$area]);
            }
            $row['overall'] = self::rated($row['items']);
            $row['school_count'] = count($row['schools']);
            unset($row['items'], $row['schools']);
            usort($row['forms'], function ($left, $right) {
                return strnatcasecmp($left['district_name'], $right['district_name'])
                    ?: strnatcasecmp($left['school_name'], $right['school_name']);
            });
        }
        unset($row);
        return array_values($rows);
    }
}
