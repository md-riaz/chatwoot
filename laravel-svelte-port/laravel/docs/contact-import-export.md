# Contact Import/Export Implementation

This document describes the enhanced contact import/export functionality implemented to match Rails backend capabilities.

## Features Implemented

### Import Functionality

1. **File Format Support**
   - CSV files (.csv, .txt)
   - Excel files (.xlsx, .xls) - requires PhpSpreadsheet package
   - File size limit: 10MB

2. **Data Processing**
   - Batch processing for memory efficiency (200 records per batch)
   - Progress tracking with callbacks
   - Comprehensive error handling and validation
   - Failed records CSV generation

3. **Duplicate Handling**
   - `skip`: Skip existing contacts (default)
   - `update`: Update existing contacts with new data
   - `create_duplicate`: Create duplicate contacts

4. **Field Mapping**
   - Custom column mapping support
   - Automatic detection of standard fields (name, email, phone_number, identifier)
   - Additional attributes support (company, city, country)
   - Custom attributes for unmapped fields

5. **Validation**
   - Email format validation
   - Phone number formatting (adds + prefix if missing)
   - Requires at least one identifier (email, phone, or identifier)
   - Data type validation

### Export Functionality

1. **Column Selection**
   - Default columns: id, name, email, phone_number, identifier, created_at
   - Additional columns: blocked, updated_at, last_activity_at
   - Support for additional_attributes and custom_attributes

2. **Filtering**
   - Advanced filter support using ContactFilterService
   - Label-based filtering
   - Date range filtering
   - String matching (contains, starts_with, etc.)
   - Boolean filtering

3. **Output Format**
   - CSV format with proper escaping
   - UTF-8 encoding
   - Chunked processing for large datasets

## API Endpoints

### Import Endpoints

```http
POST /api/v1/accounts/{account}/contacts/import
Content-Type: multipart/form-data

Parameters:
- import_file: file (required) - CSV or Excel file
- mapping: object (optional) - Column mapping {"csv_column": "contact_field"}
- duplicate_handling: string (optional) - "skip"|"update"|"create_duplicate"
```

```http
GET /api/v1/accounts/{account}/contacts/imports/{import_id}/status
Response: {
  "status": "processing|completed|failed",
  "processed": 100,
  "created": 80,
  "updated": 20,
  "errors": [...],
  "data_import_id": 123
}
```

```http
GET /api/v1/accounts/{account}/contacts/imports/{import_id}/failed
Response: CSV file with failed records and error messages
```

### Export Endpoints

```http
POST /api/v1/accounts/{account}/contacts/export
Content-Type: application/json

Parameters:
- column_names: array (optional) - Columns to export
- payload: array (optional) - Advanced filters
- label: string (optional) - Filter by label
```

```http
GET /api/v1/accounts/{account}/contacts/exports/download
Response: CSV file download
```

## Implementation Details

### Key Classes

1. **ContactImportService** (`app/Services/Contact/ContactImportService.php`)
   - Handles CSV and Excel file processing
   - Implements validation and data transformation
   - Manages batch processing and progress tracking

2. **ContactFilterService** (`app/Services/Contact/ContactFilterService.php`)
   - Applies advanced filters to contact queries
   - Supports various operators (equal_to, contains, etc.)
   - Handles JSON field filtering

3. **ImportContactsJob** (`app/Jobs/ImportContactsJob.php`)
   - Background job for processing imports
   - Uses ContactImportService for actual processing
   - Handles progress updates and notifications

4. **ExportContactsJob** (`app/Jobs/ExportContactsJob.php`)
   - Background job for generating exports
   - Uses ContactFilterService for filtering
   - Generates CSV files with proper formatting

### Database Schema

The implementation uses the existing `DataImport` model with these fields:
- `account_id`: Account association
- `user_id`: User who initiated the import
- `import_type`: Type of import (contacts)
- `file_path`: Path to uploaded file
- `status`: pending|processing|completed|failed
- `total_rows`: Total number of rows processed
- `processed_rows`: Number of rows processed so far
- `meta`: JSON field for additional metadata
- `error_message`: Error message if failed

### Progress Tracking

Import progress is tracked using:
1. Cache storage for real-time status updates
2. Database updates for persistence
3. Progress callbacks for batch updates
4. User notifications on completion

### Error Handling

1. **File Validation**
   - File type validation
   - File size limits
   - File readability checks

2. **Data Validation**
   - Email format validation
   - Required field validation
   - Data type validation

3. **Processing Errors**
   - Individual row error tracking
   - Failed records CSV generation
   - Comprehensive error messages

### Security Considerations

1. **File Upload Security**
   - File type validation
   - File size limits
   - Secure file storage

2. **Access Control**
   - Account-based access control
   - User authentication required
   - Export file access via secure tokens

3. **Data Privacy**
   - Temporary file cleanup
   - Secure file storage
   - Time-limited download links

## Testing

The implementation includes comprehensive tests:

1. **Feature Tests** (`tests/Feature/Api/V1/ContactImportExportTest.php`)
   - API endpoint testing
   - File upload validation
   - Response format validation

2. **Unit Tests** (`tests/Unit/Services/Contact/ContactImportServiceTest.php`)
   - Service logic testing
   - Data processing validation
   - Error handling verification

## Usage Examples

### Basic Import

```javascript
const formData = new FormData();
formData.append('import_file', file);
formData.append('duplicate_handling', 'update');

const response = await fetch('/api/v1/accounts/123/contacts/import', {
  method: 'POST',
  body: formData,
  headers: {
    'Authorization': 'Bearer ' + token
  }
});

const result = await response.json();
// { "import_id": "uuid", "data_import_id": 456 }
```

### Import with Custom Mapping

```javascript
const mapping = {
  "Full Name": "name",
  "Email Address": "email",
  "Mobile Phone": "phone_number",
  "Company Name": "company"
};

formData.append('mapping', JSON.stringify(mapping));
```

### Export with Filters

```javascript
const exportData = {
  column_names: ['name', 'email', 'phone_number'],
  payload: [
    {
      attribute_key: 'email',
      filter_operator: 'contains',
      values: ['@company.com'],
      query_operator: 'and'
    }
  ]
};

const response = await fetch('/api/v1/accounts/123/contacts/export', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token
  },
  body: JSON.stringify(exportData)
});
```

## Performance Considerations

1. **Memory Usage**
   - Batch processing to limit memory usage
   - Stream processing for large files
   - Chunked database operations

2. **Processing Time**
   - Background job processing
   - Progress tracking for user feedback
   - Timeout handling for large imports

3. **Storage**
   - Temporary file cleanup
   - Efficient file storage
   - Compressed export files

## Future Enhancements

1. **Additional File Formats**
   - JSON import/export
   - XML support
   - Google Sheets integration

2. **Advanced Features**
   - Import scheduling
   - Template management
   - Data transformation rules

3. **Performance Improvements**
   - Parallel processing
   - Database optimization
   - Caching strategies