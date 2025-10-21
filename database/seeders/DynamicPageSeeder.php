<?php

namespace Database\Seeders;

use App\Models\DynamicPage;
use Illuminate\Database\Seeder;

class DynamicPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {

        $content = "1. What is Lorem Ipsum?

Lorem ipsum dolor sit amet consectetur, Maecenas dui odio vitae convallis. Euismod ac ut sed tempor duis et. Auctor ornare egestas in iaculis rhoncus. Venenatis urna nibh adipiscing elementum blandit nulla pharetra blandit. Vulputate eu augue eu diam et sit. Varius a nunc enim euismod quisque vitae. Eget consequat arcu nam blandit maecenas adipiscing tristique. Odio nisi at odio eu nunc dictumst eros phasellus. Fringilla condimentum duis id adipiscing. Cursus vitae dignissim est turpis

2. What do we use it?

Lorem ipsum dolor sit amet consectetur, Maecenas dui odio vitae convallis. Euismod ac ut sed tempor duis et. Auctor ornare egestas in iaculis rhoncus. Venenatis urna nibh adipiscing elementum blandit nulla pharetra blandit. Vulputate eu augue eu diam et sit. Varius a nunc enim euismod quisque vitae. Eget consequat arcu nam blandit maecenas adipiscing tristique. Odio nisi at odio eu nunc dictumst eros phasellus. Fringilla condimentum duis id adipiscing. Cursus vitae

3. How it works?

Lorem ipsum dolor sit amet consectetur, Maecenas dui odio vitae convallis. Euismod ac ut sed tempor duis et. Auctor ornare egestas in iaculis rhoncus. Venenatis urna nibh adipiscing elementum blandit nulla pharetra blandit. Vulputate eu augue eu diam et sit. Varius a nunc enim euismod quisque vitae. Eget consequat arcu nam blandit maecenas adipiscing tristique. Odio nisi at odio eu nunc dictumst eros phasellus. Fringilla condimentum duis id adipiscing. Cursus vitae";

        DynamicPage::insert([

            [
                "page_title" => "Privacy Policy",
                "page_slug" => "privacy-policy",
                "page_content" => $content,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "page_title" => "Terms & Conditions",
                "page_slug" => "terms-and-conditions",
                "page_content" => $content,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
