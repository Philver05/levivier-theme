<?php
/* Bandeau de consentement aux cookies : affiché sur toutes les pages tant
   que le visiteur n'a pas cliqué « J'accepte » (mémorisé en localStorage,
   aucun cookie technique n'est nécessaire pour ce bandeau lui-même). */
function lv_cookie_bandeau()
{
    $texte = function_exists('lv_opt')
        ? lv_opt('opt_cookie_texte', 'Ce site utilise des cookies essentiels à son bon fonctionnement.')
        : 'Ce site utilise des cookies essentiels à son bon fonctionnement.';

    $page_politiques = get_page_by_path('politiques');
    $url_politiques   = $page_politiques ? get_permalink($page_politiques) : home_url('/politiques/');
    ?>
    <div class="cookie-bandeau" id="cookie-bandeau" role="region" aria-label="Consentement aux cookies" hidden>
        <div class="conteneur cookie-bandeau-inner">
            <p><?php echo esc_html($texte); ?>
                <a href="<?php echo esc_url($url_politiques); ?>"><?php echo esc_html('En savoir plus'); ?></a>
            </p>
            <button type="button" class="btn btn-primaire cookie-bandeau-btn" id="cookie-bandeau-accepter">J'accepte</button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'lv_cookie_bandeau');
