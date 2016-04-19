<?php

namespace Fedeisas\LaravelMailCssInliner;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class CssInlinerPlugin implements \Swift_Events_SendListener
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options options defined in the configuration file.
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        $converter = new CssToInlineStyles();
        $this->applySettings($converter);

        if ($message->getContentType() === 'text/html' ||
            ($message->getContentType() === 'multipart/alternative' && $message->getBody()) ||
            ($message->getContentType() === 'multipart/mixed' && $message->getBody())
        ) {
            $converter->setHTML($message->getBody());
            $message->setBody($converter->convert());
        }

        foreach ($message->getChildren() as $part) {
            if (strpos($part->getContentType(), 'text/html') === 0) {
                $converter->setHTML($part->getBody());
                $part->setBody($converter->convert());
            }
        }
    }

    /**
     * Applies the configuration settings.
     *
     * @param CssToInlineStyles $converter
     */
    private function applySettings(CssToInlineStyles $converter)
    {
        // Always enabled because there is no way to specify an external style sheet
        // when using this plugin
        $converter->setUseInlineStylesBlock();

        if ($this->options['strip-styles']) {
            $converter->setStripOriginalStyleTags();
        }

        if ($this->options['strip-classes']) {
            $converter->setCleanup();
        }
    }

    /**
     * Do nothing
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
        // Do Nothing
    }
}
