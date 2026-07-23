    </main>

    <?php /* Pied masqué sur tout l'univers Lofts (liste + fiches détail) :
             la bande sarcelle en bas de page joue déjà ce rôle, un pied en
             sauge tout de suite après casserait l'identité de l'univers. */ ?>
    <?php if (!is_page_template('templates/template-lofts.php') && !is_singular('loft')): ?>
    <?php
    /* Textes du pied éditables dans Réglages du site (ACF options), avec
       filet function_exists + replis = valeurs actuelles (déploiement sûr). */
    $pied_opt = function ($cle, $defaut) {
        return function_exists('lv_opt') ? lv_opt($cle, $defaut) : $defaut;
    };
    $pied_slogan   = $pied_opt('opt_pied_slogan', 'Produits locaux, frais et durables, à Matane.');
    $pied_adresse  = $pied_opt('opt_adresse', "14 Avenue D'Amours\nMatane, QC G4W 2X4");
    $pied_tel      = $pied_opt('opt_telephone', '(418) 562-5230');
    $pied_tel_lien = function_exists('lv_opt_tel_lien') ? lv_opt_tel_lien() : 'tel:+14185625230';
    $pied_courriel = $pied_opt('opt_courriel', 'epicerie@levivier.net');
    $pied_facebook = $pied_opt('opt_facebook', 'https://facebook.com/epicerielevivier/');
    $pied_h_sem    = $pied_opt('opt_horaire_semaine', '8 h 30 - 18 h');
    $pied_h_sam    = $pied_opt('opt_horaire_samedi', '9 h - 17 h');
    $pied_h_dim    = $pied_opt('opt_horaire_dimanche', '10 h - 17 h');
    ?>
    <footer class="pied">
        <div class="conteneur pied-grille">
            <div class="pied-marque">
                <div class="logo"><?php bloginfo('name'); ?></div>
                <p><?php echo esc_html($pied_slogan); ?></p>
                <p><?php echo nl2br(esc_html($pied_adresse)); ?></p>
                <p>
                    <a href="<?php echo esc_attr($pied_tel_lien); ?>"><?php echo esc_html($pied_tel); ?></a><br>
                    <a href="mailto:<?php echo esc_attr($pied_courriel); ?>"><?php echo esc_html($pied_courriel); ?></a>
                </p>
            </div>
            <div class="pied-nav-wrap">
                <h4>Navigation</h4>
                <?php
                /* Ordre logique (celui du menu header) plutôt qu'alphabétique,
                   réparti sur 2 colonnes (demande de Philippe, 22 juillet) :
                   colonne 1 = univers boutique/épicerie, colonne 2 = pages
                   utilitaires. Politique de confidentialité reste dans la
                   barre du bas à côté du copyright, pas mélangée ici. */
                $pied_lien = function ($slug) {
                    $page = get_page_by_path($slug);
                    if (!$page || $page->post_status !== 'publish') return null;
                    return ['url' => get_permalink($page), 'titre' => get_the_title($page)];
                };
                $pied_nav_col1 = ['epicerie', 'boutique', 'produits-maison', 'epicerie-africaine', 'pret-a-manger'];
                $pied_nav_col2 = ['commandez', 'lofts', 'contactez-nous', 'promotions'];
                ?>
                <div class="pied-nav-cols">
                    <ul class="pied-nav-col">
                        <?php foreach ($pied_nav_col1 as $slug):
                            $lien = $pied_lien($slug);
                            if (!$lien) continue; ?>
                            <li class="page_item"><a href="<?php echo esc_url($lien['url']); ?>"><?php echo esc_html($lien['titre']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <ul class="pied-nav-col">
                        <?php foreach ($pied_nav_col2 as $slug):
                            $lien = $pied_lien($slug);
                            if (!$lien) continue; ?>
                            <li class="page_item"><a href="<?php echo esc_url($lien['url']); ?>"><?php echo esc_html($lien['titre']); ?></a></li>
                        <?php endforeach;
                        // Archives qui ne sont pas des Pages.
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
            </div>
            <div>
                <h4>Horaires</h4>
                <ul>
                    <li>Lun - Ven : <?php echo esc_html($pied_h_sem); ?></li>
                    <li>Samedi : <?php echo esc_html($pied_h_sam); ?></li>
                    <li>Dimanche : <?php echo esc_html($pied_h_dim); ?></li>
                </ul>
                <h4 style="margin-top:1.4rem">Suivez-nous</h4>
                <ul>
                    <li><a href="<?php echo esc_url($pied_facebook); ?>" target="_blank" rel="noopener">Facebook</a></li>
                </ul>
            </div>
        </div>
        <?php $lien_politiques = $pied_lien('politiques'); ?>
        <div class="pied-bas">
            <span>&copy; <?php echo wp_date('Y'); ?> Le Vivier · Matane, Québec</span>
            <?php if ($lien_politiques): ?>
                <span aria-hidden="true">·</span>
                <a href="<?php echo esc_url($lien_politiques['url']); ?>"><?php echo esc_html($lien_politiques['titre']); ?></a>
            <?php endif; ?>
        </div>
    </footer>
    <?php endif; ?>

    <?php wp_footer(); ?>
</body>

</html>
