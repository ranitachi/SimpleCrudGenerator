# Changelog

## [v1.1] - 2025-04-13
### Added
- 🧠 Smart dynamic field generator (`generateField()`)
- 🧩 Auto parser for `flag`, `status`, `image`, `desc`, `textarea`, `date`, etc
- 💡 `formatArray()` helper untuk inject PHP array ke stub
- 🖼️ Default image preview dan select options
- 🔌 Universal `<x-form-inputs />` & `<x-datatable />` compatible views
- 📂 Vendor publishable stubs (`resources/views/stubs/blade`)
- 🎛️ Blade stubs: `index`, `create`, `edit` with Bootstrap layout
- 📊 Datatable config auto-generate di service

### Changed
- 🔄 `GenerateCrudCommand.php` refactored to be modular & DRY
- 🛠️ Stub injection via `{{fields}}`, `{{rules}}`, `{{datatable}}`, `{{fillable}}`

### Fixed
- 🐛 Controller injection path & model relation fix
- ✅ Better support for old() and $data->xxx in form fields

---
