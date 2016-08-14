<?php namespace Febalist\Phone;

use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

class Phone
{
    public $number;
    public $default_country;

    public function __construct($number, $default_country = null)
    {
        if (is_object($number) && $number instanceof Phone) {
            $default_country = $number->default_country;
            $number          = $number->number;
        }
        if (!$default_country) {
            $locale = $this->locale(null);
            if ($locale) {
                $default_country = explode('_', $locale)[0];
            }
        }
        $this->number          = (string)$number;
        $this->default_country = strtoupper($default_country);
    }

    protected function locale($default = 'en_US')
    {
        if (function_exists('config')) {
            return config('app.locale');
        }
        return $default;
    }

    /** @return PhoneNumberUtil */
    protected function util()
    {
        return PhoneNumberUtil::getInstance();
    }

    /** @return phoneNumber */
    protected function phoneNumber()
    {
        return $this->util()->parse($this->number, $this->default_country);
    }

    /** @return $this */
    public function parse($exception = false)
    {
        $valid = $this->isValid();
        if ($valid) {
            return $this;
        }
        if ($exception) {
            throw new NumberParseException(
                NumberParseException::NOT_A_NUMBER,
                'Invalid phone number.'
            );
        }
        return null;
    }

    /** @return bool */
    public function isValid()
    {
        try {
            return $this->util()->isValidNumber($this->phoneNumber());
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /** @return string */
    public function e164()
    {
        if (!$this->isValid()) {
            return $this->number;
        }
        return $this->util()->format($this->phoneNumber(), PhoneNumberFormat::E164);
    }

    /** @return string */
    public function pretty($international = false)
    {
        if (!$this->isValid()) {
            return $this->number;
        }
        $international = $international || !$this->isLocal();
        $format        = $international ? PhoneNumberFormat::INTERNATIONAL : PhoneNumberFormat::NATIONAL;
        return $this->util()->format($this->phoneNumber(), $format);
    }

    /** @return string */
    public function country()
    {
        return $this->util()->getRegionCodeForNumber($this->phoneNumber());
    }

    /** @return string */
    public function region($locale = null)
    {
        if (!$locale) {
            $locale = $this->locale();
        }
        $geocoder = PhoneNumberOfflineGeocoder::getInstance();
        return $geocoder->getDescriptionForNumber($this->phoneNumber(), $locale, $this->default_country);
    }

    /** @return bool */
    public function isLocal()
    {
        if (!$this->default_country) {
            return null;
        }
        return $this->country() == $this->default_country;
    }

    /** @return bool */
    public function isMobile()
    {
        return $this->util()->getNumberType($this->phoneNumber()) == PhoneNumberType::MOBILE;
    }

}
