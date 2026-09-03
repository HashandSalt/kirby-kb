<?php

namespace Kirby\Kb;

use Kirby\Cms\App;
use Kirby\Template\Snippet as BaseSnippet;

require_once __DIR__ . '/src/Renderer.php';
require_once __DIR__ . '/src/Template.php';
require_once __DIR__ . '/src/KbSnippet.php';

App::plugin('hashandsalt/kb', [
    'components' => [
        'template' => function (
            App $kirby,
            string $name,
            string $type = 'html',
            string $defaultType = 'html'
        ) {
            return new Template($name, $type, $defaultType);
        },
        'snippet' => function (
            App $kirby,
            string|array|null $name,
            array $data = [],
            bool $slots = false
        ): BaseSnippet|string {
            $file = $name !== null ? KbSnippet::file($name) : null;

            if ($file === null) {
                return BaseSnippet::factory($name, $data, $slots);
            }

            if ($slots === true) {
                return KbSnippet::begin($file, $data);
            }

            return (new Renderer($data['page'] ?? null, $data))->render($file);
        }
    ]
]);