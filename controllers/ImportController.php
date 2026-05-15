<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

$cvModel = new Cv();
$action = $route[1] ?? 'index';
$importId = $route[2] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'upload') {
    if (!isset($_FILES['file'])) {
        json_response(['error' => 'No file uploaded'], 400);
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'docx', 'doc', 'txt', 'rtf'];

    if (!in_array($ext, $allowed)) {
        json_response(['error' => 'Allowed formats: PDF, DOCX, DOC, TXT, RTF'], 400);
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        json_response(['error' => 'File too large. Maximum 10MB.'], 400);
    }

    $tmpPath = IMPORT_TEMP_PATH . '/' . uniqid('import_') . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
        json_response(['error' => 'Failed to upload file'], 500);
    }

    $text = '';
    if ($ext === 'docx') {
        try {
            $phpWord = IOFactory::load($tmpPath);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        // Only process child elements if parent has no getText
                        $hasText = false;
                        foreach ($element->getElements() as $child) {
                            if (method_exists($child, 'getText')) {
                                $childText = $child->getText();
                                if ($childText !== '') {
                                    $text .= $childText . "\n";
                                    $hasText = true;
                                }
                            }
                        }
                        // If children had no text, try other methods
                        if (!$hasText && method_exists($element, '__toString')) {
                            $text .= $element . "\n";
                        }
                    }
                }
            }
            // Remove duplicate consecutive lines
            $lines = explode("\n", $text);
            $uniqueLines = [];
            $prevLine = '';
            foreach ($lines as $line) {
                if ($line !== $prevLine) {
                    $uniqueLines[] = $line;
                }
                $prevLine = $line;
            }
            $text = implode("\n", $uniqueLines);
        } catch (Exception $e) {
            @unlink($tmpPath);
            json_response(['error' => 'Failed to parse DOCX: ' . $e->getMessage()], 500);
        }
    } elseif ($ext === 'pdf') {
        $output = [];
        $returnCode = 0;
        exec("pdftotext -layout " . escapeshellarg($tmpPath) . " - 2>&1", $output, $returnCode);
        if ($returnCode === 0) {
            $text = implode("\n", $output);
            // Fix common PDF encoding issues: normalize mangled bullet characters
            $text = preg_replace('/\xE2\x80\xA2/', '•', $text); // UTF-8 bullet
            $text = str_replace("\xC3\xA2\\200\xC2\xA2", '•', $text); // mangled bullet
            $text = str_replace("\xC3\xA2\xC2\x80\xC2\xA2", '•', $text); // double-mangled bullet
            $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xC2-\xF4][\x80-\xBF]*/', '•', $text); // fallback for other mangled chars
        } else {
            @unlink($tmpPath);
            json_response(['error' => 'Failed to extract text from PDF. The file may be image-based.'], 500);
        }
    } elseif ($ext === 'txt') {
        $text = file_get_contents($tmpPath);
    } elseif ($ext === 'rtf') {
        $text = strip_tags(file_get_contents($tmpPath));
    }

    @unlink($tmpPath);

    if (trim($text) === '') {
        json_response(['error' => 'No text content found in the file.'], 400);
    }

    $parsedData = parseTextToCvData($text);

    $importSessionId = bin2hex(random_bytes(16));
    $_SESSION['import_data_' . $importSessionId] = $parsedData;

    json_response([
        'success' => true,
        'import_id' => $importSessionId,
        'parsed_data' => $parsedData,
        'file_name' => $file['name'],
        'text_length' => strlen($text)
    ]);
}

if ($action === 'review' && $importId) {
    $parsedData = $_SESSION['import_data_' . $importId] ?? null;
    if (!$parsedData) {
        redirect(BASE_URL . '/import');
    }
    $pageTitle = 'Review Imported CV';
    $extraCss = ['import.css'];
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/import/review.php';
    require __DIR__ . '/../views/layouts/footer.php';
    exit;
}

