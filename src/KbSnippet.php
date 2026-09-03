<?php

namespace Kirby\Kb;

use Kirby\Cms\App;
use Kirby\Filesystem\F;
use Kirby\Template\Snippet as BaseSnippet;

/**
 * A snippet backed by a .kb.php file, rendered through the kb Renderer
 * so snippets can use the same kb: tags as templates
 */
class KbSnippet extends BaseSnippet
{
    /**
     * Resolves the first matching .kb.php snippet file for the given name(s)
     */
    public static function file(string|array $name): string|null
    {
        $root = static::root();

        foreach ((array) $name as $item) {
            $item = str_replace('\\', '/', trim((string) $item, " /\\"));
            if ($item === '' || str_contains($item, '../') || str_contains($item, '/..')) {
                continue;
            }

            $file = $root . '/' . $item . '.kb.php';

            if (F::exists($file) === true) {
                return $file;
            }
        }

        return null;
    }

    public function render(array $data = [], array $slots = []): string
    {
        if ($this->open === true) {
            ob_end_clean();
            $this->open = false;
        }

        $data = [...$this->data, ...$data, 'slot' => $slots['default'] ?? ''];

        return (new Renderer($data['page'] ?? null, $data))->render($this->file);
    }
}
