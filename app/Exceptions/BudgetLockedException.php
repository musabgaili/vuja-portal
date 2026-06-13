<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when something attempts to change a project's budget after the budget
 * has been locked (client approval onward) without going through an approved
 * change request. This is the model-level backstop behind the controller check.
 */
class BudgetLockedException extends RuntimeException
{
}
