<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Mobiles',
                'subcategories' => [
                    'Mobile Phones',
                    'Accessories',
                    'Tablets',
                    'Smart Watches',
                    'Landline Phones'
                ]
            ],
            [
                'name' => 'Vehicles',
                'subcategories' => [
                    'Cars',
                    'Cars Accessories',
                    'Spare Parts',
                    'Car Care',
                    'Buses, Vans & Trucks',
                    'Rickshaw & Chingchi',
                    'Tractors & Trailers',
                    'Oil & Lubricants',
                    'Cars on Installments',
                    'Other Vehicles',
                    'Boats'
                ]
            ],
            [
                'name' => 'Property for Sale',
                'subcategories' => [
                    'Land & Plots',
                    'Houses',
                    'Apartments & Flats',
                    'Shops - Offices - Commercial Space',
                    'Portions & Floors'
                ]
            ],
            [
                'name' => 'Property for Rent',
                'subcategories' => [
                    'Houses',
                    'Apartments & Flats',
                    'Portions & Floors',
                    'Shops - Offices - Commercial Space',
                    'Rooms',
                    'Roommates & Paying Guests',
                    'Vacation Rentals - Guest Houses',
                    'Land & Plots'
                ]
            ],
            [
                'name' => 'Electronics & Home Appliances',
                'subcategories' => [
                    'Computers & Accessories',
                    'Televisions & Accessories',
                    'Generators, UPS & Power Solutions',
                    'Games & Entertainment',
                    'Cameras & Accessories',
                    'Heaters & Geysers',
                    'Kitchen Appliances',
                    'Video-Audios',
                    'Refrigerators & Freezers',
                    'AC & Coolers',
                    'Other Home Appliances',
                    'Washing Machines & Dryers',
                    'Tools & DIY Equipment',
                    'Fans',
                    'Microwaves & Ovens',
                    'Sewing Machines',
                    'Irons & Steamers',
                    'Water Dispensers',
                    'Air Purifiers & Humidifiers'
                ]
            ],
            [
                'name' => 'Bikes',
                'subcategories' => [
                    'Motorcycles',
                    'Bicycles',
                    'Spare Parts',
                    'Scooters',
                    'Bikes Accessories',
                    'ATV & Quads',
                    'Bike Care'
                ]
            ],
            [
                'name' => 'Business, Industrial & Agriculture',
                'subcategories' => [
                    'Other Business & Industry',
                    'Food & Restaurants',
                    'Medical & Pharma',
                    'Trade & Industrial Machinery',
                    'Construction & Heavy Machinery',
                    'Business for Sale',
                    'Agriculture',
                    'Services'
                ]
            ],
            [
                'name' => 'Services',
                'subcategories' => [
                    'Other Services',
                    'Car Rental',
                    'Tuitions & Academies',
                    'Home & Office Repair',
                    'Domestic Help',
                    'Web Development',
                    'Electronics & Computer Repair',
                    'Travel & Visa',
                    'Event Services',
                    'Drivers & Taxi',
                    'Construction Services',
                    'Farm & Fresh Food',
                    'Movers & Packers',
                    'Consultancy Services',
                    'Health & Beauty',
                    'Architecture & Interior Design',
                    'Video & Photography',
                    'Camera Installation',
                    'Renting Services',
                    'Car Services',
                    'Tailor Services',
                    'Catering & Restaurant',
                    'Insurance Services'
                ]
            ],
            [
                'name' => 'Jobs',
                'subcategories' => [
                    'Other Jobs',
                    'Online',
                    'Sales',
                    'Part Time',
                    'Restaurants & Hospitality',
                    'Marketing',
                    'Customer Service',
                    'Domestic Staff',
                    'Education',
                    'Delivery Riders',
                    'Medical',
                    'Accounting & Finance',
                    'IT & Networking',
                    'Graphic Design',
                    'Clerical & Administration',
                    'Hotels & Tourism',
                    'Manufacturing',
                    'Engineering',
                    'Content Writing',
                    'Security',
                    'Real Estate',
                    'Human Resources',
                    'Internships',
                    'Advertising & PR',
                    'Architecture & Interior Design'
                ]
            ],
            [
                'name' => 'Animals',
                'subcategories' => [
                    'Hens',
                    'Cats',
                    'Parrots',
                    'Pet Food & Accessories',
                    'Dogs',
                    'Livestock',
                    'Pigeons',
                    'Finches',
                    'Fish',
                    'Rabbits',
                    'Fertile Eggs',
                    'Other Birds',
                    'Ducks',
                    'Other Animals',
                    'Doves',
                    'Peacocks',
                    'Horses'
                ]
            ],
            [
                'name' => 'Furniture & Home Decor',
                'subcategories' => [
                    'Beds & Wardrobes',
                    'Sofa & Chairs',
                    'Other Household Items',
                    'Home Decoration',
                    'Tables & Dining',
                    'Office Furniture',
                    'Garden & Outdoor',
                    'Home DIY & Renovations',
                    'Bathroom Accessories',
                    'Kitchen Essentials',
                    'Lighting',
                    'Painting & Mirrors',
                    'Rugs & Carpets',
                    'Curtains & Blinds',
                    'Home Essentials'
                ]
            ],
            [
                'name' => 'Fashion & Beauty',
                'subcategories' => [
                    'Clothes',
                    'Watches',
                    'Wedding',
                    'Skin & Hair',
                    'Footwear',
                    'Jewellery',
                    'Bags',
                    'Fragrance',
                    'Bath & Body',
                    'Fashion Accessories',
                    'Makeup',
                    'Other Fashion',
                    'DIY Jewellery'
                ]
            ],
            [
                'name' => 'Books, Sports & Hobbies',
                'subcategories' => [
                    'Books & Magazines',
                    'Gym & Fitness',
                    'Sports Equipment',
                    'Arts & Crafts',
                    'Other Hobbies',
                    'Musical Instruments',
                    'Camping & Hiking',
                    'Collectables',
                    'Crafts & DIY Supplies',
                    'Calendars'
                ]
            ],
            [
                'name' => 'Kids',
                'subcategories' => [
                    'Toys',
                    'Kids Vehicles',
                    'Baby Gear',
                    'Kids Accessories',
                    'Swings & Slides',
                    'Kids Furniture',
                    'Kids Clothing',
                    'Bath & Diapers'
                ]
            ]
        ];

        $position = 1;
        
        foreach ($categories as $categoryData) {
            // Create main category
            $mainCategory = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'parent_id' => null,
                'position' => $position++
            ]);

            $subPosition = 1;
            // Create subcategories
            foreach ($categoryData['subcategories'] as $subcategoryName) {
                $slug = Str::slug($mainCategory->name . ' ' . $subcategoryName);
                $uniqueSlug = $slug;
                $counter = 1;
                while (Category::where('slug', $uniqueSlug)->exists()) {
                    $uniqueSlug = $slug . '-' . $counter++;
                }
                
                Category::create([
                    'name' => $subcategoryName,
                    'slug' => $uniqueSlug,
                    'parent_id' => $mainCategory->id,
                    'position' => $subPosition++
                ]);
            }
        }
    }
}