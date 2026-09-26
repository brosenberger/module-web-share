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

namespace BroCode\WebShare\Block;

use BroCode\WebShare\Model\Config;
use BroCode\WebShare\Model\ShareLinkBuilder;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * The share button with its fallback links. The layout argument "share_source" says what is
 * shared: "product", "category" or "page" (the current page, used by the CMS widget). Products
 * and categories share their own URL, never the address bar with tracking or filter parameters.
 */
class Share extends Template
{
    /**
     * @var string
     */
    protected $_template = 'BroCode_WebShare::share.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ShareLinkBuilder
     */
    private $linkBuilder;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var array{url: string, title: string, text: string, image: string}|null|false false = not resolved yet
     */
    protected $shareData = false;

    public function __construct(
        Context $context,
        Config $config,
        ShareLinkBuilder $linkBuilder,
        CatalogHelper $catalogHelper,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->linkBuilder = $linkBuilder;
        $this->catalogHelper = $catalogHelper;
        $this->imageHelper = $imageHelper;
    }

    public function getSource(): string
    {
        return (string) ($this->getData('share_source') ?: 'page');
    }

    /**
     * @return array{url: string, title: string, text: string, image: string}|null
     */
    public function getShareData(): ?array
    {
        if ($this->shareData === false) {
            $data = $this->resolveShareData();
            if ($data !== null) {
                $data['text'] = trim(strtr($this->config->getShareText(), ['{{title}}' => $data['title']]));
            }
            $this->shareData = $data;
        }

        return $this->shareData;
    }

    /**
     * @return list<array{code: string, label: string, href: string}>
     */
    public function getLinks(): array
    {
        $data = $this->getShareData();

        return $data === null ? [] : $this->linkBuilder->build($this->config->getProviders(), $data);
    }

    public function isCopyLinkEnabled(): bool
    {
        return $this->config->isCopyLinkEnabled();
    }

    protected function _toHtml(): string
    {
        if (!$this->config->isEnabledFor($this->getSource()) || $this->getShareData() === null) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return array{url: string, title: string, image: string}|null
     */
    protected function resolveShareData(): ?array
    {
        switch ($this->getSource()) {
            case 'product':
                $product = $this->catalogHelper->getProduct();
                return $product === null ? null : [
                    'url' => (string) $product->getProductUrl(),
                    'title' => (string) $product->getName(),
                    'image' => (string) $this->imageHelper->init($product, 'product_page_image_large')->getUrl(),
                ];
            case 'category':
                $category = $this->catalogHelper->getCategory();
                return $category === null ? null : [
                    'url' => (string) $category->getUrl(),
                    'title' => (string) $category->getName(),
                    'image' => (string) ($category->getImageUrl() ?: ''),
                ];
            default:
                return [
                    'url' => strtok($this->_urlBuilder->getCurrentUrl(), '?#'),
                    'title' => (string) $this->pageConfig->getTitle()->get(),
                    'image' => '',
                ];
        }
    }
}