if ($action === 'create-cv' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $importId = $input['import_id'] ?? '';
    $parsedData = $_SESSION['import_data_' . $importId] ?? null;

    if (!$parsedData) {
        json_response(['error' => 'Import data expired. Please upload again.'], 400);
    }

    $overrides = $input['overrides'] ?? [];
    $data = array_replace_recursive($parsedData, $overrides);

    $title = trim($input['title'] ?? ($data['personal']['first_name'] . ' ' . $data['personal']['last_name'] . ' - CV') ?: 'Imported CV');
    $templateId = (int)($input['template_id'] ?? 1);

    $cvId = $cvModel->create($_SESSION['user_id'], $title, $templateId);

    foreach ($data as $sectionType => $content) {
        if ($sectionType === 'personal') {
            $cvModel->saveSection($cvId, 'personal', $content);
        } elseif ($sectionType === 'summary') {
            $cvModel->saveSection($cvId, 'summary', ['text' => is_array($content) ? ($content['text'] ?? '') : $content]);
        } elseif (in_array($sectionType, ['experience', 'education', 'skills', 'languages', 'certifications', 'references'])) {
            $entries = is_array($content) ? (isset($content['entries']) ? $content['entries'] : $content) : [];
            if (!empty($entries)) {
                $cvModel->saveSection($cvId, $sectionType, ['entries' => $entries]);
            }
        }
    }

    unset($_SESSION['import_data_' . $importId]);

    json_response(['success' => true, 'cv_id' => $cvId]);
}

function parseTextToCvData(string $text): array {
    $lines = explode("\n", trim($text));
    $lines = array_map('trim', $lines);
    $lines = array_filter($lines, fn($l) => $l !== '');
    $lines = array_values($lines);

    $data = [
        'personal' => ['first_name' => '', 'last_name' => '', 'job_title' => '', 'email' => '', 'phone' => '', 'address' => '', 'linkedin' => '', 'website' => '', 'photo' => ''],
        'summary' => ['text' => ''],
        'experience' => ['entries' => []],
        'education' => ['entries' => []],
        'skills' => ['entries' => []],
        'languages' => ['entries' => []],
        'certifications' => ['entries' => []],
        'references' => ['entries' => []],
    ];

    $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
    $phonePattern = '/(\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/';
    $linkedinPattern = '/linkedin\.com\/in\/[a-zA-Z0-9_-]+/i';
    $urlPattern = '/https?:\/\/[^\s]+/i';
    $datePattern = '/^((january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{4}|\d{4})/i';
    $dateRangePattern = '/((january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{4}|\d{4})\s*[-–—to]+\s*((january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{4}|\d{4}|present|current)/i';

    // Phase 1: Extract personal info from first 8 lines
    $headerEnd = 0;
    $nameFound = false;
    $titleFound = false;

    for ($i = 0; $i < min(8, count($lines)); $i++) {
        $line = $lines[$i];
        if (preg_match($emailPattern, $line, $m)) {
            $data['personal']['email'] = $m[0];
            $headerEnd = max($headerEnd, $i + 1);
        }
        if (preg_match($phonePattern, $line, $m)) {
            $data['personal']['phone'] = $m[0];
            $headerEnd = max($headerEnd, $i + 1);
        }
        if (preg_match($linkedinPattern, $line, $m)) {
            $data['personal']['linkedin'] = 'https://' . $m[0];
            $headerEnd = max($headerEnd, $i + 1);
        }
        if (preg_match($urlPattern, $line, $m) && !preg_match($linkedinPattern, $m[0])) {
            $data['personal']['website'] = $m[0];
            $headerEnd = max($headerEnd, $i + 1);
        }
        // Detect section headers - stop header parsing here
        if (detectSection($line)) {
            break;
        }
        // First line without contact info patterns is likely the name
        if (!$nameFound && !preg_match($emailPattern, $line) && !preg_match($phonePattern, $line) && !preg_match($linkedinPattern, $line) && !preg_match($urlPattern, $line) && !preg_match('/^[|•\-\*]/', $line) && strlen($line) > 2) {
            $nameParts = preg_split('/\s+/', $line, 3);
            if (count($nameParts) >= 2) {
                $data['personal']['first_name'] = $nameParts[0];
                $data['personal']['last_name'] = implode(' ', array_slice($nameParts, 1));
                $nameFound = true;
                $headerEnd = max($headerEnd, $i + 1);
            }
        } elseif ($nameFound && !$titleFound && !preg_match($emailPattern, $line) && !preg_match($phonePattern, $line) && strlen($line) > 2 && !preg_match('/^[|•\-\*]/', $line)) {
            // Second meaningful line is likely job title
            $data['personal']['job_title'] = $line;
            $titleFound = true;
            $headerEnd = max($headerEnd, $i + 1);
        }
    }

    // Phase 2: Parse sections
    $currentSection = null;
    $currentEntry = null;
    $summaryLines = [];
    $pendingTitle = null; // For experience: store title before finding company/date

    for ($i = $headerEnd; $i < count($lines); $i++) {
        $line = $lines[$i];
        if (empty($line)) continue;

        // Check if this line is a section header
        $detected = detectSection($line);
        if ($detected) {
            // Save current entry before switching sections
            if ($currentEntry) {
                addEntry($data, $currentSection, $currentEntry);
                $currentEntry = null;
            }
            $currentSection = $detected;
            $pendingTitle = null;
            continue;
        }

        // If no section detected yet, try to infer from content
        if (!$currentSection) {
            // Could be summary text before any section header
            if (strlen($line) > 20 && !preg_match($datePattern, $line)) {
                $currentSection = 'summary';
            }
        }

        switch ($currentSection) {
            case 'summary':
                // Collect summary lines until we hit something that looks like a new section or entry
                if (preg_match('/^[A-Z][a-zA-Z\s&,.]+$/', $line) && strlen($line) < 60 && !$currentEntry) {
                    // Might be a stray header or title, but if we're in summary, keep collecting
                    $summaryLines[] = $line;
                } else {
                    $summaryLines[] = $line;
                }
                break;

            case 'experience':
                parseExperienceLine($line, $data, $currentEntry, $pendingTitle, $datePattern, $dateRangePattern);
                break;

            case 'education':
                parseEducationLine($line, $data, $currentEntry, $datePattern, $dateRangePattern);
                break;

            case 'skills':
                parseSkillsLine($line, $data);
                break;

            case 'languages':
                parseLanguageLine($line, $data);
                break;

            case 'certifications':
                parseCertificationLine($line, $data, $currentEntry, $datePattern);
                break;

            case 'references':
                parseReferenceLine($line, $data, $currentEntry);
                break;

            default:
                // Try to auto-detect section from content patterns
                if (preg_match($emailPattern, $line) || preg_match($phonePattern, $line)) {
                    // Skip contact info outside header
                } elseif (preg_match($datePattern, $line) && !$currentEntry) {
                    // Likely experience or education
                    $currentSection = 'experience';
                    parseExperienceLine($line, $data, $currentEntry, $pendingTitle, $datePattern, $dateRangePattern);
                }
                break;
        }
    }

    // Save final entry
    if ($currentEntry) {
        addEntry($data, $currentSection, $currentEntry);
    }

    // Combine summary lines
    $summaryText = trim(implode(' ', $summaryLines));
    // Clean up summary: remove section header words if they leaked in
    $summaryText = preg_replace('/^(professional summary|summary|profile|objective|career objective|about me|about|personal statement)\s*[:\-]?\s*/i', '', $summaryText);
    $data['summary']['text'] = $summaryText;

    // Fallback: if no name found, use first line
    if (empty($data['personal']['first_name']) && !empty($lines)) {
        $nameParts = preg_split('/\s+/', $lines[0], 3);
        if (count($nameParts) >= 2) {
            $data['personal']['first_name'] = $nameParts[0];
            $data['personal']['last_name'] = implode(' ', array_slice($nameParts, 1));
        }
    }

    // Clean up entries
    $data['experience']['entries'] = array_values(array_filter($data['experience']['entries'], fn($e) => !empty($e['title']) || !empty($e['company'])));
    $data['education']['entries'] = array_values(array_filter($data['education']['entries'], fn($e) => !empty($e['degree']) || !empty($e['school'])));
    $data['skills']['entries'] = array_values(array_filter($data['skills']['entries'], fn($e) => !empty($e['name'])));

    return $data;
}

