<?php get_header(); ?>

<section class="section section-detail" style="text-align:center">
    <div class="conteneur">
        <p class="erreur-404-code">404</p>
        <h1>Page introuvable</h1>
        <p style="max-width:46ch;margin:.8rem auto 1.8rem;color:var(--texte-corps)">La page que vous cherchez n'existe pas ou a été déplacée.</p>
        <a class="btn btn-primaire" href="<?php echo esc_url(home_url('/')); ?>">Retour à l'accueil</a>
    </div>
</section>

<?php get_footer();
