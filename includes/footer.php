    <!-- partial:partials/_footer.html -->
    <footer class="footer">
        <div class="container-fluid d-flex justify-content-between">
            <span class="text-muted d-block text-center text-sm-start d-sm-inline-block">Copyright © stmorg.in 2024</span>
            <span class="text-muted d-block text-center text-sm-start d-sm-inline-block">
                Developed with ❤️ by <a href="https://weberq.in" target="_blank" class="text-primary fw-bold">WeberQ Global Pvt. Ltd.</a>
            </span>
        </div>
    </footer>
    <!-- partial -->
    </div>
    <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="assets/vendors/chart.js/Chart.min.js"></script>
    <script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- End custom js for this page -->
<?php $flash = function_exists('take_flash') ? take_flash() : null; ?>
<?php if ($flash): ?>
    <!-- Carried over from the page that redirected here. -->
    <div class="stm-toast stm-toast-<?= h($flash['type']) ?>" id="stmFlash">
        <i class="mdi <?= $flash['type'] === 'success' ? 'mdi-check-circle-outline' : ($flash['type'] === 'danger' ? 'mdi-alert-circle-outline' : 'mdi-information-outline') ?> me-2"></i>
        <span><?= h($flash['msg']) ?></span>
        <button type="button" class="stm-toast-close" aria-label="Dismiss">&times;</button>
    </div>
<?php endif; ?>

    <script>
      (function () {
        var flash = document.getElementById('stmFlash');
        if (flash) {
          var hide = function () {
            flash.style.transition = 'opacity .35s, transform .35s';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(10px)';
            setTimeout(function () { flash.remove(); }, 400);
          };
          flash.querySelector('.stm-toast-close').addEventListener('click', hide);
          setTimeout(hide, 6000);
        }
      })();

      (function () {
        var sidebar = document.querySelector('.sidebar-offcanvas');
        if (!sidebar) return;
        document.addEventListener('click', function (e) {
          if (window.innerWidth > 991) return;
          if (!sidebar.classList.contains('active')) return;
          if (sidebar.contains(e.target)) return;
          if (e.target.closest('[data-toggle="offcanvas"]')) return;
          sidebar.classList.remove('active');
        });
      })();
    </script>
    </body>

    </html>