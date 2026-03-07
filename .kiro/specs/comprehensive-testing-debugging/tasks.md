# Implementation Plan: Comprehensive Testing, Debugging, and Improvement

## Overview

This implementation plan transforms the Tanzeem-e-Aulaad Hazrat Haji Bahadur Management System into a production-ready application through security hardening, comprehensive testing infrastructure, and code quality improvements. The plan follows a layered approach: foundation (security and infrastructure), core functionality (validation, error handling, authentication), testing (property-based and unit tests), and integration.

## Tasks

- [ ] 1. Set up project structure and testing infrastructure
  - Create directory structure: `src/`, `tests/Unit/`, `tests/Property/`, `tests/Integration/`, `tests/Helpers/`
  - Install PHPUnit via Composer: `composer require --dev phpunit/phpunit ^10.0`
  - Create `phpunit.xml` configuration file with test suites
  - Create `tests/bootstrap.php` for test environment setup
  - Create `.env` file for environment configuration
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [ ] 2. Implement security foundation layer
  - [ ] 2.1 Create SecurityManager class
    - Implement CSRF token generation with cryptographically secure random bytes
    - Implement CSRF token validation with timing-safe comparison
    - Implement XSS prevention methods: `escapeHTML()`, `escapeJS()`, `escapeURL()`
    - Implement secure session initialization and regeneration
    - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2, 3.3, 4.1, 4.2, 5.2, 5.3, 14.4_
  
  - [ ]* 2.2 Write property test for SecurityManager
    - **Property 5: CSRF Token Uniqueness**
    - **Property 6: CSRF Token Validation**
    - **Property 7: CSRF Token Regeneration**
    - **Property 36: Session ID Cryptographic Security**
    - **Validates: Requirements 4.1, 4.2, 4.4, 14.4**

- [ ] 3. Implement input validation layer
  - [ ] 3.1 Create InputValidator class
    - Implement date validation (YYYY-MM-DD format)
    - Implement numeric validation (integers only)
    - Implement decimal validation (two decimal places max)
    - Implement length validation (min/max constraints)
    - Implement dangerous character rejection (null bytes, control chars)
    - Implement sanitization methods for strings, numerics, HTML
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_
  
  - [ ]* 3.2 Write property tests for InputValidator
    - **Property 14: Date Format Validation**
    - **Property 15: Numeric Input Validation**
    - **Property 16: Decimal Amount Validation**
    - **Property 17: Maximum Length Enforcement**
    - **Property 18: Dangerous Character Rejection**
    - **Property 19: Validation Error Message Return**
    - **Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5, 7.6**
  
  - [ ]* 3.3 Write unit tests for InputValidator edge cases
    - Test empty string handling
    - Test boundary values (min/max lengths)
    - Test special characters in different contexts
    - Test Urdu text validation
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [ ] 4. Implement error handling and logging system
  - [ ] 4.1 Create ErrorHandler class
    - Implement error logging with context
    - Implement exception logging with stack traces
    - Implement security event logging
    - Implement user-facing error display (generic messages)
    - Implement validation error display
    - Implement environment-based error reporting configuration
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7_
  
  - [ ] 4.2 Create Logger class
    - Implement PSR-3 compatible logging levels (debug, info, warning, error, critical)
    - Implement structured logging with JSON format
    - Implement log rotation (daily, 30-day retention)
    - Implement log file organization (error.log, security.log, database.log, audit.log)
    - _Requirements: 8.1, 8.3, 8.4, 20.6_
  
  - [ ]* 4.3 Write property tests for error handling
    - **Property 20: Database Error Logging**
    - **Property 21: Generic User Error Messages**
    - **Property 22: Authentication Failure Logging**
    - **Property 23: Authorization Failure Logging**
    - **Property 24: Exception Logging**
    - **Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.7**

