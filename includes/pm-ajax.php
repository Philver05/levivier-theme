<?php
add_action('wp_ajax_pm_soumettre',        'lv_pm_soumettre');
add_action('wp_ajax_nopriv_pm_soumettre', 'lv_pm_soumettre');

function lv_pm_soumettre()
{
    if (!check_ajax_referer('pm_commande', 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce invalide. Rechargez la page et réessayez.']);
    }

    $prenom      = sanitize_text_field(wp_unslash($_POST['prenom']      ?? ''));
    $nom         = sanitize_text_field(wp_unslash($_POST['nom']         ?? ''));
    $telephone   = sanitize_text_field(wp_unslash($_POST['telephone']   ?? ''));
    $email       = sanitize_email(wp_unslash($_POST['email']            ?? ''));
    $commentaire = sanitize_textarea_field(wp_unslash($_POST['commentaire'] ?? ''));
    $produits_raw = (isset($_POST['produits']) && is_array($_POST['produits'])) ? $_POST['produits'] : [];

    if (!$prenom || !$nom || !$email || !is_email($email)) {
        wp_send_json_error(['message' => 'Veuillez remplir les champs obligatoires (prénom, nom, courriel valide).']);
    }

    $produits = [];
    foreach ($produits_raw as $id => $qty) {
        $id  = absint($id);
        $qty = absint($qty);
        if ($id > 0 && $qty > 0) {
            $produits[$id] = $qty;
        }
    }

    if (empty($produits)) {
        wp_send_json_error(['message' => 'Veuillez choisir au moins un produit.']);
    }

    $lignes_html = '';
    $total       = 0.0;
    foreach ($produits as $id => $qty) {
        $titre      = get_the_title($id);
        $prix       = (float) get_field('pm_prix', $id);
        $sous_total = $prix * $qty;
        $total     += $sous_total;

        $lignes_html .= '<tr>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;">' . esc_html($titre) . '</td>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;text-align:center;">' . esc_html($qty) . '</td>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;text-align:right;">' . esc_html(number_format($prix, 2, ',', ' ')) . '&nbsp;$</td>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;text-align:right;">' . esc_html(number_format($sous_total, 2, ',', ' ')) . '&nbsp;$</td>'
            . '</tr>';
    }

    $message = '<!DOCTYPE html><html lang="fr"><body style="font-family:Arial,sans-serif;color:#2a2622;line-height:1.6;">'
        . '<h2 style="color:#4d6040;margin-bottom:1rem;">Nouvelle commande Produits Maison</h2>'
        . '<table style="border-collapse:collapse;margin-bottom:2rem;">'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Nom</th><td>' . esc_html($prenom . ' ' . $nom) . '</td></tr>'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Téléphone</th><td>' . esc_html($telephone) . '</td></tr>'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Courriel</th><td>' . esc_html($email) . '</td></tr>'
        . '</table>'
        . '<table style="border-collapse:collapse;width:100%;max-width:560px;">'
        . '<thead><tr style="background:#4d6040;color:#fff;">'
        . '<th style="padding:8px 12px;text-align:left;">Produit</th>'
        . '<th style="padding:8px 12px;text-align:center;">Qté</th>'
        . '<th style="padding:8px 12px;text-align:right;">Prix&nbsp;unit.</th>'
        . '<th style="padding:8px 12px;text-align:right;">Sous-total</th>'
        . '</tr></thead>'
        . '<tbody>' . $lignes_html . '</tbody>'
        . '<tfoot><tr style="background:#f9f5f0;font-weight:700;">'
        . '<td colspan="3" style="padding:10px 12px;border:1px solid #e4ddd0;text-align:right;">Total</td>'
        . '<td style="padding:10px 12px;border:1px solid #e4ddd0;text-align:right;">' . esc_html(number_format($total, 2, ',', ' ')) . '&nbsp;$</td>'
        . '</tr></tfoot>'
        . '</table>';

    if ($commentaire) {
        $message .= '<p style="margin-top:1.5rem;"><strong>Commentaire&nbsp;:</strong> ' . esc_html($commentaire) . '</p>';
    }

    $message .= '</body></html>';

    $sujet   = 'Nouvelle commande Produits Maison - ' . $prenom . ' ' . $nom;
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $email,
    ];

    $envoye = wp_mail(get_option('admin_email'), $sujet, $message, $headers);

    if ($envoye) {
        wp_send_json_success(['message' => 'Votre commande a bien été envoyée ! Nous vous contacterons pour confirmer la date de récupération.']);
    } else {
        wp_send_json_error(['message' => 'Une erreur est survenue lors de l\'envoi. Veuillez nous appeler au (418) 562-5230.']);
    }
}
