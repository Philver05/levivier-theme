<?php
/**
 * Template Name: Boîtes à lunch
 */

get_header();

$actif    = get_field('bx_service_actif');
$note     = get_field('bx_note_service');
$delai    = (int) get_field('bx_delai_min') ?: 24;
$min_qte  = (int) get_field('bx_min_qte')  ?: 1;
$formules = get_field('bx_formules') ?: [];

$formules_visibles = array_filter($formules, function ($f) {
    return !empty($f['bx_f_visible']) && !empty($f['bx_f_nom']);
});
$formules_visibles = array_values($formules_visibles);

$date_min = date('Y-m-d', strtotime('+' . $delai . ' hours'));
?>

<div class="page-entete">
    <div class="conteneur page-entete-conteneur">
        <div class="page-entete-texte">
            <?php
            $eyebrow = get_field('bx_eyebrow') ?: 'Service aux entreprises';
            $titre   = get_the_title();
            $intro   = get_field('bx_intro');
            ?>
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <h1 class="page-entete-titre-script"><?php echo esc_html($titre ?: 'Boîtes à lunch'); ?></h1>
            <?php if ($intro) : ?>
                <p class="page-entete-desc"><?php echo esc_html($intro); ?></p>
            <?php else : ?>
                <p class="page-entete-desc">Commandez pour votre équipe en quelques clics. Nous préparons tout, vous n'avez qu'à venir chercher.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<main class="conteneur bx-main" id="bx-page">

<?php if (!$actif) : ?>

    <div class="bx-inactif">
        <p class="bx-inactif-titre">Ce service est temporairement suspendu</p>
        <?php if ($note) : ?>
            <p class="bx-inactif-note"><?php echo esc_html($note); ?></p>
        <?php else : ?>
            <p class="bx-inactif-note">Revenez bientôt ou contactez-nous directement pour toute demande de groupe.</p>
        <?php endif; ?>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="btn">Nous contacter</a>
    </div>

<?php elseif (empty($formules_visibles)) : ?>

    <div class="bx-inactif">
        <p class="bx-inactif-titre">Les formules arrivent bientôt</p>
        <p class="bx-inactif-note">Nos boîtes à lunch seront disponibles sous peu. En attendant, appelez-nous au <?php echo function_exists('lv_opt_tel_lien') ? lv_opt_tel_lien() : '<a href="tel:+14185625230">(418) 562-5230</a>'; ?>.</p>
    </div>

