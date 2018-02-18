<?php namespace Febalist\Phone;

use Illuminate\Foundation\Application;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

/**
 * @property-read string  $international
 * @property-read string  $national
 * @property-read string  $short
 * @property-read string  $e164
 * @property-read string  $rfc3966
 * @property-read string  $country
 * @property-read string  $region
 * @property-read boolean $local
 * @property-read boolean $mobile
 */
class Phone
{
    /** @var string */
    public $number;
    /** @var string */
    public $locale;
    /** @var string */
    public $default_country;
    /** @var bool */
    public $valid;

    protected $parsed;

    public function __construct($number, $locale = null, $default_country = null)
    {
        if ($number instanceof static) {
            $number = $number->number;
            $locale = $locale ?: $number->locale;
            $default_country = $default_country ?: $number->country;
        }

        $this->number = (string) $number;
        $this->locale = $locale ?: static::detectLocale();
        $this->default_country = static::parseCountry($default_country, $this->locale);

        $this->parsed = static::parseNumber($this->number, $this->default_country);
        $this->valid = $this->parsed ? static::util()->isValidNumber($this->parsed) : false;
    }

    protected static function util()
    {
        return PhoneNumberUtil::getInstance();
    }

    protected static function parseNumber($number, $country = null)
    {
        try {
            return static::util()->parse($number, $country);
        } catch (NumberParseException $exception) {
            return null;
        }
    }

    protected static function parseCountry($country = null, $locale = null)
    {
        if (!$country && $locale) {
            $country = explode('_', $locale)[0];
        }
        return $country ? strtoupper($country) : null;
    }

    protected static function detectLocale()
    {
        if (class_exists(Application::class)) {
            return app()->getLocale();
        }
        return null;
    }

    /** @return $this */
    public function validate($exception = false)
    {
        if (!$this->valid) {
            if ($exception) {
                throw new NumberParseException(
                    NumberParseException::NOT_A_NUMBER,
                    'Invalid phone number.'
                );
            }
            $this->parsed = null;
        }
        return $this;
    }

    public function __toString()
    {
        return $this->number;
    }

    public function __get($name)
    {
        if (!$this->parsed) {
            return;
        } elseif ($name == 'international') {
            return $this->format(PhoneNumberFormat::INTERNATIONAL);
        } elseif ($name == 'national') {
            return $this->format(PhoneNumberFormat::NATIONAL);
        } elseif ($name == 'e164') {
            return $this->format(PhoneNumberFormat::E164);
        } elseif ($name == 'rfc3966') {
            return $this->format(PhoneNumberFormat::RFC3966);
        } elseif ($name == 'short') {
            return str_replace('+', '', $this->e164);
        } elseif ($name == 'country') {
            return static::util()->getRegionCodeForNumber($this->parsed);
        } elseif ($name == 'region') {
            return PhoneNumberOfflineGeocoder::getInstance()
                ->getDescriptionForNumber($this->parsed, $this->locale, $this->country);
        } elseif ($name == 'local') {
            return $this->default_country && $this->default_country == $this->country;
        } elseif ($name == 'mobile') {
            return static::util()->getNumberType($this->parsed) == PhoneNumberType::MOBILE;
        }
    }

    protected function format($format)
    {
        return static::util()->format($this->parsed, $format);
    }
}