function parseExperienceLine(string $line, array &$data, ?array &$currentEntry, ?string &$pendingTitle, string $datePattern, string $dateRangePattern): void {
    $isDate = preg_match($datePattern, $line);
    $isDateRange = preg_match($dateRangePattern, $line, $dateMatch);
    $isBullet = preg_match('/^[•\-\*]\s*/', $line) || preg_match('/^[-*]\s+/', $line);
    $cleanLine = preg_replace('/^[•\-\*\s]+/', '', $line);
    $isTitleLike = preg_match('/^[A-Z][a-zA-Z\s&,.]+$/', $line) && strlen($line) < 60 && !preg_match('/(inc|llc|corp|ltd|co\.|company|group|agency|university)/i', $line);

    // Bullet point - add to description if we have an entry
    if ($isBullet && $currentEntry) {
        $currentEntry['description'] .= ($currentEntry['description'] ? "\n" : '') . $cleanLine;
        return;
    }

    // If we have a complete entry and see a title-like line, start new entry
    if ($currentEntry && !empty($currentEntry['title']) && !empty($currentEntry['company']) && $isTitleLike) {
        addEntry($data, 'experience', $currentEntry);
        $currentEntry = ['title' => $line, 'company' => '', 'start_date' => '', 'end_date' => '', 'location' => '', 'description' => ''];
        return;
    }

    // Date line
    if ($isDate) {
        // If we have a pending title, create entry with it
        if ($pendingTitle && (!$currentEntry || (!empty($currentEntry['title']) && !empty($currentEntry['company'])))) {
            if ($currentEntry && !empty($currentEntry['title']) && !empty($currentEntry['company'])) {
                addEntry($data, 'experience', $currentEntry);
            }
            $currentEntry = ['title' => $pendingTitle, 'company' => '', 'start_date' => '', 'end_date' => '', 'location' => '', 'description' => ''];
            $pendingTitle = null;
        }

        if (!$currentEntry) {
            $currentEntry = ['title' => $pendingTitle ?? '', 'company' => '', 'start_date' => $line, 'end_date' => '', 'location' => '', 'description' => ''];
            $pendingTitle = null;
        } elseif (empty($currentEntry['start_date'])) {
            $currentEntry['start_date'] = $line;
        } else {
            // New entry starting with date
            addEntry($data, 'experience', $currentEntry);
            $currentEntry = ['title' => '', 'company' => '', 'start_date' => $line, 'end_date' => '', 'location' => '', 'description' => ''];
        }
        // Extract end date from range (group 3 is the end date)
        if ($isDateRange && isset($dateMatch[3])) {
            $currentEntry['end_date'] = preg_replace('/present|current/i', 'Present', trim($dateMatch[3]));
        }
        return;
    }

    // No current entry - this must be a title or company
    if (!$currentEntry) {
        if (preg_match('/(inc|llc|corp|ltd|co\.|company|group|agency|university)/i', $line) || preg_match('/[,|]/', $line)) {
            $currentEntry = ['title' => $pendingTitle ?? '', 'company' => '', 'start_date' => '', 'end_date' => '', 'location' => '', 'description' => ''];
            $pendingTitle = null;
            parseCompanyLine($line, $currentEntry);
        } else {
            $pendingTitle = $line;
        }
        return;
    }

    // Have entry but no title yet
    if (empty($currentEntry['title'])) {
        $currentEntry['title'] = $line;
        return;
    }

    // Have title but no company
    if (empty($currentEntry['company'])) {
        parseCompanyLine($line, $currentEntry);
        return;
    }

    // Location or description
    if (empty($currentEntry['location']) && preg_match('/^(new york|san francisco|los angeles|chicago|houston|phoenix|philadelphia|san antonio|san diego|dallas|austin|seattle|denver|boston|atlanta|miami|remote)/i', $line)) {
        $currentEntry['location'] = $line;
    } else {
        $currentEntry['description'] .= ($currentEntry['description'] ? "\n" : '') . $cleanLine;
    }
}
        $pendingTitle = null;
    }

    // Date line - start new entry or update dates
    if ($isDate) {
        if (!$currentEntry) {
            $currentEntry = ['title' => $pendingTitle ?? '', 'company' => '', 'start_date' => $line, 'end_date' => '', 'location' => '', 'description' => ''];
            $pendingTitle = null;
        } elseif (empty($currentEntry['start_date'])) {
            $currentEntry['start_date'] = $line;
        } else {
            // New entry starting with date
            addEntry($data, 'experience', $currentEntry);
            $currentEntry = ['title' => '', 'company' => '', 'start_date' => $line, 'end_date' => '', 'location' => '', 'description' => ''];
        }
        // Extract end date from range
        if ($isDateRange) {
            $currentEntry['end_date'] = preg_replace('/present|current/i', 'Present', trim(end($dateMatch) ?? ''));
        }
        return;
    }

    // Bullet point - description
    if ($isBullet && $currentEntry) {
        $currentEntry['description'] .= ($currentEntry['description'] ? "\n" : '') . $cleanLine;
        return;
    }

    // No current entry - this must be a title
    if (!$currentEntry) {
        // Check if line contains company indicators
        if (preg_match('/(inc|llc|corp|ltd|co\.|company|group|agency|university)/i', $line) || preg_match('/[,|]/', $line)) {
            // This is a company line, create entry with empty title
            $currentEntry = ['title' => '', 'company' => '', 'start_date' => '', 'end_date' => '', 'location' => '', 'description' => ''];
            parseCompanyLine($line, $currentEntry);
        } else {
            // Store as pending title - next line will clarify
            $pendingTitle = $line;
        }
        return;
    }

    // Have entry but no title yet
    if (empty($currentEntry['title'])) {
        $currentEntry['title'] = $line;
        return;
    }

    // Have title but no company
    if (empty($currentEntry['company'])) {
        parseCompanyLine($line, $currentEntry);
        return;
    }

    // Have title and company - must be description or location
    if (empty($currentEntry['location']) && preg_match('/^(new york|san francisco|los angeles|chicago|houston|phoenix|philadelphia|san antonio|san diego|dallas|austin|seattle|denver|boston|atlanta|miami|remote)/i', $line)) {
        $currentEntry['location'] = $line;
    } else {
        $currentEntry['description'] .= ($currentEntry['description'] ? "\n" : '') . $cleanLine;
    }
}

