<?php

if (!function_exists('phone')) {
    /** @return Febalist\Phone\Phone */
    function phone($number, $default_country = null)
    {
        return new Febalist\Phone\Phone($number, $default_country);
    }
}

if (!function_exists('phone_parse')) {
    /** @return Febalist\Phone\Phone */
    function phone_parse($number, $exception = false)
    {
        $phone = phone($number);
        return $phone->parse($exception);
    }
}

if (!function_exists('phone_pretty')) {
    /** @return string */
    function phone_pretty($number, $international = false)
    {
        return phone($number)->pretty($international);
    }
}

if (!function_exists('phone_e164')) {
    /** @return string */
    function phone_e164($number, $parse = false, $exception = false)
    {
        $phone = phone($number);
        if ($parse) {
            $phone = $phone->parse($exception);
        }
        if ($phone) {
            return $phone->e164();
        }
        return '';
    }
}
