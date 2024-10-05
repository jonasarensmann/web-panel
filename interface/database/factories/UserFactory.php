<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permissions = [
            'view_logs' => true,
        ];

        return [
            'username' => fake()->unique()->text(16),
            'password' => Hash::make('password'), // password
            'is_admin' => false,
            'remember_token' => Str::random(10),
            'permissions' => json_encode($permissions),
        ];
    }
}
