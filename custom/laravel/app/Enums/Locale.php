<?php

namespace App\Enums;

/**
 * Locale enum matching Rails LANGUAGES_CONFIG
 * Maps locale codes to integer values for database storage
 * Based on config/initializers/languages.rb
 */
enum Locale: int
{
    case EN = 0;    // English
    case AR = 1;    // العربية
    case NL = 2;    // Nederlands
    case FR = 3;    // Français
    case DE = 4;    // Deutsch
    case HI = 5;    // हिन्दी
    case IT = 6;    // Italiano
    case JA = 7;    // 日本語
    case KO = 8;    // 한국어
    case PT = 9;    // Português
    case RU = 10;   // русский
    case ZH = 11;   // 中文
    case ES = 12;   // Español
    case ML = 13;   // മലയാളം
    case CA = 14;   // Català
    case EL = 15;   // ελληνικά
    case PT_BR = 16; // Português Brasileiro
    case RO = 17;   // Română
    case TA = 18;   // தமிழ்
    case FA = 19;   // فارسی
    case ZH_TW = 20; // 中文 (台湾)
    case VI = 21;   // Tiếng Việt
    case DA = 22;   // dansk
    case TR = 23;   // Türkçe
    case CS = 24;   // čeština
    case FI = 25;   // suomi
    case ID = 26;   // Bahasa Indonesia
    case SV = 27;   // Svenska
    case HU = 28;   // magyar
    case NO = 29;   // norsk
    case ZH_CN = 30; // 中文
    case PL = 31;   // polski
    case SK = 32;   // slovenčina
    case UK = 33;   // українська
    case TH = 34;   // ภาษาไทย
    case LV = 35;   // latviešu
    case IS = 36;   // íslenska
    case HE = 37;   // עִברִית
    case LT = 38;   // lietuvių
    case SR = 39;   // Српски
    case BG = 40;   // български

    /**
     * Get the locale code as a string (e.g., 'en', 'ar')
     */
    public function getCode(): string
    {
        return match($this) {
            self::EN => 'en',
            self::AR => 'ar',
            self::NL => 'nl',
            self::FR => 'fr',
            self::DE => 'de',
            self::HI => 'hi',
            self::IT => 'it',
            self::JA => 'ja',
            self::KO => 'ko',
            self::PT => 'pt',
            self::RU => 'ru',
            self::ZH => 'zh',
            self::ES => 'es',
            self::ML => 'ml',
            self::CA => 'ca',
            self::EL => 'el',
            self::PT_BR => 'pt_BR',
            self::RO => 'ro',
            self::TA => 'ta',
            self::FA => 'fa',
            self::ZH_TW => 'zh_TW',
            self::VI => 'vi',
            self::DA => 'da',
            self::TR => 'tr',
            self::CS => 'cs',
            self::FI => 'fi',
            self::ID => 'id',
            self::SV => 'sv',
            self::HU => 'hu',
            self::NO => 'no',
            self::ZH_CN => 'zh_CN',
            self::PL => 'pl',
            self::SK => 'sk',
            self::UK => 'uk',
            self::TH => 'th',
            self::LV => 'lv',
            self::IS => 'is',
            self::HE => 'he',
            self::LT => 'lt',
            self::SR => 'sr',
            self::BG => 'bg',
        };
    }

    /**
     * Get locale from code string (e.g., 'en' => Locale::EN)
     */
    public static function fromCode(string $code): self
    {
        return match(strtolower($code)) {
            'en' => self::EN,
            'ar' => self::AR,
            'nl' => self::NL,
            'fr' => self::FR,
            'de' => self::DE,
            'hi' => self::HI,
            'it' => self::IT,
            'ja' => self::JA,
            'ko' => self::KO,
            'pt' => self::PT,
            'ru' => self::RU,
            'zh' => self::ZH,
            'es' => self::ES,
            'ml' => self::ML,
            'ca' => self::CA,
            'el' => self::EL,
            'pt_br' => self::PT_BR,
            'ro' => self::RO,
            'ta' => self::TA,
            'fa' => self::FA,
            'zh_tw' => self::ZH_TW,
            'vi' => self::VI,
            'da' => self::DA,
            'tr' => self::TR,
            'cs' => self::CS,
            'fi' => self::FI,
            'id' => self::ID,
            'sv' => self::SV,
            'hu' => self::HU,
            'no' => self::NO,
            'zh_cn' => self::ZH_CN,
            'pl' => self::PL,
            'sk' => self::SK,
            'uk' => self::UK,
            'th' => self::TH,
            'lv' => self::LV,
            'is' => self::IS,
            'he' => self::HE,
            'lt' => self::LT,
            'sr' => self::SR,
            'bg' => self::BG,
            default => throw new \InvalidArgumentException("Invalid locale code: $code"),
        };
    }

    /**
     * Get all available locale codes
     */
    public static function getCodes(): array
    {
        return array_map(fn($locale) => $locale->getCode(), self::cases());
    }

    /**
     * Get all locale values (integers)
     */
    public static function getValues(): array
    {
        return array_map(fn($locale) => $locale->value, self::cases());
    }
}
