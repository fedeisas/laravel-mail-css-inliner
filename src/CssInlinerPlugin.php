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
        $converter->setUseInlineStylesBlock();
        $converter->setStripOriginalStyleTags();

        $converter->setCleanup();

        if ($message->getContentType() === 'text/html' ||
            ($message->getContentType() === 'multipart/alternative' && $message->getBody()) ||
            ($message->getContentType() === 'multipart/mixed' && $message->getBody())
        ) {
            $html = '<?xml encoding="UTF-8">' . $message->getBody();
            $converter->setHTML($html);
            $message->setBody($converter->convert());
        }

        foreach ($message->getChildren() as $part) {
            if (strpos($part->getContentType(), 'text/html') === 0) {
                $html = '<?xml encoding="UTF-8">' . $part->getBody();
                $converter->setHTML($html);
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
