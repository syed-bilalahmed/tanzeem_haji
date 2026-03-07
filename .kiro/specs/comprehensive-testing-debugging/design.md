# Design Document: Comprehensive Testing, Debugging, and Improvement

## Overview

This design document outlines the technical approach for hardening, testing, and improving the Tanzeem-e-Aulaad Hazrat Haji Bahadur Management System. The system is a PHP-based financial and organizational management application that requires comprehensive security improvements, testing infrastructure, and code quality enhancements to become production-ready.

The design focuses on five core pillars:

1. **Security Hardening**: Implementing defense-in-depth security measures including CSRF protection, XSS prevention, SQL injection mitigation, and session security
2. **Testing Infrastructure**: Establishing both automated (PHPUnit) and manual testing frameworks with property-based testing principles
3. **Error Handling & Logging**: Creating a centralized error handling system with secure logging
4. **Input Validation Layer**: Building a comprehensive validation and sanitization framework
5. **Code Quality Improvements**: Refactoring for maintainability, performance, and consistency

The system uses a traditional LAMP stack (Linux/Apache/MySQL/PHP) with Bootstrap 5 for the frontend. The design maintains backward compatibility with the existing codebase while introducing modern security and testing practices.

## Architecture

### High-Level Architecture

The improved system follows a layered architecture pattern:

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│  (Bootstrap 5 UI, Chart.js, FontAwesome, Urdu/English)  │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                   Security Layer (NEW)                   │
│  (CSRF Protection, XSS Filtering, Session Security)     │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                  Application Layer                       │
│  (Business Logic, Controllers, Input Validation)        │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                   Data Access Layer                      │
│         (PDO with Prepared Statements, Models)          │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                    Database Layer                        │
│              (MySQL with utf8mb4 encoding)              │
└─────────────────────────────────────────────────────────┘
```

### Security Architecture

The security architecture implements multiple defensive layers:

1. **Input Security**: All user inputs pass through validation and sanitization
2. **Authentication**: Password hashing with bcrypt, session management with regeneration
3. **Authorization**: Role-based access control (RBAC) with permission checks
4. **CSRF Protection**: Token-based validation for all state-changing operations
5. **XSS Prevention**: Context-aware output escaping
6. **SQL Injection Prevention**: Parameterized queries exclusively
7. **Session Security**: Secure cookies, session fixation prevention, timeout enforcement

### Testing Architecture

The testing strategy employs a dual approach:

```
┌─────────────────────────────────────────────────────────┐
│                    Testing Framework                     │
├─────────────────────────────────────────────────────────┤
│  Property-Based Tests (PHPUnit + Generators)            │
│  - Universal properties across all inputs                │
│  - 100+ iterations per property                          │
│  - Round-trip validation                                 │
│  - Invariant checking                                    │
├─────────────────────────────────────────────────────────┤
│  Unit Tests (PHPUnit)                                    │
│  - Specific examples and edge cases                      │
│  - Error condition handling                              │
│  - Integration points                                    │
├─────────────────────────────────────────────────────────┤
│  Manual Testing Checklist                                │
│  - UI/UX verification                                    │
│  - Browser compatibility                                 │
│  - Urdu/English display                                  │
└─────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Security Components

#### SecurityManager Class

Central security management component:

```php
class SecurityManager {
    // CSRF token generation and validation
    public function generateCSRFToken(): string
    public function validateCSRFToken(string $token): bool
    
    // XSS prevention
    public function escapeHTML(string $input): string
    public function escapeJS(string $input): string
    public function escapeURL(string $input): string
    
    // Session security
    public function initializeSecureSession(): void
    public function regenerateSessionId(): void
    public function validateSessionIntegrity(): bool
    public function destroySession(): void
}
```

#### InputValidator Class

Comprehensive input validation and sanitization:

```php
class InputValidator {
    // Validation methods
    public function validateDate(string $date): bool
    public function validateNumeric(string $input): bool
    public function validateDecimal(string $input): bool
    public function validateEmail(string $email): bool
    public function validateLength(string $input, int $min, int $max): bool
    
    // Sanitization methods
    public function sanitizeString(string $input): string
    public function sanitizeNumeric(string $input): string
    public function sanitizeHTML(string $input): string
    
    // Validation with error messages
    public function validate(array $rules, array $data): ValidationResult
}
```

