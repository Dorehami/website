# دورهمی - جامعه توسعه‌دهندگان نرم‌افزار

![لوگو دورهمی](https://dorehami.dev/images/logo-full.png)

دورهمی یک پلتفرم اجتماعی برای توسعه‌دهندگان نرم‌افزار فارسی‌زبان است. این سایت به عنوان مرکزی برای جامعه دیسکورد عمل می‌کند و به کاربران امکان می‌دهد لینک‌ها را به اشتراک بگذارند، درباره موضوعات بحث کنند و با سایر توسعه‌دهندگان ارتباط برقرار کنند.

## ویژگی‌ها

- احراز هویت کاربران با استفاده از OAuth دیسکورد و ایمیل/رمز عبور
- سیستم اشتراک‌گذاری لینک و رأی‌دهی (مشابه Hacker News)
- سیستم نظردهی روی لینک‌های به اشتراک گذاشته شده
- پنل مدیریت برای نظارت بر محتوا
- یکپارچه‌سازی با دیسکورد برای آمار جامعه
- پشتیبانی از RTL برای زبان فارسی

## تکنولوژی‌های استفاده شده

**فرانت‌اند:**
- موتور قالب Twig
- TailwindCSS 4.0

**بک‌اند:**
- PHP 8.4
- Symfony 7.2
- PostgreSQL 16
- Doctrine ORM 3.3

**زیرساخت:**
- Docker
- Nginx
- PHP-FPM

## پیش‌نیازها

- Docker و Docker Compose
- Git

## متغیرهای محیطی

برای اجرای این پروژه، شما باید متغیرهای محیطی زیر را به فایل `.env.local` خود اضافه کنید:

```
APP_ENV=dev
APP_SECRET=your_secure_secret_key

DATABASE_URL="postgresql://app:!ChangeMe!@database:5432/app?serverVersion=16&charset=utf8"

OAUTH_DISCORD_CLIENT_ID=your_discord_client_id
OAUTH_DISCORD_CLIENT_SECRET=your_discord_client_secret
DISCORD_GUILD_ID=your_discord_server_id
DISCORD_TOKEN=your_discord_bot_token
```

## اجرای لوکال

1. کلون کردن پروژه:

```bash
git clone https://github.com/Dorehami/website.git
cd website
```

2. ایجاد فایل `.env.local` با متغیرهای محیطی مورد نیاز.

3. راه‌اندازی کانتینرهای داکر:

```bash
docker compose up -d
```

4. نصب وابستگی‌های PHP:

```bash
./docker/bin/composer install
```

5. نصب وابستگی‌های فرانت‌اند و ساخت asset ها:

```bash
./docker/bin/yarn install
```

6. راه‌اندازی پایگاه داده:

```bash
./docker/bin/console doctrine:migrations:migrate
```

7. دسترسی به وب‌سایت در آدرس http://localhost:8080

## جریان کار توسعه

### استایل کد PHP

این پروژه از PHP_CodeSniffer برای حفظ استانداردهای کدنویسی استفاده می‌کند. اجرا کنید:

```bash
./docker/bin/cs        # برای بررسی استایل کد
./docker/bin/cbf       # برای رفع مشکلات استایل کد
```

### استفاده از کنسول Symfony

دستورات کنسول Symfony را از طریق کمک‌کننده داکر اجرا کنید:

```bash
./docker/bin/console [command]
```

به عنوان مثال، برای ایجاد یک کنترلر جدید:

```bash
./docker/bin/console make:controller MyNewController
```

## ساختار دایرکتوری

```
├── assets/              # فایل‌های فرانت‌اند (JS، CSS)
├── bin/                 # کنسول Symfony
├── config/              # تنظیمات Symfony
├── docker/              # تنظیمات داکر
│   ├── bin/             # اسکریپت‌های کمکی
│   └── images/          # تنظیمات تصاویر داکر
├── public/              # دایرکتوری عمومی وب
├── src/                 # کد منبع PHP
│   ├── Controller/      # کنترلرهای برنامه
│   ├── Entity/          # موجودیت‌های Doctrine
│   ├── Form/            # انواع فرم
│   ├── Repository/      # ریپازیتوری‌های Doctrine
│   ├── Security/        # احراز هویت و مجوزها
│   ├── Service/         # سرویس‌های برنامه
│   └── Twig/            # افزونه‌های Twig
├── templates/           # قالب‌های Twig
├── translations/        # فایل‌های ترجمه
└── var/                 # فایل‌های تولید شده (کش، لاگ‌ها)
```

## احراز هویت

این برنامه از دو روش احراز هویت پشتیبانی می‌کند:

1. **دیسکورد OAuth**: کاربران می‌توانند با حساب دیسکورد خود وارد شوند
2. **ایمیل/رمز عبور**: برای کاربران ادمین

## پنل مدیریت

پنل مدیریت در آدرس `/admin` در دسترس است و نیازمند دسترسی `ROLE_ADMIN` است. این پنل رابط‌هایی را برای موارد زیر ارائه می‌دهد:

- مدیریت کاربران
- مدیریت نظرات
- تنظیمات سایت

## پیکربندی داکر

پیکربندی داکر شامل موارد زیر است:

- **Nginx**: وب سرور
- **PHP-FPM**: پردازشگر PHP
- **PostgreSQL**: سرور پایگاه داده
- **Node**: برای ساخت asset های فرانت‌اند

## مشارکت

مشارکت‌ها خوش‌آمد هستند! برای مشارکت:

1. ریپازیتوری را fork کنید
2. یک برنچ ویژگی ایجاد کنید (`git checkout -b feature/amazing-feature`)
3. تغییرات خود را کامیت کنید (`git commit -m 'Add some amazing feature'`)
4. به برنچ پوش کنید (`git push origin feature/amazing-feature`)
5. یک درخواست Pull ایجاد کنید

## مجوز

این پروژه تحت مجوز MIT منتشر شده است.