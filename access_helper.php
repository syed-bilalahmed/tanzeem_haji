<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the logged-in user has a specific permission
 * @param string $permission
 * @return bool
 */
if (!function_exists('has_permission')) {
    function has_permission($permission) {
        // Admin has all permissions
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return true;
        }
        
        // Check specific permission
        if (isset($_SESSION['permissions'])) {
            $user_perms = explode(',', $_SESSION['permissions']);
            
            // If checking for explicit edit permission (e.g. 'collections_edit')
            if (str_ends_with($permission, '_edit')) {
                return in_array($permission, $user_perms);
            }
            
            // If checking for view permission (e.g. 'collections')
            // Then having EITHER 'collections' OR 'collections_edit' Grants View Access.
            if (in_array($permission, $user_perms) || in_array($permission . '_edit', $user_perms)) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * Get all available modules for permission settings
 * New modules should be added here to automatically appear in user settings
 * @return array
 */
if (!function_exists('get_all_modules')) {
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
}
?>
