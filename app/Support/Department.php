<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class Department
{
    public const OPTIONS = [
        'EEE'     => 'EEE',
        'IT'      => 'IT',
        'FINANCE' => 'FINANCE',
    ];

    public static function normalize(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $key = strtolower(trim($value));

        $map = [
            'electronics'          => 'EEE',
            'telecommunication'    => 'EEE',
            'electrical'           => 'EEE',
            'eee'                  => 'EEE',
            'computer science'     => 'IT',
            'information management' => 'IT',
            'software dev'         => 'IT',
            'it'                   => 'IT',
            'accounting'           => 'FINANCE',
            'bussiness'            => 'FINANCE',
            'business'             => 'FINANCE',
            'procurement'          => 'FINANCE',
            'finance'              => 'FINANCE',
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        $upper = strtoupper($value);

        return array_key_exists($upper, self::OPTIONS) ? $upper : $upper;
    }

    public static function label(?string $value): string
    {
        $code = self::normalize($value);

        return $code ? (self::OPTIONS[$code] ?? $code) : 'Unknown';
    }

    /** @return array<int, string> */
    public static function validationRules(bool $required = true): array
    {
        $rules = [Rule::in(array_keys(self::OPTIONS))];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }
}
