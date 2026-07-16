<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Tiny file-based email templating.
 *
 * Reads a per-notification body template (HTML + text) from
 * resources/mail-templates/{name}.{html,txt}, substitutes {{ VAR }} markers,
 * then wraps the result in the shared layout.{html,txt} via its {{ CONTENT }}
 * marker. This keeps email copy out of PHP and easy to edit.
 */
class EmailTemplate
{
    /**
     * Render a template into both HTML and plain-text, wrapped in the layout.
     *
     * @return array{html: string, text: string}
     */
    public static function render(string $template, array $vars = []): array
    {
        $globals = [
            'APP_NAME' => config('app.name', 'WRBLO'),
            'YEAR'     => date('Y'),
            'LOGO_URL' => self::logoSrc(),
            'APP_URL'  => rtrim((string) config('app.frontend_url'), '/'),
        ];
        $vars = array_merge($globals, $vars);

        return [
            'html' => self::renderInLayout($template, 'html', $vars),
            'text' => self::renderInLayout($template, 'txt', $vars),
        ];
    }

    private static function renderInLayout(string $template, string $ext, array $vars): string
    {
        $body   = self::substitute(self::read($template, $ext), $vars);
        $layout = self::substitute(self::read('layout', $ext), $vars + ['CONTENT' => $body]);

        return self::stripLeftovers($layout);
    }

    /**
     * Prefer an embedded (cid) logo: TemplatedMail attaches the local file
     * inline, so the image travels WITH the email instead of being fetched
     * from a URL that real recipients (and Gmail's image proxy) can't reach.
     */
    private static function logoSrc(): string
    {
        $path = config('mail.logo_path');

        if ($path && is_file($path)) {
            return 'cid:wrblo-logo';
        }

        return (string) config('mail.logo_url'); // hosted fallback
    }

    private static function read(string $name, string $ext): string
    {
        $path = resource_path("mail-templates/{$name}.{$ext}");

        return File::exists($path) ? File::get($path) : '';
    }

    private static function substitute(string $content, array $vars): string
    {
        foreach ($vars as $key => $value) {
            // Tolerate both "{{ KEY }}" and "{{KEY}}"
            $content = str_replace(["{{ {$key} }}", "{{{$key}}}"], (string) $value, $content);
        }

        return $content;
    }

    // Remove any placeholders that were never supplied a value
    private static function stripLeftovers(string $content): string
    {
        return preg_replace('/\{\{\s*[A-Z0-9_]+\s*\}\}/', '', $content) ?? $content;
    }
}
