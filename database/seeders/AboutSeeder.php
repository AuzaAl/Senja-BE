<?php

namespace Database\Seeders;

use App\Models\About;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AboutSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the homepage about section (singleton row).
     */
    public function run(): void
    {
        About::updateOrCreate(['id' => 1], [
            'hero' => [
                'title' => 'Sebuah studio yang lahir saat senja',
                'subtitle' => 'Kami percaya ide terbaik muncul di antara chaos dan fokus — di sanalah kami bekerja.',
                'image' => 'https://picsum.photos/seed/about-senja/1200/800',
            ],
            'story' => [
                'title' => 'Cerita kami',
                'paragraphs' => [
                    'Senja dimulai dari sekelompok desainer dan pengembang yang jenuh dengan hasil kerja biasa-biasa saja.',
                    'Sejak itu kami membantu brand membangun identitas, produk digital, dan cerita yang berkesan.',
                ],
            ],
            'quote' => [
                'text' => 'Kami tidak sekadar membuat yang indah. Kami membuat yang bekerja.',
                'author' => 'Tim Senja',
            ],
            'principles' => [
                ['title' => 'Kejelasan', 'description' => 'Setiap keputusan punya alasan yang jelas.'],
                ['title' => 'Keberanian', 'description' => 'Kami berani mencoba hal baru dan belajar cepat.'],
                ['title' => 'Ketelitian', 'description' => 'Detail kecil adalah bagian dari kualitas besar.'],
            ],
            'capabilities' => [
                ['title' => 'Brand Identity', 'description' => 'Identitas visual yang konsisten dan berkarakter.'],
                ['title' => 'Product Design', 'description' => 'Antarmuka dan pengalaman produk digital.'],
                ['title' => 'Development', 'description' => 'Kode bersih, cepat, dan mudah dikelola.'],
            ],
            'process' => [
                ['step' => 1, 'title' => 'Discover', 'description' => 'Memahami masalah, audiens, dan tujuan.'],
                ['step' => 2, 'title' => 'Define', 'description' => 'Merumuskan strategi dan pendekatan solusi.'],
                ['step' => 3, 'title' => 'Develop', 'description' => 'Membangun dan menguji bersama klien.'],
                ['step' => 4, 'title' => 'Deliver', 'description' => 'Meluncurkan, lalu terus menyempurnakan.'],
            ],
        ]);
    }
}