function parseCompanyLine(string $line, array &$entry): void {
    if (preg_match('/[,|]/', $line)) {
        $parts = preg_split('/[,|]/', $line, 3);
        $entry['company'] = trim($parts[0]);
        if (isset($parts[1])) {
            $part1 = trim($parts[1]);
            // Check if second part looks like a location
            if (preg_match('/^(new york|san francisco|los angeles|chicago|houston|phoenix|philadelphia|san antonio|san diego|dallas|austin|seattle|denver|boston|atlanta|miami|remote|ca|ny|tx|fl|wa|il|ma|pa|ga)/i', $part1) || strlen($part1) < 30) {
                $entry['location'] = $part1;
            } else {
                $entry['company'] .= ', ' . $part1;
            }
        }
        if (isset($parts[2])) {
            $entry['location'] = trim($parts[2]);
        }
    } else {
        $entry['company'] = $line;
    }
}

function parseEducationLine(string $line, array &$data, ?array &$currentEntry, string $datePattern, string $dateRangePattern): void {
    $isDate = preg_match($datePattern, $line);
    $isDateRange = preg_match($dateRangePattern, $line, $dateMatch);
    $isDegree = preg_match('/(bachelor|master|phd|b\.?s\.?|m\.?s\.?|b\.?a\.?|m\.?a\.?|b\.?eng|m\.?eng|mba|doctorate|associate|diploma|certificate|high school)/i', $line);

    // If we have a current entry and see a date, add it as year
    if ($isDate && $currentEntry && !empty($currentEntry['degree']) && empty($currentEntry['year'])) {
        $currentEntry['year'] = $line;
        return;
    }

    if ($isDate || $isDegree) {
        if ($currentEntry) {
            addEntry($data, 'education', $currentEntry);
        }
        $currentEntry = ['degree' => '', 'school' => '', 'year' => '', 'description' => ''];
        if ($isDate) {
            $currentEntry['year'] = $line;
            return;
        }
        $currentEntry['degree'] = $line;
        return;
    }

    if (!$currentEntry) {
        $currentEntry = ['degree' => $line, 'school' => '', 'year' => '', 'description' => ''];
        return;
    }

    if (empty($currentEntry['degree'])) {
        $currentEntry['degree'] = $line;
    } elseif (empty($currentEntry['school'])) {
        $currentEntry['school'] = $line;
    } elseif (empty($currentEntry['year'])) {
        $currentEntry['year'] = $line;
    } else {
        $currentEntry['description'] .= ($currentEntry['description'] ? ' ' : '') . $line;
    }
}
        $currentEntry = ['degree' => '', 'school' => '', 'year' => '', 'description' => ''];

        if ($isDate) {
            $currentEntry['year'] = $line;
            if ($isDateRange && isset($dateMatch[0])) {
                $currentEntry['year'] = $dateMatch[0];
            }
            return;
        }

        // Degree line
        $currentEntry['degree'] = $line;
        return;
    }

    if (!$currentEntry) {
        // Start new entry with this line as degree
        $currentEntry = ['degree' => $line, 'school' => '', 'year' => '', 'description' => ''];
        return;
    }

    if (empty($currentEntry['degree'])) {
        $currentEntry['degree'] = $line;
    } elseif (empty($currentEntry['school'])) {
        $currentEntry['school'] = $line;
    } elseif (empty($currentEntry['year'])) {
        $currentEntry['year'] = $line;
    } else {
        $currentEntry['description'] .= ($currentEntry['description'] ? ' ' : '') . $line;
    }
}

