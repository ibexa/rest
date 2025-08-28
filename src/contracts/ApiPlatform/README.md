# API Platform Open API schemas provider

## Usage
```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    App\ApiPlatform\SchemasProvider: ~
```

1. `FileFinderSchemaProvider`:

```php
 final class SchemasProvider extends \Ibexa\Contracts\Rest\ApiPlatform\FileFinderSchemaProvider
 {
     protected function getFinder(): Finder
     {
         return Finder::create()
             ->in(__DIR__ . '/../Resources/api_platform/schemas')
             ->files()
             ->name('*.yaml');
     }
 }
```

2. `FileListSchemaProvider`:
```php
final class SchemasProvider extends Ibexa\Contracts\Rest\ApiPlatform\FileListSchemaProvider;
{
    protected function getFilesList(): iterable
    {
        yield __DIR__ . '/../Resources/api_platform/schemas/schema1.yaml';
        yield __DIR__ . '/../Resources/api_platform/schemas/schema2.yaml';
    }
}
```
## TODO
- [ ] Schema interface as a contract too
- [ ] Can we yield schemas instead and build at the very end (including duplication validation)?
