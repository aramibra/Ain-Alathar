<?php if (!is_logged_in()): ?>
</main>
<?php if (basename($_SERVER['SCRIPT_NAME']) === 'index.php'): ?>
<footer class="heritage-footer" aria-label="<?= h(t('footer.title')) ?>">
  <div class="heritage-footer-container footer-without-logo">
    <section class="heritage-footer-info">
      <h2><?= h(t('footer.title')) ?></h2>
      <div class="heritage-footer-block"><h3><?= h(t('footer.vision_title')) ?></h3><p><?= h(t('footer.vision')) ?></p></div>
      <div class="heritage-footer-block"><h3><?= h(t('footer.mission_title')) ?></h3><p><?= h(t('footer.mission')) ?></p></div>
    </section>
    <section class="heritage-footer-contact">
      <h2><?= h(t('footer.contact_title')) ?></h2>
      <p><i class="ti ti-map-pin"></i><?= h(t('footer.contact1')) ?></p>
      <p><i class="ti ti-building-monument"></i><?= h(t('footer.contact2')) ?></p>
      <div class="footer-links"><a href="index.php"><?= h(t('nav.home')) ?></a><a href="about.php"><?= h(t('nav.about')) ?></a></div>
    </section>
  </div>
  <hr>
  <div class="heritage-footer-bottom"><span><?= h(t('footer.rights')) ?></span></div>
</footer>
<?php else: ?>
<footer class="public-footer compact-footer"><span><?= h(t('app.tagline')) ?></span><span><?= h(t('footer.rights')) ?></span></footer>
<?php endif; ?>
<?php else: ?>
  </main>
  <footer class="app-footer"><span><?= h(t('app.tagline')) ?></span><span><?= h(t('footer.rights')) ?></span></footer>
</div>
<?php endif; ?>

<div class="modal" id="appModal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-card" role="document">
    <button class="modal-x" type="button" data-modal-close aria-label="<?= h(t('modal.close')) ?>">×</button>
    <div id="appModalContent"></div>
  </div>
</div>
<div class="confirm-dialog" id="confirmDialog" aria-hidden="true" role="alertdialog" aria-modal="true">
  <div class="confirm-backdrop"></div>
  <div class="confirm-card">
    <div class="confirm-icon"><i class="ti ti-trash"></i></div>
    <h3 data-confirm-title><?= h(t('dialog.confirm')) ?></h3>
    <p data-confirm-text><?= h(t('dialog.irreversible')) ?></p>
    <div class="confirm-actions"><button class="btn btn-light" type="button" data-confirm-cancel><?= h(t('common.cancel')) ?></button><button class="btn btn-danger" type="button" data-confirm-accept><?= h(t('common.delete')) ?></button></div>
  </div>
</div>
<div class="toast-stack" id="toastStack" aria-live="polite"></div>
<script>window.ATHAR_I18N={cancel:<?= json_encode(t('common.cancel')) ?>,delete:<?= json_encode(t('common.delete')) ?>};</script>
<script src="assets/js/app.js?v=17.0"></script>
</body>
</html>
