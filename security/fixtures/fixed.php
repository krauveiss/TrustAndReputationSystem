<?php

namespace Security\Fixtures;

class FixedExample
{
    public function deserialize(string $payload): array
    {
        $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    public function run(string $action): string
    {
        $allowed = [
            'status' => 'php artisan about',
        ];

        if (!array_key_exists($action, $allowed)) {
            return '';
        }

        return '';
    }
}
