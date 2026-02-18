<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories to find leaf categories
        $categories = Category::with('children')->get();
        
        // Define brands for each leaf category
        $brandsByCategory = [
            // Mobiles
            'Mobile Phones' => [
                'Apple', 'Samsung', 'Xiaomi', 'Oppo', 'Vivo', 'Realme', 'OnePlus',
                'Google', 'Motorola', 'Nokia', 'Huawei', 'Sony', 'LG', 'Asus',
                'Infinix', 'Tecno', 'Itel', 'Honor'
            ],
            'Accessories' => [
                'Anker', 'Belkin', 'Baseus', 'UGREEN', 'JBL', 'Samsung',
                'Xiaomi', 'Spigen', 'Case-Mate', 'OtterBox', 'Mophie'
            ],
            'Tablets' => [
                'Apple', 'Samsung', 'Lenovo', 'Huawei', 'Microsoft', 'Xiaomi',
                'Amazon', 'Realme', 'Asus', 'Google'
            ],
            'Smart Watches' => [
                'Apple', 'Samsung', 'Fitbit', 'Garmin', 'Amazfit', 'Xiaomi',
                'Fossil', 'Huawei', 'Realme', 'Noise', 'Boat'
            ],
            'Landline Phones' => [
                'Panasonic', 'Motorola', 'Philips', 'VTech', 'AT&T',
                'GE', 'Uniden', 'Cobra'
            ],

            // Vehicles
            'Cars' => [
                'Toyota', 'Honda', 'Suzuki', 'BMW', 'Mercedes-Benz', 'Audi',
                'Volkswagen', 'Ford', 'Hyundai', 'Kia', 'Nissan', 'Mitsubishi',
                'Chevrolet', 'Daihatsu', 'Lexus', 'Porsche'
            ],
            'Cars Accessories' => [
                'Bosch', 'Denso', 'NGK', 'Michelin', 'Bridgestone', 'Goodyear',
                'Yokohama', 'Pirelli', 'Mobil', 'Shell', 'Castrol', 'Motul'
            ],
            'Spare Parts' => [
                'Bosch', 'Denso', 'Aisin', 'NGK', 'Exedy', 'ZF', 'Brembo',
                'TRW', 'KYB', 'Monroe', 'Gates'
            ],
            'Car Care' => [
                'Meguiar\'s', 'Turtle Wax', '3M', 'Armor All', 'Chemical Guys',
                'Sonax', 'Mother\'s', 'Adam\'s Polishes'
            ],
            'Buses, Vans & Trucks' => [
                'Hino', 'Isuzu', 'Fuso', 'Mercedes-Benz', 'Volvo', 'Scania',
                'MAN', 'Iveco', 'TATA', 'Ashok Leyland'
            ],
            'Rickshaw & Chingchi' => [
                'Suzuki', 'Honda', 'Yamaha', 'Piaggio', 'Bajaj', 'TVS'
            ],
            'Tractors & Trailers' => [
                'John Deere', 'Massey Ferguson', 'New Holland', 'Kubota',
                'Mahindra', 'Case IH', 'CLAAS'
            ],
            'Oil & Lubricants' => [
                'Shell', 'Castrol', 'Mobil', 'Total', 'Valvoline', 'Motul',
                'Liqui Moly', 'Pennzoil', 'Repsol'
            ],
            'Cars on Installments' => [
                'Toyota', 'Honda', 'Suzuki', 'BMW', 'Mercedes-Benz', 'Audi',
                'Hyundai', 'Kia', 'Nissan', 'Mitsubishi'
            ],
            'Other Vehicles' => [
                'Yamaha', 'Kawasaki', 'Honda', 'Suzuki', 'BMW', 'Ducati'
            ],
            'Boats' => [
                'Bayliner', 'Sea Ray', 'Yamaha', 'Mercury', 'Suzuki', 'MasterCraft'
            ],

            // Electronics & Home Appliances
            'Computers & Accessories' => [
                'Apple', 'Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Microsoft',
                'Logitech', 'Razer', 'Corsair', 'Samsung', 'LG', 'BenQ'
            ],
            'Televisions & Accessories' => [
                'Samsung', 'LG', 'Sony', 'Panasonic', 'TCL', 'Hisense', 'Xiaomi',
                'Philips', 'Sharp', 'Vizio'
            ],
            'Generators, UPS & Power Solutions' => [
                'Honda', 'Yamaha', 'Generac', 'Cummins', 'Kohler', 'APC',
                'CyberPower', 'Eaton', 'Delta'
            ],
            'Games & Entertainment' => [
                'Sony', 'Microsoft', 'Nintendo', 'Steam', 'Razer', 'Logitech',
                'NVIDIA', 'AMD', 'Corsair'
            ],
            'Cameras & Accessories' => [
                'Canon', 'Nikon', 'Sony', 'Fujifilm', 'Panasonic', 'Olympus',
                'GoPro', 'DJI', 'Sigma', 'Tamron'
            ],
            'Kitchen Appliances' => [
                'Philips', 'Panasonic', 'LG', 'Samsung', 'Moulinex', 'Kenwood',
                'KitchenAid', 'Breville', 'Ninja', 'Instant Pot'
            ],
            'Refrigerators & Freezers' => [
                'LG', 'Samsung', 'Haier', 'Whirlpool', 'Panasonic', 'Hitachi',
                'Electrolux', 'Frigidaire', 'Kenmore'
            ],
            'AC & Coolers' => [
                'Daikin', 'LG', 'Panasonic', 'Samsung', 'Carrier', 'Mitsubishi',
                'Hitachi', 'General', 'Haier'
            ],
            'Washing Machines & Dryers' => [
                'LG', 'Samsung', 'Whirlpool', 'Panasonic', 'Haier', 'Electrolux',
                'Bosch', 'Miele', 'Fisher & Paykel'
            ],
            'Fans' => [
                'Panasonic', 'Havells', 'Crompton', 'Bajaj', 'Usha', 'Orient',
                'Khaitan', 'Luminous'
            ],
            'Microwaves & Ovens' => [
                'Panasonic', 'LG', 'Samsung', 'Whirlpool', 'IFB', 'Morphy Richards',
                'Philips', 'Bajaj', 'Prestige'
            ],

            // Bikes
            'Motorcycles' => [
                'Honda', 'Yamaha', 'Suzuki', 'Kawasaki', 'Harley-Davidson',
                'BMW', 'Ducati', 'KTM', 'Triumph', 'Royal Enfield',
                'Bajaj', 'Hero', 'TVS'
            ],
            'Bicycles' => [
                'Giant', 'Trek', 'Specialized', 'Cannondale', 'Scott',
                'Merida', 'Cube', 'Bianchi', 'Hero', 'Atlas', 'BSA'
            ],
            'Scooters' => [
                'Honda', 'Yamaha', 'Suzuki', 'Vespa', 'Aprilia', 'Piaggio',
                'Hero', 'TVS', 'Bajaj'
            ],
            'Bikes Accessories' => [
                'Bell', 'Shimano', 'SRAM', 'Fox', 'RockShox', 'Continental',
                'Michelin', 'Giro', 'Oakley', 'POC'
            ],
            'ATV & Quads' => [
                'Yamaha', 'Honda', 'Kawasaki', 'Suzuki', 'Can-Am', 'Polaris'
            ],

            // Fashion & Beauty
            'Clothes' => [
                'Nike', 'Adidas', 'Zara', 'H&M', 'Levi\'s', 'Uniqlo',
                'Puma', 'Tommy Hilfiger', 'Calvin Klein', 'Lacoste',
                'Gucci', 'Prada', 'Louis Vuitton', 'Chanel'
            ],
            'Watches' => [
                'Rolex', 'Omega', 'Tag Heuer', 'Casio', 'Seiko', 'Citizen',
                'Fossil', 'Michael Kors', 'Daniel Wellington', 'Apple'
            ],
            'Footwear' => [
                'Nike', 'Adidas', 'Puma', 'Reebok', 'Converse', 'Vans',
                'Skechers', 'Clarks', 'Crocs', 'Bata'
            ],
            'Jewellery' => [
                'Tiffany & Co.', 'Cartier', 'Swarovski', 'Pandora',
                'Chopard', 'Bvlgari', 'Mikimoto', 'David Yurman'
            ],
            'Bags' => [
                'Louis Vuitton', 'Gucci', 'Chanel', 'Prada', 'Hermès',
                'Michael Kors', 'Coach', 'Kate Spade', 'Longchamp'
            ],
            'Makeup' => [
                'MAC', 'Estée Lauder', 'L\'Oréal', 'Maybelline', 'Revlon',
                'Clinique', 'NARS', 'Bobbi Brown', 'Chanel', 'Dior'
            ],

            // Furniture & Home Decor
            'Beds & Wardrobes' => [
                'IKEA', 'Ashley', 'La-Z-Boy', 'Ethan Allen', 'Herman Miller',
                'Steelcase', 'Hooker Furniture', 'Broyhill'
            ],
            'Sofa & Chairs' => [
                'IKEA', 'Ashley', 'La-Z-Boy', 'Flexsteel', 'Bernhardt',
                'Klaussner', 'Stanley'
            ],
            'Home Decoration' => [
                'IKEA', 'H&M Home', 'Zara Home', 'Anthropologie',
                'West Elm', 'Crate & Barrel', 'Pottery Barn'
            ],
            'Office Furniture' => [
                'Herman Miller', 'Steelcase', 'Haworth', 'Knoll',
                'IKEA', 'HON', 'Global'
            ],

            // Animals
            'Pet Food & Accessories' => [
                'Royal Canin', 'Hill\'s Science Diet', 'Purina', 'Iams',
                'Pedigree', 'Whiskas', 'Friskies', 'Taste of the Wild'
            ],

            // Kids
            'Toys' => [
                'LEGO', 'Barbie', 'Hot Wheels', 'Fisher-Price', 'Hasbro',
                'Mattel', 'Nerf', 'Play-Doh', 'VTech'
            ],
            'Kids Clothing' => [
                'Carters', 'Gap Kids', 'Old Navy', 'Children\'s Place',
                'H&M Kids', 'Zara Kids', 'Nike Kids', 'Adidas Kids'
            ],

            // Business & Industrial
            'Medical & Pharma' => [
                'Johnson & Johnson', 'Pfizer', 'Novartis', 'Roche',
                'Merck', 'GSK', 'Sanofi', 'Abbott'
            ],
            'Construction & Heavy Machinery' => [
                'Caterpillar', 'Komatsu', 'Volvo', 'Hitachi', 'Liebherr',
                'JCB', 'Doosan', 'Hyundai'
            ]
        ];

        foreach ($brandsByCategory as $categoryName => $brandNames) {
            // Find the leaf category
            $category = Category::where('name', $categoryName)->first();
            
            if ($category) {
                foreach ($brandNames as $brandName) {
                    // Find or create brand
                    $brand = Brand::firstOrCreate([
                        'name' => $brandName
                    ]);
                    
                    // Attach brand to category if not already attached
                    if (!$category->brands()->where('brand_id', $brand->id)->exists()) {
                        $category->brands()->attach($brand->id);
                    }
                }
            }
        }

        // Also attach some brands to parent categories for broader searches
        $this->attachBrandsToParentCategories();
    }

    private function attachBrandsToParentCategories()
    {
        // Attach mobile brands to Mobiles parent category
        $mobilesCategory = Category::where('name', 'Mobiles')->first();
        if ($mobilesCategory) {
            $mobileBrands = Brand::whereIn('name', [
                'Apple', 'Samsung', 'Xiaomi', 'Oppo', 'Vivo', 'Realme',
                'OnePlus', 'Google', 'Motorola', 'Nokia'
            ])->pluck('id');
            $mobilesCategory->brands()->syncWithoutDetaching($mobileBrands);
        }

        // Attach vehicle brands to Vehicles parent category
        $vehiclesCategory = Category::where('name', 'Vehicles')->first();
        if ($vehiclesCategory) {
            $vehicleBrands = Brand::whereIn('name', [
                'Toyota', 'Honda', 'Suzuki', 'BMW', 'Mercedes-Benz', 'Audi',
                'Ford', 'Hyundai', 'Kia', 'Nissan'
            ])->pluck('id');
            $vehiclesCategory->brands()->syncWithoutDetaching($vehicleBrands);
        }

        // Attach electronics brands to Electronics parent category
        $electronicsCategory = Category::where('name', 'Electronics & Home Appliances')->first();
        if ($electronicsCategory) {
            $electronicsBrands = Brand::whereIn('name', [
                'Samsung', 'LG', 'Sony', 'Panasonic', 'Apple', 'Dell',
                'HP', 'Canon', 'Nikon', 'Philips'
            ])->pluck('id');
            $electronicsCategory->brands()->syncWithoutDetaching($electronicsBrands);
        }

        // Attach bike brands to Bikes parent category
        $bikesCategory = Category::where('name', 'Bikes')->first();
        if ($bikesCategory) {
            $bikeBrands = Brand::whereIn('name', [
                'Honda', 'Yamaha', 'Suzuki', 'Kawasaki', 'Harley-Davidson',
                'Royal Enfield', 'Bajaj', 'Hero', 'TVS'
            ])->pluck('id');
            $bikesCategory->brands()->syncWithoutDetaching($bikeBrands);
        }
    }
}