function parseSkillsLine(string $line, array &$data): void {
    $line = preg_replace('/^[•\-\*\s]+/', '', $line);
    $items = preg_split('/[,;|]/', $line);
    foreach ($items as $item) {
        $item = trim($item);
        if ($item && strlen($item) > 1 && strlen($item) < 100) {
            if (preg_match('/^(advanced|intermediate|beginner|expert|proficient|familiar)/i', $item) && strlen($item) < 20) {
                continue;
            }
            $data['skills']['entries'][] = ['name' => $item];
        }
    }
}

function parseLanguageLine(string $line, array &$data): void {
    $line = preg_replace('/^[•\-\*\s]+/', '', $line);

    if (preg_match('/([A-Za-z]+(?:\s*\([A-Za-z]+\))?)\s*[-:]\s*(native|fluent|advanced|intermediate|basic|proficient|beginner|elementary|conversational)/i', $line, $m)) {
        $data['languages']['entries'][] = ['language' => trim($m[1]), 'level' => ucfirst($m[2])];
    } elseif ($line && strlen($line) < 50 && !preg_match('/^\d{4}/', $line)) {
        $data['languages']['entries'][] = ['language' => $line, 'level' => ''];
    }
}

function parseCertificationLine(string $line, array &$data, ?array &$currentEntry, string $datePattern): void {
    $line = preg_replace('/^[•\-\*\s]+/', '', $line);
    $isDate = preg_match($datePattern, $line);

    if ($isDate && $currentEntry) {
        $currentEntry['date'] = $line;
        addEntry($data, 'certifications', $currentEntry);
        $currentEntry = null;
        return;
    }

    if (!$currentEntry) {
        $currentEntry = ['name' => $line, 'issuer' => '', 'date' => ''];
        return;
    }

    if (preg_match('/(microsoft|amazon|aws|google|oracle|cisco|comptia|linux foundation|isc2|isaca)/i', $line)) {
        $currentEntry['issuer'] = $line;
    } else {
        $currentEntry['name'] .= ' ' . $line;
    }
}

