<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Event>
     */
    protected  = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         = fake()->sentence(4);
         = fake()->dateTimeBetween('+1 day', '+1 month');

        return [
            'created_by' => User::factory(),
            'title' => ,
            'slug' => Str::slug().'-'.Str::lower(Str::random(6)),
            'description' => fake()->paragraphs(2, true),
            'location' => fake()->city(),
            'starts_at' => ,
            'ends_at' => (clone )->modify('+2 hours'),
            'status' => 'draft',
            'is_public' => false,
            'capacity' => fake()->numberBetween(20, 300),
        ];
    }

    /**
     * Indicate that the event is published.
     */
    public function published(): static
    {
        return ->state(fn (array ) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the event is public.
     */
    public function public(): static
    {
        return ->state(fn (array ) => [
            'is_public' => true,
        ]);
    }
}
