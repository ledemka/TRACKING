<?php
const STATUS_SHIPPED = 'shipped';
const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
const STATUS_DELIVERED = 'delivered';
const STATUS_DELAYED = 'delayed';

function get_status_labels() {
    return [
        STATUS_SHIPPED => 'Expédié',
        STATUS_OUT_FOR_DELIVERY => 'En cours de livraison',
        STATUS_DELIVERED => 'Livré',
        STATUS_DELAYED => 'Retardé',
    ];
}

function is_valid_status($status) {
    return array_key_exists($status, get_status_labels());
}
