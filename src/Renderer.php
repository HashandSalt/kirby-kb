<?php

namespace Kirby\Kb;

use Kirby\Cms\App;
use Kirby\Cms\Html;
use Kirby\Filesystem\F;

class Renderer
{
    protected string $cssPath;
    protected string $jsPath;
    protected string $dateFormat;

    public function __construct(
        protected object|null $page,
        protected array $data = [],
        string|null $cssPath = null,
        string|null $jsPath = null,
        string|null $dateFormat = null
    ) {
        $this->cssPath = $cssPath ?? (string) App::instance()->option('hashandsalt.kb.cssPath', 'assets/css');
        $this->jsPath = $jsPath ?? (string) App::instance()->option('hashandsalt.kb.jsPath', 'assets/js');
        $this->dateFormat = $dateFormat ?? (string) App::instance()->option('hashandsalt.kb.dateFormat', 'd/m/y');
    }

    public function render(string $file): string
    {
        return $this->renderText(F::read($file));
    }

    protected function renderText(string $text): string
    {
        $text = $this->renderForeachBlocks($text);
        $text = $this->renderIfBlocks($text);

        $text = preg_replace_callback(
            '/<kb:([a-z][a-z0-9_-]*)((?:\s+[a-z][a-z0-9_-]*(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?)*)\s*>(.*?)<\/kb:\1\s*>/is',
            function (array $match): string {
                $attributes = $this->attributes($match[2] ?? '');
                $content = in_array(strtolower($match[1]), ['if-field', 'foreach', 'pages', 'php', 'structure'], true)
                    ? $match[3]
                    : $this->renderText($match[3]);

                return $this->tag($match[1], $attributes, $content);
            },
            $text
        );

        $text = preg_replace_callback(
            '/<kb:([a-z][a-z0-9_-]*)((?:\s+[a-z][a-z0-9_-]*(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?)*)\s*\/\s*>/i',
            fn(array $match): string => $this->tag($match[1], $this->attributes($match[2] ?? ''), '', true),
            $text
        );

        return $this->interpolate($text, true);
    }

    protected function renderForeachBlocks(string $text): string
    {
        return $this->renderNestedTag(
            'foreach',
            $text,
            fn(array $attributes, string $content): string => $this->foreach($attributes, $content)
        );
    }

    protected function renderIfBlocks(string $text): string
    {
        return $this->renderNestedTag(
            'if',
            $text,
            fn(array $attributes, string $content): string => $this->conditional($this->condition($attributes), $content)
        );
    }

    /**
     * Manually scans for balanced (properly nested) occurrences of the given
     * tag name, since same-named tags nested inside each other (e.g. an
     * `if` inside another `if`) cannot be matched reliably with a single
     * non-recursive regex.
     */
    protected function renderNestedTag(string $tagName, string $text, callable $render): string
    {
        $openTag = '<kb:' . $tagName;
        $closeTag = '</kb:' . $tagName;

        while (($start = $this->tagOccurrence($text, $openTag, 0)) !== false) {
            $openingEnd = $this->tagEnd($text, $start);
            if ($openingEnd === false) {
                break;
            }

            $depth = 1;
            $cursor = $openingEnd + 1;
            while ($depth > 0) {
                $nextOpening = $this->tagOccurrence($text, $openTag, $cursor);
                $nextClosing = $this->tagOccurrence($text, $closeTag, $cursor);
                if ($nextClosing === false) {
                    return $text;
                }

                if ($nextOpening !== false && $nextOpening < $nextClosing) {
                    $depth++;
                    $cursor = $nextOpening + strlen($openTag);
                } else {
                    $depth--;
                    $cursor = $nextClosing + strlen($closeTag);
                }
            }

            $closingStart = $cursor - strlen($closeTag);
            $closingEnd = strpos($text, '>', $closingStart);
            if ($closingEnd === false) {
                break;
            }

            $opening = substr($text, $start, $openingEnd - $start + 1);
            $content = substr($text, $openingEnd + 1, $closingStart - $openingEnd - 1);
            $attributes = $this->attributes(trim(substr($opening, strlen($openTag), -1)));
            $replacement = $render($attributes, $content);
            $text = substr_replace($text, $replacement, $start, $closingEnd - $start + 1);
        }

        return $text;
    }

