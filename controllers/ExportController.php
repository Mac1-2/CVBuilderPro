<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$cvModel = new Cv();
$action = $route[1] ?? 'index';
$cvId = (int)($route[2] ?? 0);

$cv = $cvModel->getFullData($cvId, $_SESSION['user_id']);
if (!$cv) { json_response(['error' => 'CV not found'], 404); }

if ($action === 'pdf') {
    $options = new Options();
    $options->set('defaultFont', 'Helvetica');
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);

    $html = renderCvHtml($cv);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = preg_replace('/[^a-z0-9]+/i', '_', $cv['title']) . '.pdf';
    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo $dompdf->output();
    exit;
}

if ($action === 'word') {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['marginTop' => 1440, 'marginBottom' => 1440, 'marginLeft' => 1440, 'marginRight' => 1440]);

    $sections = $cv['sections'];
    foreach ($sections as $s) {
        if (!$s['is_visible']) continue;
        $content = json_decode($s['content_json'], true);

        if ($s['section_type'] === 'personal') {
            $name = trim(($content['first_name'] ?? '') . ' ' . ($content['last_name'] ?? ''));
            if ($name) {
                $t = $section->addText($name, ['size' => 22, 'bold' => true, 'spaceAfter' => 40]);
            }
            if (!empty($content['job_title'])) {
                $section->addText($content['job_title'], ['size' => 14, 'color' => '4f46e5', 'spaceAfter' => 80]);
            }
            $contactParts = array_filter([$content['email'] ?? '', $content['phone'] ?? '', $content['address'] ?? '', $content['linkedin'] ?? '']);
            if (!empty($contactParts)) {
                $section->addText(implode(' | ', $contactParts), ['size' => 10, 'color' => '666666', 'spaceAfter' => 160]);
            }
        } elseif (in_array($s['section_type'], ['summary', 'experience', 'education', 'skills', 'languages', 'certifications', 'references'])) {
            $section->addText($s['title'], ['size' => 14, 'bold' => true, 'spaceBefore' => 160, 'spaceAfter' => 80]);
            $section->addLine(['weight' => 1, 'width' => 500, 'color' => '4f46e5']);

            if ($s['section_type'] === 'summary' && !empty($content['text'])) {
                $section->addText($content['text'], ['size' => 11, 'spaceAfter' => 80]);
            } elseif ($s['section_type'] === 'experience' && !empty($content['entries'])) {
                foreach ($content['entries'] as $entry) {
                    $section->addText($entry['title'] ?? '', ['size' => 12, 'bold' => true]);
                    $company = trim(($entry['company'] ?? '') . ($entry['location'] ? ', ' . $entry['location'] : ''));
                    if ($company) $section->addText($company, ['size' => 11, 'color' => '4f46e5']);
                    if (!empty($entry['start_date'])) {
                        $dateStr = $entry['start_date'] . ($entry['end_date'] ? ' - ' . $entry['end_date'] : ' - Present');
                        $section->addText($dateStr, ['size' => 10, 'color' => '888888', 'italic' => true]);
                    }
                    if (!empty($entry['description'])) {
                        $section->addText($entry['description'], ['size' => 11, 'spaceAfter' => 80]);
                    }
                }
            } elseif ($s['section_type'] === 'education' && !empty($content['entries'])) {
                foreach ($content['entries'] as $entry) {
                    $section->addText($entry['degree'] ?? '', ['size' => 12, 'bold' => true]);
                    if (!empty($entry['school'])) $section->addText($entry['school'], ['size' => 11, 'color' => '4f46e5']);
                    if (!empty($entry['year'])) $section->addText($entry['year'], ['size' => 10, 'color' => '888888']);
                    if (!empty($entry['description'])) $section->addText($entry['description'], ['size' => 11, 'spaceAfter' => 80]);
                }
            } elseif ($s['section_type'] === 'skills' && !empty($content['entries'])) {
                $skillNames = array_map(fn($e) => $e['name'] ?? '', $content['entries']);
                $section->addText(implode(', ', $skillNames), ['size' => 11, 'spaceAfter' => 80]);
            } elseif ($s['section_type'] === 'languages' && !empty($content['entries'])) {
                foreach ($content['entries'] as $entry) {
                    $section->addText(($entry['language'] ?? '') . ' - ' . ($entry['level'] ?? ''), ['size' => 11, 'spaceAfter' => 40]);
                }
            } elseif ($s['section_type'] === 'certifications' && !empty($content['entries'])) {
                foreach ($content['entries'] as $entry) {
                    $text = $entry['name'] ?? '';
                    if (!empty($entry['issuer'])) $text .= ' - ' . $entry['issuer'];
                    if (!empty($entry['date'])) $text .= ' (' . $entry['date'] . ')';
                    $section->addText($text, ['size' => 11, 'spaceAfter' => 40]);
                }
            }
        }
    }

    $filename = preg_replace('/[^a-z0-9]+/i', '_', $cv['title']) . '.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;
}