#### AuthenticationManager Class

Enhanced authentication with security features:

```php
class AuthenticationManager {
    // Authentication
    public function login(string $username, string $password): bool
    public function logout(): void
    public function isAuthenticated(): bool
    
    // Password management
    public function hashPassword(string $password): string
    public function verifyPassword(string $password, string $hash): bool
    public function enforcePasswordPolicy(string $password): bool
    
    // Account security
    public function recordFailedLogin(string $username): void
    public function isAccountLocked(string $username): bool
    public function unlockAccount(string $username): void
}
```

#### AuthorizationManager Class

Role-based access control:

```php
class AuthorizationManager {
    // Permission checking
    public function hasPermission(string $userId, string $permission): bool
    public function hasRole(string $userId, string $role): bool
    public function canAccessResource(string $userId, string $resource): bool
    
    // Role management
    public function assignRole(string $userId, string $role): void
    public function revokeRole(string $userId, string $role): void
    public function getUserRoles(string $userId): array
}
```

### 2. Database Layer Components

#### DatabaseConnection Class

Secure PDO wrapper with prepared statements:

```php
class DatabaseConnection {
    private PDO $pdo;
    
    // Connection management
    public function __construct(array $config)
    public function getConnection(): PDO
    
    // Query execution with prepared statements
    public function query(string $sql, array $params = []): PDOStatement
    public function execute(string $sql, array $params = []): bool
    public function fetchAll(string $sql, array $params = []): array
    public function fetchOne(string $sql, array $params = []): ?array
    
    // Transaction support
    public function beginTransaction(): void
    public function commit(): void
    public function rollback(): void
}
```

#### BaseModel Class

Abstract base class for all data models:

```php
abstract class BaseModel {
    protected DatabaseConnection $db;
    protected string $table;
    protected string $primaryKey = 'id';
    
    // CRUD operations
    public function create(array $data): int
    public function read(int $id): ?array
    public function update(int $id, array $data): bool
    public function delete(int $id): bool
    public function findAll(array $filters = []): array
    
    // Validation
    abstract protected function validate(array $data): ValidationResult;
}
```

### 3. Error Handling Components

#### ErrorHandler Class

Centralized error and exception handling:

```php
class ErrorHandler {
    // Error logging
    public function logError(string $message, array $context = []): void
    public function logException(Throwable $e): void
    public function logSecurityEvent(string $event, array $context = []): void
    
    // User-facing error display
    public function displayError(string $userMessage): void
    public function displayValidationErrors(array $errors): void
    
    // Error reporting configuration
    public function setEnvironment(string $env): void
    public function enableDebugMode(): void
    public function disableDebugMode(): void
}
```

#### Logger Class

Structured logging with rotation:

```php
class Logger {
    // Logging levels
    public function debug(string $message, array $context = []): void
    public function info(string $message, array $context = []): void
    public function warning(string $message, array $context = []): void
    public function error(string $message, array $context = []): void
    public function critical(string $message, array $context = []): void
    
    // Log management
    public function rotate(): void
    public function purgeOldLogs(int $daysToKeep): void
}
```

### 4. Testing Components

#### TestDataGenerator Class

Generates random test data for property-based testing:

```php
class TestDataGenerator {
    // Random data generation
    public function randomString(int $length = 10): string
    public function randomInt(int $min = 0, int $max = 100): int
    public function randomDecimal(int $precision = 2): float
    public function randomDate(string $start = '-1 year', string $end = 'now'): string
    public function randomUrduText(int $words = 5): string
    
    // Entity generation
    public function generateCollection(): array
    public function generateIncome(): array
    public function generateExpense(): array
    public function generateEmployee(): array
}
```

### 5. Business Logic Components

#### FinancialCalculator Class

Accurate financial calculations:

```php
class FinancialCalculator {
    // Collection calculations
    public function calculateCollectionTotal(array $denominations): float
    public function calculateRunningBalance(array $transactions): array
    
    // Multi-fund calculations
    public function calculateFundTotals(array $transactions): array
    public function calculateNetCapital(float $income, float $expenses): float
    
    // Report calculations
    public function calculateMonthlyTotals(int $year, int $month): array
    public function calculateYearlyTotals(int $year): array
}
```

