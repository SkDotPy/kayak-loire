</main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">

                <div class="footer-column">
                    <h3>Navigation</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/index.php">Accueil</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/etapes.php">Nos étapes</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/packs.php">Nos packs</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/composer-parcours.php">Composer mon parcours</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li>Email : kayak@loire.fr</li>
                        <li>Téléphone : 01 23 45 67 89</li>
                        <li>Adresse : Paris, France</li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Newsletter</h3>
                    <p>Restez informé de nos offres</p>
                    <form action="<?php echo SITE_URL; ?>/api/newsletter-subscribe.php" method="POST" class="newsletter-form">
                        <input type="email" name="email" placeholder="Votre email" required>
                        <button type="submit">S'inscrire</button>
                    </form>
                </div>
            </div>

            <div class="footer-bottom">
                <p>2025 Kayak Loire - Tous droits réservés | <a href="#">Mentions légales</a> | <a href="#">CGV</a></p>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>