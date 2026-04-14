<?php

if (! function_exists('format_nama_jamaah')) {
    /**
     * Normalisasi nama jamaah: huruf besar semua (UTF-8), cocok untuk tampilan & dokumen.
     */
    function format_nama_jamaah(?string $name): string
    {
        if ($name === null) {
            return '';
        }
        $t = trim($name);
        if ($t === '') {
            return '';
        }

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($t, 'UTF-8')
            : strtoupper($t);
    }
}

if (! function_exists('format_nama_jamaah_title')) {
    /**
     * Nama jamaah untuk tampilan daftar: huruf awal tiap kata besar (title case), UTF-8.
     */
    function format_nama_jamaah_title(?string $name): string
    {
        if ($name === null) {
            return '';
        }
        $t = trim($name);
        if ($t === '') {
            return '';
        }
        if (function_exists('mb_convert_case') && function_exists('mb_strtolower')) {
            return mb_convert_case(mb_strtolower($t, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }

        return ucwords(strtolower($t));
    }
}

if (! function_exists('format_participant_name_row')) {
    /**
     * Terapkan format nama jamaah pada satu baris hasil query (mis. getRowArray).
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function format_participant_name_row(array $row): array
    {
        foreach (['name', 'passport_full_name', 'passport_name_idn', 'emergency_name'] as $field) {
            if (isset($row[$field]) && is_string($row[$field]) && $row[$field] !== '') {
                $row[$field] = format_nama_jamaah($row[$field]);
            }
        }

        return $row;
    }
}

if (! function_exists('participant_effective_departure')) {
    /**
     * Tanggal/jam efektif keberangkatan untuk hitungan H- dan syarat boarding.
     * Jika departure_note berisi format datetime-local (Y-m-d\TH:i), nilai itu menggantikan tanggal paket.
     */
    function participant_effective_departure(?string $packageDeparture, ?string $departureNote): ?string
    {
        $note = trim((string) $departureNote);
        if ($note !== '' && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $note)) {
            $normalized = str_replace('T', ' ', substr($note, 0, 16)) . ':00';
            $ts = strtotime($normalized);
            if ($ts !== false) {
                return $normalized;
            }
            // catatan tidak valid: pakai tanggal paket
        }

        $pkg = trim((string) $packageDeparture);

        return $pkg !== '' ? $packageDeparture : null;
    }
}

if (! function_exists('participant_days_until_departure')) {
    /**
     * Selisih hari kalender antara hari ini dan hari keberangkatan efektif (floor).
     */
    function participant_days_until_departure(?string $effectiveDeparture): ?int
    {
        if ($effectiveDeparture === null || trim((string) $effectiveDeparture) === '') {
            return null;
        }
        $ts = strtotime($effectiveDeparture);
        if ($ts === false) {
            return null;
        }
        $today = strtotime(date('Y-m-d'));
        $depDay = strtotime(date('Y-m-d', $ts));

        return (int) floor(($depDay - $today) / 86400);
    }
}
