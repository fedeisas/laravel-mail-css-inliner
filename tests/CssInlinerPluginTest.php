<?php

use Fedeisas\LaravelMailCssInliner\CssInlinerPlugin;
use Orchestra\Testbench\TestCase;

class CssInlinerPluginTest extends TestCase
{

    protected $stubs;
    protected $config;
    protected $mailer;

    public function setUp()
    {
        parent::setUp();

        $this->config = App::make('config');

        $this->setupStubs();
        $this->setupMailer();
    }

    private function setupStubs()
    {
        $this->stubs['plain-text'] = file_get_contents(__DIR__.'/stubs/plain-text.stub');
        $this->stubs['original-html'] = file_get_contents(__DIR__.'/stubs/original-html.stub');
        $this->stubs['converted-html'] = file_get_contents(__DIR__.'/stubs/converted-html.stub');
        $this->stubs['stripped-style-tags-html'] = file_get_contents(__DIR__.'/stubs/stripped-style-tags-html.stub');
    }

    private function setupMailer()
    {
        $this->mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());
        $this->mailer->registerPlugin(new CssInlinerPlugin($this->config));
    }

    private function makeMessage()
    {
        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');

        return $message;
    }

    /** @test **/
    public function itShouldConvertHtmlBody()
    {
        $message = $this->makeMessage();

        $message->setBody($this->stubs['original-html'], 'text/html');

        $this->mailer->send($message);

        $this->assertEquals($this->stubs['converted-html'], $message->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAndTextParts()
    {
        $message = $this->makeMessage();

        $message->setBody($this->stubs['original-html'], 'text/html');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $this->mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $message->getBody());
        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldLeavePlainTextUnmodified()
    {
        $message = $this->makeMessage();

        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $this->mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAsAPart()
    {
        $message = $this->makeMessage();

        $message->addPart($this->stubs['original-html'], 'text/html');

        $this->mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldStripStyleTags()
    {
        $this->config->set('laravel-mail-css-inliner.strip-style-tags', true);

        $message = $this->makeMessage();

        $message->setBody($this->stubs['original-html'], 'text/html');

        $this->mailer->send($message);

        $this->assertEquals($this->stubs['stripped-style-tags-html'], $message->getBody());
    }
}
