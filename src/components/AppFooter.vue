<template>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-brand">
        <h3 class="footer-title">MyWay</h3>
        <p class="footer-text">
          A student community platform for discovering, sharing, and reviewing
          useful places and experiences.
        </p>
      </div>

      <div class="footer-section">
        <h4 class="footer-heading">Navigation</h4>
        <ul class="footer-links">
          <li><RouterLink to="/">Home</RouterLink></li>
          <li><RouterLink to="/browse">Browse</RouterLink></li>
          <li><RouterLink to="/about">About</RouterLink></li>
          <li><RouterLink to="/support">Support</RouterLink></li>
        </ul>
      </div>

      <div class="footer-section">
        <h4 class="footer-heading">Features</h4>
        <ul class="footer-links">
          <li>Search places</li>
          <li>Save favourites</li>
          <li>Like and comment</li>
          <li>Manage content</li>
        </ul>
      </div>

      <div class="footer-section">
        <h4 class="footer-heading">Project</h4>
        <p class="footer-text">
          Built with Vue, Vue Router, Bootstrap, PHP, and MariaDB for a modern
          full-stack web application.
        </p>
      </div>
    </div>

    <div class="container footer-bottom">
      <p class="mb-0">
        © 2026 MyWay. Built for COS30043 Interface Design and Development.
      </p>
    </div>
  </footer>
</template>