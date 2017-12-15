<?php
/**
 * User: execut
 * Date: 01.08.16
 * Time: 17:09
 */

namespace execut\import\components;


use yii\base\Component;

class EncodingDetector extends Component
{
    public $source = null;
    public $expected = null;
    public function detect() {
        if ($this->source === $this->expected) {
            return [];
        }

        $encodings = self::getEncodingsList();
        foreach ($encodings as $fromEncoding) {
            foreach ($encodings as $toEncoding) {
                if ($fromEncoding === $toEncoding) {
                    continue;
                }

                if ($result = $this->tryConvert($this->source, $this->expected, $fromEncoding, $toEncoding)) {
                    return $result;
                }
            }
        }
    }

    protected function tryConvert($source, $expected, $from, $to, $isTryLevel = true) {
        $currentResult = \mb_convert_encoding($source, $to, $from);
        if ($currentResult === $expected) {
            return [$from, $to];
        }

        if (!$isTryLevel) {
            return;
        }

        $encodings = self::getEncodingsList();
        foreach ($encodings as $fromEncoding) {
            foreach ($encodings as $toEncoding) {
                if ($fromEncoding === $toEncoding) {
                    continue;
                }

                if ($result = $this->tryConvert($currentResult, $this->expected, $fromEncoding, $toEncoding, false)) {
                    return $result;
                }
            }
        }
    }

    public static function getEncodingsList() {
        $encodings = explode(', ', 'KOI8-R, KOI8-U, UTF-8, UTF-16, CP1251, CP1252, ISO-8859-1');
        return $encodings;
        $encodings = \mb_list_encodings();
        unset($encodings[0]);
        unset($encodings[1]);
        return $encodings;
    }
}