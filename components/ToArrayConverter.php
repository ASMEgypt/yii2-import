<?php
/**
 * User: execut
 * Date: 19.07.16
 * Time: 11:55
 */

namespace execut\import\components;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use yii\base\Component;

class ToArrayConverter extends Component
{
    public $file;
    public $fileExtension;
    public $cache = null;
    public $encoding = 'UTF-8';
    public $enclosure = '"';
    public $delimiter = ',';
    public $trim = null;
    public $limit = null;
    public $mimeType = null;
    public function convert() {
        $file = $this->file;
        if (!is_string($file)) {
            $isUnlinkFile = true;
            $fileContent = stream_get_contents($file);
            $file = tempnam('/tmp', 'test_');
            file_put_contents($file, $fileContent);
        } else {
            $isUnlinkFile = false;
        }

        if ($this->mimeType === 'application/x-rar' || ($this->mimeType === null && mime_content_type($file) === 'application/x-rar')) {
            $unpackedFile = tempnam('/tmp', 'test_');
            exec('unrar p -inul "' . $file . '" > ' . $unpackedFile);
            if ($isUnlinkFile) {
                unlink($file);
            }

            $file = $unpackedFile;
            $isUnlinkFile = true;
        }

        if ($this->mimeType === 'application/zip' || ($this->mimeType === null && mime_content_type($file) === 'application/zip')) {
            $unpackedFile = '/tmp/' . exec('unzip -l "' . $file . '" | awk \'/-----/ {p = ++p % 2; next} p {print $NF}\'');
            exec('unzip -cqq "' . $file . '" > ' . $unpackedFile);
            if ($isUnlinkFile) {
                unlink($file);
            }

            $file = $unpackedFile;
            $isUnlinkFile = true;
        }

        $cache = $this->getCache();
        if ($cache) {
            $md5 = md5_file($file);
            $cacheKey = $md5 . $this->delimiter . '-' . $this->enclosure . '-' . $this->encoding . '-' . $this->trim;
            if ($result = $cache->get($cacheKey)) {
//                $result = array_splice($result, 0, 10);
                return $result;
            }
        }

//        \PHPExcel_Settings::setLocale('ru_RU');
        \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');
        \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator('');
        try {
            $reader = IOFactory::createReaderForFile($file);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext === 'xls') {
                $reader = IOFactory::createReader('Xls');
            } else {
                throw $e;
            }
        }

        if (($reader instanceof Csv) || ($reader instanceof Html)) {
            $result = [];
            $content = str_replace(["\r\n", "\n\r", "\r"], "\n", file_get_contents($file));
            $content = explode("\n", $content);
            $delimiter = $this->delimiter;
            foreach ($content as $value) {
                $value = $this->convertEncoding($value);
                if ($this->delimiter) {
                    if (strlen($this->delimiter) == 2) {
                        $delimiter = '~';
                        $value = str_replace($this->delimiter, $delimiter, $value);
                    }
                }

                $parts = str_getcsv($value, $delimiter, $this->enclosure);
                $result[] = $parts;
            }
        } else {
//            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
//            $cacheSettings = array( 'memoryCacheSize' => '500MB');
//            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
//            $reader->setReadDataOnly(true);
            $oldReporting = error_reporting();
            error_reporting(E_ALL & ~E_NOTICE);
            try {
                $table = $reader->load($file);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $reader = \PHPExcel_IOFactory::createReaderForFile($file);
                $table = $reader->load($file);
            }

            error_reporting($oldReporting);

            $result = [];
            $sheets = $table->getAllSheets();
            foreach ($sheets as $sheet) {
                $result = array_merge($result, $sheet->toArray(null, false));
            }

            if ($this->encoding !== null) {
                foreach ($result as &$row) {
                    foreach ($row as $key => $value) {
                        $value = $this->convertEncoding($value);

                        $row[$key] = $value;
                    }
                }
            }
        }

        if ($this->trim !== null) {
            foreach ($result as &$row) {
                foreach ($row as $key => $value) {
                    $value = trim($value, $this->trim);

                    $row[$key] = $value;
                }
            }
        }


        if ($isUnlinkFile) {
            unlink($file);
        }

        $cache = $this->getCache();
        if ($cache) {
            $cache->set($cacheKey, $result, 3600);
        }

        return $result;
    }

    public function getCache() {
        if ($this->cache !== null) {
            return $this->cache;
        }

        if (YII_ENV !== 'test') {
            return $this->cache = \yii::$app->cache;
        }
    }

    /**
     * @param $value
     * @return mixed|string
     */
    protected function convertEncoding($value)
    {
        if ($this->encoding === 'ISO-8859-1') {
            $value = \mb_convert_encoding(mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8'), 'UTF-8', 'cp1251');
        } else {
            $value = \mb_convert_encoding($value, 'UTF-8', $this->encoding);
        }

        return $value;
    }
}