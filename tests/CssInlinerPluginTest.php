<?php

use Fedeisas\LaravelMailCssInliner\CssInlinerPlugin;

class CssInlinerPluginTest extends PHPUnit_Framework_TestCase
{
    protected $stubs;

    protected $options;

    protected static $stubDefinitions = array(
        'plain-text', 'original-html', 'converted-html', 'converted-html-with-classes',
        'converted-html-with-styles'
    );

    public function setUp()
    {
        foreach (self::$stubDefinitions as $stub) {
            $this->stubs[$stub] = file_get_contents(__DIR__.'/stubs/'.$stub.'.stub');
        }

        $this->options = require(__DIR__.'/../config/css-inliner.php');
    }

    /** @test **/
    public function itShouldConvertHtmlBody()
    {
        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html'], $message->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyKeepingClasses()
    {
        $this->options['strip-classes'] = false;

        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html-with-classes'], $message->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyKeepingStyles()
    {
        $this->options['strip-styles'] = false;

        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html-with-styles'], $message->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAndTextParts()
    {
        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody($this->stubs['original-html'], 'text/html');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $message->getBody());
        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldLeavePlainTextUnmodified()
    {
        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAsAPart()
    {
        $mailer = Swift_Mailer::newInstance(Swift_NullTransport::newInstance());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->addPart($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $children[0]->getBody());
    }
}
