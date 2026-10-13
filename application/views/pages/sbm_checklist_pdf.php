<?php
$checklist_record = isset($sbmc) && $sbmc ? $sbmc : null;
$principles = isset($sbm) && is_array($sbm) ? $sbm : array();
$indicators = isset($sbm_sub) && is_array($sbm_sub) ? $sbm_sub : array();
$school = isset($school) ? $school : null;
$division = isset($division) ? $division : null;
$district = isset($district) ? $district : null;
$school_id = isset($view_school_id) ? (string) $view_school_id : (string) $this->session->username;

$title_case = static function ($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    return function_exists('mb_convert_case')
        ? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8')
        : ucwords(strtolower($value));
};

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$school_name = $school && trim((string) $school->schoolName) !== ''
    ? $title_case($school->schoolName)
    : $title_case((string) $this->session->user);
$division_name = $division && trim((string) $division->description) !== ''
    ? $title_case($division->description)
    : 'Not assigned';
$district_name = $district && trim((string) $district->description) !== ''
    ? $title_case($district->description)
    : 'Not assigned';
$fiscal_year = (string) $this->session->fy;

$rating_labels = array(
    1 => 'Not Yet Manifested',
    2 => 'Rarely Manifested',
    3 => 'Frequently Manifested',
    4 => 'Always Manifested',
    5 => 'Not Applicable',
);

$rating_counts = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
$questions_by_principle = array();
foreach ($indicators as $indicator) {
    $principle_id = isset($indicator->priciple_id) ? (string) $indicator->priciple_id : '';
    if ($principle_id === '') {
        continue;
    }

    if (!isset($questions_by_principle[$principle_id])) {
        $questions_by_principle[$principle_id] = array();
    }

    $questions_by_principle[$principle_id][] = $indicator;
}

$principle_sections = array();
$answered_count = 0;
$total_questions = count($indicators);

foreach ($principles as $principle) {
    $principle_id = isset($principle->id) ? (string) $principle->id : '';
    $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
    $items = array();

    foreach ($principle_questions as $question) {
        $field = 'q' . (int) $question->i_no;
        $value = ($checklist_record && isset($checklist_record->$field)) ? (int) $checklist_record->$field : 0;

        if ($value > 0) {
            $answered_count++;
            if (isset($rating_counts[$value])) {
                $rating_counts[$value]++;
            }
        }

        $items[] = array(
            'number' => (int) $question->i_no,
            'text' => (string) $question->description,
            'rating' => ($value > 0 && isset($rating_labels[$value])) ? $rating_labels[$value] : 'No selection',
        );
    }

    $principle_sections[] = array(
        'title' => (string) $principle->indicator,
        'description' => (string) $principle->description,
        'items' => $items,
    );
}

$completion_rate = $total_questions > 0 ? ($answered_count / $total_questions) * 100 : 0;
$pdf_payload = array(
    'title' => 'School-Based Management Self-Assessment Checklist',
    'fiscal_year' => $fiscal_year,
    'school_name' => $school_name,
    'school_id' => $school_id,
    'district_name' => $district_name,
    'division_name' => $division_name,
    'answered_count' => $answered_count,
    'total_questions' => $total_questions,
    'completion_rate' => round($completion_rate, 1),
    'rating_counts' => array(
        array('label' => 'Not Yet Manifested', 'count' => (int) $rating_counts[1]),
        array('label' => 'Rarely Manifested', 'count' => (int) $rating_counts[2]),
        array('label' => 'Frequently Manifested', 'count' => (int) $rating_counts[3]),
        array('label' => 'Always Manifested', 'count' => (int) $rating_counts[4]),
        array('label' => 'Not Applicable', 'count' => (int) $rating_counts[5]),
    ),
    'principles' => $principle_sections,
);

$pdf_file_name = 'SBM-Checklist-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $school_id) . '-FY' . preg_replace('/[^0-9]/', '', $fiscal_year) . '.pdf';
$position = strtolower(trim((string) $this->session->position));
$is_school_user = $position === 'school';
$back_url = $is_school_user
    ? base_url() . 'Pages/sbm_checklist'
    : base_url() . 'Pages/checklist_district/' . rawurlencode($school_id);
