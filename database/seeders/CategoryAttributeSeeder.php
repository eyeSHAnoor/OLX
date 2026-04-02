<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\AttributeGroup;
use App\Models\CategoryAttribute;

class CategoryAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories
        $categories = Category::all();
        
        // Get attribute groups
        $generalGroup = AttributeGroup::firstOrCreate(['name' => 'General Specifications']);
        $physicalGroup = AttributeGroup::firstOrCreate(['name' => 'Physical Characteristics']);
        $technicalGroup = AttributeGroup::firstOrCreate(['name' => 'Technical Details']);
        $performanceGroup = AttributeGroup::firstOrCreate(['name' => 'Performance']);
        $connectivityGroup = AttributeGroup::firstOrCreate(['name' => 'Connectivity']);
        $conditionGroup = AttributeGroup::firstOrCreate(['name' => 'Condition']);
        $propertyGroup = AttributeGroup::firstOrCreate(['name' => 'Property Details']);
        $jobGroup = AttributeGroup::firstOrCreate(['name' => 'Job Details']);
        $serviceGroup = AttributeGroup::firstOrCreate(['name' => 'Service Details']);
        $animalGroup = AttributeGroup::firstOrCreate(['name' => 'Animal Details']);
        $furnitureGroup = AttributeGroup::firstOrCreate(['name' => 'Furniture Details']);
        $fashionGroup = AttributeGroup::firstOrCreate(['name' => 'Fashion Details']);
        $bookGroup = AttributeGroup::firstOrCreate(['name' => 'Book & Hobby Details']);
        $kidsGroup = AttributeGroup::firstOrCreate(['name' => 'Kids Items Details']);
        $businessGroup = AttributeGroup::firstOrCreate(['name' => 'Business Details']);
        
        // Define attributes for different category types
        $attributesByCategory = [
            'Mobiles' => [
                ['name' => 'Storage Capacity', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'RAM', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Battery Capacity (mAh)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Rear Camera (MP)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Front Camera (MP)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Screen Size (inches)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Screen Resolution', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Operating System', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Processor', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'SIM Type', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'connectivity'],
                ['name' => 'Network', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'connectivity'],
                ['name' => 'Warranty', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
                ['name' => 'Box Contents', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
            ],
            'Vehicles' => [
                ['name' => 'Year', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Mileage (km)', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'performance'],
                ['name' => 'Engine Capacity (cc)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Transmission', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Fuel Type', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Registration City', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Number of Owners', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Assembly', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Body Type', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Seating Capacity', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Drivetrain', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Insurance Expiry', 'type' => 'date', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
                ['name' => 'Registration Expiry', 'type' => 'date', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
            ],
            'Property for Sale' => [
                ['name' => 'Property Type', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Area (sq ft)', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Bedrooms', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Bathrooms', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Location', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Purpose', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Furnished', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Parking', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Construction Year', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Total Floors', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Property On', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Possession', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
            ],
            'Property for Rent' => [
                ['name' => 'Property Type', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Area (sq ft)', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Bedrooms', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Bathrooms', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Location', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Furnished', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Parking', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'property'],
                ['name' => 'Security Deposit', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
                ['name' => 'Rental Duration', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Utilities Included', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
            ],
            'Electronics & Home Appliances' => [
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Power Consumption (Watts)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Warranty (months)', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
                ['name' => 'Brand', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Model', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Energy Rating', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'performance'],
                ['name' => 'Size/Dimensions', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'physical'],
                ['name' => 'Smart Features', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Remote Control', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
            ],
            'Bikes' => [
                ['name' => 'Year', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Mileage (km)', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'performance'],
                ['name' => 'Engine Capacity (cc)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Transmission', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'technical'],
                ['name' => 'Bike Type', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Registration City', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
            ],
            'Business, Industrial & Agriculture' => [
                ['name' => 'Business Type', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'business'],
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Year Established', 'type' => 'select', 'is_required' => false, 'is_filterable' => false, 'group' => 'general'],
                ['name' => 'Annual Revenue', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'business'],
                ['name' => 'Number of Employees', 'type' => 'select', 'is_required' => false, 'is_filterable' => false, 'group' => 'business'],
                ['name' => 'Reason for Selling', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'business'],
                ['name' => 'Machinery Age', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Power Requirement', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'technical'],
            ],
            'Services' => [
                ['name' => 'Service Type', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'service'],
                ['name' => 'Experience (years)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Service Area', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Response Time', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'service'],
                ['name' => 'Warranty on Work', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => false, 'group' => 'service'],
                ['name' => 'Free Estimate', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'service'],
                ['name' => 'Emergency Service', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'service'],
            ],
            'Jobs' => [
                ['name' => 'Job Type', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'job'],
                ['name' => 'Experience Required (years)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'job'],
                ['name' => 'Education Required', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'job'],
                ['name' => 'Shift Type', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'job'],
                ['name' => 'Gender Preference', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'job'],
                ['name' => 'Work Location', 'type' => 'text', 'is_required' => true, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Number of Vacancies', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'job'],
                ['name' => 'Urgent Hiring', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'job'],
            ],
            'Animals' => [
                ['name' => 'Animal Type', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'animal'],
                ['name' => 'Breed', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'animal'],
                ['name' => 'Age (months/years)', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'animal'],
                ['name' => 'Gender', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'animal'],
                ['name' => 'Vaccinated', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'animal'],
                ['name' => 'Dewormed', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'animal'],
                ['name' => 'Neutered', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'animal'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Microchipped', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => false, 'group' => 'animal'],
            ],
            'Furniture & Home Decor' => [
                ['name' => 'Material', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'furniture'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Dimensions (LxWxH)', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'physical'],
                ['name' => 'Assembly Required', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => false, 'group' => 'furniture'],
                ['name' => 'Weight Capacity (kg)', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'furniture'],
                ['name' => 'Style', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'furniture'],
                ['name' => 'Room Type', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'furniture'],
            ],
            'Fashion & Beauty' => [
                ['name' => 'Size', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'fashion'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Material', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'fashion'],
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Gender', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'fashion'],
                ['name' => 'Brand', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Season', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'fashion'],
                ['name' => 'Occasion', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'fashion'],
                ['name' => 'Fabric Type', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'fashion'],
            ],
            'Books, Sports & Hobbies' => [
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Author/Publisher', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'book'],
                ['name' => 'Edition', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'book'],
                ['name' => 'Language', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'book'],
                ['name' => 'Sport Type', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Brand', 'type' => 'text', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
                ['name' => 'Size', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Skill Level', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'general'],
            ],
            'Kids' => [
                ['name' => 'Age Range', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'kids'],
                ['name' => 'Condition', 'type' => 'select', 'is_required' => true, 'is_filterable' => true, 'group' => 'condition'],
                ['name' => 'Color', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'physical'],
                ['name' => 'Material', 'type' => 'text', 'is_required' => false, 'is_filterable' => false, 'group' => 'kids'],
                ['name' => 'Gender', 'type' => 'select', 'is_required' => false, 'is_filterable' => true, 'group' => 'kids'],
                ['name' => 'Battery Required', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => false, 'group' => 'technical'],
                ['name' => 'Safety Certified', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'kids'],
                ['name' => 'Educational', 'type' => 'boolean', 'is_required' => false, 'is_filterable' => true, 'group' => 'kids'],
            ],
        ];
        
        // Assign attributes to categories
        foreach ($categories as $category) {
            $categoryName = $category->name;
            $position = 1;
            
            // Check if this category has predefined attributes
            if (isset($attributesByCategory[$categoryName])) {
                foreach ($attributesByCategory[$categoryName] as $attributeData) {
                    // Get the appropriate attribute group
                    $group = match($attributeData['group']) {
                        'general' => $generalGroup,
                        'physical' => $physicalGroup,
                        'technical' => $technicalGroup,
                        'performance' => $performanceGroup,
                        'connectivity' => $connectivityGroup,
                        'condition' => $conditionGroup,
                        'property' => $propertyGroup,
                        'job' => $jobGroup,
                        'service' => $serviceGroup,
                        'animal' => $animalGroup,
                        'furniture' => $furnitureGroup,
                        'fashion' => $fashionGroup,
                        'book' => $bookGroup,
                        'kids' => $kidsGroup,
                        'business' => $businessGroup,
                        default => $generalGroup,
                    };
                    
                    CategoryAttribute::create([
                        'category_id' => $category->id,
                        'attribute_group_id' => $group ? $group->id : null,
                        'name' => $attributeData['name'],
                        'type' => $attributeData['type'],
                        'is_required' => $attributeData['is_required'],
                        'is_filterable' => $attributeData['is_filterable'],
                        'position' => $position++,
                    ]);
                }
            }
        }
    }
}