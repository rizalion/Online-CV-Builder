<?php
/**
 * Template Base — Shared helper functions for all CV templates.
 * 
 * Include this at the top of every template file.
 * Expects $cv array to be set before inclusion.
 */

/**
 * Format a date range (e.g., "Jan 2020 – Present").
 */
function tpl_date_range(?string $start, ?string $end): string
{
    $s = $start ? (new DateTime($start))->format('M Y') : '';
    $e = $end ? (new DateTime($end))->format('M Y') : 'Present';
    return $s . ' – ' . $e;
}

/**
 * Render proficiency dots (●●●○○ for level 3/5).
 */
function tpl_proficiency_dots(int $level): string
{
    $filled = str_repeat('●', $level);
    $empty = str_repeat('○', 5 - $level);
    return '<span class="prof-dots">' . $filled . '<span class="dots-empty">' . $empty . '</span></span>';
}

/**
 * Render a proficiency bar.
 */
function tpl_proficiency_bar(int $level, string $color = '#6366f1'): string
{
    $pct = $level * 20;
    return '<div class="skill-bar"><div class="skill-bar-fill" style="width:'.$pct.'%;background:'.$color.';"></div></div>';
}

/**
 * Safe output helper — shorthand for htmlspecialchars.
 */
function t(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

/**
 * Get the profile picture as a base64 data URI (for PDF rendering).
 */
function tpl_profile_picture_base64(?string $path): string
{
    if (empty($path)) {
        return '';
    }

    // Try absolute path first
    $fullPath = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__) . '/') . $path;
    if (!file_exists($fullPath)) {
        return '';
    }

    $mime = mime_content_type($fullPath);
    $data = base64_encode(file_get_contents($fullPath));
    return "data:{$mime};base64,{$data}";
}

/**
 * Get profile picture URL or base64 depending on context.
 */
function tpl_profile_src(?string $path, bool $forPdf = false): string
{
    if ($forPdf) {
        return tpl_profile_picture_base64($path);
    }
    if (empty($path)) {
        return '';
    }
    return (defined('BASE_URL') ? BASE_URL : '') . '/' . $path;
}
