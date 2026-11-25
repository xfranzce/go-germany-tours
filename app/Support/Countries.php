<?php

namespace App\Support;

/** Dial-code list for the phone country select. Key = ISO code, value = [name, dial code]. */
class Countries
{
    public static function all(): array
    {
        return [
            'US' => ['United States', '+1'], 'CA' => ['Canada', '+1'], 'GB' => ['United Kingdom', '+44'],
            'IE' => ['Ireland', '+353'], 'AU' => ['Australia', '+61'], 'NZ' => ['New Zealand', '+64'],
            'DE' => ['Germany', '+49'], 'AT' => ['Austria', '+43'], 'CH' => ['Switzerland', '+41'],
            'FR' => ['France', '+33'], 'ES' => ['Spain', '+34'], 'IT' => ['Italy', '+39'], 'PT' => ['Portugal', '+351'],
            'NL' => ['Netherlands', '+31'], 'BE' => ['Belgium', '+32'], 'LU' => ['Luxembourg', '+352'],
            'DK' => ['Denmark', '+45'], 'SE' => ['Sweden', '+46'], 'NO' => ['Norway', '+47'], 'FI' => ['Finland', '+358'],
            'PL' => ['Poland', '+48'], 'CZ' => ['Czechia', '+420'], 'GR' => ['Greece', '+30'],
            'MX' => ['Mexico', '+52'], 'BR' => ['Brazil', '+55'], 'AR' => ['Argentina', '+54'], 'CL' => ['Chile', '+56'],
            'CO' => ['Colombia', '+57'], 'PR' => ['Puerto Rico', '+1'],
            'ZA' => ['South Africa', '+27'], 'AE' => ['United Arab Emirates', '+971'], 'IL' => ['Israel', '+972'],
            'IN' => ['India', '+91'], 'SG' => ['Singapore', '+65'], 'HK' => ['Hong Kong', '+852'],
            'JP' => ['Japan', '+81'], 'KR' => ['South Korea', '+82'], 'PH' => ['Philippines', '+63'], 'TH' => ['Thailand', '+66'],
        ];
    }

    public static function dial(string $iso): ?string
    {
        return self::all()[$iso][1] ?? null;
    }

    /**
     * Allowed length of the national number (digits after the dial code, without a leading trunk 0), per country.
     * Countries with very variable lengths get a wide range. Used by the server AND by the form script (see enquiry-form).
     */
    public const LENGTHS = [
        'US' => [10, 10], 'CA' => [10, 10], 'PR' => [10, 10], 'GB' => [9, 10], 'IE' => [7, 9], 'AU' => [9, 9], 'NZ' => [8, 10],
        'DE' => [6, 13], 'AT' => [4, 13], 'CH' => [9, 9], 'FR' => [9, 9], 'ES' => [9, 9], 'IT' => [6, 11], 'PT' => [9, 9],
        'NL' => [9, 9], 'BE' => [8, 9], 'LU' => [4, 11], 'DK' => [8, 8], 'SE' => [7, 10], 'NO' => [8, 8], 'FI' => [5, 12],
        'PL' => [9, 9], 'CZ' => [9, 9], 'GR' => [10, 10], 'MX' => [10, 10], 'BR' => [10, 11], 'AR' => [10, 11], 'CL' => [9, 9],
        'CO' => [10, 10], 'ZA' => [9, 9], 'AE' => [8, 9], 'IL' => [8, 9], 'IN' => [10, 10], 'SG' => [8, 8], 'HK' => [8, 8],
        'JP' => [9, 10], 'KR' => [8, 10], 'PH' => [10, 10], 'TH' => [8, 9],
    ];

    /** Countries whose numbers keep their leading 0 after the dial code (Italian landlines). */
    private const KEEP_ZERO = ['IT'];

    /** The national digits the visitor typed: the dial code (if they typed or kept it) and a trunk 0 removed. */
    public static function national(string $iso, string $phone): string
    {
        $dialDigits = ltrim((string) self::dial($iso), '+');
        $digits = preg_replace('/\D+/', '', $phone);
        // The form pre-fills the dial code ("+1 "), and people also type the full international number: don't count it twice.
        if (str_starts_with(trim($phone), '+') && $dialDigits !== '' && str_starts_with($digits, $dialDigits)) {
            $digits = substr($digits, strlen($dialDigits));
        }

        return in_array($iso, self::KEEP_ZERO, true) ? $digits : ltrim($digits, '0');
    }

    /** null when the number fits the country, otherwise the error message to show. */
    public static function problem(string $iso, string $phone): ?string
    {
        $dial = self::dial($iso);
        if (! $dial) {
            return 'Please choose a country code.';
        }
        $n = strlen(self::national($iso, $phone));
        [$min, $max] = self::LENGTHS[$iso] ?? [4, 14];
        $expect = $min === $max ? "{$min} digits" : "{$min} to {$max} digits";
        if ($n === 0) {
            return 'Please enter your phone number.';
        }
        if ($n < $min) {
            return "This number is too short. Numbers in ".self::all()[$iso][0]." have {$expect} after {$dial}.";
        }
        if ($n > $max) {
            return "This number is too long. Numbers in ".self::all()[$iso][0]." have {$expect} after {$dial}.";
        }

        return null;
    }

    /** Build an E.164 number, or null when it does not fit the country's length. */
    public static function e164(string $iso, string $national): ?string
    {
        if (self::problem($iso, $national) !== null) {
            return null;
        }

        return self::dial($iso).self::national($iso, $national);
    }

    /** iso => [dial, min, max] for the form script. */
    public static function forScript(): array
    {
        return collect(self::all())->map(fn ($c, $iso) => [$c[1], ...(self::LENGTHS[$iso] ?? [4, 14])])->all();
    }
}
