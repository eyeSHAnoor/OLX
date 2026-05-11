<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // -------------------------------
        // 1. ABOUT PAGE
        // -------------------------------
        $aboutContent = [
            'description' => "Welcome to Amo Mercatus, a modern and user-friendly online marketplace designed to connect buyers and sellers in a fast, secure, and efficient way.\n\nAt Amo Mercatus, our goal is to simplify online trading by providing a platform where users can easily buy, sell, and promote their products or services. Whether you are an individual seller, a small business, or a growing brand, our platform is built to support your needs and help you reach a wider audience.\n\n**What We Focus On**\n• Smooth User Experience – Clean interface, fast performance, and reliable features for a hassle-free experience.\n• Post & Explore – Users can post ads, explore listings, and communicate directly, making the entire process convenient.\n\n**Membership Options**\nAmo Mercatus also offers membership options that provide additional benefits such as better visibility, featured listings, and enhanced promotion tools to help sellers grow faster.\n\n**Our Promise**\nOur mission is to create a trusted digital marketplace where quality, transparency, and user satisfaction come first. We are continuously working to improve our platform by introducing new features and ensuring a secure environment for all users.",
            'mission' => "To provide a reliable, secure, and efficient platform that connects buyers and sellers while delivering real value through innovation and technology.",
            'vision' => "To become a leading online marketplace that empowers individuals and businesses to trade and grow digitally.",
            'values' => [
                "Smooth User Experience",
                "Post & Explore",
                "Better Visibility",
                "Featured Listings",
                "Enhanced Promotion Tools",
                "Trust & Continuous Improvement"
            ]
        ];

        DB::table('page_contents')->insert([
            'page_key'   => 'about',
            'title'      => 'About Us',
            'subtitle'   => 'Learn more about Amo Mercatus and what makes us different',
            'content'    => json_encode($aboutContent, JSON_UNESCAPED_UNICODE),
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // -------------------------------
        // 2. CONTACT PAGE
        // -------------------------------
        $contactContent = [
            'email'     => 'amomercatus@gmail.com, manageramomercatus@gmail.com', // comma-separated for mailto
            'phone'     => null,
            'address'   => null,
            'twitter'   => null,
            'instagram' => null,
            'facebook'  => null,
        ];

        DB::table('page_contents')->insert([
            'page_key'   => 'contact',
            'title'      => 'Contact Us',
            'subtitle'   => 'We’d love to hear from you. Reach out through any of the channels below',
            'content'    => json_encode($contactContent, JSON_UNESCAPED_UNICODE),
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // -------------------------------
        // 3. TEAM PAGE
        // -------------------------------
        $teamContent = [
            'members' => [
                [
                    'name'         => 'Moeez Ismail',
                    'designation'  => 'Marketing',
                    'email'        => 'amomercatusmoiz@gmail.com',
                    'photo_url'    => null,
                    'photo_preview'=> null,
                ],
                [
                    'name'         => 'Javeria',
                    'designation'  => 'Manager',
                    'email'        => 'manageramomercatus@gmail.com',
                    'photo_url'    => null,
                    'photo_preview'=> null,
                ],
                [
                    'name'         => 'Rida Shoukat',
                    'designation'  => 'Marketing',
                    'email'        => 'amomercatusrida@gmail.com',
                    'photo_url'    => null,
                    'photo_preview'=> null,
                ],
                [
                    'name'         => 'Muqadas Saleem',
                    'designation'  => 'Marketing',
                    'email'        => 'muqadasamomercatus@gmail.com',
                    'photo_url'    => null,
                    'photo_preview'=> null,
                ],
                [
                    'name'         => 'Huzaifa Saleem',
                    'designation'  => 'Marketing',
                    'email'        => 'Huzaifaamo61@gmail.com',
                    'photo_url'    => null,
                    'photo_preview'=> null,
                ],
            ],
        ];

        DB::table('page_contents')->insert([
            'page_key'   => 'team',
            'title'      => 'Our Team',
            'subtitle'   => 'Meet the people behind Amo Mercatus',
            'content'    => json_encode($teamContent, JSON_UNESCAPED_UNICODE),
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}