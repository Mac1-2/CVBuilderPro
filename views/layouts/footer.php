    </main>
    <?php if (!empty($_SESSION['user_id'])): ?>
    <footer class="app-footer">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?></p>
    </footer>
    <?php endif; ?>
    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
    <?php if (isset($extraJs)): ?><?php foreach ($extraJs as $js): ?><script src="<?= ASSETS_URL ?>/js/<?= e($js) ?>"></script><?php endforeach; ?><?php endif; ?>
</body>
</html>
