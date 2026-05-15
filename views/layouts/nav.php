<nav class="app-nav">
    <div class="nav-brand">
        <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-file-lines"></i> <?= APP_NAME ?></a>
    </div>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>/dashboard" class="<?= ($controller ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/cv/create" class="<?= ($controller ?? '') === 'cv' ? 'active' : '' ?>"><i class="fas fa-plus"></i> New CV</a>
        <a href="<?= BASE_URL ?>/templates"><i class="fas fa-palette"></i> Templates</a>
        <a href="<?= BASE_URL ?>/creator/new"><i class="fas fa-wand-magic-sparkles"></i> Template Creator</a>
        <a href="<?= BASE_URL ?>/phrases"><i class="fas fa-lightbulb"></i> Phrase Library</a>
    </div>
    <div class="nav-user">
        <div class="user-dropdown">
            <button class="user-btn">
                <i class="fas fa-user-circle"></i>
                <span><?= e(explode(' ', $_SESSION['user_name'] ?? 'User')[0]) ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>
