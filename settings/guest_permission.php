<?php
/* ----------------------------------
   Guest Student Permission Helper
-----------------------------------*/

function getGuestSettings($adminData) {
    return $adminData['Panel Settings']['Guest Student'] ?? [];
}

function guestEnabled($settings) {
    return ($settings['panel_active'] ?? 'no') === 'yes';
}

function guestCan($settings, $key) {
    return !empty($settings[$key]);
}

function guestLoginSecurity($settings) {
    return $settings['login_security'] ?? [];
}