    /**
     * Finds the next occurrence of an exact tag name, ignoring longer
     * tag names that share the same prefix (e.g. `if-field` vs `if`)
     */
    protected function tagOccurrence(string $text, string $tag, int $offset): int|false
    {
        while (($position = stripos($text, $tag, $offset)) !== false) {
            $next = $text[$position + strlen($tag)] ?? '';
            if ($next === '' || $next === '>' || $next === '/' || ctype_space($next) === true) {
                return $position;
            }

            $offset = $position + strlen($tag);
        }

        return false;
    }

    protected function tagEnd(string $text, int $start): int|false
    {
        $quote = null;
        $length = strlen($text);

        for ($index = $start; $index < $length; $index++) {
            $character = $text[$index];
            if ($quote !== null) {
                if ($character === $quote) {
                    $quote = null;
                }
            } elseif ($character === '"' || $character === "'") {
                $quote = $character;
            } elseif ($character === '>') {
                return $index;
            }
        }

        return false;
    }

    protected function tag(string $name, array $attributes, string $content = '', bool $selfClosing = false): string
    {
        return match (strtolower($name)) {
            'title' => $this->title($attributes),
            'field' => $this->field($attributes),
            'email' => $this->email($attributes),
            'tel' => $this->tel($attributes),
            'date' => $this->date($attributes, $content, $selfClosing),
            'js' => $this->js($attributes),
            'css' => $this->css($attributes),
            'excerpt' => $this->excerpt($attributes),
            'prev-title' => $this->adjacentTitle('prevListed'),
            'next-title' => $this->adjacentTitle('nextListed'),
            'prev' => $this->adjacentLink('prevListed', $attributes, $content),
            'next' => $this->adjacentLink('nextListed', $attributes, $content),
            'permlink' => $this->page?->url() ?? '',
            'link' => $this->headLink($attributes),
            'blocks' => $this->blocks($attributes),
            'a' => $this->link($attributes, $content),
            'img', 'image' => $this->image($attributes),
            'video' => $this->video($attributes),
            'vimeo' => $this->vimeo($attributes),
            'youtube' => $this->youtube($attributes),
            'qr' => $this->qr($attributes),
            'gist' => $this->gist($attributes),
            'svg' => $this->svg($attributes),
            'section' => $this->page?->parent()?->title()->esc()->value() ?? '',
            'breadcrumb' => $this->breadcrumb($attributes),
            'tags' => $this->tags($attributes),
            'if' => $this->conditional($this->condition($attributes), $content),
            'if-field' => $this->conditional($this->fieldIsNotEmpty($attributes['name'] ?? ''), $content),
            'foreach' => $this->foreach($attributes, $content),
            'structure' => $this->structure($attributes, $content),
            'php' => $this->php($content),
            'snippet' => $this->snippet($attributes, $content),
            'pages' => $this->pages($attributes, $content),
            default => '',
        };
    }

    protected function title(array $attributes): string
    {
        $page = $this->targetUrl($attributes);

        return $page?->title()->value() ?? '';
    }

    protected function field(array $attributes): string
    {
        $field = $attributes['name'] ?? '';
        if ($this->page === null || $field === '') {
            return '';
        }

        $value = $this->page->{$field}();

        if (($attributes['kt'] ?? 'true') === 'true') {
            return (string) $value->kt();
        }

        return $value->value();
    }

    protected function email(array $attributes): string
    {
        $email = $this->fieldOrValue($attributes, 'address');
        // no address given, or the field it points to is missing/empty
        if ($email === '') {
            return '';
        }

        $emailAttributes = [
            ...$this->evaluatedAttributes($attributes, ['class', 'rel', 'target', 'title']),
            ...$this->dataAttributes($attributes),
        ];

        return Html::email($email, $attributes['text'] ?? null, $emailAttributes);
    }

    protected function date(array $attributes, string $content, bool $selfClosing = false): string
    {
        $format = $attributes['format'] ?? $this->dateFormat;

        if (isset($attributes['field']) === false) {
            $formatted = date($format);

            if ($selfClosing === true) {
                return $formatted;
            }

            $tagAttributes = [
                ...$this->evaluatedAttributes($attributes, ['class']),
                ...$this->dataAttributes($attributes),
            ];
            $label = trim($content);

            return Html::tag(
                'time',
                ($label !== '' ? $label . ' ' : '') . $formatted,
                [...$tagAttributes, 'datetime' => $formatted]
            );
        }

        $field = $attributes['field'];
        if ($this->page === null || $field === '') {
            return '';
        }

        $value = $this->page->{$field}();

        if ($selfClosing === true) {
            return $value->toDate($format);
        }

        $tagAttributes = [
            ...$this->evaluatedAttributes($attributes, ['class']),
            ...$this->dataAttributes($attributes),
        ];
        $label = trim($content);
        $formatted = $value->toDate($format);

        return Html::tag(
            'time',
            ($label !== '' ? $label . ' ' : '') . $formatted,
            [...$tagAttributes, 'datetime' => $value->toDate('c')]
        );
    }

