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
        $this->loadOptions($options);
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
            $body = $this->loadCssFilesFromLinks($message->getBody());
            $message->setBody($this->converter->convert($body, $this->css));
        }

        foreach ($message->getChildren() as $part) {
            if (strpos($part->getContentType(), 'text/html') === 0) {
                $body = $this->loadCssFilesFromLinks($part->getBody());
                $part->setBody($this->converter->convert($body, $this->css));
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

    /**
     * Load the options
     * @param  array $options Options array
     */
    public function loadOptions($options){
        if (isset($options['css-files']) && count($options['css-files']) > 0) {
            $this->css = '';
            foreach ($options['css-files'] as $file) {
                $this->css .= file_get_contents($file);
            }
        }
    }

    /**
     * Find CSS stylesheet links and load them
     * 
     * Loads the body of the message and passes 
     * any link stylesheets to $this->css
     * Removes any link elements
     * 
     * @return string $message The message
     */
    public function loadCssFilesFromLinks($message){
        $dom = new \DOMDocument();
        $dom->loadHTML($message);
        $link_tags = $dom->getElementsByTagName('link');

        if($link_tags->length > 0){
            foreach($link_tags as $link_tag)
            {
                if($link_tag->getAttribute('rel') == "stylesheet"){
                    $options['css-files'][] = $link_tag->getAttribute('href');                 
                    // remove the link node
                    $link_tag->parentNode->removeChild($link_tag);
                }
            }

            if(isset($options)){
                // reload the options
                $this->loadOptions($options);               
            }

            return $dom->saveHTML();
        }

        return $message;
    }
}