- [ ] 5. Checkpoint - Verify foundation layer
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Implement database layer with security
  - [ ] 6.1 Create DatabaseConnection class
    - Implement PDO connection with prepared statements only
    - Implement connection configuration from .env file
    - Implement query execution with parameter binding
    - Implement transaction support (begin, commit, rollback)
    - Implement connection error handling and logging
    - Implement automatic connection closure
    - _Requirements: 2.4, 22.1, 22.2, 22.3, 22.4, 22.5, 22.6, 22.7_
  
  - [ ] 6.2 Create BaseModel abstract class
    - Implement CRUD operations using prepared statements
    - Implement abstract validate() method
    - Implement findAll() with filtering support
    - Integrate with InputValidator for data validation
    - _Requirements: 2.4, 7.1, 7.2, 7.3, 10.6_
  
  - [ ]* 6.3 Write property tests for database layer
    - **Property 1: Database Round-Trip Preservation**
    - **Property 2: SQL Injection Rejection**
    - **Property 51: Database Credential Protection**
    - **Property 52: Database Connection Closure**
    - **Property 53: Database Connection Failure Handling**
    - **Validates: Requirements 2.4, 2.5, 22.2, 22.6, 22.7**

- [ ] 7. Enhance database schema with constraints and indexes
  - [ ] 7.1 Create database migration script
    - Add foreign key constraints to collections, income, expenses tables
    - Add indexes on date columns and foreign keys
    - Add CHECK constraints for non-negative amounts
    - Ensure utf8mb4 charset and collation on all tables
    - _Requirements: 2.5, 17.5, 22.1, 22.3_
  
  - [ ] 7.2 Create audit_log table
    - Implement table schema with user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at
    - Add indexes on user_id, action, created_at
    - _Requirements: 20.6_
  
  - [ ] 7.3 Enhance users table
    - Add failed_login_attempts column
    - Add account_locked_until column
    - Add last_login column
    - Add password_hash column (if not exists)
    - _Requirements: 5.1, 5.4, 5.5, 5.6_

- [ ] 8. Implement authentication system
  - [ ] 8.1 Create AuthenticationManager class
    - Implement login() with password verification using password_verify()
    - Implement logout() with session destruction
    - Implement isAuthenticated() check
    - Implement password hashing with password_hash() using bcrypt
    - Implement password policy enforcement (min 8 chars, uppercase, lowercase, number)
    - Implement failed login tracking and account lockout (5 attempts, 30-minute lockout)
    - Implement session ID regeneration on login
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 14.6, 14.7_
  
  - [ ]* 8.2 Write property tests for authentication
    - **Property 9: Password Complexity Enforcement**
    - **Property 10: Session ID Regeneration on Login**
    - **Property 37: Session Destruction on Logout**
    - **Validates: Requirements 5.1, 5.3, 14.6, 14.7**
  
  - [ ]* 8.3 Write unit tests for authentication edge cases
    - Test login with incorrect password
    - Test login with non-existent user
    - Test account lockout after 5 failed attempts
    - Test account unlock after timeout
    - Test session timeout after 30 minutes
    - _Requirements: 5.4, 5.5, 5.6, 14.5_

- [ ] 9. Implement authorization system
  - [ ] 9.1 Create AuthorizationManager class
    - Implement hasPermission() for resource-level checks
    - Implement hasRole() for role-based checks
    - Implement canAccessResource() with permission verification
    - Implement role assignment and revocation
    - Implement getUserRoles() for current user
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_
  
  - [ ]* 9.2 Write property tests for authorization
    - **Property 12: Authorization Denial for Insufficient Permissions**
    - **Property 13: Permission Check Before Data Access**
    - **Validates: Requirements 6.3, 6.6**
  
  - [ ]* 9.3 Write unit tests for authorization scenarios
    - Test admin access to all resources
    - Test user access to limited resources
    - Test unauthorized access denial
    - Test role-based page access
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ] 10. Checkpoint - Verify security layer
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Implement financial calculation components
  - [ ] 11.1 Create FinancialCalculator class
    - Implement calculateCollectionTotal() for denomination-based totals
    - Implement calculateRunningBalance() for cumulative transaction balances
    - Implement calculateFundTotals() for multi-fund aggregation
    - Implement calculateNetCapital() for income minus expenses
    - Implement calculateMonthlyTotals() and calculateYearlyTotals()
    - Use bcmath functions for precise decimal arithmetic
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 13.4_
  
  - [ ]* 11.2 Write property tests for financial calculations
    - **Property 26: Collection Total Calculation Invariant**
    - **Property 27: Dashboard Aggregate Accuracy**
    - **Property 28: Running Balance Cumulative Invariant**
    - **Property 29: Multi-Fund Total Invariant**
    - **Property 30: Financial Report Sum Invariant**
    - **Property 32: Net Capital Calculation**
    - **Validates: Requirements 11.1, 11.2, 11.3, 11.4, 11.6, 13.1, 13.2, 13.3, 13.4**
  
  - [ ]* 11.3 Write unit tests for financial edge cases
    - Test zero amounts
    - Test negative amounts (should be rejected)
    - Test very large amounts (boundary testing)
    - Test rounding behavior for two decimal places
    - _Requirements: 11.1, 11.4, 11.5_

