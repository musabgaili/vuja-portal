<?php

namespace Tests\Feature;

use App\Actions\Payments\SendPaymentRequestAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Jobs\ProcessMoyasarWebhook;
use App\Mail\PaymentRequestMail;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        config([
            'services.moyasar.publishable_key' => 'pk_test_example',
            'services.moyasar.secret_key' => 'sk_test_example',
            'services.moyasar.webhook_secret' => 'webhook-test-secret',
        ]);
    }

    public function test_manager_internal_dashboard_renders_with_payment_nav(): void
    {
        $manager = $this->user(UserRole::MANAGER, 'manager');

        $this->actingAs($manager)->get(route('internal.dashboard'))
            ->assertOk()
            ->assertSee(__('portal.payments.title'));
    }

    public function test_only_manager_can_create_payment_request(): void
    {
        $employee = $this->user(UserRole::EMPLOYEE, 'employee');

        $this->actingAs($employee)->get(route('payment-requests.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('payment-requests.create'))->assertForbidden();
        $this->actingAs($employee)->post(route('payment-requests.store'), $this->payload())
            ->assertForbidden();

        $manager = $this->user(UserRole::MANAGER, 'manager');
        $client = User::factory()->create([
            'email' => 'client@example.test',
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($manager)->post(route('payment-requests.store'), $this->payload())
            ->assertRedirect();

        $paymentRequest = PaymentRequest::firstOrFail();
        $this->assertSame($client->id, $paymentRequest->client_id);
        $this->assertSame(3, $paymentRequest->quantity);
        $this->assertSame(12550, $paymentRequest->unit_amount_minor);
        $this->assertSame(37650, $paymentRequest->total_amount_minor);
        $this->assertSame('pending', $paymentRequest->status);
        $this->assertEqualsWithDelta(48, now()->diffInHours($paymentRequest->expires_at), 1);
    }

    public function test_signed_public_link_logs_each_open_and_collects_billing_data(): void
    {
        $paymentRequest = $this->paymentRequest();
        $url = SendPaymentRequestAction::publicUrl($paymentRequest);

        $this->get($url)->assertOk()->assertSee($paymentRequest->localizedTitle());
        $this->get($url)->assertOk();

        $this->assertDatabaseCount('payment_request_events', 3); // created + two opens
        $this->assertSame('opened', $paymentRequest->fresh()->status);

        $this->post(route('payments.public.billing', $paymentRequest), [
            'tax_id' => '310000000000003',
            'billing_address' => 'King Fahd Road, Riyadh, Saudi Arabia',
        ])->assertRedirect();

        $this->assertDatabaseHas('payment_requests', [
            'id' => $paymentRequest->id,
            'tax_id' => '310000000000003',
        ]);
    }

    public function test_save_and_send_emails_entered_address(): void
    {
        Mail::fake();
        $manager = $this->user(UserRole::MANAGER, 'manager');
        $payload = [...$this->payload(), 'email' => 'guest@example.test', 'send' => '1'];

        $this->actingAs($manager)->post(route('payment-requests.store'), $payload)
            ->assertRedirect();

        $this->assertSame('sent', PaymentRequest::firstOrFail()->status);
        Mail::assertSent(PaymentRequestMail::class, function (PaymentRequestMail $mail) {
            return $mail->hasTo('guest@example.test')
                && str_contains((string) $mail->actionUrl, '/pay/');
        });
    }

    public function test_public_page_switches_localized_title_with_locale(): void
    {
        $paymentRequest = $this->paymentRequest([
            'title_en' => 'English deposit title',
            'title_ar' => 'عنوان الدفعة العربي',
            'description_en' => 'English description body',
            'description_ar' => 'وصف عربي للدفعة',
        ]);
        $url = SendPaymentRequestAction::publicUrl($paymentRequest);

        $this->withSession(['locale' => 'en'])->get($url)
            ->assertOk()
            ->assertSee('English deposit title')
            ->assertSee('English description body')
            ->assertDontSee('عنوان الدفعة العربي')
            ->assertSee('images/vd-logo-light.png', false);

        $this->withSession(['locale' => 'ar'])->get($url)
            ->assertOk()
            ->assertSee('عنوان الدفعة العربي')
            ->assertSee('وصف عربي للدفعة')
            ->assertDontSee('English deposit title');
    }

    public function test_existing_client_sees_payment_request_in_portal_notifications(): void
    {
        $client = User::factory()->create([
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
        ]);
        $paymentRequest = $this->paymentRequest();
        $paymentRequest->update(['client_id' => $client->id, 'sent_at' => now()]);

        $this->actingAs($client)
            ->get(route('client.notifications.index'))
            ->assertOk()
            ->assertSee($paymentRequest->title);
    }

    public function test_expired_signed_link_is_rejected(): void
    {
        $paymentRequest = $this->paymentRequest();
        $url = SendPaymentRequestAction::publicUrl($paymentRequest);

        $this->travel(49)->hours();

        $this->get($url)->assertForbidden();
    }

    public function test_callback_verifies_amount_currency_and_metadata_before_paid(): void
    {
        $paymentRequest = $this->paymentRequest();
        $paymentId = '79cced57-9deb-4c4b-8f48-59c124f79688';

        Http::fake([
            'api.moyasar.com/v1/payments/*' => Http::response([
                'id' => $paymentId,
                'status' => 'paid',
                'amount' => $paymentRequest->total_amount_minor,
                'currency' => 'SAR',
                'metadata' => ['payment_request_uuid' => $paymentRequest->uuid],
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'source' => ['type' => 'creditcard', 'number' => 'XXXX-1111'],
            ]),
        ]);

        $this->get(route('payments.callback', $paymentRequest).'?id='.$paymentId)
            ->assertOk()
            ->assertSee(__('portal.payments.paid_message'));

        $this->assertSame('paid', $paymentRequest->fresh()->status);
        $this->assertNotNull($paymentRequest->fresh()->paid_at);
        $this->assertDatabaseHas('payment_attempts', ['moyasar_payment_id' => $paymentId]);
    }

    public function test_manager_can_attach_quote_number_and_file(): void
    {
        Storage::fake('private');
        $manager = $this->user(UserRole::MANAGER, 'manager');
        $file = UploadedFile::fake()->create('quotation.pdf', 120, 'application/pdf');

        $this->actingAs($manager)->get(route('payment-requests.create'))
            ->assertOk()
            ->assertSee(__('portal.payments.quote_optional'))
            ->assertSee(__('portal.payments.quote_file_button'))
            ->assertDontSee('Q0001');

        $this->actingAs($manager)->post(route('payment-requests.store'), [
            ...$this->payload(),
            'quote_number' => 'Q0001',
            'quote_file' => $file,
        ])->assertRedirect();

        $paymentRequest = PaymentRequest::firstOrFail();
        $this->assertSame('Q0001', $paymentRequest->quote_number);
        $this->assertNotNull($paymentRequest->quote_file);
        Storage::disk('private')->assertExists($paymentRequest->quote_file);

        $this->get(SendPaymentRequestAction::publicUrl($paymentRequest))
            ->assertOk()
            ->assertSee('Q0001')
            ->assertSee(__('portal.payments.download_quote'))
            ->assertSee(__('portal.payments.brand'))
            ->assertDontSee('Laravel');
    }

    public function test_signed_quote_download_requires_file_and_signature(): void
    {
        Storage::fake('private');
        $paymentRequest = $this->paymentRequest();

        $this->get(route('payments.public.quote', $paymentRequest))->assertForbidden();

        $this->get(URL::temporarySignedRoute(
            'payments.public.quote',
            $paymentRequest->expires_at,
            ['paymentRequest' => $paymentRequest],
        ))->assertNotFound();

        $path = UploadedFile::fake()->create('quotation.pdf', 80, 'application/pdf')->store('payment-quotes', 'private');
        $paymentRequest->update([
            'quote_number' => 'Q0001',
            'quote_file' => $path,
        ]);

        $this->get(SendPaymentRequestAction::quoteDownloadUrl($paymentRequest->fresh()))
            ->assertOk()
            ->assertDownload('Q0001.pdf');
    }

    public function test_manager_index_is_searchable_and_sortable(): void
    {
        $manager = $this->user(UserRole::MANAGER, 'manager');
        $this->paymentRequest([
            'name' => 'Alpha Client',
            'email' => 'alpha@example.test',
            'status' => 'paid',
            'created_at' => now()->subDay(),
        ]);
        $this->paymentRequest([
            'name' => 'Beta Client',
            'email' => 'beta@example.test',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->actingAs($manager)->get(route('payment-requests.index'))
            ->assertOk()
            ->assertSee(__('portal.payments.new'))
            ->assertSee('Alpha Client')
            ->assertSee('Beta Client')
            ->assertDontSee(__('portal.payments.quote_optional'));

        $this->actingAs($manager)->get(route('payment-requests.index', ['q' => 'alpha@example.test']))
            ->assertOk()
            ->assertSee('Alpha Client')
            ->assertDontSee('Beta Client');

        $this->actingAs($manager)->get(route('payment-requests.index', ['q' => 'Beta']))
            ->assertOk()
            ->assertSee('Beta Client')
            ->assertDontSee('Alpha Client');

        $this->actingAs($manager)->get(route('payment-requests.index', ['status' => 'paid']))
            ->assertOk()
            ->assertSee('Alpha Client')
            ->assertDontSee('Beta Client');

        $byDate = $this->actingAs($manager)
            ->get(route('payment-requests.index', ['sort' => 'date', 'direction' => 'desc']))
            ->assertOk()
            ->getContent();
        $this->assertTrue(strpos($byDate, 'Beta Client') < strpos($byDate, 'Alpha Client'));

        $byStatus = $this->actingAs($manager)
            ->get(route('payment-requests.index', ['sort' => 'status', 'direction' => 'asc']))
            ->assertOk()
            ->getContent();
        $this->assertTrue(strpos($byStatus, 'Alpha Client') < strpos($byStatus, 'Beta Client'));
    }

    public function test_manager_show_page_includes_copy_link_and_timeline(): void
    {
        $manager = $this->user(UserRole::MANAGER, 'manager');
        $paymentRequest = $this->paymentRequest();

        $this->actingAs($manager)->get(route('payment-requests.show', $paymentRequest))
            ->assertOk()
            ->assertSee($paymentRequest->title)
            ->assertSee(__('portal.payments.copy_link'))
            ->assertSee(__('portal.payments.timeline'))
            ->assertSee('Created');
    }

    public function test_webhook_authenticates_logs_and_dispatches_processing(): void
    {
        Queue::fake();
        $paymentRequest = $this->paymentRequest();
        $eventId = '65a2b41b-b644-4792-a45d-f1c6f67846fe';

        $payload = [
            'id' => $eventId,
            'type' => 'payment_paid',
            'created_at' => now()->toIso8601String(),
            'secret_token' => 'webhook-test-secret',
            'data' => [
                'id' => '79cced57-9deb-4c4b-8f48-59c124f79688',
                'metadata' => ['payment_request_uuid' => $paymentRequest->uuid],
            ],
        ];

        $this->postJson(route('webhooks.moyasar'), [...$payload, 'secret_token' => 'wrong'])
            ->assertUnauthorized();

        $this->postJson(route('webhooks.moyasar'), $payload)->assertNoContent();
        $this->postJson(route('webhooks.moyasar'), $payload)->assertNoContent();

        $this->assertDatabaseCount('payment_request_events', 2); // created + one deduplicated webhook
        $this->assertDatabaseMissing('payment_request_events', ['payload->secret_token' => 'webhook-test-secret']);
        Queue::assertPushed(ProcessMoyasarWebhook::class);
    }

    private function user(UserRole $role, string $spatieRole): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $user->assignRole($spatieRole);

        return $user;
    }

    private function paymentRequest(array $overrides = []): PaymentRequest
    {
        $manager = $this->user(UserRole::MANAGER, 'manager');

        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $paymentRequest = PaymentRequest::create([
            'created_by' => $manager->id,
            'name' => 'Client Name',
            'email' => 'client@example.test',
            'phone' => '+966500000000',
            'title' => 'Prototype deposit',
            'title_en' => 'Prototype deposit',
            'title_ar' => 'دفعة النموذج الأولي',
            'description' => 'Initial payment',
            'description_en' => 'Initial payment',
            'description_ar' => 'دفعة أولية',
            'quantity' => 1,
            'unit_amount_minor' => 15000,
            'total_amount_minor' => 15000,
            'currency' => 'SAR',
            'status' => 'sent',
            'expires_at' => now()->addHours(48),
            ...$overrides,
        ]);

        if ($createdAt) {
            $paymentRequest->forceFill(['created_at' => $createdAt])->saveQuietly();
        }

        app(\App\Actions\Payments\RecordPaymentRequestEventAction::class)
            ->execute($paymentRequest, 'created');

        return $paymentRequest->fresh();
    }

    private function payload(): array
    {
        return [
            'name' => 'Client Name',
            'email' => 'CLIENT@example.test',
            'phone' => '+966500000000',
            'title_en' => 'Prototype deposit',
            'title_ar' => 'دفعة النموذج الأولي',
            'description_en' => 'Initial payment',
            'description_ar' => 'دفعة أولية',
            'quantity' => 3,
            'amount' => '125.50',
            'send' => '0',
        ];
    }
}
