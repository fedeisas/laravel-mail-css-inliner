<?php

use Fedeisas\LaravelMailCssInliner\CssInlinerPlugin;

class CssInlinerPluginTest extends PHPUnit_Framework_TestCase
{

    protected $stubs;

    public function setUp()
    {
        $this->stubs['plain-text'] = file_get_contents(__DIR__.'/stubs/plain-text.stub');
        $this->stubs['original-html'] = file_get_contents(__DIR__.'/stubs/original-html.stub');
        $this->stubs['converted-html'] = file_get_contents(__DIR__.'/stubs/converted-html.stub');
    }

    /** @test **/
    public function it_should_convert_html_body()
    {
        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPLugin(new CssInlinerPlugin());

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setContentType('text/html');
        $message->setBody($this->stubs['original-html']);

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html'], $message->getBody());
    }

    /** @test **/
    public function it_should_convert_html_parts()
    {
        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPLugin(new CssInlinerPlugin());

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->addPart($this->stubs['original-html'], 'text/html');
        $message->addPart('plain part', 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $children[0]->getBody());
    }

    /** @test **/
    public function it_should_leave_plain_text_unmodified()
    {
        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPLugin(new CssInlinerPlugin());

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }
}
