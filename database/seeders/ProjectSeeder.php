<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the 4 real showcase projects (mirrors FE/CMS data/projects.ts).
     */
    public function run(): void
    {
        if (Project::exists()) {
            return;
        }

        foreach ($this->projects() as $index => $data) {
            $gallery = $data['gallery'] ?? [];
            $partnerSlugs = $data['partner_slugs'] ?? [];
            unset($data['gallery'], $data['partner_slugs']);

            $data['sort_order'] = $index + 1;

            /** @var Project $project */
            $project = Project::create($data);

            foreach ($gallery as $gi => $image) {
                $project->gallery()->create([
                    'image_path' => $image['src'],
                    'alt' => $image['alt'] ?? null,
                    'position' => $image['position'] ?? null,
                    'sort_order' => $gi,
                ]);
            }

            if ($partnerSlugs !== []) {
                $ids = Partner::whereIn('slug', $partnerSlugs)->pluck('id')->all();
                $project->partners()->sync($ids);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function projects(): array
    {
        return [
            [
                'title' => 'Digital Signage Installation',
                'slug' => 'digital-signage-installation',
                'number' => '01',
                'category' => 'F&B',
                'location' => 'Surabaya',
                'year' => '2025',
                'client' => 'Hospitality Group',
                'featured' => true,
                'summary' => 'A connected menu-board ecosystem designed to make content management effortless across every customer touchpoint.',
                'description' => 'A connected menu-board ecosystem designed to make content management effortless across every customer touchpoint.',
                'overview' => 'A growing hospitality brand needed a digital communication system that could keep pace with changing menus, promotions, and customer behavior. Senja created a warm, visually integrated signage experience that feels native to the interior rather than added on afterward.',
                'challenge' => 'Content updates were slow and inconsistent across displays, while the existing screens competed with the carefully designed atmosphere. The system needed to be simple for the operational team and invisible to the guest.',
                'solution' => 'We combined commercial-grade displays, centralized content management, and custom mounting details. Every screen was calibrated for consistent color and readability, giving the team one intuitive workflow for every customer-facing message.',
                'services' => ['Experience design', 'Digital signage', 'Content management', 'System integration'],
                'content' => 'A growing hospitality brand needed a digital communication system that could keep pace with changing menus, promotions, and customer behavior.',
                'stats' => [
                    ['value' => '05', 'label' => 'Integrated displays'],
                    ['value' => '01', 'label' => 'Control platform'],
                    ['value' => '40%', 'label' => 'Faster updates'],
                ],
                'cover_image' => '/images/2.png',
                'gallery' => [
                    ['src' => '/images/2.png', 'alt' => 'Integrated digital menu displays above the service counter'],
                    ['src' => '/images/3.png', 'alt' => 'Technology detail and display integration'],
                    ['src' => '/images/5.png', 'alt' => 'Warm interior lighting and integrated technology', 'position' => 'center 62%'],
                    ['src' => '/images/2.png', 'alt' => 'A connected presentation environment'],
                ],
                'partner_slugs' => ['panasonic', 'benq', 'tp-link'],
            ],
            [
                'title' => 'Intelligent Meeting Ecosystem',
                'slug' => 'intelligent-meeting-ecosystem',
                'number' => '02',
                'category' => 'Workplace',
                'location' => 'Jakarta',
                'year' => '2025',
                'client' => 'Corporate Headquarters',
                'featured' => false,
                'summary' => 'One-touch collaboration, room control, and video conferencing built around the way modern teams actually work.',
                'description' => 'One-touch collaboration, room control, and video conferencing built around the way modern teams actually work.',
                'overview' => 'This workplace transformation turns everyday meetings into effortless collaborative sessions. Technology is intentionally quiet: the room responds quickly, remote participants feel present, and teams can focus on ideas instead of controls.',
                'challenge' => 'Different meeting platforms and disconnected room devices created delays at the beginning of every session. The client wanted a consistent experience for both frequent users and first-time guests.',
                'solution' => 'Senja designed a single-touch control layer connecting video, audio, lighting, and room scheduling. Automated presets remove repetitive setup while enterprise monitoring keeps every room ready throughout the day.',
                'services' => ['AV consulting', 'Video collaboration', 'Room automation', 'User training'],
                'content' => 'This workplace transformation turns everyday meetings into effortless collaborative sessions.',
                'stats' => [
                    ['value' => '12', 'label' => 'Connected rooms'],
                    ['value' => '01', 'label' => 'Touch to start'],
                    ['value' => '99%', 'label' => 'Room readiness'],
                ],
                'cover_image' => '/images/3.png',
                'gallery' => [
                    ['src' => '/images/3.png', 'alt' => 'Modern connected meeting room'],
                    ['src' => '/images/5.png', 'alt' => 'Executive meeting environment'],
                    ['src' => '/images/2.png', 'alt' => 'Large-format collaboration display'],
                    ['src' => '/images/2.png', 'alt' => 'Integrated digital information display'],
                ],
                'partner_slugs' => ['logitech', 'benq', 'tp-link'],
            ],
            [
                'title' => 'Immersive Learning Space',
                'slug' => 'immersive-learning-space',
                'number' => '03',
                'category' => 'Education',
                'location' => 'Bandung',
                'year' => '2024',
                'client' => 'Learning Institute',
                'featured' => false,
                'summary' => 'An adaptive classroom where clear audio, immersive displays, and simple controls keep the focus on learning.',
                'description' => 'An adaptive classroom where clear audio, immersive displays, and simple controls keep the focus on learning.',
                'overview' => 'Designed as a flexible home for lectures, workshops, and hybrid classes, this learning environment gives every participant a clear view and a clear voice—whether they are in the first row or joining remotely.',
                'challenge' => 'A long room, mixed teaching formats, and variable daylight made consistent sightlines and intelligible audio difficult. Lecturers also needed to switch formats without technical support.',
                'solution' => 'We coordinated display placement, distributed audio, camera tracking, and intuitive lectern controls. Flexible presets allow the space to shift between lecture, discussion, and hybrid modes in seconds.',
                'services' => ['Learning space design', 'Acoustic planning', 'Hybrid learning', 'Control programming'],
                'content' => 'Designed as a flexible home for lectures, workshops, and hybrid classes.',
                'stats' => [
                    ['value' => '80', 'label' => 'Learner capacity'],
                    ['value' => '03', 'label' => 'Teaching modes'],
                    ['value' => '360°', 'label' => 'Audio coverage'],
                ],
                'cover_image' => '/images/2.png',
                'gallery' => [
                    ['src' => '/images/2.png', 'alt' => 'Immersive classroom with large presentation screen'],
                    ['src' => '/images/3.png', 'alt' => 'Collaborative learning table'],
                    ['src' => '/images/5.png', 'alt' => 'Integrated display wall in a dark interior'],
                    ['src' => '/images/2.png', 'alt' => 'Digital content presentation system'],
                ],
                'partner_slugs' => ['epson', 'logitech', 'tp-link'],
            ],
            [
                'title' => 'Executive Collaboration Suite',
                'slug' => 'executive-collaboration-suite',
                'number' => '04',
                'category' => 'Workplace',
                'location' => 'Jakarta',
                'year' => '2025',
                'client' => 'Regional Enterprise',
                'featured' => true,
                'summary' => 'A discreetly integrated boardroom that combines premium interiors with enterprise-grade collaboration technology.',
                'description' => 'A discreetly integrated boardroom that combines premium interiors with enterprise-grade collaboration technology.',
                'overview' => 'A flagship boardroom where high-stakes conversations can happen without technological friction. The experience pairs cinematic presence with discreet systems that preserve the architectural character of the room.',
                'challenge' => 'The room required broadcast-quality communication without visible cable runs, intrusive hardware, or complicated operation. Speech needed to remain clear across a long table and for every remote participant.',
                'solution' => 'Senja embedded beamforming microphones, directional audio, dual displays, and secure conferencing behind carefully coordinated interior details. A tailored interface presents only the controls needed for each meeting mode.',
                'services' => ['Executive AV design', 'Secure conferencing', 'Interior coordination', 'Automation'],
                'content' => 'A flagship boardroom where high-stakes conversations can happen without technological friction.',
                'stats' => [
                    ['value' => '24', 'label' => 'Executive seats'],
                    ['value' => '4K', 'label' => 'Visual clarity'],
                    ['value' => '02', 'label' => 'Meeting modes'],
                ],
                'cover_image' => '/images/5.png',
                'gallery' => [
                    ['src' => '/images/5.png', 'alt' => 'Executive boardroom with warm architectural lighting'],
                    ['src' => '/images/3.png', 'alt' => 'Meeting room display and collaboration setup'],
                    ['src' => '/images/2.png', 'alt' => 'Integrated screen installation'],
                    ['src' => '/images/2.png', 'alt' => 'Presentation screen viewed from audience seating'],
                ],
                'partner_slugs' => ['panasonic', 'logitech', 'benq'],
            ],
        ];
    }
}
