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

use BroCode\WebShare\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testProvidersAreDecodedFromTheStoredJson(): void
    {
        $config = $this->config(['catalog/web_share/providers' => '{"x":{"label":"X","url_template":"https://x.com/?u={{url}}"}}']);

        self::assertSame(['x' => ['label' => 'X', 'url_template' => 'https://x.com/?u={{url}}']], $config->getProviders());
    }

    public function testBrokenOrEmptyProviderValueGivesNoProviders(): void
    {
        self::assertSame([], $this->config(['catalog/web_share/providers' => '{broken'])->getProviders());
        self::assertSame([], $this->config(['catalog/web_share/providers' => ''])->getProviders());
        self::assertSame([], $this->config(['catalog/web_share/providers' => null])->getProviders());
    }

    public function testPlacementFlagsNeedTheMasterSwitch(): void
    {
        $off = $this->config([], ['catalog/web_share/enabled' => false, 'catalog/web_share/on_product' => true, 'catalog/web_share/on_category' => true]);
        $on = $this->config([], ['catalog/web_share/enabled' => true, 'catalog/web_share/on_product' => true, 'catalog/web_share/on_category' => false]);

        self::assertFalse($off->isEnabledFor('product'));
        self::assertFalse($off->isEnabledFor('page'));
        self::assertTrue($on->isEnabledFor('product'));
        self::assertFalse($on->isEnabledFor('category'));
        self::assertTrue($on->isEnabledFor('page'));
        self::assertFalse($on->isEnabledFor('unknown'));

        $lists = $this->config([], ['catalog/web_share/enabled' => true, 'catalog/web_share/on_listing' => true]);
        self::assertTrue($lists->isEnabledFor('listing'));
        self::assertFalse($on->isEnabledFor('listing'));
    }

    private function config(array $values, array $flags = []): Config
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn (string $path) => $values[$path] ?? null);
        $scopeConfig->method('isSetFlag')->willReturnCallback(fn (string $path) => $flags[$path] ?? false);

        return new Config($scopeConfig, new Json());
    }

    public function testShareTextIsTheStoredValueTrimmed(): void
    {
        self::assertSame('Look: {{title}}', $this->config(['catalog/web_share/share_text' => "  Look: {{title}} \n"])->getShareText());
        self::assertSame('', $this->config([])->getShareText());
    }
}
