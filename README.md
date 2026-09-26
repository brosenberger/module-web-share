# BroCode_WebShare

A **Share** button for Magento 2 product pages, category pages, product lists and CMS pages. On phones,
tablets and most desktop browsers it opens the device's own share sheet (WhatsApp,
Messages, AirDrop, Mail, …) through the browser's
[Web Share API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Share_API). Where the
browser has no share sheet, it opens a small panel of share links instead, configured in
the admin. No third-party scripts, no tracking pixels, no extra CSP entries.

```bash
composer require brocode/module-web-share
bin/magento module:enable BroCode_WebShare
bin/magento setup:upgrade
bin/magento cache:flush
```

In production mode, also run `setup:di:compile` and `setup:static-content:deploy` as
usual.

![The Share action next to "Add to Wish List" and "Add to Compare", with the fallback panel open on desktop and mobile](docs/images/share-dropdown.png)

## What it does

- **Looks like Luma's own actions.** The Share control sits in the product page's
  wishlist/compare row with the same grey, uppercase, icon-first styling, and on category
  pages below the description.
- **On every product tile, too.** Category listings, quick search and advanced search get
  a share icon next to the wishlist and compare icons of each tile, sharing that product's
  page. The link panel is rendered hidden there, so a list under every tile never appears.
- **Shares the right URL.** Products and categories share their own URL — not the address
  bar, so no tracking parameters and no layered-navigation filters or sorting. The CMS
  widget shares the page it is placed on, without the query string.
- **Native first, links second.** A tap opens the device's share sheet. If the browser has
  none, or sharing fails, the fallback panel opens. If the customer simply closes the share
  sheet, nothing else happens.
- **Works without JavaScript.** The share links are server-rendered; without JavaScript
  they are shown as a plain list on product and category pages.
- **Accessible.** A real `<button>` with `aria-expanded`, Escape closes the panel and
  returns focus, and "Link copied" is announced through a live region.

![The share icon next to wishlist and compare on product tiles, with the panel open on desktop and mobile](docs/images/share-product-list.png)

## Configuration

*Stores > Configuration > Catalog > Catalog > Web Share* (store view scope):

| Setting | Default |
|---|---|
| Enabled | Yes |
| Show on Product Pages | Yes |
| Show on Category Pages | Yes |
| Show on Product Lists | Yes (one icon per product tile) |
| Offer "Copy Link" in the Fallback | Yes (shown only where the Clipboard API is available) |
| Fallback Share Links | WhatsApp, Facebook, X, Pinterest, Email |

Each fallback link is a label and a URL template with these placeholders, URL-encoded on
output:

| Placeholder | Product page / tile | Category | CMS widget |
|---|---|---|---|
| `{{url}}` | product URL | category URL | page URL without query string |
| `{{title}}` | product name | category name | page title |
| `{{image}}` | main image (on tiles: the grid image already generated for the tile) | category image, if set | empty |

Add, remove and reorder rows freely. **Only `https:`, `http:` and `mailto:` templates are
rendered** — the templates end up in `href` attributes, so anything else (`javascript:`,
`data:`, relative paths) is dropped instead of output.

For CMS pages and blocks, insert the widget **Web Share Button**.

## Hooks for analytics and themes

Every share fires a `webshare:share` event on `document`:

```javascript
document.addEventListener('webshare:share', (e) => {
  // e.detail.method: "native", "copy", or the code of the fallback link that was clicked
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({ event: 'share', method: e.detail.method, content_url: e.detail.url });
});
```

The markup uses `.brocode-web-share`, `.brocode-web-share-toggle` and
`.brocode-web-share-links`; the panel is only positioned as a dropdown once JavaScript has
added `.is-enhanced`, and opens to the left (`.is-flipped`) when it would leave the viewport.

## Requirements and limits

- Magento 2.4.x, PHP 8.1–8.4. Verified on 2.4.8-p5 with Luma, desktop and mobile widths.
- **Luma-based themes.** The styles use Magento's LESS library only, so Blank-based themes
  compile too. Hyvä does not load RequireJS components and needs a small compatibility
  template (not included).
- **Browser support for the native share sheet varies.** Mobile browsers, Safari, Edge and
  Chrome on desktop broadly support it; Firefox on desktop only behind a preference. The
  fallback panel covers every browser without it.
- The page must be served over HTTPS; the Web Share and Clipboard APIs only exist in
  secure contexts.

## License

MIT, see [LICENSE](LICENSE).

If this saves you time: [buy me a coffee](https://www.buymeacoffee.com/brosenberger).
