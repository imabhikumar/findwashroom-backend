<?php

namespace App\Models;

/**
 * Admin is stored in the same `users` table (role = admin),
 * but kept as a separate model for clean domain boundaries.
 */
class Admin extends User
{
    // Intentionally no extra fields. Role governance is handled in repository/middleware.
}

