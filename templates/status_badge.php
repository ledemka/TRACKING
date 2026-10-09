<?php
function render_status_badge($status) {
    $labels = get_status_labels();
    $label = escape_html($labels[$status] ?? $status);
    
    $classes = '';
    if ($status === STATUS_SHIPPED) {
        $classes = 'bg-status-shipped-bg text-status-shipped-text border border-status-shipped-border';
    } elseif ($status === STATUS_OUT_FOR_DELIVERY) {
        $classes = 'bg-status-delivery-bg text-status-delivery-text border border-status-delivery-border';
    } elseif ($status === STATUS_DELIVERED) {
        $classes = 'bg-status-delivered-bg text-status-delivered-text border border-status-delivered-border';
    } elseif ($status === STATUS_DELAYED) {
        $classes = 'bg-status-delayed-bg text-status-delayed-text border border-status-delayed-border';
    } else {
        $classes = 'bg-slate-100 text-slate-700 border border-slate-300';
    }
    
    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ' . $classes . '">' . $label . '</span>';
}
