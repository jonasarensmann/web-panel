<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Domain;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain>
 */
class DomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->domainName(),
            'user_id' => 1,
            'locked' => false,
            'dns' => function (array $attributes) {
                return Domain::create_dns_template($attributes['name'], shell_exec('curl ipinfo.io/ip'));
            },
            'caddyconfig' => function (array $attributes) {
                return Domain::create_caddyconfig_template($attributes['name'], $attributes['user_id']);
            },
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Domain $domain) {
            Domain::create_dns_file($domain);
            Domain::create_caddyfile($domain);
        });
    }
}
