<?php
/**
 * Helper functions for consistent password hashing
 */

define('SALT', 'fitlife2023'); // Fixed salt for consistent hashing

/**
 * Hash a password using a consistent method
 * This will always generate the same hash for the same password
 */
function hashPassword($password) {
    return hash('sha256', $password . SALT);
}

/**
 * Verify a password against a hash
 */
function verifyPassword($password, $hash) {
    return hashPassword($password) === $hash;
}
