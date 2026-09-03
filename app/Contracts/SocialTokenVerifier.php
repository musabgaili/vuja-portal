<?php

namespace App\Contracts;

use App\Support\Auth\SocialIdentity;

interface SocialTokenVerifier
{
    /** Verify a native SDK token and return a trusted identity, or throw. */
    public function verify(string $token, ?string $nonce = null): SocialIdentity;
}
