# 📧 PHP Email Builder

A framework-agnostic, modular PHP library to support email template generation, dynamic content injection, and custom email block storage. Designed to work standalone or as the foundation for Laravel-based email builder wrappers.

---

## 🚀 Features

- 🔌 **Pluggable drivers** for storing custom blocks (File, SQLite)
- 🧠 **Industry-specific templates** and subject line data (via JSON files)
- 📦 Framework-agnostic (no Laravel dependency)
- 🧪 Built-in Pest/PHPUnit test support
- 🏗️ Easy to extend into Laravel/Firebase/etc

---

## 📦 Installation

You can install the package via Composer:

```bash
composer require apsonex/php-email-builder
```

> This assumes the package is published. Otherwise, require it locally or via `repositories` in your `composer.json`.

---

## 🛠 Usage

### Instantiate a Driver

```php
use Apsonex\EmailBuilderPhp\Support\CustomBlock\CustomBlock;
use Apsonex\EmailBuilderPhp\Support\CustomBlock\Drivers\SqLiteCustomBlockDriver;

// Example: SQLite
$pdo = new PDO('sqlite:/path/to/email-builder.sqlite');

$blocks = CustomBlock::make()
    ->driver(SqLiteCustomBlockDriver::class);

$block = $blocks->store([
    'tenant_id' => 1,
    'owner_id' => 1,
    'data' => [
        'name' => 'Welcome Banner',
        'category' => 'header',
        'html' => '<h1>Hello 👋</h1>',
    ],
]);
```

---

### Enable Multitenancy (optional)

```php
CustomBlock::enableMultitenancy('tenant_id');
CustomBlock::ownerKeyName('owner_id');
```

---

## 🧠 Industry Support

Pre-defined email subjects and industry categories are available under `data/email-subjects/*.json`.

Each file contains:

```json
{
  "industry": {
    "slug": "fitness",
    "label": "Fitness & Wellness"
  },
  "subjects": [
    "Get fit now – your first week free!",
    "Join our 30-day challenge 💪"
  ]
}
```

Use it like:

```php
use Apsonex\EmailBuilderPhp\Support\Industries;

$industries = Industries::make()->all(); // ['fitness' => 'Fitness & Wellness', ...]
$data = Industries::make()->industry('fitness');
```

---

## 🧪 Running Tests

This repo supports both **Pest** and **PHPUnit**:

```bash
vendor/bin/pest
# or
vendor/bin/phpunit
```

Test files are under:
- `tests/Feature/`
- `tests/Unit/`

---

## 🗃 Directory Structure

```
├── data/
│   └── email-subjects/
├── src/
│   └── Support/
│       └── CustomBlock/
│           └── Drivers/
├── tests/
│   ├── Feature/
│   └── Unit/
├── composer.json
├── phpunit.xml
└── zip.sh
```

---

## 🤝 Contributing

Contributions are welcome! Please submit a PR with proper tests and documentation.

---

## 📄 License

This project is open-sourced under the MIT license. See [`LICENSE.md`](LICENSE.md) for details.

---

## 🧭 Roadmap Ideas

- MJML or MJML-to-HTML integration
- AI-powered subject/CTA generation
- Laravel Eloquent driver (WIP)
- Web UI builder integration (via JS)

---

## 🔗 Maintained by [Apsonex](https://apsonex.com)

Happy building ✨
