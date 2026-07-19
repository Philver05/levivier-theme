<?php
/*
Template Name: Contact
*/
get_header();
if (have_posts()) the_post();

/* Filets : lv_opt vit dans functions.php (déploiement fichier par fichier possible) */
$ct_opt = function ($cle, $defaut) {
    return function_exists('lv_opt') ? lv_opt($cle, $defaut) : $defaut;
};
$ct_champ = function ($cle, $defaut) {
    $valeur = function_exists('get_field') ? get_field($cle) : '';
    return $valeur ?: $defaut;
};

$surtitre  = $ct_champ('ct_surtitre', 'On vous écoute · Le Vivier');
$adresse   = $ct_opt('opt_adresse', "14 Avenue D'Amours\nMatane, QC G4W 2X4");
$telephone = $ct_opt('opt_telephone', '(418) 562-5230');
$tel_lien  = function_exists('lv_opt_tel_lien') ? lv_opt_tel_lien() : 'tel:+14185625230';
$courriel  = $ct_opt('opt_courriel', 'epicerie@levivier.net');
$facebook  = $ct_opt('opt_facebook', 'https://facebook.com/epicerielevivier/');
$h_sem     = $ct_opt('opt_horaire_semaine', '8 h 30 - 18 h');
$h_sam     = $ct_opt('opt_horaire_samedi', '9 h - 17 h');
$h_dim     = $ct_opt('opt_horaire_dimanche', '10 h - 17 h');
?>

<!-- ======================================================
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow"><?php echo esc_html($surtitre); ?></p>
        <h1><?php the_title(); ?></h1>
        <?php if (trim(wp_strip_all_tags(get_the_content())) !== ''): the_content(); else: ?>
            <p><?php echo esc_html($ct_champ('ct_intro', 'Une question, une commande spéciale ou simplement l\'envie de nous dire bonjour ? Écrivez-nous ou passez nous voir, on vous répond rapidement.')); ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================
     COORDONNÉES + FORMULAIRE
====================================================== -->
<section class="section" style="padding-top:clamp(1rem,.75rem+1vw,1.5rem)">
    <div class="conteneur">
        <div class="ct-grille">

            <!-- Colonne coordonnées (carte sauge) + carte Google -->
            <div class="reveal">
                <div class="ct-coordonnees">
                    <h2><?php echo esc_html($ct_champ('ct_coord_titre', 'Venez nous voir')); ?></h2>
                    <ul class="ct-liste">
                        <li>
                            <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
                            <div><h4>Adresse</h4><p><?php echo nl2br(esc_html($adresse)); ?></p></div>
                        </li>
                        <li>
                            <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h3l2 5-2.5 1.5a12 12 0 0 0 5 5L17 14l5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2Z"/></svg></span>
                            <div><h4>Téléphone</h4><p><a href="<?php echo esc_attr($tel_lien); ?>"><?php echo esc_html($telephone); ?></a></p></div>
                        </li>
                        <li>
                            <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
                            <div><h4>Courriel</h4><p><a href="mailto:<?php echo esc_attr($courriel); ?>"><?php echo esc_html($courriel); ?></a></p></div>
                        </li>
                        <li>
                            <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                            <div>
                                <h4>Horaires</h4>
                                <p>Lun - Ven : <?php echo esc_html($h_sem); ?><br>
                                   Samedi : <?php echo esc_html($h_sam); ?><br>
                                   Dimanche : <?php echo esc_html($h_dim); ?></p>
                            </div>
                        </li>
                        <li>
                            <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M15 8h-2a2 2 0 0 0-2 2v10M8 12h6"/></svg></span>
                            <div><h4>Suivez-nous</h4><p><a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener">Facebook</a></p></div>
                        </li>
                    </ul>
                </div>

                <div class="ct-map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2776.3!2d-67.5282195!3d48.8447292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4c96c1fddfc0165f%3A0x6e352ffd0909e5d8!2sLe%20Vivier%2C%20%C3%A9picerie%20boutique!5e0!3m2!1sfr!2sca!4v1"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Le Vivier — 14 Avenue D'Amours, Matane, QC">
                    </iframe>
                </div>
            </div>

            <!-- Colonne formulaire -->
            <div class="ct-form-carte reveal reveal-delai-1">
                <h2><?php echo esc_html($ct_champ('ct_form_titre', 'Écrivez-nous')); ?></h2>
                <p><?php echo esc_html($ct_champ('ct_form_texte', 'Remplissez le formulaire et nous vous répondrons dans les plus brefs délais.')); ?></p>

                <form id="ct-formulaire" novalidate>
                    <div class="champ-duo">
                        <div class="champ">
                            <label for="ct-prenom">Prénom <abbr title="requis" style="color:var(--terra);text-decoration:none">*</abbr></label>
                            <input type="text" id="ct-prenom" name="prenom" autocomplete="given-name" required>
                        </div>
                        <div class="champ">
                            <label for="ct-nom">Nom <abbr title="requis" style="color:var(--terra);text-decoration:none">*</abbr></label>
                            <input type="text" id="ct-nom" name="nom" autocomplete="family-name" required>
                        </div>
                    </div>

                    <div class="champ-duo">
                        <div class="champ">
                            <label for="ct-courriel">Courriel <abbr title="requis" style="color:var(--terra);text-decoration:none">*</abbr></label>
                            <input type="email" id="ct-courriel" name="courriel" autocomplete="email" required>
                        </div>
                        <div class="champ">
                            <label for="ct-telephone">Téléphone</label>
                            <input type="tel" id="ct-telephone" name="telephone" autocomplete="tel">
                        </div>
                    </div>

                    <div class="champ">
                        <label for="ct-sujet">Sujet</label>
                        <input type="text" id="ct-sujet" name="sujet" placeholder="Ex : Question sur un produit">
                    </div>

                    <div class="champ">
                        <label for="ct-message">Message <abbr title="requis" style="color:var(--terra);text-decoration:none">*</abbr></label>
                        <textarea id="ct-message" name="message" rows="6" required></textarea>
                    </div>

                    <!-- Leurre anti-pourriel : laissé vide par les humains -->
                    <div class="ct-champ-leurre" aria-hidden="true">
                        <label for="ct-site">Site web</label>
                        <input type="text" id="ct-site" name="site_web" tabindex="-1" autocomplete="off">
                    </div>

                    <button type="submit" class="btn btn-primaire">Envoyer le message</button>

                    <div class="ct-retour" id="ct-retour" role="status" aria-live="polite" hidden></div>
                </form>
            </div>

        </div>
    </div>
</section>

<?php get_footer();
