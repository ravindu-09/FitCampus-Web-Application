<?php
?>
    <!-- Universal Modular Footer -->
    <footer class="global-footer">
        <div class="footer-nav">
            <a href="#">Privacy Policy</a>
            <a href="#">Support</a>
            <a href="#">Facility Rules</a>
        </div>
        <p class="copyright">FITCAMPUS &copy; UNIVERSITY OF COLOMBO</p>
    </footer>

    <!-- Universal & Page Specific Script Drivers -->
    <script src="../../assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <script src="../../assets/js/<?php echo htmlspecialchars($extra_js, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endif; ?>

</body>
</html>