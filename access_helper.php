<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the logged-in user has a specific permission
 * @param string $permission
 * @return bool
 */
function has_permission($permission) {
    // Admin has all permissions
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return true;
    }
    
    // Check specific permission
    if (isset($_SESSION['permissions'])) {
        $user_perms = explode(',', $_SESSION['permissions']);
        if (in_array($permission, $user_perms)) {
            return true;
        }
        // Also check for _edit permission implying view permission? 
        // Typically no, permissions are explicit. 
        // But if user has 'collections_edit', they probably need 'collections' view too.
        // For now, keep it simple/explicit.
    }
    
    return false;
}

/**
 * Get all available modules for permission settings
 * New modules should be added here to automatically appear in user settings
 * @return array
 */
function get_all_modules() {
    return [
        'dashboard'   => ['label' => 'ڈیش بورڈ (Dashboard)', 'has_edit' => false],
        'collections' => ['label' => 'چندہ (Collections)', 'has_edit' => true],
        'ledger'      => ['label' => 'کھاتہ (Monthly Ledger)', 'has_edit' => true],
        'salaries'    => ['label' => 'تنخواہ (Salaries)', 'has_edit' => true],
        'notices'     => ['label' => 'نوٹیفکیشن (Notices)', 'has_edit' => true],
        'reports'     => ['label' => 'رپورٹس (Reports)', 'has_edit' => false],
        'funeral'     => ['label' => 'تجہیز و تکفین (Funeral)', 'has_edit' => true],
    ];
}
?>
