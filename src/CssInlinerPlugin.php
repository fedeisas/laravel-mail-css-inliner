<?php

namespace Fedeisas\LaravelMailCssInliner;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class CssInlinerPlugin implements \Swift_Events_SendListener
{
    /**
     * @var CssToInlineStyles
     */
    private $converter;

    /**
     * @var string
     */
    protected $css;

    /**
     * @param array $options options defined in the configuration file.
     */
    public function __construct(array $options)
    {
        $this->converter = new CssToInlineStyles();
        if (isset($options['css-files']) && count($options['css-files']) > 0) {
            $this->css = '';
            foreach ($options['css-files'] as $file) {
                $this->css .= file_get_contents($file);
            }
        }
    }

    /**
     * @param \Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        if ($message->getContentType() === 'text/html'
            || ($message->getContentType() === 'multipart/alternative' && $message->getBody())
            || ($message->getContentType() === 'multipart/mixed' && $message->getBody())
        ) {
            $message->setBody($this->converter->convert($message->getBody(), $this->css));
        }

        foreach ($message->getChildren() as $part) {
            if (strpos($part->getContentType(), 'text/html') === 0) {
                $part->setBody($this->converter->convert($part->getBody(), $this->css));
            }
        }
    }

    /**
     * Do nothing
     *
     * @param \Swift_Events_SendEvent $evt
     */
    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
        // Do Nothing
    }
}
