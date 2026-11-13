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

    /** Build an E.164 number, or null when it is not plausible (8-15 digits in total). */
    public static function e164(string $iso, string $national): ?string
    {
        $dial = self::dial($iso);
        if (! $dial) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $national);
        $dialDigits = ltrim($dial, '+');
        // If the user typed the full international number, don't repeat the dial code.
        if (str_starts_with(trim($national), '+') && str_starts_with($digits, $dialDigits)) {
            $digits = substr($digits, strlen($dialDigits));
        }
        $digits = ltrim($digits, '0');
        $total = strlen($dialDigits) + strlen($digits);

        return ($total >= 8 && $total <= 15) ? '+'.$dialDigits.$digits : null;
    }
}
