<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 'all', 'name' => 'All', 'icon' => 'grid_view_rounded', 'description' => 'Browse our complete luxury curated catalog', 'accent_color' => '0xFFFF2D6F', 'badge' => 'TOP'],
            ['id' => 'apparel', 'name' => 'Apparel', 'icon' => 'checkroom_rounded', 'description' => 'Designer streetwear, hoodies, linen shirts & tailored outerwear', 'accent_color' => '0xFFFF2D6F', 'badge' => 'NEW'],
            ['id' => 'accessories', 'name' => 'Accessories', 'icon' => 'watch_rounded', 'description' => 'Sapphire timepieces, titanium eyewear & leather goods', 'accent_color' => '0xFF6C63FF', 'badge' => 'HOT'],
            ['id' => 'footwear', 'name' => 'Footwear', 'icon' => 'snowshoeing_rounded', 'description' => 'Cushioned foam runners, leather low-tops & loafers', 'accent_color' => '0xFF10B981', 'badge' => 'SALE'],
            ['id' => 'electronics', 'name' => 'Electronics', 'icon' => 'headphones_rounded', 'description' => 'Active noise cancelling audio, wearables & smart home', 'accent_color' => '0xFF3B82F6', 'badge' => 'PRO'],
            ['id' => 'bags', 'name' => 'Bags', 'icon' => 'work_outline_rounded', 'description' => 'Full-grain leather duffels, waterproof city backpacks & totes', 'accent_color' => '0xFFF59E0B', 'badge' => 'LUXE'],
            ['id' => 'jewelry', 'name' => 'Jewelry', 'icon' => 'diamond_outlined', 'description' => '18K gold vermeil, sterling silver rings & handcrafted pendants', 'accent_color' => '0xFFEC4899', 'badge' => 'FINE'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['id' => $cat['id']], $cat);
        }
    }
}
