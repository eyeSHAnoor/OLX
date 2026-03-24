<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AdSeeder extends Seeder
{
    // Cities of Pakistan with their regions
    private $pakistanCities = [
        // Punjab
        'Punjab' => [
            'Lahore', 'Faisalabad', 'Rawalpindi', 'Multan', 'Gujranwala', 'Sialkot',
            'Bahawalpur', 'Sargodha', 'Gujrat', 'Sheikhupura', 'Jhelum', 'Sahiwal',
            'Wah Cantonment', 'Kasur', 'Okara', 'Mandi Bahauddin', 'Pakpattan'
        ],
        
        // Sindh
        'Sindh' => [
            'Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Nawabshah', 'Mirpur Khas',
            'Jacobabad', 'Shikarpur', 'Khairpur', 'Dadu', 'Badin', 'Thatta'
        ],
        
        // KPK
        'Khyber Pakhtunkhwa' => [
            'Peshawar', 'Mardan', 'Mingora', 'Kohat', 'Abbottabad', 'Dera Ismail Khan',
            'Mansehra', 'Swabi', 'Nowshera', 'Charsadda', 'Karachi (KPK)', 'Haripur'
        ],
        
        // Balochistan
        'Balochistan' => [
            'Quetta', 'Turbat', 'Khuzdar', 'Hub', 'Chaman', 'Gwadar',
            'Dera Murad Jamali', 'Dera Allah Yar', 'Usta Mohammad', 'Loralai'
        ],
        
        // Islamabad & AJK
        'Islamabad & AJK' => [
            'Islamabad', 'Muzaffarabad', 'Mirpur', 'Kotli', 'Rawalakot', 'Bagh'
        ],
        
        // Gilgit-Baltistan
        'Gilgit-Baltistan' => [
            'Gilgit', 'Skardu', 'Chilas', 'Ghizer', 'Hunza', 'Nagar'
        ]
    ];

    // Popular Pakistani names for sellers
    private $pakistaniNames = [
        'Ali', 'Ahmed', 'Muhammad', 'Hassan', 'Hussain', 'Usman', 'Omar', 'Bilal',
        'Zain', 'Abdullah', 'Fatima', 'Aisha', 'Zainab', 'Maryam', 'Sana', 'Hina',
        'Rabia', 'Sadia', 'Kashif', 'Kamran', 'Salman', 'Imran', 'Waqas', 'Farhan',
        'Nadia', 'Saima', 'Ayesha', 'Sara', 'Mehak', 'Anum'
    ];

    // Common search keywords by category
    private $searchKeywordsByCategory = [
        'Mobile Phones' => [
            'smartphone', 'android', 'ios', 'mobile', 'phone', 'cellphone',
            'used phone', 'new phone', 'iphone', 'samsung', 'xiaomi', 'huawei',
            '5g', '4g', 'dual sim', 'camera', 'gaming phone', 'budget phone'
        ],
        'Cars' => [
            'vehicle', 'automobile', 'sedan', 'hatchback', 'suv', 'toyota', 'honda',
            'suzuki', 'used car', 'new car', 'first owner', 'automatic', 'manual',
            'petrol', 'diesel', 'cng', 'low mileage', 'accident free'
        ],
        'Motorcycles' => [
            'bike', 'scooter', 'motorcycle', 'honda', 'yamaha', 'suzuki',
            '70cc', '125cc', '150cc', 'new bike', 'used bike', 'petrol',
            'economical', 'cd70', 'cg125', 'delivery bike'
        ],
        'Houses' => [
            'home', 'property', 'real estate', 'residential', 'villa', 'banglow',
            'for sale', 'for rent', 'luxury', 'modern', 'furnished', 'unfurnished',
            'dha', 'bahria', 'gulberg', '3bed', '4bed', 'garden', 'parking'
        ],
        'Clothes' => [
            'apparel', 'fashion', 'garments', 'dress', 'shirt', 'jeans',
            'traditional', 'western', 'branded', 'designer', 'new', 'used',
            'cotton', 'silk', 'woolen', 'summer', 'winter', 'casual', 'formal'
        ],
        'Televisions & Accessories' => [
            'tv', 'television', 'led', 'oled', 'qled', 'smart tv', 'samsung',
            'lg', 'sony', 'new tv', 'used tv', 'home theater', 'soundbar'
        ],
        'Computers & Accessories' => [
            'laptop', 'desktop', 'computer', 'pc', 'macbook', 'dell', 'hp',
            'lenovo', 'gaming laptop', 'office use', 'processor', 'ram', 'ssd',
            'keyboard', 'mouse', 'monitor', 'printer'
        ]
    ];

    // Default keywords for other categories
    private $defaultKeywords = [
        'for sale', 'urgent sale', 'best price', 'negotiable', 'cash payment',
        'good condition', 'excellent condition', 'brand new', 'used', 'second hand',
        'original', 'genuine', 'pakistan', 'quality', 'affordable', 'cheap', 'reasonable'
    ];

    // Ad titles and descriptions by category
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
            'features' => [
                'Fully furnished', 'Semi furnished', 'Unfurnished',
                'Modern kitchen', 'Parking available', 'Garden', 'Security'
            ]
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

    // Default template for categories not specified above
    private $defaultTemplate = [
        'titles' => [
            '{category} - {condition} Condition',
            'Urgent Sale: {category}',
            'Brand New {category}',
            '{category} Available - Best Price'
        ],
        'descriptions' => [
            'Selling {category} in {condition} condition. Reason for selling: {reason}.',
            'Brand new {category}, never used. Original packaging.',
            'Urgent sale! {category} available at reasonable price.',
            'Excellent condition {category}. Price is negotiable.'
        ],
        'conditions' => ['Brand New', 'Excellent', 'Very Good', 'Good', 'Fair'],
        'reasons' => ['Not needed', 'Upgraded', 'Going abroad', 'Need money', 'Duplicate item']
    ];

    // Image placeholders configuration
    private $imagePlaceholders = [
        'Mobile Phones' => ['phone', 'mobile', 'smartphone', 'iphone', 'samsung'],
        'Cars' => ['car', 'vehicle', 'automobile', 'sedan', 'suv'],
        'Motorcycles' => ['motorcycle', 'bike', 'scooter'],
        'Houses' => ['house', 'home', 'property', 'building'],
        'Apartments & Flats' => ['apartment', 'flat', 'condo'],
        'Clothes' => ['clothing', 'fashion', 'dress', 'apparel'],
        'Televisions & Accessories' => ['tv', 'television', 'display'],
        'Computers & Accessories' => ['computer', 'laptop', 'desktop'],
        'Furniture & Home Decor' => ['furniture', 'sofa', 'chair', 'table'],
        'Bikes Accessories' => ['helmet', 'bike parts'],
        'Cars Accessories' => ['car parts', 'accessories'],
        'Pet Food & Accessories' => ['pet', 'dog', 'cat'],
        'Toys' => ['toy', 'game'],
        'Books & Magazines' => ['book', 'reading']
    ];

    private $defaultImagePlaceholders = ['product', 'item', 'goods'];

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

        // Get all leaf categories (categories without children)
        $leafCategories = Category::whereDoesntHave('children')->get();
        // $leafCategories = Category::where('id' , 1)->get();

        
        // Get all brands
        $brands = Brand::all();

        $this->command->info('Starting to create 200 ads with images...');

        // Create 200 random ads
        for ($i = 0; $i < 200; $i++) {
            try {
                // Select random leaf category
                $category = $leafCategories->random();
                
                // Get relevant brands for this category
                $categoryBrands = $category->brands;
                if ($categoryBrands->isEmpty()) {
                    // If no brands attached, use all brands
                    $brand = $brands->random();
                } else {
                    $brand = $categoryBrands->random();
                }

                // Select random region and city
                $region = array_rand($this->pakistanCities);
                $city = $this->pakistanCities[$region][array_rand($this->pakistanCities[$region])];

                // Generate ad data
                $adData = $this->generateAdData($category->name, $brand->name);
                
                // Generate search keywords
                $searchKeywords = $this->generateSearchKeywords(
                    $category->name, 
                    $brand->name, 
                    $adData['title'], 
                    $adData['description'],
                    $city,
                    $region
                );

                // Create the ad
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

                // Add images to the ad (1-3 images per ad)
                $this->addImagesToAd($ad, $category->name);
                
                if ($i % 20 == 0) {
                    $this->command->info("Created {$i} ads...");
                }
                
            } catch (\Exception $e) {
                $this->command->error("Error creating ad: " . $e->getMessage());
                // Continue if there's an error with one ad
                continue;
            }
        }

        $this->command->info('Created 200 ads with images and Pakistani regional data!');
    }

    /**
     * Add placeholder images to an ad
     */
    private function addImagesToAd(Ad $ad, string $categoryName): void
    {
        // Determine how many images to add (1-3)
        $imageCount = rand(1, 3);
        
        // Get appropriate keywords for this category
        $keywords = $this->imagePlaceholders[$categoryName] ?? $this->defaultImagePlaceholders;
        $keyword = $keywords[array_rand($keywords)];
        
        for ($i = 0; $i < $imageCount; $i++) {
            try {
                // Use different placeholder services for variety
                $imagePath = $this->downloadPlaceholderImage($ad->id, $i + 1, $keyword);
                
                if ($imagePath) {
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'path' => $imagePath,
                        'is_primary' => ($i === 0), // First image is primary
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error("Error adding image to ad {$ad->id}: " . $e->getMessage());
                // Continue with next image
                continue;
            }
        }
    }

    /**
     * Download a placeholder image from a service
     */
    private function downloadPlaceholderImage(int $adId, int $imageNumber, string $keyword): ?string
    {
        // Randomly choose between different placeholder services
        $service = rand(1, 3);
        
        // Set random image dimensions (variety of sizes)
        $width = rand(400, 800);
        $height = rand(300, 600);
        
        $imageContent = null;
        
        try {
            switch ($service) {
                case 1:
                    // Using Picsum (random photos)
                    $imageId = rand(1, 1000);
                    $url = "https://picsum.photos/id/{$imageId}/{$width}/{$height}";
                    break;
                    
                case 2:
                    // Using PlaceKitten (cute kittens)
                    $url = "https://placekitten.com/{$width}/{$height}";
                    break;
                    
                case 3:
                    // Using placeholder.picsum with keyword
                    $url = "https://picsum.photos/{$width}/{$height}?random=" . rand(1, 10000);
                    break;
            }
            
            // Download image
            $imageContent = file_get_contents($url);
            
            if ($imageContent === false) {
                throw new \Exception("Failed to download image from {$url}");
            }
            
            // Generate filename
            $filename = "ad_{$adId}_img_{$imageNumber}_" . time() . ".jpg";
            $path = "ads/{$filename}";
            
            // Store image
            Storage::disk('public')->put($path, $imageContent);
            
            return $path;
            
        } catch (\Exception $e) {
            // If online placeholder fails, create a simple colored placeholder
            return $this->createFallbackImage($adId, $imageNumber);
        }
    }

    /**
     * Create a simple colored placeholder image as fallback
     */
    private function createFallbackImage(int $adId, int $imageNumber): ?string
    {
        try {
            // Generate a simple colored square
            $width = 400;
            $height = 400;
            $image = imagecreatetruecolor($width, $height);
            
            // Random color
            $r = rand(0, 255);
            $g = rand(0, 255);
            $b = rand(0, 255);
            $color = imagecolorallocate($image, $r, $g, $b);
            
            // Fill background
            imagefill($image, 0, 0, $color);
            
            // Add some text
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $text = "Ad #{$adId}";
            imagestring($image, 5, 150, 190, $text, $textColor);
            
            // Start output buffering
            ob_start();
            imagejpeg($image);
            $imageContent = ob_get_clean();
            
            // Clean up
            imagedestroy($image);
            
            // Save file
            $filename = "ad_{$adId}_img_{$imageNumber}_fallback_" . time() . ".jpg";
            $path = "ads/{$filename}";
            Storage::disk('public')->put($path, $imageContent);
            
            return $path;
            
        } catch (\Exception $e) {
            $this->command->error("Failed to create fallback image: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate search keywords based on ad content
     */
    private function generateSearchKeywords(
        string $category, 
        string $brand, 
        string $title, 
        string $description,
        string $city,
        string $region
    ): array {
        $keywords = [];
        
        // Add category-specific keywords
        if (isset($this->searchKeywordsByCategory[$category])) {
            $keywords = array_merge($keywords, $this->searchKeywordsByCategory[$category]);
        } else {
            // Add category name as keyword
            $keywords[] = strtolower($category);
        }

        // Add brand as keyword
        $keywords[] = strtolower($brand);
        
        // Add location keywords
        $keywords[] = strtolower($city);
        $keywords[] = strtolower($region);
        $keywords[] = 'pakistan';
        
        // Extract keywords from title and description
        $text = $title . ' ' . $description;
        $text = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $text));
        $words = preg_split('/\s+/', $text);
        
        // Filter meaningful words (3+ characters)
        $textWords = array_filter($words, function($word) {
            return strlen($word) >= 3 && !is_numeric($word);
        });
        
        // Add top words from text (limit to 10)
        $textWords = array_slice(array_unique($textWords), 0, 10);
        $keywords = array_merge($keywords, $textWords);
        
        // Add default keywords
        $keywords = array_merge($keywords, $this->defaultKeywords);
        
        // Remove duplicates and limit to 20 keywords
        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, 20);
        
        // Convert to lowercase and trim
        $keywords = array_map('trim', $keywords);
        $keywords = array_filter($keywords, function($keyword) {
            return !empty($keyword) && strlen($keyword) >= 2;
        });
        
        return array_values($keywords);
    }

    /**
     * Generate ad title, description, and price based on category
     */
    private function generateAdData(string $categoryName, string $brandName): array
    {
        $template = $this->adTemplates[$categoryName] ?? $this->defaultTemplate;
        
        // Generate title
        $title = $this->replaceTemplatePlaceholders(
            $template['titles'][array_rand($template['titles'])],
            $categoryName,
            $brandName,
            $template
        );

        // Generate description
        $description = $this->replaceTemplatePlaceholders(
            $template['descriptions'][array_rand($template['descriptions'])],
            $categoryName,
            $brandName,
            $template
        );

        // Generate price based on category
        $price = $this->generatePrice($categoryName);

        return [
            'title' => $title,
            'description' => $description,
            'price' => $price
        ];
    }

    /**
     * Replace placeholders in template
     */
    private function replaceTemplatePlaceholders(string $text, string $category, string $brand, array $template): string
    {
        $replacements = [
            '{category}' => $category,
            '{brand}' => $brand,
            '{condition}' => $template['conditions'][array_rand($template['conditions'])] ?? 'Good',
            '{reason}' => $template['reasons'][array_rand($template['reasons'])] ?? 'Not needed',
        ];

        // Add dynamic replacements based on template
        foreach ($template as $key => $values) {
            if (is_array($values) && !in_array($key, ['titles', 'descriptions', 'conditions', 'reasons'])) {
                $placeholder = '{' . $key . '}';
                if (strpos($text, $placeholder) !== false) {
                    $replacements[$placeholder] = $values[array_rand($values)];
                }
            }
        }

        // Special replacements
        if (strpos($text, '{model}') !== false) {
            $replacements['{model}'] = $this->getRandomModel($category, $brand);
        }

        if (strpos($text, '{mileage}') !== false) {
            $replacements['{mileage}'] = rand(10000, 100000);
        }

        if (strpos($text, '{average}') !== false) {
            $replacements['{average}'] = rand(10, 25);
        }

        if (strpos($text, '{battery}') !== false) {
            $replacements['{battery}'] = rand(80, 100);
        }

        if (strpos($text, '{year}') !== false) {
            $replacements['{year}'] = rand(2018, 2023);
        }

        if (strpos($text, '{original_price}') !== false) {
            $replacements['{original_price}'] = 'Rs ' . number_format(rand(5000, 50000));
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Generate random model based on category and brand
     */
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

    /**
     * Generate price based on category
     */
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