- [ ] 12. Implement test data generator for property tests
  - [ ] 12.1 Create TestDataGenerator class
    - Implement generateCollection() with random valid data
    - Implement generateIncome() with random fund allocations
    - Implement generateExpense() with random fund allocations
    - Implement generateEmployee() with random Urdu names
    - Implement randomDate(), randomDecimal(), randomUrduText() helpers
    - Ensure generated data respects validation rules
    - _Requirements: 1.3, 17.1_

- [ ] 13. Refactor existing pages with security integration
  - [ ] 13.1 Refactor collections management pages
    - Add CSRF token to all forms
    - Add input validation using InputValidator
    - Add XSS prevention using SecurityManager::escapeHTML()
    - Add authorization checks before data access
    - Add error handling with ErrorHandler
    - Replace direct SQL with prepared statements via DatabaseConnection
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 4.1, 4.2, 6.6, 7.1, 7.2, 7.3, 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_
  
  - [ ] 13.2 Refactor income management pages
    - Add CSRF token to all forms
    - Add input validation for dates, descriptions, fund amounts
    - Add XSS prevention for output
    - Add authorization checks
    - Add error handling
    - Replace direct SQL with prepared statements
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 4.1, 4.2, 6.6, 7.1, 7.2, 7.3, 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_
  
  - [ ] 13.3 Refactor expense management pages
    - Add CSRF token to all forms
    - Add input validation for dates, descriptions, fund amounts
    - Add XSS prevention for output
    - Add authorization checks
    - Add error handling
    - Replace direct SQL with prepared statements
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 4.1, 4.2, 6.6, 7.1, 7.2, 7.3, 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_
  
  - [ ] 13.4 Refactor employee management pages
    - Add CSRF token to all forms
    - Add input validation for employee data
    - Add XSS prevention for Urdu names and descriptions
    - Add authorization checks
    - Add error handling
    - Replace direct SQL with prepared statements
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 4.1, 4.2, 6.6, 7.1, 7.2, 7.3, 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_
  
  - [ ] 13.5 Refactor dashboard page
    - Add authorization checks for dashboard access
    - Integrate FinancialCalculator for all statistics
    - Add XSS prevention for all displayed data
    - Add error handling for chart data generation
    - Verify aggregate calculations match database sums
    - _Requirements: 3.1, 6.6, 11.2, 13.1, 13.2, 13.3, 13.4, 13.5, 13.6, 13.7_

- [ ] 14. Implement session security enhancements
  - [ ] 14.1 Create session security middleware
    - Implement session timeout enforcement (30 minutes)
    - Implement session hijacking detection (IP and user agent validation)
    - Implement secure session cookie configuration (httponly, secure, samesite)
    - Implement session ID regeneration on privilege escalation
    - _Requirements: 5.7, 14.1, 14.2, 14.3, 14.4, 14.5_
  
  - [ ]* 14.2 Write property tests for session security
    - **Property 11: Session Hijacking Detection**
    - **Validates: Requirements 5.7**
  
  - [ ]* 14.3 Write unit tests for session scenarios
    - Test session timeout after 30 minutes of inactivity
    - Test session invalidation on IP change
    - Test session invalidation on user agent change
    - Test secure cookie attributes
    - _Requirements: 5.7, 14.1, 14.2, 14.3, 14.5_

