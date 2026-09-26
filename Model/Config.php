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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const PATH = 'catalog/web_share/';

    /**
     * Where the button can appear, mapped to the flag that switches it on there.
     * The CMS widget ("page") only needs the master switch.
     */
    private const PLACEMENT_FLAGS = [
        'product' => 'on_product',
        'category' => 'on_category',
        'page' => null,
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $json;

    public function __construct(ScopeConfigInterface $scopeConfig, Json $json)
    {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
    }

    public function isEnabledFor(string $source): bool
    {
        if (!array_key_exists($source, self::PLACEMENT_FLAGS) || !$this->flag('enabled')) {
            return false;
        }
        $flag = self::PLACEMENT_FLAGS[$source];

        return $flag === null || $this->flag($flag);
    }

    public function isCopyLinkEnabled(): bool
    {
        return $this->flag('copy_link');
    }

    /**
     * @return array<string, mixed> rows keyed by code, as stored by the dynamic-rows field
     */
    public function getProviders(): array
    {
        $value = (string) $this->scopeConfig->getValue(self::PATH . 'providers', ScopeInterface::SCOPE_STORE);
        if ($value === '') {
            return [];
        }
        try {
            $rows = $this->json->unserialize($value);
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }

    private function flag(string $field): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH . $field, ScopeInterface::SCOPE_STORE);
    }
}
