<?php

if (!function_exists('phone')) {
    /** @return Febalist\Phone\Phone */
    function phone($number, $locale = null, $country = null)
    {
        return new Febalist\Phone\Phone($number, $locale, $country);
    }
}

if (!function_exists('phone_parse')) {
    /** @return string */
    function phone_parse($number, $exception = false, $locale = null, $country = null)
    {
        return phone($number, $locale, $country)->validate($exception)->e164;
    }
}

if (!function_exists('phone_pretty')) {
    /** @return string */
    function phone_pretty($number, $international = false, $locale = null, $country = null)
    {
        $phone = phone($number, $locale, $country);

        return ($international ? $phone->international : $phone->national) ?: $number;
    }
}
