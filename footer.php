    </main>

    <?php /* Pied masqué sur tout l'univers Lofts (liste + fiches détail) :
             la bande sarcelle en bas de page joue déjà ce rôle, un pied en
             sauge tout de suite après casserait l'identité de l'univers. */ ?>
    <?php if (!is_page_template('templates/template-lofts.php') && !is_singular('loft')): ?>
    <footer class="pied">
        <div class="conteneur pied-grille">
            <div class="pied-marque">
                <div class="logo"><?php bloginfo('name'); ?></div>
                <p>Produits locaux, frais et durables, à Matane.</p>
                <p>14 Avenue D'Amours<br>Matane, QC G4W 2X4</p>
                <p>
                    <a href="tel:+14185625230">(418) 562-5230</a><br>
                    <a href="mailto:epicerie@levivier.net">epicerie@levivier.net</a>
                </p>
            </div>
            <div>
                <h4>Navigation</h4>
                <ul class="pied-nav">
                    <?php
                    // Navigation complete auto : toutes les pages publiees...
                    wp_list_pages([
                        'title_li'    => '',
                        'sort_column' => 'menu_order, post_title',
                    ]);
                    // ...plus les archives qui ne sont pas des Pages.
                    $arch_producteur = get_post_type_archive_link('producteur');
                    if ($arch_producteur) {
                        echo '<li class="page_item"><a href="' . esc_url($arch_producteur) . '">Producteurs</a></li>';
                    }
                    $arch_produit = get_post_type_archive_link('produit');
                    if ($arch_produit) {
                        echo '<li class="page_item"><a href="' . esc_url($arch_produit) . '">Produits</a></li>';
                    }
                    ?>
                </ul>
            </div>
            <div>
                <h4>Horaires</h4>
                <ul>
                    <li>Lun - Ven : 8 h 30 - 18 h</li>
                    <li>Samedi : 9 h - 17 h</li>
                    <li>Dimanche : 10 h - 17 h</li>
                </ul>
                <h4 style="margin-top:1.4rem">Suivez-nous</h4>
                <ul>
                    <li><a href="https://facebook.com/epicerielevivier/" target="_blank" rel="noopener">Facebook</a></li>
                </ul>
            </div>
        </div>
        <div class="pied-bas">
            &copy; <?php echo wp_date('Y'); ?> Le Vivier · Matane, Québec
        </div>
    </footer>
    <?php endif; ?>

    <?php wp_footer(); ?>
</body>

</html>
