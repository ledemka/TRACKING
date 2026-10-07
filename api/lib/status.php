<?php
const STATUS_SHIPPED = 'shipped';
const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
const STATUS_DELIVERED = 'delivered';

function get_status_labels() {
    return [
        STATUS_SHIPPED => 'Expédié',
        STATUS_OUT_FOR_DELIVERY => 'En cours de livraison',
        STATUS_DELIVERED => 'Livré',
    ];
}

function is_valid_status($status) {
    return array_key_exists($status, get_status_labels());
}

function is_delayed($status, $estimated) {
    if ($status === STATUS_DELIVERED) return false;
    if (!$estimated) return false;
    return strtotime($estimated) < time();
}
