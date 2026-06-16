<?php
/*
Template Name: À propos
*/
get_header();

/* Champs ACF */
$telephone        = get_field('telephone');
$courriel         = get_field('courriel');
$adresse          = get_field('adresse');
$facebook_url     = get_field('facebook');
$horaire_semaine  = get_field('horaire_semaine');
$horaire_samedi   = get_field('horaire_samedi');
$horaire_dimanche = get_field('horaire_dimanche');
?>

<!-- ======================================================
     À PROPOS — texte + photo
====================================================== -->
<section class="section-hero-direct section-apropos-direct section-hero-direct--rose">
    <div class="conteneur">
        <div class="apropos-texte">
            <p class="banniere-surtitre">Notre histoire · Le Vivier</p>
            <?php if (have_posts()): while (have_posts()): the_post(); ?>
                <div class="corps-article"><?php the_content(); ?></div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</section>

<!-- ======================================================
     COORDONNÉES + CARTE GOOGLE
====================================================== -->
<section class="section-contact-bloc">
    <div class="conteneur">

        <h2 class="titre-section">Venez nous voir</h2>

        <div class="contact-interieur">

            <div class="contact-infos">

                <?php if ($adresse): ?>
                    <div class="contact-info-item">
                        <span class="info-label">Adresse</span>
                        <span class="info-valeur"><?php echo nl2br(esc_html($adresse)); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($telephone): ?>
                    <div class="contact-info-item">
                        <span class="info-label">Téléphone</span>
                        <span class="info-valeur">
                            <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telephone)); ?>">
                                <?php echo esc_html($telephone); ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($courriel): ?>
                    <div class="contact-info-item">
                        <span class="info-label">Courriel</span>
                        <span class="info-valeur">
                            <a href="mailto:<?php echo esc_attr($courriel); ?>">
                                <?php echo esc_html($courriel); ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($horaire_semaine || $horaire_samedi || $horaire_dimanche): ?>
                    <div class="contact-info-item">
                        <span class="info-label">Horaires</span>
                        <span class="info-valeur">
                            <?php if ($horaire_semaine): ?>
                                Lundi &ndash; Vendredi : <?php echo esc_html($horaire_semaine); ?><br>
                            <?php endif; ?>
                            <?php if ($horaire_samedi): ?>
                                Samedi : <?php echo esc_html($horaire_samedi); ?><br>
                            <?php endif; ?>
                            <?php if ($horaire_dimanche): ?>
                                Dimanche : <?php echo esc_html($horaire_dimanche); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($facebook_url): ?>
                    <div class="contact-info-item">
                        <span class="info-label">Suivez-nous</span>
                        <span class="info-valeur">
                            <a href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener">Facebook</a>
                        </span>
                    </div>
                <?php endif; ?>

            </div>

            <div class="carte-google">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2776.3!2d-67.5282195!3d48.8447292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4c96c1fddfc0165f%3A0x6e352ffd0909e5d8!2sLe%20Vivier%2C%20%C3%A9picerie%20boutique!5e0!3m2!1sfr!2sca!4v1"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Le Vivier — 14 Avenue D'Amours, Matane, QC">
                </iframe>
            </div>

        </div>
    </div>
</section>

<!-- ======================================================
     FORMULAIRE DE CONTACT
====================================================== -->
<section class="section-formulaire-contact">
    <div class="conteneur">

        <div class="formulaire-contact-interieur">
            <h2 class="titre-section">Écrivez-nous</h2>
            <p class="sous-titre-formulaire">Une question, une commande spéciale ou l'envie de nous dire bonjour&nbsp;? On vous répond rapidement.</p>

            <form class="formulaire-bloc" action="#" method="post" novalidate>
                <?php wp_nonce_field('contact', 'contact_nonce'); ?>

                <div class="formulaire-grille-2">
                    <div class="formulaire-groupe">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Marie" required>
                    </div>
                    <div class="formulaire-groupe">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Tremblay" required>
                    </div>
                </div>

                <div class="formulaire-groupe">
                    <label for="courriel_form">Courriel</label>
                    <input type="email" id="courriel_form" name="courriel" placeholder="marie@exemple.com" required>
                </div>

                <div class="formulaire-groupe">
                    <label for="sujet">Sujet</label>
                    <input type="text" id="sujet" name="sujet" placeholder="Ex : Question sur un produit">
                </div>

                <div class="formulaire-groupe">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" placeholder="Votre message..." rows="6" required></textarea>
                </div>

                <button type="submit" class="bouton-primaire">Envoyer le message</button>
            </form>
        </div>

    </div>
</section>

<?php get_footer();
