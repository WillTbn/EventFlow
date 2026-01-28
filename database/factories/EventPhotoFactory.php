<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventPhoto>
 */
class EventPhotoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\EventPhoto>
     */
    protected $model = EventPhoto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'uploaded_by' => User::factory(),
            'original_path' => 'events/sample/original.jpg',
            'medium_path' => 'events/sample/medium.jpg',
            'thumb_path' => 'events/sample/thumb.jpg',
        ];
    }
}
