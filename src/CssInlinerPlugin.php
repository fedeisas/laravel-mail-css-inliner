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
        $converter->setEncoding($message->getCharset());
        $converter->setUseInlineStylesBlock();
        $converter->setCleanup();

        if ($message->getContentType() === 'text/html' ||
            ($message->getContentType() === 'multipart/alternative' && $message->getBody())
        ) {
            $message->setBody($this->includeExternalStylesheets($message->getBody()),'text/html');
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

    public function includeExternalStylesheets($message)
    {
        preg_match_all('/<link[^>]+>/mi', $message, $matches);
        $linkElements = $matches[0];
        $count = count($linkElements);

        if ($linkElements) {
            foreach ($linkElements as $i => $linkElement) {
                $cssContent = $this->getCssContent($linkElement);
                $message = str_replace($linkElement, '
                    <style type="text/css">
                        ' . $cssContent . '
                    </style>
                ', $message);
            }
        }
        return $message;
    }

    /**
     * @param $linkElement
     * @return string
     */
    private function getCssContent($linkElement)
    {
        // load data from html element
        preg_match_all("/href=['\"]([^'\"]+)['\"]/", $linkElement, $matches);
        $url = $matches[1][0];

        $absUrl = $url;
        
        // this needs to be changed to search in all include paths
        $cssContent = @file_get_contents($absUrl);

        if (!$cssContent) {
            trigger_error('Error loading stylesheet from ' . $absUrl, E_USER_NOTICE);
        }

        return $cssContent;
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
