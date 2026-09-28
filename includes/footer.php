    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="footer-text mb-0">
                        &copy; <?= date('Y') ?> <?= APP_NAME ?>. Crafted with <i class="fas fa-heart text-danger"></i>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-links">
                        <a href="#" class="footer-link">Privacy</a>
                        <a href="#" class="footer-link">Terms</a>
                        <a href="#" class="footer-link">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- App JS -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>

    <?php if (isset($extraJs)): ?>
        <?php foreach ((array)$extraJs as $js): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= e($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