- [ ] 15. Checkpoint - Verify core functionality
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 16. Implement UTF-8 and Urdu support verification
  - [ ] 16.1 Verify database UTF-8 configuration
    - Ensure all tables use utf8mb4 charset
    - Ensure all text columns use utf8mb4_unicode_ci collation
    - Verify connection charset is set to utf8mb4
    - _Requirements: 17.5, 22.1_
  
  - [ ]* 16.2 Write property tests for Urdu text handling
    - **Property 38: UTF-8 Urdu Text Round-Trip**
    - **Property 41: Mixed Language Display Integrity**
    - **Validates: Requirements 17.1, 17.4, 17.5**
  
  - [ ]* 16.3 Write unit tests for RTL/LTR layout
    - Test RTL direction for Urdu content pages
    - Test LTR direction for English content pages
    - Test mixed content display
    - _Requirements: 17.2, 17.3, 17.4_

- [ ] 17. Implement PDF generation with Urdu support
  - [ ] 17.1 Integrate PDF library with Urdu font support
    - Install TCPDF or mPDF via Composer
    - Configure Urdu font (Noto Nastaliq Urdu or similar)
    - Create PDFGenerator class with Urdu text support
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 17.6_
  
  - [ ] 17.2 Implement report PDF generation
    - Implement generateCollectionReport() with Urdu support
    - Implement generateIncomeReport() with Urdu support
    - Implement generateExpenseReport() with Urdu support
    - Implement generateFinancialSummary() with charts
    - _Requirements: 12.1, 12.2, 12.3, 12.4_
  
  - [ ]* 17.3 Write property tests for PDF generation
    - **Property 31: PDF Generation Validity**
    - **Property 42: PDF Urdu Font Rendering**
    - **Validates: Requirements 12.4, 17.6**

- [ ] 18. Implement backup and restore functionality
  - [ ] 18.1 Create BackupManager class
    - Implement createBackup() with mysqldump
    - Implement backup filename with timestamp
    - Implement backup file validation
    - Implement restoreBackup() from SQL dump
    - Implement automated backup scheduling (daily)
    - _Requirements: 18.1, 18.2, 18.3, 18.4, 18.5_
  
  - [ ]* 18.2 Write property tests for backup functionality
    - **Property 43: Database Backup Completeness**
    - **Property 44: Backup Filename Timestamp**
    - **Property 45: Backup File Validity**
    - **Property 46: Backup Restore Round-Trip**
    - **Validates: Requirements 18.1, 18.2, 18.3, 18.4**

- [ ] 19. Implement user feedback and confirmation dialogs
  - [ ] 19.1 Create JavaScript confirmation dialogs
    - Implement delete confirmation for all destructive actions
    - Add loading indicators for form submissions
    - Add success message display after operations
    - Add error message display with clear descriptions
    - _Requirements: 19.1, 19.2, 19.3, 19.4, 19.5_
  
  - [ ]* 19.2 Write property tests for user feedback
    - **Property 47: Success Message Display**
    - **Property 48: Error Message Clarity**
    - **Property 49: Destructive Action Confirmation**
    - **Validates: Requirements 19.1, 19.2, 19.5**

- [ ] 20. Implement audit logging for sensitive operations
  - [ ] 20.1 Create AuditLogger class
    - Implement logCreate() for record creation
    - Implement logUpdate() for record updates with old/new values
    - Implement logDelete() for record deletion
    - Implement logLogin() for authentication events
    - Implement logPermissionChange() for authorization changes
    - Capture IP address and user agent for all audit entries
    - _Requirements: 20.1, 20.2, 20.3, 20.4, 20.5, 20.6_
  
  - [ ]* 20.2 Write property tests for audit logging
    - **Property 50: Sensitive Operation Audit Logging**
    - **Validates: Requirements 20.6**

