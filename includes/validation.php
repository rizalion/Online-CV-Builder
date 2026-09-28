<?php
/**
 * Server-Side Validation Helpers
 */

/**
 * Validate an email address.
 */
function validate_email(string $email): ?string
{
    $email = trim($email);
    if (empty($email)) {
        return 'Email is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }
    return null;
}

/**
 * Validate a required text field.
 */
function validate_required(string $value, string $fieldName, int $maxLength = 255): ?string
{
    $value = trim($value);
    if (empty($value)) {
        return "$fieldName is required.";
    }
    if (strlen($value) > $maxLength) {
        return "$fieldName must be under $maxLength characters.";
    }
    return null;
}

/**
 * Validate an optional text field (if provided, check length).
 */
function validate_optional(string $value, string $fieldName, int $maxLength = 255): ?string
{
    $value = trim($value);
    if (!empty($value) && strlen($value) > $maxLength) {
        return "$fieldName must be under $maxLength characters.";
    }
    return null;
}

/**
 * Validate a phone number.
 */
function validate_phone(string $phone): ?string
{
    $phone = trim($phone);
    if (!empty($phone) && !preg_match('/^[+]?[\d\s\-()]{7,20}$/', $phone)) {
        return 'Please enter a valid phone number.';
    }
    return null;
}

/**
 * Validate a URL.
 */
function validate_url(string $url, string $fieldName = 'URL'): ?string
{
    $url = trim($url);
    if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
        return "Please enter a valid $fieldName.";
    }
    return null;
}

/**
 * Validate a date string (YYYY-MM-DD).
 */
function validate_date(string $date, string $fieldName = 'Date', bool $required = false): ?string
{
    $date = trim($date);
    if (empty($date)) {
        return $required ? "$fieldName is required." : null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        return "Please enter a valid date for $fieldName.";
    }
    return null;
}

/**
 * Validate that end_date is after start_date (when both present).
 */
function validate_date_range(string $start, string $end): ?string
{
    if (empty($start) || empty($end)) {
        return null;
    }
    $s = new DateTime($start);
    $e = new DateTime($end);
    if ($e < $s) {
        return 'End date must be after start date.';
    }
    return null;
}

/**
 * Validate proficiency level (1-5).
 */
function validate_proficiency(int $level): ?string
{
    if ($level < 1 || $level > 5) {
        return 'Proficiency must be between 1 and 5.';
    }
    return null;
}

/**
 * Sanitize a string for safe output.
 */
function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize an array of values recursively.
 */
function sanitize_array(array $data): array
{
    $clean = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $clean[$key] = sanitize_array($value);
        } else {
            $clean[$key] = sanitize((string)$value);
        }
    }
    return $clean;
}

/**
 * Collect validation errors from a set of checks.
 * Pass an array of nullable error strings; non-null entries are collected.
 */
function collect_errors(array $checks): array
{
    return array_values(array_filter($checks, fn($e) => $e !== null));
}
