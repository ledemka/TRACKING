<?php
// api/lib/validation.php

function validate_coordinates($lat, $lng, &$errors, $errorKey = 'coordinates') {
    $lat = trim($lat);
    $lng = trim($lng);
    
    if (($lat !== '' && $lng === '') || ($lat === '' && $lng !== '')) {
        $errors[$errorKey] = function_exists('__') ? __('validation.coords_both') : 'Les deux coordonnées doivent être fournies ou aucune.';
        return [null, null];
    }
    
    if ($lat !== '' && $lng !== '') {
        if (!is_numeric($lat) || $lat < -90 || $lat > 90) {
            $errors[$errorKey] = function_exists('__') ? __('validation.lat_invalid') : 'Latitude invalide (-90 à 90).';
        }
        if (!is_numeric($lng) || $lng < -180 || $lng > 180) {
            $errors[$errorKey] = function_exists('__') ? __('validation.lng_invalid') : 'Longitude invalide (-180 à 180).';
        }
        return [round((float)$lat, 6), round((float)$lng, 6)];
    }
    return [null, null];
}

function validate_required_string($field, $name, $maxLength, &$errors) {
    $val = trim($field);
    if ($val === '') {
        $errors[$name] = function_exists('__') ? __('validation.required') : 'Ce champ est obligatoire.';
    } elseif (strlen($val) > $maxLength) {
        $errors[$name] = function_exists('__') ? __('validation.max_length', ['max' => $maxLength]) : "Ce champ ne doit pas dépasser $maxLength caractères.";
    }
    return $val;
}

function validate_email($email, &$errors) {
    $val = trim($email);
    if ($val === '') {
        $errors['recipient_email'] = function_exists('__') ? __('validation.email_required') : 'L\'adresse e-mail est obligatoire.';
    } elseif (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
        $errors['recipient_email'] = function_exists('__') ? __('validation.email_invalid') : 'L\'adresse e-mail n\'est pas valide.';
    }
    return $val;
}

function validate_weight($weight, &$errors) {
    $val = trim($weight);
    if ($val === '') return null;
    if (!is_numeric($val) || $val <= 0 || $val > 10000) {
        $errors['weight'] = function_exists('__') ? __('validation.weight_positive') : 'Le poids doit être un nombre positif (max 10000).';
    }
    return (float)$val;
}

function validate_packages_count($count, &$errors) {
    $val = trim($count);
    if ($val === '') return 1;
    if (!ctype_digit((string)$val) || $val < 1 || $val > 1000) {
        $errors['packages_count'] = function_exists('__') ? __('validation.packages_between') : 'Le nombre de colis doit être un entier entre 1 et 1000.';
    }
    return (int)$val;
}