- [ ] 21. Implement file upload security (if applicable)
  - [ ] 21.1 Create FileUploadHandler class
    - Implement file type validation (whitelist approach)
    - Implement PHP file extension rejection
    - Implement file size validation
    - Implement secure file storage outside web root
    - Implement virus scanning integration (if available)
    - _Requirements: 23.1, 23.2, 23.3, 23.4, 23.5, 23.6_
  
  - [ ]* 21.2 Write property tests for file upload security
    - **Property 54: PHP File Upload Rejection**
    - **Validates: Requirements 23.6**

- [ ] 22. Checkpoint - Verify all components integrated
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 23. Implement XSS prevention across all output contexts
  - [ ] 23.1 Audit all output points in existing code
    - Identify all echo, print, and output statements
    - Categorize by context: HTML, JavaScript, URL, CSS
    - Apply appropriate escaping using SecurityManager methods
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [ ]* 23.2 Write property tests for XSS prevention
    - **Property 3: HTML Output Escaping**
    - **Property 4: JavaScript Context Encoding**
    - **Validates: Requirements 3.1, 3.2, 3.5**

- [ ] 24. Implement CSRF protection across all forms
  - [ ] 24.1 Add CSRF tokens to all state-changing forms
    - Add token generation to all forms (POST, PUT, DELETE)
    - Add token validation to all form handlers
    - Add token regeneration after successful submission
    - Ensure GET requests never change state
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_
  
  - [ ]* 24.2 Write property tests for CSRF protection
    - **Property 8: State-Changing Operations CSRF Protection**
    - **Validates: Requirements 4.5**

- [ ] 25. Implement date filtering and report generation
  - [ ] 25.1 Create ReportGenerator class
    - Implement monthly report generation with date filtering
    - Implement yearly report generation
    - Implement custom date range filtering
    - Integrate with FinancialCalculator for accurate totals
    - Integrate with PDFGenerator for PDF output
    - _Requirements: 11.5, 11.6, 12.1, 12.2, 12.3, 13.7_
  
  - [ ]* 25.2 Write property tests for date filtering
    - **Property 35: Date Filter Correctness**
    - **Validates: Requirements 13.7**
  
  - [ ]* 25.3 Write unit tests for report generation
    - Test monthly report with various months
    - Test yearly report with various years
    - Test custom date range reports
    - Test empty result handling
    - _Requirements: 11.5, 11.6, 12.1, 12.2_

- [ ] 26. Implement chart data generation with validation
  - [ ] 26.1 Create ChartDataGenerator class
    - Implement generateIncomeExpenseChart() for dashboard
    - Implement generateFundDistributionChart()
    - Implement generateMonthlyTrendsChart()
    - Validate chart data matches database aggregates
    - _Requirements: 13.5_
  
  - [ ]* 26.2 Write property tests for chart data
    - **Property 33: Chart Data Accuracy**
    - **Validates: Requirements 13.5**

- [ ] 27. Implement funeral statistics (if applicable)
  - [ ] 27.1 Create FuneralStatisticsCalculator class
    - Implement calculateFuneralCount()
    - Implement calculateFuneralTotals()
    - Implement generateFuneralReport()
    - _Requirements: 13.6_
  
  - [ ]* 27.2 Write property tests for funeral statistics
    - **Property 34: Funeral Statistics Calculation**
    - **Validates: Requirements 13.6**

- [ ] 28. Implement comprehensive validation for all CRUD operations
  - [ ] 28.1 Add validation to all create operations
    - Validate all input fields before database insertion
    - Return validation errors to user
    - Log validation failures
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 10.6_
  
  - [ ] 28.2 Add validation to all update operations
    - Validate all input fields before database update
    - Return validation errors to user
    - Log validation failures
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 10.6_
  
  - [ ]* 28.3 Write property tests for CRUD validation
    - **Property 25: Invalid Data Rejection**
    - **Validates: Requirements 10.6**

