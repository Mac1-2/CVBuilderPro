<?php
$phraseModel = new Phrase();
$action = $route[1] ?? 'index';

$industries = $phraseModel->getAllIndustries();
$search = $_GET['q'] ?? '';
$industryId = $_GET['industry'] ?? null;
$category = $_GET['category'] ?? null;
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 24;
$offset = ($page - 1) * $perPage;

$phrases = $phraseModel->search($search, $industryId ? (int)$industryId : null, $category, $perPage, $offset);
$total = $phraseModel->count($search, $industryId ? (int)$industryId : null, $category);
$totalPages = ceil($total / $perPage);

$pageTitle = 'Phrase Library';
$extraCss = ['phrases.css'];
require __DIR__ . '/../views/layouts/header.php';
require __DIR__ . '/../views/phrases/library.php';
require __DIR__ . '/../views/layouts/footer.php';
