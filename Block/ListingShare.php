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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AwareInterface as ProductAwareInterface;

/**
 * The share icon on a product tile. Luma's add-to container hands every tile's product to its
 * children through setProduct(), and one block instance renders all tiles, so the resolved
 * share data is reset for each product.
 */
class ListingShare extends Share implements ProductAwareInterface
{
    /**
     * @var string
     */
    protected $_template = 'BroCode_WebShare::share-listing.phtml';

    /**
     * @var ProductInterface|null
     */
    private $product;

    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        $this->shareData = false;

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function getSource(): string
    {
        return 'listing';
    }

    protected function resolveShareData(): ?array
    {
        if ($this->product === null) {
            return null;
        }

        return [
            'url' => (string) $this->product->getProductUrl(),
            'title' => (string) $this->product->getName(),
            // The grid image is already generated for the tile; a larger role would resize per product.
            'image' => (string) $this->imageHelper->init($this->product, 'category_page_grid')->getUrl(),
        ];
    }
}
