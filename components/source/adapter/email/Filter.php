<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 3:40 PM
 */

namespace execut\import\components\source\adapter\email;


use yii\base\Component;

class Filter extends Component
{
    public $subject = null;
    public $sender = null;
    public $attachmentName = null;
    public $excludedFileTypes = [
        'jpg',
        'jpeg',
        'gif',
        'png',
        'bmp'
    ];
    public function filtrate($mails) {
        if (empty($this->subject) && empty($this->sender) && empty($this->attachmentName)) {
            return [];
        }

        $this->filtrateBySender($mails);
        $this->filtrateBySubject($mails);
        $this->filtrateByFileTypes($mails);
        $this->filtrateByAttachmentName($mails);

        return $mails;
    }

    protected function filtrateByFileTypes(&$mails) {
        if ($this->excludedFileTypes === null) {
            return;
        }

        foreach ($mails as $mail) {
            if (empty($mail->attachments)) {
                continue;
            }

            $attachments = [];
            foreach ($mail->attachments as $attachment) {
                $isExcluded = false;
                foreach ($this->excludedFileTypes as $fileType) {
                    if (strpos($attachment->fileName, '.' . $fileType) !== false) {
                        $isExcluded = true;
                    }
                }

                if (!$isExcluded) {
                    $attachments[] = $attachment;
                }
            }

            $mail->attachments = $attachments;
        }
    }

    protected function filtrateByAttachmentName(&$mails) {
        if ($this->attachmentName === null) {
            return;
        }

        foreach ($mails as $mail) {
            if (empty($mail->attachments)) {
                continue;
            }

            $attachments = [];
            foreach ($mail->attachments as $attachment) {
                if ($this->isTemplateMatched($attachment->fileName, $this->attachmentName)) {
                    $attachments[] = $attachment;
                }
            }

            $mail->attachments = $attachments;
        }
    }

    protected function filtrateBySender(&$mails) {
        return $this->filtrateByAttribute($mails, 'sender');
    }

    protected function filtrateBySubject(&$mails) {
        return $this->filtrateByAttribute($mails, 'subject');
    }

    protected function filtrateByAttribute(&$mails, $attribute) {
        if (empty($this->$attribute)) {
            return;
        }

        $subjectTemplate = $this->$attribute;
        foreach ($mails as $mailKey => $mail) {
            $subject = $mail->$attribute;
            $isMatch = $this->isTemplateMatched($subject, $subjectTemplate);
            if (!$isMatch) {
                unset($mails[$mailKey]);
            }
        }
    }

    /**
     * @param $subject
     * @param $subjectTemplate
     * @return bool
     */
    protected function isTemplateMatched($subject, $subjectTemplate)
    {
        $isMatch = true;
        $subject = mb_strtolower($subject);
        $subject = trim($subject);
        $subjectTemplate = mb_strtolower($subjectTemplate);
        if (strpos($subjectTemplate, '*') !== false) {
            $parts = explode('*', $subjectTemplate);
            $prevMatchPos = false;
            foreach ($parts as $key => $part) {
                if (empty($part) && $key !== 0) {
                    if ($key === (count($parts) - 1)) {
                        break;
                    }

                    $matchPos = 0;
                } else {
                    if (empty($part) && $key === 0) {
                        $matchPos = 0;
                    } else {
                        $matchPos = strpos($subject, $part);
                    }

                    if ($key === 0 && $matchPos !== 0) {
                        $isMatch = false;
                        break;
                    }
                }

                if ($matchPos === false || ($prevMatchPos !== false && $matchPos < $prevMatchPos)) {
                    $isMatch = false;
                    break;
                }

                if ($key === (count($parts) - 1) && (strlen($subject) > (strlen($part) + $matchPos))) {
                    $isMatch = false;
                    break;
                }

                $prevMatchPos = $matchPos;
            }
        } else if ($subject !== $subjectTemplate) {
            $isMatch = false;
        }

        return $isMatch;
    }
}