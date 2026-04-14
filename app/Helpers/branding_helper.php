<?php

if (!function_exists('get_company_logo')) {
    function get_company_logo()
    {
        $db = \Config\Database::connect();
        $owner = $db->table('users')->where('role', 'owner')->get()->getRowArray();

        if ($owner && !empty($owner['company_logo'])) {
            return base_url($owner['company_logo']);
        }

        return base_url('assets/img/logo_.png');
    }
}

if (!function_exists('get_company_name')) {
    function get_company_name()
    {
        $db = \Config\Database::connect();
        $owner = $db->table('users')->where('role', 'owner')->get()->getRowArray();
        if ($owner && !empty($owner['company_name'])) {
            return $owner['company_name'];
        }
        return 'Aliston Tour & Travel';
    }
}

if (!function_exists('get_company_favicon_url')) {
    /**
     * URL ikon tab browser; sama dengan logo perusahaan di pengaturan akun.
     */
    function get_company_favicon_url()
    {
        return get_company_logo();
    }
}

if (!function_exists('get_company_favicon_type')) {
    function get_company_favicon_type()
    {
        $href = get_company_favicon_url();
        $path = parse_url($href, PHP_URL_PATH) ?: $href;
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'svg':
                return 'image/svg+xml';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'webp':
                return 'image/webp';
            case 'gif':
                return 'image/gif';
            case 'ico':
                return 'image/x-icon';
            default:
                return 'image/png';
        }
    }
}
