<form class="formulaire-recherche" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <input
        type="search"
        name="s"
        placeholder="Rechercher..."
        value="<?php echo esc_attr(get_search_query()); ?>"
        aria-label="Rechercher sur le site">
    <button type="submit" aria-label="Lancer la recherche">Rechercher</button>
</form>
