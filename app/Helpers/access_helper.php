<?php

if (! function_exists('is_owner')) {
    function is_owner(): bool
    {
        return session()->get('role') === 'owner';
    }
}

if (! function_exists('is_office_admin')) {
    function is_office_admin(): bool
    {
        return session()->get('role') === 'office_admin';
    }
}

if (! function_exists('is_back_office')) {
    /** Pemilik tour atau admin kantor (bukan agency). */
    function is_back_office(): bool
    {
        return in_array(session()->get('role'), ['owner', 'office_admin'], true);
    }
}

if (! function_exists('can_view_commission')) {
    /** Hanya pemilik — admin kantor tidak melihat komisi. */
    function can_view_commission(): bool
    {
        return session()->get('role') === 'owner';
    }
}

if (! function_exists('can_manage_agency')) {
    /** Daftar agensi, tambah agensi, admin kantor baru — hanya pemilik. */
    function can_manage_agency(): bool
    {
        return is_owner();
    }
}

if (! function_exists('dashboard_url_for_role')) {
    function dashboard_url_for_role(string $role): string
    {
        if (in_array($role, ['owner', 'office_admin'], true)) {
            return 'owner';
        }

        return 'agency';
    }
}
