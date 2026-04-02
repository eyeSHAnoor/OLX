<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\BrandModel;

class BrandModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define models for each brand
        $modelsByBrand = [
            // Mobile Brands
            'Apple' => ['iPhone 12', 'iPhone 13', 'iPhone 14', 'iPhone 15', 'iPhone 16', 'iPhone SE', 'iPhone 12 Pro', 'iPhone 13 Pro', 'iPhone 14 Pro', 'iPhone 15 Pro'],
            'Samsung' => ['Galaxy S21', 'Galaxy S22', 'Galaxy S23', 'Galaxy S24', 'Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy Note 20', 'Galaxy Z Fold', 'Galaxy Z Flip'],
            'Xiaomi' => ['Redmi Note 10', 'Redmi Note 11', 'Redmi Note 12', 'Redmi Note 13', 'Mi 11', 'Mi 12', 'Mi 13', 'Poco X3', 'Poco X4', 'Poco X5'],
            'Oppo' => ['Reno 6', 'Reno 7', 'Reno 8', 'Reno 9', 'Reno 10', 'Find X3', 'Find X5', 'A16', 'A17', 'A18'],
            'Vivo' => ['V21', 'V23', 'V25', 'V27', 'V29', 'Y20', 'Y21', 'Y22', 'Y35', 'X80'],
            'Realme' => ['Realme 8', 'Realme 9', 'Realme 10', 'Realme 11', 'Realme 12', 'Narzo 50', 'Narzo 60', 'GT Neo', 'GT 2', 'C55'],
            'OnePlus' => ['OnePlus 9', 'OnePlus 10', 'OnePlus 11', 'OnePlus 12', 'Nord 2', 'Nord 3', 'Nord CE', 'Nord N20', '10 Pro', '11 Pro'],
            'Google' => ['Pixel 6', 'Pixel 7', 'Pixel 8', 'Pixel 9', 'Pixel 6 Pro', 'Pixel 7 Pro', 'Pixel 8 Pro', 'Pixel 6a', 'Pixel 7a', 'Pixel 8a'],
            'Motorola' => ['Moto G', 'Moto G Power', 'Moto G Stylus', 'Moto E', 'Moto Edge', 'Moto Razr', 'Moto G52', 'Moto G62', 'Moto G82', 'Moto G200'],
            'Nokia' => ['Nokia G10', 'Nokia G20', 'Nokia G21', 'Nokia G22', 'Nokia X10', 'Nokia X20', 'Nokia C10', 'Nokia C20', 'Nokia C21', 'Nokia C22'],
            'Huawei' => ['P40', 'P50', 'P60', 'Mate 40', 'Mate 50', 'Nova 9', 'Nova 10', 'Nova 11', 'Y9a', 'Y9s'],
            'Sony' => ['Xperia 1', 'Xperia 5', 'Xperia 10', 'Xperia Pro', 'Xperia 1 IV', 'Xperia 5 IV', 'Xperia 10 IV', 'Xperia 1 V', 'Xperia 5 V'],
            'LG' => ['LG Wing', 'LG Velvet', 'LG K92', 'LG K61', 'LG G8', 'LG V60', 'LG Stylo', 'LG Q92', 'LG K52', 'LG K42'],
            'Asus' => ['Zenfone 8', 'Zenfone 9', 'Zenfone 10', 'ROG Phone 5', 'ROG Phone 6', 'ROG Phone 7', 'Zenfone Max', 'Zenfone Live', 'ROG Phone 8'],
            'Infinix' => ['Hot 10', 'Hot 11', 'Hot 12', 'Hot 13', 'Note 10', 'Note 11', 'Note 12', 'Note 13', 'Smart 6', 'Smart 7'],
            'Tecno' => ['Spark 7', 'Spark 8', 'Spark 9', 'Spark 10', 'Camon 17', 'Camon 18', 'Camon 19', 'Camon 20', 'Pova 3', 'Pova 4'],
            'Itel' => ['Itel A16', 'Itel A23', 'Itel A25', 'Itel A26', 'Itel A27', 'Itel A48', 'Itel A49', 'Itel A56', 'Itel A58', 'Itel S18'],
            'Honor' => ['Honor X7', 'Honor X8', 'Honor X9', 'Honor 70', 'Honor 80', 'Honor 90', 'Honor Magic 4', 'Honor Magic 5', 'Honor Pad X8', 'Honor Pad X9'],

            // Car Brands
            'Toyota' => ['Corolla', 'Camry', 'Yaris', 'Land Cruiser', 'Prado', 'Fortuner', 'Hilux', 'Revo', 'Aqua', 'Prius', 'Vitz', 'Passo'],
            'Honda' => ['Civic', 'Accord', 'City', 'CR-V', 'HR-V', 'Fit', 'Vezel', 'Stepwgn', 'Odyssey', 'Pilot', 'Ridgeline'],
            'Suzuki' => ['Alto', 'Wagon R', 'Swift', 'Cultus', 'Every', 'Bolan', 'Ravi', 'Jimny', 'Vitara', 'Ciaz', 'Ignis'],
            'BMW' => ['3 Series', '5 Series', '7 Series', 'X1', 'X3', 'X5', 'X7', 'M3', 'M5', 'i4', 'i7', 'Z4'],
            'Mercedes-Benz' => ['C-Class', 'E-Class', 'S-Class', 'GLA', 'GLC', 'GLE', 'GLS', 'A-Class', 'CLA', 'EQS', 'EQE'],
            'Audi' => ['A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q3', 'Q5', 'Q7', 'Q8', 'e-tron', 'R8'],
            'Volkswagen' => ['Golf', 'Passat', 'Jetta', 'Tiguan', 'Atlas', 'Polo', 'Beetle', 'Arteon', 'ID.4', 'ID.6'],
            'Ford' => ['Mustang', 'Focus', 'Fusion', 'F-150', 'Ranger', 'Explorer', 'Escape', 'Edge', 'Bronco', 'Maverick'],
            'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Kona', 'Palisade', 'Accent', 'Venue', 'Ioniq', 'Nexo'],
            'Kia' => ['Sportage', 'Sorento', 'Telluride', 'K5', 'Seltos', 'Soul', 'Forte', 'Rio', 'Carnival', 'EV6'],
            'Nissan' => ['Altima', 'Maxima', 'Sentra', 'Versa', 'Rogue', 'Pathfinder', 'Murano', 'Frontier', 'Titan', 'Leaf'],
            'Mitsubishi' => ['Outlander', 'Pajero', 'Montero', 'Lancer', 'Mirage', 'Eclipse Cross', 'ASX', 'Triton', 'Delica', 'Xpander'],
            'Chevrolet' => ['Silverado', 'Malibu', 'Camaro', 'Equinox', 'Traverse', 'Tahoe', 'Suburban', 'Spark', 'Cruze', 'Colorado'],
            'Daihatsu' => ['Mira', 'Move', 'Tanto', 'Copen', 'Terios', 'Xenia', 'Sirion', 'Boon', 'Cast', 'Wake'],
            'Lexus' => ['ES', 'IS', 'LS', 'RX', 'NX', 'GX', 'LX', 'UX', 'LC', 'RC'],
            'Porsche' => ['911', 'Cayenne', 'Macan', 'Panamera', 'Taycan', 'Cayman', 'Boxster', '718'],
            
            // Accessories Brands
            'Anker' => ['PowerCore', 'Soundcore', 'Nano', 'PowerLine', 'Charger', 'Slim', 'Neo', 'MagGo', 'Prime', 'Nebula'],
            'Belkin' => ['BoostCharge', 'SoundForm', 'Connect', 'Valet', 'Dual', 'Car Charger', 'Cable', 'Mount', 'Earbuds', 'Surge Protector'],
            'Baseus' => ['Bowie', 'GaN', 'Magnetic', 'Mini', 'Super', 'Car Charger', 'Cable', 'Hub', 'Fast Charging', 'Wireless'],
            'UGREEN' => ['Charger', 'Cable', 'Hub', 'Adapter', 'Dock', 'KVM', 'Bluetooth', 'Ethernet', 'Magnetic', 'Power Bank'],
            'JBL' => ['Flip', 'Charge', 'Go', 'Clip', 'Boom', 'Endurance', 'Live', 'Tune', 'Quantum', 'PartyBox'],
            'Spigen' => ['Case', 'Screen Protector', 'Armor', 'Liquid Air', 'Ultra Hybrid', 'Rugged', 'Slim', 'Tough', 'MagSafe', 'Wallet'],
            'OtterBox' => ['Defender', 'Commuter', 'Symmetry', 'Strada', 'Popsocket', 'Screen Protector', 'Case', 'Cover', 'Pursuit', 'Prefix'],
            
            // Electronics Brands
            'Dell' => ['XPS', 'Inspiron', 'Latitude', 'Precision', 'Vostro', 'OptiPlex', 'Alienware', 'G Series', 'PowerEdge', 'Dell G3'],
            'HP' => ['Spectre', 'Envy', 'Pavilion', 'EliteBook', 'ProBook', 'Omen', 'Victus', 'ZBook', 'Chromebook', 'Stream'],
            'Lenovo' => ['ThinkPad', 'IdeaPad', 'Yoga', 'Legion', 'ThinkBook', 'Chromebook', 'IdeaCentre', 'ThinkCentre', 'Tab', 'Legion Go'],
            'Acer' => ['Aspire', 'Swift', 'Predator', 'Nitro', 'Chromebook', 'Spin', 'TravelMate', 'ConceptD', 'Veriton', 'Acer One'],
            'Microsoft' => ['Surface Pro', 'Surface Laptop', 'Surface Go', 'Surface Book', 'Surface Studio', 'Xbox', 'Surface Duo', 'Surface Headphones'],
            'Logitech' => ['MX Master', 'G Series', 'C920', 'K400', 'Logitech G', 'Zone', 'MeetUp', 'Rally', 'StreamCam', 'Brio'],
            'Razer' => ['Blade', 'BlackWidow', 'DeathAdder', 'Kraken', 'Viper', 'Huntsman', 'Barracuda', 'Naga', 'Basilisk', 'Orbweaver'],
            'Corsair' => ['K70', 'M65', 'Void', 'Corsair One', 'AIO Cooler', 'RGB', 'DOMINATOR', 'VENGEANCE', 'Force', 'Crystal Series'],
            
            // Camera Brands
            'Canon' => ['EOS R5', 'EOS R6', 'EOS R7', 'EOS R8', 'EOS R50', 'EOS 90D', 'EOS 2000D', 'PowerShot G7', 'PowerShot SX70', 'EOS M50'],
            'Nikon' => ['Z9', 'Z8', 'Z7 II', 'Z6 II', 'Z5', 'Z fc', 'Z30', 'D850', 'D780', 'D500'],
            'Fujifilm' => ['X-T5', 'X-T4', 'X-H2', 'X-S20', 'X100V', 'GFX100', 'GFX50S', 'X-E4', 'X-Pro3', 'X-T30'],
            'GoPro' => ['Hero 12', 'Hero 11', 'Hero 10', 'Hero 9', 'Hero 8', 'Hero Black', 'MAX', 'Session', 'Bones', 'Volta'],
            'DJI' => ['Mavic 3', 'Air 3', 'Mini 4 Pro', 'Avata', 'Inspire 3', 'Osmo', 'Ronin', 'Osmo Pocket', 'Osmo Mobile', 'FPV'],
            
            // Fashion Brands
            'Nike' => ['Air Max', 'Air Force', 'Dunk', 'Jordan', 'Blazer', 'Cortez', 'Pegasus', 'VaporMax', 'Metcon', 'React'],
            'Adidas' => ['Ultraboost', 'NMD', 'Superstar', 'Stan Smith', 'Yeezy', 'Samba', 'Gazelle', 'Predator', 'Campus', 'Forum'],
            'Zara' => ['Jacket', 'Blazer', 'Jeans', 'Dress', 'Shirt', 'T-Shirt', 'Sweater', 'Coat', 'Trousers', 'Skirt'],
            'H&M' => ['Hoodie', 'Sweater', 'Jeans', 'Blazer', 'Shirt', 'Dress', 'Coat', 'Trousers', 'Jacket', 'Cardigan'],
            'Levi\'s' => ['501', '511', '512', '513', '514', '541', '721', 'Jeans', 'Trucker Jacket', 'Denim Jacket'],
            'Puma' => ['Suede', 'RS-X', 'Clyde', 'Future', 'Fusion', 'Ultra', 'Magnify', 'Velocity', 'Disc', 'Leadcat'],
            'Tommy Hilfiger' => ['Hoodie', 'Polo', 'Shirt', 'Jacket', 'Jeans', 'Sweater', 'T-Shirt', 'Chinos', 'Coat', 'Blazer'],
            'Calvin Klein' => ['Jeans', 'Shirt', 'Jacket', 'Sweater', 'T-Shirt', 'Underwear', 'Polo', 'Blazer', 'Coat', 'Dress'],
            'Rolex' => ['Submariner', 'Daytona', 'Datejust', 'GMT-Master', 'Explorer', 'Yacht-Master', 'Sky-Dweller', 'Oyster', 'Milgauss', 'Air-King'],
            'Omega' => ['Seamaster', 'Speedmaster', 'Constellation', 'De Ville', 'Aqua Terra', 'Planet Ocean', 'Railmaster', 'Globemaster'],
            'Casio' => ['G-Shock', 'Edifice', 'Baby-G', 'Pro Trek', 'Vintage', 'Sheen', 'Oceanus', 'Databank', 'Watches'],
            'Seiko' => ['Prospex', 'Presage', 'Astron', '5 Sports', 'Grand Seiko', 'King Seiko', 'Coutura', 'Solar'],
            
            // Furniture Brands
            'IKEA' => ['Kallax', 'Billy', 'Malm', 'Lack', 'Hemnes', 'Poang', 'Alex', 'Ektorp', 'Kivik', 'Ivar', 'Bestå', 'Fjällbo'],
            'Ashley' => ['Pallen', 'Bolburg', 'Larkinhurst', 'Alenya', 'Gareld', 'Bolanburg', 'Haddigan', 'Kierland', 'Milo', 'Aldwin'],
            'Herman Miller' => ['Aeron', 'Embody', 'Sayl', 'Mirra', 'Eames', 'Noguchi', 'Nelson', 'Marshmallow', 'Diamond', 'Coconut'],
            'Steelcase' => ['Leap', 'Gesture', 'Think', 'Series 1', 'Series 2', 'Please', 'Amia', 'Criterion', 'Karman', 'Migration'],
            
            // Pet Brands
            'Royal Canin' => ['Maxi', 'Mini', 'Medium', 'Giant', 'Kitten', 'Puppy', 'Indoor', 'Sterilised', 'Veterinary', 'Breed Specific'],
            'Hill\'s Science Diet' => ['Adult', 'Puppy', 'Kitten', 'Senior', 'Indoor', 'Light', 'Sensitive', 'Dental', 'Perfect Weight', 'Metabolic'],
            'Purina' => ['One', 'Pro Plan', 'Friskies', 'Purina Dog Chow', 'Beneful', 'Beyond', 'Tidy Cats', 'Fancy Feast', 'Cat Chow'],
            'Pedigree' => ['Adult', 'Puppy', 'Senior', 'DentaStix', 'Chopped', 'Meaty', 'Pouches', 'Complete', 'Pal', 'Cesar'],
            'Whiskas' => ['Adult', 'Kitten', 'Senior', 'Pouches', 'Dry', 'Jelly', 'Gravy', 'Slices', 'Tuna', 'Chicken'],
        ];

        foreach ($modelsByBrand as $brandName => $models) {
            // Find the brand
            $brand = Brand::where('name', $brandName)->first();
            
            if ($brand) {
                foreach ($models as $index => $modelName) {
                    // Create or update model
                    BrandModel::updateOrCreate(
                        [
                            'brand_id' => $brand->id,
                            'name' => $modelName
                        ],
                        [
                            'brand_id' => $brand->id,
                            'name' => $modelName
                        ]
                    );
                }
                
                $this->command->info("Created " . count($models) . " models for brand: {$brandName}");
            } else {
                $this->command->warn("Brand not found: {$brandName}");
            }
        }
        
        $this->command->info("BrandModelSeeder completed successfully!");
    }
}