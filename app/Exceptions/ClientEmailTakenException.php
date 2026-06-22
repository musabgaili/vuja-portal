<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an inline "add client" attempt uses an email that already belongs
 * to a staff (internal) account — we never silently turn staff into a client.
 */
class ClientEmailTakenException extends RuntimeException
{
}
