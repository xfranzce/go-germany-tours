<?php

namespace App\Services\Ghl;

use App\Models\QuizStep;
use Illuminate\Support\Str;

/**
 * SINGLE SOURCE OF TRUTH for GoHighLevel custom field names.
 * Fields are matched in GHL by EXACT NAME. If you rename a field in GHL,
 * change it here in the same commit (and vice versa). Never rename on one side only.
 */
class GhlFields
{
    public const TEXT = 'TEXT';

    public const LARGE = 'LARGE_TEXT';

    // Fixed fields
    public const TRAVEL_DATE = 'Preferred Travel Date';
    public const SECOND_QUOTE = 'Second Quote Preference';
    public const SPECIAL_REQUEST = 'Special Request';
    public const PACKAGE = 'Package';
    public const FORM_NAME = 'Form Name';
    public const QUIZ_FULL = 'Quiz Answers (Full)';
    public const CONSENT_GIVEN = 'Consent Given';
    public const CONSENT_AT = 'Consent Timestamp';
    public const CONSENT_VERSION = 'Consent Text Version';
    public const TIME_OF_YEAR = 'Time of Year';
    public const GROUP_SIZE = 'Group Size';
    public const NIGHTS = 'Nights';

    /** tracking key => label stem. Each exists twice: "<stem> First" and "<stem> Last". */
    public const TRACKING = [
        'utm_source' => 'UTM Source', 'utm_medium' => 'UTM Medium', 'utm_campaign' => 'UTM Campaign',
        'utm_term' => 'UTM Term', 'utm_content' => 'UTM Content', 'gclid' => 'GCLID', 'gbraid' => 'GBRAID',
        'wbraid' => 'WBRAID', 'referrer' => 'Referrer', 'landing_page' => 'Landing Page URL',
    ];

    /**
     * Plain snake_case fields shared with the other Go* sites' GHL setup. They hold the LATEST touch
     * (what brought the visitor back to enquire) plus the form the enquiry came from.
     * utm_device / utm_adcreative / utm_adposition come from Google Ads {device} / {creative} / {adposition}.
     */
    public const CLASSIC = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
        'utm_device', 'utm_gclid', 'utm_adcreative', 'utm_adposition', 'form_source',
    ];

    public static function tracking(string $key, string $touch): string
    {
        return self::TRACKING[$key].' '.ucfirst($touch); // "UTM Source First"
    }

    public static function quiz(string $stepKey): string
    {
        return 'Quiz: '.Str::headline($stepKey);
    }

    /** @return array<string, string> field name => GHL data type */
    public static function all(): array
    {
        $fields = [
            self::TRAVEL_DATE => self::TEXT, self::SECOND_QUOTE => self::TEXT, self::SPECIAL_REQUEST => self::LARGE,
            self::PACKAGE => self::TEXT, self::FORM_NAME => self::TEXT, self::QUIZ_FULL => self::LARGE,
            self::CONSENT_GIVEN => self::TEXT, self::CONSENT_AT => self::TEXT, self::CONSENT_VERSION => self::TEXT,
            self::TIME_OF_YEAR => self::TEXT, self::GROUP_SIZE => self::TEXT, self::NIGHTS => self::TEXT,
        ];
        foreach (self::CLASSIC as $name) {
            $fields[$name] = self::TEXT;
        }
        foreach (array_keys(self::TRACKING) as $key) {
            foreach (['first', 'last'] as $touch) {
                $fields[self::tracking($key, $touch)] = in_array($key, ['referrer', 'landing_page']) ? self::LARGE : self::TEXT;
            }
        }
        try {
            foreach (QuizStep::pluck('key') as $key) {
                $fields[self::quiz($key)] = self::TEXT;
            }
        } catch (\Throwable) {
            // database unavailable: quiz fields skipped
        }

        return $fields;
    }
}
