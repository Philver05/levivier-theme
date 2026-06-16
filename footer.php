        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-interieur">
            <div class="footer-marque">
                <a class="footer-nom" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php bloginfo('name'); ?>
                </a>
                <p>Épicerie fine locale à Matane.<br>Produits locaux, frais et durables.</p>
                <p>14 Avenue D'Amours<br>Matane, QC G4W 2X4</p>
                <p>
                    <a href="tel:+14185625230">(418) 562-5230</a><br>
                    <a href="mailto:epicerie@levivier.net">epicerie@levivier.net</a>
                </p>
            </div>
            <div class="footer-col">
                <h4>Navigation</h4>
                <?php
                wp_nav_menu([
                    'menu'            => 'principal',
                    'container'       => false,
                    'depth'           => 1,
                    'lv_footer'       => true,
                ]);
                ?>
            </div>
            <div class="footer-col">
                <h4>Horaires</h4>
                <ul>
                    <li>Lun&ndash;Ven : 8 h 30 &ndash; 18 h</li>
                    <li>Samedi : 9 h &ndash; 17 h</li>
                    <li>Dimanche : 10 h &ndash; 17 h</li>
                </ul>
                <br>
                <h4>Suivez-nous</h4>
                <ul>
                    <li><a href="https://facebook.com/epicerielevivier/" target="_blank" rel="noopener">Facebook</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bas">
            <p class="footer-copyright">
                &copy; <?php echo wp_date('Y'); ?> Le Vivier &mdash; Épicerie fine locale, Matane, Québec
            </p>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>

</html>
