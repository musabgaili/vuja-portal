<?php

namespace App\Services\Clients;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Exceptions\ClientEmailTakenException;
use App\Mail\GenericNotification;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Creates / reuses client accounts for the inline "add client" flows (project,
 * proposal, opportunity) and sends activation invitations.
 *
 * A created client is an ACTIVE account with a random password (so it appears in
 * client dropdowns immediately) plus a linked CRM contact. The client sets their
 * own password later through the emailed invitation; we never expose a password.
 */
class ClientProvisioningService
{
    /**
     * Find an existing client by email, or create a new active client account
     * plus a linked CRM contact.
     *
     * @return array{user: User, created: bool}
     *
     * @throws ClientEmailTakenException when the email belongs to a staff account.
     */
    public function findOrCreateClient(array $data, ?int $ownerId = null): array
    {
        $existing = User::where('email', $data['email'])->first();
        if ($existing) {
            if (! $existing->isClient()) {
                throw new ClientEmailTakenException();
            }

            return ['user' => $existing, 'created' => false];
        }

        $user = DB::transaction(function () use ($data, $ownerId) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make(Str::random(40)),
                'role' => UserRole::CLIENT,
                'type' => 'client',
                'status' => UserStatus::ACTIVE,
            ]);

            // Mirror into the CRM address book, linked to the new account.
            $companyId = null;
            if (! empty($data['company'])) {
                $companyId = Company::firstOrCreate(
                    ['name' => $data['company']],
                    ['owner_id' => $ownerId],
                )->id;
            }

            Contact::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company_id' => $companyId,
                'owner_id' => $ownerId,
                'user_id' => $user->id,
            ]);

            return $user;
        });

        return ['user' => $user, 'created' => true];
    }

    /**
     * Email the client a signed link to set their password and activate their
     * account. Failures are logged and swallowed so they never break the action
     * that triggered the invite.
     */
    public function sendInvitation(User $user, ?User $invitedBy = null): bool
    {
        try {
            $url = URL::temporarySignedRoute('invite.accept', now()->addDays(14), ['user' => $user->id]);
            $lang = app()->getLocale() === 'ar' ? 'ar' : 'en';

            Mail::to($user->email)->send(new GenericNotification(
                __('portal.invite.email_subject'),
                __('portal.invite.email_heading'),
                __('portal.invite.email_body', ['inviter' => $invitedBy?->name ?: config('app.name', 'VujaDe')]),
                $url,
                __('portal.invite.email_cta'),
                $lang,
            ));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Client invitation email failed', [
                'user' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
