<?php

namespace Fedeisas\LaravelMailCssInliner;

use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Component\Mime\Part\TextPart;

class Util {
    public static function getTextFromPart(AbstractPart $part, string $mediaSubType = 'html'): ?string
    {
        if ($part instanceof TextPart && $part->getMediaType() === 'text' && $part->getMediaSubtype() === $mediaSubType) {
            return $part->getBody();
        } elseif ($part instanceof AbstractMultipartPart) {
            foreach ($part->getParts() as $childPart) {
                $text = Util::getTextFromPart($childPart, $mediaSubType);

                if (! is_null($text)) {
                    return $text;
                }
            }
        }

        return null;
    }
}
