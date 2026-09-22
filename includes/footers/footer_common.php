<!-- Universal Modular Footer -->
    <footer class="global-footer">
        <div class="footer-nav">
            <a href="#">Privacy Policy</a>
            <a href="#">Support</a>
            <a href="#">Facility Rules</a>
        </div>
        <p class="copyright">FITCAMPUS &copy; UNIVERSITY OF COLOMBO</p>
    </footer>

    <!-- Universal Driver -->
    <script src="../../assets/js/main.js"></script>

    <!-- Page Specific Script Driver (Supports String or Array) -->
    <?php if (!empty($extra_js)): ?>
        <?php 
        $scripts = is_array($extra_js) ? $extra_js : [$extra_js]; 
        foreach ($scripts as $script_file): 
        ?>
            <script src="../../assets/js/<?php echo htmlspecialchars($script_file, ENT_QUOTES, 'UTF-8'); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>