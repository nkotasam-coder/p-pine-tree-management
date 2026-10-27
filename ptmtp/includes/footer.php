<?php if (isset($_SESSION['user_id']) && empty($public_page)): ?>
    </main>
    <footer class="app-footer">Pine Tree Management & Payment Tracking System · NAEB &copy; <?= date('Y') ?></footer>
  </div>
</div>
<div class="sidebar-backdrop no-print" id="sidebarBackdrop"></div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sidebar = document.getElementById('appSidebar');
const backdrop = document.getElementById('sidebarBackdrop');
function toggleSidebar(open) {
  if (!sidebar) return;
  sidebar.classList.toggle('show', open);
  if (backdrop) backdrop.classList.toggle('show', open);
}
document.getElementById('sidebarOpen')?.addEventListener('click', () => toggleSidebar(true));
document.getElementById('sidebarClose')?.addEventListener('click', () => toggleSidebar(false));
backdrop?.addEventListener('click', () => toggleSidebar(false));
</script>
</body>
</html>
