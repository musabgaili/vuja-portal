<?php

namespace App\Support\Auth;

/** Verified identity extracted from a Google ID token or Apple identity token. */
final class SocialIdentity
{
    public function __construct(
        public string $provider,
        public string $providerId,
        public ?string $email,
        public ?string $name,
        public bool $emailVerified,
    ) {}
}