$dashboard_url = base_url();
$success_message = $this->session->flashdata('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $escape($title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(214, 168, 75, 0.18), transparent 26%),
                linear-gradient(180deg, #fffaf1 0%, #f7f9fc 100%);
            color: #172033;
        }

        .pdf-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .pdf-card {
            width: min(760px, 100%);
            padding: 32px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid #e4e9f0;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
        }

        .pdf-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(127, 29, 29, 0.1);
            color: #7f1d1d;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .pdf-card h1 {
            margin: 16px 0 10px;
            color: #7f1d1d;
            font-size: clamp(28px, 4vw, 36px);
            line-height: 1.1;
        }

        .pdf-card p {
            margin: 0;
            color: #687386;
            line-height: 1.7;
        }

        .pdf-flash {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-weight: 600;
        }

        .pdf-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .pdf-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #f7f9fc;
            border: 1px solid #e4e9f0;
            color: #172033;
            font-size: 14px;
        }

        .pdf-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .pdf-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 18px;
            border-radius: 14px;
            border: 1px solid transparent;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        }

        .pdf-button:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .pdf-button-primary {
            background: #7f1d1d;
            color: #fff;
            box-shadow: 0 16px 32px rgba(127, 29, 29, 0.18);
        }

        .pdf-button-secondary {
            background: #fff;
            color: #7f1d1d;
            border-color: rgba(127, 29, 29, 0.14);
        }

        .pdf-status {
            margin-top: 18px;
            color: #4b5563;
            font-size: 14px;
        }

        @media (max-width: 640px) {
            .pdf-card {
                padding: 24px 18px;
            }

            .pdf-actions {
                flex-direction: column;
            }

            .pdf-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="pdf-shell">
        <div class="pdf-card">
            <span class="pdf-eyebrow">Checklist Finalized</span>
            <h1>Checklist PDF Ready</h1>
            <p>Your finalized checklist PDF should start downloading automatically. If nothing happens, use the download button below.</p>

            <?php if ($success_message) : ?>
                <div class="pdf-flash"><?= $escape($success_message); ?></div>
            <?php endif; ?>

            <div class="pdf-meta">
                <span><?= $escape($school_name); ?></span>
                <span>School ID <?= $escape($school_id); ?></span>
                <span><?= $escape($district_name); ?></span>
                <span><?= $escape($division_name); ?></span>
                <span>Fiscal Year <?= $escape($fiscal_year); ?></span>
            </div>

            <div class="pdf-actions">
                <button type="button" class="pdf-button pdf-button-primary" onclick="downloadChecklistPdf()">Download PDF</button>
                <a href="<?= $back_url; ?>" class="pdf-button pdf-button-secondary">Back to Checklist</a>
                <a href="<?= $dashboard_url; ?>" class="pdf-button pdf-button-secondary">Dashboard</a>
            </div>

            <p id="pdfStatus" class="pdf-status">Preparing PDF content...</p>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
    <script>
    const checklistPdfPayload = <?= json_encode($pdf_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const checklistPdfFileName = <?= json_encode($pdf_file_name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    function buildChecklistPdfDefinition(data) {
        const content = [
            { text: data.title, style: "title" },
            { text: "Fiscal Year " + data.fiscal_year, style: "subtitle" },
            {
                columns: [
                    [
                        { text: "School", style: "metaLabel" },
                        { text: data.school_name, style: "metaValue" }
                    ],
                    [
                        { text: "School ID", style: "metaLabel" },
                        { text: data.school_id, style: "metaValue" }
                    ],
                    [
                        { text: "District", style: "metaLabel" },
                        { text: data.district_name, style: "metaValue" }
                    ],
                    [
                        { text: "Division", style: "metaLabel" },
                        { text: data.division_name, style: "metaValue" }
                    ]
                ],
                columnGap: 18,
                margin: [0, 0, 0, 18]
            },
            {
                table: {
                    widths: ["*", "*", "*", "*"],
                    body: [
                        [
                            { text: "Answered Indicators", style: "summaryHeader" },
                            { text: "Completion Rate", style: "summaryHeader" },
                            { text: "Always Manifested", style: "summaryHeader" },
                            { text: "Not Applicable", style: "summaryHeader" }
                        ],
                        [
                            { text: data.answered_count + " / " + data.total_questions, style: "summaryValue" },
                            { text: Number(data.completion_rate).toFixed(1) + "%", style: "summaryValue" },
                            { text: String(data.rating_counts[3].count), style: "summaryValue" },
                            { text: String(data.rating_counts[4].count), style: "summaryValue" }
                        ]
                    ]
                },
                layout: "lightHorizontalLines",
                margin: [0, 0, 0, 18]
            }
        ];

        data.principles.forEach(function (principle) {
            content.push(
                { text: principle.title, style: "sectionTitle", margin: [0, 0, 0, 4] },
                { text: principle.description, style: "sectionDescription", margin: [0, 0, 0, 8] }
            );

            const body = [[
                { text: "#", style: "tableHeader" },
                { text: "Indicator", style: "tableHeader" },
                { text: "Selected Rating", style: "tableHeader" }
            ]];

            principle.items.forEach(function (item) {
                body.push([
                    { text: String(item.number), style: "cellNumber" },
                    { text: item.text, style: "cellText" },
                    { text: item.rating, style: "cellRating" }
                ]);
            });

            content.push({
                table: {
                    headerRows: 1,
                    widths: [24, "*", 130],
                    body: body
                },
                layout: {
                    hLineColor: function () { return "#d7dee8"; },
                    vLineColor: function () { return "#d7dee8"; },
                    fillColor: function (rowIndex) { return rowIndex === 0 ? "#f4f7fb" : null; },
                    paddingLeft: function () { return 8; },
                    paddingRight: function () { return 8; },
                    paddingTop: function () { return 6; },
                    paddingBottom: function () { return 6; }
                },
                margin: [0, 0, 0, 14]
            });
        });

        return {
            pageSize: "A4",
            pageOrientation: "landscape",
            pageMargins: [28, 32, 28, 36],
            footer: function (currentPage, pageCount) {
                return {
                    text: "Generated from the SBM system | Page " + currentPage + " of " + pageCount,
                    alignment: "right",
                    margin: [0, 0, 24, 0],
                    fontSize: 8,
                    color: "#687386"
                };
            },
            content: content,
            defaultStyle: {
                fontSize: 9,
                color: "#172033"
            },
            styles: {
                title: {
                    fontSize: 20,
                    bold: true,
                    color: "#7f1d1d",
                    margin: [0, 0, 0, 4]
                },
                subtitle: {
                    fontSize: 11,
                    color: "#687386",
                    margin: [0, 0, 0, 16]
                },
                metaLabel: {
                    fontSize: 8,
                    bold: true,
                    color: "#687386",
                    margin: [0, 0, 0, 3]
                },
                metaValue: {
                    fontSize: 10,
                    bold: true,
                    color: "#172033"
                },
                summaryHeader: {
                    bold: true,
                    color: "#687386",
                    fillColor: "#f4f7fb",
                    margin: [0, 4, 0, 4]
                },
                summaryValue: {
                    fontSize: 12,
                    bold: true,
                    color: "#172033",
                    margin: [0, 6, 0, 6]
                },
                sectionTitle: {
                    fontSize: 12,
                    bold: true,
                    color: "#172033"
                },
                sectionDescription: {
                    fontSize: 9,
                    color: "#687386",
                    lineHeight: 1.4
                },
                tableHeader: {
                    bold: true,
                    color: "#172033",
                    fontSize: 9
                },
                cellNumber: {
                    alignment: "center",
                    fontSize: 8,
                    color: "#475569"
                },
                cellText: {
                    fontSize: 8.5,
                    lineHeight: 1.35,
                    color: "#172033"
                },
                cellRating: {
                    fontSize: 8.5,
                    bold: true,
                    color: "#7f1d1d"
                }
            }
        };
    }

    function setPdfStatus(message, isError) {
        const status = document.getElementById("pdfStatus");
        if (!status) {
            return;
        }

        status.textContent = message;
        status.style.color = isError ? "#b91c1c" : "#4b5563";
    }

    function downloadChecklistPdf() {
        if (typeof pdfMake === "undefined") {
            setPdfStatus("PDF generator is unavailable on this page. Please reload and try again.", true);
            return;
        }

        try {
            setPdfStatus("Generating PDF...");
            pdfMake.createPdf(buildChecklistPdfDefinition(checklistPdfPayload)).download(checklistPdfFileName);
            setPdfStatus("PDF download started. If nothing happened, check your browser download settings and use the button again.");
        } catch (error) {
            setPdfStatus("Unable to generate the PDF automatically. Please try the download button again.", true);
        }
    }

    window.addEventListener("load", function () {
        window.setTimeout(downloadChecklistPdf, 350);
    });
    </script>
</body>
</html>
