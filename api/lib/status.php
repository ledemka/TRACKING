<?php
const STATUS_SHIPPED = 'shipped';
const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
const STATUS_DELIVERED = 'delivered';
const STATUS_DELAYED = 'delayed';

function get_status_labels() {
    return [
        STATUS_SHIPPED => function_exists('__') ? __('status.shipped') : 'Expédié',
        STATUS_OUT_FOR_DELIVERY => function_exists('__') ? __('status.out_for_delivery') : 'En cours de livraison',
        STATUS_DELIVERED => function_exists('__') ? __('status.delivered') : 'Livré',
        STATUS_DELAYED => function_exists('__') ? __('status.delayed') : 'Retardé',
    ];
}

function is_valid_status($status) {
    return in_array($status, [STATUS_SHIPPED, STATUS_OUT_FOR_DELIVERY, STATUS_DELIVERED, STATUS_DELAYED], true);
}

// Modes de transport supportés
const TRANSPORT_MODE_LAND = 'land';
const TRANSPORT_MODE_AIR  = 'air';
const TRANSPORT_MODE_SEA  = 'sea';

/**
 * Retourne la configuration complète des modes de transport (libellé, icône, description).
 * Localisé via le système centralisé d'i18n.
 */
function get_transport_modes() {
    return [
        TRANSPORT_MODE_LAND => [
            'key'         => TRANSPORT_MODE_LAND,
            'label'       => function_exists('__') ? __('transport.land') : 'Terrestre',
            'label_full'  => function_exists('__') ? __('transport.land_full') : 'Transport terrestre',
            'icon'        => 'truck',
            'description' => function_exists('__') ? __('transport.land_desc') : 'Acheminement routier et ferroviaire',
        ],
        TRANSPORT_MODE_AIR => [
            'key'         => TRANSPORT_MODE_AIR,
            'label'       => function_exists('__') ? __('transport.air') : 'Aérien',
            'label_full'  => function_exists('__') ? __('transport.air_full') : 'Transport aérien',
            'icon'        => 'plane',
            'description' => function_exists('__') ? __('transport.air_desc') : 'Fret et acheminement par voie aérienne',
        ],
        TRANSPORT_MODE_SEA => [
            'key'         => TRANSPORT_MODE_SEA,
            'label'       => function_exists('__') ? __('transport.sea') : 'Maritime',
            'label_full'  => function_exists('__') ? __('transport.sea_full') : 'Transport maritime',
            'icon'        => 'ship',
            'description' => function_exists('__') ? __('transport.sea_desc') : 'Fret et acheminement maritime',
        ],
    ];
}

/**
 * Retourne la liste simple des libellés des modes de transport.
 */
function get_transport_mode_labels() {
    $labels = [];
    foreach (get_transport_modes() as $key => $config) {
        $labels[$key] = $config['label'];
    }
    return $labels;
}

/**
 * Normalise un mode de transport avec tolérance pour les alias historiques (road, maritime, etc.).
 */
function normalize_transport_mode($mode): string {
    $clean = strtolower(trim((string)$mode));
    $map = [
        'road' => TRANSPORT_MODE_LAND,
        'truck' => TRANSPORT_MODE_LAND,
        'terrestre' => TRANSPORT_MODE_LAND,
        'plane' => TRANSPORT_MODE_AIR,
        'flight' => TRANSPORT_MODE_AIR,
        'aerien' => TRANSPORT_MODE_AIR,
        'maritime' => TRANSPORT_MODE_SEA,
        'ship' => TRANSPORT_MODE_SEA,
        'boat' => TRANSPORT_MODE_SEA,
    ];
    if (isset($map[$clean])) {
        return $map[$clean];
    }
    if (in_array($clean, [TRANSPORT_MODE_LAND, TRANSPORT_MODE_AIR, TRANSPORT_MODE_SEA], true)) {
        return $clean;
    }
    return TRANSPORT_MODE_LAND;
}

/**
 * Valide si un mode de transport donné est autorisé.
 */
function is_valid_transport_mode($mode) {
    return is_string($mode) && in_array(normalize_transport_mode($mode), [TRANSPORT_MODE_LAND, TRANSPORT_MODE_AIR, TRANSPORT_MODE_SEA], true);
}

/**
 * Retourne le libellé utilisateur d'un mode de transport avec repli propre sur Terrestre.
 * Peut être traduit dans une langue cible spécifique (ex: pour e-mails de notification).
 */
function get_transport_mode_label($mode, ?string $lang = null): string {
    $normalized = normalize_transport_mode($mode);
    if (function_exists('__')) {
        return __("transport.{$normalized}", [], $lang);
    }
    $modes = get_transport_modes();
    return $modes[$normalized]['label'] ?? 'Terrestre';
}

/**
 * Retourne le libellé d'un statut dans une langue donnée.
 */
function get_status_label(string $status, ?string $lang = null): string {
    if (function_exists('__')) {
        return __("status.{$status}", [], $lang);
    }
    $labels = [
        STATUS_SHIPPED => 'Expédié',
        STATUS_OUT_FOR_DELIVERY => 'En cours de livraison',
        STATUS_DELIVERED => 'Livré',
        STATUS_DELAYED => 'Retardé',
    ];
    return $labels[$status] ?? $status;
}
