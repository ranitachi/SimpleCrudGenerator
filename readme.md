# SimpleCrudGenerator

![Version](https://img.shields.io/badge/version-v1.1-blue.svg)

[![Latest Stable Version](https://poser.pugx.org/ranitachi/simple-crud-generator/v/stable)](https://packagist.org/packages/ranitachi/simple-crud-generator)  
[![Total Downloads](https://poser.pugx.org/ranitachi/simple-crud-generator/downloads)](https://packagist.org/packages/ranitachi/simple-crud-generator)  
[![License](https://poser.pugx.org/ranitachi/simple-crud-generator/license)](https://packagist.org/packages/ranitachi/simple-crud-generator)

---

## 📌 Introduction

**SimpleCrudGenerator** adalah package Laravel untuk mempercepat proses pembuatan CRUD (Create, Read, Update, Delete).  
Dengan **1 perintah artisan**, kamu akan langsung dapat:

- Model dengan soft deletes
- Controller RESTful
- Service logic terpisah
- Request validator
- Migration table
- Blade view siap pakai (index, create, edit)
- Komponen Blade universal: `<x-form-inputs />` & `<x-datatable />`

---

## ⚙️ Installation

### 1. Install via Composer

```bash
composer require ranitachi/simple-crud-generator
```

### 2. (Optional) Tambahkan ServiceProvider secara manual

Jika auto-discovery tidak aktif, daftarkan provider:

```php
'providers' => [
    Fcn\SimpleCrudGenerator\SimpleCrudGeneratorServiceProvider::class,
];
```

### 3. Publish Stubs dan Blade View

```bash
php artisan vendor:publish --provider="Fcn\SimpleCrudGenerator\SimpleCrudGeneratorServiceProvider"
```

---

## 🚀 Usage

```bash
php artisan make:simple-crud {table_name}
```

Contoh:

```bash
php artisan make:simple-crud posts
```

Yang akan digenerate:

- ✅ Model → `app/Models/Post.php`
- ✅ Controller → `app/Http/Controllers/PostController.php`
- ✅ Service → `app/Services/PostService.php`
- ✅ Request → `app/Http/Requests/PostRequest.php`
- ✅ Migration → `database/migrations/..._create_posts_table.php`
- ✅ View → `resources/views/pages/post/{index,create,edit}.blade.php`

---

## ✨ Fitur Unggulan (v1.1)

- 🔍 **Auto-detect field**: `text`, `textarea`, `select`, `file`, `image`, `date`, `number`, `wysiwyg`
- 🧠 Field seperti `flag`, `status`, `photo`, `desc` langsung dikenali
- 📄 WYSIWYG Editor via Summernote
- 🖼️ Image preview langsung dari input file
- 📊 Auto config kolom datatable (index page)
- 🧩 `x-form-inputs` dan `x-datatable` support full kolom dinamis
- 🛡️ Validasi otomatis di `Request` (via parser)

---

## 🧩 Contoh Route & Komponen Blade

Tambahkan ke `routes/web.php`:

```php
Route::prefix('admin')->group(function () {
    Route::resource('posts', \App\Http\Controllers\PostController::class);
});
```

Di `index.blade.php`:

```blade
<x-datatable :columns="$columns" ajax="{{ route('posts.index') }}" />
```

Di `create/edit.blade.php`:

```blade
<x-form-inputs :fields="$fields" />
```

---

## 🗂️ Struktur File

```
├── app/
│   ├── Models/Post.php
│   ├── Services/PostService.php
│   └── Http/
│       ├── Controllers/PostController.php
│       └── Requests/PostRequest.php

├── resources/views/pages/post/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php

├── resources/views/components/
│   ├── form-inputs.blade.php
│   └── datatable.blade.php
```

---

## 📜 Changelog

Lihat [`CHANGELOG.md`](CHANGELOG.md) untuk detail update fitur per versi.

---

## 👨‍💻 Contribution

Pull request, ide, dan kolaborasi sangat diterima!  
Yuk ikut bantu sempurnakan generator CRUD ini 🔥

---

## 🧾 License

MIT © 2025 – by [ranitachi](https://github.com/ranitachi)
