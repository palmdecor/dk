# Invenio MVP (Shared Hosting)

A plain PHP 8 + MySQL MVP for shared hosting. Admins manage templates and fonts, define headline/subhead boxes on a canvas, and users upload photos to render outputs.

## Requirements

- PHP 8.x
- MySQL / MariaDB
- Apache with `.htaccess` rewrite enabled
- Imagick (preferred) or GD (fallback)

## Setup

1. Install dependencies:

```bash
composer install
```

2. Create the database schema:

```sql
SOURCE schema.sql;
```

3. Configure credentials in `config/config.php` (or use env vars `DB_DSN`, `DB_USER`, `DB_PASS`, `BASE_URL`).

4. Ensure storage folders are writable:

```
storage/
storage/uploads/
storage/overlays/
storage/fonts/
storage/renders/
storage/previews/
```

5. Create the first admin user (example):

```php
<?php echo password_hash('your-password', PASSWORD_DEFAULT); ?>
```

```sql
INSERT INTO users (email, password_hash, role) VALUES (
  'admin@example.com',
  '$2y$10$yourGeneratedHashHere',
  'admin'
);
```

## Notes

- Overlay PNG is required for renders.
- SVG upload is not wired in the MVP (PNG overlay only).
- Video rendering is intentionally omitted (shared hosting FFmpeg not guaranteed).
