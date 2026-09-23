<?php

namespace Database\Factories;

use App\Models\About;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<About>
 */
class AboutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hero' => [
                'eyebrow' => 'About Senja · Jakarta, Indonesia',
                'title' => 'Technology, made to feel human.',
                'description' => 'We design and integrate connected spaces where technology feels intuitive, purposeful, and quietly powerful.',
                'image' => ['src' => '/images/about.png', 'alt' => 'A connected Senja learning and collaboration environment'],
            ],
            'story' => [
                'eyebrow' => 'Why we exist',
                'title' => "A better space doesn't ask people to understand the technology. It understands them.",
                'paragraphs' => [
                    'Senja brings technology, spatial thinking, and human needs into one integrated experience. We work across the entire journey—from the first conversation and system design to installation and long-term support.',
                    'The result is not a collection of devices. It is a space where ideas move more freely, teams collaborate naturally, and every interaction feels considered.',
                ],
                'stats' => [
                    ['value' => '50+', 'label' => 'Spaces transformed'],
                    ['value' => '12', 'label' => 'Technology partners'],
                    ['value' => '04', 'label' => 'Industries served'],
                ],
                'image' => ['src' => '/images/bg-hero.png', 'alt' => 'Modern connected meeting space'],
            ],
            'quote' => [
                'eyebrow' => 'Our point of view',
                'text' => "The best technology doesn't take over the room. It gives the room more possibility.",
            ],
            'principles' => [
                'eyebrow' => 'What guides us',
                'title' => 'Our principles',
                'description' => 'Simple ideas that shape every space we create.',
                'items' => [
                    ['title' => 'Human first', 'description' => 'Technology should disappear into the experience. Every decision begins with the people who will use the space.'],
                    ['title' => 'Built as one', 'description' => 'Design, hardware, software, and support are considered together so every touchpoint feels clear and connected.'],
                    ['title' => 'Ready for change', 'description' => 'We create adaptable systems that can grow with new teams, new tools, and the next way of working.'],
                ],
            ],
            'capabilities' => [
                'eyebrow' => 'What we connect',
                'title' => 'One partner. Every layer.',
                'description' => 'From a single meeting room to an entire connected workplace, we make every layer work together as one clear experience.',
                'visualLabel' => 'Spaces / systems / experiences',
                'services' => ['Workplace collaboration', 'Digital signage', 'Learning environments', 'Network infrastructure', 'Experience design', 'Lifecycle support'],
                'image' => ['src' => '/images/solution.png', 'alt' => 'Integrated meeting room technology'],
            ],
            'process' => [
                'eyebrow' => 'How we work',
                'title' => 'From intent to impact.',
                'description' => 'A collaborative process with clarity at every step.',
                'steps' => [
                    ['title' => 'Listen', 'description' => 'Understand the people, space, and real operational challenge.'],
                    ['title' => 'Imagine', 'description' => 'Translate needs into one clear technology and experience concept.'],
                    ['title' => 'Integrate', 'description' => 'Deliver every system as one reliable, carefully finished environment.'],
                    ['title' => 'Evolve', 'description' => 'Support, measure, and improve the space as your needs change.'],
                ],
            ],
        ];
    }
}
