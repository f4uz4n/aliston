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
