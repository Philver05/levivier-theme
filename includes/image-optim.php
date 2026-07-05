<?php
/**
 * Le Vivier — Optimisation automatique des images
 * ------------------------------------------------
 * Compresse et allège chaque image au moment de l'upload, sans plugin.
 *
 * Ce qui est fait automatiquement :
 *   1. Qualité de compression raisonnable (JPEG + WebP).
 *   2. Réduction des images trop grandes (cap de dimension).
 *   3. Génération des tailles d'affichage en WebP (servies via le srcset natif).
 *
 * Important : ces réglages s'appliquent aux NOUVEAUX médias (et aux tailles
 * régénérées). Les images déjà en ligne ne sont pas re-traitées tant qu'on ne
 * régénère pas leurs miniatures (extension « Regenerate Thumbnails » ou ré-upload).
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------------------------
   1. Qualité de compression (82 = bon compromis poids / qualité)
------------------------------------------------------------------ */
function lv_image_quality()
{
    return 82;
}
add_filter('jpeg_quality', 'lv_image_quality');
add_filter('wp_editor_set_quality', 'lv_image_quality');

/* ------------------------------------------------------------------
   2. Cap de dimension : WordPress réduit déjà les images au-dessus d'un
   seuil (et crée une version « -scaled »). On abaisse ce seuil pour des
   fichiers plus légers — 1800px suffit largement pour un site vitrine.
------------------------------------------------------------------ */
add_filter('big_image_size_threshold', function () {
    return 1800;
});

/* ------------------------------------------------------------------
   3. WebP : on génère les tailles d'affichage en WebP plutôt qu'en JPEG.
   WordPress les sert ensuite automatiquement dans le srcset, sans
   <picture> ni réécriture serveur. Activé seulement si le serveur sait
   produire du WebP (GD avec imagewebp, ou Imagick compilé avec WEBP).
------------------------------------------------------------------ */
function lv_serveur_supporte_webp()
{
    if (function_exists('imagewebp')) {
        return true;
    }
    if (class_exists('Imagick')) {
        $formats = @\Imagick::queryFormats('WEBP');
        return !empty($formats);
    }
    return false;
}

add_action('init', function () {
    if (!lv_serveur_supporte_webp()) {
        return;
    }
    add_filter('image_editor_output_format', function ($formats) {
        // Les images JPEG produisent des miniatures WebP.
        $formats['image/jpeg'] = 'image/webp';
        return $formats;
    });
});
