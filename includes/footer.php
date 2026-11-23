  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <p>© <?= date('Y') ?> JumysHub. Барлық құқықтар қорғалған.</p>
      <div>
        <a href="index.php">Басты бет</a>
        <a href="catalog.php">Вакансиялар</a>
        <?php if (is_logged_in()): ?>
          <a href="profile.php">Жеке кабинет</a>
        <?php else: ?>
          <a href="register.php">Тіркелу</a>
        <?php endif; ?>
      </div>
    </div>
  </footer>

  <script src="assets/js/main.js"></script>
</body>
</html>