## Data Models

### Enhanced Database Schema

The existing schema will be validated and enhanced with proper constraints:

#### Collections Table
```sql
CREATE TABLE collections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    collection_date DATE NOT NULL,
    collector_name VARCHAR(255) NOT NULL,
    rs_5000 INT DEFAULT 0,
    rs_1000 INT DEFAULT 0,
    rs_500 INT DEFAULT 0,
    rs_100 INT DEFAULT 0,
    rs_50 INT DEFAULT 0,
    rs_20 INT DEFAULT 0,
    rs_10 INT DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_collection_date (collection_date),
    INDEX idx_collector_name (collector_name),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Income Table
```sql
CREATE TABLE income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    income_date DATE NOT NULL,
    description VARCHAR(500) NOT NULL,
    dargah_fund DECIMAL(10,2) DEFAULT 0.00,
    qabristan_fund DECIMAL(10,2) DEFAULT 0.00,
    masjid_fund DECIMAL(10,2) DEFAULT 0.00,
    urs_fund DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_income_date (income_date),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Expenses Table
```sql
CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expense_date DATE NOT NULL,
    description VARCHAR(500) NOT NULL,
    dargah_fund DECIMAL(10,2) DEFAULT 0.00,
    qabristan_fund DECIMAL(10,2) DEFAULT 0.00,
    masjid_fund DECIMAL(10,2) DEFAULT 0.00,
    urs_fund DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_expense_date (expense_date),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Users Table (Enhanced)
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    failed_login_attempts INT DEFAULT 0,
    account_locked_until TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Audit Log Table (New)
```sql
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Configuration Management

Environment-based configuration using `.env` file:

```
DB_HOST=localhost
DB_NAME=tanzeem_db
DB_USER=tanzeem_user
DB_PASS=secure_password_here
DB_CHARSET=utf8mb4

SESSION_LIFETIME=1800
SESSION_NAME=TANZEEM_SESSION

LOG_PATH=/var/log/tanzeem/
LOG_LEVEL=warning

ENVIRONMENT=production
DEBUG_MODE=false

CSRF_TOKEN_NAME=csrf_token
CSRF_TOKEN_LENGTH=32
```

### Data Validation Rules

Centralized validation rules for each entity:

