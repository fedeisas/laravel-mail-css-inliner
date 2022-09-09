<?php

namespace Tests;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Fedeisas\LaravelMailCssInliner\CssInlinerPlugin;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\TextPart;

class CssInlinerPluginTest extends TestCase
{
    protected array $stubs;

    protected array $options;

    protected static array $stubDefinitions = [
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
            $this->stubs[$stub] = $this->cleanupHtmlStringForComparison(
                file_get_contents(__DIR__ . "/stubs/{$stub}.stub")
            );
        }
    }

    public function test_it_should_convert_html_body(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html');
    }

    public function test_it_should_convert_html_body_with_given_css(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html-with-css']),
            [__DIR__ . '/css/test.css']
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-css');
    }

    public function test_it_should_convert_html_body_and_text_parts(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)
                ->html($this->stubs['original-html'])
                ->text($this->stubs['plain-text'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html');
        $this->assertBodyMatchesStub($message, 'plain-text', 'plain');
    }

    public function test_it_should_leave_plain_text_unmodified(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->text($this->stubs['plain-text'])
        );

        $this->assertBodyMatchesStub($message, 'plain-text');
    }

    public function test_it_should_convert_html_body_as_a_part(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html');
    }

    public function test_it_should_convert_html_body_with_link_css(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html-with-link-css'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-css');
    }

    public function test_it_should_convert_html_body_with_links_css(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html-with-links-css'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-links-css');
    }

    private function assertBodyMatchesStub(object $message, string $stub, string $mediaSubType = 'html'): void
    {
        $this->assertInstanceOf(Email::class, $message);

        $body = $message->getBody();

        if ($body instanceof TextPart) {
            $actual = $body->getBody();
        } elseif ($body instanceof AlternativePart || $body instanceof MixedPart) {
            $actual = (new Collection($body->getParts()))->first(
                static fn ($part) => $part instanceof TextPart && $part->getMediaType() === 'text' && $part->getMediaSubtype() === $mediaSubType
            )->getBody();
        } else {
            throw new RuntimeException('Unknown message body type');
        }

        $this->assertEquals($this->stubs[$stub], $this->cleanupHtmlStringForComparison($actual));
    }

    private function cleanupHtmlStringForComparison(string $string): string
    {
        // Strip out all newlines and trim newlines from the start and end
        $string = str_replace("\n", '', trim($string));

        // Strip out any whitespace between HTML tags
        return preg_replace('/(>)\s+(<\/?[a-z]+)/', '$1$2', $string);
    }

    private function fakeSendMessageUsingInlinePlugin(Email $message, array $inlineCssFiles = []): Email
    {
        $processedMessage = null;

        $dispatcher = new EventDispatcher;
        $dispatcher->addListener(MessageEvent::class, static function (MessageEvent $event) use ($inlineCssFiles, &$processedMessage) {
            $handler = new CssInlinerPlugin($inlineCssFiles);

            $handler->handleSymfonyEvent($event);

            $processedMessage = $event->getMessage();
        });

        $mailer = new Mailer(
            Transport::fromDsn('null://default', $dispatcher)
        );

        try {
            $mailer->send(
                $message->to('test2@example.com')
                        ->from('test@example.com')
                        ->subject('Test')
            );
        } catch (TransportExceptionInterface) {
            // We are not really expecting anything to happen here considering it's a `NullTransport` we are using :)
        }

        if (!$processedMessage instanceof Email) {
            throw new RuntimeException('No email was processed!');
        }

        return $processedMessage;
    }
}
