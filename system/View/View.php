<?php

declare(strict_types=1);

namespace System\View;

class View
{
    public function __construct(private string $basePath)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string
    {
        $file = $this->resolvePath($view);

        if (!is_file($file)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        $variables = [];
        foreach ($data as $key => $value) {
            $variables[$key] = Escaper::wrap($value);
        }

        extract($variables, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderWithLayout(string $view, array $data, string $layout): string
    {
        $content = $this->render($view, $data);
        $layoutData = $data;
        $layoutData['content'] = new RawHtml($content);
        return $this->render($layout, $layoutData);
    }

    private function resolvePath(string $view): string
    {
        return rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