- [ ] 29. Create integration tests for complete workflows
  - [ ]* 29.1 Write integration test for collection workflow
    - Test complete flow: login → create collection → view collection → edit collection → delete collection → logout
    - Verify CSRF protection at each step
    - Verify authorization at each step
    - Verify data persistence and retrieval
    - _Requirements: 2.1, 4.1, 6.6, 10.1, 10.2, 10.3, 10.4, 10.5_
  
  - [ ]* 29.2 Write integration test for income/expense workflow
    - Test complete flow: login → create income → create expense → view dashboard → generate report → logout
    - Verify financial calculations are accurate
    - Verify PDF generation works
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 11.1, 11.2, 11.4, 12.1, 13.4_
  
  - [ ]* 29.3 Write integration test for authentication workflow
    - Test login, session management, logout
    - Test failed login attempts and account lockout
    - Test session timeout
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 14.5, 14.6, 14.7_

- [ ] 30. Checkpoint - Verify all integration tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 31. Code quality improvements and refactoring
  - [ ] 31.1 Refactor code for consistency
    - Apply consistent naming conventions (PSR-12)
    - Remove code duplication
    - Extract common functionality into helper functions
    - Improve code comments and documentation
    - _Requirements: 21.1, 21.2, 21.3, 21.4_
  
  - [ ] 31.2 Optimize database queries
    - Add indexes to frequently queried columns
    - Optimize JOIN operations
    - Use EXPLAIN to verify query performance
    - Implement query result caching where appropriate
    - _Requirements: 22.4, 22.5_
  
  - [ ] 31.3 Implement error page templates
    - Create 404 error page
    - Create 403 forbidden page
    - Create 500 internal error page
    - Create generic error page with user-friendly messages
    - _Requirements: 8.2, 19.2_

- [ ] 32. Create comprehensive documentation
  - [ ] 32.1 Create developer documentation
    - Document architecture and design decisions
    - Document security measures implemented
    - Document testing strategy and how to run tests
    - Document deployment procedures
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 21.5_
  
  - [ ] 32.2 Create user documentation
    - Document user roles and permissions
    - Document how to use each feature
    - Document backup and restore procedures
    - Create troubleshooting guide
    - _Requirements: 6.1, 6.2, 18.1, 18.4_
  
  - [ ] 32.3 Create security documentation
    - Document security best practices for deployment
    - Document password policies
    - Document session security configuration
    - Document HTTPS setup requirements
    - _Requirements: 5.1, 5.2, 5.7, 14.1, 14.2, 14.3, 24.1_

- [ ] 33. Create deployment checklist and production configuration
  - [ ] 33.1 Create production .env template
    - Set DEBUG_MODE=false
    - Set ENVIRONMENT=production
    - Configure secure session settings
    - Configure log paths
    - _Requirements: 8.5, 8.6, 24.2, 24.3_
  
  - [ ] 33.2 Create deployment script
    - Run database migrations
    - Set proper file permissions
    - Configure Apache/Nginx for HTTPS
    - Set up log rotation
    - Configure automated backups
    - _Requirements: 18.5, 22.1, 24.1, 24.2_
  
  - [ ] 33.3 Create security hardening checklist
    - Verify HTTPS is enforced
    - Verify database credentials are secure
    - Verify file upload directory is outside web root
    - Verify error display is disabled in production
    - Verify session cookies are secure
    - _Requirements: 14.1, 14.2, 14.3, 22.2, 23.4, 24.1_

- [ ] 34. Final checkpoint - Run all tests and verify production readiness
  - Run complete test suite (unit, property, integration)
  - Verify all 54 correctness properties pass
  - Verify code coverage meets 70% minimum
  - Verify all security measures are in place
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional testing tasks and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties with 100+ iterations each
- Unit tests validate specific examples and edge cases
- Integration tests validate complete workflows
- Checkpoints ensure incremental validation and provide opportunities for user feedback
- All security-critical components (authentication, authorization, input validation, CSRF, XSS prevention) have 100% test coverage requirements
- Financial calculation components have 100% test coverage requirements due to accuracy criticality
- The implementation follows a layered approach: foundation → core → testing → integration → deployment
