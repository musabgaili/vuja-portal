<?php

namespace Database\Factories;

use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FcmToken>
 */
class FcmTokenFactory extends Factory
{
    protected $model = FcmToken::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token' => Str::random(140),
            'device' => 'test-device',
            'platform' => 'android',
        ];
    }
}
