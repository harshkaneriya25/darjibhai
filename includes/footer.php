<footer class="admin-footer">

    <div class="footer-left">

        <span>
            © <?= date('Y') ?> Darjibhai.
        </span>

        <span>
            All rights reserved.
        </span>

    </div>

    <div class="footer-right">

        <span>
            Sewing the Happiness
        </span>

        <i class="fa-solid fa-scissors"></i>

    </div>

</footer>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- Common JS -->

<script
    src="/darjibhai/assets/js/script.js"
></script>


<?php if (!empty($extra_js)): ?>

    <?= $extra_js ?>

<?php endif; ?>

</body>

</html>