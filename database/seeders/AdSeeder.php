<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;
use App\Models\AdImage;
use App\Models\AdAttributeValue;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Models\CategoryAttribute;
use App\Models\AttributeOption;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdSeeder extends Seeder
{
    // Cities of Pakistan with their regions
    private $pakistanCities = [
        'Punjab' => ['Lahore', 'Faisalabad', 'Rawalpindi', 'Multan', 'Gujranwala', 'Sialkot', 'Bahawalpur', 'Sargodha', 'Gujrat', 'Sheikhupura', 'Jhelum', 'Sahiwal', 'Wah Cantonment', 'Kasur', 'Okara', 'Mandi Bahauddin', 'Pakpattan'],
        'Sindh' => ['Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Nawabshah', 'Mirpur Khas', 'Jacobabad', 'Shikarpur', 'Khairpur', 'Dadu', 'Badin', 'Thatta'],
        'Khyber Pakhtunkhwa' => ['Peshawar', 'Mardan', 'Mingora', 'Kohat', 'Abbottabad', 'Dera Ismail Khan', 'Mansehra', 'Swabi', 'Nowshera', 'Charsadda', 'Haripur'],
        'Balochistan' => ['Quetta', 'Turbat', 'Khuzdar', 'Hub', 'Chaman', 'Gwadar', 'Dera Murad Jamali', 'Dera Allah Yar', 'Usta Mohammad', 'Loralai'],
        'Islamabad & AJK' => ['Islamabad', 'Muzaffarabad', 'Mirpur', 'Kotli', 'Rawalakot', 'Bagh'],
        'Gilgit-Baltistan' => ['Gilgit', 'Skardu', 'Chilas', 'Ghizer', 'Hunza', 'Nagar']
    ];

    private $pakistaniNames = [
        'Ali', 'Ahmed', 'Muhammad', 'Hassan', 'Hussain', 'Usman', 'Omar', 'Bilal',
        'Zain', 'Abdullah', 'Fatima', 'Aisha', 'Zainab', 'Maryam', 'Sana', 'Hina',
        'Rabia', 'Sadia', 'Kashif', 'Kamran', 'Salman', 'Imran', 'Waqas', 'Farhan',
        'Nadia', 'Saima', 'Ayesha', 'Sara', 'Mehak', 'Anum'
    ];

    private $searchKeywordsByCategory = [
        'Mobile Phones' => ['smartphone', 'android', 'ios', 'mobile', 'phone', 'used phone', 'new phone', 'iphone', 'samsung', 'xiaomi', '5g', '4g', 'dual sim', 'camera'],
        'Cars' => ['vehicle', 'automobile', 'sedan', 'hatchback', 'suv', 'toyota', 'honda', 'suzuki', 'used car', 'new car', 'first owner', 'automatic', 'manual', 'petrol', 'diesel', 'cng', 'low mileage'],
        'Motorcycles' => ['bike', 'scooter', 'motorcycle', 'honda', 'yamaha', 'suzuki', '70cc', '125cc', '150cc', 'new bike', 'used bike', 'petrol', 'cd70', 'cg125'],
        'Houses' => ['home', 'property', 'real estate', 'residential', 'villa', 'banglow', 'for sale', 'for rent', 'luxury', 'modern', 'furnished', 'unfurnished', 'dha', 'bahria', 'gulberg', '3bed', '4bed'],
        'Clothes' => ['apparel', 'fashion', 'garments', 'dress', 'shirt', 'jeans', 'traditional', 'western', 'branded', 'designer', 'new', 'used', 'cotton', 'silk', 'woolen', 'summer', 'winter'],
        'Televisions & Accessories' => ['tv', 'television', 'led', 'oled', 'qled', 'smart tv', 'samsung', 'lg', 'sony', 'new tv', 'used tv', 'home theater', 'soundbar'],
        'Computers & Accessories' => ['laptop', 'desktop', 'computer', 'pc', 'macbook', 'dell', 'hp', 'lenovo', 'gaming laptop', 'office use', 'processor', 'ram', 'ssd', 'keyboard', 'mouse', 'monitor', 'printer']
    ];

    private $defaultKeywords = [
        'for sale', 'urgent sale', 'best price', 'negotiable', 'cash payment',
        'good condition', 'excellent condition', 'brand new', 'used', 'second hand',
        'original', 'genuine', 'pakistan', 'quality', 'affordable', 'cheap', 'reasonable'
    ];

    private $adTemplates = [
        'Mobile Phones' => [
            'titles' => [
                'Apple iPhone {model} - {condition} Condition',
                'Samsung Galaxy {model} - Latest Model',
                'Brand New {brand} {model} - Unboxed',
                '{brand} {model} - Excellent Condition',
                'Urgent Sale: {brand} {model} Mobile Phone'
            ],
            'descriptions' => [
                'This {brand} {model} is in {condition} condition. Battery health {battery}%. Comes with original box and charger.',
                'Selling my {brand} {model}. Perfect working condition. No scratches on screen. Price is negotiable.',
                'Brand new {brand} {model}, sealed pack. Original warranty available. Hurry up!',
                '{condition} condition {brand} {model}. All features working perfectly. Reason for selling: {reason}.',
                'Urgent sale! {brand} {model} mobile phone. Includes extra accessories. Cash payment only.'
            ],
            'models' => ['13 Pro Max', '14 Pro', '15 Plus', 'S23 Ultra', 'A54', 'Note 20', 'Poco X5', 'Redmi Note 12'],
            'conditions' => ['Brand New', 'Like New', 'Excellent', 'Good', 'Fair'],
            'reasons' => ['Upgraded to new phone', 'Going abroad', 'Need money urgently', 'Duplicate phone', 'Switching brands']
        ],
        'Cars' => [
            'titles' => [
                '{year} {brand} {model} - {condition} Condition',
                'Urgent Sale: {brand} {model} {year}',
                '{brand} {model} - Well Maintained',
                'Car {brand} {model} - Low Mileage',
                'First Owner {brand} {model} - Accident Free'
            ],
            'descriptions' => [
                '{year} {brand} {model} in {condition} condition. Mileage: {mileage} km. {fuel_type} engine. Well maintained.',
                'Selling my {brand} {model}. First owner. No accident history. Regular service done.',
                'Urgent sale! {brand} {model} {year} model. Price negotiable for serious buyers.',
                'Excellent condition {brand} {model}. All papers complete. Bank loan available.',
                'Well maintained {brand} {model}. Fuel average: {average} km/l. Reason for selling: {reason}.'
            ],
            'models' => ['Corolla', 'Civic', 'City', 'Swift', 'Mehran', 'Alto', 'Cultus', 'Vitz', 'Bolan'],
            'years' => ['2018', '2019', '2020', '2021', '2022', '2023'],
            'conditions' => ['Excellent', 'Very Good', 'Good', 'Average'],
            'fuel_types' => ['Petrol', 'Diesel', 'Hybrid', 'CNG'],
            'reasons' => ['Going abroad', 'Buying new car', 'Need money', 'Company car duplicate']
        ],
        'Motorcycles' => [
            'titles' => [
                '{year} {brand} {model} - {condition}',
                'Urgent Sale: {brand} {model}',
                '{brand} {model} - Low Mileage',
                'Brand New {brand} {model} - Showroom Condition'
            ],
            'descriptions' => [
                '{year} {brand} {model} in {condition} condition. Mileage: {mileage} km. Well maintained.',
                'Selling my {brand} {model}. First owner. No accident. Excellent condition.',
                'Brand new {brand} {model}, just arrived from showroom. Hurry up!',
                'Urgent sale! {brand} {model}. Price negotiable. Cash payment preferred.'
            ],
            'models' => ['CD 70', 'CG 125', 'GS 150', 'YBR 125', 'United 100', 'PRIDO 110'],
            'years' => ['2020', '2021', '2022', '2023'],
            'conditions' => ['Brand New', 'Excellent', 'Very Good', 'Good']
        ],
        'Houses' => [
            'titles' => [
                '{bedrooms} Bedroom House for {type} in {area}',
                'Beautiful House Available for {type}',
                'Luxury House {type} - Prime Location',
                'Urgent {type}: {bedrooms} Bed House'
            ],
            'descriptions' => [
                'Beautiful {bedrooms} bedroom house available for {type} in {area}. {features}. Contact for viewing.',
                'Luxury house in prime location. {features}. Ideal for family.',
                'Urgent {type}! {bedrooms} bed house with modern facilities. Price negotiable.',
                'Spacious house with {bedrooms} bedrooms. {features}. Available immediately.'
            ],
            'types' => ['Sale', 'Rent'],
            'bedrooms' => ['2', '3', '4', '5'],
            'areas' => ['DHA', 'Bahria Town', 'Gulberg', 'Model Town', 'Clifton', 'F-8', 'E-11'],
            'features' => ['Fully furnished', 'Semi furnished', 'Unfurnished', 'Modern kitchen', 'Parking available', 'Garden', 'Security']
        ],
        'Clothes' => [
            'titles' => [
                '{brand} {item} - {size} Size',
                'Brand New {brand} {item}',
                'Designer {item} - Latest Collection',
                'Urgent Sale: {brand} {item}'
            ],
            'descriptions' => [
                'Brand new {brand} {item}, size {size}. Original price {original_price}, selling for {price}.',
                'Designer {item} from {brand}. Perfect condition. Never worn.',
                'Latest collection {brand} {item}. Available in different sizes.',
                'Urgent sale! {brand} {item}. Price negotiable.'
            ],
            'items' => ['Shirt', 'T-Shirt', 'Jeans', 'Suit', 'Kurta', 'Saree', 'Dress'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
            'conditions' => ['Brand New', 'Like New', 'Excellent']
        ]
    ];

    private $defaultTemplate = [
        'titles' => ['{category} - {condition} Condition', 'Urgent Sale: {category}', 'Brand New {category}', '{category} Available - Best Price'],
        'descriptions' => ['Selling {category} in {condition} condition. Reason for selling: {reason}.', 'Brand new {category}, never used. Original packaging.', 'Urgent sale! {category} available at reasonable price.', 'Excellent condition {category}. Price is negotiable.'],
        'conditions' => ['Brand New', 'Excellent', 'Very Good', 'Good', 'Fair'],
        'reasons' => ['Not needed', 'Upgraded', 'Going abroad', 'Need money', 'Duplicate item']
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create storage directory if it doesn't exist
        Storage::disk('public')->makeDirectory('ads');

        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Get all leaf categories
        $leafCategories = Category::whereDoesntHave('children')->get();
        $brands = Brand::all();

        $this->command->info('Starting to create 200 ads with attributes and local images...');

        for ($i = 0; $i < 200; $i++) {
            try {
                $category = $leafCategories->random();
                $categoryBrands = $category->brands;
                $brand = $categoryBrands->isEmpty() ? $brands->random() : $categoryBrands->random();

                $region = array_rand($this->pakistanCities);
                $city = $this->pakistanCities[$region][array_rand($this->pakistanCities[$region])];

                $adData = $this->generateAdData($category->name, $brand->name);
                $searchKeywords = $this->generateSearchKeywords(
                    $category->name, $brand->name, $adData['title'],
                    $adData['description'], $city, $region
                );

                $ad = Ad::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'ad_title' => $adData['title'],
                    'description' => $adData['description'],
                    'price' => $adData['price'],
                    'city' => $city,
                    'location' => $region,
                    'seller_name' => $this->pakistaniNames[array_rand($this->pakistaniNames)],
                    'seller_phone' => '03' . rand(10, 99) . '-' . rand(1000000, 9999999),
                    'search_keywords' => $searchKeywords,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);

                // Add attribute values for this ad
                $this->addAttributeValues($ad, $category);

                // Add local dummy images (1-3 per ad)
                // $this->addLocalImages($ad);

                if ($i % 20 == 0) {
                    $this->command->info("Created {$i} ads...");
                }
            } catch (\Exception $e) {
                $this->command->error("Error creating ad: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info('Created 200 ads with attributes and local images!');
    }

    /**
     * Add attribute values for the ad based on its category.
     */
    private function addAttributeValues(Ad $ad, Category $category): void
    {
        $attributes = CategoryAttribute::where('category_id', $category->id)->get();

        foreach ($attributes as $attribute) {
            $value = $this->generateAttributeValue($attribute);
            if ($value !== null) {
                AdAttributeValue::create([
                    'ad_id' => $ad->id,
                    'category_attribute_id' => $attribute->id,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Generate a value for a given attribute.
     */
    private function generateAttributeValue(CategoryAttribute $attribute): ?string
    {
        switch ($attribute->type) {
            case 'select':
                $options = AttributeOption::where('category_attribute_id', $attribute->id)->get();
                if ($options->isNotEmpty()) {
                    return $options->random()->value;
                }
                return $this->randomContextualValue($attribute->name);

            case 'boolean':
                return rand(0, 1) ? 'Yes' : 'No';

            case 'date':
                return now()->subDays(rand(0, 365))->format('Y-m-d');

            case 'text':
            default:
                return $this->randomContextualValue($attribute->name);
        }
    }

    /**
     * Generate contextual text for attribute names.
     */
    private function randomContextualValue(string $attributeName): string
    {
        $name = strtolower($attributeName);
        if (str_contains($name, 'storage') || str_contains($name, 'capacity')) {
            return rand(1, 3) . ' ' . ['32GB', '64GB', '128GB', '256GB', '512GB'][array_rand(['32GB', '64GB', '128GB', '256GB', '512GB'])];
        }
        if (str_contains($name, 'ram')) {
            return ['2GB', '4GB', '6GB', '8GB', '12GB', '16GB'][array_rand(['2GB', '4GB', '6GB', '8GB', '12GB', '16GB'])];
        }
        if (str_contains($name, 'camera')) {
            return rand(12, 108) . 'MP ' . (rand(0, 1) ? 'Ultra Wide' : '');
        }
        if (str_contains($name, 'mileage') || str_contains($name, 'kilometers')) {
            return rand(1000, 150000) . ' km';
        }
        if (str_contains($name, 'engine')) {
            return rand(600, 3000) . ' cc';
        }
        if (str_contains($name, 'area')) {
            return rand(500, 5000) . ' sq ft';
        }
        if (str_contains($name, 'bedrooms')) {
            return (string) rand(1, 5);
        }
        if (str_contains($name, 'bathrooms')) {
            return (string) rand(1, 4);
        }
        if (str_contains($name, 'color')) {
            $colors = ['Black', 'White', 'Silver', 'Gold', 'Blue', 'Red', 'Green', 'Purple', 'Gray'];
            return $colors[array_rand($colors)];
        }
        if (str_contains($name, 'condition')) {
            $conds = ['New', 'Like New', 'Very Good', 'Good', 'Fair'];
            return $conds[array_rand($conds)];
        }
        if (str_contains($name, 'fuel')) {
            return ['Petrol', 'Diesel', 'Hybrid', 'Electric', 'CNG'][array_rand(['Petrol', 'Diesel', 'Hybrid', 'Electric', 'CNG'])];
        }
        if (str_contains($name, 'transmission')) {
            return ['Manual', 'Automatic', 'CVT'][array_rand(['Manual', 'Automatic', 'CVT'])];
        }
        return ucfirst(Str::random(rand(5, 12)));
    }

    /**
     * Add local dummy images (JPEG) using GD – no external calls.
     */
    private function addLocalImages(Ad $ad): void
    {
        $numImages = rand(1, 3);
        $directory = 'ads/' . $ad->id;
        Storage::disk('public')->makeDirectory($directory);

        for ($i = 0; $i < $numImages; $i++) {
            $filename = Str::random(40) . '.jpg';
            $path = $directory . '/' . $filename;

            // Create image
            $width = 800;
            $height = 600;
            $image = imagecreatetruecolor($width, $height);

            // Random background color
            $bgColor = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
            imagefill($image, 0, 0, $bgColor);

            // Add text (ad info)
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $text = "Ad #{$ad->id} - " . Str::limit($ad->ad_title, 30);
            $fontSize = 5;
            $x = ($width - imagefontwidth($fontSize) * strlen($text)) / 2;
            $y = ($height - imagefontheight($fontSize)) / 2;
            imagestring($image, $fontSize, $x, $y, $text, $textColor);

            // Output JPEG
            ob_start();
            imagejpeg($image, null, 75);
            $imageContent = ob_get_clean();
            imagedestroy($image);

            Storage::disk('public')->put($path, $imageContent);

            AdImage::create([
                'ad_id' => $ad->id,
                'path' => 'storage/' . $path,
                'is_primary' => ($i === 0),
            ]);
        }
    }

    // ----------------------------------------------------------------------
    // Helper methods from your original seeder (unchanged)
    // ----------------------------------------------------------------------

    private function generateSearchKeywords($category, $brand, $title, $description, $city, $region): array
    {
        $keywords = [];
        if (isset($this->searchKeywordsByCategory[$category])) {
            $keywords = array_merge($keywords, $this->searchKeywordsByCategory[$category]);
        } else {
            $keywords[] = strtolower($category);
        }
        $keywords[] = strtolower($brand);
        $keywords[] = strtolower($city);
        $keywords[] = strtolower($region);
        $keywords[] = 'pakistan';

        $text = $title . ' ' . $description;
        $text = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $text));
        $words = preg_split('/\s+/', $text);
        $textWords = array_filter($words, fn($w) => strlen($w) >= 3 && !is_numeric($w));
        $keywords = array_merge($keywords, array_slice(array_unique($textWords), 0, 10));
        $keywords = array_merge($keywords, $this->defaultKeywords);

        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, 20);
        $keywords = array_map('trim', $keywords);
        $keywords = array_filter($keywords, fn($kw) => !empty($kw) && strlen($kw) >= 2);
        return array_values($keywords);
    }

    private function generateAdData(string $categoryName, string $brandName): array
    {
        $template = $this->adTemplates[$categoryName] ?? $this->defaultTemplate;
        $title = $this->replaceTemplatePlaceholders($template['titles'][array_rand($template['titles'])], $categoryName, $brandName, $template);
        $description = $this->replaceTemplatePlaceholders($template['descriptions'][array_rand($template['descriptions'])], $categoryName, $brandName, $template);
        $price = $this->generatePrice($categoryName);
        return ['title' => $title, 'description' => $description, 'price' => $price];
    }

    private function replaceTemplatePlaceholders(string $text, string $category, string $brand, array $template): string
    {
        $replacements = [
            '{category}' => $category,
            '{brand}' => $brand,
            '{condition}' => $template['conditions'][array_rand($template['conditions'])] ?? 'Good',
            '{reason}' => $template['reasons'][array_rand($template['reasons'])] ?? 'Not needed',
        ];
        foreach ($template as $key => $values) {
            if (is_array($values) && !in_array($key, ['titles', 'descriptions', 'conditions', 'reasons'])) {
                $placeholder = '{' . $key . '}';
                if (strpos($text, $placeholder) !== false) {
                    $replacements[$placeholder] = $values[array_rand($values)];
                }
            }
        }
        if (strpos($text, '{model}') !== false) {
            $replacements['{model}'] = $this->getRandomModel($category, $brand);
        }
        if (strpos($text, '{mileage}') !== false) $replacements['{mileage}'] = rand(10000, 100000);
        if (strpos($text, '{average}') !== false) $replacements['{average}'] = rand(10, 25);
        if (strpos($text, '{battery}') !== false) $replacements['{battery}'] = rand(80, 100);
        if (strpos($text, '{year}') !== false) $replacements['{year}'] = rand(2018, 2023);
        if (strpos($text, '{original_price}') !== false) $replacements['{original_price}'] = 'Rs ' . number_format(rand(5000, 50000));
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    private function getRandomModel(string $category, string $brand): string
    {
        $models = [
            'Mobile Phones' => [
                'Apple' => ['iPhone 13', 'iPhone 14', 'iPhone 15', 'iPhone 12 Pro'],
                'Samsung' => ['Galaxy S23', 'Galaxy A54', 'Galaxy Note 20', 'Galaxy Z Flip'],
                'Xiaomi' => ['Redmi Note 12', 'Poco X5', 'Mi 11 Lite', 'Redmi 10'],
            ],
            'Cars' => [
                'Toyota' => ['Corolla', 'Vitz', 'Prius', 'Hilux'],
                'Honda' => ['Civic', 'City', 'Accord', 'BR-V'],
                'Suzuki' => ['Mehran', 'Cultus', 'Alto', 'Swift'],
            ],
            'Motorcycles' => [
                'Honda' => ['CD 70', 'CG 125', 'CB 150F'],
                'Yamaha' => ['YBR 125', 'YZF R15'],
                'Suzuki' => ['GD 110', 'GS 150'],
            ]
        ];
        return $models[$category][$brand][array_rand($models[$category][$brand] ?? ['Generic Model'])] ?? 'Generic Model';
    }

    private function generatePrice(string $category): float
    {
        $priceRanges = [
            'Mobile Phones' => [15000, 300000],
            'Cars' => [500000, 10000000],
            'Motorcycles' => [50000, 500000],
            'Houses' => [5000000, 50000000],
            'Apartments & Flats' => [2000000, 20000000],
            'Clothes' => [500, 10000],
            'Televisions & Accessories' => [15000, 300000],
            'Computers & Accessories' => [20000, 500000],
            'Furniture & Home Decor' => [5000, 200000],
            'Bikes Accessories' => [1000, 50000],
            'Cars Accessories' => [1000, 100000],
            'Pet Food & Accessories' => [500, 10000],
            'Toys' => [200, 10000],
            'Books & Magazines' => [100, 5000],
        ];
        $range = $priceRanges[$category] ?? [1000, 50000];
        return rand($range[0], $range[1]);
    }
}