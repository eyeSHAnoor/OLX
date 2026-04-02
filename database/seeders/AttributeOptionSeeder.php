<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryAttribute;
use App\Models\AttributeOption;

class AttributeOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all category attributes
        $attributes = CategoryAttribute::all();
        
        // Define options for different attribute types
        $optionsByAttribute = [
            // Mobile related
            'Storage Capacity' => ['32GB', '64GB', '128GB', '256GB', '512GB', '1TB'],
            'RAM' => ['2GB', '3GB', '4GB', '6GB', '8GB', '12GB', '16GB'],
            'Screen Resolution' => ['HD', 'Full HD', '2K', '4K', 'Retina'],
            'Operating System' => ['iOS', 'Android', 'Windows', 'Other'],
            'SIM Type' => ['Single SIM', 'Dual SIM', 'eSIM'],
            'Network' => ['4G', '5G', 'WiFi Only'],
            
            // Vehicle related
            'Transmission' => ['Automatic', 'Manual', 'CVT', 'Semi-Automatic'],
            'Fuel Type' => ['Petrol', 'Diesel', 'Hybrid', 'Electric', 'CNG', 'LPG'],
            'Number of Owners' => ['1', '2', '3', '4', '5+'],
            'Assembly' => ['Local', 'Imported', 'CBU', 'CKD'],
            'Body Type' => ['Sedan', 'Hatchback', 'SUV', 'Cross-over', 'Coupe', 'Convertible', 'Wagon', 'Truck', 'Van'],
            'Seating Capacity' => ['2', '4', '5', '6', '7', '8', '9', '10+'],
            'Drivetrain' => ['FWD', 'RWD', 'AWD', '4WD'],
            'Bike Type' => ['Sports', 'Cruiser', 'Touring', 'Dirt Bike', 'Scooter', 'Moped', 'Electric'],
            
            // Property related
            'Property Type' => ['House', 'Apartment', 'Land', 'Commercial', 'Farm House', 'Villa', 'Townhouse'],
            'Bedrooms' => ['Studio', '1', '2', '3', '4', '5+'],
            'Bathrooms' => ['1', '2', '3', '4', '5+'],
            'Purpose' => ['Residential', 'Commercial', 'Industrial', 'Agricultural'],
            'Furnished' => ['Fully Furnished', 'Semi Furnished', 'Unfurnished'],
            'Total Floors' => ['Ground', '1', '2', '3', '4', '5+'],
            'Property On' => ['Corner', 'Main Road', 'Street', 'Lane'],
            'Rental Duration' => ['Monthly', 'Quarterly', 'Yearly', 'Long Term'],
            
            // General attributes
            'Color' => ['Black', 'White', 'Silver', 'Gold', 'Rose Gold', 'Blue', 'Red', 'Green', 'Purple', 'Yellow', 'Orange', 'Pink', 'Brown', 'Gray'],
            'Condition' => ['New', 'Like New', 'Very Good', 'Good', 'Fair', 'Needs Repair'],
            'Year' => ['2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016', '2015', '2014', '2013', '2012', '2011', '2010'],
            'Construction Year' => ['2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016', '2015'],
            'Energy Rating' => ['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'],
            'Smart Features' => ['Yes', 'No'],
            
            // Fashion related
            'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
            'Gender' => ['Men', 'Women', 'Unisex', 'Boys', 'Girls'],
            'Season' => ['Summer', 'Winter', 'Spring', 'Fall', 'All Season'],
            'Occasion' => ['Casual', 'Formal', 'Party', 'Wedding', 'Sports', 'Office', 'Travel', 'Beach'],
            'Fabric Type' => ['Cotton', 'Polyester', 'Silk', 'Wool', 'Leather', 'Denim', 'Linen', 'Nylon', 'Spandex', 'Velvet'],
            
            // Furniture related
            'Material' => ['Wood', 'Metal', 'Glass', 'Plastic', 'Leather', 'Fabric', 'Marble', 'Bamboo', 'Rattan', 'Engineered Wood'],
            'Style' => ['Modern', 'Traditional', 'Contemporary', 'Minimalist', 'Industrial', 'Rustic', 'Scandinavian', 'Bohemian', 'Mid-Century', 'Art Deco'],
            'Room Type' => ['Living Room', 'Bedroom', 'Dining Room', 'Kitchen', 'Office', 'Bathroom', 'Outdoor', 'Kids Room'],
            
            // Job related
            'Job Type' => ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Freelance', 'Temporary', 'Volunteer'],
            'Education Required' => ['High School', 'Bachelor\'s', 'Master\'s', 'PhD', 'Diploma', 'Certificate', 'No Degree Required'],
            'Shift Type' => ['Day', 'Night', 'Evening', 'Rotating', 'Flexible'],
            'Gender Preference' => ['Male', 'Female', 'Any'],
            
            // Service related
            'Service Type' => ['One Time', 'Recurring', 'Hourly', 'Fixed Price'],
            
            // Business related
            'Business Type' => ['Retail', 'Wholesale', 'Manufacturing', 'Service', 'E-commerce', 'Franchise', 'Startup', 'Distribution'],
            'Number of Employees' => ['1-10', '11-50', '51-200', '201-500', '500+'],
            
            // Animal related
            'Animal Type' => ['Dog', 'Cat', 'Bird', 'Fish', 'Rabbit', 'Hamster', 'Reptile', 'Horse', 'Cow', 'Goat', 'Chicken', 'Other'],
            
            // Book & Hobby related
            'Language' => ['English', 'Urdu', 'Arabic', 'French', 'Spanish', 'German', 'Chinese', 'Japanese', 'Other'],
            'Sport Type' => ['Cricket', 'Football', 'Tennis', 'Badminton', 'Swimming', 'Gym', 'Yoga', 'Running', 'Cycling', 'Basketball', 'Volleyball', 'Other'],
            'Skill Level' => ['Beginner', 'Intermediate', 'Advanced', 'Professional'],
            
            // Kids related
            'Age Range' => ['0-6 months', '6-12 months', '1-2 years', '3-5 years', '6-8 years', '9-12 years', '12+ years'],
        ];
        
        foreach ($attributes as $attribute) {
            $position = 1;
            
            // Check if this attribute has predefined options
            if (isset($optionsByAttribute[$attribute->name])) {
                foreach ($optionsByAttribute[$attribute->name] as $optionValue) {
                    AttributeOption::create([
                        'category_attribute_id' => $attribute->id,
                        'value' => $optionValue,
                        'position' => $position++,
                    ]);
                }
            }
            
            // Add boolean options for boolean type attributes
            if ($attribute->type === 'boolean') {
                AttributeOption::create([
                    'category_attribute_id' => $attribute->id,
                    'value' => 'Yes',
                    'position' => 1,
                ]);
                AttributeOption::create([
                    'category_attribute_id' => $attribute->id,
                    'value' => 'No',
                    'position' => 2,
                ]);
            }
        }
    }
}