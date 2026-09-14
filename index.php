<?php

namespace Kirby\Kb;

use Kirby\Cms\App;
use Kirby\Template\Snippet as BaseSnippet;

require_once __DIR__ . '/src/Renderer.php';
require_once __DIR__ . '/src/Template.php';
require_once __DIR__ . '/src/KbSnippet.php';

App::plugin('hashandsalt/kb', [
    'options' => [
        'cssPath' => 'assets/css',
        'jsPath' => 'assets/js',
        'dateFormat' => 'd/m/y',
    ],
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

            return (new Renderer(
                $data['page'] ?? null,
                $data,
                $kirby->option('hashandsalt.kb.cssPath', 'assets/css'),
                $kirby->option('hashandsalt.kb.jsPath', 'assets/js'),
                $kirby->option('hashandsalt.kb.dateFormat', 'd/m/y')
            ))->render($file);
        }
    ]
]);