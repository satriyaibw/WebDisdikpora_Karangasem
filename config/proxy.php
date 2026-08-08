<?php

// Daftar proxy tepercaya (CIDR, dipisah koma). Default subnet jaringan Docker
// compose ("disdikpora-network") — pastikan sinkron dengan ipam di
// docker-compose.yml dan TRUSTED_PROXIES di .env.
return [
    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', '172.18.0.0/16')),
    ))),
];
