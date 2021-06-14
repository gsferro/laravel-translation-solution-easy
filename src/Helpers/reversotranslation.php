<?php

if (!function_exists('reversotranslation')) {
    /**
     * Initiate ReversoTranslation hook.
     *
     * @return Gsferro\TranslationSolutionEasy\Services\ReversoTranslation
     */
    function reversotranslation()
    {
        return app('reversotranslation');
    }
}

if (!function_exists('reversoTranslationMessageSuccess')) {
    /**
     * Translate message success
     * @param $lang
     * @return string
     */
    function reversoTranslationMessageSuccess($lang)
    {
        return [
            "pt-br" => "Traduzido com sucesso!",
            "en"    => "Translated successfully!",
            "fr"    => "Traduit avec succès!",
            "es"    => "¡Traducido con gran éxito!",
            "it"    => "Tradotto con successo!",
            "de"    => "Erfolgreich übersetzt!",
            "ru"    => "Перевод выполнен успешно!",
            "pl"    => "Tłumaczenie powiodło się!",
            "nl"    => "Succesvol vertaald!",
            "tr"    => "Başarıyla çevrildi!",
            "ro"    => "Tradus cu succes!",
            "iw"    => "תורגם בהצלחה!",
            "ar"    => "ترجم بنجاح!",
        ][ $lang ] ?? null;
    }
}


if (!function_exists('reversoTranslationLangsConvert')) {
    function reversoTranslationLangsConvert($lang)
    {
        return [
            "pt-br" => "por", // portugues brasil
            "en"    => "eng", // ingles
            "fr"    => "fra", // frances
            "es"    => "spa", // espanhol
            "it"    => "ita", // italiano
            "de"    => "ger", // alemão
            "pl"    => "pol", // polones
            "nl"    => "dut", // holondês
            "tr"    => "tur", // turco
            "ro"    => "rum", // Romeno
            "ar"    => "ara", // árabe
            "ru"    => "rus", // russo
            //"iw"    => "heb", // hebraico
            //"es"    => "chi", // chines
            //"es"    => "jpn", // japonês
        ][ strtolower($lang) ] ?? null;
    }
}