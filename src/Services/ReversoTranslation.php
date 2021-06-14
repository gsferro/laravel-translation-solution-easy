<?php

namespace Gsferro\TranslationSolutionEasy\Services;

use Exception;

class ReversoTranslation
{
    /** * @var string */
    private $text;
    /** * @var string|null */
    private $langFrom = null;
    /** * @var string|array|null */
    private $langTo = null;
    /** * @var string */
    private $from;
    /** * @var array|string */
    private $to;
    /** @var bool */
    private $simplified;

    /**
     * ReversoTranslation constructor.
     * @param string $langFrom
     * @param string|array $langTo
     * @throws Exception
     */
    public function __construct(string $langFrom, $langTo)
    {
        if (is_null($this->langFrom = reversoTranslationLangsConvert($langFrom))) {
            throw new Exception("your lang [ {$langFrom} ] not support!");
        }

        $lans = [];
        if (is_array($langTo)) {
            foreach ($langTo as $to) {
                if ($langFrom == $to) {
                    continue;
                }

                if (is_null(reversoTranslationLangsConvert($to))) {
                    throw new Exception("langs [ {$to} ] from your translate not support!");
                }

                $lans[] = reversoTranslationLangsConvert($to);
            }
        }

        $this->langTo = count($lans) > 0 ? $lans : reversoTranslationLangsConvert($langTo);
        $this->from   = $langFrom;
        $this->to     = $langTo;

        return $this;
    }

    /**
     * @param string $text
     * @param bool $simplified
     * @return array|string
     */
    public function translate(string $text, bool $simplified = true)
    {
        $this->simplified = $simplified;
        try {
            $this->setText($text);

            if (is_array($this->to)) {
                return $this->multiTrans();
            }

            return $this->success($this->api($this->langTo));
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @param string $input
     * @param bool $simplified
     * @return array|string
     * @see ReversoTranslation::translate()
     */
    public function trans(string $input, bool $simplified = true)
    {
        return $this->translate($input, $simplified);
    }

    /**
     * @param mixed $text
     * @throws Exception
     */
    private function setText($text)
    {
        // TODO maibe limit of string
        /*if (strlen($text) > 500) {
            throw new Exception("text not string");
        }*/

        $this->text = $text;
    }

    /**
     * If langTo for array
     *
     * @return array
     * @throws Exception
     */
    private function multiTrans()
    {
        $trans = [];
        foreach ($this->to as $i => $to) {
            $trans[ $to ] = $this->api(reversoTranslationLangsConvert($to));
        }
        return $this->success($trans);
    }

    /**
     * @param $langTo
     * @return string
     * @throws Exception
     */
    private function api($langTo)
    {
        if ($this->langFrom == $langTo) {
            return $this->text;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://api.reverso.net/translate/v1/translation",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $this->jsonFields($langTo),
            CURLOPT_HTTPHEADER     => [
                "cache-control: no-cache",
                "content-type: application/json",
                "content-encoding: gzip",
            ],
        ));

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception($err);
        }
        return current(json_decode($response)->translation);
    }

    /**
     * @param $langTo
     * @return false|string
     */
    private function jsonFields($langTo)
    {
        return json_encode([
            "input"   => $this->text,
            "from"    => $this->langFrom,
            "to"      => $langTo,
            "format"  => "text",
            "options" => [
                "origin"            => "reversodesktop",
                "sentenceSplitter"  => false,
                "contextResults"    => false,
                "languageDetection" => false
            ]
        ]);
    }

    /**
     * @param $message
     * @return array
     */
    private function fail($message)
    {
        return $this->message(false, $message);
    }

    /**
     * @param string|array $translate
     * @return array
     */
    private function success($translate)
    {
        return $this->message(true, reversoTranslationMessageSuccess($this->from), $translate);
    }

    /**
     * @param bool $success
     * @param string|null $message
     * @param array|null $translate
     * @return array
     */
    private function message(bool $success, string $message = null, $translate = []|null)
    {
        return $this->simplified ? [
            "success"   => $success,
            "translate" => $translate,
            "message"   => $message,
        ] : [
            "success"   => $success,
            "from"      => $this->from,
            "to"        => $this->to,
            "text"      => $this->text,
            "translate" => $translate,
            "message"   => $message,
        ] ;
    }
}