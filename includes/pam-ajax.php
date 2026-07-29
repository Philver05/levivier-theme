<?php
add_action('wp_ajax_pam_soumettre',        'lv_pam_soumettre');
add_action('wp_ajax_nopriv_pam_soumettre', 'lv_pam_soumettre');

function lv_pam_soumettre()
{
    if (!check_ajax_referer('pam_commande', 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce invalide. Rechargez la page et réessayez.']);
    }

    $prenom      = sanitize_text_field(wp_unslash($_POST['prenom']      ?? ''));
    $nom         = sanitize_text_field(wp_unslash($_POST['nom']         ?? ''));
    $telephone   = sanitize_text_field(wp_unslash($_POST['telephone']   ?? ''));
    $email       = sanitize_email(wp_unslash($_POST['email']            ?? ''));
    $commentaire = sanitize_textarea_field(wp_unslash($_POST['commentaire'] ?? ''));
    $jour        = sanitize_text_field(wp_unslash($_POST['jour']        ?? ''));
    $date_recup  = sanitize_text_field(wp_unslash($_POST['date_recuperation']  ?? ''));
    $heure_recup = sanitize_text_field(wp_unslash($_POST['heure_recuperation'] ?? ''));
    $produits_raw = (isset($_POST['produits']) && is_array($_POST['produits'])) ? $_POST['produits'] : [];

    if (!$prenom || !$nom || !$email || !is_email($email)) {
        wp_send_json_error(['message' => 'Veuillez remplir les champs obligatoires (prénom, nom, courriel valide).']);
    }

    if (!$date_recup || !$heure_recup) {
        wp_send_json_error(['message' => 'Veuillez choisir une date et une plage horaire de récupération.']);
    }

    $date_recup_ts    = strtotime($date_recup);
    $date_recup_label = $date_recup_ts ? date_i18n('l j F Y', $date_recup_ts) : $date_recup;

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

    $jours_labels = [
        'tous_les_jours' => 'Disponibles tous les jours',
        'lundi'          => 'Spécial lundi',
        'mardi'          => 'Spécial mardi',
        'mercredi'       => 'Spécial mercredi',
        'jeudi'          => 'Spécial jeudi',
        'vendredi'       => 'Spécial vendredi',
        'samedi'         => 'Spécial samedi',
        'dimanche'       => 'Spécial dimanche',
    ];
    $jour_label = $jours_labels[$jour] ?? $jour;

    $lignes_html = '';
    $total       = 0.0;
    foreach ($produits as $id => $qty) {
        $titre      = get_the_title($id);
        $prix       = (float) get_field('pam_prix', $id);
        $sous_total = $prix * $qty;
        $total     += $sous_total;

        $lignes_html .= '<tr>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;color:#1f2937;">' . esc_html($titre) . '</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;text-align:center;color:#1f2937;">' . esc_html($qty) . '</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;text-align:right;color:#1f2937;">' . esc_html(number_format($prix, 2, ',', ' ')) . '&nbsp;$</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;text-align:right;color:#1f2937;font-weight:600;">' . esc_html(number_format($sous_total, 2, ',', ' ')) . '&nbsp;$</td>'
            . '</tr>';
    }

    $lignes_client = [
        'Nom'                       => $prenom . ' ' . $nom,
        'Téléphone'                 => $telephone ?: '—',
        'Courriel'                  => $email,
        'Jour choisi dans le bon'   => $jour_label,
        'Date de récupération'     => $date_recup_label,
        'Heure de récupération'    => $heure_recup,
    ];
    $client_html = '';
    foreach ($lignes_client as $label => $valeur) {
        $client_html .= '<tr>'
            . '<td style="padding:5px 0;color:#4b5563;font-size:13px;width:180px;">' . esc_html($label) . '</td>'
            . '<td style="padding:5px 0;color:#1f2937;font-weight:600;">' . esc_html($valeur) . '</td>'
            . '</tr>';
    }

    $tel_site = function_exists('lv_opt') ? lv_opt('opt_telephone', '(418) 562-5230') : '(418) 562-5230';

    $message = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head>'
        . '<body style="margin:0;padding:0;background:#f9f5f0;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">'
        . '<table role="presentation" width="100%" style="background:#f9f5f0;padding:24px 0;">'
        . '<tr><td align="center">'
        . '<table role="presentation" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #d1d5db;">'

        // Bandeau
        . '<tr><td style="background:#4d6040;padding:24px 32px;">'
        . '<span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:.02em;">Le Vivier</span><br>'
        . '<span style="color:#dde8d9;font-size:14px;">Nouvelle commande — Prêt à manger</span>'
        . '</td></tr>'

        // Coordonnées client
        . '<tr><td style="padding:28px 32px 8px;">'
        . '<h2 style="margin:0 0 16px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Coordonnées du client</h2>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;">' . $client_html . '</table>'
        . '</td></tr>'

        // Produits
        . '<tr><td style="padding:24px 32px 8px;">'
        . '<h2 style="margin:0 0 16px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Détail de la commande</h2>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;">'
        . '<thead><tr style="background:#dde8d9;">'
        . '<th style="padding:10px 14px;text-align:left;color:#1f2937;font-size:13px;">Produit</th>'
        . '<th style="padding:10px 14px;text-align:center;color:#1f2937;font-size:13px;">Qté</th>'
        . '<th style="padding:10px 14px;text-align:right;color:#1f2937;font-size:13px;">Prix unit.</th>'
        . '<th style="padding:10px 14px;text-align:right;color:#1f2937;font-size:13px;">Sous-total</th>'
        . '</tr></thead>'
        . '<tbody>' . $lignes_html . '</tbody>'
        . '</table>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;margin-top:8px;">'
        . '<tr><td style="padding:12px 14px;text-align:right;color:#1f2937;font-size:15px;font-weight:700;">Total&nbsp;: <span style="color:#b85c50;font-size:18px;">' . esc_html(number_format($total, 2, ',', ' ')) . '&nbsp;$</span></td></tr>'
        . '</table>'
        . '</td></tr>';

    if ($commentaire) {
        $message .= '<tr><td style="padding:8px 32px 8px;">'
            . '<h2 style="margin:0 0 8px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Commentaire du client</h2>'
            . '<p style="margin:0;color:#1f2937;background:#f9f5f0;border-radius:8px;padding:12px 16px;">' . esc_html($commentaire) . '</p>'
            . '</td></tr>';
    }

    $message .= '<tr><td style="padding:24px 32px 28px;border-top:1px solid #d1d5db;">'
        . '<p style="margin:0;color:#4b5563;font-size:13px;">Répondez directement à ce courriel pour rejoindre le client, ou appelez-le. Numéro du Vivier&nbsp;: ' . esc_html($tel_site) . '.</p>'
        . '</td></tr>'

        . '</table>'
        . '</td></tr>'
        . '</table>'
        . '</body></html>';

    $sujet = 'Nouvelle commande Prêt à manger - ' . $prenom . ' ' . $nom;

    $destinataire_principal = function_exists('lv_opt') ? lv_opt('opt_courriel', 'epicerie@levivier.net') : 'epicerie@levivier.net';
    if (!$destinataire_principal || !is_email($destinataire_principal)) {
        $destinataire_principal = get_option('admin_email');
    }
    $destinataires = [$destinataire_principal];
    $destinataire_secondaire = function_exists('lv_opt') ? lv_opt('opt_courriel_secondaire', '') : '';
    if ($destinataire_secondaire && is_email($destinataire_secondaire)) {
        $destinataires[] = $destinataire_secondaire;
    }

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $email,
    ];

    $envoye = wp_mail($destinataires, $sujet, $message, $headers);

    if ($envoye) {
        wp_send_json_success(['message' => 'Votre commande a bien été envoyée ! Nous vous contacterons pour confirmer la date de récupération.']);
    } else {
        wp_send_json_error(['message' => 'Une erreur est survenue lors de l\'envoi. Veuillez nous appeler au (418) 562-5230.']);
    }
}
