<?php
function convert_admin_time_to_utc($datetime_local_str) {
    if (!$datetime_local_str) return null;
    try {
        $app_tz = getenv('APP_TIMEZONE') ?: 'Europe/Paris';
        $dt = new DateTime($datetime_local_str, new DateTimeZone($app_tz));
        $dt->setTimezone(new DateTimeZone('UTC'));
        return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return null;
    }
}

function convert_utc_to_admin_time($utc_str, $format = 'Y-m-d\TH:i') {
    if (!$utc_str) return '';
    try {
        $app_tz = getenv('APP_TIMEZONE') ?: 'Europe/Paris';
        $dt = new DateTime($utc_str, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone($app_tz));
        return $dt->format($format);
    } catch (Exception $e) {
        return '';
    }
}
