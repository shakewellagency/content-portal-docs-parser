# Content Portal Docs Parser

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shakewellagency/content-portal-docs-parser.svg?style=flat-square)](https://packagist.org/packages/shakewellagency/content-portal-docs-parser)
[![Tests](https://img.shields.io/github/actions/workflow/status/shakewellagency/content-portal-docs-parser/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/shakewellagency/content-portal-docs-parser/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/shakewellagency/content-portal-docs-parser.svg?style=flat-square)](https://packagist.org/packages/shakewellagency/content-portal-docs-parser)

A Laravel package for processing and parsing Word documents (DOCX) by converting them to PDF format and extracting page-by-page content. This package is designed to work seamlessly with the Shakewell Agency content portal ecosystem and integrates with the `content-portal-pdf-parser` for comprehensive document processing workflows.

## Features

- **DOCX to PDF Conversion**: Convert Word documents to PDF format for standardized processing
- **Batch Processing**: Handle large documents efficiently using Laravel queues and batch processing
- **Page-by-Page Parsing**: Extract content from individual pages for detailed analysis
- **Queue Integration**: Asynchronous processing with proper error handling and retry mechanisms
- **Event-Driven Architecture**: Emit events for parsing lifecycle (started, finished, failed)
- **S3 Storage Support**: Direct integration with AWS S3 for document storage
- **Comprehensive Logging**: Detailed logging and activity tracking throughout the parsing process
- **Error Recovery**: Robust error handling with automatic cleanup and failure notifications

## Requirements

- PHP 8.3+
- Laravel 11.9+
- Queue system (Redis, database, etc.) for background processing
- S3 storage configuration for document handling

## Installation

You can install the package via composer:

```bash
composer require shakewellagency/content-portal-docs-parser
```

## Configuration

The package relies on configuration values for S3 storage and status enums. Ensure your Laravel application has the following configuration:

```php
// config/shakewell-parser.php (example)
return [
    's3' => env('SHAKEWELL_PARSER_S3_DISK', 's3'),
    'enums' => [
        'package_status_enum' => App\Enums\PackageStatusEnum::class,
    ],
];
```

## Usage

### Basic Document Processing

```php
use Shakewellagency\ContentPortalDocsParser\Features\Packages\Facades\DOCXParse;

// Process a DOCX document
DOCXParse::execute($package, $version);
```

### Processing Workflow

The package follows a structured workflow:

1. **Package Initialization**: Converts DOCX to PDF and counts total pages
2. **Rendition Creation**: Creates a rendition record for tracking
3. **Batch Processing**: Divides pages into batches for efficient processing
4. **Page Parsing**: Extracts content from each page using the PDF parser
5. **Completion**: Marks the package as finished and triggers completion events

### Job Classes

The package includes several job classes for different processing stages:

#### PackageInitializationJob
- Converts DOCX to PDF format
- Generates package hash for uniqueness
- Counts total pages in the document
- Sets up the package for processing

#### PageParserJob
- Creates rendition records
- Dispatches batch processing jobs
- Handles initial page processing setup

#### BatchParserJob
- Processes pages in configurable batches (default: 100 pages)
- Extracts content from each page
- Handles cover photo processing for first page
- Manages completion detection and finalization

#### InitialPageParserJob
- Specialized processing for the first page
- Cover photo extraction and processing
- Initial page metadata setup

### Events

The package emits several events during processing:

```php
use Shakewellagency\ContentPortalPdfParser\Events\ParsingTriggerEvent;
use Shakewellagency\ContentPortalPdfParser\Events\ParsingStartedEvent;
use Shakewellagency\ContentPortalPdfParser\Events\ParsingFinishedEvent;

// Listen for parsing events
Event::listen(ParsingStartedEvent::class, function ($event) {
    // Handle parsing started
});

Event::listen(ParsingFinishedEvent::class, function ($event) {
    // Handle parsing completion
});
```

### Error Handling

The package includes comprehensive error handling:

- **Automatic Retry**: Failed jobs are automatically retried
- **Cleanup**: Temporary files are cleaned up on failure
- **Logging**: All errors are logged with context
- **Notifications**: Failed packages trigger appropriate events

### Batch Configuration

You can customize batch processing by modifying the batch size in `BatchParserJob`:

```php
// In BatchParserJob
$batchSize = 100; // Adjust based on your needs and server capacity
```

## Architecture

### Dependencies

The package integrates with several other components:

- **content-portal-pdf-parser**: Core PDF processing functionality
- **phpoffice/phpword**: DOCX file handling (future enhancement)
- **barryvdh/laravel-dompdf**: PDF generation and manipulation
- **spatie/laravel-activitylog**: Activity logging and tracking

### Job Processing Flow

```
DOCXParse::execute()
    ↓
PackageInitializationJob
    ↓
PageParserJob
    ↓
BatchParserJob (multiple instances)
    ↓
InitialPageParserJob (for first page)
    ↓
Completion & Event Emission
```

### Storage Structure

The package expects documents to be stored in S3 with the following structure:
- Documents are referenced by `file_path` in the package model
- Temporary PDF files are created in system temp directory during processing
- Processed content is stored via the rendition system

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Code Quality

Format code using Laravel Pint:

```bash
composer format
```

## Performance Considerations

- **Queue Workers**: Ensure sufficient queue workers for concurrent processing
- **Memory Limits**: Large documents may require increased memory limits
- **Batch Size**: Adjust batch sizes based on document complexity and server capacity
- **S3 Configuration**: Optimize S3 settings for your document sizes
- **Timeout Settings**: Jobs have 2-hour timeouts for large document processing

## Troubleshooting

### Common Issues

1. **Memory Exhaustion**: Increase PHP memory limit for large documents
2. **Queue Timeouts**: Ensure queue workers have sufficient timeout settings
3. **S3 Permissions**: Verify proper S3 read/write permissions
4. **PDF Conversion Errors**: Check temporary directory write permissions

### Logging

Enable detailed logging by checking these log channels:
- Application logs for general processing information
- Queue logs for job-specific issues
- Activity logs for user actions and system events

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Kylyn Luterte](https://github.com/shakewellagency)
- [Shakewell Agency](https://shakewell.agency)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
