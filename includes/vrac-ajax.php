<?php
add_action('wp_ajax_vrac_soumettre',        'lv_vrac_soumettre');
add_action('wp_ajax_nopriv_vrac_soumettre', 'lv_vrac_soumettre');

function lv_vrac_soumettre()
{
    if (!check_ajax_referer('vrac_commande', 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce invalide. Rechargez la page et réessayez.']);
    }

    $prenom      = sanitize_text_field(wp_unslash($_POST['prenom']      ?? ''));
    $nom         = sanitize_text_field(wp_unslash($_POST['nom']         ?? ''));
    $telephone   = sanitize_text_field(wp_unslash($_POST['telephone']   ?? ''));
    $email       = sanitize_email(wp_unslash($_POST['email']            ?? ''));
    $commentaire = sanitize_textarea_field(wp_unslash($_POST['commentaire'] ?? ''));
    $escompte    = (float) ($_POST['escompte_pct'] ?? 0);
    $produits_raw = (isset($_POST['produits']) && is_array($_POST['produits'])) ? $_POST['produits'] : [];

    if (!$prenom || !$nom || !$email || !is_email($email)) {
        wp_send_json_error(['message' => 'Veuillez remplir les champs obligatoires (prénom, nom, courriel valide).']);
    }

    // Sanitize: produits[id][format_label] = qty
    $produits = [];
    foreach ($produits_raw as $prod_id => $formats) {
        $prod_id = absint($prod_id);
        if (!$prod_id || !is_array($formats)) continue;

        foreach ($formats as $fmt_index => $fmt_data) {
            if (!is_array($fmt_data)) continue;
            $label = sanitize_text_field(wp_unslash($fmt_data['label'] ?? ''));
            $qty   = absint($fmt_data['qty'] ?? 0);
            $prix  = (float) ($fmt_data['prix'] ?? 0);
            if ($qty > 0 && $label) {
                $produits[] = [
                    'prod_id' => $prod_id,
                    'titre'   => get_the_title($prod_id),
                    'label'   => $label,
                    'qty'     => $qty,
                    'prix'    => $prix,
                ];
            }
        }
    }

    if (empty($produits)) {
        wp_send_json_error(['message' => 'Veuillez choisir au moins un produit.']);
    }

    $escompte_pct = min(100, max(0, $escompte));

    $lignes_html = '';
    $sous_total  = 0.0;
    foreach ($produits as $ligne) {
        $montant     = $ligne['prix'] * $ligne['qty'];
        $sous_total += $montant;

        $lignes_html .= '<tr>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;">' . esc_html($ligne['titre']) . '</td>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;">' . esc_html($ligne['label']) . '</td>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;text-align:center;">' . esc_html($ligne['qty']) . '</td>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;text-align:right;">' . esc_html(number_format($ligne['prix'], 2, ',', ' ')) . '&nbsp;$</td>'
            . '<td style="padding:6px 12px;border:1px solid #e4ddd0;text-align:right;">' . esc_html(number_format($montant, 2, ',', ' ')) . '&nbsp;$</td>'
            . '</tr>';
    }

    $rabais = $escompte_pct > 0 ? round($sous_total * $escompte_pct / 100, 2) : 0;
    $total  = $sous_total - $rabais;

    $message = '<!DOCTYPE html><html lang="fr"><body style="font-family:Arial,sans-serif;color:#2a2622;line-height:1.6;">'
        . '<h2 style="color:#4d6040;margin-bottom:1rem;">Nouvelle commande Vrac</h2>'
        . '<table style="border-collapse:collapse;margin-bottom:2rem;">'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Nom</th><td>' . esc_html($prenom . ' ' . $nom) . '</td></tr>'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Téléphone</th><td>' . esc_html($telephone) . '</td></tr>'
        . '<tr><th style="text-align:left;padding:4px 16px 4px 0;font-weight:600;">Courriel</th><td>' . esc_html($email) . '</td></tr>'
        . '</table>'
        . '<table style="border-collapse:collapse;width:100%;max-width:620px;">'
        . '<thead><tr style="background:#4d6040;color:#fff;">'
        . '<th style="padding:8px 12px;text-align:left;">Produit</th>'
        . '<th style="padding:8px 12px;text-align:left;">Format</th>'
        . '<th style="padding:8px 12px;text-align:center;">Qté</th>'
        . '<th style="padding:8px 12px;text-align:right;">Prix&nbsp;unit.</th>'
        . '<th style="padding:8px 12px;text-align:right;">Sous-total</th>'
        . '</tr></thead>'
        . '<tbody>' . $lignes_html . '</tbody>'
        . '<tfoot>';

    $message .= '<tr style="background:#f9f5f0;">'
        . '<td colspan="4" style="padding:8px 12px;border:1px solid #e4ddd0;text-align:right;font-weight:600;">Sous-total</td>'
        . '<td style="padding:8px 12px;border:1px solid #e4ddd0;text-align:right;">' . esc_html(number_format($sous_total, 2, ',', ' ')) . '&nbsp;$</td>'
        . '</tr>';

    if ($escompte_pct > 0) {
        $message .= '<tr style="background:#dde8d9;">'
            . '<td colspan="4" style="padding:8px 12px;border:1px solid #e4ddd0;text-align:right;color:#4d6040;font-weight:600;">Escompte (' . esc_html($escompte_pct) . '&nbsp;%)</td>'
            . '<td style="padding:8px 12px;border:1px solid #e4ddd0;text-align:right;color:#4d6040;">-' . esc_html(number_format($rabais, 2, ',', ' ')) . '&nbsp;$</td>'
            . '</tr>';
    }

    $message .= '<tr style="background:#f9f5f0;font-weight:700;">'
        . '<td colspan="4" style="padding:10px 12px;border:1px solid #e4ddd0;text-align:right;">Total</td>'
        . '<td style="padding:10px 12px;border:1px solid #e4ddd0;text-align:right;">' . esc_html(number_format($total, 2, ',', ' ')) . '&nbsp;$</td>'
        . '</tr>'
        . '</tfoot></table>';

    if ($commentaire) {
        $message .= '<p style="margin-top:1.5rem;"><strong>Commentaire&nbsp;:</strong> ' . esc_html($commentaire) . '</p>';
    }

    $message .= '</body></html>';

    $sujet   = 'Nouvelle commande Vrac - ' . $prenom . ' ' . $nom;
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $email,
    ];

    $destinataire = function_exists('lv_opt') ? lv_opt('opt_courriel', 'epicerie@levivier.net') : 'epicerie@levivier.net';
    if (!$destinataire || !is_email($destinataire)) {
        $destinataire = get_option('admin_email');
    }

    $envoye = wp_mail($destinataire, $sujet, $message, $headers);

    if ($envoye) {
        wp_send_json_success(['message' => 'Votre commande a bien été envoyée ! Nous vous contacterons pour confirmer.']);
    } else {
        wp_send_json_error(['message' => 'Une erreur est survenue lors de l\'envoi. Veuillez nous appeler au (418) 562-5230.']);
    }
}
