<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        $users = [
            [
                'id' => 'usr_1',
                'name' => 'Demo Customer',
                'email' => 'customer@luxecart.com',
                'password' => $defaultPassword,
                'phone' => '+1 (555) 234-5678',
                'role' => 'customer',
            ],
            [
                'id' => 'usr_2',
                'name' => 'Emma Wills',
                'email' => 'emma.wills@example.com',
                'password' => $defaultPassword,
                'phone' => '+1 (555) 234-5678',
                'role' => 'customer',
            ],
            [
                'id' => 'usr_3',
                'name' => 'Sophia Lauren',
                'email' => 'sophia.lauren@example.com',
                'password' => $defaultPassword,
                'phone' => '+1 (555) 876-5432',
                'role' => 'customer',
            ],
            [
                'id' => 'usr_4',
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => $defaultPassword,
                'phone' => '+1 (555) 987-6543',
                'role' => 'customer',
            ],
            [
                'id' => 'usr_admin',
                'name' => 'LuxeCart Admin',
                'email' => 'admin@luxecart.com',
                'password' => $defaultPassword,
                'phone' => '+1 (555) 000-1111',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Default address for Emma Wills (both by user_id 'usr_2' and 'emma.wills@example.com')
        Address::updateOrCreate(
            ['id' => 'addr_default_1'],
            [
                'user_id' => 'emma.wills@example.com',
                'recipient_name' => 'Emma Wills',
                'street' => '14 Market Street',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10001',
                'country' => 'United States',
                'phone' => '+1 (555) 234-5678',
                'is_default' => true,
            ]
        );

        // Default wishlist for Emma Wills
        $wishlist = Wishlist::updateOrCreate(
            ['id' => 'wl_default'],
            [
                'user_id' => 'emma.wills@example.com',
                'name' => 'Favorites',
                'description' => 'My curated designer pieces',
                'icon' => '✨',
                'is_default' => true,
            ]
        );
        $wishlist->products()->syncWithoutDetaching([1, 3, 5]);

        // Sample initial reviews for Product #1
        Review::updateOrCreate(
            ['id' => 1],
            [
                'product_id' => 1,
                'user_name' => 'Marcus Vance',
                'rating' => 5.0,
                'comment' => 'The heavyweight cotton feel is unmatched. Incredible drape and craftsmanship.',
            ]
        );
        Review::updateOrCreate(
            ['id' => 2],
            [
                'product_id' => 1,
                'user_name' => 'Sarah Jenkins',
                'rating' => 4.8,
                'comment' => 'High quality fabric and true to size oversized look. Highly recommend.',
            ]
        );
    }
}
