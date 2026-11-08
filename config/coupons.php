<?php

/*
|--------------------------------------------------------------------------
| Marketplace Promo Coupons
|--------------------------------------------------------------------------
|
| Daftar kupon promo yang dapat dipakai pada keranjang marketplace.
| Key = kode kupon (huruf besar). Field:
|   - type          : 'percent' (persentase) atau 'fixed' (nominal tetap, Rupiah)
|   - value         : besar diskon (persen atau Rupiah)
|   - min_subtotal  : minimal subtotal keranjang agar kupon berlaku (opsional)
|   - max_discount  : batas maksimal potongan untuk tipe percent (opsional)
|   - expires_at    : tanggal kedaluwarsa 'Y-m-d' (opsional, null = tanpa batas)
|
*/

return [
    'RETIDE10' => [
        'type' => 'percent',
        'value' => 10,
        'min_subtotal' => 0,
        'max_discount' => 50000,
        'expires_at' => null,
    ],
    'ECOHEMAT25' => [
        'type' => 'percent',
        'value' => 25,
        'min_subtotal' => 200000,
        'max_discount' => 100000,
        'expires_at' => null,
    ],
    'HEMAT20K' => [
        'type' => 'fixed',
        'value' => 20000,
        'min_subtotal' => 100000,
        'max_discount' => null,
        'expires_at' => null,
    ],
];
