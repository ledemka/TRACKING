<?php
function validate_coordinates($lat, $lng, &$errors, $errorKey = 'coordinates') {
    $lat = trim($lat);
    $lng = trim($lng);
    
    if (($lat !== '' && $lng === '') || ($lat === '' && $lng !== '')) {
        $errors[$errorKey] = 'Les deux coordonnées doivent être fournies ou aucune.';
        return [null, null];
    }
    
    if ($lat !== '' && $lng !== '') {
        if (!is_numeric($lat) || $lat < -90 || $lat > 90) {
            $errors[$errorKey] = 'Latitude invalide (-90 à 90).';
        }
        if (!is_numeric($lng) || $lng < -180 || $lng > 180) {
            $errors[$errorKey] = 'Longitude invalide (-180 à 180).';
        }
        return [round((float)$lat, 6), round((float)$lng, 6)];
    }
    return [null, null];
}

function validate_required_string($field, $name, $maxLength, &$errors) {
    $val = trim($field);
    if ($val === '') {
        $errors[$name] = 'Ce champ est obligatoire.';
    } elseif (strlen($val) > $maxLength) {
        $errors[$name] = "Ce champ ne doit pas dépasser $maxLength caractères.";
    }
    return $val;
}

function validate_email($email, &$errors) {
    $val = trim($email);
    if ($val === '') {
        $errors['recipient_email'] = 'L\'adresse e-mail est obligatoire.';
    } elseif (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
        $errors['recipient_email'] = 'L\'adresse e-mail n\'est pas valide.';
    }
    return $val;
}

function validate_weight($weight, &$errors) {
    $val = trim($weight);
    if ($val === '') return null;
    if (!is_numeric($val) || $val <= 0 || $val > 10000) {
        $errors['weight'] = 'Le poids doit être un nombre positif (max 10000).';
    }
    return (float)$val;
}

function validate_packages_count($count, &$errors) {
    $val = trim($count);
    if ($val === '') return 1;
    if (!ctype_digit((string)$val) || $val < 1 || $val > 1000) {
        $errors['packages_count'] = 'Le nombre de colis doit être un entier entre 1 et 1000.';
    }
    return (int)$val;
}