function renderCvHtml(array $cv): string {
    $sections = $cv['sections'];
    $data = [];
    foreach ($sections as $s) {
        $data[$s['section_type']] = json_decode($s['content_json'], true);
    }

    $personal = $data['personal'] ?? [];
    $fullName = trim(($personal['first_name'] ?? '') . ' ' . ($personal['last_name'] ?? ''));
    $firstInitial = strtoupper(substr($personal['first_name'] ?? '', 0, 1));
    $lastInitial = strtoupper(substr($personal['last_name'] ?? '', 0, 1));

    $contactParts = array_filter([
        $personal['email'] ?? '',
        $personal['phone'] ?? '',
        $personal['address'] ?? '',
        $personal['linkedin'] ?? '',
        $personal['website'] ?? ''
    ]);
    $contactHtml = implode(' &bull; ', $contactParts);

    $summary = $data['summary']['text'] ?? '';

    $html = $cv['html_structure'] ?? '';

    $html = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{first_initial}}', '{{last_initial}}', '{{job_title}}', '{{email}}', '{{phone}}', '{{address}}', '{{linkedin}}', '{{website}}', '{{photo}}'],
        [$personal['first_name'] ?? '', $personal['last_name'] ?? '', $firstInitial, $lastInitial, $personal['job_title'] ?? '', $personal['email'] ?? '', $personal['phone'] ?? '', $personal['address'] ?? '', $personal['linkedin'] ?? '', $personal['website'] ?? '', ''],
        $html
    );

    $contactBlock = '';
    if (!empty($personal['email'])) $contactBlock .= "<p>{$personal['email']}</p>";
    if (!empty($personal['phone'])) $contactBlock .= "<p>{$personal['phone']}</p>";
    if (!empty($personal['address'])) $contactBlock .= "<p>{$personal['address']}</p>";
    if (!empty($personal['linkedin'])) $contactBlock .= "<p class='rela-block social linked-in'>{$personal['linkedin']}</p>";
    $html = str_replace('{{contact_entries}}', $contactBlock, $html);
    $html = str_replace('{{summary}}', $summary, $html);

    $experienceHtml = '';
    if (!empty($data['experience']['entries'])) {
        foreach ($data['experience']['entries'] as $entry) {
            $dateStr = ($entry['start_date'] ?? '') . ($entry['end_date'] ? ' - ' . $entry['end_date'] : ' - Present');
            $locationStr = !empty($entry['location']) ? ', ' . $entry['location'] : '';
            $experienceHtml .= "<div style='margin-bottom:15px;'>
                <div style='font-weight:bold;font-size:14px;'>{$entry['title']}</div>
                <div style='color:#4f46e5;font-size:13px;'>{$entry['company']}{$locationStr}</div>
                <div style='color:#888;font-size:12px;font-style:italic;'>{$dateStr}</div>
                <div style='font-size:13px;line-height:1.6;margin-top:4px;'>{$entry['description']}</div>
            </div>";
        }
    }
    $html = str_replace('{{experience_entries}}', $experienceHtml, $html);

    $educationHtml = '';
    if (!empty($data['education']['entries'])) {
        foreach ($data['education']['entries'] as $entry) {
            $educationHtml .= "<div style='margin-bottom:10px;'>
                <div style='font-weight:bold;font-size:14px;'>{$entry['degree']}</div>
                <div style='color:#4f46e5;font-size:13px;'>{$entry['school']}</div>
                <div style='color:#888;font-size:12px;'>{$entry['year']}</div>
            </div>";
        }
    }
    $html = str_replace('{{education_entries}}', $educationHtml, $html);

    $skillsHtml = '';
    if (!empty($data['skills']['entries'])) {
        foreach ($data['skills']['entries'] as $entry) {
            $skillsHtml .= "<span style='display:inline-block;background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;margin:3px;'>{$entry['name']}</span>";
        }
    }
    $html = str_replace('{{skills_entries}}', $skillsHtml, $html);

    $langHtml = '';
    if (!empty($data['languages']['entries'])) {
        foreach ($data['languages']['entries'] as $entry) {
            $langHtml .= "<div style='font-size:13px;'>{$entry['language']} - {$entry['level']}</div>";
        }
    }
    $html = str_replace('{{language_entries}}', $langHtml, $html);

    $certHtml = '';
    if (!empty($data['certifications']['entries'])) {
        foreach ($data['certifications']['entries'] as $entry) {
            $text = $entry['name'] ?? '';
            if (!empty($entry['issuer'])) $text .= ' - ' . $entry['issuer'];
            if (!empty($entry['date'])) $text .= ' (' . $entry['date'] . ')';
            $certHtml .= "<div style='font-size:13px;'>{$text}</div>";
        }
    }
    $html = str_replace('{{certification_entries}}', $certHtml, $html);

    $html = str_replace(['{{project_entries}}', '{{publication_entries}}', '{{skill_group_entries}}', '{{highlight_entries}}', '{{interest_entries}}', '{{reference_entries}}'], '', $html);

    return "<!DOCTYPE html><html><head><meta charset='UTF-8'>{$cv['css_styles']}</head><body><div class='cv-{$cv['template_slug']}'>{$html}</div></body></html>";
}
