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

namespace BroCode\WebShare\Test\Unit\Model;

use BroCode\WebShare\Model\ShareLinkBuilder;
use PHPUnit\Framework\TestCase;

class ShareLinkBuilderTest extends TestCase
{
    private const URL = 'https://shop.example/driven-backpack.html';

    public function testPlaceholdersAreFilledUrlEncoded(): void
    {
        $links = (new ShareLinkBuilder())->build(
            ['x' => ['label' => 'X', 'url_template' => 'https://x.com/intent/post?text={{title}}&url={{url}}']],
            ['url' => self::URL, 'title' => 'Driven Backpack & more', 'text' => '', 'image' => '']
        );

        self::assertSame(
            'https://x.com/intent/post?text=Driven%20Backpack%20%26%20more&url=https%3A%2F%2Fshop.example%2Fdriven-backpack.html',
            $links[0]['href']
        );
        self::assertSame(['code' => 'x', 'label' => 'X'], array_diff_key($links[0], ['href' => true]));
    }

    public function testMailtoTemplatesAreKept(): void
    {
        $links = (new ShareLinkBuilder())->build(
            ['email' => ['label' => 'Email', 'url_template' => 'mailto:?subject={{title}}&body={{url}}']],
            ['url' => self::URL, 'title' => 'Backpack', 'text' => '', 'image' => '']
        );

        self::assertSame('mailto:?subject=Backpack&body=https%3A%2F%2Fshop.example%2Fdriven-backpack.html', $links[0]['href']);
    }

    public function testOnlyHttpHttpsAndMailtoTemplatesAreRendered(): void
    {
        $links = (new ShareLinkBuilder())->build(
            [
                'js' => ['label' => 'Evil', 'url_template' => 'javascript:alert({{url}})'],
                'data' => ['label' => 'Data', 'url_template' => 'data:text/html,{{url}}'],
                'rel' => ['label' => 'Relative', 'url_template' => '/share?u={{url}}'],
                'mixed' => ['label' => 'Mixed case', 'url_template' => ' JavaScript:alert(1)'],
                'ok' => ['label' => 'Plain', 'url_template' => 'http://share.example/?u={{url}}'],
            ],
            ['url' => self::URL, 'title' => 'T', 'text' => '', 'image' => '']
        );

        self::assertSame(['ok'], array_column($links, 'code'));
    }

    public function testEmptyImageLeavesAnEmptyParameter(): void
    {
        $links = (new ShareLinkBuilder())->build(
            ['p' => ['label' => 'Pinterest', 'url_template' => 'https://www.pinterest.com/pin/create/button/?url={{url}}&media={{image}}']],
            ['url' => self::URL, 'title' => 'T', 'text' => '', 'image' => '']
        );

        self::assertStringEndsWith('&media=', $links[0]['href']);
    }

    public function testIncompleteRowsAreSkippedAndOrderIsKept(): void
    {
        $links = (new ShareLinkBuilder())->build(
            [
                'b' => ['label' => 'B', 'url_template' => 'https://b.example/?u={{url}}'],
                'nolabel' => ['label' => ' ', 'url_template' => 'https://c.example/?u={{url}}'],
                'notemplate' => ['label' => 'D'],
                'a' => ['label' => 'A', 'url_template' => 'https://a.example/?u={{url}}'],
                'broken' => 'not-an-array',
            ],
            ['url' => self::URL, 'title' => 'T', 'text' => '', 'image' => '']
        );

        self::assertSame(['b', 'a'], array_column($links, 'code'));
    }

    public function testTextPlaceholderCarriesTheConfiguredShareText(): void
    {
        $links = (new ShareLinkBuilder())->build(
            ['whatsapp' => ['label' => 'WhatsApp', 'url_template' => 'https://wa.me/?text={{text}}%20{{url}}']],
            ['url' => self::URL, 'title' => 'Backpack', 'text' => 'Look: Backpack', 'image' => '']
        );

        self::assertSame('https://wa.me/?text=Look%3A%20Backpack%20https%3A%2F%2Fshop.example%2Fdriven-backpack.html', $links[0]['href']);
    }

    public function testRowsSwitchedOffAreSkippedAndRowsWithoutTheSwitchStayActive(): void
    {
        $links = (new ShareLinkBuilder())->build(
            [
                'on' => ['label' => 'On', 'url_template' => 'https://on.example/?u={{url}}', 'active' => '1'],
                'off' => ['label' => 'Off', 'url_template' => 'https://off.example/?u={{url}}', 'active' => '0'],
                'legacy' => ['label' => 'Legacy', 'url_template' => 'https://legacy.example/?u={{url}}'],
            ],
            ['url' => self::URL, 'title' => 'T', 'text' => '', 'image' => '']
        );

        self::assertSame(['on', 'legacy'], array_column($links, 'code'));
    }
}
