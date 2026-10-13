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
}
