<?php
class UIHelper {
    
    public static function generateFooter($customText = null) {
        $year = date('Y');
        $text = $customText ?: "REM Project. All rights reserved.";
        return "<footer class=\"dashboard-footer\">&copy; {$year} {$text}</footer>";
    }
    
    public static function formatPropertyStatus($status) {
        return $status === 'for_sale' ? 'For Sale' : 'For Rent';
    }
    
    public static function formatDate($date, $format = 'F j, Y') {
        return date($format, strtotime($date));
    }
    
    public static function formatPrice($price, $includeDecimals = false) {
        if ($includeDecimals) {
            return '€' . number_format($price, 2);
        }
        return '€' . number_format($price);
    }
}
