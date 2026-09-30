# 🛡️ Gambio GProtector

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Gambio Core Modul](https://img.shields.io/badge/Gambio-Core_Module-green.svg)](#)
[![Security Protection](https://img.shields.io/badge/Security-Request_Filtering-critical.svg)](#)

## 🔒 Overview
**GProtector** is an integral part of the Gambio core. It protects the shop against attacks by analysing and filtering **all incoming POST and GET parameters**, so potential vulnerabilities and exploits are caught before they can do damage.

The module acts as a **security firewall at application level** and is designed to proactively block unknown attack vectors as well.

---

## 🛠️ Features
- Filtering and validation of all **POST & GET requests**
- Protection against **SQL injection**, **XSS attacks**, **code injection** and other exploits
- Automatic blocking of suspicious or manipulated parameters
- Updated through Gambio core updates (no separate module update needed)
- Performance-optimised: minimal impact on page load times
- No configuration required — **“Plug & Protect”**

---

## 🚀 Installation
**GProtector is included in the Gambio core by default since GX 4.x.**

For developers/shop owners:
1. Make sure the shop runs a current Gambio version (at least GX 4.x).
2. No separate installation needed – GProtector is active directly in the core.
3. If needed, the logging of blocked requests can be checked in debug mode.

---

## 🧩 Filter rules
Rules live in `filter/standard.json` (published with every release to `https://protect.gambio-server.net/standard.json`
and loaded by the shops without any action by the merchant) or in additional `filter/*.json` files.

| Field | Required | Meaning |
|---|---|---|
| `key` | yes | Name of the rule |
| `script_name` | yes | Script (or list), e.g. `shop.php`, `admin/orders.php` |
| `variables` | yes | `[{ "type": "GET" \| "POST" \| "REQUEST", "property": "…" }]`, optionally with `subcategory` |
| `function` | yes, except for `deny` | Filter function without the `gprotector_` prefix, e.g. `filter_text` |
| `severity` | yes | `error`, `warning` or `notice` (logging only) |
| `pattern` | no, yes for `deny` | PCRE with delimiters, e.g. `#^Foo(?:\W\|$)#` |
| `action` | no | `sanitize` (default) or `deny` |

- **`sanitize`**: `function` changes the value. With `pattern`, only values that match (for arrays: per element); all
  others stay unchanged. Without `pattern`, all values, as before.
- **`deny`**: If a value matches `pattern`, the request is refused with `403` and logged. The check runs **after** all
  `sanitize` rules, so it sees the value the shop actually uses. A `pattern` that matches an empty value
  (e.g. `#.*#`) is rejected.
- An invalid rule (e.g. a broken `pattern`) is skipped and noted in the `gprotector_error` log; all other rules keep
  working.
- A rule naming a `function` that doesn't exist in the shop is skipped (entry in the `gprotector_error` log). Only
  publish rules using new functions once the function has been shipped.

### Example: blocking a page

**1. What to block**

| | |
|---|---|
| URL | `https://<shop>/shop.php?do=StyleEdit4Authentication` |
| Method | `GET` |
| Script | `shop.php` |
| Parameter | `do` (query string) |
| Value | `StyleEdit4Authentication`, plus every variant the shop routes to the same controller: `StyleEdit4Authentication/x`, `StyleEdit4Authentication.x`, `StyleEdit4Authentication-x`, … |
| Must keep working | every other `do` value, e.g. `Cart/Add`, `JsTranslations`; the same URL on `admin/admin.php` |

**2. The rule**

```json
{
  "key": "styleedit-storefront-auth",
  "script_name": "shop.php",
  "variables": [{ "type": "GET", "property": "do" }],
  "pattern": "#^StyleEdit4Authentication(?:\\W|$)#",
  "action": "deny",
  "function": "filter_text",
  "severity": "error"
}
```

**3. How the rule maps to the request**

| Request | Rule field |
|---|---|
| Script `shop.php` | `"script_name": "shop.php"` |
| Method `GET` + parameter `do` | `"variables": [{ "type": "GET", "property": "do" }]` (`POST` for form fields, `REQUEST` for both) |
| Value and its variants | `"pattern"`: starts with the controller name, followed by the end of the value or any non-word character (`\\W` because it's inside a JSON string) |
| Refuse the request | `"action": "deny"` → `403 forbidden`, the page's code never runs |
| Old shops (≤ 2.2.17) | `"function": "filter_text"`, required by old versions, ignored by new ones for `deny` (see below) |
| Log level | `"severity": "error"` |

**4. Result:** `shop.php?do=StyleEdit4Authentication` and its variants → `403`, logged in `security`; all other requests
unchanged. Before publishing, add the values from step 1 to `tests/fixtures/deny-rule-cases.json` and run the tests
(see [Tests](#tests)).

**Always write a `pattern` for `do` in this form:** `#^<Controller>(?:\W|$)#`. The shop takes the part of `do` up to the
first `/` or non-word character as the controller (`preg_split('/\W/', explode('/', $do)[0])[0]`), so `Foo/x`,
`Foo.x` and `Foo-x` all lead to `Foo` too. An exact match (`#^Foo$#`) lets them through. Don't use the `i` or `u`
modifiers: controller names are case-sensitive, and `u` misses values with invalid UTF-8.

**Always give `deny` rules a `function`** while shops with GProtector ≤ 2.2.17 are still in use. Those versions require
`function` on every rule; if it's missing, they reject the **whole** file, stop receiving rule updates and download it
again on every request. Pick a function that already runs on that variable (for `do` in `shop.php`: `filter_text`),
so the rule has no effect there. Newer versions don't run the function for `deny` rules.

### Tests
```
composer install
vendor/bin/phpunit
```
Every `deny` rule in `filter/standard.json` needs values it must block in `tests/fixtures/deny-rule-cases.json`. The
tests also check that it blocks no other controller of the shop (`tests/fixtures/storefront-do-corpus.json`; to
regenerate it, see `tests/fixtures/generate-do-corpus.php`).

---

## 📋 Compatibility
- Gambio GX 4.0.x up to the latest version
- PHP 7.4 – 8.x

---

## ❗ Notes
- GProtector does **not replace a server-side firewall (WAF)**, but adds protection at application level.
- For full security, other measures such as regular updates, strong passwords and server-side hardening are needed as well.
- The filters are deliberately restrictive – if false positives occur in rare cases, Gambio Support can help.

---

## 🆘 Support
Since GProtector is part of the Gambio core, support & security updates come directly from Gambio:  
👉 [Gambio Support Portal](https://www.gambio-support.de)

---

## 📄 License
This module is licensed under the **GPL 2.0 license**.  
More information: [GPL-2.0 License](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

---

## 🔐 Security by design
GProtector is continuously developed to proactively defend against new attack vectors and vulnerabilities — **so your online shop stays safe.**