```php
// Collection validation rules
$collectionRules = [
    'collection_date' => ['required', 'date', 'format:Y-m-d'],
    'collector_name' => ['required', 'string', 'max:255'],
    'rs_5000' => ['numeric', 'min:0'],
    'rs_1000' => ['numeric', 'min:0'],
    'total_amount' => ['required', 'decimal', 'min:0']
];

// Income validation rules
$incomeRules = [
    'income_date' => ['required', 'date', 'format:Y-m-d'],
    'description' => ['required', 'string', 'max:500'],
    'dargah_fund' => ['decimal', 'min:0'],
    'qabristan_fund' => ['decimal', 'min:0'],
    'total_amount' => ['required', 'decimal', 'min:0']
];
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

After analyzing all acceptance criteria, I identified the following redundancies and consolidations:

- Properties 13.1, 13.2, 13.3 (dashboard statistics matching database sums) can be consolidated into a single property about aggregate calculations
- Properties 3.1 and 3.5 (HTML escaping for user input and database content) can be combined into one comprehensive property
- Properties 4.1, 4.2, 4.3, 4.4 (CSRF token generation, validation, rejection, regeneration) represent a complete CSRF workflow that can be tested as related properties
- Properties 11.2 and 13.1-13.3 overlap in testing that aggregates match sums - consolidated into financial calculation invariants
- Properties 17.1 and 17.5 (Urdu display and storage) both test UTF-8 handling and can be combined into a round-trip property

### Property 1: Database Round-Trip Preservation

*For any* valid entity data (collection, income, expense, employee, notice, renter), creating the entity in the database then retrieving it should return data equivalent to the original input.

**Validates: Requirements 2.5, 10.7**

### Property 2: SQL Injection Rejection

*For any* user input containing SQL injection patterns (quotes, semicolons, SQL keywords), the database layer should reject the query attempt and prevent execution.

**Validates: Requirements 2.4**

### Property 3: HTML Output Escaping

*For any* string containing HTML special characters (<, >, &, ", '), when displayed in HTML context, the output should have all special characters properly escaped using htmlspecialchars.

**Validates: Requirements 3.1, 3.5**

### Property 4: JavaScript Context Encoding

*For any* data containing JavaScript special characters, when embedded in JavaScript context, the output should be properly encoded using json_encode with appropriate flags.

**Validates: Requirements 3.2**

### Property 5: CSRF Token Uniqueness

*For any* two consecutive form renderings, the generated CSRF tokens should be unique and cryptographically random.

**Validates: Requirements 4.1**

### Property 6: CSRF Token Validation

*For any* form submission with a valid CSRF token, the system should accept the request; for any submission with invalid or missing token, the system should reject the request with an error.

**Validates: Requirements 4.2, 4.3**

### Property 7: CSRF Token Regeneration

*For any* successful form submission, the CSRF token should be regenerated, and the old token should no longer be valid for subsequent requests.

**Validates: Requirements 4.4**

### Property 8: State-Changing Operations CSRF Protection

*For any* HTTP request using POST, PUT, DELETE, or PATCH methods, CSRF token validation should be enforced before processing.

**Validates: Requirements 4.5**

### Property 9: Password Complexity Enforcement

*For any* password string, validation should enforce minimum 8 characters with at least one uppercase letter, one lowercase letter, and one number.

**Validates: Requirements 5.1**

### Property 10: Session ID Regeneration on Login

*For any* successful user login, the session ID before login should differ from the session ID after login.

**Validates: Requirements 5.3, 14.7**

### Property 11: Session Hijacking Detection

*For any* active session, if the IP address or user agent changes from the original values, the session should be invalidated immediately.

**Validates: Requirements 5.7**

### Property 12: Authorization Denial for Insufficient Permissions

*For any* user attempting to access a resource without the required permission, access should be denied and an appropriate error message displayed.

**Validates: Requirements 6.3**

### Property 13: Permission Check Before Data Access

*For any* request to access sensitive data, permission verification should occur before the data is retrieved or displayed.

**Validates: Requirements 6.6**

### Property 14: Date Format Validation

*For any* date input string, validation should verify it matches YYYY-MM-DD format and represents a valid calendar date.

**Validates: Requirements 7.1**

### Property 15: Numeric Input Validation

*For any* input expected to be numeric, validation should verify it contains only digits (0-9) and optional negative sign.

**Validates: Requirements 7.2**

### Property 16: Decimal Amount Validation

*For any* monetary amount input, validation should verify it is a valid decimal number with at most two decimal places.

**Validates: Requirements 7.3**

### Property 17: Maximum Length Enforcement

*For any* text input with a defined maximum length constraint, validation should reject inputs exceeding that length.

**Validates: Requirements 7.4**

### Property 18: Dangerous Character Rejection

*For any* input containing null bytes (\0) or control characters, validation should reject the input.

**Validates: Requirements 7.5**

### Property 19: Validation Error Message Return

*For any* validation failure, the system should return a descriptive error message indicating which field failed and why.

**Validates: Requirements 7.6**

### Property 20: Database Error Logging

*For any* database error that occurs, the error handler should log the error with timestamp, error message, and contextual information.

**Validates: Requirements 8.1**

### Property 21: Generic User Error Messages

*For any* error displayed to end users, the message should be generic and should not expose system details like file paths, database structure, or stack traces.

**Validates: Requirements 8.2**

### Property 22: Authentication Failure Logging

*For any* failed login attempt, the error handler should log the event with username, IP address, and timestamp.

**Validates: Requirements 8.3**

### Property 23: Authorization Failure Logging

*For any* authorization denial, the error handler should log the event with user ID, attempted resource, and timestamp.

**Validates: Requirements 8.4**

### Property 24: Exception Logging

*For any* uncaught exception thrown during execution, the error handler should catch it and log the complete stack trace.

**Validates: Requirements 8.7**

### Property 25: Invalid Data Rejection

*For any* CRUD operation receiving invalid data, the system should reject the operation and display validation errors without modifying the database.

**Validates: Requirements 10.6**

### Property 26: Collection Total Calculation Invariant

*For any* collection record with denomination counts, the total amount should equal the sum of (denomination_count × denomination_value) across all denominations.

**Validates: Requirements 11.1**

### Property 27: Dashboard Aggregate Accuracy

*For any* dashboard statistic showing aggregated data, the displayed value should equal the sum of the underlying filtered database records.

**Validates: Requirements 11.2, 13.1, 13.2, 13.3**

### Property 28: Running Balance Cumulative Invariant

*For any* sequence of financial transactions, the running balance at position N should equal the sum of all transaction amounts from position 0 to N.

**Validates: Requirements 11.3**

### Property 29: Multi-Fund Total Invariant

*For any* transaction with multiple fund allocations (dargah, qabristan, masjid, urs), the total amount should equal the sum of all individual fund amounts.

**Validates: Requirements 11.4**

### Property 30: Financial Report Sum Invariant

*For any* financial report showing itemized data, the sum of all line items should equal the reported total.

**Validates: Requirements 11.6**

### Property 31: PDF Generation Validity

*For any* report data, generating a PDF should produce a valid PDF file that can be opened without errors.

**Validates: Requirements 12.4**

### Property 32: Net Capital Calculation

*For any* time period, the net capital should equal total income minus total expenses for that period.

**Validates: Requirements 13.4**

### Property 33: Chart Data Accuracy

*For any* chart displayed on the dashboard, the data points should match the corresponding database records for the selected filters.

**Validates: Requirements 13.5**

### Property 34: Funeral Statistics Calculation

*For any* set of funeral records, the calculated statistics should accurately reflect the count and totals from the database.

**Validates: Requirements 13.6**

### Property 35: Date Filter Correctness

*For any* month and year filter applied to data, the returned results should include only records with dates falling within that month and year.

**Validates: Requirements 13.7**

### Property 36: Session ID Cryptographic Security

*For any* generated session ID, it should have sufficient entropy (at least 128 bits) and be generated using a cryptographically secure random number generator.

**Validates: Requirements 14.4**

### Property 37: Session Destruction on Logout

*For any* user logout action, the session should be completely destroyed, and subsequent requests with the old session ID should be rejected.

**Validates: Requirements 14.6**

### Property 38: UTF-8 Urdu Text Round-Trip

*For any* Urdu text string, storing it in the database then retrieving it should return the identical text without corruption or character loss.

**Validates: Requirements 17.1, 17.5**

### Property 39: RTL Layout for Urdu Content

*For any* page displaying Urdu content, the text direction should be set to right-to-left (RTL).

**Validates: Requirements 17.2**

### Property 40: LTR Layout for English Content

*For any* page displaying English content, the text direction should be set to left-to-right (LTR).

**Validates: Requirements 17.3**

### Property 41: Mixed Language Display Integrity

*For any* content containing both Urdu and English text, displaying it should preserve both languages without corruption or character encoding issues.

**Validates: Requirements 17.4**

### Property 42: PDF Urdu Font Rendering

*For any* PDF containing Urdu text, the text should render correctly with proper font support and character display.

**Validates: Requirements 17.6**

### Property 43: Database Backup Completeness

*For any* backup operation, the resulting backup file should contain all tables and data from the database.

**Validates: Requirements 18.1**

### Property 44: Backup Filename Timestamp

*For any* backup file created, the filename should include a timestamp indicating when the backup was created.

**Validates: Requirements 18.2**

### Property 45: Backup File Validity

*For any* backup file created, it should be a valid SQL dump that can be parsed without errors.

**Validates: Requirements 18.3**

### Property 46: Backup Restore Round-Trip

*For any* database state, creating a backup then restoring it should result in an equivalent database state.

**Validates: Requirements 18.4**

### Property 47: Success Message Display

*For any* successful form submission, the system should display a success message to the user.

**Validates: Requirements 19.1**

### Property 48: Error Message Clarity

*For any* error condition, the system should display a clear, actionable error message that helps the user understand what went wrong.

**Validates: Requirements 19.2**

### Property 49: Destructive Action Confirmation

*For any* destructive action (delete, remove, purge), the system should display a confirmation dialog before executing the action.

**Validates: Requirements 19.5**

### Property 50: Sensitive Operation Audit Logging

*For any* sensitive operation (user creation, permission change, data deletion), the system should create an audit log entry with user ID, action, timestamp, and affected resources.

**Validates: Requirements 20.6**

### Property 51: Database Credential Protection

*For any* error message or log entry, database credentials (username, password, connection string) should never be exposed.

**Validates: Requirements 22.2**

### Property 52: Database Connection Closure

*For any* database operation, the connection should be properly closed after the operation completes or if an error occurs.

**Validates: Requirements 22.6**

### Property 53: Database Connection Failure Handling

*For any* database connection failure, the system should log the error with details and display a generic error message to the user.

**Validates: Requirements 22.7**

### Property 54: PHP File Upload Rejection

*For any* file upload attempt with a PHP file extension (.php, .phtml, .php3, .php4, .php5), the upload should be rejected.

**Validates: Requirements 23.6**

## Error Handling

### Error Handling Strategy

The system implements a centralized error handling approach with three layers:

1. **User-Facing Layer**: Generic, helpful messages that don't expose system internals
2. **Logging Layer**: Detailed error information for debugging and security monitoring
3. **Recovery Layer**: Graceful degradation and fallback mechanisms

### Error Categories

#### 1. Validation Errors
- **Trigger**: Invalid user input
- **User Message**: Specific field-level errors ("Date must be in YYYY-MM-DD format")
- **Logging**: Log validation failures with input data (sanitized)
- **Recovery**: Display form with errors highlighted, preserve valid inputs

#### 2. Authentication Errors
- **Trigger**: Login failures, session issues
- **User Message**: Generic ("Invalid username or password")
- **Logging**: Log with username, IP address, timestamp, failure reason
- **Recovery**: Implement account lockout after threshold, display lockout message

#### 3. Authorization Errors
- **Trigger**: Insufficient permissions
- **User Message**: "You don't have permission to access this resource"
- **Logging**: Log with user ID, attempted resource, timestamp
- **Recovery**: Redirect to appropriate page, suggest contacting administrator

#### 4. Database Errors
- **Trigger**: Connection failures, query errors, constraint violations
- **User Message**: "A database error occurred. Please try again later."
- **Logging**: Log full error message, query (with parameters sanitized), stack trace
- **Recovery**: Rollback transactions, close connections, display error page

#### 5. Security Errors
- **Trigger**: CSRF validation failure, XSS attempts, SQL injection attempts
- **User Message**: "Invalid request. Please try again."
- **Logging**: Log with full details, user ID, IP address, attack vector
- **Recovery**: Reject request, invalidate session if severe, consider IP blocking

#### 6. System Errors
- **Trigger**: File system errors, memory exhaustion, uncaught exceptions
- **User Message**: "An unexpected error occurred. Please contact support."
- **Logging**: Log full exception with stack trace, system state, memory usage
- **Recovery**: Display error page, send alert to administrators

### Error Logging Implementation

```php
// Log structure
[2024-01-15 14:23:45] ERROR: Database connection failed
Context: {
    "error_code": "HY000",
    "error_message": "SQLSTATE[HY000] [2002] Connection refused",
    "user_id": 42,
    "ip_address": "192.168.1.100",
    "request_uri": "/dashboard.php",
    "session_id": "abc123...",
    "memory_usage": "12.5 MB"
}
```

### Log Files Organization

```
/var/log/tanzeem/
├── error.log          # General application errors
├── security.log       # Security events (auth failures, CSRF, XSS attempts)
├── database.log       # Database errors and slow queries
├── audit.log          # Sensitive operations audit trail
└── access.log         # HTTP access log (Apache handles this)
```

### Log Rotation Policy

- Rotate daily at midnight
- Keep 30 days of logs
- Compress logs older than 7 days
- Maximum log file size: 100 MB (rotate if exceeded)
- Purge logs older than 30 days automatically

### Error Display Templates

#### Development Mode
```php
// Show detailed errors with stack traces
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

