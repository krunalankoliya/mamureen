<footer id="footer" class="footer border-top bg-white py-4 mt-auto">
    <div class="container-fluid px-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="copyright text-muted small">
                &copy; <?= date('Y') ?> <strong>Majma Markazi</strong>. All Rights Reserved.
            </div>
            <div class="footer-links d-flex gap-3">
                <a href="https://www.talabulilm.com" class="text-muted text-decoration-none small hover-primary">Talabulilm Portal</a>
                <span class="text-muted opacity-25">|</span>
                <a href="<?= MODULE_PATH ?>support_tickets.php" class="text-muted text-decoration-none small hover-primary">Help Desk</a>
            </div>
        </div>
    </div>
</footer>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 40px; height: 40px; background: var(--primary); color: white; transition: all 0.3s;">
    <i class="bi bi-arrow-up-short h4 mb-0"></i>
</a>

<?php require_once __DIR__ . '/js-block.php'; ?>

</body>
</html>
