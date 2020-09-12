<?php

namespace Tests;

use Fedeisas\LaravelMailCssInliner\CssInlinerPlugin;
use Swift_Mailer;
use Swift_Message;
use Swift_NullTransport;
use PHPUnit\Framework\TestCase;

class CssInlinerPluginTest extends TestCase
{
    /**
     * @var array
     */
    protected $stubs;

    /**
     * @var array
     */
    protected $options;

    protected static $stubDefinitions = [
        'converted-html',
        'converted-html-with-css',
        'converted-html-with-link-css',
        'converted-html-with-link-external',
        'converted-html-with-link-relative-external',
        'converted-html-with-links-css',
        'converted-html-with-mixed-type-links',
        'converted-html-with-non-stylesheet-link',
        'original-html',
        'original-html-with-css',
        'original-html-with-link-css',
        'original-html-with-link-external',
        'original-html-with-link-relative-external',
        'original-html-with-links-css',
        'original-html-with-mixed-type-links',
        'original-html-with-non-stylesheet-link',
        'plain-text',
    ];

    public function setUp(): void
    {
        foreach (self::$stubDefinitions as $stub) {
            $this->stubs[$stub] = $this->normalize(file_get_contents(__DIR__ . '/stubs/' . $stub . '.stub'));
        }

        $this->options = require(__DIR__ . '/../config/css-inliner.php');
    }

    /** @test **/
    public function itShouldConvertHtmlBody()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setBody($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $this->assertEquals(
            $this->stubs['converted-html'],
            $this->normalize($message->getBody())
        );
    }

    /** @test **/
    public function itShouldConvertHtmlBodyWithGivenCss()
    {
        $this->options['css-files'] = [__DIR__ . '/css/test.css'];
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setBody($this->stubs['original-html-with-css'], 'text/html');

        $mailer->send($message);

        $this->assertXmlStringEqualsXmlString(
            $this->stubs['converted-html-with-css'],
            $this->normalize($message->getBody())
        );
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAndTextParts()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setBody($this->stubs['original-html'], 'text/html');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $this->normalize($message->getBody()));
        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldLeavePlainTextUnmodified()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAsAPart()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->addPart($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $this->normalize($children[0]->getBody()));
    }

    /** @test **/
    public function itShouldConvertHtmlBodyWithLinkCss()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');

        $message->setBody($this->stubs['original-html-with-link-css'], 'text/html');

        $mailer->send($message);

        $this->assertXmlStringEqualsXmlString(
            $this->stubs['converted-html-with-link-css'],
            $this->normalize($message->getBody())
        );
    }

    /** @test **/
    public function itShouldConvertHtmlBodyWithLinksCss()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');

        $message->setBody($this->stubs['original-html-with-links-css'], 'text/html');

        $mailer->send($message);

        $this->assertXmlStringEqualsXmlString(
            $this->stubs['converted-html-with-links-css'],
            $this->normalize($message->getBody())
        );
    }

    /** @test **/
    public function itShouldWorkWithNonStylesheetLinks()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');

        $message->setBody($this->stubs['original-html-with-non-stylesheet-link'], 'text/html');

        $mailer->send($message);

        $this->assertEquals(
            $this->stubs['converted-html-with-non-stylesheet-link'],
            $this->normalize($message->getBody())
        );
    }

    /** @test **/
    public function itShouldWorkWithMixedTypeLinks()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');

        $message->setBody($this->stubs['original-html-with-mixed-type-links'], 'text/html');

        $mailer->send($message);

        $this->assertEquals(
            $this->stubs['converted-html-with-mixed-type-links'],
            $this->normalize($message->getBody())
        );
    }

    /** @test **/
    public function itShouldWorkWithExternalLink()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');

        $message->setBody($this->stubs['original-html-with-link-external'], 'text/html');

        $mailer->send($message);

        $this->assertEquals(
            $this->stubs['converted-html-with-link-external'],
            $this->normalize($message->getBody())
        );
    }

    /** @test **/
    public function itShouldWorkWithRelativeExternalLink()
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message('Test');

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');

        $message->setBody($this->stubs['original-html-with-link-relative-external'], 'text/html');

        $mailer->send($message);

        $this->assertEquals(
            $this->stubs['converted-html-with-link-relative-external'],
            $this->normalize($message->getBody())
        );
    }

    protected function normalize(string $html): string
    {
        $document = new \DomDocument();
        $document->loadHTML($html);
        $document->preserveWhiteSpace = false;

        $normalizedHtml = trim($document->saveHTML());

        $search = [
            '/(?:<head>)(\s)+(?:<\/head>)/s', // libxml handles this different across platforms
        ];

        $replace = [
            '<head></head>',
        ];

        return trim(preg_replace($search, $replace, $normalizedHtml));
    }
}