#### Production Mode
```php
// Hide errors from users, log everything
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/tanzeem/error.log');
```

## Testing Strategy

### Dual Testing Approach

The testing strategy employs both unit testing and property-based testing to achieve comprehensive coverage:

- **Unit Tests**: Verify specific examples, edge cases, and error conditions
- **Property Tests**: Verify universal properties across randomized inputs (minimum 100 iterations each)

Both approaches are complementary and necessary. Unit tests catch concrete bugs in specific scenarios, while property tests verify general correctness across a wide input space.

### Testing Framework: PHPUnit

We will use PHPUnit as the testing framework for both unit and property-based tests.

**Installation**:
```bash
composer require --dev phpunit/phpunit ^10.0
```

**Configuration** (`phpunit.xml`):
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit Tests">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Property Tests">
            <directory>tests/Property</directory>
        </testsuite>
        <testsuite name="Integration Tests">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

### Property-Based Testing Implementation

Each correctness property will be implemented as a property-based test with minimum 100 iterations.

**Example Property Test**:
```php
<?php
// tests/Property/DatabaseRoundTripTest.php

use PHPUnit\Framework\TestCase;

/**
 * Feature: comprehensive-testing-debugging, Property 1: Database Round-Trip Preservation
 * 
 * For any valid entity data, creating then retrieving should return equivalent data.
 */
class DatabaseRoundTripTest extends TestCase
{
    private const ITERATIONS = 100;
    
    public function testCollectionRoundTrip()
    {
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // Generate random collection data
            $originalData = TestDataGenerator::generateCollection();
            
            // Create in database
            $collectionId = $this->collectionModel->create($originalData);
            
            // Retrieve from database
            $retrievedData = $this->collectionModel->read($collectionId);
            
            // Assert equivalence
            $this->assertEquals($originalData['collection_date'], $retrievedData['collection_date']);
            $this->assertEquals($originalData['collector_name'], $retrievedData['collector_name']);
            $this->assertEquals($originalData['total_amount'], $retrievedData['total_amount']);
            
            // Cleanup
            $this->collectionModel->delete($collectionId);
        }
    }
}
```

