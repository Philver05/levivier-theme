<?php
/* Formulaire de contact : envoi AJAX + wp_mail (même mécanique que les bons
   de commande PAM/PM/vrac). Destinataire : courriel des Réglages du site,
   repli sur l'adresse admin de WP. */

add_action('wp_ajax_ct_soumettre',        'lv_contact_soumettre');
add_action('wp_ajax_nopriv_ct_soumettre', 'lv_contact_soumettre');

function lv_contact_soumettre()
{
    if (!check_ajax_referer('ct_contact', 'nonce', false)) {
        wp_send_json_error(['message' => 'Session expirée. Rechargez la page et réessayez.']);
    }

    /* Leurre anti-pourriel : un humain ne remplit jamais ce champ caché.
       On répond "succès" pour ne pas donner d'indice aux robots. */
    if (!empty($_POST['site_web'])) {
        wp_send_json_success(['message' => 'Votre message a bien été envoyé. Merci !']);
    }

    $prenom    = sanitize_text_field(wp_unslash($_POST['prenom']    ?? ''));
    $nom       = sanitize_text_field(wp_unslash($_POST['nom']       ?? ''));
    $courriel  = sanitize_email(wp_unslash($_POST['courriel']       ?? ''));
    $telephone = sanitize_text_field(wp_unslash($_POST['telephone'] ?? ''));
    $sujet     = sanitize_text_field(wp_unslash($_POST['sujet']     ?? ''));
    $texte     = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

    if (!$prenom || !$nom || !$courriel || !is_email($courriel) || !$texte) {
        wp_send_json_error(['message' => 'Veuillez remplir les champs obligatoires (prénom, nom, courriel valide et message).']);
    }

    $destinataire_principal = function_exists('lv_opt') ? lv_opt('opt_courriel', 'epicerie@levivier.net') : 'epicerie@levivier.net';
    if (!$destinataire_principal || !is_email($destinataire_principal)) {
        $destinataire_principal = get_option('admin_email');
    }
    $destinataires = [$destinataire_principal];
    $destinataire_secondaire = function_exists('lv_opt') ? lv_opt('opt_courriel_secondaire', '') : '';
    if ($destinataire_secondaire && is_email($destinataire_secondaire)) {
        $destinataires[] = $destinataire_secondaire;
    }

    $message = '<!DOCTYPE html><html lang="fr"><body style="font-family:Arial,sans-serif;color:#2a2622;line-height:1.6;">'
        . '<h2 style="color:#4d6040;margin-bottom:1rem;">Nouveau message — formulaire Contactez-nous</h2>'
        . '<table style="border-collapse:collapse;margin-bottom:1.5rem;">'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Nom</th><td>' . esc_html($prenom . ' ' . $nom) . '</td></tr>'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Courriel</th><td>' . esc_html($courriel) . '</td></tr>'
        . ($telephone ? '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Téléphone</th><td>' . esc_html($telephone) . '</td></tr>' : '')
        . ($sujet ? '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Sujet</th><td>' . esc_html($sujet) . '</td></tr>' : '')
        . '</table>'
        . '<p style="white-space:pre-line;background:#f9f5f0;border-left:3px solid #4d6040;padding:12px 16px;border-radius:0 8px 8px 0;">'
        . esc_html($texte)
        . '</p>'
        . '</body></html>';

    $objet   = 'Message du site — ' . ($sujet ?: $prenom . ' ' . $nom);
    $entetes = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Le Vivier <' . $destinataire_principal . '>',
        'Reply-To: ' . $courriel,
    ];

    $envoye = wp_mail($destinataires, $objet, $message, $entetes);

    if ($envoye) {
        wp_send_json_success(['message' => 'Votre message a bien été envoyé. Merci ! Nous vous répondrons rapidement.']);
    } else {
        $tel = function_exists('lv_opt') ? lv_opt('opt_telephone', '(418) 562-5230') : '(418) 562-5230';
        wp_send_json_error(['message' => 'Une erreur est survenue lors de l\'envoi. Vous pouvez nous appeler au ' . $tel . '.']);
    }
}
