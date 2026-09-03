<?php

namespace Kirby\Kb;

use Kirby\Template\Template as BaseTemplate;

class Template extends BaseTemplate
{
    public function extension(): string
    {
        return 'kb.php';
    }

    public function file(): string|null
    {
        $txp = parent::file();

        if ($txp !== null && str_ends_with($txp, '.kb.php')) {
            return $txp;
        }

        return (new BaseTemplate($this->name(), $this->type(), $this->defaultType()))->file();
    }

    public function render(array $data = []): string
    {
        $file = $this->file();

        if ($file === null || str_ends_with($file, '.kb.php') === false) {
            return (new BaseTemplate($this->name(), $this->type(), $this->defaultType()))->render($data);
        }

        return (new Renderer($data['page'] ?? null, $data))->render($file);
    }
}