### Test Data Generation

For property-based testing, we need generators that produce random valid inputs:

```php
<?php
// tests/Helpers/TestDataGenerator.php

class TestDataGenerator
{
    public static function generateCollection(): array
    {
        return [
            'collection_date' => self::randomDate(),
            'collector_name' => self::randomUrduName(),
            'rs_5000' => rand(0, 10),
            'rs_1000' => rand(0, 20),
            'rs_500' => rand(0, 30),
            'rs_100' => rand(0, 50),
            'rs_50' => rand(0, 100),
            'rs_20' => rand(0, 100),
            'rs_10' => rand(0, 100),
            'total_amount' => 0 // Will be calculated
        ];
    }
    
    public static function randomDate(string $start = '-1 year', string $end = 'now'): string
    {
        $startTimestamp = strtotime($start);
        $endTimestamp = strtotime($end);
        $randomTimestamp = rand($startTimestamp, $endTimestamp);
        return date('Y-m-d', $randomTimestamp);
    }
    
    public static function randomUrduName(): string
    {
        $urduNames = [
            'محمد احمد',
            'علی حسن',
            'فاطمہ زہرا',
            'عائشہ صدیقہ',
            'حسین علی'
        ];
        return $urduNames[array_rand($urduNames)];
    }
    
    public static function randomDecimal(int $min = 0, int $max = 10000, int $decimals = 2): float
    {
        $value = $min + (rand() / getrandmax()) * ($max - $min);
        return round($value, $decimals);
    }
}
```

