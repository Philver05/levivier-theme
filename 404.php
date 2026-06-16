<?php get_header(); ?>

<div class="page-404">
    <p class="code-erreur">404</p>
    <h2>Page introuvable</h2>
    <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <a class="bouton-primaire" href="<?php echo esc_url(home_url('/')); ?>">Retour à l'accueil</a>
</div>

<?php get_footer();
