<?php


/**
 * Content negotiation for language codes
 *
 * @param string $httplang HTTP Accept-Language header
 *
 * @return string language code for best language match
 */

function client_prefered_language($httplang)
{
    $client_langs = array();

    $all_languages = common_config('site', 'langs');

    preg_match_all('"(((\S\S)-?(\S\S)?)(;q=([0-9.]+))?)\s*(,\s*|$)"',
    strtolower($httplang), $httplang);

    for ($i = 0; $i < count($httplang); $i++) {
        if (!empty($httplang[2][$i])) {
            // if no q default to 1.0
            $client_langs[$httplang[2][$i]] =
            ($httplang[6][$i]? (float) $httplang[6][$i] : 1.0 - ($i*0.01));
        }
        if (!empty($httplang[3][$i]) && empty($client_langs[$httplang[3][$i]])) {
            // if a catchall default 0.01 lower
            $client_langs[$httplang[3][$i]] =
            ($httplang[6][$i]? (float) $httplang[6][$i]-0.01 : 0.99);
        }
    }
    // sort in decending q
    arsort($client_langs);

    foreach ($client_langs as $lang => $q) {
        if (isset($all_languages[$lang])) {
            return($lang);
        }
    }

    return false;
}