### Unit Testing Strategy

Unit tests focus on specific examples and edge cases:

**Example Unit Test**:
```php
<?php
// tests/Unit/InputValidatorTest.php

use PHPUnit\Framework\TestCase;

class InputValidatorTest extends TestCase
{
    private InputValidator $validator;
    
    protected function setUp(): void
    {
        $this->validator = new InputValidator();
    }
    
    public function testValidDateFormat()
    {
        $this->assertTrue($this->validator->validateDate('2024-01-15'));
    }
    
    public function testInvalidDateFormat()
    {
        $this->assertFalse($this->validator->validateDate('15-01-2024'));
        $this->assertFalse($this->validator->validateDate('2024/01/15'));
        $this->assertFalse($this->validator->validateDate('invalid'));
    }
    
    public function testEmptyDateRejection()
    {
        $this->assertFalse($this->validator->validateDate(''));
    }
    
    public function testNullByteRejection()
    {
        $input = "test\0injection";
        $this->assertFalse($this->validator->sanitizeString($input));
    }
}
```

### Integration Testing

Integration tests verify that components work together correctly:

```php
<?php
// tests/Integration/AuthenticationFlowTest.php

use PHPUnit\Framework\TestCase;

class AuthenticationFlowTest extends TestCase
{
    public function testCompleteLoginFlow()
    {
        // Create test user
        $username = 'testuser_' . uniqid();
        $password = 'TestPass123!';
        $this->createTestUser($username, $password);
        
        // Attempt login
        $authManager = new AuthenticationManager();
        $result = $authManager->login($username, $password);
        
        // Verify success
        $this->assertTrue($result);
        $this->assertTrue($authManager->isAuthenticated());
        
        // Verify session ID was regenerated
        $sessionId = session_id();
        $this->assertNotEmpty($sessionId);
        
        // Logout
        $authManager->logout();
        $this->assertFalse($authManager->isAuthenticated());
        
        // Cleanup
        $this->deleteTestUser($username);
    }
    
    public function testAccountLockoutAfterFailedAttempts()
    {
        $username = 'testuser_' . uniqid();
        $password = 'TestPass123!';
        $this->createTestUser($username, $password);
        
        $authManager = new AuthenticationManager();
        
        // Attempt 5 failed logins
        for ($i = 0; $i < 5; $i++) {
            $authManager->login($username, 'WrongPassword');
        }
        
        // Verify account is locked
        $this->assertTrue($authManager->isAccountLocked($username));
        
        // Verify correct password also fails when locked
        $result = $authManager->login($username, $password);
        $this->assertFalse($result);
        
        // Cleanup
        $this->deleteTestUser($username);
    }
}
```

