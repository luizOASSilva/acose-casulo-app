<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class RichTextSanitizer
{
    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $cachePath = storage_path('framework/cache/htmlpurifier');

        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0775, true);
        }

        $config = HTMLPurifier_Config::createDefault();

        $config->set('Cache.SerializerPath', $cachePath);

        $config->set(
            'HTML.Allowed',
            implode(',', [
                'p',
                'br',
                'strong',
                'b',
                'em',
                'i',
                'u',
                's',
                'h2',
                'h3',
                'ul',
                'ol',
                'li',
                'blockquote',
                'a[href|target|rel]',
            ])
        );

        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', false);

        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'tel' => true,
        ]);

        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
