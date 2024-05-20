![Filament Translatable Fields](docs/images/banner.png)

# Filament Translatable Fields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/outerweb/filament-translatable-fields.svg?style=flat-square)](https://packagist.org/packages/outerweb/filament-translatable-fields)
[![Total Downloads](https://img.shields.io/packagist/dt/outerweb/filament-translatable-fields.svg?style=flat-square)](https://packagist.org/packages/outerweb/filament-translatable-fields)

This package adds a way to make all filament fields translatable.
It uses the `spatie/laravel-translatable` package in the background.

## Installation

First install and configure your model(s) to use the `spatie/laravel-translatable` package.

You can install the package via composer:

```bash
composer require outerweb/filament-translatable-fields
```

Add the plugin to your desired Filament panel:

```php
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;

class FilamentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugins([
                FilamentTranslatableFieldsPlugin::make(),
            ]);
    }
}
```

You can specify the supported locales:

```php
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;

class FilamentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugins([
                FilamentTranslatableFieldsPlugin::make()
                    ->supportedLocales([
                        'en' => 'English',
                        'nl' => 'Dutch',
                    ]),
            ]);
    }
}
```

By default, the package will use the `app.locale` if you don't specify the locales.

### Combining with the official [spatie-laravel-translatable-plugin](https://github.com/filamentphp/spatie-laravel-translatable-plugin)?

This package is a replacement for the official on the **create** and **edit** pages only. If you are already using the official package, you will have to delete the `use Translatable` trait and the `LocaleSwitcher` header action from those pages:

```diff
-use Filament\Actions\LocaleSwitcher;
-use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPage extends EditRecord
{
-    use Translatable;

    protected function getHeaderActions(): array
    {
        return [
-            LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
```

## Usage

You can simply add `->translatable()` to any field to make it translatable.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->label('Name')
    ->translatable(),
```

## Disable translations dynamically

If you want to disable translations dynamically, you can set the first parameter of the `->translatable()` function to `true` or `false`.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->label('Name')
    ->translatable(false),
```

## Overwrite locales

If you want to overwrite the locales on a specific field you can set the locales through the second parameter of the `->translatable()` function.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->label('Name')
    ->translatable(true, ['en' => 'English', 'nl' => 'Dutch', 'fr' => 'French']),
```

## Locale specific validation rules

You can add locale specific validation rules with the third parameter of the `->translatable()` method.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->label('Name')
    ->translatable(true, null, [
        'en' => ['required', 'string', 'max:255'],
        'nl' => ['nullable', 'string', 'max:255'],
    ]);
```

### Good to know

This package will substitute the original field with a `Filament\Forms\Components\Tabs` component. This component will render the original field for each locale.

All chained methods you add before calling `->translatable()` will be applied to the original field.
All chained methods you add after calling `->translatable()` will be applied to the `Filament\Forms\Components\Tabs` component.

## Laravel support

| Laravel Version | Package version |
| --------------- | --------------- |
| ^11.0           | ^1.0.2, ^2.0.0  |
| ^10.0           | ^1.0.0, ^2.0.0  |

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Simon Broekaert](https://github.com/SimonBroekaert)
- [Finn Paes](https://github.com/FinnPaes)
- [Vedat YILMAZ](https://github.com/vedatyilmaz)
- [All Contributors](../../contributors)

## License

MIT License (MIT). Read the [License File](LICENSE.md) for more information.
