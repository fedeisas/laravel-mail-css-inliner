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
use Symfony\Component\Mime\Exception\LogicException;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Component\Mime\Part\AbstractPart;
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

    public function test_it_should_convert_html_body_with_attachment(): void
    {
        $originalMessage = $this->createMessageToSend(
            (new Email)->html($this->stubs['original-html']),
            __DIR__ . '/stubs/original-html.stub'
        );

        $message = $this->fakeSendMessageUsingInlinePlugin($originalMessage);

        $this->assertBodyMatchesStub($message, 'converted-html');
        $this->assertSameMessageStructure($originalMessage, $message);
        $this->assertAttachmentsAreIdentical($originalMessage, $message);
    }

    public function test_it_should_convert_html_body_with_given_css(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html-with-css']),
            [__DIR__ . '/css/test.css']
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-css');
    }

    public function test_it_should_convert_html_body_with_given_css_content(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html-with-css']),
            [],
            file_get_contents(__DIR__ . '/css/test.css')
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-css');
    }

    public function test_it_should_convert_html_body_with_given_css_and_attachment(): void
    {
        $originalMessage = $this->createMessageToSend(
            (new Email)->html($this->stubs['original-html-with-css']),
            __DIR__ . '/stubs/original-html.stub'
        );

        $message = $this->fakeSendMessageUsingInlinePlugin(
            $originalMessage,
            [__DIR__ . '/css/test.css']
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-css');
        $this->assertSameMessageStructure($originalMessage, $message);
        $this->assertAttachmentsAreIdentical($originalMessage, $message);
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

    public function test_it_should_convert_html_body_and_text_parts_with_attachment(): void
    {
        $originalMessage = $this->createMessageToSend(
            (new Email)->html($this->stubs['original-html'])->text($this->stubs['plain-text']),
            __DIR__ . '/stubs/original-html.stub'
        );

        $message = $this->fakeSendMessageUsingInlinePlugin($originalMessage);

        $this->assertBodyMatchesStub($message, 'converted-html');
        $this->assertBodyMatchesStub($message, 'plain-text', 'plain');
        $this->assertSameMessageStructure($originalMessage, $message);
        $this->assertAttachmentsAreIdentical($originalMessage, $message);
    }

    public function test_it_should_leave_plain_text_unmodified(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->text($this->stubs['plain-text'])
        );

        $this->assertBodyMatchesStub($message, 'plain-text', 'plain');
    }

    public function test_it_should_leave_plain_text_unmodified_with_attachment(): void
    {
        $originalMessage = $this->createMessageToSend(
            (new Email)->text($this->stubs['plain-text']),
            __DIR__ . '/stubs/original-html.stub'
        );

        $message = $this->fakeSendMessageUsingInlinePlugin($originalMessage);

        $this->assertBodyMatchesStub($message, 'plain-text', 'plain');
        $this->assertSameMessageStructure($originalMessage, $message);
        $this->assertAttachmentsAreIdentical($originalMessage, $message);
    }

    public function test_it_should_convert_html_body_as_a_part(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html');
    }

    public function test_it_should_convert_html_body_as_a_part_with_attachment(): void
    {
        $originalMessage = $this->createMessageToSend(
            (new Email)->html($this->stubs['original-html']),
            __DIR__ . '/stubs/original-html.stub'
        );
        $message = $this->fakeSendMessageUsingInlinePlugin($originalMessage);

        $this->assertBodyMatchesStub($message, 'converted-html');
        $this->assertSameMessageStructure($originalMessage, $message);
        $this->assertAttachmentsAreIdentical($originalMessage, $message);
    }

    public function test_it_should_convert_html_body_with_link_css(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html-with-link-css'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-css');
    }

    public function test_it_should_convert_html_body_with_link_css_and_attachment(): void
    {
        $originalMessage = $this->createMessageToSend(
            (new Email)->html($this->stubs['original-html-with-link-css']),
            __DIR__ . '/stubs/original-html.stub'
        );

        $message = $this->fakeSendMessageUsingInlinePlugin($originalMessage);

        $this->assertBodyMatchesStub($message, 'converted-html-with-css');
        $this->assertSameMessageStructure($originalMessage, $message);
        $this->assertAttachmentsAreIdentical($originalMessage, $message);
    }

    public function test_it_should_convert_html_body_with_links_css(): void
    {
        $message = $this->fakeSendMessageUsingInlinePlugin(
            (new Email)->html($this->stubs['original-html-with-links-css'])
        );

        $this->assertBodyMatchesStub($message, 'converted-html-with-links-css');
    }

    public function test_it_should_convert_html_body_with_links_css_and_attachment(): void
    {
        $originalMessage = $this->createMEssageToSend(
            (new Email)->html($this->stubs['original-html-with-links-css']),
            __DIR__ . '/stubs/original-html.stub'
        );
        $message = $this->fakeSendMessageUsingInlinePlugin($originalMessage);

        $this->assertBodyMatchesStub($message, 'converted-html-with-links-css');
        $this->assertSameMessageStructure($originalMessage, $message);
        $this->assertAttachmentsAreIdentical($originalMessage, $message);
    }

    private function assertBodyMatchesStub(Email $message, string $stub, string $mediaSubType = 'html'): void
    {
        $body = $message->getBody();

        if (! $body instanceof AbstractPart) {
            throw new RuntimeException('Unknown message body type');
        }

        $actual = $this->getTextFromPart($body, $mediaSubType);

        if (is_null($actual)) {
            throw new RuntimeException("No text found in body with media subtype '$mediaSubType'" );
        }

        $this->assertEquals($this->stubs[$stub], $this->cleanupHtmlStringForComparison($actual));

        if ($mediaSubType === 'html') {
            $htmlBody = $message->getHtmlBody();

            if (!empty($htmlBody)) {
                $this->assertEquals($this->stubs[$stub], $this->cleanupHtmlStringForComparison($htmlBody));
            }
        }
    }

    private function assertSameMessageStructure(Email $expected, Email $actual)
    {
        $expected_structure = $this->getMessagePartStructure($expected->getBody());
        $actual_structure = $this->getMessagePartStructure($actual->getBody());

        $this->assertEquals($expected_structure, $actual_structure);
    }

    private function assertAttachmentsAreIdentical(Email $expected, Email $actual)
    {
        $expected_attachments = $expected->getAttachments();
        $actual_attachments = $actual->getAttachments();

        $this->assertGreaterThan(0, count($expected_attachments));
        $this->assertSameSize($expected_attachments, $actual_attachments);

        for ($i = 0; $i < count($expected_attachments); $i++) {
            $this->assertEquals($expected_attachments[$i]->getBody(), $actual_attachments[$i]->getBody());
        }
    }

    private function getMessagePartStructure(AbstractPart $part): array|string
    {
        $structure = [];

        $partClass = get_class($part);

        if (! $part instanceof AbstractMultipartPart) {
            $structure[] = $partClass;
        } else {
            $structure[$partClass] = [];
            foreach ($part->getParts() as $childPart) {
                $structure[$partClass][] = $this->getMessagePartStructure($childPart);
            }
        }

        if (count($structure, COUNT_RECURSIVE) === 1) {
            $structure = $structure[0];
        }

        return $structure;
    }

    private function cleanupHtmlStringForComparison(string $string): string
    {
        // Strip out all newlines and trim newlines from the start and end
        $string = str_replace("\n", '', trim($string));

        // Strip out any whitespace between HTML tags
        return preg_replace('/(>)\s+(<\/?[a-z]+)/', '$1$2', $string);
    }

    private function getTextFromPart(AbstractPart $part, string $mediaSubType = 'html'): ?string
    {
        if ($part instanceof TextPart && $part->getMediaType() === 'text' && $part->getMediaSubtype() === $mediaSubType) {
            return $part->getBody();
        } elseif ($part instanceof AbstractMultipartPart) {
            foreach ($part->getParts() as $childPart) {
                $text = $this->getTextFromPart($childPart, $mediaSubType);

                if (! is_null($text)) {
                    return $text;
                }
            }
        }

        return null;
    }

    private function fakeSendMessageUsingInlinePlugin(Email $message, array $inlineCssFiles = [], string $inlineCssContent = null): Email
    {
        $processedMessage = null;

        $dispatcher = new EventDispatcher;
        $dispatcher->addListener(MessageEvent::class, static function (MessageEvent $event) use ($inlineCssFiles, $inlineCssContent, &$processedMessage) {
            $handler = new CssInlinerPlugin([
                'css-files' => $inlineCssFiles,
                'css-content' => $inlineCssContent,
            ]);

            $handler->handleSymfonyEvent($event);

            $processedMessage = $event->getMessage();
        });

        $mailer = new Mailer(
            Transport::fromDsn('null://default', $dispatcher)
        );

        // Check if the message is valid (has to, cc and bcc set). If not, we create a valid message.
        try {
            $message->ensureValidity();
        } catch (LogicException) {
            $message = $this->createMessageToSend($message);
        }

        try {
            $mailer->send($message);
        } catch (TransportExceptionInterface) {
            // We are not really expecting anything to happen here considering it's a `NullTransport` we are using :)
        }

        if (!$processedMessage instanceof Email) {
            throw new RuntimeException('No email was processed!');
        }

        return $processedMessage;
    }

    private function createMessageToSend(Email $message, string $attachmentPath = null): Email
    {
        $message = $message->to('test2@example.com')
                    ->from('test@example.com')
                    ->subject('Test');

        if (! is_null($attachmentPath)) {
            $message = $message->attachFromPath($attachmentPath);
        }

        return $message;
    }
}
