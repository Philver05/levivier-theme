<?php
add_action('wp_ajax_bx_soumettre',        'lv_bx_soumettre');
add_action('wp_ajax_nopriv_bx_soumettre', 'lv_bx_soumettre');

function lv_bx_soumettre()
{
    if (!check_ajax_referer('bx_commande', 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce invalide. Rechargez la page et réessayez.']);
    }

    $prenom      = sanitize_text_field(wp_unslash($_POST['prenom']      ?? ''));
    $nom         = sanitize_text_field(wp_unslash($_POST['nom']         ?? ''));
    $entreprise  = sanitize_text_field(wp_unslash($_POST['entreprise']  ?? ''));
    $telephone   = sanitize_text_field(wp_unslash($_POST['telephone']   ?? ''));
    $email       = sanitize_email(wp_unslash($_POST['email']            ?? ''));
    $date_recup  = sanitize_text_field(wp_unslash($_POST['date_recuperation']  ?? ''));
    $heure_raw   = sanitize_text_field(wp_unslash($_POST['heure_recuperation'] ?? ''));
    $restrictions = sanitize_textarea_field(wp_unslash($_POST['restrictions'] ?? ''));
    $commentaire  = sanitize_textarea_field(wp_unslash($_POST['commentaire']   ?? ''));
    $formules_raw = (isset($_POST['formules']) && is_array($_POST['formules'])) ? $_POST['formules'] : [];
    $leurre       = sanitize_text_field(wp_unslash($_POST['site_web'] ?? ''));

    /* Honeypot */
    if ($leurre) {
        wp_send_json_error(['message' => 'Erreur de validation.']);
    }

    if (!$prenom || !$nom || !$email || !is_email($email) || !$telephone) {
        wp_send_json_error(['message' => 'Veuillez remplir tous les champs obligatoires (prénom, nom, courriel, téléphone).']);
    }

    if (!$date_recup || !$heure_raw) {
        wp_send_json_error(['message' => 'Veuillez choisir une date et une heure de récupération.']);
    }

    if (preg_match('/^(\d{1,2}):(\d{2})$/', $heure_raw, $hm)) {
        $heure_label = (int) $hm[1] . ' h' . ($hm[2] !== '00' ? ' ' . $hm[2] : '');
    } else {
        $heure_label = $heure_raw;
    }

    $formules = [];
    foreach ($formules_raw as $f) {
        $nom_f   = sanitize_text_field(wp_unslash($f['nom']    ?? ''));
        $prix_f  = (float) ($f['prix']  ?? 0);
        $qty_f   = absint($f['qty']     ?? 0);
        $inclus_f = sanitize_textarea_field(wp_unslash($f['inclus'] ?? ''));
        if ($nom_f && $qty_f > 0) {
            $formules[] = ['nom' => $nom_f, 'prix' => $prix_f, 'qty' => $qty_f, 'inclus' => $inclus_f];
        }
    }

    if (empty($formules)) {
        wp_send_json_error(['message' => 'Veuillez sélectionner au moins une formule.']);
    }

    $date_ts    = strtotime($date_recup);
    $date_label = $date_ts ? date_i18n('l j F Y', $date_ts) : $date_recup;

    $lignes_html  = '';
    $total        = 0.0;
    $total_boites = 0;
    foreach ($formules as $f) {
        $sous_total    = $f['prix'] * $f['qty'];
        $total        += $sous_total;
        $total_boites += $f['qty'];
        $lignes_html  .= '<tr>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;color:#1f2937;">' . esc_html($f['nom']) . '</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;text-align:center;color:#1f2937;">' . esc_html($f['qty']) . '</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;text-align:right;color:#1f2937;">' . esc_html(number_format($f['prix'], 2, ',', ' ')) . '&nbsp;$</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e4ddd0;text-align:right;font-weight:600;color:#1f2937;">' . esc_html(number_format($sous_total, 2, ',', ' ')) . '&nbsp;$</td>'
            . '</tr>';
    }

    $lignes_client = [
        'Nom'              => $prenom . ' ' . $nom,
        'Téléphone'        => $telephone,
        'Courriel'         => $email,
        'Date'             => $date_label,
        'Heure'            => $heure_label,
    ];
    if ($entreprise)   $lignes_client['Entreprise']   = $entreprise;
    if ($restrictions) $lignes_client['Restrictions'] = $restrictions;

    $client_html = '';
    foreach ($lignes_client as $label => $valeur) {
        $client_html .= '<tr>'
            . '<td style="padding:5px 0;color:#4b5563;font-size:13px;width:200px;">' . esc_html($label) . '</td>'
            . '<td style="padding:5px 0;color:#1f2937;font-weight:600;">' . esc_html($valeur) . '</td>'
            . '</tr>';
    }

    $tel_site    = function_exists('lv_opt') ? lv_opt('opt_telephone', '(418) 562-5230') : '(418) 562-5230';
    $mention_ent = $entreprise ? ' — ' . $entreprise : '';
    $boites_label = $total_boites . ' boîte' . ($total_boites > 1 ? 's' : '');

    $message = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head>'
        . '<body style="margin:0;padding:0;background:#f9f5f0;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">'
        . '<table role="presentation" width="100%" style="background:#f9f5f0;padding:24px 0;"><tr><td align="center">'
        . '<table role="presentation" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #d1d5db;">'

        . '<tr><td style="background:#4d6040;padding:24px 32px;">'
        . '<span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:.02em;">Le Vivier</span><br>'
        . '<span style="color:#dde8d9;font-size:14px;">Nouvelle commande — Boîtes à lunch' . esc_html($mention_ent) . '</span>'
        . '</td></tr>'

        . '<tr><td style="padding:28px 32px 8px;">'
        . '<h2 style="margin:0 0 16px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Coordonnées</h2>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;">' . $client_html . '</table>'
        . '</td></tr>'

        . '<tr><td style="padding:24px 32px 8px;">'
        . '<h2 style="margin:0 0 16px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Commande (' . esc_html($boites_label) . ')</h2>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;">'
        . '<thead><tr style="background:#dde8d9;">'
        . '<th style="padding:10px 14px;text-align:left;color:#1f2937;font-size:13px;">Formule</th>'
        . '<th style="padding:10px 14px;text-align:center;color:#1f2937;font-size:13px;">Qté</th>'
        . '<th style="padding:10px 14px;text-align:right;color:#1f2937;font-size:13px;">Prix/pers.</th>'
        . '<th style="padding:10px 14px;text-align:right;color:#1f2937;font-size:13px;">Sous-total</th>'
        . '</tr></thead>'
        . '<tbody>' . $lignes_html . '</tbody>'
        . '</table>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;margin-top:8px;">'
        . '<tr><td style="padding:12px 14px;text-align:right;color:#1f2937;font-size:15px;font-weight:700;">Total : <span style="color:#b85c50;font-size:18px;">' . esc_html(number_format($total, 2, ',', ' ')) . '&nbsp;$</span></td></tr>'
        . '</table>'
        . '</td></tr>';

    if ($commentaire) {
        $message .= '<tr><td style="padding:8px 32px 8px;">'
            . '<h2 style="margin:0 0 8px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Commentaire</h2>'
            . '<p style="margin:0;color:#1f2937;background:#f9f5f0;border-radius:8px;padding:12px 16px;">' . esc_html($commentaire) . '</p>'
            . '</td></tr>';
    }

    $message .= '<tr><td style="padding:24px 32px 28px;border-top:1px solid #d1d5db;">'
        . '<p style="margin:0;color:#4b5563;font-size:13px;">Répondez directement à ce courriel pour rejoindre le client. Numéro du Vivier : ' . esc_html($tel_site) . '.</p>'
        . '</td></tr>'
        . '</table></td></tr></table></body></html>';

    $sujet = 'Boîtes à lunch — ' . $prenom . ' ' . $nom . ($entreprise ? ' (' . $entreprise . ')' : '') . ' — ' . $date_label;

    $dest = function_exists('lv_opt') ? lv_opt('opt_courriel', 'epicerie@levivier.net') : 'epicerie@levivier.net';
    if (!$dest || !is_email($dest)) $dest = get_option('admin_email');
    if (!$dest || !is_email($dest)) $dest = 'epicerie@levivier.net';

    $dests = [$dest];
    $sec   = function_exists('lv_opt') ? lv_opt('opt_courriel_secondaire', '') : '';
    if ($sec && is_email($sec)) $dests[] = $sec;

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Le Vivier <' . $dest . '>',
        'Reply-To: ' . $email,
    ];
    $envoye = wp_mail($dests, $sujet, $message, $headers);

    /* Confirmation automatique au client */
    $adresse_site = function_exists('lv_opt')
        ? preg_replace('/\s*\R\s*/', ', ', trim(lv_opt('opt_adresse', "14 Avenue D'Amours\nMatane, QC G4W 2X4")))
        : "14 Avenue D'Amours, Matane, QC G4W 2X4";

    $msg_client = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head>'
        . '<body style="margin:0;padding:0;background:#f9f5f0;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">'
        . '<table role="presentation" width="100%" style="background:#f9f5f0;padding:24px 0;"><tr><td align="center">'
        . '<table role="presentation" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #d1d5db;">'

        . '<tr><td style="background:#4d6040;padding:24px 32px;">'
        . '<span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:.02em;">Le Vivier</span><br>'
        . '<span style="color:#dde8d9;font-size:14px;">Merci pour votre commande !</span>'
        . '</td></tr>'

        . '<tr><td style="padding:28px 32px 8px;">'
        . '<p style="margin:0 0 16px;font-size:15px;">Bonjour ' . esc_html($prenom) . ',<br>Nous avons bien reçu votre commande de boîtes à lunch. Voici un résumé.</p>'
        . '<h2 style="margin:24px 0 16px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Récupération</h2>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;">'
        . '<tr><td style="padding:5px 0;color:#4b5563;font-size:13px;width:180px;">Date</td><td style="padding:5px 0;color:#1f2937;font-weight:600;">' . esc_html($date_label) . '</td></tr>'
        . '<tr><td style="padding:5px 0;color:#4b5563;font-size:13px;width:180px;">Heure</td><td style="padding:5px 0;color:#1f2937;font-weight:600;">' . esc_html($heure_label) . '</td></tr>'
        . '<tr><td style="padding:5px 0;color:#4b5563;font-size:13px;width:180px;">Adresse</td><td style="padding:5px 0;color:#1f2937;font-weight:600;">' . esc_html($adresse_site) . '</td></tr>'
        . '</table>'
        . '</td></tr>'

        . '<tr><td style="padding:24px 32px 8px;">'
        . '<h2 style="margin:0 0 16px;color:#4d6040;font-size:16px;text-transform:uppercase;letter-spacing:.04em;">Votre commande (' . esc_html($boites_label) . ')</h2>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;">'
        . '<thead><tr style="background:#dde8d9;">'
        . '<th style="padding:10px 14px;text-align:left;color:#1f2937;font-size:13px;">Formule</th>'
        . '<th style="padding:10px 14px;text-align:center;color:#1f2937;font-size:13px;">Qté</th>'
        . '<th style="padding:10px 14px;text-align:right;color:#1f2937;font-size:13px;">Prix/pers.</th>'
        . '<th style="padding:10px 14px;text-align:right;color:#1f2937;font-size:13px;">Sous-total</th>'
        . '</tr></thead>'
        . '<tbody>' . $lignes_html . '</tbody>'
        . '</table>'
        . '<table role="presentation" width="100%" style="border-collapse:collapse;margin-top:8px;">'
        . '<tr><td style="padding:12px 14px;text-align:right;color:#1f2937;font-size:15px;font-weight:700;">Total : <span style="color:#b85c50;font-size:18px;">' . esc_html(number_format($total, 2, ',', ' ')) . '&nbsp;$</span></td></tr>'
        . '</table>'
        . '</td></tr>'

        . '<tr><td style="padding:8px 32px 28px;border-top:1px solid #d1d5db;">'
        . '<p style="margin:16px 0 0;color:#4b5563;font-size:13px;">Nous vous contacterons seulement s\'il y a un problème avec votre commande. Sinon, on vous attend à la date et l\'heure choisies. Une question ? Répondez à ce courriel ou appelez-nous au ' . esc_html($tel_site) . '.</p>'
        . '</td></tr>'

        . '</table></td></tr></table></body></html>';

    $headers_client = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Le Vivier <' . $dest . '>',
        'Reply-To: ' . $dest,
    ];
    wp_mail($email, 'Votre commande de boîtes à lunch, Le Vivier', $msg_client, $headers_client);

    if ($envoye) {
        wp_send_json_success(['message' => 'Votre commande a bien été envoyée ! Nous vous contacterons si nécessaire. À bientôt au Vivier.']);
    } else {
        wp_send_json_error(['message' => 'Une erreur est survenue lors de l\'envoi. Veuillez nous appeler au ' . $tel_site . '.']);
    }
}
