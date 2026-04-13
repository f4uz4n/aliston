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