function parseReferenceLine(string $line, array &$data, ?array &$currentEntry): void {
    $line = preg_replace('/^[•\-\*\s]+/', '', $line);

    if (!$currentEntry || (preg_match('/^[A-Z][a-zA-Z\s]+$/', $line) && strlen($line) < 40 && !preg_match('/[@]/', $line))) {
        if ($currentEntry) {
            addEntry($data, 'references', $currentEntry);
        }
        $currentEntry = ['name' => $line, 'title' => '', 'contact' => ''];
        return;
    }

    if (empty($currentEntry['title']) && !preg_match('/[@]/', $line) && !preg_match('/\d{3}/', $line)) {
        $currentEntry['title'] = $line;
    } else {
        $currentEntry['contact'] .= ($currentEntry['contact'] ? ' ' : '') . $line;
    }
}

function detectSection(string $line): ?string {
    $line = trim($line);
    $lower = strtolower($line);

    $patterns = [
        'experience' => '/^(experience|work experience|work history|employment|professional experience|career|jobs?|positions?|employment history)/i',
        'education' => '/^(education|academic|qualifications?|academic background|education history)/i',
        'skills' => '/^(skills|technical skills|core competencies|competencies?|expertise|proficiencies?|tools?|technologies?|technical proficiencies)/i',
        'summary' => '/^(summary|professional summary|profile|objective|career objective|about me|about|personal statement|career summary)/i',
        'languages' => '/^(languages?|language proficiency|language skills)/i',
        'certifications' => '/^(certifications?|licenses?|certificates?|professional certifications?|credentials?)/i',
        'references' => '/^(references?|professional references?|referees?)/i',
        'projects' => '/^(projects?|key projects?|personal projects?|portfolio)/i',
        'volunteer' => '/^(volunteer|volunteer experience|community service|volunteering)/i',
        'awards' => '/^(awards?|honors?|achievements?|recognitions?)/i',
    ];

    foreach ($patterns as $section => $pattern) {
        if (preg_match($pattern, $line)) {
            return $section;
        }
    }
    return null;
}

function addEntry(array &$data, string $section, array $entry): void {
    $sectionMap = [
        'experience' => 'experience',
        'education' => 'education',
        'certifications' => 'certifications',
        'references' => 'references',
        'projects' => 'projects',
        'volunteer' => 'volunteer',
        'awards' => 'awards',
    ];
    if (isset($sectionMap[$section]) && !empty($entry)) {
        $data[$sectionMap[$section]]['entries'][] = $entry;
    }
}

$pageTitle = 'Import Existing CV';
require __DIR__ . '/../views/layouts/header.php';
require __DIR__ . '/../views/import/index.php';
require __DIR__ . '/../views/layouts/footer.php';
