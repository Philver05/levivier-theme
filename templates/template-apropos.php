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
     EN-TÊTE
====================================================== -->
<section class="page-entete">
    <span class="arche-mini am-terra"></span>
    <span class="arche-mini am-ocre"></span>
    <div class="conteneur">
        <p class="eyebrow">Notre histoire · Le Vivier</p>
        <h1><?php the_title(); ?></h1>
    </div>
</section>

<!-- ======================================================
     NOTRE HISTOIRE — contenu éditeur
====================================================== -->
<?php if (have_posts()): while (have_posts()): the_post();
    $a_contenu = trim(get_the_content()) !== '';
    $a_image   = has_post_thumbnail();
    if ($a_contenu || $a_image): ?>
        <section class="section" style="padding-top:clamp(1.5rem,1rem+2vw,2.5rem)">
            <div class="conteneur">
                <?php if ($a_contenu && $a_image): ?>
                    <div class="apropos-split apropos-split--histoire">
                        <div class="page-prose reveal" style="margin:0"><?php the_content(); ?></div>
                        <div class="apropos-media reveal">
                            <div class="cadre"><?php the_post_thumbnail('large', ['alt' => get_the_title()]); ?></div>
                        </div>
                    </div>
                <?php elseif ($a_contenu): ?>
                    <div class="page-prose reveal"><?php the_content(); ?></div>
                <?php else: ?>
                    <div class="apropos-media reveal" style="max-width:640px;margin-inline:auto">
                        <div class="cadre"><?php the_post_thumbnail('large', ['alt' => get_the_title()]); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif;
endwhile; endif; ?>

<!-- ======================================================
     VENEZ NOUS VOIR — coordonnées + carte
====================================================== -->
<section class="section producteurs">
    <div class="conteneur">
        <div class="section-titre">
            <h2>Venez nous voir</h2>
        </div>

        <div class="contact-grille contact-grille--carte">
            <ul class="contact-info reveal">
                <?php if ($adresse): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.6 7-12a7 7 0 1 0-14 0c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
                        <div><h4>Adresse</h4><p><?php echo nl2br(esc_html($adresse)); ?></p></div>
                    </li>
                <?php endif; ?>
                <?php if ($telephone): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h3l2 5-2.5 1.5a12 12 0 0 0 5 5L17 14l5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2Z"/></svg></span>
                        <div><h4>Téléphone</h4><p><a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telephone)); ?>"><?php echo esc_html($telephone); ?></a></p></div>
                    </li>
                <?php endif; ?>
                <?php if ($courriel): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
                        <div><h4>Courriel</h4><p><a href="mailto:<?php echo esc_attr($courriel); ?>"><?php echo esc_html($courriel); ?></a></p></div>
                    </li>
                <?php endif; ?>
                <?php if ($horaire_semaine || $horaire_samedi || $horaire_dimanche): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                        <div>
                            <h4>Horaires</h4>
                            <p>
                                <?php if ($horaire_semaine): ?>Lundi au vendredi : <?php echo esc_html($horaire_semaine); ?><br><?php endif; ?>
                                <?php if ($horaire_samedi): ?>Samedi : <?php echo esc_html($horaire_samedi); ?><br><?php endif; ?>
                                <?php if ($horaire_dimanche): ?>Dimanche : <?php echo esc_html($horaire_dimanche); ?><?php endif; ?>
                            </p>
                        </div>
                    </li>
                <?php endif; ?>
                <?php if ($facebook_url): ?>
                    <li>
                        <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M15 8h-2a2 2 0 0 0-2 2v10M8 12h6"/></svg></span>
                        <div><h4>Suivez-nous</h4><p><a href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener">Facebook</a></p></div>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="carte-map reveal">
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
<section class="section">
    <div class="conteneur">
        <div class="section-titre">
            <h2>Écrivez-nous</h2>
            <p>Une question, une commande spéciale ou l'envie de nous dire bonjour&nbsp;? On vous répond rapidement.</p>
        </div>

        <form class="contact-form reveal" action="#" method="post" novalidate style="max-width:680px;margin-inline:auto">
            <?php wp_nonce_field('contact', 'contact_nonce'); ?>

            <div class="champ-duo">
                <div class="champ">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Marie" required>
                </div>
                <div class="champ">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Tremblay" required>
                </div>
            </div>

            <div class="champ">
                <label for="courriel_form">Courriel</label>
                <input type="email" id="courriel_form" name="courriel" placeholder="marie@exemple.com" required>
            </div>

            <div class="champ">
                <label for="sujet">Sujet</label>
                <input type="text" id="sujet" name="sujet" placeholder="Ex : Question sur un produit">
            </div>

            <div class="champ">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Votre message..." rows="6" required></textarea>
            </div>

            <button type="submit" class="btn btn-primaire">Envoyer le message</button>
        </form>
    </div>
</section>

<?php get_footer();
