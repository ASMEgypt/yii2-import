<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 9/27/16
 * Time: 3:50 PM
 */

namespace execut\import\components\source\adapter\email;

use execut\import\components\source\File;
use execut\TestCase;

class FilterTest extends TestCase
{
    public function testFiltrateBySender() {
        $filter = new Filter;
        $mails = [
            new Mail([
                'sender' => 'sender@sender.ru'
            ])
        ];

        $this->assertCount(0, $filter->filtrate($mails));

        $filter->sender = 'sender2@sender.ru';
        $this->assertCount(0, $filter->filtrate($mails));

        $filter->sender = 'sender@sender.ru';
        $this->assertCount(1, $filter->filtrate($mails));

        $filter->sender = 'send*@sender.ru';
        $this->assertCount(1, $filter->filtrate($mails));
    }

    public function testFiltrateBySubject() {
        $filter = new Filter;
        $mails = [
            new Mail([
                'subject' => 'AT-Import,подгрузка'
            ])
        ];

        $filter->subject = 'AT-Import,подгрузка 2';
        $this->assertCount(0, $filter->filtrate($mails));

        $filter->subject = 'AT-Import,Подгрузка';
        $this->assertCount(1, $filter->filtrate($mails));
    }

    public function testFiltrateBySubjectByAfterPattern() {
        $filter = new Filter;
        $filter->subject = 'AB*';

        $mails = [
            new Mail([
                'subject' => 'AB 2'
            ])
        ];
        $result = $filter->filtrate($mails);
        $this->assertCount(1, $result);

        $mails = [
            new Mail([
                'subject' => '2AB'
            ])
        ];
        $this->assertCount(0, $filter->filtrate($mails));
    }

    public function testFiltrateBySubjectByBeforePattern() {
        $filter = new Filter;
        $filter->subject = '*AB';

        $mails = [
            new Mail([
                'subject' => '2 AB'
            ])
        ];
        $result = $filter->filtrate($mails);
        $this->assertCount(1, $result);

        $mails = [
            new Mail([
                'subject' => 'AB2'
            ])
        ];
        $result = $filter->filtrate($mails);
        $this->assertCount(0, $result);
    }

    public function testFiltrateByFileTypes() {
        $fileTypes = [
            'jpg',
        ];
        $filter = new Filter([
            'sender' => 'sender@sender.ru',
            'excludedFileTypes' => $fileTypes,
        ]);
        $mails = $filter->filtrate([
            new Mail([
                'sender' => 'sender@sender.ru',
                'attachments' => [
                    new File([
                        'fileName' => 'testjpg',
                    ]),
                    new File([
                        'fileName' => 'test.jpg',
                    ]),
                    new File([
                        'fileName' => 'test.xls',
                    ]),
                ],
            ])
        ]);
        $this->assertCount(2, $mails[0]->attachments);
    }

    public function testBugWithFiltrateByRolfSubject() {
        $filter = new Filter;
        $filter->subject = 'Актуальное наличие Рольф Витебский';

        $mails = [
            new Mail([
                'subject' => 'Актуальное наличие Рольф Витебский'
            ])
        ];
        $result = $filter->filtrate($mails);
        $this->assertCount(1, $result);
    }
}