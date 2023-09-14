# filament-sql-nested-builder-form

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thiktak/filament-sql-nested-builder-form.svg?style=flat-square)](https://packagist.org/packages/thiktak/filament-sql-nested-builder-form)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/thiktak/filament-sql-nested-builder-form/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/thiktak/filament-sql-nested-builder-form/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/thiktak/filament-sql-nested-builder-form/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/thiktak/filament-sql-nested-builder-form/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/thiktak/filament-sql-nested-builder-form.svg?style=flat-square)](https://packagist.org/packages/thiktak/filament-sql-nested-builder-form)


An application of [thiktak/filament-nested-builder-form](https://github.com/Thiktak//filament-nested-builder-form) for SQL with (not) AND/OR and sub-groups.

> [!WARNING]
> Be careful, this package is not yet recommended for production.
> Help us with testing and feedback :)

## Installation

You can install the package via composer:

```bash
composer require thiktak/filament-sql-nested-builder-form
```

This package is based on [thiktak/filament-nested-builder-form](https://github.com/Thiktak//filament-nested-builder-form).

## Usage

```php
// use App\Models\User;
use Thiktak\FilamentNestedBuilderForm\Forms\Components\NestedBuilder;
use Thiktak\FilamentNestedBuilderForm\Forms\Components\NestedSubBuilder;
use Thiktak\FilamentSQLNestedBuilderForm\Forms\Components\SQLNestedBuilder;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Nested Builder Form')
                    ->description('Example of the SQL Nested Builder Form (SQL Query)')
                    ->schema([
                        // configuration is an Array
                        SQLNestedBuilder::make('configuration')

                            // nestedConfiguration apply this set up to all children
                            ->nestedConfiguration(function (NestedSubBuilder $builder, NestedBuilder $parent) {
                                // import default configuration of this package
                                $parent->defaultNestedConfiguration($builder);
                            })

                            // Display the first Hint as a Raw SQL query of User Model
                            ->hint(function (?array $state) {
                                return SQLNestedBuilder::getFullyLinearizedArrayToEloquent($state, User::query())
                                    ->getQuery()
                                    ->toRawSql()
                                ;
                            })
                    ]),
            ]);
    }
```

## Configuration

### Change the field Input (name of the field)
Use the nestedConfiguration and set up ```fieldComponent```

```php
    SQLNestedBuilder::make('configuration')

        ->nestedConfiguration(function (NestedSubBuilder $builder, NestedBuilder $parent) {
            // import default configuration of this package
            $parent->defaultNestedConfiguration($builder);

            // Change the TextInput -> Select
            $parent->fieldComponent(
                fn () => Select::make('field')
                    ->options([
                        'id'    => 'User Id',
                        'email' => 'User email',
                    ])
                    ->searchable()
            );
        })
```

### Export to SQL (string)
This method will return the Raw SQL of a User model query. $state is the data array.

```php
SQLNestedBuilder::getFullyLinearizedArrayToEloquent($state, User::query())
    ->getQuery()
    ->toRawSql()
```

Output (example):

```SQL
select *
  from `users`
  where (
    (
      not (
        (`a` = '1') and (`b` in ('2', '3'))
        and (`a` between '1' and '99')
      )
      or (`email` LIKE '%admin.com%')
      or (`email` LIKE 'a%')
      or (`email` LIKE '%com')
    )
    and (`tenant_id` = '1')
  )
```

### Export to Eloquent

```php
  SQLNestedBuilder::getFullyLinearizedArrayToSQL(?array $state); // If consume the whole array

  SQLNestedBuilder::getFullyLinearizedArrayToSQL(?array $state, 'group'); // if level of group
  SQLNestedBuilder::getFullyLinearizedArrayToSQL(?array $state, 'rule'); // if level of rule
```

Output (example):

```SQL
(`a` = '1' AND `b` IN ('2', '3') AND `a` BETWEEN '1' AND '99') AND `email` LIKE '%admin.com%' AND `email` LIKE 'a%' AND `email` LIKE '%com'
```

### Add custom operators

(current solution) Extend the SQLNesterBuilder, and redefine the method loadDefinition() with a call to ```$this->registerOperator(MyOperator::class)```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Thiktak](https://github.com/Thiktak)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