### Manual Testing Checklist

Some aspects require manual verification:

#### Security Testing
- [ ] Verify HTTPS is enforced in production
- [ ] Test CSRF protection by manually removing tokens
- [ ] Attempt SQL injection with common payloads
- [ ] Attempt XSS with common payloads
- [ ] Test session timeout by waiting 30 minutes
- [ ] Test account lockout by failing login 5 times

#### Browser Compatibility
- [ ] Test in Chrome (latest)
- [ ] Test in Firefox (latest)
- [ ] Test in Safari (latest)
- [ ] Test in Edge (latest)
- [ ] Test responsive design on mobile devices
- [ ] Test on tablets (iPad, Android)

#### Urdu/English Display
- [ ] Verify Urdu text displays correctly
- [ ] Verify RTL layout works for Urdu
- [ ] Verify English text displays correctly
- [ ] Verify LTR layout works for English
- [ ] Test mixed Urdu/English content
- [ ] Test PDF generation with Urdu fonts

#### User Experience
- [ ] Verify success messages appear after form submissions
- [ ] Verify error messages are clear and helpful
- [ ] Test loading indicators on slow operations
- [ ] Test confirmation dialogs for delete actions
- [ ] Verify breadcrumb navigation works
- [ ] Test keyboard navigation

#### Performance
- [ ] Measure dashboard load time (should be < 2 seconds)
- [ ] Test with large datasets (1000+ records)
- [ ] Verify queries use indexes (check EXPLAIN output)
- [ ] Test concurrent user access
- [ ] Monitor memory usage during operations

### Test Coverage Goals

- **Overall Code Coverage**: Minimum 70%
- **Critical Security Functions**: 100% coverage
- **Financial Calculations**: 100% coverage
- **Input Validation**: 100% coverage
- **Authentication/Authorization**: 100% coverage

### Continuous Testing

```bash
# Run all tests
composer test

# Run only unit tests
composer test:unit

# Run only property tests
composer test:property

# Run with coverage report
composer test:coverage

# Run specific test file
./vendor/bin/phpunit tests/Unit/InputValidatorTest.php
```

### Test Database Setup

Use a separate test database to avoid affecting production data:

```php
// tests/bootstrap.php

// Load test environment configuration
$_ENV['DB_NAME'] = 'tanzeem_test';
$_ENV['ENVIRONMENT'] = 'testing';

// Run migrations to create test database schema
require_once __DIR__ . '/../database/migrations/run.php';
```

### Property Test Tagging Convention

Each property-based test MUST include a comment tag referencing the design document:

```php
/**
 * Feature: comprehensive-testing-debugging, Property 26: Collection Total Calculation Invariant
 * 
 * For any collection record with denomination counts, the total amount should equal
 * the sum of (denomination_count × denomination_value) across all denominations.
 */
```

This ensures traceability between design properties and test implementation.

