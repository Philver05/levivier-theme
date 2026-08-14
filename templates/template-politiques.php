<?php /* Template Name: Politique de confidentialité */
get_header(); ?>

<style>
/* Politique — CSS inline (bypass cache serveur) */
.pol-page { padding-block: clamp(2.5rem,4vw,5rem) clamp(3.5rem,6vw,7rem); }

.pol-grille {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: clamp(2.5rem,4vw,5rem);
    align-items: start;
}

.pol-sidebar-inner {
    position: sticky;
    top: calc(88px + 1.5rem);
    background: var(--sauge-pale);
    border-radius: .75rem;
    padding: 1.4rem 1.25rem 1.6rem;
}

.pol-sidebar-titre {
    font-size: .63rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--sauge);
    margin: 0 0 .9rem;
    padding-bottom: .7rem;
    border-bottom: 1px solid rgba(77,96,64,.25);
}

.pol-toc { list-style: none; margin: 0; padding: 0; }
.pol-toc li + li { margin-top: .1rem; }

.pol-toc-lien {
    display: block;
    font-size: .77rem;
    line-height: 1.35;
    color: var(--texte-moyen);
    text-decoration: none;
    padding: .3rem .5rem .3rem .7rem;
    border-left: 2px solid transparent;
    border-radius: 0 .2rem .2rem 0;
    transition: color .15s, border-color .15s, background .15s;
}

.pol-toc-lien:hover {
    color: var(--sauge);
    background: rgba(255,255,255,.55);
    border-left-color: var(--sauge);
}

.pol-toc-lien--actif {
    color: var(--sauge);
    font-weight: 700;
    border-left-color: var(--terracotta);
    background: rgba(255,255,255,.6);
}

/* Carte méta */
.pol-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem 2rem;
    background: var(--creme);
    border: 1px solid var(--bordure);
    border-radius: .6rem;
    padding: 1rem 1.25rem;
    margin-bottom: 2.75rem;
}

.pol-meta-item { font-size: .82rem; color: var(--texte-moyen); line-height: 1.55; }
.pol-meta-item strong { display: block; font-size: .62rem; letter-spacing: .1em; text-transform: uppercase; color: var(--sauge); font-weight: 700; margin-bottom: .1rem; }
.pol-meta-item a { color: var(--terracotta-texte); text-decoration: none; }
.pol-meta-item a:hover { text-decoration: underline; }

/* Corps */
.pol-prose { font-size: 1.0rem; line-height: 1.82; color: var(--texte); }
.pol-prose p { margin: 0 0 1.1rem; }

.pol-prose h2 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--sauge);
    margin: 3rem 0 .8rem;
    padding-top: 1.75rem;
    border-top: 1px solid var(--bordure);
    scroll-margin-top: 110px;
    letter-spacing: .01em;
}

.pol-prose h2:first-of-type { margin-top: 0; padding-top: 0; border-top: none; }

.pol-prose h3 { font-size: .9rem; font-weight: 700; color: var(--texte-moyen); margin: 1.75rem 0 .5rem; }

.pol-prose ul, .pol-prose ol { margin: .4rem 0 1.35rem; padding: 0; list-style: none; }

.pol-prose li {
    position: relative;
    padding-left: 1.25rem;
    margin-bottom: .55rem;
    line-height: 1.7;
    font-size: .95rem;
}

.pol-prose ul > li::before {
    content: '';
    position: absolute;
    left: .15rem;
    top: .7em;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--terracotta);
    opacity: .8;
}

.pol-prose a { color: var(--terracotta-texte); text-decoration: underline; text-underline-offset: 3px; }
.pol-prose a:hover { color: var(--sauge); }
.pol-prose em { font-style: italic; color: var(--texte-moyen); }
.pol-prose strong { font-weight: 700; }

@media (max-width: 860px) {
    .pol-grille { grid-template-columns: 1fr; }
    .pol-sidebar { display: none; }
    .pol-prose h2 { scroll-margin-top: 80px; }
    .pol-meta { flex-direction: column; gap: .65rem; }
}
</style>

<section class="page-entete">
    <div class="conteneur">
        <p class="eyebrow">Informations légales</p>
        <h1><?php the_title(); ?></h1>
    </div>
</section>

<div class="pol-page">
    <div class="conteneur pol-grille">

        <aside class="pol-sidebar" aria-label="Sommaire">
            <div class="pol-sidebar-inner">
                <p class="pol-sidebar-titre">Sommaire</p>
                <nav><ul class="pol-toc" id="pol-toc"></ul></nav>
            </div>
        </aside>

        <main class="pol-contenu">

            <div class="pol-meta">
                <div class="pol-meta-item">
                    <strong>Responsable</strong>
                    Le Vivier, épicerie boutique — Matane (QC)
                </div>
                <div class="pol-meta-item">
                    <strong>Courriel</strong>
                    <a href="mailto:epicerie@levivier.net">epicerie@levivier.net</a>
                </div>
                <div class="pol-meta-item">
                    <strong>Mis à jour</strong>
                    Janvier 2025
                </div>
            </div>

            <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <div class="pol-prose"><?php the_content(); ?></div>
            <?php endwhile; endif; ?>

        </main>

    </div>
</div>

<script>
(function () {
    var prose = document.querySelector('.pol-prose');
    var toc   = document.getElementById('pol-toc');
    if (!prose || !toc) return;

    var headings = Array.from(prose.querySelectorAll('h2'));
    headings.forEach(function (h, i) {
        h.id = 'pol-s' + i;
        var li = document.createElement('li');
        var a  = document.createElement('a');
        a.href        = '#pol-s' + i;
        a.textContent = h.textContent.replace(/^\d+\.\s*/, '');
        a.className   = 'pol-toc-lien';
        li.appendChild(a);
        toc.appendChild(li);
    });

    var liens = Array.from(toc.querySelectorAll('.pol-toc-lien'));
    function majActif() {
        var actif = 0;
        headings.forEach(function (h, i) {
            if (h.getBoundingClientRect().top <= 120) actif = i;
        });
        liens.forEach(function (l, i) {
            l.classList.toggle('pol-toc-lien--actif', i === actif);
        });
    }
    window.addEventListener('scroll', majActif, { passive: true });
    majActif();
})();
</script>

<?php get_footer();
