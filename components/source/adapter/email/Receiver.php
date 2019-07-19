<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 2:26 PM
 */

namespace execut\import\components\source\adapter\email;


use execut\import\components\source\File;
use roopz\imap\Imap;
use roopz\imap\IncomingMail;
use yii\base\Component;

class Receiver extends Component
{
    public $filter = null;
    /**
     * @var Imap
     */
    public $imap = null;
    public $now = null;
    public $cache = null;
    protected $searchCriteria = null;
    protected $mails;

    public function setSearchCriteria($criteria) {
        $this->searchCriteria = $criteria;
    }

    public function getSearchCriteria() {
        if ($this->searchCriteria === null) {
            return 'UNSEEN SINCE "' . $this->getNow() . '"';
        }

        return $this->searchCriteria;
    }

    protected function getNow() {
        if ($this->now !== null) {
            return $this->now;
        }
        return date('d F Y', time() - 3600 * 24 * 10);
    }

    protected function _getMails() {
        if ($this->cache !== null) {
            if ($mails = $this->cache->get()) {
                return $mails;
            }
        }

        $mailsIds = $this->imap->searchMailBox($this->getSearchCriteria());
        $mails = [];
        foreach ($mailsIds as $id) {
            $mails[] = self::createMailFromImap($this->imap->getMail($id, false));
        }

        if ($this->cache !== null) {
            $this->cache->set($mails);
        }

        return $this->mails = $mails;
    }

    /**
     * @return Mail[];
     */
    public function getMails() {
        $mails = $this->_getMails();
        if ($this->filter) {
            $mails = $this->filter->filtrate($mails);
            if ($this->imap !== null) {
                foreach ($mails as $mail) {
                    $this->imap->markMailAsRead($mail->id);
                }
            }
        }

        return $mails;
    }

    public static function createMailFromImap(IncomingMail $imapMail) {
        $attachments = [];
        foreach ($imapMail->getAttachments() as $imapAttachment) {
            $attachment = new File([
                'fileName' => $imapAttachment->name,
                'filePath' => $imapAttachment->filePath,
            ]);
            $attachments[] = $attachment;
        }

        return new Mail([
            'id' => $imapMail->id,
            'sender' => $imapMail->fromAddress,
            'subject' => $imapMail->subject,
            'attachments' => $attachments,
        ]);
    }
}