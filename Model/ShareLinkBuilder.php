<?php
/**
 * Copyright (C) 2026 Benjamin Rosenberger <bensch.rosenberger@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @copyright 2026 Benjamin Rosenberger
 * @author bensch.rosenberger@gmail.com
 * @license MIT
 * @link https://brocode.at
 */
declare(strict_types=1);

namespace BroCode\WebShare\Model;

/**
 * Turns the admin's URL templates into fallback share links.
 *
 * Templates come from admin input and end up in href attributes, so only http:, https: and
 * mailto: templates are rendered; everything else (javascript:, data:, relative paths) is dropped.
 */
class ShareLinkBuilder
{
    private const ALLOWED_SCHEME = '/^(https?:\/\/|mailto:)/i';

    /**
     * @param array<string, mixed> $providers rows keyed by code: ['label' => ..., 'url_template' => ...]
     * @return list<array{code: string, label: string, href: string}>
     */
    public function build(array $providers, string $url, string $title, string $image): array
    {
        $replacements = [
            '{{url}}' => rawurlencode($url),
            '{{title}}' => rawurlencode($title),
            '{{image}}' => rawurlencode($image),
        ];
        $links = [];
        foreach ($providers as $code => $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $template = trim((string) ($row['url_template'] ?? ''));
            if ($label === '' || !preg_match(self::ALLOWED_SCHEME, $template)) {
                continue;
            }
            $links[] = ['code' => (string) $code, 'label' => $label, 'href' => strtr($template, $replacements)];
        }

        return $links;
    }
}