    protected function tel(array $attributes): string
    {
        $tel = $this->fieldOrValue($attributes, 'number');
        $telAttributes = [
            ...$this->evaluatedAttributes($attributes, ['class', 'rel', 'target', 'title']),
            ...$this->dataAttributes($attributes),
        ];

        return Html::tel($tel, $attributes['text'] ?? null, $telAttributes);
    }

    /**
     * Resolves an attribute to a plain string, either from a page field
     * (via the `field` attribute), a literal string, or a $-expression
     */
    protected function fieldOrValue(array $attributes, string $attribute): string
    {
        if (($attributes['field'] ?? '') !== '') {
            return $this->value($attributes['field']);
        }

        $value = $this->snippetVariable($attributes[$attribute] ?? '');

        return match (true) {
            is_object($value) && method_exists($value, 'value') => (string) $value->value(),
            default => (string) $value,
        };
    }

    protected function js(array $attributes): string
    {
        $urls = $this->assetUrls($attributes, $this->jsPath);
        if ($urls === []) {
            return '';
        }

        return (string) js($urls);
    }

    protected function css(array $attributes): string
    {
        $urls = $this->assetUrls($attributes, $this->cssPath);
        if ($urls === []) {
            return '';
        }

        return (string) css($urls);
    }

    protected function assetUrls(array $attributes, string $prefix): array
    {
        $files = $attributes['files'] ?? $attributes['src'] ?? '';
        if ($files === '') {
            return [];
        }

        return array_values(array_filter(array_map(function (string $url) use ($prefix): string {
            $url = trim($url);
            if ($url === '' || $url === '@auto' || str_starts_with($url, '/') || str_starts_with($url, $prefix . '/') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) === 1) {
                return $url;
            }

            return $prefix . '/' . $url;
        }, explode(',', $files)), fn ($url) => $url !== ''));
    }

    protected function evaluatedAttributes(array $attributes, array $names): array
    {
        $result = [];
        foreach ($names as $name) {
            if (isset($attributes[$name])) {
                $value = $attributes[$name];
                // bare attributes (no "=") are parsed as bool true; keep as-is so Html::attr renders them valueless
                $result[$name] = is_string($value) === true ? $this->snippetVariable($value) : $value;
            }
        }

        return $result;
    }

    /**
     * Collects data-* and aria-* attributes so tags can pass through arbitrary data/aria attributes
     */
    protected function dataAttributes(array $attributes): array
    {
        $names = array_filter(
            array_keys($attributes),
            fn ($name) => str_starts_with($name, 'data-') || str_starts_with($name, 'aria-')
        );

        return $this->evaluatedAttributes($attributes, $names);
    }

    protected function headLink(array $attributes): string
    {
        $href = $attributes['href'] ?? '';
        if ($href === '') {
            return '';
        }

        $linkAttributes = ['href' => url($href)];
        foreach ($attributes as $name => $value) {
            if ($name !== 'href') {
                $linkAttributes[$name] = $value;
            }
        }

        return Html::tag('link', [], $linkAttributes);
    }

    protected function blocks(array $attributes): string
    {
        $field = $attributes['field'] ?? '';
        $source = $field !== '' && $this->page !== null
            ? $this->page->{$field}()
            : $this->snippetVariable($attributes['src'] ?? '');

        if ($source === '') {
            return '';
        }

        if ($source instanceof \Kirby\Content\Field) {
            return (string) $source->toBlocks();
        }

        return function_exists('blocks')
            ? (string) blocks($source)
            : (string) $source;
    }

    protected function svg(array $attributes): string
    {
        $path = $this->snippetVariable($attributes['src'] ?? '');
        if ($path === '') {
            return '';
        }

        return function_exists('svg')
            ? (string) svg($path)
            : '';
    }

    protected function video(array $attributes): string
    {
        $options = $this->snippetVariable($attributes['options'] ?? '');
        $videoAttributes = $this->snippetVariable($attributes['attr'] ?? '');
        $poster = $this->snippetVariable($attributes['poster'] ?? '');

        $options = is_array($options) === true ? $options : [];
        $videoAttributes = is_array($videoAttributes) === true ? $videoAttributes : [];

        if (is_object($poster) === true && method_exists($poster, 'url') === true) {
            $poster = $poster->url();
        }

        if (is_string($poster) === true && $poster !== '') {
            $videoAttributes['poster'] = $poster;
        }

        return video($this->fieldOrValue($attributes, 'url'), $options, $videoAttributes) ?? '';
    }

    protected function vimeo(array $attributes): string
    {
        $options = $this->snippetVariable($attributes['options'] ?? '');
        $videoAttributes = $this->snippetVariable($attributes['attr'] ?? '');

        return vimeo(
            $this->fieldOrValue($attributes, 'url'),
            is_array($options) === true ? $options : [],
            is_array($videoAttributes) === true ? $videoAttributes : []
        ) ?? '';
    }

    protected function youtube(array $attributes): string
    {
        $options = $this->snippetVariable($attributes['options'] ?? '');
        $videoAttributes = $this->snippetVariable($attributes['attr'] ?? '');

        return youtube(
            $this->fieldOrValue($attributes, 'url'),
            is_array($options) === true ? $options : [],
            is_array($videoAttributes) === true ? $videoAttributes : []
        ) ?? '';
    }

    protected function qr(array $attributes): string
    {
        $data = $this->snippetVariable($attributes['data'] ?? '');

        if (
            is_string($data) === false &&
            $data instanceof \Kirby\Cms\ModelWithContent === false
        ) {
            return '';
        }

        return (string) qr($data);
    }

    protected function gist(array $attributes): string
    {
        $url = $this->fieldOrValue($attributes, 'url');
        $file = $this->fieldOrValue($attributes, 'file');

        return $url === '' ? '' : gist($url, $file !== '' ? $file : null);
    }

    protected function link(array $attributes, string $content): string
    {
        $target = $this->targetUrl($attributes);
        $url = $this->snippetVariable($attributes['url'] ?? '');
        if ($target === null && $url === '') {
            return '';
        }

        $label = $content !== ''
            ? $content
            : ($target !== null && method_exists($target, 'title') ? $target->title()->esc()->value() : '');
        $linkAttributes = ['href' => $target?->url() ?? $url];

        foreach ($attributes as $name => $value) {
            if (
                in_array($name, ['class', 'style', 'target', 'rel', 'title'], true) ||
                str_starts_with($name, 'data-') === true ||
                str_starts_with($name, 'aria-') === true
            ) {
                $linkAttributes[$name] = $this->snippetVariable($value);
            }
        }

        return Html::tag('a', [$label], $linkAttributes);
    }

    protected function image(array $attributes): string
    {
        $image = $attributes['src'] ?? $attributes['image'] ?? '';
        $image = $this->snippetVariable($image);

        if (is_string($image) === true && $image !== '') {
            $file = $this->page?->files()->filter(
                fn ($file) => $file->url() === $image
            )->first();
            $image = $file ?? asset($image);
        }

        if (
            is_object($image) === false
            || (method_exists($image, 'html') === false && method_exists($image, 'url') === false)
        ) {
            return '';
        }

        $width = $attributes['width'] ?? '';
        $height = $attributes['height'] ?? '';
        if (
            ctype_digit($width) === true
            && ctype_digit($height) === true
            && (int) $width > 0
            && (int) $height > 0
        ) {
            $mode = strtolower((string) ($attributes['mode'] ?? 'resize'));
            $format = $attributes['format'] ?? '';
            $quality = isset($attributes['quality']) && ctype_digit($attributes['quality'])
                ? (int) $attributes['quality']
                : null;
            $options = array_filter([
                'format' => $format !== '' ? $this->snippetVariable($format) : null,
                'quality' => $quality
            ], static fn ($value) => $value !== null);

            $image = match ($mode) {
                'crop' => method_exists($image, 'crop')
                    ? $image->crop((int) $width, (int) $height, $options !== [] ? $options : null)
                    : $image,
                'resize' => method_exists($image, 'thumb')
                    ? $image->thumb([
                        'width' => (int) $width,
                        'height' => (int) $height,
                        ...$options
                    ])
                    : $image,
                default => $image,
            };
        }

        $imageAttributes = $this->evaluatedAttributes(
            $attributes,
            ['alt', 'class', 'height', 'loading', 'sizes', 'width']
        );

        $ratio = $this->snippetVariable($attributes['ratio'] ?? 'auto');
        $ratio = is_object($ratio) && method_exists($ratio, 'value') ? $ratio->value() : $ratio;
        $contain = $this->snippetVariable($attributes['contain'] ?? false);
        $imageAttributes['style'] = 'aspect-ratio: ' . $ratio . '; object-fit: ' . ($contain ? 'contain' : 'cover');

        return method_exists($image, 'html')
            ? $image->html($imageAttributes)
            : Html::img($image->url(), $imageAttributes);
    }

    protected function targetUrl(array $attributes): object|null
    {
        $target = isset($attributes['url']) && $attributes['url'] !== ''
            ? $this->snippetVariable($attributes['url'])
            : $this->page;

        if (is_string($target) === true) {
            $target = page($target);
        }

        return is_object($target)
            && method_exists($target, 'url')
            ? $target
            : null;
    }

    protected function value(string $field): string
    {
        if ($this->page === null || $field === '') {
            return '';
        }

        // field may not exist or hold no value, which resolves to null
        return (string) ($this->page->{$field}()->value() ?? '');
    }

    protected function adjacentTitle(string $method): string
    {
        if ($this->page === null || method_exists($this->page, $method) === false) {
            return '';
        }

        $page = $this->page->{$method}();

        return $page?->title()->esc()->value() ?? '';
    }

    protected function adjacentLink(string $method, array $attributes, string $content): string
    {
        if ($this->page === null || method_exists($this->page, $method) === false) {
            return '';
        }

        $page = $this->page->{$method}();
        if ($page === null) {
            return '';
        }

        $label = $content !== '' ? $content : $page->title()->esc()->value();
        $linkAttributes = ['href' => $page->url()];

        foreach ($attributes as $name => $value) {
            if (
                in_array($name, ['class', 'style', 'target', 'rel', 'title'], true) ||
                str_starts_with($name, 'data-') === true ||
                str_starts_with($name, 'aria-') === true
            ) {
                $linkAttributes[$name] = $this->snippetVariable($value);
            }
        }

        return Html::tag('a', [$label], $linkAttributes);
    }

    protected function excerpt(array $attributes): string
    {
        $field = $attributes['field'] ?? 'excerpt';

        if ($this->page === null || $field === '') {
            return '';
        }

        $chars = isset($attributes['chars']) && ctype_digit($attributes['chars'])
            ? (int) $attributes['chars']
            : 120;
        $strip = ($attributes['strip'] ?? 'true') !== 'false';
        $rep = $attributes['rep'] ?? ' …';
        $renderKirbyText = ($attributes['kt'] ?? 'true') === 'true';

        $value = $this->page->{$field}();

        try {
            $excerpt = $value->toBlocks()->excerpt($chars, $strip, $rep);
        } catch (\Throwable) {
            $excerpt = $value->excerpt($chars, $strip, $rep)->value();
        }

        return $renderKirbyText === true ? kt($excerpt) : $excerpt;
    }

    protected function fieldIsNotEmpty(string $field): bool
    {
        return $field !== '' && $this->page !== null && method_exists($this->page, $field)
            && $this->page->{$field}()->isNotEmpty();
    }

    protected function condition(array $attributes): bool
    {
        $value = $attributes['condition'] ?? $attributes['test'] ?? '';

        if ($value === '') {
            return false;
        }

        if (preg_match('/^\$([a-z_][a-z0-9_]*)\s*=\s*(.+)$/i', trim($value), $match) === 1) {
            $this->data[$match[1]] = $this->snippetVariable($match[2]);
            $value = $this->data[$match[1]];
        } else {
            $value = $this->snippetVariable($value);
        }

        if (is_string($value) === true) {
            $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $boolean ?? $value !== '';
        }

        return (bool) $value;
    }

    protected function conditional(bool $condition, string $content): string
    {
        $branches = preg_split('/<kb:else\s*\/\s*>/i', $content, 2);
        $selected = $condition ? ($branches[0] ?? '') : ($branches[1] ?? '');

        return $this->renderText($selected);
    }

    protected function foreach(array $attributes, string $content): string
    {
        $items = $attributes['items'] ?? $attributes['in'] ?? '';
        $variable = $attributes['as'] ?? 'item';

        if ($items === '' || preg_match('/^[a-z_][a-z0-9_]*$/i', $variable) !== 1) {
            return '';
        }

        $items = $this->snippetVariable($items);
        if (is_iterable($items) === false) {
            return '';
        }

        $output = '';
        foreach ($items as $item) {
            $data = [...$this->data, $variable => $item];
            $renderer = new self($this->page, $data);
            $nestedContent = preg_replace_callback(
                '/<kb:foreach((?:\s+[a-z][a-z0-9_-]*(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?)*\s*)>(.*?)<\/kb:foreach\s*>/is',
                fn(array $match): string => $renderer->foreach(
                    $renderer->attributes($match[1] ?? ''),
                    $match[2] ?? ''
                ),
                $content
            );
            $output .= $renderer->renderText($nestedContent);
        }

        return $output;
    }

    protected function structure(array $attributes, string $content): string
    {
        $field = $attributes['field'] ?? '';
        $variable = $attributes['as'] ?? 'item';

        if (
            $this->page === null
            || $field === ''
            || preg_match('/^[a-z_][a-z0-9_]*$/i', $variable) !== 1
        ) {
            return '';
        }

        $items = $this->page->{$field}()->toStructure();
        return $this->renderItems($items, $variable, $content);
    }

    protected function php(string $content): string
    {
        $content = preg_replace('/^\s*<\?php\s*/', '', $content);
        $content = preg_replace('/\s*\?>\s*$/', '', $content);

        ob_start();
        try {
            extract($this->snippetData(), EXTR_SKIP);
            eval ($content);
            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }

    protected function snippet(array $attributes, string $content = ''): string
    {
        $name = $attributes['name'] ?? '';

        if ($name === '') {
            return '';
        }

        $variables = [];
        foreach ($attributes as $key => $value) {
            if ($key !== 'name') {
                $variables[$key] = $this->snippetVariable($value);
            }
        }

        $data = [...$this->snippetData(), ...$variables];

        if ($content === '') {
            return (string) snippet($name, $data, true);
        }

        $snippet = snippet($name, $data, true, true);

        return $snippet instanceof \Kirby\Template\Snippet
            ? $snippet->render([], ['default' => $content])
            : (string) $snippet;
    }

    protected function snippetVariable(string $value): mixed
    {
        if (preg_match('/^\$[a-z_][a-z0-9_]*(?:(?:->|\?->|\[)|$)/i', trim($value)) !== 1) {
            return $value;
        }

        extract($this->snippetData(), EXTR_SKIP);

        return eval('return ' . $value . ';');
    }

    protected function snippetData(): array
    {
        return [
            ...$this->data,
            'page' => $this->page,
            'site' => $this->page?->kirby()->site()
        ];
    }

    protected function breadcrumb(array $attributes): string
    {
        if ($this->page === null) {
            return '';
        }

        $pages = $this->page->kirby()->site()->breadcrumb();
        $items = [];
        foreach ($pages as $index => $page) {
            $title = $page->title()->esc()->value();
            $item = $index === $pages->count() - 1
                ? Html::tag('span', $title, ['aria-current' => 'page'])
                : Html::tag('a', $title, ['href' => $page->url()]);
            $items[] = Html::tag('li', [$item]);
        }

        return Html::tag(
            'nav',
            [Html::tag('ol', [implode(' / ', $items)])],
            ['aria-label' => 'Breadcrumb']
        );
    }

    protected function tags(array $attributes): string
    {
        $field = $attributes['field'] ?? 'tags';
        if ($this->page === null || $field === '') {
            return '';
        }

        $tags = $this->page->{$field}()->split();
        if ($tags === []) {
            return '';
        }

        $parent = $this->page->parent();
        $wrapTag = trim((string) ($attributes['wraptag'] ?? 'ul'));
        $breakTag = trim((string) ($attributes['breaktag'] ?? 'li'));
        $breakClass = trim((string) ($attributes['breakclass'] ?? ''));
        $output = '';
        foreach ($tags as $tag) {
            $item = Html::tag('a', $tag, [
                'href' => $parent?->url(['params' => ['tag' => $tag]]) ?? '',
            ]);

            if ($breakTag !== '') {
                $item = Html::tag($breakTag, [$item], $breakClass !== '' ? ['class' => $breakClass] : []);
            }

            $output .= $item;
        }

        $wrapClass = trim((string) ($attributes['wrapclass'] ?? $attributes['class'] ?? 'tags'));
        if ($wrapTag !== '') {
            return Html::tag($wrapTag, [$output], $wrapClass !== '' ? ['class' => $wrapClass] : []);
        }

        return $output;
    }

    protected function pages(array $attributes, string $content): string
    {
        $section = $attributes['section'] ?? null;
        $source = null;

        if ($section !== null && $section !== '') {
            $section = $this->snippetVariable($section);
            if (is_string($section) === true && strtolower((string) $section) === 'site') {
                $source = $this->page?->kirby()->site();
            } elseif (is_string($section) === true) {
                $source = page($section);
            } elseif (is_object($section) && method_exists($section, 'children')) {
                $source = $section;
            }
        }

        if ($source === null) {
            $source = $this->page;
        }

        if ($source === null || method_exists($source, 'children') === false) {
            return '';
        }

        $mode = strtolower(trim((string) ($attributes['mode'] ?? 'listed')));
        $articles = match ($mode) {
            'unlisted' => $source->children()->unlisted(),
            'both' => $source->children(),
            default => $source->children()->listed(),
        };

        $sort = strtolower(trim((string) ($attributes['sort'] ?? '')));
        if ($sort === 'asc' || $sort === 'desc') {
            $articles = $articles->sortBy('title', $sort, SORT_NATURAL | SORT_FLAG_CASE);
        }

        if (isset($attributes['offset']) && ctype_digit($attributes['offset'])) {
            $articles = $articles->offset((int) $attributes['offset']);
        }

        if (isset($attributes['limit']) && ctype_digit($attributes['limit'])) {
            $articles = $articles->limit((int) $attributes['limit']);
        }

        $variable = $attributes['as'] ?? 'page';
        $wrapTag = trim((string) ($attributes['wraptag'] ?? 'ul'));
        $breakTag = trim((string) ($attributes['breaktag'] ?? 'li'));
        $wrapClass = trim((string) ($attributes['class'] ?? ''));
        $breakClass = trim((string) ($attributes['breakclass'] ?? ''));

        if ($content === '') {
            $content = Html::tag(
                'a',
                ['{{ $' . $variable . '->title()->esc() }}'],
                ['href' => [
                    'value' => '{{ $' . $variable . '->url() }}',
                    'escape' => false,
                ]]
            );
        }

        $output = '';
        foreach ($articles as $item) {
            $data = [...$this->data, $variable => $item];
            $renderer = new self($item, $data);
            $itemContent = $renderer->renderText($content);

            if ($breakTag !== '') {
                $itemContent = Html::tag($breakTag, [$itemContent], $breakClass !== '' ? ['class' => $breakClass] : []);
            }

            $output .= $itemContent;
        }

        if ($wrapTag !== '') {
            return Html::tag($wrapTag, [$output], $wrapClass !== '' ? ['class' => $wrapClass] : []);
        }

        return $output;
    }

    protected function renderItems(iterable $items, string $variable, string $content, bool $itemIsPage = false): string
    {
        $output = '';
        foreach ($items as $item) {
            $page = $itemIsPage ? $item : $this->page;
            $data = $variable === '' ? $this->data : [...$this->data, $variable => $item];
            $output .= (new self($page, $data))->renderText($content);
        }

        return $output;
    }

    protected function attributes(string $source): array
    {
        preg_match_all('/([a-z][a-z0-9_-]*)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s]+)))?/i', $source, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $match) {
            $value = $match[2] ?? $match[3] ?? $match[4] ?? true;
            $attributes[strtolower($match[1])] = is_string($value) === true
                ? (preg_match('/^\s*\$/', $value) === 1 ? $value : $this->interpolate($value))
                : $value;
        }

        return $attributes;
    }

    protected function interpolate(string $text, bool $escape = false): string
    {
        return preg_replace_callback(
            '/\{\{\s*(\$[a-z_][a-z0-9_]*(?:(?:->|\?->)[a-z_][a-z0-9_]*\([^{}]*\)|\[[^{}]*\])*)\s*\}\}/i',
            function (array $match) use ($escape): string {
                $value = (string) $this->snippetVariable($match[1]);

                return $escape === true
                    ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    : $value;
            },
            $text
        );
    }
}