<?php else : ?>

    <?php if ($note) : ?>
        <div class="bx-note-service"><?php echo esc_html($note); ?></div>
    <?php endif; ?>

    <form id="bx-formulaire" class="bx-formulaire" novalidate>

        <!-- ① Formules -->
        <section class="bx-section">
            <h2 class="bx-section-titre">Choisissez vos formules</h2>
            <?php if ($min_qte > 1) : ?>
                <p class="bx-aide">Minimum <?php echo esc_html($min_qte); ?> boîtes par commande.</p>
            <?php endif; ?>
            <div class="bx-grille">
                <?php foreach ($formules_visibles as $i => $f) :
                    $nom    = esc_html($f['bx_f_nom']);
                    $prix   = (float) ($f['bx_f_prix'] ?? 0);
                    $desc   = $f['bx_f_description'] ?? '';
                    $inclus = $f['bx_f_inclus'] ?? '';
                    $image  = $f['bx_f_image'] ?? null;
                    $inclus_attr = esc_attr($inclus);
                    ?>
                    <div class="bx-card">
                        <?php if ($image && !empty($image['url'])) : ?>
                            <div class="bx-card-photo">
                                <img src="<?php echo esc_url($image['url']); ?>"
                                     alt="<?php echo esc_attr($image['alt'] ?: $f['bx_f_nom']); ?>"
                                     loading="lazy">
                            </div>
                        <?php endif; ?>
                        <div class="bx-card-corps">
                            <div class="bx-card-entete">
                                <span class="bx-card-nom"><?php echo $nom; ?></span>
                                <span class="bx-card-prix"><?php echo esc_html(number_format($prix, 2, ',', ' ')); ?>&nbsp;$</span>
                            </div>
                            <?php if ($desc) : ?>
                                <p class="bx-card-desc"><?php echo esc_html($desc); ?></p>
                            <?php endif; ?>
                            <?php if ($inclus) : ?>
                                <ul class="bx-card-inclus">
                                    <?php foreach (array_filter(array_map('trim', explode("\n", $inclus))) as $ligne) : ?>
                                        <li><?php echo esc_html($ligne); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <div class="bx-qty-wrap">
                                <button type="button" class="bx-qty-moins" data-index="<?php echo $i; ?>" aria-label="Retirer une boîte">–</button>
                                <input type="number"
                                       class="bx-qty-input"
                                       data-index="<?php echo $i; ?>"
                                       data-prix="<?php echo esc_attr($prix); ?>"
                                       data-nom="<?php echo esc_attr($f['bx_f_nom']); ?>"
                                       data-inclus="<?php echo $inclus_attr; ?>"
                                       value="0" min="0" readonly
                                       aria-label="Quantité pour <?php echo $nom; ?>">
                                <button type="button" class="bx-qty-plus" data-index="<?php echo $i; ?>" aria-label="Ajouter une boîte">+</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ② Coordonnées + récupération -->
        <section class="bx-section">
            <h2 class="bx-section-titre">Vos coordonnées</h2>
            <div class="bx-form-carte">

                <p id="bx-msg-succes" class="bx-msg-succes" hidden></p>
                <p id="bx-msg-erreur" class="bx-msg-erreur" hidden></p>

                <div class="bx-champs-grille">
                    <div class="bx-champ">
                        <label for="bx-prenom">Prénom <span aria-hidden="true">*</span></label>
                        <input type="text" id="bx-prenom" name="prenom" required autocomplete="given-name">
                    </div>
                    <div class="bx-champ">
                        <label for="bx-nom">Nom <span aria-hidden="true">*</span></label>
                        <input type="text" id="bx-nom" name="nom" required autocomplete="family-name">
                    </div>
                    <div class="bx-champ">
                        <label for="bx-entreprise">Entreprise ou organisation</label>
                        <input type="text" id="bx-entreprise" name="entreprise" autocomplete="organization">
                    </div>
                    <div class="bx-champ">
                        <label for="bx-telephone">Téléphone <span aria-hidden="true">*</span></label>
                        <input type="tel" id="bx-telephone" name="telephone" required autocomplete="tel">
                    </div>
                    <div class="bx-champ bx-champ--large">
                        <label for="bx-email">Courriel <span aria-hidden="true">*</span></label>
                        <input type="email" id="bx-email" name="email" required autocomplete="email">
                    </div>
                </div>

                <h3 class="bx-sous-titre">Récupération</h3>
                <div class="bx-champs-grille">
                    <div class="bx-champ">
                        <label for="bx-date">Date de récupération <span aria-hidden="true">*</span></label>
                        <input type="date" id="bx-date" name="date_recuperation" required min="<?php echo esc_attr($date_min); ?>">
                    </div>
                    <div class="bx-champ">
                        <label for="bx-heure">Heure de récupération <span aria-hidden="true">*</span></label>
                        <select id="bx-heure" name="heure_recuperation" required>
                            <option value="">Choisir une heure</option>
                            <option value="8:30">8 h 30</option>
                            <option value="9:00">9 h 00</option>
                            <option value="9:30">9 h 30</option>
                            <option value="10:00">10 h 00</option>
                            <option value="10:30">10 h 30</option>
                            <option value="11:00">11 h 00</option>
                            <option value="11:30">11 h 30</option>
                            <option value="12:00">12 h 00</option>
                            <option value="12:30">12 h 30</option>
                            <option value="13:00">13 h 00</option>
                            <option value="13:30">13 h 30</option>
                            <option value="14:00">14 h 00</option>
                            <option value="14:30">14 h 30</option>
                            <option value="15:00">15 h 00</option>
                            <option value="15:30">15 h 30</option>
                            <option value="16:00">16 h 00</option>
                            <option value="16:30">16 h 30</option>
                            <option value="17:00">17 h 00</option>
                        </select>
                    </div>
                </div>

                <div class="bx-champ bx-champ--plein">
                    <label for="bx-restrictions">Restrictions alimentaires ou allergies</label>
                    <textarea id="bx-restrictions" name="restrictions" rows="2" placeholder="Ex. : sans gluten, allergie aux noix..."></textarea>
                </div>
                <div class="bx-champ bx-champ--plein">
                    <label for="bx-commentaire">Commentaire ou demande spéciale</label>
                    <textarea id="bx-commentaire" name="commentaire" rows="3"></textarea>
                </div>

                <!-- Honeypot -->
                <div class="bx-champ-leurre" aria-hidden="true">
                    <label for="bx-site-web">Site web</label>
                    <input type="text" id="bx-site-web" name="site_web" tabindex="-1" autocomplete="off">
                </div>

            </div>
        </section>

    </form>

    <!-- Barre sticky de total -->
    <div class="bx-barre" id="bx-barre" hidden>
        <div class="bx-barre-conteneur conteneur">
            <span class="bx-barre-info">
                <span id="bx-total-boites">0 boîtes</span>
                &mdash;
                <strong id="bx-total-prix">0,00&nbsp;$</strong>
            </span>
            <button type="submit" form="bx-formulaire" class="btn bx-btn-soumettre" id="bx-btn-soumettre">
                Envoyer ma commande
            </button>
        </div>
    </div>

<?php endif; ?>

</main>

<?php get_footer(); ?>
