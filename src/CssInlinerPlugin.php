<?php

namespace Fedeisas\LaravelMailCssInliner;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class CssInlinerPlugin implements \Swift_Events_SendListener
{
    /**
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        $converter = new CssToInlineStyles();
        $external = new ExternalCssPlugin();
        $converter->setEncoding($message->getCharset());
        $converter->setUseInlineStylesBlock();
        $converter->setCleanup();

        if ($message->getContentType() === 'text/html' ||
            ($message->getContentType() === 'multipart/alternative' && $message->getBody())
        ) {
            $message->setBody($external->includeExternalStylesheets($message->getBody()), 'text/html');
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
     * Do nothing
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
        // Do Nothing
    }
}
