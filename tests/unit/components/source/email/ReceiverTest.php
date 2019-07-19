<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 4:50 PM
 */

namespace execut\import\components\source\adapter\email;


use Ddeboer\Imap\Message\Attachment;
use execut\actions\action\adapter\File;
use execut\import\tests\TestCase;
use roopz\imap\Imap;
use roopz\imap\IncomingMail;
use roopz\imap\IncomingMailAttachment;
use yii\caching\Cache;
use yii\caching\CacheInterface;

class ReceiverTest extends TestCase
{
    public function testGetMails() {
        $imap = $this->getMockBuilder(Imap::class)->setMethods([
            'searchMailBox',
            'getMail',
        ])->getMock();
        $now = date('d F Y');
        $imap->method('searchMailBox')->with('UNSEEN SINCE "' . $now . '"')->willReturn([
            1,
        ]);
        $imap->expects($this->once())->method('searchMailBox')->with('UNSEEN SINCE "' . $now . '"')->willReturn([]);

        $imap->expects($this->once())->method('getMail')->with(1, false)->willReturn(new IncomingMail());

        $receiver = new Receiver([
            'imap' => $imap,
            'now' => $now,
            'cache' => new ReceiverCacheStub(),
        ]);
        $receiver->getMails();
        $result = $receiver->getMails();
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Mail::class, $result[0]);
    }

    public function testFiltrate() {
        $file = new File();
        $emails = [
            new Mail([
                'id' => 1,
                'subject' => 'Test',
                'attachments' => [
                    $file,
                ],
            ]),
        ];

        $filter = $this->getMockBuilder(Filter::class)->setMethods([
            'filtrate'
        ])->getMock();
        $filter->expects($this->once())->method('filtrate')->with($emails)->willReturn($emails);

        $imap = $this->getMockBuilder(Imap::class)->setMethods([
            'markMailAsRead',
        ])->getMock();
        $imap->expects($this->once())->method('markMailAsRead')->with(1);

        $receiver = $this->getMockBuilder(Receiver::class)->setConstructorArgs([
            [
                'imap' => $imap,
                'filter' => $filter,
            ],
        ])->setMethods(['_getMails'])->getMock();
        $receiver->method('_getMails')->willReturn($emails);
        $this->assertEquals($emails, $receiver->getMails());
    }

    public function testCreateMailFromImap() {
        $imapMail = new IncomingMail();
        $attributes = [
            'id' => 1,
            'subject' => 'subject',
            'fromAddress' => 'address@address.ru'
        ];
        foreach ($attributes as $key => $attribute) {
            $imapMail->$key = $attribute;
        }

        $attachment = new IncomingMailAttachment();
        $attachment->name = 'fileName';
        $attachment->filePath = 'filePath';
        $imapMail->addAttachment($attachment);

        $mail = Receiver::createMailFromImap($imapMail);
        $this->assertEquals([
            'id' => 1,
            'subject' => 'subject',
            'sender' => 'address@address.ru',
        ], [
            'id' => $mail->id,
            'subject' => $mail->subject,
            'sender' => $mail->sender,
        ]);

        $this->assertCount(1, $mail->attachments);
        $file = $mail->attachments[0];
        $this->assertEquals('filePath', $file->filePath);
        $this->assertEquals('fileName', $file->fileName);
    }
}

class ReceiverCacheStub extends ReceiverCache {
    public $cache = null;
    public function get()
    {
        return $this->cache;
    }

    public function set($mails)
    {
        return $this->cache = $mails;
    }
}