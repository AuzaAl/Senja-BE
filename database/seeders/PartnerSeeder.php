<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the 5 real technology partners (mirrors FE/CMS data/partners.ts).
     */
    public function run(): void
    {
        if (Partner::exists()) {
            return;
        }

        foreach ($this->partners() as $index => $data) {
            $gallery = $data['gallery'] ?? [];
            $products = $data['products'] ?? [];
            unset($data['gallery'], $data['products']);

            $data['sort_order'] = $index + 1;
            $data['is_active'] = true;

            /** @var Partner $partner */
            $partner = Partner::create($data);

            foreach ($gallery as $gi => $image) {
                $partner->gallery()->create([
                    'image_path' => $image['src'],
                    'alt' => $image['alt'] ?? null,
                    'position' => $image['position'] ?? null,
                    'sort_order' => $gi,
                ]);
            }

            foreach ($products as $pi => $product) {
                $partner->products()->create([
                    'name' => $product['name'],
                    'category' => $product['category'] ?? null,
                    'description' => $product['description'] ?? null,
                    'image_path' => $product['image'] ?? null,
                    'sort_order' => $pi,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function partners(): array
    {
        return [
            [
                'name' => 'Panasonic',
                'slug' => 'panasonic',
                'number' => '01',
                'category' => 'Display',
                'logo' => '/images/partners-4.png',
                'hero_image' => '/images/2.png',
                'description' => 'Professional visual systems engineered for dependable, always-on communication environments.',
                'capabilities' => ['Professional displays', 'AV systems', 'Digital signage'],
                'relationship' => 'Reliable visual technology for spaces that need to perform all day, every day.',
                'relationship_detail' => 'Senja works with Panasonic professional visual solutions to deliver clear, dependable communication across meeting rooms, digital signage environments, and shared facilities. Our role connects product selection, spatial coordination, installation, and ongoing support into one accountable experience.',
                'website' => 'https://panasonic.com',
                'gallery' => [
                    ['src' => '/images/5.png', 'alt' => 'Panasonic display technology integrated into a premium meeting space'],
                    ['src' => '/images/2.png', 'alt' => 'Professional digital display in a flexible presentation environment'],
                ],
                'products' => [
                    ['name' => 'SQ2H Series', 'category' => 'Professional Display', 'description' => 'High-brightness 4K displays for premium signage and corporate communication.', 'image' => '/images/2.png'],
                    ['name' => 'EQ2 Series', 'category' => 'Commercial Display', 'description' => 'Versatile professional displays for meeting rooms, information, and everyday signage.', 'image' => '/images/5.png'],
                    ['name' => 'PressIT360', 'category' => 'Collaboration', 'description' => 'A 360-degree camera speakerphone designed for equitable hybrid meetings.', 'image' => '/images/4.png'],
                ],
            ],
            [
                'name' => 'TP-Link',
                'slug' => 'tp-link',
                'number' => '02',
                'category' => 'Network',
                'logo' => '/images/partners-3.png',
                'hero_image' => '/images/1.png',
                'description' => 'Reliable network infrastructure that keeps every connected room responsive and secure.',
                'capabilities' => ['Enterprise network', 'Wi-Fi', 'Connectivity'],
                'relationship' => 'The connected foundation behind every responsive Senja experience.',
                'relationship_detail' => "Together with TP-Link's Omada ecosystem, Senja designs network foundations that support collaboration, signage, room control, and guest connectivity. We plan coverage, switching, segmentation, and centralized management around the real operational needs of each space.",
                'website' => 'https://tp-link.com',
                'gallery' => [
                    ['src' => '/images/1.png', 'alt' => 'Connected workplace powered by enterprise network infrastructure'],
                    ['src' => '/images/2.png', 'alt' => 'A learning environment supported by reliable wireless connectivity'],
                ],
                'products' => [
                    ['name' => 'EAP653', 'category' => 'Wi-Fi 6 Access Point', 'description' => 'Slim AX3000 ceiling access point for fast, high-capacity business wireless coverage.', 'image' => '/images/1.png'],
                    ['name' => 'SG2210P', 'category' => 'Managed PoE+ Switch', 'description' => 'A managed ten-port switch that powers and connects room technology through PoE+.', 'image' => '/images/5.png'],
                    ['name' => 'OC200', 'category' => 'Hardware Controller', 'description' => 'Centralized on-premises management for access points, switches, and gateways.', 'image' => '/images/3.png'],
                ],
            ],
            [
                'name' => 'Logitech',
                'slug' => 'logitech',
                'number' => '03',
                'category' => 'Collaboration',
                'logo' => '/images/partners-1.png',
                'hero_image' => '/images/4.png',
                'description' => 'Human-centered video collaboration solutions made for intuitive hybrid communication.',
                'capabilities' => ['Video conferencing', 'Room systems', 'Peripherals'],
                'relationship' => 'Making video collaboration feel natural in rooms of every size.',
                'relationship_detail' => 'Senja and Logitech bring human-centered collaboration into the workplace through room systems that are easy to start, consistent to operate, and simple for IT teams to manage. We match the right camera, controller, and room configuration to each meeting experience.',
                'website' => 'https://logitech.com',
                'gallery' => [
                    ['src' => '/images/5.png', 'alt' => 'Executive collaboration room using Logitech meeting technology'],
                    ['src' => '/images/3.png', 'alt' => 'Modern meeting room designed for effortless video collaboration'],
                ],
                'products' => [
                    ['name' => 'Rally Bar', 'category' => 'All-in-one Video Bar', 'description' => 'A premium appliance-based video bar for medium-sized meeting rooms.', 'image' => '/images/5.png'],
                    ['name' => 'MeetUp 2', 'category' => 'Conference Camera', 'description' => 'AI-enabled USB video bar designed for PC and BYOD-based small meeting rooms.', 'image' => '/images/3.png'],
                    ['name' => 'Tap IP', 'category' => 'Touch Controller', 'description' => 'A network-connected meeting controller built around one-touch join simplicity.', 'image' => '/images/4.png'],
                ],
            ],
            [
                'name' => 'BenQ',
                'slug' => 'benq',
                'number' => '04',
                'category' => 'Display',
                'logo' => '/images/partners-2.png',
                'hero_image' => '/images/2.png',
                'description' => 'Interactive displays and visual solutions designed for productive workplaces and learning spaces.',
                'capabilities' => ['Interactive displays', 'Meeting rooms', 'Education'],
                'relationship' => 'Interactive visual experiences for ideas people can see, shape, and share.',
                'relationship_detail' => 'Senja integrates BenQ displays into workplaces and learning environments where participation matters. From interactive boards to wireless presentation and managed signage, we design the supporting system so every user can present and collaborate with confidence.',
                'website' => 'https://benq.com',
                'gallery' => [
                    ['src' => '/images/2.png', 'alt' => 'Interactive display in a modern learning space'],
                    ['src' => '/images/4.png', 'alt' => 'BenQ visual communication technology integrated into an interior'],
                ],
                'products' => [
                    ['name' => 'Board Pro RP04', 'category' => 'Interactive Display', 'description' => 'Google-certified 4K interactive displays for collaborative workplaces and classrooms.', 'image' => '/images/2.png'],
                    ['name' => 'InstaShow VS20', 'category' => 'Wireless Presentation', 'description' => 'Plug-and-play 4K wireless presentation and hybrid meeting integration.', 'image' => '/images/4.png'],
                    ['name' => 'ST04 Series', 'category' => 'Smart Signage', 'description' => 'Secure 4K smart signage for corporate information and customer-facing content.', 'image' => '/images/5.png'],
                ],
            ],
            [
                'name' => 'Epson',
                'slug' => 'epson',
                'number' => '05',
                'category' => 'Display',
                'logo' => '/images/partners-5.png',
                'hero_image' => '/images/2.png',
                'description' => 'Scalable projection technology for immersive presentations, learning, and shared experiences.',
                'capabilities' => ['Laser projection', 'Immersive visuals', 'Large venues'],
                'relationship' => 'Scalable projection for ideas that deserve a larger canvas.',
                'relationship_detail' => "Senja works with Epson projection technology to create bright, flexible visual experiences for classrooms, meeting rooms, signage, and large venues. We coordinate projection geometry, mounting, signal flow, control, and commissioning for a complete result.",
                'website' => 'https://epson.com',
                'gallery' => [
                    ['src' => '/images/2.png', 'alt' => 'Large-format Epson projection in a learning environment'],
                    ['src' => '/images/5.png', 'alt' => 'Immersive projected presentation in a professional interior'],
                ],
                'products' => [
                    ['name' => 'PowerLite L210SF', 'category' => 'Short Throw Laser', 'description' => 'A Full HD short-throw laser display for classrooms and collaborative spaces.', 'image' => '/images/2.png'],
                    ['name' => 'PowerLite 810E', 'category' => 'Extreme Short Throw', 'description' => 'A compact lamp-free display with 4K enhancement for large images near the wall.', 'image' => '/images/5.png'],
                    ['name' => 'EB-PU1008B', 'category' => 'Large Venue Projector', 'description' => 'An 8,500-lumen WUXGA laser projector with 4K enhancement for demanding venues.', 'image' => '/images/1.png'],
                ],
            ],
        ];
    }
}
