<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\City;
use App\Models\Region;

class RegionsSeeder extends Seeder
{
    public function run()
    {
        // Define all cities with their areas
        $citiesData = [
            'Abbottabad' => [
            'country' => 'PK',
            'areas' => [
                // Union Councils
                'Abbottabad Central', 'Bagh', 'Bagnotar', 'Bakot', 'Baldheri',
                'Banda Pir Khan', 'Beerangali', 'Berote Kalan', 'Boi', 'Chamhad',
                'Dalola', 'Dhamtour', 'Jarral', 'Jhangi', 'Kakul', 'Kehal Urban',
                'Kukmang', 'Kuthiala', 'Kuthwal', 'Malikpura Urban', 'Mirpur',
                'Nagri Bala', 'Nambal', 'Namli Maira', 'Nathiagali', 'Nawansher Urban',
                'Palak', 'Pattan Kalan', 'Pawa', 'Phalkot', 'Pind Kargu Khan',
                'Salhad', 'Sarbhana', 'Sheikh-ul-Bandi', 'Sherwan',
                // Cantonment & major localities
                'Gulistan Colony', 'Malik Pura Housing Scheme', 'Martin Line',
                'Shabbir Sharif Road', 'Pine View Road', 'Jinnah Road', 'Liaquat Road',
                'Allama Iqbal Road', 'Hill Road', 'Circular Road', 'Rehmatabad',
                'Chinnar Road', 'Jinnahabad', 'Habibullah Colony', 'Workshop Road',
                'Raza Khan Road', 'Link Road Mandian', 'Javed Shaheed Road',
                'Lower Jinnahabad', 'Phool Gulab Road', 'Al Mansoor Town', 'Gulberg Colony',
                'COMSATS Road', 'Maira Mirpur', 'Aspedar', 'Doctor Colony', 'Sir Syed Colony',
                'Rehmania Road', 'Kaghan Colony', 'Al Badar Colony', 'Jhangi',
                'Gulshan e Iqbal', 'Lambi Dheri', 'Banda Dilazak', 'Iqbal Road',
                'Sikandarabad', 'Sheikh Dheri', 'Jadoon Colony', 'Narrian Road',
                'Murree Road', 'Aziz Town', 'Sardar Colony', 'Akram Khan Colony',
                'Thanda Chowa', 'Bilal Town', 'Shahzaman Colony', 'Garga', 'Hamayun Town',
                'Hashmi Colony', 'Choona Kari', 'Kholay Choona Kari', 'Neelor Phool Dheri',
                'Hassan Town', 'Tarkanna', 'Sarban Colony', 'Niazi Colony', 'Shalimar Colony',
                'Janjua Street', 'Mohallah Zahid Abad', 'Shahzaman Town', 'Lalazar Colony',
                'Tanoli Street', 'Toheed Colony', 'Mir Alam Town', 'Mohallah Umar Farooq',
                'Main Mansehra Residential Road', 'Maira Muzzafar', 'Usmanabad', 'Mughal Town',
                'Manshera Road', 'Kakul Road', 'Nathiagali Road', 'Shinkiari Road'
            ]
        ],
        'Adilpur' => [
            'country' => 'PK',
            'areas' => [
                'Adilpur Town', 'UC Adilpur 1', 'UC Adilpur 2', 'Bangla Ichar',
                'Chak 4', 'Chak 5', 'Chak 6', 'Chak 7', 'Goth Haji Khan', 'Goth Murid',
                'Jalalpur', 'Khanpur Mahar', 'Miani', 'Muhammadpur', 'Nurpur', 'Pir Bux',
                'Rahimabad', 'Sadiqabad', 'Salehpat', 'Sathoi', 'Shamspur', 'Tando Masti',
                'Adilpur Cantt', 'Gulshan-e-Adil', 'Madina Colony Adilpur', 'Peoples Colony Adilpur',
                'Jinnah Colony Adilpur', 'Adilpur Bazaar', 'Railway Colony', 'Mohallah Khaskheli'
            ]
        ],
        'Ahmadpur East' => [
            'country' => 'PK',
            'areas' => [
                // Urban areas
                'Model Town', 'Satellite Town', 'Railway Colony', 'Kachehri Bazar',
                'Muhammadpura', 'Fateh Khan Bazar', 'Chah Mubarak', 'Shadman Colony',
                'Ghalla Mandi', 'Ahmad Nagar', 'Islam Nagar', 'Jamia Colony',
                // Union Councils (rural)
                'UC 1 (City)', 'UC 2 (Fazilpur)', 'UC 3 (Jalalpur Pirwala)',
                'UC 4 (Bait Qaimwala)', 'UC 5 (Chak 25/F)', 'UC 6 (Chak 32/F)',
                'UC 7 (Dera Nawab Sahib)', 'UC 8 (Mari Kalan)', 'UC 9 (Chak 41/F)',
                'UC 10 (Basti Maluk)', 'UC 11 (Qaimpur)', 'UC 12 (Chak 56/F)',
                'UC 13 (Chak 73/F)', 'UC 14 (Rukanpur)', 'UC 15 (Fatehpur)',
                'Ahmadpur East Cantt', 'Gulshan-e-Ahmad', 'Peoples Colony', 'Jinnah Colony',
                'Chak 17/F', 'Chak 18/F', 'Basti Bahadur', 'Basti Khokhar'
            ]
        ],
        'Ahmadpur Sial' => [
            'country' => 'PK',
            'areas' => [
                'Ahmadpur Sial City', 'Mandi Town', 'Chak 37 JB', 'Chak 38 JB',
                'Chak 39 JB', 'Chak 40 JB', 'Basti Jhandir', 'Basti Ghulam Muhammad',
                'Basti Khichian', 'Basti Sandilian', 'Mohallah Qazian', 'Mohallah Arain',
                'Mohallah Gujran', 'Mohallah Shaikhian', 'Peer Colony', 'Gulshan-e-Madina',
                'Allama Iqbal Colony', 'Muhammad Nagar', 'Jinnahabad', 'Islamia Colony',
                'Ahmadpur Sial Cantt', 'Railway Road', 'Kachehri Bazar', 'Chak 41 JB',
                'Basti Kharal', 'Kot Ahmad', 'Model Town', 'Satellite Town'
            ]
        ],
        'Akora' => [
            'country' => 'PK',
            'areas' => [
                'Akora Khattak', 'Khattak Colony', 'Cantt Area', 'Ghazi Colony',
                'Muhammadzai', 'Gulshan Abad', 'Fazalabad', 'Waliabad', 'Khanabad',
                'Mian Essa Colony', 'Tajabad', 'Zakori', 'Hakimabad', 'Mera Akora',
                'Sher Garh', 'Lalozai', 'Khasha Khel', 'Painda Khel', 'Manki',
                'Jalbai', 'Nizampur', 'Pabbi (part)', 'Misri Banda', 'Shahbaz Garhi',
                'Akora Bazaar', 'Jinnah Colony Akora', 'Madina Colony', 'Peoples Colony',
                'Akora Railway Station', 'GT Road Akora', 'Cantt Bazar'
            ]
        ],
        'Aliabad' => [
            'country' => 'PK',
            'areas' => [
                'Aliabad Town', 'Karimabad', 'Ganish', 'Murtazaabad', 'Altit',
                'Gulmit', 'Hussainabad', 'Nasirabad', 'Hyderabad (Hunza)', 'Shiskat',
                'Ghulkin', 'Gulkin', 'Passu', 'Sost', 'Khizerabad', 'Central Hunza',
                'Gojal Valley', 'Shimshal', 'Aliabad Bazar', 'Cantt Area', 'KKH Aliabad',
                'Basti Ali', 'Rahmanabad', 'Aminabad', 'Jamalabad', 'Eagle Nest Area'
            ]
        ],
        'Alik Ghund' => [
            'country' => 'PK',
            'areas' => [
                'Alik Ghund Town', 'Kuchlak', 'Samungli', 'Killi Alizai', 'Killi Abdul Rehman',
                'Killi Malik Nader', 'Killi Haji Khan', 'Killi Mulla Abdullah', 'Killi Sahib Khan',
                'Killi Qasim', 'Killi Zarghoon', 'Killi Khudaidad', 'Sariab', 'Spin Karez',
                'Kuchlagh', 'Surkhab', 'Alik Ghund Cantt', 'Basti Alik', 'Killi Kakar',
                'Quetta Road Alik', 'Gulshan-e-Alik', 'Madina Colony', 'Peoples Colony'
            ]
        ],
        'Alipur' => [
            'country' => 'PK',
            'areas' => [
                'Alipur City', 'Alipur Saddar', 'Basti Arain', 'Basti Barath', 'Basti Bahadur',
                'Basti Darakhan', 'Basti Dina', 'Basti Ghulam Shah', 'Basti Jalla', 'Basti Kharal',
                'Basti Khoja', 'Basti Langah', 'Basti Masti', 'Basti Mughla', 'Basti Pathana',
                'Basti Rajputan', 'Chak 1', 'Chak 2', 'Chak 3', 'Chak 4', 'Kot Qutab',
                'Mianwali Qureshian', 'Ruknpur', 'Shaheedabad', 'Tarkhanwala', 'Alipur Cantt',
                'Model Town', 'Satellite Town', 'Gulshan-e-Alipur', 'Peoples Colony', 'Jinnah Colony',
                'Railway Road', 'Kachehri Bazar', 'Chak 5', 'Basti Khokhar', 'Kot Alipur'
            ]
        ],
        'Alizai' => [
            'country' => 'PK',
            'areas' => [
                'Alizai Bazar', 'Pewar', 'Sadda', 'Karman', 'Sheraki', 'Mandozai', 'Tari Mangal',
                'Ghozgari', 'Badshah Kot', 'Maqbal', 'Kunj Alizai', 'Kot Ragha', 'Dwa Toi',
                'Khewa', 'Shingak', 'Spina Shaga', 'Tangi', 'Tor Tarkhobi', 'Alizai City',
                'Cantt Area', 'Killi Alizai', 'Basti Alizai', 'Kurram Road', 'Gulshan-e-Alizai'
            ]
        ],
        'Alpurai' => [
            'country' => 'PK',
            'areas' => [
                'Alpurai Bazar', 'Shangla Top', 'Dandai', 'Chakesar', 'Chamkani', 'Pirkot',
                'Shahpur', 'Kandar', 'Guniyal', 'Dagai', 'Martung', 'Naway Kali', 'Kuz Kali',
                'Gidarpata', 'Kana', 'Lilownai', 'Makhuzai', 'Tarkana', 'Puran', 'Karak',
                'Alpurai City', 'Cantt Area', 'Basti Alpurai', 'Shangla Road', 'Gulshan-e-Alpurai'
            ]
        ],
        'Aman Garh' => [
            'country' => 'PK',
            'areas' => [
                'Aman Garh Town', 'Shabqadar', 'Mirza Khel', 'Sherpao', 'Umarzai',
                'Tangi (part)', 'Turangzai', 'Agra', 'Banda', 'Batagram', 'Dargai',
                'Ghund Dheri', 'Hajizai', 'Harichand', 'Kati Garh', 'Khwazakhela',
                'Miana Banda', 'Pirdil', 'Qaziabad', 'Risalpur (outskirts)', 'Aman Garh Cantt',
                'Gulshan-e-Aman', 'Madina Colony', 'Peoples Colony', 'Charsadda Road'
            ]
        ],
        'Amirabad' => [
            'country' => 'PK',
            'areas' => [
                'Amirabad City', 'Mirpur', 'Nawansher', 'Havelian (part)', 'Dhamtour',
                'Jhangi', 'Bagnotar', 'Salhad', 'Kala Pul', 'Mandian', 'Kotli Bagh',
                'Mohallah Khawaja', 'Ghazi Colony', 'Gulshan-e-Amirabad', 'Iqbal Colony',
                'Rehman Colony', 'Shahzad Town', 'Usmanabad', 'Malikabad', 'Amirabad Cantt',
                'Abbottabad Road', 'Jinnahabad', 'Sardar Colony', 'Railway Colony'
            ]
        ],
        'Arifwala' => [
            'country' => 'PK',
            'areas' => [
                'Arifwala City', 'Arifwala Cantt', 'Basti Allahabad', 'Basti Bahadur',
                'Basti Danishmand', 'Basti Dinpur', 'Basti Haibat', 'Basti Hamza',
                'Basti Jalalabad', 'Basti Jandoke', 'Basti Khichian', 'Basti Malook',
                'Basti Mithu', 'Basti Noon', 'Basti Qutab', 'Chak 43', 'Chak 44',
                'Chak 45', 'Chak 46', 'Chak 47', 'Faqirwali', 'Gandi Khokhar',
                'Khichian', 'Lal Khanwala', 'Machhianwala', 'Mian Sahib', 'Shahbazpur',
                'Tahli', 'Tinda', 'Model Town', 'Satellite Town', 'Peoples Colony',
                'Gulshan-e-Arifwala', 'Railway Road', 'Kachehri Bazar'
            ]
        ],
        'Ashanagro Koto' => [
            'country' => 'PK',
            'areas' => [
                'Ashanagro', 'Koto', 'Batagram', 'Banna', 'Sandasar', 'Allai', 'Shumlai',
                'Kuza Banda', 'Nika Banda', 'Gijbori', 'Ajmera', 'Jabori', 'Rashang',
                'Thakot', 'Shingli', 'Banian', 'Tandu', 'Pashto', 'Maira', 'Chappar',
                'Ashanagro Bazar', 'Koto Cantt', 'Gulshan-e-Ashanagro', 'Basti Khan'
            ]
        ],
        'Athmuqam' => [
            'country' => 'PK',
            'areas' => [
                'Athmuqam Town', 'Neelum Bazar', 'Kel', 'Sharda', 'Taobat', 'Janawai',
                'Surgan', 'Ratti Gali', 'Keran', 'Jura', 'Nambal', 'Halmat', 'Chita Katha',
                'Lawat', 'Salkhala', 'Khurshidabad', 'Dudhnial', 'Phulawai', 'Noori Top',
                'Dawarian', 'Athmuqam Cantt', 'Neelum Road', 'Jhelum Valley Road', 'Basti Athmuqam'
            ]
        ],
        'Attock City' => [
            'country' => 'PK',
            'areas' => [
                'Attock Khurd', 'Attock Cantt', 'Hasan Abdal', 'Kamra', 'Hazro', 'Jand',
                'Sanjwal', 'Fateh Jang', 'Pindi Gheb', 'Basal', 'Makhad', 'Kot Fateh Khan',
                'Dhoun Kal', 'Ghazi', 'Shadi Khan', 'Sra Alamgir', 'Malho', 'Jhangi',
                'Lakhwal', 'Kamrial', 'Pindsultani', 'Chhab', 'Adal', 'Tajak', 'Gondal',
                'Chhachh (region)', 'Thatta (Attock)', 'Attock City Area', 'Railway Road',
                'Gulshan-e-Attock', 'Madina Colony', 'Peoples Colony', 'Satellite Town',
                'Jinnah Colony', 'GT Road Attock', 'Attock Fort Area'
            ]
        ],
        'Awaran' => [
            'country' => 'PK',
            'areas' => [
                'Awaran Town', 'Mashkay', 'Gishkore', 'Jhal Jhao', 'Kech', 'Parwar',
                'Dasht', 'Gajar', 'Kharkharan', 'Sohrag', 'Kolwa', 'Kalag', 'Nal',
                'Jhao', 'Panjgur (nearby)', 'Chaman (rural)', 'Dand', 'Kardagap',
                'Awaran Cantt', 'Awaran Bazaar', 'Killi Awaran', 'Basti Baloch', 'Gulshan-e-Awaran'
            ]
        ],
        'Baddomalhi' => [
            'country' => 'PK',
            'areas' => [
                'Baddomalhi City', 'Narowal (part)', 'Zafarwal (part)', 'Shakargarh (outskirts)',
                'Dhamke', 'Chhina', 'Ghuman', 'Charoya', 'Wazirke', 'Kotli Khokhar',
                'Baddo Gujran', 'Kaluwala', 'Khara', 'Mohallah Muslimabad', 'Mohallah Anwarabad',
                'Mohallah Ghaznavi', 'Gulshan-e-Baddomalhi', 'Railway Colony', 'City Colony',
                'Baddomalhi Cantt', 'Sialkot Road', 'Jinnahabad', 'Peoples Colony'
            ]
        ],
        'Badin' => [
            'country' => 'PK',
            'areas' => [
                'Badin City', 'Badin Cantt', 'Tando Bago', 'Matli', 'Golarchi', 'Kadhan',
                'Pangrio', 'Talhar', 'Nindo Shaher', 'Khoski', 'Seerani', 'Kario Ghanwar',
                'Mithi (nearby)', 'Diplo', 'Chhat', 'Ghulam Nabi Shah', 'Haji Abdullah',
                'Machi', 'Rajar', 'Sharifabad', 'Koral', 'Sheedi Colony', 'Mohalla Faqir',
                'Badin Old City', 'Badin Bazaar', 'Gulshan-e-Badin', 'Madina Colony',
                'Peoples Colony', 'Satellite Town', 'Jinnah Colony', 'Badin Bypass'
            ]
        ],
        'Baffa' => [
            'country' => 'PK',
            'areas' => [
                'Baffa Town', 'Mansehra (part)', 'Oghi (part)', 'Balakot (outskirts)',
                'Shinkiari', 'Kohala', 'Darband', 'Behali', 'Karor', 'Malikabad', 'Khaki',
                'Ghanool', 'Namli Maira', 'Saeedabad', 'Batrasi', 'Chanai', 'Garhi Habibullah (part)',
                'Tarlai', 'Lassan Nawab', 'Paswal', 'Baffa Cantt', 'Mansehra Road', 'Kaghan Road',
                'Gulshan-e-Baffa', 'Peoples Colony', 'Jinnah Colony'
            ]
        ],
        'Bagarji' => [
            'country' => 'PK',
            'areas' => [
                'Bagarji Town', 'Kot Diji', 'Khairpur (part)', 'Ranipur', 'Sobho Dero',
                'Kingri', 'Thari Mirwah', 'Pir Jo Goth', 'Faiz Ganj', 'Kumb', 'Jado',
                'Bhurgari', 'Daharki (nearby)', 'Tando Masti Khan', 'Hingoro', 'Bakhshapur',
                'Mehrabpur', 'Dalel Dero', 'Rahimabad', 'Bagarji Cantt', 'Basti Bagarji',
                'Gulshan-e-Bagarji', 'Khairpur Road'
            ]
        ],
        'Bagh' => [
            'country' => 'PK',
            'areas' => [
                'Bagh City', 'Dhirkot', 'Hari Gehal', 'Neel Butt', 'Chamyati', 'Moola',
                'Panyali', 'Birpani', 'Bagh Fort', 'Kahuta (AK)', 'Malot', 'Seri', 'Topi',
                'Sain Hakim', 'Ghambeer', 'Lassana', 'Abbaspur', 'Dewal', 'Bhati Dher', 'Arja',
                'Bagh Cantt', 'Rawalakot Road', 'Bagh Bazaar', 'Gulshan-e-Bagh', 'Peoples Colony'
            ]
        ],
        'Bahawalnagar' => [
            'country' => 'PK',
            'areas' => [
                'Bahawalnagar City', 'Bahawalnagar Cantt', 'Minchinabad', 'Chishtian',
                'Haroonabad', 'Fort Abbas', 'Faqirwali', 'Mandi Sadiqganj', 'Dunga Bunga',
                'Kot Fateh Khan', 'Basti Allahabad', 'Basti Sadiq', 'Chak 1-12 (various chaks)',
                'Mari', 'Nawan Shehr', 'Ghalla Mandi', 'Model Town', 'Satellite Town',
                'Peoples Colony', 'Stadium Road', 'Railway Road', 'Alipur', 'Rafiqabad', 'Tariqabad',
                'Gulshan-e-Bahawalnagar', 'Madina Colony', 'Jinnah Colony', 'Basti Khokhar'
            ]
        ],
        'Bahawalpur' => [
            'country' => 'PK',
            'areas' => [
                'Bahawalpur City', 'Bahawalpur Cantt', 'Satellite Town', 'Model Town A',
                'Model Town B', 'Faisal Colony', 'Gulshan-e-Iqbal', 'Gulshan-e-Hadeed',
                'Shahdrah', 'Madina Town', 'Islamia Colony', 'Peoples Colony No. 1',
                'Peoples Colony No. 2', 'Railway Colony', 'Kutchery Road', 'Circular Road',
                'Multan Road', 'Ahmedpur East Road', 'Farid Gate', 'Derawar Fort Area',
                'Uch Sharif', 'Yazman', 'Hasilpur', 'Khas Khel', 'Mouza Mubarakpur',
                'Mouza Qaimpur', 'Mouza Chak 22', 'Mouza Chak 35', 'Mouza Chak 48',
                'Bahawalpur Saddar', 'Ghalla Mandi', 'Basti Arain', 'Basti Khokhar',
                'Jinnah Colony', 'Gulshan-e-Bahawalpur', 'Airport Road'
            ]
        ],
        'Bakhri Ahmad Khan' => [
            'country' => 'PK',
            'areas' => [
                'Bakhri Ahmad Khan Town', 'Chak 47/TDA', 'Chak 48/TDA', 'Chak 49/TDA',
                'Basti Bhutta', 'Basti Sial', 'Basti Kharal', 'Basti Joiya', 'Basti Gujjar',
                'Kotla Qasim', 'Kotla Haji Shah', 'Mouza Shergarh', 'Mouza Fatehpur',
                'Mouza Jalalabad', 'Mouza Kotli', 'Shah Sadar Din', 'Jhok Pathan',
                'Bakhri Cantt', 'Layyah Road', 'Gulshan-e-Bakhri', 'Peoples Colony'
            ]
        ],
        'Bandhi' => [
            'country' => 'PK',
            'areas' => [
                'Bandhi Town', 'Bandhi Bazar', 'Bhit Shah', 'Bhan Syedabad', 'Daur',
                'Kazi Ahmad', 'Nawabshah (part)', 'Sakrand', 'Basti Ghulam Shah',
                'Basti Mangi', 'Basti Khaskheli', 'Basti Memon', 'Basti Unar',
                'Basti Solangi', 'Mouza Jalalpur', 'Mouza Khuda Bux', 'Mouza Allah Dino',
                'Bandhi Cantt', 'Gulshan-e-Bandhi', 'Madina Colony', 'Hyderabad Road'
            ]
        ],
        'Bannu' => [
            'country' => 'PK',
            'areas' => [
                'Bannu City', 'Bannu Cantt', 'Miran Shah', 'Kakki', 'Mandi Bannu',
                'Domail', 'Naurang', 'Mastikhel', 'Baka Khel', 'Janikhel', 'Hindukhel',
                'Mamash Khel', 'Baran', 'Gul Hasan Khel', 'Ahmadzai', 'Basti Khel',
                'Isakhel', 'Ali Khel', 'Mughal Khel', 'Khojari', 'Mela Mandan',
                'Ghoriwala', 'Shahbaz Khel', 'Khawaja Madin', 'Punjabi Colony',
                'Saddar Bazar', 'Jinnahabad', 'Gulshan-e-Bannu', 'Sikandarabad',
                'Bannu Bypass', 'Railway Road', 'Peoples Colony', 'Model Town'
            ]
        ],
        'Barishal' => [
            'country' => 'PK',
            'areas' => [
                'Barishal Town', 'Central Hunza', 'Karimabad (part)', 'Gulmit (part)',
                'Hussainabad', 'Ghulkin', 'Passu', 'Sost', 'Shimshal', 'Khyber',
                'Jamalabad', 'Murtazaabad', 'Nasirabad', 'Khizerabad', 'Gojal Valley',
                'Misgar', 'Chapursan', 'Borith Lake Area', 'Gulkin', 'Rahmanabad',
                'Barishal Cantt', 'KKH Barishal', 'Basti Barishal'
            ]
        ],
        'Barkhan' => [
            'country' => 'PK',
            'areas' => [
                'Barkhan Town', 'Rakhni', 'Jandran', 'Damboli', 'Saddar Barkhan',
                'Kohlu (part)', 'Mughalzai', 'Khajjak', 'Luni', 'Dhadar', 'Mithri',
                'Kot Murtaza', 'Basti Malik', 'Basti Buzdar', 'Basti Marri', 'Killi Rahim',
                'Barkhan Cantt', 'Barkhan Bazaar', 'Gulshan-e-Barkhan', 'Loralai Road'
            ]
        ],
        'Basirpur' => [
            'country' => 'PK',
            'areas' => [
                'Basirpur City', 'Depalpur (part)', 'Hujra Shah Muqeem', 'Renala Khurd',
                'Chak 37/GD', 'Chak 38/GD', 'Chak 39/GD', 'Chak 40/GD', 'Basti Arain',
                'Basti Gujjar', 'Basti Jattan', 'Basti Khokhar', 'Kot Murad', 'Kot Hidayat',
                'Mohallah Muslimabad', 'Mohallah Ghaznavi', 'Gulshan-e-Basirpur', 'Railway Colony',
                'Basirpur Cantt', 'Okara Road', 'Model Town', 'Peoples Colony'
            ]
        ],
        'Basti Dosa' => [
            'country' => 'PK',
            'areas' => [
                'Basti Dosa Town', 'Layyah (part)', 'Karor Lal Esan', 'Chak 46/TDA',
                'Chak 47/TDA', 'Basti Malik', 'Basti Dhandla', 'Basti Khichi', 'Basti Awan',
                'Basti Bhatti', 'Kotla Haji Shah', 'Mouza Ghaznavi', 'Mouza Sultanpur',
                'Jhalar', 'Nai Basti', 'Basti Dosa Cantt', 'Layyah Road', 'Gulshan-e-Dosa'
            ]
        ],
        'Bat Khela' => [
            'country' => 'PK',
            'areas' => [
                'Bat Khela City', 'Malakand (part)', 'Dargai', 'Thana', 'Palai', 'Sakhakot',
                'Alladand Dheri', 'Totakan', 'Khar', 'Kot', 'Heroshah', 'Prang Ghar',
                'Shahkot', 'Ziarat', 'Mian Khan', 'Chakdara (nearby)', 'Amandara',
                'Qasimabad', 'Gulshan Colony', 'Malikabad', 'Bat Khela Cantt', 'Malakand Road',
                'Peoples Colony', 'Jinnah Colony', 'Bat Khela Bazaar'
            ]
        ],
        'Battagram' => [
            'country' => 'PK',
            'areas' => [
                'Battagram City', 'Allai', 'Kuza Banda', 'Nika Banda', 'Rashang', 'Thakot',
                'Banna', 'Sandasar', 'Shumlai', 'Ajmera', 'Jabori', 'Gijbori', 'Banian',
                'Pashto', 'Maira', 'Chappar', 'Kotki', 'Bateela', 'Biari', 'Pheral',
                'Battagram Cantt', 'Karakoram Highway', 'Battagram Bazaar', 'Gulshan-e-Battagram'
            ]
        ],
        'Begowala' => [
            'country' => 'PK',
            'areas' => [
                'Begowala Town', 'Sialkot (part)', 'Daska (part)', 'Sambrial', 'Pasrur',
                'Kotli Loharan', 'Wanaz', 'Mianwali Bangla', 'Jinnahpura', 'Mohallah Shahbaz',
                'Mohallah Fatima', 'Kot Begowala', 'Chak 1', 'Chak 2', 'Nurpur',
                'Khawaja Colony', 'Rehman Colony', 'Begowala Cantt', 'Sialkot Road',
                'Gulshan-e-Begowala', 'Peoples Colony'
            ]
        ],
        'Bela' => [
            'country' => 'PK',
            'areas' => [
                'Bela Town', 'Uthal', 'Hub (part)', 'Lasbela', 'Sonmiani', 'Winder',
                'Khuzdar (part)', 'Kanraj', 'Dureji', 'Lakhra', 'Gondrani', 'Bagh Bela',
                'Sassi', 'Pabbi', 'Goth Haji', 'Mohallah Shahi', 'Bela Cantt Area',
                'Bela Bazaar', 'Gulshan-e-Bela', 'Karachi Road', 'Peoples Colony'
            ]
        ],
        'Berani' => [
            'country' => 'PK',
            'areas' => [
                'Berani Town', 'Sanghar (part)', 'Shahdadpur', 'Tando Adam', 'Khipro',
                'Jam Nawaz Ali', 'Sindhri', 'Pir Bux', 'Haji Khan', 'Khairpur (Nawabshah part)',
                'Basti Waryam', 'Basti Lal', 'Goth Berani', 'Mouza Mehrabpur',
                'Mouza Allah Dino', 'Mohallah Khaskheli', 'Berani Cantt', 'Berani Bazaar',
                'Gulshan-e-Berani', 'Sanghar Road'
            ]
        ],
        'Bhag' => [
            'country' => 'PK',
            'areas' => [
                'Bhag Town', 'Dhadar', 'Mach (part)', 'Sibi (part)', 'Lehri', 'Nari',
                'Khattan', 'Marri', 'Jafarabad', 'Usta Muhammad', 'Gandakha', 'Ghat',
                'Mouza Karim Bakhsh', 'Killi Haji', 'Basti Baloch', 'Bhag Saddar',
                'Bhag Cantt', 'Bhag Bazaar', 'Gulshan-e-Bhag', 'Sibi Road'
            ]
        ],
        'Bhakkar' => [
            'country' => 'PK',
            'areas' => [
                'Bhakkar City', 'Bhakkar Cantt', 'Darya Khan', 'Kalur Kot', 'Mankera',
                'Kaloorkot', 'Peplan', 'Basti Sheikh', 'Basti Awan', 'Basti Jandan',
                'Chak 46/TDA', 'Chak 47/TDA', 'Basti Haji', 'Kotla Jam', 'Jandanwala',
                'Mohallah Islamia', 'Gulshan-e-Bhakkar', 'Bhakkar Bazaar', 'Satellite Town',
                'Peoples Colony', 'Railway Road', 'Model Town'
            ]
        ],
        'Bhalwal' => [
            'country' => 'PK',
            'areas' => [
                'Bhalwal City', 'Sargodha (part)', 'Sahiwal (Sargodha)', 'Silanwali',
                'Kot Momin', 'Chak 31 NB', 'Chak 32 NB', 'Basti Peer', 'Basti Khokhar',
                'Bhalwal Saddar', 'Mohallah Arain', 'Mohallah Gujjar', 'Railway Colony',
                'Peoples Colony', 'Satellite Town', 'Bhalwal Cantt', 'Sargodha Road',
                'Gulshan-e-Bhalwal', 'Jinnah Colony', 'Model Town'
            ]
        ],
        'Bhan' => [
            'country' => 'PK',
            'areas' => [
                'Bhan Town', 'Sehwan Sharif (part)', 'Johi', 'Kotri (part)', 'Manjhand',
                'Odero Lal', 'Bagh Amiri', 'Sann', 'Basti Waryam', 'Basti Khaskheli',
                'Goth Bhan', 'Mouza Jalalpur', 'Killi Haji', 'Bhan Bazar', 'Mohallah Mughal',
                'Bhan Cantt', 'Gulshan-e-Bhan', 'Hyderabad Road', 'Peoples Colony'
            ]
        ],
        'Bhawana' => [
            'country' => 'PK',
            'areas' => [
                'Bhawana City', 'Chiniot (part)', 'Lalian', 'Rabwah (Chenab Nagar)',
                'Chak 1', 'Chak 2', 'Basti Bhatti', 'Basti Sial', 'Kot Hameed',
                'Kot Qutab', 'Mouza Jhang', 'Mouza Sultanpur', 'Mohallah Kashmirian',
                'Gulshan-e-Bhawana', 'Bhawana Cantt', 'Jhang Road', 'Peoples Colony',
                'Model Town', 'Railway Colony'
            ]
        ],
        'Bhera' => [
            'country' => 'PK',
            'areas' => [
                'Bhera City', 'Sargodha (part)', 'Bhalwal (part)', 'Sahiwal (Sargodha)',
                'Chak 28 NB', 'Chak 29 NB', 'Basti Gondal', 'Basti Bajwa', 'Kot Inayat',
                'Kot Shadman', 'Bhera Saddar', 'Mohallah Mughalpura', 'Mohallah Khokharan',
                'Old Bhera', 'Railway Road', 'Bhera Cantt', 'Gulshan-e-Bhera', 'Peoples Colony',
                'Model Town', 'Satellite Town'
            ]
        ],
        'Bhimbar' => [
            'country' => 'PK',
            'areas' => [
                'Bhimbar City', 'Samahni', 'Barnala', 'Kotli (part)', 'Mirpur (part)',
                'Nala', 'Panyam', 'Sangiot', 'Gharhi', 'Dheri', 'Sehnsa', 'Jandala',
                'Khuiratta', 'Bhimber Cantt', 'Mohallah Qadeerabad', 'Gulshan Colony',
                'Bhimbar Bazaar', 'Mirpur Road', 'Peoples Colony', 'Jinnah Colony'
            ]
        ],
        'Bhiria' => [
            'country' => 'PK',
            'areas' => [
                'Bhiria City', 'Naushahro Feroze (part)', 'Moro', 'Kandiaro', 'Mehrabpur',
                'Padidan', 'Tharushah', 'Basti Punjabi', 'Basti Solangi', 'Basti Mangi',
                'Goth Bhiria', 'Mouza Jan Muhammad', 'Killi Allahdad', 'Bhiria Bazar',
                'Bhiria Cantt', 'Gulshan-e-Bhiria', 'Naushahro Feroze Road', 'Peoples Colony'
            ]
        ],
        'Bhit Shah' => [
            'country' => 'PK',
            'areas' => [
                'Bhit Shah Town', 'Matiari (part)', 'Hala', 'Saeedabad', 'New Saeedabad',
                'Odero Lal', 'Basti Mughal', 'Basti Khaskheli', 'Bhit Shah Bazar',
                'Dargah Shah Abdul Latif Area', 'Mouza Nasarpur', 'Goth Jalal', 'Mohallah Soomro',
                'Bhit Shah Cantt', 'Hyderabad Road', 'Gulshan-e-Bhit Shah', 'Peoples Colony'
            ]
        ],
        'Bhopalwala' => [
            'country' => 'PK',
            'areas' => [
                'Bhopalwala Town', 'Sialkot (part)', 'Daska (part)', 'Sambrial', 'Pasrur',
                'Kotli Loharan', 'Wanaz', 'Mianwali Bangla', 'Jinnahpura', 'Mohallah Farooq',
                'Kot Bhopal', 'Chak 1', 'Nurpur', 'Khawaja Colony', 'Bhopalwala Cantt',
                'Sialkot Road', 'Gulshan-e-Bhopalwala', 'Peoples Colony'
            ]
        ],
        'Bozdar Wada' => [
            'country' => 'PK',
            'areas' => [
                'Bozdar Wada Town', 'Moro (part)', 'Bhiria (part)', 'Kandiaro', 'Mehrabpur',
                'Basti Bozdar', 'Basti Khaskheli', 'Goth Haji', 'Mouza Allah Dino',
                'Killi Yousif', 'Bozdar Bazar', 'Mohallah Bozdar', 'Bozdar Wada Cantt',
                'Gulshan-e-Bozdar', 'Naushahro Feroze Road'
            ]
        ],
        'Bulri' => [
            'country' => 'PK',
            'areas' => [
                'Bulri Town', 'Thatta (part)', 'Sujawal', 'Mirpur Bathoro', 'Daro',
                'Jati', 'Keti Bunder', 'Basti Khaskheli', 'Basti Sheikh', 'Goth Bulri',
                'Mouza Haji', 'Bulri Bazar', 'Mohallah Bulri', 'Bulri Cantt', 'Thatta Road',
                'Gulshan-e-Bulri', 'Peoples Colony'
            ]
        ],
        'Burewala' => [
            'country' => 'PK',
            'areas' => [
                'Burewala City', 'Vehari (part)', 'Mailsi', 'Gaggoo', 'Sahuka', 'Karamabad',
                'Dunyapur (part)', 'Chak 52', 'Chak 53', 'Chak 54', 'Basti Ahmad',
                'Basti Khokhar', 'Mohallah Ghaznavi', 'Model Town', 'Satellite Town',
                'Peoples Colony', 'Gulshan-e-Burewala', 'Burewala Cantt', 'Multan Road',
                'Railway Colony', 'Jinnah Colony', 'Kachehri Bazar'
            ]
        ],
        'Chak' => [
            'country' => 'PK',
            'areas' => [
                'Chak Town', 'Sukkur (part)', 'Rohri (part)', 'Pano Aqil', 'Salehpat',
                'Basti Mughal', 'Basti Khaskheli', 'Goth Chak', 'Mouza Haji', 'Killi Mian',
                'Chak Bazar', 'Mohallah Punjabi', 'Chak Cantt', 'Sukkur Road', 'Gulshan-e-Chak',
                'Peoples Colony', 'Railway Colony'
            ]
        ],
        'Chak Azam Sahu' => [
            'country' => 'PK',
            'areas' => [
                'Chak Azam Sahu City', 'Sahiwal (part)', 'Chichawatni (part)', 'Kassowal',
                'Noorshah', 'Chak 39/12-L', 'Chak 40/12-L', 'Basti Arain', 'Basti Khokhar',
                'Kot Sahu', 'Mohallah Muslimabad', 'Gulshan-e-Azam', 'Chak Azam Cantt',
                'Sahiwal Road', 'Model Town', 'Peoples Colony', 'Railway Colony'
            ]
        ],
        'Chak Five Hundred Seventy-five' => [
            'country' => 'PK',
            'areas' => [
                'Chak 575 GB', 'Sheikhupura (part)', 'Faisalabad (part)', 'Jaranwala',
                'Nankana Sahib (part)', 'Chak 574 GB', 'Chak 576 GB', 'Basti Jattan',
                'Basti Rajput', 'Kot Hafeez', 'Mouza Wah', 'Chak 575 Cantt', 'Faisalabad Road',
                'Gulshan-e-Chak', 'Peoples Colony'
            ]
        ],
        'Chak Jhumra' => [
            'country' => 'PK',
            'areas' => [
                'Chak Jhumra City', 'Faisalabad (part)', 'Jaranwala', 'Samundri',
                'Chak 42 JB', 'Chak 43 JB', 'Basti Jhumra', 'Kot Mian', 'Mohallah Faiz',
                'Railway Station Area', 'Chak Jhumra Cantt', 'Faisalabad Road',
                'Gulshan-e-Jhumra', 'Peoples Colony', 'Model Town'
            ]
        ],
        'Chak One Hundred Twenty Nine Left' => [
            'country' => 'PK',
            'areas' => [
                'Chak 129/12-L', 'Sahiwal (part)', 'Chichawatni (part)', 'Harappa',
                'Chak 128/12-L', 'Chak 130/12-L', 'Basti Malik', 'Kot Hashim', 'Mouza Darbar',
                'Chak 129 Cantt', 'Sahiwal Road', 'Gulshan-e-Chak', 'Peoples Colony'
            ]
        ],
        'Chak Thirty-one -Eleven Left' => [
            'country' => 'PK',
            'areas' => [
                'Chak 31/11-L', 'Khanewal (part)', 'Kabirwala', 'Mian Channu',
                'Chak 30/11-L', 'Chak 32/11-L', 'Basti Sial', 'Basti Gujjar', 'Mouza Murad',
                'Chak 31 Cantt', 'Khanewal Road', 'Gulshan-e-Chak', 'Peoples Colony'
            ]
        ],
        'Chak Two Hundred Forty-nine Thal Development Authority' => [
            'country' => 'PK',
            'areas' => [
                'Chak 249 TDA', 'Layyah (part)', 'Karor Lal Esan', 'Fatehpur',
                'Chak 248 TDA', 'Chak 250 TDA', 'Basti Awan', 'Basti Bhatti', 'Kotla Haji',
                'Chak 249 Cantt', 'Layyah Road', 'Gulshan-e-Chak', 'Peoples Colony'
            ]
        ],
        'Chakwal' => [
            'country' => 'PK',
            'areas' => [
                'Chakwal City', 'Chakwal Cantt', 'Kallar Kahar', 'Talagang', 'Lawa',
                'Bhaun', 'Choa Saidan Shah', 'Dhudial', 'Jand', 'Kot Chaudhrian',
                'Basti Awan', 'Mohallah Khokhar', 'Model Town', 'Satellite Town',
                'Gulshan-e-Chakwal', 'Chakwal Bazaar', 'Peoples Colony', 'Railway Road',
                'Jinnah Colony', 'Chakwal Fort Area', 'Rawalpindi Road'
            ]
        ],
        'Chaman' => [
            'country' => 'PK',
            'areas' => [
                'Chaman City', 'Chaman Cantt', 'Killi Jabar', 'Killi Abdul Rehman',
                'Killi Malik Nader', 'Shela Bagh', 'Spin Buldak (border area)', 'Ghazai',
                'Killi Akhtar', 'Killi Haji', 'Ziarat Baba', 'Mohallah Arain',
                'Pakistan Customs Area', 'Chaman Bazaar', 'Gulshan-e-Chaman', 'Peoples Colony',
                'Quetta Road', 'Railway Station Chaman'
            ]
        ],
        'Chamber' => [
            'country' => 'PK',
            'areas' => [
                'Chamber Town', 'Tando Allahyar (part)', 'Tando Adam (part)', 'Nasarpur',
                'Hala (part)', 'Basti Soomro', 'Basti Khaskheli', 'Goth Chamber',
                'Mouza Mirza', 'Killi Abdullah', 'Chamber Bazar', 'Chamber Cantt',
                'Tando Allahyar Road', 'Gulshan-e-Chamber', 'Peoples Colony'
            ]
        ],
        'Charsadda' => [
            'country' => 'PK',
            'areas' => [
                'Charsadda City', 'Charsadda Cantt', 'Tangi', 'Shabqadar', 'Utmanzai',
                'Rajjar', 'Turangzai', 'Sherpao', 'Umarzai', 'Sarfaraz Khel', 'Agra',
                'Banda', 'Dargai', 'Mohallah Afghanan', 'Mohallah Qazi', 'Gulshan Colony',
                'Khanabad', 'Charsadda Bazaar', 'Peoples Colony', 'Model Town', 'Jinnah Colony',
                'Peshawar Road', 'Swat River Area'
            ]
        ],
        'Chawinda' => [
            'country' => 'PK',
            'areas' => [
                'Chawinda Town', 'Sialkot (part)', 'Pasrur (part)', 'Daska (part)',
                'Kotli Loharan', 'Badiana', 'Behlim', 'Thathi', 'Chak 1', 'Mohallah Taj',
                'Kot Chawinda', 'Railway Colony', 'Chawinda Cantt', 'Sialkot Road',
                'Gulshan-e-Chawinda', 'Peoples Colony'
            ]
        ],
        'Chenab Nagar' => [
            'country' => 'PK',
            'areas' => [
                'Chenab Nagar City (Rabwah)', 'Chiniot (part)', 'Lalian', 'Bhawana',
                'Chak 1', 'Chak 2', 'Basti Bhatti', 'Mohallah Ahmadiyya', 'Mohallah Khokhar',
                'Kot Qutab', 'Jhang Road Area', 'Gulshan-e-Chenab', 'Chenab Nagar Cantt',
                'Satellite Town', 'Peoples Colony', 'Model Town', 'Railway Colony'
            ]
        ],
        'Cherat Cantonement' => [
            'country' => 'PK',
            'areas' => [
                'Cherat Cantt', 'Cherat Hills', 'Saleem Khan', 'Cherat Bazar',
                'Ghari Matani', 'Manki Sharif', 'Mohallah Cantt', 'Garrison Area',
                'Officer Colony', 'Cherat Cantt Area', 'Nowshera Road', 'Jalozai',
                'Cantt Market', 'Jinnah Park'
            ]
        ],
        'Chhor' => [
            'country' => 'PK',
            'areas' => [
                'Chhor Town', 'Mithi (part)', 'Nagarparkar', 'Islamkot', 'Virawah',
                'Basti Chhor', 'Goth Haji', 'Chhor Bazar', 'Mohallah Soomro', 'Chhor Cantt',
                'Mithi Road', 'Gulshan-e-Chhor', 'Peoples Colony', 'Karoonjhar Hills Area'
            ]
        ],
        'Chichawatni' => [
            'country' => 'PK',
            'areas' => [
                'Chichawatni City', 'Sahiwal (part)', 'Kassowal', 'Noorshah',
                'Chak 42/12-L', 'Chak 43/12-L', 'Basti Arain', 'Model Town',
                'Satellite Town', 'Railway Colony', 'Chichawatni Cantt', 'Sahiwal Road',
                'Gulshan-e-Chichawatni', 'Peoples Colony', 'Jinnah Colony', 'Chichawatni Forest Area'
            ]
        ],
        'Chilas' => [
            'country' => 'PK',
            'areas' => [
                'Chilas Town', 'Diamer', 'Gunar Farm', 'Thalichi', 'Tangir', 'Darel',
                'Bunar', 'Chilas Bazar', 'KKH Area', 'River View Colony', 'Chilas Cantt',
                'Gilgit Road', 'Gulshan-e-Chilas', 'Peoples Colony', 'Indus River Area'
            ]
        ],
        'Chiniot' => [
            'country' => 'PK',
            'areas' => [
                'Chiniot City', 'Chiniot Cantt', 'Lalian', 'Bhawana', 'Rabwah (Chenab Nagar)',
                'Mohallah Mughalpura', 'Mohallah Qazian', 'Satellite Town', 'Peoples Colony',
                'Jhang Road Area', 'Chiniot Bazaar', 'Gulshan-e-Chiniot', 'Model Town',
                'Railway Colony', 'Jinnah Colony', 'Chenab River Area'
            ]
        ],
        'Chishtian' => [
            'country' => 'PK',
            'areas' => [
                'Chishtian City', 'Bahawalnagar (part)', 'Minchinabad', 'Haroonabad',
                'Basti Allahabad', 'Chak 1', 'Chak 2', 'Mohallah Chishti', 'Model Town',
                'Chishtian Cantt', 'Bahawalnagar Road', 'Gulshan-e-Chishtian', 'Peoples Colony',
                'Satellite Town', 'Railway Colony', 'Jinnah Colony'
            ]
        ],
        'Chitral' => [
            'country' => 'PK',
            'areas' => [
                'Chitral City', 'Chitral Cantt', 'Booni', 'Drosh', 'Mastuj', 'Garam Chashma',
                'Ayun', 'Bumburet', 'Rumbur', 'Birir', 'Shahi Bazar', 'Mohallah Afghanan',
                'Chitral Bazaar', 'Gulshan-e-Chitral', 'Peoples Colony', 'Karakoram Highway',
                'Chitral Fort Area', 'Lotkoh Valley', 'Kunar River Area'
            ]
        ],
        'Choa Saidan Shah' => [
            'country' => 'PK',
            'areas' => [
                'Choa Saidan Shah Town', 'Chakwal (part)', 'Kallar Kahar', 'Mohallah Awan',
                'Katas Raj Area', 'Salt Mines Area', 'Choa Bazar', 'Choa Saidan Shah Cantt',
                'Chakwal Road', 'Gulshan-e-Choa', 'Peoples Colony', 'Salt Range Area'
            ]
        ],
        'Chowki Jamali' => [
            'country' => 'PK',
            'areas' => [
                'Chowki Jamali Town', 'Jafarabad (part)', 'Usta Muhammad', 'Dera Allah Yar',
                'Gandakha', 'Basti Jamali', 'Killi Haji', 'Chowki Bazar', 'Chowki Jamali Cantt',
                'Jafarabad Road', 'Gulshan-e-Jamali', 'Peoples Colony'
            ]
        ],
        'Chuchar-kana Mandi' => [
            'country' => 'PK',
            'areas' => [
                'Chuchar-kana Mandi Town', 'Sheikhupura (part)', 'Faisalabad (part)',
                'Jaranwala', 'Chak 47 GB', 'Chak 48 GB', 'Basti Chuchar', 'Mandi Bazar',
                'Chuchar-kana Cantt', 'Sheikhupura Road', 'Gulshan-e-Chuchar', 'Peoples Colony',
                'Railway Colony', 'Model Town'
            ]
        ],
        'Chuhar Jamali' => [
            'country' => 'PK',
            'areas' => [
                'Chuhar Jamali Town', 'Thatta (part)', 'Sujawal', 'Mirpur Bathoro', 'Daro',
                'Basti Jamali', 'Goth Haji', 'Chuhar Bazar', 'Chuhar Jamali Cantt', 'Thatta Road',
                'Gulshan-e-Chuhar', 'Peoples Colony'
            ]
        ],
        'Chunian' => [
            'country' => 'PK',
            'areas' => [
                'Chunian City', 'Kasur (part)', 'Pattoki', 'Kanganpur', 'Kot Radha Kishan',
                'Chak 1', 'Chak 2', 'Basti Arain', 'Model Town', 'Railway Colony',
                'Chunian Cantt', 'Lahore Road', 'Gulshan-e-Chunian', 'Peoples Colony',
                'Satellite Town', 'Jinnah Colony', 'Chunian Bypass'
            ]
        ],
                'Dadhar' => [
            'country' => 'PK',
            'areas' => [
                'Dadhar Town', 'Dhadar', 'Bhag (part)', 'Mach (part)', 'Sibi (part)',
                'Khattan', 'Marri', 'Dadhar Bazar', 'Killi Haji', 'Dadhar City', 'Dadhar Cantt',
                'Quetta Road Dadhar', 'Gulshan-e-Dadhar', 'Madina Colony Dadhar', 'Peoples Colony Dadhar',
                'Dadhar Model Town', 'Jinnah Colony Dadhar', 'Killi Kakar', 'Basti Marri', 'Dadhar Bypass'
            ]
        ],
        'Dadu' => [
            'country' => 'PK',
            'areas' => [
                'Dadu City', 'Dadu Cantt', 'Mehar', 'Khairpur Nathan Shah', 'Johi',
                'Sehwan Sharif', 'Basti Mughal', 'Basti Khaskheli', 'Model Town',
                'Satellite Colony', 'Dadu City Area', 'Hyderabad Road Dadu', 'Dadu Bazaar',
                'Gulshan-e-Dadu', 'Madina Colony Dadu', 'Peoples Colony Dadu', 'Jinnah Colony Dadu',
                'Mehar Road', 'Johi Road', 'Sehwan Road', 'Basti Khosa', 'Dadu Bypass', 'Dadu Railway Station'
            ]
        ],
        'Daggar' => [
            'country' => 'PK',
            'areas' => [
                'Daggar Town', 'Buner (part)', 'Gagra', 'Pir Baba', 'Elai', 'Nawai Kali',
                'Daggar Bazar', 'Mohallah Afghanan', 'Daggar City', 'Daggar Cantt', 'Swabi Road Daggar',
                'Gulshan-e-Daggar', 'Madina Colony Daggar', 'Peoples Colony Daggar', 'Daggar Model Town',
                'Jinnah Colony Daggar', 'Gagra Road', 'Pir Baba Road', 'Basti Khan', 'Daggar Bypass'
            ]
        ],
        'Daira Din Panah' => [
            'country' => 'PK',
            'areas' => [
                'Daira Din Panah Town', 'Muzaffargarh (part)', 'Alipur (part)', 'Jatoi',
                'Basti Din Panah', 'Chak 27', 'Chak 28', 'Kot Ada', 'Basti Khokhar',
                'Mohallah Arain', 'Daira City', 'Daira Cantt', 'Muzaffargarh Road', 'Daira Bazaar',
                'Gulshan-e-Daira', 'Madina Colony Daira', 'Peoples Colony Daira', 'Daira Model Town',
                'Jinnah Colony Daira', 'Alipur Road', 'Jatoi Road', 'Chak 29', 'Daira Bypass'
            ]
        ],
        'Dajal' => [
            'country' => 'PK',
            'areas' => [
                'Dajal Town', 'Rajanpur (part)', 'Rojhan', 'Fazilpur', 'Basti Dajal',
                'Chak 1', 'Chak 2', 'Kot Mithan', 'Mohallah Baloch', 'Dajal City',
                'Dajal Cantt', 'Rajanpur Road', 'Dajal Bazaar', 'Gulshan-e-Dajal', 'Madina Colony Dajal',
                'Peoples Colony Dajal', 'Dajal Model Town', 'Jinnah Colony Dajal', 'Rojhan Road',
                'Chak 3', 'Basti Khar', 'Dajal Bypass'
            ]
        ],
        'Dalbandin' => [
            'country' => 'PK',
            'areas' => [
                'Dalbandin Town', 'Chagai (part)', 'Nok Kundi', 'Taftan', 'Killi Dalbandin',
                'Killi Malik', 'Mohallah Punjabi', 'Airport Area', 'Dalbandin City', 'Dalbandin Cantt',
                'Quetta Road Dalbandin', 'Dalbandin Bazaar', 'Gulshan-e-Dalbandin', 'Madina Colony Dalbandin',
                'Peoples Colony Dalbandin', 'Dalbandin Model Town', 'Jinnah Colony Dalbandin', 'Nok Kundi Road',
                'Killi Haji', 'Dalbandin Bypass', 'Dalbandin Fort Area'
            ]
        ],
        'Dandot RS' => [
            'country' => 'PK',
            'areas' => [
                'Dandot RS Town', 'Jhelum (part)', 'Pind Dadan Khan', 'Dandot Cement Area',
                'Basti Dandot', 'Railway Station Colony', 'Mohallah Awan', 'Dandot City',
                'Dandot Cantt', 'Jhelum Road Dandot', 'Dandot Bazaar', 'Gulshan-e-Dandot',
                'Madina Colony Dandot', 'Peoples Colony Dandot', 'Dandot Model Town', 'Jinnah Colony Dandot',
                'Pind Dadan Khan Road', 'Cement Factory Colony', 'Dandot Bypass'
            ]
        ],
        'Daromehar' => [
            'country' => 'PK',
            'areas' => [
                'Daromehar Town', 'Thatta (part)', 'Sujawal', 'Mirpur Bathoro',
                'Basti Daromehar', 'Goth Haji', 'Daromehar Bazar', 'Daromehar City',
                'Daromehar Cantt', 'Thatta Road Daromehar', 'Gulshan-e-Daromehar', 'Madina Colony Daromehar',
                'Peoples Colony Daromehar', 'Daromehar Model Town', 'Jinnah Colony Daromehar',
                'Sujawal Road', 'Basti Mallah', 'Daromehar Bypass'
            ]
        ],
        'Darya Khan' => [
            'country' => 'PK',
            'areas' => [
                'Darya Khan Town', 'Bhakkar (part)', 'Kalur Kot', 'Mankera', 'Basti Darya',
                'Chak 45/TDA', 'Kotla Jam', 'Mohallah Arain', 'Darya Khan City', 'Darya Khan Cantt',
                'Bhakkar Road', 'Darya Khan Bazaar', 'Gulshan-e-Darya', 'Madina Colony Darya',
                'Peoples Colony Darya', 'Darya Khan Model Town', 'Jinnah Colony Darya', 'Kalur Kot Road',
                'Chak 46/TDA', 'Basti Khar', 'Darya Bypass'
            ]
        ],
        'Darya Khan Marri' => [
            'country' => 'PK',
            'areas' => [
                'Darya Khan Marri Town', 'Naushahro Feroze (part)', 'Moro', 'Bhiria',
                'Basti Marri', 'Goth Darya', 'Killi Marri', 'Darya Bazar', 'Darya Khan Marri City',
                'Darya Khan Marri Cantt', 'Naushahro Feroze Road', 'Gulshan-e-Marri', 'Madina Colony Marri',
                'Peoples Colony Marri', 'Darya Khan Marri Model Town', 'Jinnah Colony Marri',
                'Moro Road', 'Bhiria Road', 'Basti Buriro', 'Marri Bypass'
            ]
        ],
        'Daska Kalan' => [
            'country' => 'PK',
            'areas' => [
                'Daska Kalan City', 'Sialkot (part)', 'Pasrur', 'Sambrial', 'Kotli Loharan',
                'Chak 1', 'Chak 2', 'Mohallah Muslimabad', 'Model Town', 'Railway Road',
                'Daska Cantt', 'Sialkot Road Daska', 'Daska Bazaar', 'Gulshan-e-Daska',
                'Madina Colony Daska', 'Peoples Colony Daska', 'Satellite Town Daska', 'Jinnah Colony Daska',
                'Pasrur Road', 'Sambrial Road', 'Kotli Loharan Road', 'Chak 3', 'Basti Bhatti',
                'Daska Bypass', 'Daska Railway Station'
            ]
        ],
        'Dasu' => [
            'country' => 'PK',
            'areas' => [
                'Dasu Town', 'Kohistan (part)', 'Pattan', 'Komila', 'Dassu Bazar',
                'KKH Area', 'Mohallah Afghanan', 'Dasu City', 'Dasu Cantt', 'Karakoram Highway Dasu',
                'Gulshan-e-Dasu', 'Madina Colony Dasu', 'Peoples Colony Dasu', 'Dasu Model Town',
                'Jinnah Colony Dasu', 'Pattan Road', 'Komila Road', 'Basti Khan', 'Dasu Bypass',
                'Dasu Dam Area'
            ]
        ],
        'Daud Khel' => [
            'country' => 'PK',
            'areas' => [
                'Daud Khel Town', 'Mianwali (part)', 'Isa Khel', 'Kundian', 'Basti Daud',
                'Chak 1', 'Chak 2', 'Mohallah Awan', 'Daud Khel City', 'Daud Khel Cantt',
                'Mianwali Road', 'Daud Khel Bazaar', 'Gulshan-e-Daud', 'Madina Colony Daud',
                'Peoples Colony Daud', 'Daud Khel Model Town', 'Jinnah Colony Daud', 'Isa Khel Road',
                'Kundian Road', 'Chak 3', 'Basti Khel', 'Daud Khel Bypass'
            ]
        ],
        'Daulatpur' => [
            'country' => 'PK',
            'areas' => [
                'Daulatpur Town', 'Dadu (part)', 'Mehar', 'Khairpur Nathan Shah',
                'Basti Daulat', 'Goth Haji', 'Daulatpur Bazar', 'Daulatpur City', 'Daulatpur Cantt',
                'Dadu Road Daulatpur', 'Gulshan-e-Daulat', 'Madina Colony Daulat', 'Peoples Colony Daulat',
                'Daulatpur Model Town', 'Jinnah Colony Daulat', 'Mehar Road', 'Khairpur Nathan Road',
                'Basti Khosa', 'Daulatpur Bypass'
            ]
        ],
        'Daultala' => [
            'country' => 'PK',
            'areas' => [
                'Daultala Town', 'Rawalpindi (part)', 'Gujar Khan', 'Mandra', 'Basti Daultala',
                'Mohallah Rajput', 'Model Town', 'Daultala City', 'Daultala Cantt', 'Rawalpindi Road',
                'Daultala Bazaar', 'Gulshan-e-Daultala', 'Madina Colony Daultala', 'Peoples Colony Daultala',
                'Satellite Town Daultala', 'Jinnah Colony Daultala', 'Gujar Khan Road', 'Mandra Road',
                'Basti Awan', 'Daultala Bypass'
            ]
        ],
        'Daur' => [
            'country' => 'PK',
            'areas' => [
                'Daur Town', 'Nawabshah (part)', 'Sakrand', 'Qazi Ahmed', 'Basti Daur',
                'Goth Khaskheli', 'Daur Bazar', 'Daur City', 'Daur Cantt', 'Nawabshah Road Daur',
                'Gulshan-e-Daur', 'Madina Colony Daur', 'Peoples Colony Daur', 'Daur Model Town',
                'Jinnah Colony Daur', 'Sakrand Road', 'Qazi Ahmed Road', 'Basti Jatoi', 'Daur Bypass'
            ]
        ],
        'Dera Allahyar' => [
            'country' => 'PK',
            'areas' => [
                'Dera Allahyar Town', 'Jafarabad (part)', 'Usta Muhammad', 'Gandakha',
                'Basti Allahyar', 'Killi Haji', 'Dera Bazar', 'Dera Allahyar City', 'Dera Allahyar Cantt',
                'Jafarabad Road', 'Gulshan-e-Allahyar', 'Madina Colony Allahyar', 'Peoples Colony Allahyar',
                'Dera Allahyar Model Town', 'Jinnah Colony Allahyar', 'Usta Muhammad Road', 'Gandakha Road',
                'Basti Khoso', 'Dera Bypass'
            ]
        ],
        'Dera Bugti' => [
            'country' => 'PK',
            'areas' => [
                'Dera Bugti Town', 'Sui', 'Pirkoh', 'Loti', 'Phailawagh', 'Mithani',
                'Sumbazar', 'Killi Bugti', 'Basti Bugti', 'Dera Bazar', 'Dera Bugti City',
                'Dera Bugti Cantt', 'Sui Road', 'Gulshan-e-Bugti', 'Madina Colony Bugti',
                'Peoples Colony Bugti', 'Dera Bugti Model Town', 'Jinnah Colony Bugti', 'Pirkoh Road',
                'Killi Kakar', 'Dera Bypass'
            ]
        ],
        'Dera Ghazi Khan' => [
            'country' => 'PK',
            'areas' => [
                'Dera Ghazi Khan City', 'Dera Ghazi Khan Cantt', 'Taunsa Sharif', 'Kot Chutta',
                'Sakhi Sarwar', 'Basti Arain', 'Basti Khokhar', 'Model Town', 'Satellite Town',
                'Ghalla Mandi', 'Jampur Road', 'Multan Road', 'Kot Mubarak', 'Mohallah Khawaja',
                'Dera City Area', 'Railway Road DGK', 'Gulshan-e-DGK', 'Madina Colony DGK',
                'Peoples Colony DGK', 'Jinnah Colony DGK', 'Taunsa Road', 'Kot Chutta Road',
                'Sakhi Sarwar Road', 'Chak No 1', 'Chak No 2', 'Dera Bypass', 'Dera Railway Station',
                'Indus Highway DGK'
            ]
        ],
        'Dera Ismail Khan' => [
            'country' => 'PK',
            'areas' => [
                'Dera Ismail Khan City', 'Dera Ismail Khan Cantt', 'Paroa', 'Paharpur',
                'Darazinda', 'Kulachi', 'Muryali', 'Basti Awan', 'Bilot Sharif',
                'Mohallah Afghanan', 'Model Town', 'Railway Colony', 'Gomal University Area',
                'DIK City Area', 'Multan Road DIK', 'DIK Bazaar', 'Gulshan-e-DIK', 'Madina Colony DIK',
                'Peoples Colony DIK', 'Satellite Town DIK', 'Jinnah Colony DIK', 'Paroa Road',
                'Paharpur Road', 'Kulachi Road', 'Basti Khel', 'DIK Bypass', 'DIK Railway Station',
                'Gomal Zam Dam Area', 'KPK Highway'
            ]
        ],
        'Dera Murad Jamali' => [
            'country' => 'PK',
            'areas' => [
                'Dera Murad Jamali Town', 'Nasirabad (part)', 'Jafarabad (part)', 'Usta Muhammad',
                'Dera Allahyar', 'Basti Jamali', 'Killi Haji', 'Dera Bazar', 'Railway Station Area',
                'Dera Murad Jamali City', 'Dera Murad Jamali Cantt', 'Nasirabad Road', 'Gulshan-e-Jamali',
                'Madina Colony Jamali', 'Peoples Colony Jamali', 'Dera Murad Model Town', 'Jinnah Colony Jamali',
                'Usta Muhammad Road', 'Basti Khoso', 'Dera Bypass'
            ]
        ],
        'Dhanot' => [
            'country' => 'PK',
            'areas' => [
                'Dhanot Town', 'Bahawalpur (part)', 'Hasilpur', 'Yazman', 'Basti Dhanot',
                'Chak 11', 'Chak 12', 'Kot Sabzal', 'Mohallah Arain', 'Dhanot City',
                'Dhanot Cantt', 'Bahawalpur Road', 'Dhanot Bazaar', 'Gulshan-e-Dhanot',
                'Madina Colony Dhanot', 'Peoples Colony Dhanot', 'Dhanot Model Town', 'Jinnah Colony Dhanot',
                'Hasilpur Road', 'Yazman Road', 'Chak 13', 'Basti Khokhar', 'Dhanot Bypass'
            ]
        ],
        'Dhaunkal' => [
            'country' => 'PK',
            'areas' => [
                'Dhaunkal Town', 'Gujrat (part)', 'Wazirabad (part)', 'Kunjah', 'Basti Dhaunkal',
                'Mohallah Gujjar', 'Kot Dhaunkal', 'Model Town', 'Dhaunkal City', 'Dhaunkal Cantt',
                'Gujrat Road', 'Dhaunkal Bazaar', 'Gulshan-e-Dhaunkal', 'Madina Colony Dhaunkal',
                'Peoples Colony Dhaunkal', 'Satellite Town Dhaunkal', 'Jinnah Colony Dhaunkal',
                'Wazirabad Road', 'Kunjah Road', 'Chak No 1', 'Basti Cheema', 'Dhaunkal Bypass'
            ]
        ],
        'Dhoro Naro' => [
            'country' => 'PK',
            'areas' => [
                'Dhoro Naro Town', 'Sanghar (part)', 'Tando Adam (part)', 'Khipro',
                'Basti Dhoro', 'Goth Naro', 'Dhoro Bazar', 'Mohallah Khaskheli', 'Dhoro Naro City',
                'Dhoro Naro Cantt', 'Sanghar Road', 'Gulshan-e-Dhoro', 'Madina Colony Dhoro',
                'Peoples Colony Dhoro', 'Dhoro Naro Model Town', 'Jinnah Colony Dhoro',
                'Tando Adam Road', 'Khipro Road', 'Basti Malkani', 'Dhoro Bypass'
            ]
        ],
        'Digri' => [
            'country' => 'PK',
            'areas' => [
                'Digri Town', 'Mirpur Khas (part)', 'Jhuddo', 'Kot Ghulam Muhammad',
                'Basti Digri', 'Goth Digri', 'Digri Bazar', 'Mohallah Soomro', 'Digri City',
                'Digri Cantt', 'Mirpur Khas Road', 'Gulshan-e-Digri', 'Madina Colony Digri',
                'Peoples Colony Digri', 'Digri Model Town', 'Jinnah Colony Digri', 'Jhuddo Road',
                'Kot Ghulam Muhammad Road', 'Basti Khaskheli', 'Digri Bypass'
            ]
        ],
        'Dijkot' => [
            'country' => 'PK',
            'areas' => [
                'Dijkot Town', 'Faisalabad (part)', 'Samundri', 'Tandlianwala',
                'Chak 45 JB', 'Chak 46 JB', 'Basti Dijkot', 'Model Town', 'Railway Road',
                'Dijkot City', 'Dijkot Cantt', 'Faisalabad Road Dijkot', 'Dijkot Bazaar',
                'Gulshan-e-Dijkot', 'Madina Colony Dijkot', 'Peoples Colony Dijkot', 'Satellite Town Dijkot',
                'Jinnah Colony Dijkot', 'Samundri Road', 'Tandlianwala Road', 'Chak 47 JB',
                'Basti Kamboh', 'Dijkot Bypass'
            ]
        ],
        'Dinan Bashnoian Wala' => [
            'country' => 'PK',
            'areas' => [
                'Dinan Bashnoian Wala Town', 'Bahawalnagar (part)', 'Minchinabad', 'Chishtian',
                'Basti Bashnoi', 'Chak 3', 'Chak 4', 'Kot Dinan', 'Mohallah Arain', 'Dinan City',
                'Dinan Cantt', 'Bahawalnagar Road', 'Dinan Bazaar', 'Gulshan-e-Bashnoi', 'Madina Colony Bashnoi',
                'Peoples Colony Bashnoi', 'Dinan Model Town', 'Jinnah Colony Bashnoi', 'Minchinabad Road',
                'Chishtian Road', 'Chak 5', 'Basti Khokhar', 'Dinan Bypass'
            ]
        ],
        'Dinga' => [
            'country' => 'PK',
            'areas' => [
                'Dinga City', 'Gujrat (part)', 'Kharian', 'Jalalpur Jattan', 'Basti Dinga',
                'Mohallah Arain', 'Model Town', 'Railway Road', 'Dinga Cantt', 'Gujrat Road Dinga',
                'Dinga Bazaar', 'Gulshan-e-Dinga', 'Madina Colony Dinga', 'Peoples Colony Dinga',
                'Satellite Town Dinga', 'Jinnah Colony Dinga', 'Kharian Road', 'Jalalpur Jattan Road',
                'Chak No 1', 'Basti Cheema', 'Dinga Bypass', 'Dinga Railway Station'
            ]
        ],
        'Dipalpur' => [
            'country' => 'PK',
            'areas' => [
                'Dipalpur City', 'Okara (part)', 'Renala Khurd', 'Hujra Shah Muqeem',
                'Basirpur (part)', 'Chak 35/GD', 'Chak 36/GD', 'Basti Arain', 'Kot Dipal',
                'Model Town', 'Railway Colony', 'Dipalpur Cantt', 'Okara Road Dipalpur',
                'Dipalpur Bazaar', 'Gulshan-e-Dipalpur', 'Madina Colony Dipalpur', 'Peoples Colony Dipalpur',
                'Satellite Town Dipalpur', 'Jinnah Colony Dipalpur', 'Renala Khurd Road', 'Hujra Road',
                'Basirpur Road', 'Chak 37/GD', 'Basti Gujjar', 'Dipalpur Bypass'
            ]
        ],
        'Diplo' => [
            'country' => 'PK',
            'areas' => [
                'Diplo Town', 'Mithi (part)', 'Chhor (part)', 'Nagarparkar', 'Islamkot',
                'Basti Diplo', 'Goth Diplo', 'Diplo Bazar', 'Diplo City', 'Diplo Cantt',
                'Mithi Road Diplo', 'Gulshan-e-Diplo', 'Madina Colony Diplo', 'Peoples Colony Diplo',
                'Diplo Model Town', 'Jinnah Colony Diplo', 'Chhor Road', 'Nagarparkar Road',
                'Islamkot Road', 'Basti Kolhi', 'Diplo Bypass'
            ]
        ],
        'Doaba' => [
            'country' => 'PK',
            'areas' => [
                'Doaba Town', 'Hangu (part)', 'Tall', 'Thall', 'Kahi', 'Basti Doaba',
                'Mohallah Afghanan', 'Doaba City', 'Doaba Cantt', 'Hangu Road', 'Doaba Bazaar',
                'Gulshan-e-Doaba', 'Madina Colony Doaba', 'Peoples Colony Doaba', 'Doaba Model Town',
                'Jinnah Colony Doaba', 'Tall Road', 'Kahi Road', 'Basti Khel', 'Doaba Bypass'
            ]
        ],
        'Dokri' => [
            'country' => 'PK',
            'areas' => [
                'Dokri Town', 'Larkana (part)', 'Ratodero', 'Naudero', 'Basti Dokri',
                'Goth Dokri', 'Dokri Bazar', 'Dokri City', 'Dokri Cantt', 'Larkana Road Dokri',
                'Gulshan-e-Dokri', 'Madina Colony Dokri', 'Peoples Colony Dokri', 'Dokri Model Town',
                'Jinnah Colony Dokri', 'Ratodero Road', 'Naudero Road', 'Basti Khoso', 'Dokri Bypass'
            ]
        ],
        'Duki' => [
            'country' => 'PK',
            'areas' => [
                'Duki Town', 'Loralai (part)', 'Sanjawi', 'Mekhtar', 'Killi Duki',
                'Basti Duki', 'Duki Bazar', 'Duki City', 'Duki Cantt', 'Loralai Road Duki',
                'Gulshan-e-Duki', 'Madina Colony Duki', 'Peoples Colony Duki', 'Duki Model Town',
                'Jinnah Colony Duki', 'Sanjawi Road', 'Mekhtar Road', 'Killi Kakar', 'Duki Bypass'
            ]
        ],
        'Dullewala' => [
            'country' => 'PK',
            'areas' => [
                'Dullewala Town', 'Bhakkar (part)', 'Kalur Kot', 'Darya Khan', 'Basti Dulle',
                'Chak 44/TDA', 'Kotla Dulle', 'Dullewala City', 'Dullewala Cantt', 'Bhakkar Road',
                'Dullewala Bazaar', 'Gulshan-e-Dulle', 'Madina Colony Dulle', 'Peoples Colony Dulle',
                'Dullewala Model Town', 'Jinnah Colony Dulle', 'Kalur Kot Road', 'Darya Khan Road',
                'Chak 45/TDA', 'Basti Khar', 'Dullewala Bypass'
            ]
        ],
        'Dunga Bunga' => [
            'country' => 'PK',
            'areas' => [
                'Dunga Bunga Town', 'Bahawalnagar (part)', 'Minchinabad', 'Haroonabad',
                'Basti Dunga', 'Chak 1', 'Chak 2', 'Kot Dunga', 'Dunga Bunga City', 'Dunga Bunga Cantt',
                'Bahawalnagar Road', 'Dunga Bazaar', 'Gulshan-e-Dunga', 'Madina Colony Dunga',
                'Peoples Colony Dunga', 'Dunga Bunga Model Town', 'Jinnah Colony Dunga', 'Minchinabad Road',
                'Haroonabad Road', 'Chak 3', 'Basti Khokhar', 'Dunga Bypass'
            ]
        ],
        'Dunyapur' => [
            'country' => 'PK',
            'areas' => [
                'Dunyapur City', 'Lodhran (part)', 'Kahror Pacca', 'Vehari (part)',
                'Basti Dunya', 'Chak 1', 'Chak 2', 'Model Town', 'Dunyapur Cantt', 'Lodhran Road',
                'Dunyapur Bazaar', 'Gulshan-e-Dunya', 'Madina Colony Dunya', 'Peoples Colony Dunya',
                'Satellite Town Dunyapur', 'Jinnah Colony Dunya', 'Kahror Pacca Road', 'Vehari Road',
                'Chak 3', 'Basti Khokhar', 'Dunyapur Bypass', 'Dunyapur Railway Station'
            ]
        ],
        'Eidgah' => [
            'country' => 'PK',
            'areas' => [
                'Eidgah Town', 'Skardu (part)', 'Shigar', 'Kachura', 'Eidgah Bazar', 'KKH Area',
                'Eidgah City', 'Eidgah Cantt', 'Skardu Road Eidgah', 'Gulshan-e-Eidgah', 'Madina Colony Eidgah',
                'Peoples Colony Eidgah', 'Eidgah Model Town', 'Jinnah Colony Eidgah', 'Shigar Road',
                'Kachura Road', 'Basti Balti', 'Eidgah Bypass', 'Eidgah Mosque Area'
            ]
        ],
        'Eminabad' => [
            'country' => 'PK',
            'areas' => [
                'Eminabad Town', 'Gujranwala (part)', 'Kamoke', 'Wazirabad (part)',
                'Basti Eminabad', 'Mohallah Sheikh', 'Railway Colony', 'Eminabad City', 'Eminabad Cantt',
                'Gujranwala Road', 'Eminabad Bazaar', 'Gulshan-e-Eminabad', 'Madina Colony Eminabad',
                'Peoples Colony Eminabad', 'Eminabad Model Town', 'Jinnah Colony Eminabad', 'Kamoke Road',
                'Wazirabad Road', 'Chak No 1', 'Basti Cheema', 'Eminabad Bypass'
            ]
        ],
        'Faisalabad' => [
            'country' => 'PK',
            'areas' => [
                // Core city & cantonment
                'Faisalabad City', 'Faisalabad Cantt',
                
                // Major towns & suburbs
                'Jaranwala', 'Samundri', 'Tandlianwala', 'Dijkot', 'Khurrianwala', 'Mamoo Kanjan', 'Sammundri Road',
                
                // Renowned residential schemes & colonies
                'Madina Town', 'Peoples Colony', 'Satellite Town', 'Model Town', 'Gulberg', 'D Ground',
                'Ghulam Muhammadabad', 'Nishatabad', 'Millat Town', 'Jinnah Colony', 'Amin Town',
                'Iqbal Town', 'Green Town', 'Farooq Colony', 'Rajpoot Colony', 'New Satellite Town',
                'Canal View Housing Scheme', 'Wapda Town', 'Maskan Housing Scheme', 'Faisal Town',
                'Rehman Town', 'Abbas Town', 'Sargodha Road', 'Susan Road', 'Jail Road', 'Kohinoor City',
                
                // Agricultural / Chak numbers (common in Faisalabad)
                'Chak 208 RB', 'Chak 209 RB', 'Chak 212 RB', 'Chak 35 JB', 'Chak 40 GB', 'Chak 46 JB',
                'Chak 61 GB', 'Chak 104 RB', 'Chak 107 RB', 'Chak 114 RB', 'Chak 118 RB', 'Chak 205 RB',
                
                // Other notable neighborhoods
                'Gulshan-e-Madina', 'Muhammad Nagar', 'Raza Abad', 'Samanabad', 'Islam Nagar', 'Saeed Nagar',
                'Ashrafabad', 'Batala Colony', 'Dhobi Ghat', 'Kashmir Road', 'Railway Road', 'Narwala Road',
                'Khiali Stop', 'Tariqabad', 'Siddique Abad', 'Shadab Colony', 'Punjab Society'
            ]
        ],
        'Faqirwali' => [
            'country' => 'PK',
            'areas' => [
                'Faqirwali Town', 'Bahawalnagar (part)', 'Chishtian', 'Haroonabad',
                'Basti Faqir', 'Chak 5', 'Chak 6', 'Kot Faqir', 'Faqirwali City', 'Faqirwali Cantt',
                'Bahawalnagar Road', 'Faqirwali Bazaar', 'Gulshan-e-Faqir', 'Madina Colony Faqir',
                'Peoples Colony Faqir', 'Faqirwali Model Town', 'Jinnah Colony Faqir', 'Chishtian Road',
                'Haroonabad Road', 'Chak 7', 'Basti Khokhar', 'Faqirwali Bypass'
            ]
        ],
        'Faruka' => [
            'country' => 'PK',
            'areas' => [
                'Faruka Town', 'Sargodha (part)', 'Bhera (part)', 'Chak 30 NB', 'Chak 31 NB',
                'Basti Faruka', 'Kot Faruka', 'Railway Colony', 'Faruka City', 'Faruka Cantt',
                'Sargodha Road Faruka', 'Faruka Bazaar', 'Gulshan-e-Faruka', 'Madina Colony Faruka',
                'Peoples Colony Faruka', 'Faruka Model Town', 'Jinnah Colony Faruka', 'Bhera Road',
                'Chak 32 NB', 'Basti Kharal', 'Faruka Bypass'
            ]
        ],
        'Fazilpur' => [
            'country' => 'PK',
            'areas' => [
                'Fazilpur Town', 'Narowal (part)', 'Shakargarh (part)', 'Zafarwal',
                'Basti Fazil', 'Mohallah Gujjar', 'Kot Fazil', 'Fazilpur City', 'Fazilpur Cantt',
                'Narowal Road', 'Fazilpur Bazaar', 'Gulshan-e-Fazil', 'Madina Colony Fazil',
                'Peoples Colony Fazil', 'Fazilpur Model Town', 'Jinnah Colony Fazil', 'Shakargarh Road',
                'Zafarwal Road', 'Chak No 1', 'Basti Bhatti', 'Fazilpur Bypass'
            ]
        ],
        'Fort Abbas' => [
            'country' => 'PK',
            'areas' => [
                'Fort Abbas City', 'Bahawalnagar (part)', 'Haroonabad', 'Minchinabad',
                'Basti Abbas', 'Chak 10', 'Chak 11', 'Fort Area', 'Fort Abbas Cantt',
                'Bahawalnagar Road Fort', 'Fort Abbas Bazaar', 'Gulshan-e-Abbas', 'Madina Colony Abbas',
                'Peoples Colony Abbas', 'Fort Abbas Model Town', 'Jinnah Colony Abbas', 'Haroonabad Road',
                'Minchinabad Road', 'Chak 12', 'Basti Khokhar', 'Fort Abbas Bypass'
            ]
        ],
        'Gadani' => [
            'country' => 'PK',
            'areas' => [
                'Gadani Town', 'Hub (part)', 'Lasbela', 'Sonmiani', 'Gadani Beach Area',
                'Ship Breaking Yard', 'Basti Gadani', 'Gadani City', 'Gadani Cantt', 'Hub Road Gadani',
                'Gadani Bazaar', 'Gulshan-e-Gadani', 'Madina Colony Gadani', 'Peoples Colony Gadani',
                'Gadani Model Town', 'Jinnah Colony Gadani', 'Sonmiani Road', 'Basti Baloch', 'Gadani Bypass'
            ]
        ],
        'Gakuch' => [
            'country' => 'PK',
            'areas' => [
                'Gakuch Town', 'Ghizer (part)', 'Phander', 'Sher Qilla', 'Yasin',
                'Gakuch Bazar', 'KKH Area', 'Gakuch City', 'Gakuch Cantt', 'Gilgit Road Gakuch',
                'Gulshan-e-Gakuch', 'Madina Colony Gakuch', 'Peoples Colony Gakuch', 'Gakuch Model Town',
                'Jinnah Colony Gakuch', 'Phander Road', 'Sher Qilla Road', 'Yasin Road', 'Basti Gakuch',
                'Gakuch Bypass', 'Ghizer River Area'
            ]
        ],
        'Gambat' => [
            'country' => 'PK',
            'areas' => [
                'Gambat Town', 'Khairpur (part)', 'Sobho Dero', 'Kingri', 'Basti Gambat',
                'Goth Gambat', 'Gambat Bazar', 'Gambat City', 'Gambat Cantt', 'Khairpur Road Gambat',
                'Gulshan-e-Gambat', 'Madina Colony Gambat', 'Peoples Colony Gambat', 'Gambat Model Town',
                'Jinnah Colony Gambat', 'Sobho Dero Road', 'Kingri Road', 'Basti Soomro', 'Gambat Bypass'
            ]
        ],
        'Gandava' => [
            'country' => 'PK',
            'areas' => [
                'Gandava Town', 'Dhadar', 'Bhag (part)', 'Mach (part)', 'Killi Gandava',
                'Basti Gandava', 'Gandava Bazar', 'Gandava City', 'Gandava Cantt', 'Sibi Road Gandava',
                'Gulshan-e-Gandava', 'Madina Colony Gandava', 'Peoples Colony Gandava', 'Gandava Model Town',
                'Jinnah Colony Gandava', 'Dhadar Road', 'Bhag Road', 'Killi Kakar', 'Gandava Bypass'
            ]
        ],
        'Garh Maharaja' => [
            'country' => 'PK',
            'areas' => [
                'Garh Maharaja Town', 'Jhang (part)', 'Ahmadpur Sial (part)', 'Shorkot',
                'Basti Maharaja', 'Kot Maharaja', 'Garh Maharaja City', 'Garh Maharaja Cantt',
                'Jhang Road Garh', 'Garh Bazaar', 'Gulshan-e-Garh', 'Madina Colony Garh',
                'Peoples Colony Garh', 'Garh Maharaja Model Town', 'Jinnah Colony Garh',
                'Ahmadpur Sial Road', 'Shorkot Road', 'Chak No 1', 'Basti Kharal', 'Garh Bypass'
            ]
        ],
        'Garhi Khairo' => [
            'country' => 'PK',
            'areas' => [
                'Garhi Khairo Town', 'Jacobabad (part)', 'Shikarpur (part)', 'Thul',
                'Basti Khairo', 'Garhi Bazar', 'Garhi Khairo City', 'Garhi Khairo Cantt',
                'Jacobabad Road Garhi', 'Gulshan-e-Khairo', 'Madina Colony Khairo', 'Peoples Colony Khairo',
                'Garhi Khairo Model Town', 'Jinnah Colony Khairo', 'Shikarpur Road', 'Thul Road',
                'Basti Khoso', 'Garhi Bypass'
            ]
        ],
        'Garhiyasin' => [
            'country' => 'PK',
            'areas' => [
                'Garhiyasin Town', 'Shikarpur (part)', 'Lakhi Ghulam Shah', 'Khanpur',
                'Basti Yasin', 'Goth Garhi', 'Garhiyasin City', 'Garhiyasin Cantt', 'Shikarpur Road',
                'Garhiyasin Bazaar', 'Gulshan-e-Yasin', 'Madina Colony Yasin', 'Peoples Colony Yasin',
                'Garhiyasin Model Town', 'Jinnah Colony Yasin', 'Lakhi Road', 'Khanpur Road',
                'Basti Baloch', 'Garhi Bypass'
            ]
        ],
        'Gharo' => [
            'country' => 'PK',
            'areas' => [
                'Gharo Town', 'Thatta (part)', 'Gharo Cantt', 'Port Qasim Area',
                'Basti Gharo', 'Gharo Bazar', 'Gharo City', 'Thatta Road Gharo', 'Gulshan-e-Gharo',
                'Madina Colony Gharo', 'Peoples Colony Gharo', 'Gharo Model Town', 'Jinnah Colony Gharo',
                'Port Qasim Road', 'Basti Mallah', 'Gharo Bypass', 'Gharo Beach Area'
            ]
        ],
        'Ghauspur' => [
            'country' => 'PK',
            'areas' => [
                'Ghauspur Town', 'Kashmore (part)', 'Kandhkot', 'Tangwani',
                'Basti Ghaus', 'Ghauspur Bazar', 'Ghauspur City', 'Ghauspur Cantt',
                'Kashmore Road', 'Gulshan-e-Ghaus', 'Madina Colony Ghaus', 'Peoples Colony Ghaus',
                'Ghauspur Model Town', 'Jinnah Colony Ghaus', 'Kandhkot Road', 'Tangwani Road',
                'Basti Khoso', 'Ghauspur Bypass'
            ]
        ],
        'Ghotki' => [
            'country' => 'PK',
            'areas' => [
                'Ghotki City', 'Ghotki Cantt', 'Daharki', 'Mirpur Mathelo', 'Ubauro',
                'Khangarh', 'Basti Ghotki', 'Model Town', 'Ghotki City Area', 'Sukkur Road Ghotki',
                'Ghotki Bazaar', 'Gulshan-e-Ghotki', 'Madina Colony Ghotki', 'Peoples Colony Ghotki',
                'Satellite Town Ghotki', 'Jinnah Colony Ghotki', 'Daharki Road', 'Mirpur Mathelo Road',
                'Ubauro Road', 'Chak No 1', 'Basti Khoso', 'Ghotki Bypass', 'Ghotki Railway Station'
            ]
        ],
        'Gilgit' => [
            'country' => 'PK',
            'areas' => [
                'Gilgit City', 'Gilgit Cantt', 'Jutial', 'Danyor', 'Nomal', 'Minawar',
                'Oshikhandass', 'Kargah', 'Silk Route Area', 'Model Town', 'Gilgit City Area',
                'Karakoram Highway Gilgit', 'Gilgit Bazaar', 'Gulshan-e-Gilgit', 'Madina Colony Gilgit',
                'Peoples Colony Gilgit', 'Satellite Town Gilgit', 'Jinnah Colony Gilgit', 'Jutial Road',
                'Danyor Road', 'Nomal Road', 'Kargah Road', 'Basti Gilgit', 'Gilgit Bypass',
                'Gilgit Airport Area', 'Gilgit River View'
            ]
        ],
        'Gojra' => [
            'country' => 'PK',
            'areas' => [
                'Gojra City', 'Toba Tek Singh (part)', 'Kamalia', 'Pir Mahal',
                'Chak 1', 'Chak 2', 'Basti Gojra', 'Model Town', 'Gojra Cantt',
                'Faisalabad Road Gojra', 'Gojra Bazaar', 'Gulshan-e-Gojra', 'Madina Colony Gojra',
                'Peoples Colony Gojra', 'Satellite Town Gojra', 'Jinnah Colony Gojra', 'Kamalia Road',
                'Pir Mahal Road', 'Chak 3', 'Basti Kharal', 'Gojra Bypass', 'Gojra Railway Station'
            ]
        ],
        'Goth Garelo' => [
            'country' => 'PK',
            'areas' => [
                'Goth Garelo Town', 'Larkana (part)', 'Dokri (part)', 'Basti Garelo',
                'Goth Haji', 'Garelo Bazar', 'Goth Garelo City', 'Goth Garelo Cantt', 'Larkana Road',
                'Gulshan-e-Garelo', 'Madina Colony Garelo', 'Peoples Colony Garelo', 'Goth Garelo Model Town',
                'Jinnah Colony Garelo', 'Dokri Road', 'Basti Khoso', 'Garelo Bypass'
            ]
        ],
        'Goth Phulji' => [
            'country' => 'PK',
            'areas' => [
                'Goth Phulji Town', 'Dadu (part)', 'Johi', 'Basti Phulji', 'Goth Phulji Bazar',
                'Goth Phulji City', 'Goth Phulji Cantt', 'Dadu Road', 'Gulshan-e-Phulji', 'Madina Colony Phulji',
                'Peoples Colony Phulji', 'Goth Phulji Model Town', 'Jinnah Colony Phulji', 'Johi Road',
                'Basti Khosa', 'Phulji Bypass'
            ]
        ],
        'Goth Radhan' => [
            'country' => 'PK',
            'areas' => [
                'Goth Radhan Town', 'Nawabshah (part)', 'Daur', 'Basti Radhan', 'Goth Radhan Bazar',
                'Goth Radhan City', 'Goth Radhan Cantt', 'Nawabshah Road', 'Gulshan-e-Radhan',
                'Madina Colony Radhan', 'Peoples Colony Radhan', 'Goth Radhan Model Town', 'Jinnah Colony Radhan',
                'Daur Road', 'Basti Jatoi', 'Radhan Bypass'
            ]
        ],
        'Gujar Khan' => [
            'country' => 'PK',
            'areas' => [
                'Gujar Khan City', 'Rawalpindi (part)', 'Daultala', 'Mandra', 'Basti Gujar',
                'Mohallah Rajput', 'Model Town', 'Railway Colony', 'Gujar Khan Cantt', 'Rawalpindi Road',
                'Gujar Khan Bazaar', 'Gulshan-e-Gujar', 'Madina Colony Gujar', 'Peoples Colony Gujar',
                'Satellite Town Gujar', 'Jinnah Colony Gujar', 'Daultala Road', 'Mandra Road',
                'Chak No 1', 'Basti Awan', 'Gujar Khan Bypass'
            ]
        ],
        'Gujranwala' => [
            'country' => 'PK',
            'areas' => [
                'Gujranwala City', 'Gujranwala Cantt', 'Kamoke', 'Wazirabad (part)',
                'Ali Pur Chatta', 'Satellite Town', 'Peoples Colony', 'Model Town',
                'Gulshan-e-Iqbal', 'Ghalla Mandi', 'Railway Road', 'Gujranwala City Area',
                'Lahore Road', 'Sialkot Road', 'Gujranwala Bazaar', 'Madina Colony Gujranwala',
                'Jinnah Colony Gujranwala', 'Sheikhupura Road', 'Eminabad Road', 'Aroop Town',
                'Canal View Housing Scheme', 'Askari Colony', 'Gujranwala Bypass', 'Gujranwala Railway Station'
            ]
        ],
        'Gujrat' => [
            'country' => 'PK',
            'areas' => [
                'Gujrat City', 'Gujrat Cantt', 'Kharian', 'Dinga', 'Jalalpur Jattan',
                'Kunjah', 'Model Town', 'Satellite Town', 'Railway Colony', 'Gujrat City Area',
                'Gujranwala Road', 'Gujrat Bazaar', 'Gulshan-e-Gujrat', 'Madina Colony Gujrat',
                'Peoples Colony Gujrat', 'Jinnah Colony Gujrat', 'Kharian Road', 'Dinga Road',
                'Jalalpur Jattan Road', 'Kunjah Road', 'Chak No 1', 'Basti Cheema', 'Gujrat Bypass',
                'Gujrat Railway Station'
            ]
        ],
        'Gulishah Kach' => [
            'country' => 'PK',
            'areas' => [
                'Gulishah Kach Town', 'Bannu (part)', 'Domail', 'Basti Gulishah', 'Kach Bazar',
                'Gulishah Kach City', 'Gulishah Kach Cantt', 'Bannu Road', 'Gulishah Bazaar',
                'Gulshan-e-Gulishah', 'Madina Colony Gulishah', 'Peoples Colony Gulishah',
                'Gulishah Model Town', 'Jinnah Colony Gulishah', 'Domail Road', 'Basti Khel',
                'Gulishah Bypass'
            ]
        ],
        'Gwadar' => [
            'country' => 'PK',
            'areas' => [
                'Gwadar City', 'Gwadar Port Area', 'Jiwani', 'Pasni', 'Ormara', 'Koh-e-Batil',
                'Gwadar Cantt', 'Fish Harbour Area', 'Gwadar City Area', 'Makran Coastal Highway',
                'Gwadar Bazaar', 'Gulshan-e-Gwadar', 'Madina Colony Gwadar', 'Peoples Colony Gwadar',
                'Satellite Town Gwadar', 'Jinnah Colony Gwadar', 'Jiwani Road', 'Pasni Road',
                'Ormara Road', 'Basti Baloch', 'Gwadar Bypass', 'Gwadar Airport Area', 'Gwadar East Bay',
                'Gwadar West Bay', 'Gwadar Industrial Area', 'China-Pakistan Economic Corridor (CPEC) Area'
            ]
        ],
                'Hadali' => [
            'country' => 'PK',
            'areas' => [
                'Hadali Town', 'Sialkot (part)', 'Pasrur', 'Kotli Loharan', 'Basti Hadali',
                'Mohallah Hadali', 'Hadali City', 'Hadali Cantt', 'Sialkot Road Hadali', 'Hadali Bazaar',
                'Gulshan-e-Hadali', 'Madina Colony Hadali', 'Peoples Colony Hadali', 'Hadali Model Town',
                'Jinnah Colony Hadali', 'Pasrur Road', 'Kotli Loharan Road', 'Chak No 1', 'Basti Bhatti',
                'Hadali Bypass', 'Hadali Railway Station', 'Mohallah Mughal', 'Mohallah Arain'
            ]
        ],
        'Hafizabad' => [
            'country' => 'PK',
            'areas' => [
                'Hafizabad City', 'Hafizabad Cantt', 'Sukheke Mandi', 'Jalalpur Bhatti',
                'Kot Sarwar', 'Model Town', 'Hafizabad City Area', 'Gujranwala Road', 'Hafizabad Bazaar',
                'Gulshan-e-Hafizabad', 'Madina Colony Hafizabad', 'Peoples Colony Hafizabad',
                'Satellite Town Hafizabad', 'Jinnah Colony Hafizabad', 'Sukheke Mandi Road',
                'Jalalpur Bhatti Road', 'Kot Sarwar Road', 'Chak No 1', 'Chak No 2', 'Basti Gujjar',
                'Hafizabad Bypass', 'Hafizabad Railway Station'
            ]
        ],
        'Hala' => [
            'country' => 'PK',
            'areas' => [
                'Hala Town', 'Matiari (part)', 'Bhit Shah', 'Saeedabad', 'New Hala', 'Basti Hala',
                'Hala City', 'Hala Cantt', 'Hyderabad Road Hala', 'Hala Bazaar', 'Gulshan-e-Hala',
                'Madina Colony Hala', 'Peoples Colony Hala', 'Hala Model Town', 'Jinnah Colony Hala',
                'Bhit Shah Road', 'Saeedabad Road', 'New Hala Colony', 'Basti Mallah', 'Hala Bypass',
                'Hala Railway Station'
            ]
        ],
        'Hangu' => [
            'country' => 'PK',
            'areas' => [
                'Hangu City', 'Hangu Cantt', 'Tall', 'Doaba', 'Thall', 'Kahi', 'Basti Hangu',
                'Hangu City Area', 'Kohat Road Hangu', 'Hangu Bazaar', 'Gulshan-e-Hangu', 'Madina Colony Hangu',
                'Peoples Colony Hangu', 'Satellite Town Hangu', 'Jinnah Colony Hangu', 'Tall Road',
                'Doaba Road', 'Kahi Road', 'Basti Khel', 'Hangu Bypass', 'Hangu Fort Area'
            ]
        ],
        'Haripur' => [
            'country' => 'PK',
            'areas' => [
                'Haripur City', 'Haripur Cantt', 'Khanpur', 'Ghazi', 'Havelian (part)',
                'Tarbela', 'Sirikot', 'Model Town', 'Haripur City Area', 'Abbottabad Road',
                'Haripur Bazaar', 'Gulshan-e-Haripur', 'Madina Colony Haripur', 'Peoples Colony Haripur',
                'Satellite Town Haripur', 'Jinnah Colony Haripur', 'Khanpur Road', 'Ghazi Road',
                'Tarbela Road', 'Sirikot Road', 'Basti Haripur', 'Haripur Bypass', 'Haripur Railway Station',
                'Tarbela Dam Colony', 'Hattar Industrial Area'
            ]
        ],
        'Harnai' => [
            'country' => 'PK',
            'areas' => [
                'Harnai Town', 'Harnai Cantt', 'Khost', 'Sharigh', 'Shahrag', 'Killi Harnai',
                'Harnai City', 'Quetta Road Harnai', 'Harnai Bazaar', 'Gulshan-e-Harnai', 'Madina Colony Harnai',
                'Peoples Colony Harnai', 'Harnai Model Town', 'Jinnah Colony Harnai', 'Khost Road',
                'Sharigh Road', 'Shahrag Road', 'Killi Kakar', 'Harnai Bypass', 'Harnai Fort Area'
            ]
        ],
        'Harnoli' => [
            'country' => 'PK',
            'areas' => [
                'Harnoli Town', 'Mianwali (part)', 'Isa Khel', 'Piplan', 'Basti Harnoli', 'Harnoli City',
                'Harnoli Cantt', 'Mianwali Road', 'Harnoli Bazaar', 'Gulshan-e-Harnoli', 'Madina Colony Harnoli',
                'Peoples Colony Harnoli', 'Harnoli Model Town', 'Jinnah Colony Harnoli', 'Isa Khel Road',
                'Piplan Road', 'Basti Khel', 'Harnoli Bypass', 'Harnoli Railway Station'
            ]
        ],
        'Harunabad' => [
            'country' => 'PK',
            'areas' => [
                'Harunabad City', 'Bahawalnagar (part)', 'Fort Abbas', 'Minchinabad', 'Basti Harun',
                'Harunabad Cantt', 'Bahawalnagar Road', 'Harunabad Bazaar', 'Gulshan-e-Harun', 'Madina Colony Harun',
                'Peoples Colony Harun', 'Harunabad Model Town', 'Jinnah Colony Harun', 'Fort Abbas Road',
                'Minchinabad Road', 'Chak No 1', 'Chak No 2', 'Basti Khokhar', 'Harunabad Bypass'
            ]
        ],
        'Hasilpur' => [
            'country' => 'PK',
            'areas' => [
                'Hasilpur City', 'Bahawalpur (part)', 'Yazman', 'Dhanot', 'Chak 1', 'Chak 2', 'Model Town',
                'Hasilpur Cantt', 'Bahawalpur Road Hasilpur', 'Hasilpur Bazaar', 'Gulshan-e-Hasilpur',
                'Madina Colony Hasilpur', 'Peoples Colony Hasilpur', 'Satellite Town Hasilpur', 'Jinnah Colony Hasilpur',
                'Yazman Road', 'Dhanot Road', 'Chak No 3', 'Basti Khokhar', 'Hasilpur Bypass', 'Hasilpur Railway Station'
            ]
        ],
        'Hattian Bala' => [
            'country' => 'PK',
            'areas' => [
                'Hattian Bala Town', 'Dowarian', 'Chikar', 'Leepa Valley', 'Hattian Bazar',
                'Mohallah Mughal', 'Hattian Bala City', 'Hattian Cantt', 'Muzaffarabad Road', 'Hattian Bazaar',
                'Gulshan-e-Hattian', 'Madina Colony Hattian', 'Peoples Colony Hattian', 'Hattian Model Town',
                'Jinnah Colony Hattian', 'Dowarian Road', 'Chikar Road', 'Leepa Valley Road', 'Basti Mughal',
                'Hattian Bypass', 'Jhelum Valley Road'
            ]
        ],
        'Haveli Lakha' => [
            'country' => 'PK',
            'areas' => [
                'Haveli Lakha Town', 'Okara (part)', 'Basirpur (part)', 'Depalpur (part)',
                'Chak 1/GD', 'Chak 2/GD', 'Basti Lakha', 'Haveli Lakha City', 'Haveli Lakha Cantt',
                'Okara Road', 'Haveli Lakha Bazaar', 'Gulshan-e-Lakha', 'Madina Colony Lakha',
                'Peoples Colony Lakha', 'Haveli Lakha Model Town', 'Jinnah Colony Lakha', 'Basirpur Road',
                'Depalpur Road', 'Chak No 3/GD', 'Basti Gujjar', 'Haveli Bypass'
            ]
        ],
        'Havelian' => [
            'country' => 'PK',
            'areas' => [
                'Havelian City', 'Abbottabad (part)', 'Nawansher', 'Mirpur', 'Amirabad (part)',
                'Kotli Bagh', 'Mohallah Havelian', 'Havelian Cantt', 'Haripur Road Havelian', 'Havelian Bazaar',
                'Gulshan-e-Havelian', 'Madina Colony Havelian', 'Peoples Colony Havelian', 'Satellite Town Havelian',
                'Jinnah Colony Havelian', 'Nawansher Road', 'Mirpur Road', 'Amirabad Road', 'Kotli Bagh Road',
                'Basti Syedan', 'Havelian Bypass', 'Havelian Railway Station'
            ]
        ],
        'Hazro City' => [
            'country' => 'PK',
            'areas' => [
                'Hazro City', 'Attock (part)', 'Hasan Abdal', 'Sanjwal', 'Hazro Cantt', 'Basti Hazro',
                'Hazro City Area', 'GT Road Hazro', 'Hazro Bazaar', 'Gulshan-e-Hazro', 'Madina Colony Hazro',
                'Peoples Colony Hazro', 'Satellite Town Hazro', 'Jinnah Colony Hazro', 'Hasan Abdal Road',
                'Sanjwal Road', 'Basti Khel', 'Hazro Bypass', 'Hazro Railway Station'
            ]
        ],
        'Hingorja' => [
            'country' => 'PK',
            'areas' => [
                'Hingorja Town', 'Khairpur (part)', 'Gambat', 'Kingri', 'Basti Hingorja', 'Hingorja Bazar',
                'Hingorja City', 'Hingorja Cantt', 'Khairpur Road', 'Hingorja Bazaar', 'Gulshan-e-Hingorja',
                'Madina Colony Hingorja', 'Peoples Colony Hingorja', 'Hingorja Model Town', 'Jinnah Colony Hingorja',
                'Gambat Road', 'Kingri Road', 'Basti Soomro', 'Hingorja Bypass'
            ]
        ],
        'Hujra Shah Muqim' => [
            'country' => 'PK',
            'areas' => [
                'Hujra Shah Muqim Town', 'Okara (part)', 'Dipalpur (part)', 'Renala Khurd',
                'Basti Hujra', 'Model Town', 'Hujra Shah Muqim City', 'Hujra Cantt', 'Okara Road Hujra',
                'Hujra Bazaar', 'Gulshan-e-Hujra', 'Madina Colony Hujra', 'Peoples Colony Hujra',
                'Satellite Town Hujra', 'Jinnah Colony Hujra', 'Dipalpur Road', 'Renala Khurd Road',
                'Chak No 1', 'Basti Gujjar', 'Hujra Bypass'
            ]
        ],
        'Hyderabad' => [
            'country' => 'PK',
            'areas' => [
                'Hyderabad City', 'Hyderabad Cantt', 'Latifabad', 'Qasimabad', 'Kotri (part)',
                'Hirabad', 'Auto Bahn Road', 'Model Colony', 'Gulshan-e-Mustafa', 'Sakhi Hassan',
                'Tando Hyder', 'Hyderabad City Area', 'Saddar Hyderabad', 'Market Road', 'Hyderabad Bazaar',
                'Gulshan-e-Hyderabad', 'Madina Colony Hyderabad', 'Peoples Colony Hyderabad',
                'Satellite Town Hyderabad', 'Jinnah Colony Hyderabad', 'Latifabad Unit 1-12',
                'Qasimabad Sector 1-10', 'Auto Bahn Town', 'Bhatti Chowk', 'Hussainabad',
                'Hyderabad Bypass', 'Hyderabad Railway Station', 'University of Sindh Area',
                'Rani Bagh', 'Fort Hyderabad (Pakka Qila)', 'Jamshoro Road', 'Karachi Road'
            ]
        ],
        'Islamabad' => [
            'country' => 'PK',
            'areas' => [
                // Core city & rural areas
                'Islamabad City', 'Islamabad Rural',
                
                // Major sectors (Sectors D, E, F, G, H, I)
                'Sector D-12', 'Sector D-13', 'Sector D-17',
                'Sector E-7', 'Sector E-8', 'Sector E-9', 'Sector E-10', 'Sector E-11', 'Sector E-12',
                'Sector F-5', 'Sector F-6', 'Sector F-7', 'Sector F-8', 'Sector F-9', 'Sector F-10', 'Sector F-11', 'Sector F-12',
                'Sector G-5', 'Sector G-6', 'Sector G-7', 'Sector G-8', 'Sector G-9', 'Sector G-10', 'Sector G-11', 'Sector G-12', 'Sector G-13', 'Sector G-14', 'Sector G-15',
                'Sector H-8', 'Sector H-9', 'Sector H-10', 'Sector H-11', 'Sector H-12', 'Sector H-13', 'Sector H-15', 'Sector H-16', 'Sector H-17',
                'Sector I-8', 'Sector I-9', 'Sector I-10', 'Sector I-11', 'Sector I-12', 'Sector I-14', 'Sector I-15', 'Sector I-16',
                
                // Major housing schemes & societies
                'Bahria Town Islamabad', 'DHA Islamabad', 'DHA Phase 1', 'DHA Phase 2', 'DHA Valley',
                'Capital Development Authority (CDA) Sectors', 'Park View Housing Scheme', 'Country Club Islamabad',
                'Gulberg Residencia', 'Blue World City', 'Top City 1', 'Multan Gardens', 'Airport Housing Scheme',
                'Capital Smart City', 'Mall of Islamabad', 'Mivida', 'Zamzama Residencia',
                
                // Suburbs & villages / towns
                'Bara Kahu', 'Bhara Kahu', 'Nilore', 'Golra Sharif', 'Shah Allah Ditta', 'Tarlai', 'Alipur Farash',
                'Kuri Model Town', 'Chirah', 'Sihala', 'Phulgran', 'Jhangi Sayedan', 'Humak', 'Korang Town',
                'Malpur', 'Nurpur Shahan', 'Bani Gala', 'Bhimber Colony', 'Chak Shahzad', 'Jhanda Chichi',
                'Morgah', 'Rawal Town', 'Sawan Camp', 'Tarar Khel', 'Pind Begwal', 'Pindorian',
                
                // Major roads & zones
                'Islamabad Highway', 'Srinagar Highway', 'Margalla Road', 'Khayaban-e-Sir Syed',
                'Park Road', 'Lehtrar Road', 'Kashmir Highway', 'G.T. Road (Islamabad section)'
            ]
        ],
        'Islamkot' => [
            'country' => 'PK',
            'areas' => [
                'Islamkot Town', 'Mithi (part)', 'Chhor (part)', 'Diplo', 'Nagarparkar', 'Basti Islam',
                'Islamkot City', 'Islamkot Cantt', 'Mithi Road Islamkot', 'Islamkot Bazaar', 'Gulshan-e-Islam',
                'Madina Colony Islamkot', 'Peoples Colony Islamkot', 'Islamkot Model Town', 'Jinnah Colony Islamkot',
                'Chhor Road', 'Diplo Road', 'Nagarparkar Road', 'Basti Kolhi', 'Basti Meghwar', 'Islamkot Bypass'
            ]
        ],
        'Jacobabad' => [
            'country' => 'PK',
            'areas' => [
                'Jacobabad City', 'Jacobabad Cantt', 'Garhi Khairo', 'Thul', 'Basti Jacob', 'Model Town',
                'Jacobabad City Area', 'Shikarpur Road', 'Jacobabad Bazaar', 'Gulshan-e-Jacob', 'Madina Colony Jacob',
                'Peoples Colony Jacob', 'Satellite Town Jacob', 'Jinnah Colony Jacob', 'Garhi Khairo Road',
                'Thul Road', 'Basti Khoso', 'Jacobabad Bypass', 'Jacobabad Railway Station', 'Jacobabad Airbase Area'
            ]
        ],
        'Jahanian Shah' => [
            'country' => 'PK',
            'areas' => [
                'Jahanian Shah Town', 'Khanewal (part)', 'Kabirwala', 'Mian Channu', 'Basti Jahanian',
                'Jahanian Shah City', 'Jahanian Shah Cantt', 'Khanewal Road', 'Jahanian Shah Bazaar',
                'Gulshan-e-Jahanian', 'Madina Colony Jahanian', 'Peoples Colony Jahanian', 'Jahanian Shah Model Town',
                'Jinnah Colony Jahanian', 'Kabirwala Road', 'Mian Channu Road', 'Chak No 1', 'Basti Kharal',
                'Jahanian Bypass'
            ]
        ],
        'Jalalpur Jattan' => [
            'country' => 'PK',
            'areas' => [
                'Jalalpur Jattan Town', 'Gujrat (part)', 'Dinga', 'Kharian', 'Basti Jattan', 'Jalalpur Jattan City',
                'Jalalpur Jattan Cantt', 'Gujrat Road Jalalpur', 'Jalalpur Bazaar', 'Gulshan-e-Jalalpur',
                'Madina Colony Jalalpur', 'Peoples Colony Jalalpur', 'Jalalpur Model Town', 'Jinnah Colony Jalalpur',
                'Dinga Road', 'Kharian Road', 'Chak No 1', 'Basti Cheema', 'Jalalpur Bypass'
            ]
        ],
        'Jalalpur Pirwala' => [
            'country' => 'PK',
            'areas' => [
                'Jalalpur Pirwala Town', 'Multan (part)', 'Shujabad', 'Basti Pirwala', 'Chak 1', 'Chak 2',
                'Jalalpur Pirwala City', 'Jalalpur Cantt', 'Multan Road Jalalpur', 'Jalalpur Bazaar',
                'Gulshan-e-Pirwala', 'Madina Colony Pirwala', 'Peoples Colony Pirwala', 'Jalalpur Model Town',
                'Jinnah Colony Pirwala', 'Shujabad Road', 'Chak No 3', 'Basti Khokhar', 'Jalalpur Bypass'
            ]
        ],
        'Jampur' => [
            'country' => 'PK',
            'areas' => [
                'Jampur Town', 'Rajanpur (part)', 'Rojhan', 'Fazilpur', 'Basti Jampur', 'Jampur City',
                'Jampur Cantt', 'Rajanpur Road', 'Jampur Bazaar', 'Gulshan-e-Jampur', 'Madina Colony Jampur',
                'Peoples Colony Jampur', 'Jampur Model Town', 'Jinnah Colony Jampur', 'Rojhan Road',
                'Fazilpur Road', 'Basti Khar', 'Jampur Bypass', 'Jampur Fort Area'
            ]
        ],
        'Jamshoro' => [
            'country' => 'PK',
            'areas' => [
                'Jamshoro City', 'Jamshoro Cantt', 'Kotri (part)', 'Hyderabad (part)',
                'Nooriabad', 'Sindh University Area', 'Liaquat University Area', 'Jamshoro City Area',
                'Hyderabad Road Jamshoro', 'Jamshoro Bazaar', 'Gulshan-e-Jamshoro', 'Madina Colony Jamshoro',
                'Peoples Colony Jamshoro', 'Satellite Town Jamshoro', 'Jinnah Colony Jamshoro', 'Nooriabad Road',
                'Kotri Road', 'Basti Mirza', 'Jamshoro Bypass', 'Indus Highway Jamshoro', 'Jamshoro Power Plant Area'
            ]
        ],
        'Jand' => [
            'country' => 'PK',
            'areas' => [
                'Jand Town', 'Attock (part)', 'Fateh Jang', 'Pindi Gheb', 'Basti Jand', 'Mohallah Awan',
                'Jand City', 'Jand Cantt', 'Attock Road Jand', 'Jand Bazaar', 'Gulshan-e-Jand', 'Madina Colony Jand',
                'Peoples Colony Jand', 'Jand Model Town', 'Jinnah Colony Jand', 'Fateh Jang Road', 'Pindi Gheb Road',
                'Basti Khel', 'Jand Bypass'
            ]
        ],
        'Jandiala Sher Khan' => [
            'country' => 'PK',
            'areas' => [
                'Jandiala Sher Khan Town', 'Sheikhupura (part)', 'Nankana Sahib (part)',
                'Chak 50 GB', 'Basti Jandiala', 'Kot Sher Khan', 'Jandiala City', 'Jandiala Cantt',
                'Sheikhupura Road', 'Jandiala Bazaar', 'Gulshan-e-Jandiala', 'Madina Colony Jandiala',
                'Peoples Colony Jandiala', 'Jandiala Model Town', 'Jinnah Colony Jandiala', 'Nankana Sahib Road',
                'Chak No 50 GB', 'Basti Gujjar', 'Jandiala Bypass'
            ]
        ],
        'Jaranwala' => [
            'country' => 'PK',
            'areas' => [
                'Jaranwala City', 'Faisalabad (part)', 'Tandlianwala', 'Samundri',
                'Chak 46 JB', 'Chak 47 JB', 'Model Town', 'Jaranwala Cantt', 'Faisalabad Road Jaranwala',
                'Jaranwala Bazaar', 'Gulshan-e-Jaranwala', 'Madina Colony Jaranwala', 'Peoples Colony Jaranwala',
                'Satellite Town Jaranwala', 'Jinnah Colony Jaranwala', 'Tandlianwala Road', 'Samundri Road',
                'Chak No 46 JB', 'Chak No 47 JB', 'Basti Kamboh', 'Jaranwala Bypass', 'Jaranwala Railway Station'
            ]
        ],
        'Jati' => [
            'country' => 'PK',
            'areas' => [
                'Jati Town', 'Sujawal (part)', 'Mirpur Bathoro', 'Daro', 'Basti Jati', 'Jati Bazar',
                'Jati City', 'Jati Cantt', 'Thatta Road Jati', 'Jati Bazaar', 'Gulshan-e-Jati', 'Madina Colony Jati',
                'Peoples Colony Jati', 'Jati Model Town', 'Jinnah Colony Jati', 'Mirpur Bathoro Road',
                'Daro Road', 'Basti Mallah', 'Jati Bypass', 'Jati Port Area'
            ]
        ],
        'Jatoi Shimali' => [
            'country' => 'PK',
            'areas' => [
                'Jatoi Shimali Town (Jatoi)', 'Muzaffargarh (part)', 'Alipur (part)',
                'Kot Addu (part)', 'Basti Jatoi', 'Jatoi City', 'Jatoi Cantt', 'Muzaffargarh Road',
                'Jatoi Bazaar', 'Gulshan-e-Jatoi', 'Madina Colony Jatoi', 'Peoples Colony Jatoi',
                'Jatoi Model Town', 'Jinnah Colony Jatoi', 'Alipur Road', 'Kot Addu Road', 'Chak No 1',
                'Basti Khar', 'Jatoi Bypass'
            ]
        ],
        'Jauharabad' => [
            'country' => 'PK',
            'areas' => [
                'Jauharabad City', 'Khushab (part)', 'Mitha Tiwana', 'Quaidabad', 'Model Town',
                'Jauharabad Cantt', 'Khushab Road', 'Jauharabad Bazaar', 'Gulshan-e-Jauhar', 'Madina Colony Jauhar',
                'Peoples Colony Jauhar', 'Satellite Town Jauhar', 'Jinnah Colony Jauhar', 'Mitha Tiwana Road',
                'Quaidabad Road', 'Chak No 1', 'Basti Kharal', 'Jauharabad Bypass', 'Jauharabad Railway Station'
            ]
        ],
        'Jhang City' => [
            'country' => 'PK',
            'areas' => [
                'Jhang City', 'Jhang Sadr (twin city)', 'Shorkot', 'Ahmadpur Sial (part)',
                'Mohallah Ghaznavi', 'Satellite Town', 'Peoples Colony', 'Jhang City Area', 'Trimmu Road',
                'Jhang Bazaar', 'Gulshan-e-Jhang', 'Madina Colony Jhang', 'Jinnah Colony Jhang', 'Shorkot Road',
                'Ahmadpur Sial Road', 'Chak No 1', 'Chak No 2', 'Basti Kharal', 'Jhang Bypass', 'Jhang Railway Station',
                'Chenab Colony', 'Ghaznavi Gate', 'Kotwali Road', 'University Road Jhang'
            ]
        ],
        'Jhang Sadr' => [
            'country' => 'PK',
            'areas' => [
                'Jhang Sadr', 'Jhang City (twin)', 'Basti Arain', 'Kotwali Bazar', 'Railway Road',
                'Jhang Sadr Area', 'Cantt Area Jhang', 'Sadr Bazaar', 'Gulshan-e-Sadr', 'Madina Colony Sadr',
                'Peoples Colony Sadr', 'Satellite Town Sadr', 'Jinnah Colony Sadr', 'Amin Town Jhang',
                'Faisal Colony', 'Jhang Sadr Bypass', 'Shahbaz Nagar'
            ]
        ],
        'Jhawarian' => [
            'country' => 'PK',
            'areas' => [
                'Jhawarian Town', 'Sargodha (part)', 'Shahpur (part)', 'Bhalwal (part)', 'Basti Jhawarian',
                'Jhawarian City', 'Jhawarian Cantt', 'Sargodha Road', 'Jhawarian Bazaar', 'Gulshan-e-Jhawarian',
                'Madina Colony Jhawarian', 'Peoples Colony Jhawarian', 'Jhawarian Model Town', 'Jinnah Colony Jhawarian',
                'Shahpur Road', 'Bhalwal Road', 'Chak No 1', 'Basti Kharal', 'Jhawarian Bypass'
            ]
        ],
        'Jhelum' => [
            'country' => 'PK',
            'areas' => [
                'Jhelum City', 'Jhelum Cantt', 'Dina', 'Sohawa', 'Pind Dadan Khan',
                'Railway Colony', 'Model Town', 'Jhelum City Area', 'G.T. Road Jhelum', 'Jhelum Bazaar',
                'Gulshan-e-Jhelum', 'Madina Colony Jhelum', 'Peoples Colony Jhelum', 'Satellite Town Jhelum',
                'Jinnah Colony Jhelum', 'Dina Road', 'Sohawa Road', 'Pind Dadan Khan Road', 'Chak No 1',
                'Basti Awan', 'Jhelum Bypass', 'Jhelum Railway Station', 'River Jhelum View', 'Kala Jhelum',
                'Tariqabad', 'Cantt Bazar Jhelum'
            ]
        ],
        'Jhol' => [
            'country' => 'PK',
            'areas' => [
                'Jhol Town', 'Sanghar (part)', 'Tando Adam (part)', 'Khipro', 'Basti Jhol', 'Jhol Bazar',
                'Jhol City', 'Jhol Cantt', 'Sanghar Road', 'Jhol Bazaar', 'Gulshan-e-Jhol', 'Madina Colony Jhol',
                'Peoples Colony Jhol', 'Jhol Model Town', 'Jinnah Colony Jhol', 'Tando Adam Road',
                'Khipro Road', 'Basti Malkani', 'Jhol Bypass'
            ]
        ],
        'Jiwani' => [
            'country' => 'PK',
            'areas' => [
                'Jiwani Town', 'Gwadar (part)', 'Pasni', 'Jiwani Port Area', 'Basti Jiwani', 'Jiwani City',
                'Jiwani Cantt', 'Gwadar Road Jiwani', 'Jiwani Bazaar', 'Gulshan-e-Jiwani', 'Madina Colony Jiwani',
                'Peoples Colony Jiwani', 'Jiwani Model Town', 'Jinnah Colony Jiwani', 'Pasni Road',
                'Basti Baloch', 'Jiwani Bypass', 'Jiwani Beach', 'Jiwani Airport Area'
            ]
        ],
        'Johi' => [
            'country' => 'PK',
            'areas' => [
                'Johi Town', 'Dadu (part)', 'Sehwan Sharif', 'Mehar', 'Basti Johi', 'Johi City',
                'Johi Cantt', 'Dadu Road', 'Johi Bazaar', 'Gulshan-e-Johi', 'Madina Colony Johi',
                'Peoples Colony Johi', 'Johi Model Town', 'Jinnah Colony Johi', 'Sehwan Road',
                'Mehar Road', 'Basti Khosa', 'Johi Bypass'
            ]
        ],
        'Jām Sāhib' => [
            'country' => 'PK',
            'areas' => [
                'Jam Sahib Town', 'Sanghar (part)', 'Shahdadpur', 'Jam Nawaz Ali', 'Basti Jam',
                'Jam Sahib City', 'Jam Sahib Cantt', 'Sanghar Road', 'Jam Sahib Bazaar', 'Gulshan-e-Jam',
                'Madina Colony Jam', 'Peoples Colony Jam', 'Jam Sahib Model Town', 'Jinnah Colony Jam',
                'Shahdadpur Road', 'Jam Nawaz Ali Road', 'Basti Malkani', 'Jam Sahib Bypass'
            ]
        ],
                'Kabirwala' => [
            'country' => 'PK',
            'areas' => [
                'Kabirwala City', 'Khanewal (part)', 'Mian Channu', 'Jahanian Shah', 'Basti Kabir',
                'Kabirwala Cantt', 'Khanewal Road Kabirwala', 'Kabirwala Bazaar', 'Gulshan-e-Kabir',
                'Madina Colony Kabirwala', 'Peoples Colony Kabirwala', 'Kabirwala Model Town', 'Jinnah Colony Kabirwala',
                'Mian Channu Road', 'Jahanian Shah Road', 'Chak No 1', 'Chak No 2', 'Basti Kharal', 'Basti Jattan',
                'Kabirwala Bypass', 'Kabirwala Railway Station'
            ]
        ],
        'Kadhan' => [
            'country' => 'PK',
            'areas' => [
                'Kadhan Town', 'Badin (part)', 'Tando Bago', 'Matli', 'Basti Kadhan', 'Kadhan City',
                'Kadhan Cantt', 'Badin Road Kadhan', 'Kadhan Bazaar', 'Gulshan-e-Kadhan', 'Madina Colony Kadhan',
                'Peoples Colony Kadhan', 'Kadhan Model Town', 'Jinnah Colony Kadhan', 'Tando Bago Road',
                'Matli Road', 'Basti Mallah', 'Basti Mirza', 'Kadhan Bypass'
            ]
        ],
        'Kahna Nau' => [
            'country' => 'PK',
            'areas' => [
                'Kahna Nau Town', 'Lahore (part)', 'Raiwind', 'Basti Kahna', 'Model Town',
                'Kahna Nau City', 'Lahore Road Kahna', 'Kahna Nau Bazaar', 'Gulshan-e-Kahna', 'Madina Colony Kahna',
                'Peoples Colony Kahna', 'Satellite Town Kahna', 'Jinnah Colony Kahna', 'Raiwind Road',
                'Chak No 1', 'Basti Gujjar', 'Kahna Nau Bypass', 'Kahna Railway Station', 'Nishtar Colony (Kahna)'
            ]
        ],
        'Kahror Pakka' => [
            'country' => 'PK',
            'areas' => [
                'Kahror Pakka City (Kahror Pacca)', 'Lodhran (part)', 'Dunyapur', 'Mailsi', 'Basti Kahror',
                'Kahror Pakka Cantt', 'Lodhran Road', 'Kahror Pakka Bazaar', 'Gulshan-e-Kahror', 'Madina Colony Kahror',
                'Peoples Colony Kahror', 'Kahror Pakka Model Town', 'Jinnah Colony Kahror', 'Dunyapur Road',
                'Mailsi Road', 'Chak No 1', 'Chak No 2', 'Basti Khokhar', 'Kahror Bypass'
            ]
        ],
        'Kahuta' => [
            'country' => 'PK',
            'areas' => [
                'Kahuta Town', 'Kahuta Cantt', 'Sehala', 'Kotali', 'Mohallah Rajput', 'Railway Colony',
                'Kahuta City Area', 'Kahuta Bazaar', 'Gulshan-e-Kahuta', 'Madina Colony Kahuta', 'Peoples Colony Kahuta',
                'Kahuta Model Town', 'Jinnah Colony Kahuta', 'Sehala Road', 'Kotali Road', 'Basti Kahuta',
                'Kahuta Bypass', 'Kahuta Fort Area', 'Poona Kahuta', 'Jabbi'
            ]
        ],
        'Kakad Wari Dir Upper' => [
            'country' => 'PK',
            'areas' => [
                'Kakad Wari Town', 'Dir (part)', 'Wari', 'Sheringal', 'Khall', 'Basti Kakad',
                'Kakad Wari City', 'Dir Road Kakad', 'Kakad Wari Bazaar', 'Gulshan-e-Kakad', 'Madina Colony Kakad',
                'Peoples Colony Kakad', 'Kakad Wari Model Town', 'Jinnah Colony Kakad', 'Wari Road', 'Sheringal Road',
                'Khall Road', 'Basti Khan', 'Basti Gujjar', 'Kakad Bypass'
            ]
        ],
        'Kalabagh' => [
            'country' => 'PK',
            'areas' => [
                'Kalabagh Town', 'Mianwali (part)', 'Kamar Mushani', 'Kalabagh Cantt', 'Salt Range Area',
                'Kalabagh City', 'Mianwali Road Kalabagh', 'Kalabagh Bazaar', 'Gulshan-e-Kalabagh', 'Madina Colony Kalabagh',
                'Peoples Colony Kalabagh', 'Kalabagh Model Town', 'Jinnah Colony Kalabagh', 'Kamar Mushani Road',
                'Basti Khel', 'Kalabagh Bypass', 'Indus River View Area', 'Kalabagh Fort Area'
            ]
        ],
        'Kalaswala' => [
            'country' => 'PK',
            'areas' => [
                'Kalaswala Town', 'Sialkot (part)', 'Pasrur', 'Kotli Loharan', 'Basti Kalas', 'Kalaswala City',
                'Kalaswala Cantt', 'Sialkot Road Kalaswala', 'Kalaswala Bazaar', 'Gulshan-e-Kalaswala',
                'Madina Colony Kalaswala', 'Peoples Colony Kalaswala', 'Kalaswala Model Town', 'Jinnah Colony Kalaswala',
                'Pasrur Road', 'Kotli Loharan Road', 'Chak No 1', 'Basti Bhatti', 'Kalaswala Bypass'
            ]
        ],
        'Kalat' => [
            'country' => 'PK',
            'areas' => [
                'Kalat City', 'Kalat Cantt', 'Mangochar', 'Surab', 'Killi Kalat', 'Model Town',
                'Kalat City Area', 'Quetta Road Kalat', 'Kalat Bazaar', 'Gulshan-e-Kalat', 'Madina Colony Kalat',
                'Peoples Colony Kalat', 'Satellite Town Kalat', 'Jinnah Colony Kalat', 'Mangochar Road', 'Surab Road',
                'Killi Kakar', 'Killi Ahmadzai', 'Kalat Bypass', 'Kalat Fort Area', 'Kalat Valley'
            ]
        ],
        'Kaleke Mandi' => [
            'country' => 'PK',
            'areas' => [
                'Kaleke Mandi Town', 'Gujranwala (part)', 'Kamoke', 'Eminabad', 'Basti Kaleke', 'Kaleke Mandi City',
                'Kaleke Mandi Cantt', 'Gujranwala Road Kaleke', 'Kaleke Bazaar', 'Gulshan-e-Kaleke',
                'Madina Colony Kaleke', 'Peoples Colony Kaleke', 'Kaleke Mandi Model Town', 'Jinnah Colony Kaleke',
                'Kamoke Road', 'Eminabad Road', 'Chak No 1', 'Basti Cheema', 'Kaleke Bypass'
            ]
        ],
        'Kallar Kahar' => [
            'country' => 'PK',
            'areas' => [
                'Kallar Kahar Town', 'Chakwal (part)', 'Choa Saidan Shah', 'Kallar Kahar Lake Area',
                'Katas Raj Area', 'Kallar Kahar City', 'Kallar Kahar Cantt', 'Chakwal Road', 'Kallar Kahar Bazaar',
                'Gulshan-e-Kallar', 'Madina Colony Kallar', 'Peoples Colony Kallar', 'Kallar Kahar Model Town',
                'Jinnah Colony Kallar', 'Choa Saidan Shah Road', 'Katas Raj Road', 'Basti Awan',
                'Kallar Kahar Bypass', 'Kallar Kahar Hill Station Area', 'Kallar Kahar Lake View'
            ]
        ],
        'Kalur Kot' => [
            'country' => 'PK',
            'areas' => [
                'Kalur Kot Town', 'Bhakkar (part)', 'Darya Khan', 'Mankera', 'Basti Kalur', 'Kalur Kot City',
                'Kalur Kot Cantt', 'Bhakkar Road', 'Kalur Kot Bazaar', 'Gulshan-e-Kalur', 'Madina Colony Kalur',
                'Peoples Colony Kalur', 'Kalur Kot Model Town', 'Jinnah Colony Kalur', 'Darya Khan Road',
                'Mankera Road', 'Chak No 1', 'Basti Khar', 'Kalur Kot Bypass'
            ]
        ],
        'Kamalia' => [
            'country' => 'PK',
            'areas' => [
                'Kamalia City', 'Toba Tek Singh (part)', 'Gojra', 'Pir Mahal', 'Basti Kamalia', 'Kamalia Cantt',
                'Toba Tek Singh Road', 'Kamalia Bazaar', 'Gulshan-e-Kamalia', 'Madina Colony Kamalia',
                'Peoples Colony Kamalia', 'Satellite Town Kamalia', 'Jinnah Colony Kamalia', 'Gojra Road',
                'Pir Mahal Road', 'Chak No 1', 'Chak No 2', 'Basti Kharal', 'Kamalia Bypass', 'Kamalia Railway Station'
            ]
        ],
        'Kamar Mushani' => [
            'country' => 'PK',
            'areas' => [
                'Kamar Mushani Town', 'Mianwali (part)', 'Kalabagh', 'Musa Khel', 'Basti Kamar', 'Kamar Mushani City',
                'Kamar Mushani Cantt', 'Mianwali Road', 'Kamar Mushani Bazaar', 'Gulshan-e-Kamar', 'Madina Colony Kamar',
                'Peoples Colony Kamar', 'Kamar Mushani Model Town', 'Jinnah Colony Kamar', 'Kalabagh Road',
                'Musa Khel Road', 'Basti Khel', 'Kamar Bypass'
            ]
        ],
        'Kambar' => [
            'country' => 'PK',
            'areas' => [
                'Kambar City', 'Larkana (part)', 'Shahdadkot', 'Miro Khan', 'Basti Kambar', 'Kambar Cantt',
                'Larkana Road Kambar', 'Kambar Bazaar', 'Gulshan-e-Kambar', 'Madina Colony Kambar',
                'Peoples Colony Kambar', 'Satellite Town Kambar', 'Jinnah Colony Kambar', 'Shahdadkot Road',
                'Miro Khan Road', 'Basti Khoso', 'Basti Buriro', 'Kambar Bypass'
            ]
        ],
        'Kamoke' => [
            'country' => 'PK',
            'areas' => [
                'Kamoke City', 'Gujranwala (part)', 'Eminabad', 'Qila Didar Singh', 'Model Town', 'Kamoke Cantt',
                'Gujranwala Road Kamoke', 'Kamoke Bazaar', 'Gulshan-e-Kamoke', 'Madina Colony Kamoke',
                'Peoples Colony Kamoke', 'Satellite Town Kamoke', 'Jinnah Colony Kamoke', 'Eminabad Road',
                'Qila Didar Singh Road', 'Chak No 1', 'Basti Cheema', 'Kamoke Bypass', 'Kamoke Railway Station'
            ]
        ],
        'Kamra' => [
            'country' => 'PK',
            'areas' => [
                'Kamra Town', 'Attock (part)', 'Kamra Cantt', 'Kamra Airbase Area', 'Basti Kamra', 'Kamra City',
                'Attock Road Kamra', 'Kamra Bazaar', 'Gulshan-e-Kamra', 'Madina Colony Kamra', 'Peoples Colony Kamra',
                'Kamra Model Town', 'Jinnah Colony Kamra', 'Hazro Road', 'Basti Khel', 'Kamra Bypass',
                'Kamra Airbase Colony', 'PAF Kamra Officers Colony'
            ]
        ],
        'Kandhkot' => [
            'country' => 'PK',
            'areas' => [
                'Kandhkot City', 'Kashmore (part)', 'Ghauspur', 'Tangwani', 'Basti Kandh', 'Kandhkot Cantt',
                'Kashmore Road', 'Kandhkot Bazaar', 'Gulshan-e-Kandhkot', 'Madina Colony Kandhkot',
                'Peoples Colony Kandhkot', 'Satellite Town Kandhkot', 'Jinnah Colony Kandhkot', 'Ghauspur Road',
                'Tangwani Road', 'Basti Khoso', 'Basti Baloch', 'Kandhkot Bypass', 'Kandhkot Railway Station'
            ]
        ],
        'Kandiari' => [
            'country' => 'PK',
            'areas' => [
                'Kandiari Town', 'Nawabshah (part)', 'Daur', 'Basti Kandiari', 'Kandiari Bazar', 'Kandiari City',
                'Kandiari Cantt', 'Nawabshah Road', 'Kandiari Bazaar', 'Gulshan-e-Kandiari', 'Madina Colony Kandiari',
                'Peoples Colony Kandiari', 'Kandiari Model Town', 'Jinnah Colony Kandiari', 'Daur Road',
                'Basti Jatoi', 'Basti Bukhari', 'Kandiari Bypass'
            ]
        ],
        'Kandiaro' => [
            'country' => 'PK',
            'areas' => [
                'Kandiaro Town', 'Naushahro Feroze (part)', 'Bhiria', 'Moro', 'Basti Kandiaro', 'Kandiaro City',
                'Kandiaro Cantt', 'Naushahro Feroze Road', 'Kandiaro Bazaar', 'Gulshan-e-Kandiaro',
                'Madina Colony Kandiaro', 'Peoples Colony Kandiaro', 'Kandiaro Model Town', 'Jinnah Colony Kandiaro',
                'Bhiria Road', 'Moro Road', 'Basti Bhutto', 'Basti Unar', 'Kandiaro Bypass'
            ]
        ],
        'Kanganpur' => [
            'country' => 'PK',
            'areas' => [
                'Kanganpur Town', 'Kasur (part)', 'Chunian', 'Kot Radha Kishan', 'Basti Kangan', 'Kanganpur City',
                'Kanganpur Cantt', 'Kasur Road', 'Kanganpur Bazaar', 'Gulshan-e-Kangan', 'Madina Colony Kangan',
                'Peoples Colony Kangan', 'Kanganpur Model Town', 'Jinnah Colony Kangan', 'Chunian Road',
                'Kot Radha Kishan Road', 'Chak No 1', 'Basti Kamboh', 'Kanganpur Bypass'
            ]
        ],
        'Karachi' => [
            'country' => 'PK',
            'areas' => [
                'Karachi City', 'Karachi Cantt', 'Saddar', 'Clifton', 'Defence Housing Authority (DHA)',
                'Gulshan-e-Iqbal', 'Gulistan-e-Jauhar', 'North Nazimabad', 'Korangi', 'Landhi',
                'Malir', 'Lyari', 'Orangi Town', 'Baldia Town', 'Surjani Town', 'Shah Faisal Colony',
                'Model Colony', 'Kiamari', 'Port Qasim', 'Bin Qasim Town', 'Gadap Town', 'Manghopir',
                'New Karachi', 'Federal B Area', 'Gulshan-e-Maymar', 'SITE Area', 'Shershah', 'Mauripur',
                'Keamari', 'Manora', 'Hawkesbay', 'Saeedabad', 'Metroville', 'Nazimabad', 'Liaquatabad',
                'Paposh Nagar', 'Karachi Bypass', 'Super Highway', 'Shahrah-e-Faisal', 'Korangi Creek',
                'Gulshan e hadeed', 'Gulshan e maymar', 'Gulshan e iqbal', 'Gulistan e jauhar', 'North nazimabad', 'Korangi',
                'Ibrahim Hyderi', 'Rehri Goth', 'Bhains Colony', 'DHA City', 'Bahria Town Karachi'
            ]
        ],
        'Karak' => [
            'country' => 'PK',
            'areas' => [
                'Karak City', 'Karak Cantt', 'Takht-e-Nasrati', 'Banda Daud Shah', 'Tari Khel', 'Basti Karak',
                'Karak City Area', 'Kohat Road Karak', 'Karak Bazaar', 'Gulshan-e-Karak', 'Madina Colony Karak',
                'Peoples Colony Karak', 'Satellite Town Karak', 'Jinnah Colony Karak', 'Takht-e-Nasrati Road',
                'Banda Daud Shah Road', 'Tari Khel Road', 'Basti Khel', 'Karak Bypass', 'Karak Fort Area'
            ]
        ],
        'Karaundi' => [
            'country' => 'PK',
            'areas' => [
                'Karaundi Town', 'Nawabshah (part)', 'Daur', 'Sakrand', 'Basti Karaundi', 'Karaundi City',
                'Karaundi Cantt', 'Nawabshah Road', 'Karaundi Bazaar', 'Gulshan-e-Karaundi', 'Madina Colony Karaundi',
                'Peoples Colony Karaundi', 'Karaundi Model Town', 'Jinnah Colony Karaundi', 'Daur Road',
                'Sakrand Road', 'Basti Jatoi', 'Karaundi Bypass'
            ]
        ],
        'Kario Ghanwar' => [
            'country' => 'PK',
            'areas' => [
                'Kario Ghanwar Town', 'Badin (part)', 'Tando Bago', 'Golarchi', 'Basti Kario', 'Kario Ghanwar City',
                'Kario Ghanwar Cantt', 'Badin Road', 'Kario Bazaar', 'Gulshan-e-Kario', 'Madina Colony Kario',
                'Peoples Colony Kario', 'Kario Ghanwar Model Town', 'Jinnah Colony Kario', 'Tando Bago Road',
                'Golarchi Road', 'Basti Mallah', 'Kario Bypass'
            ]
        ],
        'Karor' => [
            'country' => 'PK',
            'areas' => [
                'Karor Town (Karor Lal Esan)', 'Layyah (part)', 'Chak 46/TDA', 'Basti Karor', 'Karor City',
                'Karor Cantt', 'Layyah Road', 'Karor Bazaar', 'Gulshan-e-Karor', 'Madina Colony Karor',
                'Peoples Colony Karor', 'Karor Model Town', 'Jinnah Colony Karor', 'Chak 46/TDA Road',
                'Basti Kharal', 'Karor Bypass', 'Karor Lal Esan Shrine Area'
            ]
        ],
        'Kashmor' => [
            'country' => 'PK',
            'areas' => [
                'Kashmor City', 'Kandhkot', 'Ghauspur', 'Tangwani', 'Basti Kashmor', 'Kashmor Cantt',
                'Kandhkot Road', 'Kashmor Bazaar', 'Gulshan-e-Kashmor', 'Madina Colony Kashmor',
                'Peoples Colony Kashmor', 'Satellite Town Kashmor', 'Jinnah Colony Kashmor', 'Ghauspur Road',
                'Tangwani Road', 'Basti Khoso', 'Kashmor Bypass', 'Kashmor Railway Station'
            ]
        ],
        'Kasur' => [
            'country' => 'PK',
            'areas' => [
                'Kasur City', 'Kasur Cantt', 'Chunian', 'Kanganpur', 'Pattoki', 'Model Town', 'Railway Colony',
                'Kasur City Area', 'Lahore Road Kasur', 'Kasur Bazaar', 'Gulshan-e-Kasur', 'Madina Colony Kasur',
                'Peoples Colony Kasur', 'Satellite Town Kasur', 'Jinnah Colony Kasur', 'Chunian Road',
                'Kanganpur Road', 'Pattoki Road', 'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan',
                'Kasur Bypass', 'Kasur Fort Area', 'River Ravi View', 'Ganda Singh Wala Border Area'
            ]
        ],
        'Keshupur' => [
            'country' => 'PK',
            'areas' => [
                'Keshupur Town', 'Sargodha (part)', 'Jhawarian', 'Shahpur', 'Basti Keshupur', 'Keshupur City',
                'Keshupur Cantt', 'Sargodha Road', 'Keshupur Bazaar', 'Gulshan-e-Keshupur', 'Madina Colony Keshupur',
                'Peoples Colony Keshupur', 'Keshupur Model Town', 'Jinnah Colony Keshupur', 'Jhawarian Road',
                'Shahpur Road', 'Chak No 1', 'Basti Kharal', 'Keshupur Bypass'
            ]
        ],
        'Keti Bandar' => [
            'country' => 'PK',
            'areas' => [
                'Keti Bandar Town', 'Thatta (part)', 'Kharo Chan', 'Keti Bandar Port Area', 'Basti Keti',
                'Keti Bandar City', 'Thatta Road', 'Keti Bandar Bazaar', 'Gulshan-e-Keti', 'Madina Colony Keti',
                'Peoples Colony Keti', 'Keti Bandar Model Town', 'Jinnah Colony Keti', 'Kharo Chan Road',
                'Basti Mallah', 'Keti Bandar Beach', 'Keti Bandar Fish Harbour', 'Indus Delta Area'
            ]
        ],
        'Khadan Khak' => [
            'country' => 'PK',
            'areas' => [
                'Khadan Khak Town', 'Chaman (part)', 'Killi Khadan', 'Basti Khak', 'Khadan Khak City',
                'Khadan Khak Cantt', 'Chaman Road', 'Khadan Khak Bazaar', 'Gulshan-e-Khadan', 'Madina Colony Khadan',
                'Peoples Colony Khadan', 'Khadan Khak Model Town', 'Jinnah Colony Khadan', 'Killi Kakar',
                'Basti Malik', 'Khadan Khak Bypass'
            ]
        ],
        'Khadro' => [
            'country' => 'PK',
            'areas' => [
                'Khadro Town', 'Sanghar (part)', 'Shahdadpur', 'Jam Nawaz Ali', 'Basti Khadro', 'Khadro City',
                'Khadro Cantt', 'Sanghar Road', 'Khadro Bazaar', 'Gulshan-e-Khadro', 'Madina Colony Khadro',
                'Peoples Colony Khadro', 'Khadro Model Town', 'Jinnah Colony Khadro', 'Shahdadpur Road',
                'Jam Nawaz Ali Road', 'Basti Malkani', 'Khadro Bypass'
            ]
        ],
        'Khairpur' => [
            'country' => 'PK',
            'areas' => [
                'Khairpur City (Khairpur Mirs)', 'Khairpur Cantt', 'Gambat', 'Kingri', 'Kot Diji', 'Model Town',
                'Khairpur City Area', 'Sukkur Road Khairpur', 'Khairpur Bazaar', 'Gulshan-e-Khairpur',
                'Madina Colony Khairpur', 'Peoples Colony Khairpur', 'Satellite Town Khairpur', 'Jinnah Colony Khairpur',
                'Gambat Road', 'Kingri Road', 'Kot Diji Road', 'Basti Soomro', 'Basti Khairpur',
                'Khairpur Bypass', 'Khairpur Railway Station', 'Kot Diji Fort Area', 'Faiz Ganj', 'Mirwah'
            ]
        ],
        'Khairpur Mir’s' => [
            'country' => 'PK',
            'areas' => [
                'Khairpur Mirs City', 'Ahmedpur (nearby)', 'Thari Mirwah', 'Basti Khairpur', 'Khairpur Mirs Cantt',
                'Khairpur Mirs City Area', 'Sukkur Road', 'Khairpur Mirs Bazaar', 'Gulshan-e-Khairpur Mirs',
                'Madina Colony Khairpur Mirs', 'Peoples Colony Khairpur Mirs', 'Satellite Town Khairpur Mirs',
                'Jinnah Colony Khairpur Mirs', 'Ahmedpur Road', 'Thari Mirwah Road', 'Basti Soomro',
                'Khairpur Mirs Bypass', 'Faiz Mahal Area'
            ]
        ],
        'Khairpur Nathan Shah' => [
            'country' => 'PK',
            'areas' => [
                'Khairpur Nathan Shah Town', 'Dadu (part)', 'Mehar', 'Johi', 'Basti Nathan', 'Khairpur Nathan Shah City',
                'Khairpur Nathan Shah Cantt', 'Dadu Road', 'Khairpur Nathan Shah Bazaar', 'Gulshan-e-Khairpur Nathan',
                'Madina Colony Nathan', 'Peoples Colony Nathan', 'Khairpur Nathan Model Town', 'Jinnah Colony Nathan',
                'Mehar Road', 'Johi Road', 'Basti Khosa', 'Khairpur Nathan Bypass'
            ]
        ],
        'Khairpur Tamewah' => [
            'country' => 'PK',
            'areas' => [
                'Khairpur Tamewah Town', 'Bahawalpur (part)', 'Hasilpur', 'Yazman', 'Basti Tamewah', 'Khairpur Tamewah City',
                'Khairpur Tamewah Cantt', 'Bahawalpur Road', 'Khairpur Tamewah Bazaar', 'Gulshan-e-Tamewah',
                'Madina Colony Tamewah', 'Peoples Colony Tamewah', 'Khairpur Tamewah Model Town', 'Jinnah Colony Tamewah',
                'Hasilpur Road', 'Yazman Road', 'Chak No 1', 'Basti Khokhar', 'Tamewah Bypass'
            ]
        ],
        'Khalabat' => [
            'country' => 'PK',
            'areas' => [
                'Khalabat Township', 'Haripur (part)', 'Khanpur (part)', 'Khalabat Bazar', 'Mohallah Khalabat',
                'Khalabat City', 'Khalabat Cantt', 'Haripur Road', 'Khalabat Bazaar', 'Gulshan-e-Khalabat',
                'Madina Colony Khalabat', 'Peoples Colony Khalabat', 'Khalabat Model Town', 'Jinnah Colony Khalabat',
                'Khanpur Road', 'Basti Khalabat', 'Khalabat Bypass', 'Khalabat Dam View'
            ]
        ],
        'Khandowa' => [
            'country' => 'PK',
            'areas' => [
                'Khandowa Town', 'Chakwal (part)', 'Kallar Kahar', 'Mohallah Awan', 'Basti Khandowa', 'Khandowa City',
                'Khandowa Cantt', 'Chakwal Road', 'Khandowa Bazaar', 'Gulshan-e-Khandowa', 'Madina Colony Khandowa',
                'Peoples Colony Khandowa', 'Khandowa Model Town', 'Jinnah Colony Khandowa', 'Kallar Kahar Road',
                'Basti Awan', 'Khandowa Bypass'
            ]
        ],
        'Khanewal' => [
            'country' => 'PK',
            'areas' => [
                'Khanewal City', 'Kabirwala', 'Mian Channu', 'Jahanian Shah', 'Model Town', 'Railway Colony',
                'Khanewal Cantt', 'Multan Road Khanewal', 'Khanewal Bazaar', 'Gulshan-e-Khanewal',
                'Madina Colony Khanewal', 'Peoples Colony Khanewal', 'Satellite Town Khanewal', 'Jinnah Colony Khanewal',
                'Kabirwala Road', 'Mian Channu Road', 'Jahanian Shah Road', 'Chak No 1', 'Chak No 2',
                'Basti Kharal', 'Basti Jattan', 'Khanewal Bypass', 'Khanewal Railway Station'
            ]
        ],
        'Khangah Dogran' => [
            'country' => 'PK',
            'areas' => [
                'Khangah Dogran Town', 'Sheikhupura (part)', 'Nankana Sahib (part)', 'Basti Dogran', 'Khangah Dogran City',
                'Khangah Dogran Cantt', 'Sheikhupura Road', 'Khangah Dogran Bazaar', 'Gulshan-e-Dogran',
                'Madina Colony Dogran', 'Peoples Colony Dogran', 'Khangah Dogran Model Town', 'Jinnah Colony Dogran',
                'Nankana Sahib Road', 'Chak No 1', 'Basti Gujjar', 'Dogran Bypass'
            ]
        ],
        'Khangarh' => [
            'country' => 'PK',
            'areas' => [
                'Khangarh Town', 'Muzaffargarh (part)', 'Alipur', 'Jatoi', 'Basti Khangarh', 'Khangarh City',
                'Khangarh Cantt', 'Muzaffargarh Road', 'Khangarh Bazaar', 'Gulshan-e-Khangarh', 'Madina Colony Khangarh',
                'Peoples Colony Khangarh', 'Khangarh Model Town', 'Jinnah Colony Khangarh', 'Alipur Road',
                'Jatoi Road', 'Chak No 1', 'Basti Khar', 'Khangarh Bypass'
            ]
        ],
        'Khanpur' => [
            'country' => 'PK',
            'areas' => [
                'Khanpur City', 'Rahim Yar Khan (part)', 'Sadiqabad', 'Zahir Pir', 'Basti Khanpur', 'Khanpur Cantt',
                'Rahim Yar Khan Road', 'Khanpur Bazaar', 'Gulshan-e-Khanpur', 'Madina Colony Khanpur',
                'Peoples Colony Khanpur', 'Satellite Town Khanpur', 'Jinnah Colony Khanpur', 'Sadiqabad Road',
                'Zahir Pir Road', 'Chak No 1', 'Chak No 2', 'Basti Khokhar', 'Khanpur Bypass', 'Khanpur Railway Station'
            ]
        ],
        'Khanpur Mahar' => [
            'country' => 'PK',
            'areas' => [
                'Khanpur Mahar Town', 'Ghotki (part)', 'Daharki', 'Mirpur Mathelo', 'Basti Mahar', 'Khanpur Mahar City',
                'Khanpur Mahar Cantt', 'Ghotki Road', 'Khanpur Mahar Bazaar', 'Gulshan-e-Mahar', 'Madina Colony Mahar',
                'Peoples Colony Mahar', 'Khanpur Mahar Model Town', 'Jinnah Colony Mahar', 'Daharki Road',
                'Mirpur Mathelo Road', 'Basti Khoso', 'Khanpur Mahar Bypass'
            ]
        ],
        'Kharan' => [
            'country' => 'PK',
            'areas' => [
                'Kharan City', 'Kharan Cantt', 'Washuk (part)', 'Killi Kharan', 'Basti Kharan', 'Kharan City Area',
                'Quetta Road Kharan', 'Kharan Bazaar', 'Gulshan-e-Kharan', 'Madina Colony Kharan',
                'Peoples Colony Kharan', 'Satellite Town Kharan', 'Jinnah Colony Kharan', 'Washuk Road',
                'Killi Kakar', 'Kharan Bypass', 'Kharan Fort Area', 'Kharan Desert Area'
            ]
        ],
        'Kharian' => [
            'country' => 'PK',
            'areas' => [
                'Kharian City', 'Gujrat (part)', 'Dinga', 'Jalalpur Jattan', 'Kharian Cantt', 'Model Town',
                'Kharian City Area', 'Gujrat Road Kharian', 'Kharian Bazaar', 'Gulshan-e-Kharian',
                'Madina Colony Kharian', 'Peoples Colony Kharian', 'Satellite Town Kharian', 'Jinnah Colony Kharian',
                'Dinga Road', 'Jalalpur Jattan Road', 'Chak No 1', 'Basti Cheema', 'Kharian Bypass',
                'Kharian Railway Station', 'Cantt Bazar Kharian'
            ]
        ],
        'Khewra' => [
            'country' => 'PK',
            'areas' => [
                'Khewra Town', 'Jhelum (part)', 'Pind Dadan Khan', 'Khewra Salt Mines Area', 'Basti Khewra',
                'Khewra City', 'Khewra Cantt', 'Jhelum Road', 'Khewra Bazaar', 'Gulshan-e-Khewra',
                'Madina Colony Khewra', 'Peoples Colony Khewra', 'Khewra Model Town', 'Jinnah Colony Khewra',
                'Pind Dadan Khan Road', 'Chak No 1', 'Basti Kharal', 'Khewra Bypass', 'Khewra Salt Mines Colony',
                'Mall Road Khewra', 'Khewra Museum Area'
            ]
        ],
        'Khurrianwala' => [
            'country' => 'PK',
            'areas' => [
                'Khurrianwala Town', 'Faisalabad (part)', 'Jaranwala', 'Chak 46 JB', 'Basti Khurrian', 'Khurrianwala City',
                'Khurrianwala Cantt', 'Faisalabad Road Khurrian', 'Khurrianwala Bazaar', 'Gulshan-e-Khurrian',
                'Madina Colony Khurrian', 'Peoples Colony Khurrian', 'Khurrianwala Model Town', 'Jinnah Colony Khurrian',
                'Jaranwala Road', 'Chak No 1', 'Chak No 2', 'Basti Kamboh', 'Khurrianwala Bypass'
            ]
        ],
        'Khushāb' => [
            'country' => 'PK',
            'areas' => [
                'Khushab City', 'Khushab Cantt', 'Jauharabad', 'Quaidabad', 'Model Town', 'Khushab City Area',
                'Sargodha Road Khushab', 'Khushab Bazaar', 'Gulshan-e-Khushab', 'Madina Colony Khushab',
                'Peoples Colony Khushab', 'Satellite Town Khushab', 'Jinnah Colony Khushab', 'Jauharabad Road',
                'Quaidabad Road', 'Chak No 1', 'Chak No 2', 'Basti Kharal', 'Khushab Bypass', 'Khushab Fort Area',
                'Soon Valley', 'Naushera', 'Khushab Railway Station'
            ]
        ],
        'Khuzdar' => [
            'country' => 'PK',
            'areas' => [
                'Khuzdar City', 'Khuzdar Cantt', 'Wadh', 'Naal', 'Killi Khuzdar', 'Model Town',
                'Khuzdar City Area', 'Quetta Road Khuzdar', 'Khuzdar Bazaar', 'Gulshan-e-Khuzdar',
                'Madina Colony Khuzdar', 'Peoples Colony Khuzdar', 'Satellite Town Khuzdar', 'Jinnah Colony Khuzdar',
                'Wadh Road', 'Naal Road', 'Killi Kakar', 'Khuzdar Bypass', 'Khuzdar Fort Area', 'Mula River Area'
            ]
        ],
        'Kohat' => [
            'country' => 'PK',
            'areas' => [
                'Kohat City', 'Kohat Cantt', 'Lachi', 'Dara Adam Khel', 'Kacha Pacca', 'Model Town',
                'Kohat City Area', 'Peshawar Road Kohat', 'Kohat Bazaar', 'Gulshan-e-Kohat', 'Madina Colony Kohat',
                'Peoples Colony Kohat', 'Satellite Town Kohat', 'Jinnah Colony Kohat', 'Lachi Road',
                'Dara Adam Khel Road', 'Basti Khel', 'Kohat Bypass', 'Kohat Fort Area', 'Kohat Tunnel Area',
                'Jarma', 'Shakardara', 'Kohat Railway Station'
            ]
        ],
        'Kohlu' => [
            'country' => 'PK',
            'areas' => [
                'Kohlu Town', 'Kohlu Cantt', 'Kahan', 'Killi Kohlu', 'Basti Kohlu', 'Kohlu City',
                'Sibi Road Kohlu', 'Kohlu Bazaar', 'Gulshan-e-Kohlu', 'Madina Colony Kohlu', 'Peoples Colony Kohlu',
                'Kohlu Model Town', 'Jinnah Colony Kohlu', 'Kahan Road', 'Killi Kakar', 'Kohlu Bypass'
            ]
        ],
        'Kot Addu' => [
            'country' => 'PK',
            'areas' => [
                'Kot Addu City', 'Muzaffargarh (part)', 'Alipur (part)', 'Jatoi', 'Model Town', 'Kot Addu Cantt',
                'Muzaffargarh Road', 'Kot Addu Bazaar', 'Gulshan-e-Kot Addu', 'Madina Colony Kot Addu',
                'Peoples Colony Kot Addu', 'Satellite Town Kot Addu', 'Jinnah Colony Kot Addu', 'Alipur Road',
                'Jatoi Road', 'Chak No 1', 'Chak No 2', 'Basti Khar', 'Kot Addu Bypass', 'Kot Addu Railway Station'
            ]
        ],
        'Kot Diji' => [
            'country' => 'PK',
            'areas' => [
                'Kot Diji Town', 'Khairpur (part)', 'Ranipur', 'Kot Diji Fort Area', 'Basti Diji', 'Kot Diji City',
                'Kot Diji Cantt', 'Khairpur Road', 'Kot Diji Bazaar', 'Gulshan-e-Kot Diji', 'Madina Colony Kot Diji',
                'Peoples Colony Kot Diji', 'Kot Diji Model Town', 'Jinnah Colony Kot Diji', 'Ranipur Road',
                'Basti Soomro', 'Kot Diji Bypass', 'Kot Diji Fort View'
            ]
        ],
        'Kot Ghulam Muhammad' => [
            'country' => 'PK',
            'areas' => [
                'Kot Ghulam Muhammad Town', 'Sialkot (part)', 'Pasrur', 'Kotli Loharan', 'Basti Ghulam Muhammad',
                'Kot Ghulam Muhammad City', 'Kot Ghulam Muhammad Cantt', 'Sialkot Road', 'Kot Ghulam Bazaar',
                'Gulshan-e-Ghulam', 'Madina Colony Ghulam', 'Peoples Colony Ghulam', 'Kot Ghulam Model Town',
                'Jinnah Colony Ghulam', 'Pasrur Road', 'Kotli Loharan Road', 'Chak No 1', 'Basti Bhatti',
                'Kot Ghulam Bypass'
            ]
        ],
        'Kot Malik Barkhurdar' => [
            'country' => 'PK',
            'areas' => [
                'Kot Malik Barkhurdar Town', 'Quetta (part)', 'Hazara Town', 'Basti Malik', 'Killi Barkhurdar',
                'Kot Malik City', 'Kot Malik Cantt', 'Quetta Road', 'Kot Malik Bazaar', 'Gulshan-e-Kot Malik',
                'Madina Colony Kot Malik', 'Peoples Colony Kot Malik', 'Kot Malik Model Town', 'Jinnah Colony Kot Malik',
                'Hazara Town Road', 'Killi Kakar', 'Kot Malik Bypass'
            ]
        ],
        'Kot Mumin' => [
            'country' => 'PK',
            'areas' => [
                'Kot Mumin Town', 'Sargodha (part)', 'Bhalwal', 'Sahiwal (Sargodha)', 'Basti Mumin', 'Kot Mumin City',
                'Kot Mumin Cantt', 'Sargodha Road', 'Kot Mumin Bazaar', 'Gulshan-e-Kot Mumin', 'Madina Colony Kot Mumin',
                'Peoples Colony Kot Mumin', 'Kot Mumin Model Town', 'Jinnah Colony Kot Mumin', 'Bhalwal Road',
                'Sahiwal Road', 'Chak No 1', 'Basti Kharal', 'Kot Mumin Bypass'
            ]
        ],
        'Kot Radha Kishan' => [
            'country' => 'PK',
            'areas' => [
                'Kot Radha Kishan Town', 'Kasur (part)', 'Chunian', 'Kanganpur', 'Basti Radha Kishan', 'Kot Radha Kishan City',
                'Kot Radha Kishan Cantt', 'Kasur Road', 'Kot Radha Bazaar', 'Gulshan-e-Radha', 'Madina Colony Radha',
                'Peoples Colony Radha', 'Kot Radha Model Town', 'Jinnah Colony Radha', 'Chunian Road',
                'Kanganpur Road', 'Chak No 1', 'Basti Kamboh', 'Kot Radha Bypass'
            ]
        ],
        'Kot Rajkour' => [
            'country' => 'PK',
            'areas' => [
                'Kot Rajkour Town', 'Sialkot (part)', 'Daska', 'Sambrial', 'Basti Rajkour', 'Kot Rajkour City',
                'Kot Rajkour Cantt', 'Sialkot Road', 'Kot Rajkour Bazaar', 'Gulshan-e-Rajkour', 'Madina Colony Rajkour',
                'Peoples Colony Rajkour', 'Kot Rajkour Model Town', 'Jinnah Colony Rajkour', 'Daska Road',
                'Sambrial Road', 'Chak No 1', 'Basti Bhatti', 'Kot Rajkour Bypass'
            ]
        ],
        'Kot Samaba' => [
            'country' => 'PK',
            'areas' => [
                'Kot Samaba Town', 'Rahim Yar Khan (part)', 'Sadiqabad', 'Khanpur (part)', 'Basti Samaba', 'Kot Samaba City',
                'Kot Samaba Cantt', 'Rahim Yar Khan Road', 'Kot Samaba Bazaar', 'Gulshan-e-Samaba', 'Madina Colony Samaba',
                'Peoples Colony Samaba', 'Kot Samaba Model Town', 'Jinnah Colony Samaba', 'Sadiqabad Road',
                'Khanpur Road', 'Chak No 1', 'Basti Khokhar', 'Kot Samaba Bypass'
            ]
        ],
        'Kot Sultan' => [
            'country' => 'PK',
            'areas' => [
                'Kot Sultan Town', 'Layyah (part)', 'Karor Lal Esan', 'Chak 46/TDA', 'Basti Sultan', 'Kot Sultan City',
                'Kot Sultan Cantt', 'Layyah Road', 'Kot Sultan Bazaar', 'Gulshan-e-Kot Sultan', 'Madina Colony Kot Sultan',
                'Peoples Colony Kot Sultan', 'Kot Sultan Model Town', 'Jinnah Colony Kot Sultan', 'Karor Road',
                'Chak 46/TDA Road', 'Basti Kharal', 'Kot Sultan Bypass'
            ]
        ],
        'Kotli' => [
            'country' => 'PK',
            'areas' => [
                'Kotli City', 'Kotli Cantt', 'Charhoi', 'Sehnsa', 'Khuiratta', 'Basti Kotli', 'Kotli City Area',
                'Mirpur Road Kotli', 'Kotli Bazaar', 'Gulshan-e-Kotli', 'Madina Colony Kotli', 'Peoples Colony Kotli',
                'Satellite Town Kotli', 'Jinnah Colony Kotli', 'Charhoi Road', 'Sehnsa Road', 'Khuiratta Road',
                'Basti Syedan', 'Kotli Bypass', 'Kotli Fort Area', 'Poonch River Area'
            ]
        ],
        'Kotli Loharan' => [
            'country' => 'PK',
            'areas' => [
                'Kotli Loharan Town', 'Sialkot (part)', 'Pasrur', 'Daska', 'Basti Loharan', 'Kotli Loharan City',
                'Kotli Loharan Cantt', 'Sialkot Road', 'Kotli Loharan Bazaar', 'Gulshan-e-Loharan',
                'Madina Colony Loharan', 'Peoples Colony Loharan', 'Kotli Loharan Model Town', 'Jinnah Colony Loharan',
                'Pasrur Road', 'Daska Road', 'Chak No 1', 'Basti Bhatti', 'Kotli Bypass'
            ]
        ],
        'Kotri' => [
            'country' => 'PK',
            'areas' => [
                'Kotri City', 'Kotri Cantt', 'Jamshoro (part)', 'Hyderabad (part)', 'Model Town', 'Kotri City Area',
                'Hyderabad Road Kotri', 'Kotri Bazaar', 'Gulshan-e-Kotri', 'Madina Colony Kotri', 'Peoples Colony Kotri',
                'Satellite Town Kotri', 'Jinnah Colony Kotri', 'Jamshoro Road', 'Basti Mirza', 'Basti Malkani',
                'Kotri Bypass', 'Kotri Railway Station', 'Kotri Barrage Area', 'Kotri Industrial Area'
            ]
        ],
        'Kulachi' => [
            'country' => 'PK',
            'areas' => [
                'Kulachi Town', 'Dera Ismail Khan (part)', 'Darazinda', 'Basti Kulachi', 'Kulachi City',
                'Kulachi Cantt', 'Dera Ismail Khan Road', 'Kulachi Bazaar', 'Gulshan-e-Kulachi', 'Madina Colony Kulachi',
                'Peoples Colony Kulachi', 'Kulachi Model Town', 'Jinnah Colony Kulachi', 'Darazinda Road',
                'Basti Khel', 'Kulachi Bypass'
            ]
        ],
        'Kundian' => [
            'country' => 'PK',
            'areas' => [
                'Kundian Town', 'Mianwali (part)', 'Piplan', 'Daud Khel', 'Basti Kundian', 'Kundian City',
                'Kundian Cantt', 'Mianwali Road', 'Kundian Bazaar', 'Gulshan-e-Kundian', 'Madina Colony Kundian',
                'Peoples Colony Kundian', 'Kundian Model Town', 'Jinnah Colony Kundian', 'Piplan Road',
                'Daud Khel Road', 'Basti Khel', 'Kundian Bypass', 'Kundian Railway Station'
            ]
        ],
        'Kunjah' => [
            'country' => 'PK',
            'areas' => [
                'Kunjah Town', 'Gujrat (part)', 'Wazirabad (part)', 'Dinga', 'Basti Kunjah', 'Kunjah City',
                'Kunjah Cantt', 'Gujrat Road', 'Kunjah Bazaar', 'Gulshan-e-Kunjah', 'Madina Colony Kunjah',
                'Peoples Colony Kunjah', 'Kunjah Model Town', 'Jinnah Colony Kunjah', 'Wazirabad Road',
                'Dinga Road', 'Chak No 1', 'Basti Cheema', 'Kunjah Bypass'
            ]
        ],
        'Kunri' => [
            'country' => 'PK',
            'areas' => [
                'Kunri Town', 'Umerkot (part)', 'Mithi (part)', 'Samaro', 'Basti Kunri', 'Kunri City',
                'Kunri Cantt', 'Umerkot Road Kunri', 'Kunri Bazaar', 'Gulshan-e-Kunri', 'Madina Colony Kunri',
                'Peoples Colony Kunri', 'Kunri Model Town', 'Jinnah Colony Kunri', 'Mithi Road', 'Samaro Road',
                'Basti Kolhi', 'Basti Meghwar', 'Kunri Bypass', 'Kunri Railway Station'
            ]
        ],
        'Lachi' => [
            'country' => 'PK',
            'areas' => [
                'Lachi Town', 'Kohat (part)', 'Dara Adam Khel', 'Basti Lachi', 'Lachi City',
                'Lachi Cantt', 'Kohat Road Lachi', 'Lachi Bazaar', 'Gulshan-e-Lachi', 'Madina Colony Lachi',
                'Peoples Colony Lachi', 'Lachi Model Town', 'Jinnah Colony Lachi', 'Dara Adam Khel Road',
                'Basti Khel', 'Lachi Bypass'
            ]
        ],
        'Ladhewala Waraich' => [
            'country' => 'PK',
            'areas' => [
                'Ladhewala Waraich Town', 'Gujranwala (part)', 'Wazirabad (part)', 'Basti Waraich', 'Ladhewala Waraich City',
                'Ladhewala Cantt', 'Gujranwala Road', 'Ladhewala Bazaar', 'Gulshan-e-Waraich', 'Madina Colony Waraich',
                'Peoples Colony Waraich', 'Ladhewala Model Town', 'Jinnah Colony Waraich', 'Wazirabad Road',
                'Chak No 1', 'Basti Cheema', 'Ladhewala Bypass'
            ]
        ],
        'Lahore' => [
            'country' => 'PK',
            'areas' => [
                // Core city & cantonment
                'Lahore City', 'Lahore Cantt',
                
                // Major residential schemes & DHA phases
                'Defence Housing Authority (DHA)', 'DHA Phase 1', 'DHA Phase 2', 'DHA Phase 3', 'DHA Phase 4',
                'DHA Phase 5', 'DHA Phase 6', 'DHA Phase 7', 'DHA Phase 8', 'DHA Phase 9',
                
                // Well-known towns
                'Gulberg', 'Model Town', 'Johar Town', 'Iqbal Town', 'Samanabad', 'Garden Town',
                'Allama Iqbal Town', 'Shadman', 'Faisal Town', 'Wahga Town', 'Raiwind', 'Township',
                'Valencia Town', 'Bahria Town Lahore', 'Lake City','Al hafeez garden', 'Askari XI', 'Canal Bank Housing Scheme',
                
                // Major colonies & neighborhoods
                'Mozang', 'Anarkali', 'Ichhra', 'Qila Gujjar Singh', 'Mughalpura', 'Garhi Shahu',
                'Gulshan-e-Ravi', 'Gulshan-e-Iqbal', 'Gulshan-e-Maymar', 'Gulshan-e-Hadeed',
                'Sabzazar', 'Green Town', 'New Garden Town', 'Muslim Town', 'Islam Nagar',
                'Shadbagh', 'Baghbanpura', 'Shahdara', 'Ravi Road', 'Ferozepur Road', 'Main Boulevard',
                
                // Outskirts & nearby towns
                'Chunian', 'Kot Abdul Malik', 'Kot Lakhpat', 'Sundar', 'Kahna Nau', 'Jallo',
                'Thokar Niaz Baig', 'Manga Mandi', 'Harbanspura', 'Nishtar Colony', 'Taj bagh','Qanchi bazar','Punjab Cooperative Society',
                
                // Additional notable areas
                'Mall Road', 'Liberty Market', 'Fortress Stadium', 'Cavalry Ground', 'Gulberg II', 'Gulberg III',
                'Askari X', 'Askari IV', 'PCSIR Housing Scheme', 'State Life Housing Society', 'Wapda Town Lahore',
                'LDA Avenue 1', 'LDA City', 'Sukh Chayn Gardens', 'Emporium Mall Area', 'Kalma Chowk'
            ]
        ],
        'Lakhi' => [
            'country' => 'PK',
            'areas' => [
                'Lakhi Town', 'Shikarpur (part)', 'Ghulam Muhammad', 'Basti Lakhi', 'Lakhi City',
                'Lakhi Cantt', 'Shikarpur Road', 'Lakhi Bazaar', 'Gulshan-e-Lakhi', 'Madina Colony Lakhi',
                'Peoples Colony Lakhi', 'Lakhi Model Town', 'Jinnah Colony Lakhi', 'Ghulam Muhammad Road',
                'Basti Khosa', 'Lakhi Bypass'
            ]
        ],
        'Lakki' => [
            'country' => 'PK',
            'areas' => [
                'Lakki Town (Lakki Marwat)', 'Serai Naurang', 'Tajori', 'Basti Lakki', 'Lakki City',
                'Lakki Cantt', 'Bannu Road', 'Lakki Bazaar', 'Gulshan-e-Lakki', 'Madina Colony Lakki',
                'Peoples Colony Lakki', 'Lakki Model Town', 'Jinnah Colony Lakki', 'Serai Naurang Road',
                'Tajori Road', 'Basti Khel', 'Lakki Bypass', 'Lakki Fort Area'
            ]
        ],
        'Lala Musa' => [
            'country' => 'PK',
            'areas' => [
                'Lala Musa Town', 'Gujrat (part)', 'Kharian', 'Dinga', 'Model Town', 'Lala Musa Cantt',
                'Gujrat Road', 'Lala Musa Bazaar', 'Gulshan-e-Lala', 'Madina Colony Lala', 'Peoples Colony Lala',
                'Satellite Town Lala', 'Jinnah Colony Lala', 'Kharian Road', 'Dinga Road', 'Chak No 1',
                'Basti Cheema', 'Lala Musa Bypass', 'Lala Musa Railway Station'
            ]
        ],
        'Lalian' => [
            'country' => 'PK',
            'areas' => [
                'Lalian Town', 'Chiniot (part)', 'Bhawana', 'Rabwah', 'Basti Lalian', 'Lalian City',
                'Lalian Cantt', 'Chiniot Road', 'Lalian Bazaar', 'Gulshan-e-Lalian', 'Madina Colony Lalian',
                'Peoples Colony Lalian', 'Lalian Model Town', 'Jinnah Colony Lalian', 'Bhawana Road',
                'Rabwah Road', 'Chak No 1', 'Basti Kharal', 'Lalian Bypass'
            ]
        ],
        'Landi Kotal' => [
            'country' => 'PK',
            'areas' => [
                'Landi Kotal Town', 'Khyber (part)', 'Jamrud', 'Michni', 'Landi Kotal Bazar', 'Landi Kotal City',
                'Landi Kotal Cantt', 'Khyber Road', 'Landi Kotal Bazaar', 'Gulshan-e-Landi', 'Madina Colony Landi',
                'Peoples Colony Landi', 'Landi Kotal Model Town', 'Jinnah Colony Landi', 'Jamrud Road',
                'Michni Road', 'Basti Afridi', 'Landi Kotal Bypass', 'Khyber Pass Area'
            ]
        ],
        'Larkana' => [
            'country' => 'PK',
            'areas' => [
                'Larkana City', 'Larkana Cantt', 'Dokri', 'Ratodero', 'Naudero', 'Model Town',
                'Larkana City Area', 'Mohenjo Daro Road', 'Larkana Bazaar', 'Gulshan-e-Larkana',
                'Madina Colony Larkana', 'Peoples Colony Larkana', 'Satellite Town Larkana', 'Jinnah Colony Larkana',
                'Dokri Road', 'Ratodero Road', 'Naudero Road', 'Basti Khoso', 'Basti Buriro', 'Larkana Bypass',
                'Larkana Railway Station', 'Chandka Medical College Area', 'Mohenjo Daro Area (nearby)'
            ]
        ],
        'Layyah' => [
            'country' => 'PK',
            'areas' => [
                'Layyah City', 'Karor Lal Esan', 'Fatehpur', 'Chak 46/TDA', 'Model Town', 'Layyah Cantt',
                'Layyah City Area', 'Multan Road Layyah', 'Layyah Bazaar', 'Gulshan-e-Layyah', 'Madina Colony Layyah',
                'Peoples Colony Layyah', 'Satellite Town Layyah', 'Jinnah Colony Layyah', 'Karor Road',
                'Fatehpur Road', 'Chak 46/TDA Road', 'Basti Kharal', 'Layyah Bypass', 'Layyah Railway Station'
            ]
        ],
        'Liliani' => [
            'country' => 'PK',
            'areas' => [
                'Liliani Town', 'Sargodha (part)', 'Shahpur', 'Bhalwal', 'Basti Liliani', 'Liliani City',
                'Liliani Cantt', 'Sargodha Road', 'Liliani Bazaar', 'Gulshan-e-Liliani', 'Madina Colony Liliani',
                'Peoples Colony Liliani', 'Liliani Model Town', 'Jinnah Colony Liliani', 'Shahpur Road',
                'Bhalwal Road', 'Chak No 1', 'Basti Kharal', 'Liliani Bypass'
            ]
        ],
        'Lodhran' => [
            'country' => 'PK',
            'areas' => [
                'Lodhran City', 'Dunyapur', 'Kahror Pakka', 'Basti Lodhran', 'Model Town', 'Lodhran Cantt',
                'Lodhran City Area', 'Multan Road Lodhran', 'Lodhran Bazaar', 'Gulshan-e-Lodhran',
                'Madina Colony Lodhran', 'Peoples Colony Lodhran', 'Satellite Town Lodhran', 'Jinnah Colony Lodhran',
                'Dunyapur Road', 'Kahror Pakka Road', 'Chak No 1', 'Chak No 2', 'Basti Khokhar', 'Lodhran Bypass',
                'Lodhran Railway Station'
            ]
        ],
        'Loralai' => [
            'country' => 'PK',
            'areas' => [
                'Loralai City', 'Loralai Cantt', 'Duki', 'Mekhtar', 'Killi Loralai', 'Model Town',
                'Loralai City Area', 'Quetta Road Loralai', 'Loralai Bazaar', 'Gulshan-e-Loralai',
                'Madina Colony Loralai', 'Peoples Colony Loralai', 'Satellite Town Loralai', 'Jinnah Colony Loralai',
                'Duki Road', 'Mekhtar Road', 'Killi Kakar', 'Loralai Bypass', 'Loralai Fort Area'
            ]
        ],
        'Mach' => [
            'country' => 'PK',
            'areas' => [
                'Mach Town', 'Mach Cantt', 'Kolpur', 'Killi Mach', 'Maching Goth', 'Mach Colony',
                'Bosti Mach', 'Machingabad', 'Harnai Road', 'Gulshan-e-Mach', 'Shahbaz Nagar',
                'Mach Bazaar', 'Railway Station Mach', 'Mach Industrial Area', 'Sibi Road Mach'
            ]
        ],
        'Madeji' => [
            'country' => 'PK',
            'areas' => [
                'Madeji Town', 'Shikarpur (part)', 'Khanpur', 'Basti Madeji', 'Madeji Cantt',
                'Gulshan-e-Madeji', 'Madeji City', 'Shikarpur Road', 'Madeji Mor', 'Jalalabad Colony',
                'Bhit Shah Colony', 'Madeji Muhalla', 'Sultanabad', 'Faisal Nagar', 'Rehman Colony'
            ]
        ],
        'Mailsi' => [
            'country' => 'PK',
            'areas' => [
                'Mailsi Town', 'Vehari (part)', 'Burewala', 'Dunyapur', 'Basti Mailsi', 'Mailsi Cantt',
                'Mailsi City', 'Pakpattan Road', 'Mailsi Bypass', 'Chak No 4/M', 'Chak No 17/M',
                'Jinnah Colony Mailsi', 'Gulshan-e-Mailsi', 'Model Town Mailsi', 'Madina Colony Mailsi',
                'Basti Qureshian', 'Basti Langah', 'Shahbaz Nagar Mailsi'
            ]
        ],
        'Malakand' => [
            'country' => 'PK',
            'areas' => [
                'Malakand City', 'Malakand Cantt', 'Bat Khela', 'Dargai', 'Thana', 'Sakhakot', 'Alladand',
                'Palai', 'Malakand Agency', 'Khar Bajaur (part)', 'Chakdara', 'Kot Malakand', 'Sam Ranizai',
                'Jandol', 'Malakand Mor', 'Gulshan Colony Malakand', 'Hill Station Malakand'
            ]
        ],
        'Malakwal' => [
            'country' => 'PK',
            'areas' => [
                'Malakwal Town', 'Mandi Bahauddin (part)', 'Phalia', 'Basti Malakwal', 'Malakwal Cantt',
                'Malakwal City', 'Gujrat Road', 'Railway Road Malakwal', 'Sargodha Road Malakwal',
                'Chak No 14', 'Chak No 25', 'Jinnah Colony Malakwal', 'Gulshan-e-Malakwal',
                'Malakwal Model Town', 'Shahbaz Nagar Malakwal', 'Farooq Colony', 'Abbas Town Malakwal'
            ]
        ],
        'Malakwal City' => [
            'country' => 'PK',
            'areas' => [
                'Malakwal City', 'Malakwal Cantt', 'Phalia (part)', 'Basti Malakwal', 'Mohallah Arain',
                'Gulshan-e-Malakwal', 'Malakwal City Area', 'Jamia Masjid Road', 'Railway Road City',
                'Mandi Bahauddin Road', 'Sargodha Road City', 'Mohallah Sheikhupura', 'Mohallah Qureshian',
                'Malakwal Old City', 'Malakwal New Colony', 'Fatima Colony', 'Ali Nagar Malakwal'
            ]
        ],
        'Malir Cantonment' => [
            'country' => 'PK',
            'areas' => [
                'Malir Cantt', 'Malir Colony', 'Malir Halt', 'Khokhrapar', 'Saudabad', 'Malir City',
                'Malir Cantt Bazaar', 'Dawood Chowrangi', 'Malir 15', 'Malir Extension', 'Jaffar-e-Tayyar Society',
                'Malir City Sector 1', 'Malir City Sector 2', 'Shah Faisal Colony', 'Quaidabad',
                'Malir River Side', 'Airbase Malir', 'Malir Model Colony', 'Malir Cantt Railway Station'
            ]
        ],
        'Mamu Kanjan' => [
            'country' => 'PK',
            'areas' => [
                'Mamu Kanjan Town', 'Faisalabad (part)', 'Jaranwala', 'Chak 46 JB', 'Basti Mamu',
                'Mamu Kanjan City', 'Chak No 47 JB', 'Chak No 48 JB', 'Gulshan-e-Mamu', 'Madina Colony Mamu',
                'Peoples Colony Mamu', 'Satellite Town Mamu', 'Mamu Kanjan Bypass', 'Jaranwala Road',
                'Faisalabad Road Mamu', 'Mamu Kanjan Cantt', 'Model Town Mamu'
            ]
        ],
        'Mananwala' => [
            'country' => 'PK',
            'areas' => [
                'Mananwala Town', 'Sheikhupura (part)', 'Nankana Sahib (part)', 'Basti Manan',
                'Mananwala City', 'Mananwala Cantt', 'Gulshan-e-Manan', 'Mananwala Bypass',
                'Jaranwala Road Manan', 'Safdarabad Road', 'Chak No 1 G', 'Chak No 2 G',
                'Mananwala Colony', 'Jinnah Colony Manan', 'Shahbaz Nagar Manan', 'Rehman Colony'
            ]
        ],
        'Mandi Bahauddin' => [
            'country' => 'PK',
            'areas' => [
                'Mandi Bahauddin City', 'Mandi Bahauddin Cantt', 'Phalia', 'Malakwal', 'Model Town',
                'Mandi Bahauddin City Area', 'Railway Road Mandi', 'Gujrat Road Mandi', 'Sargodha Road Mandi',
                'Chak No 15', 'Chak No 17', 'Gulshan-e-Mandi', 'Madina Colony Mandi', 'Peoples Colony Mandi',
                'Satellite Town Mandi', 'Mandi Bahauddin Bypass', 'Jinnah Colony Mandi', 'Abbas Town'
            ]
        ],
        'Mangla' => [
            'country' => 'PK',
            'areas' => [
                'Mangla Town', 'Jhang (part)', 'Shorkot', 'Basti Mangla', 'Mangla Cantt',
                'Mangla City', 'Mangla Dam Area', 'Mangla View Colony', 'Mangla Colony', 'Gulshan-e-Mangla',
                'Jhang Road Mangla', 'Shorkot Road', 'Mangla Bazaar', 'Railway Station Mangla',
                'Chak No 10', 'Chak No 12', 'New Mangla City', 'Old Mangla Town'
            ]
        ],
        'Mankera' => [
            'country' => 'PK',
            'areas' => [
                'Mankera Town', 'Bhakkar (part)', 'Kalur Kot', 'Basti Mankera', 'Mankera Cantt',
                'Mankera City', 'Mankera Fort', 'Mankera Bazaar', 'Bhakkar Road', 'Kalur Kot Road',
                'Chak No 1', 'Chak No 2', 'Gulshan-e-Mankera', 'Madina Colony Mankera', 'Mankera Model Town',
                'Shahbaz Nagar Mankera', 'Basti Bhutta', 'Basti Kharal'
            ]
        ],
        'Mansehra' => [
            'country' => 'PK',
            'areas' => [
                'Mansehra City', 'Mansehra Cantt', 'Shinkiari', 'Baffa', 'Oghi', 'Model Town',
                'Mansehra City Area', 'Abbottabad Road', 'Battal', 'Shergarh', 'Darband', 'Phulra',
                'Gulshan-e-Mansehra', 'Madina Colony Mansehra', 'Peoples Colony Mansehra', 'Satellite Town Mansehra',
                'Jinnah Colony Mansehra', 'Kaghan Road', 'Mansehra Bazaar', 'Railway Station Mansehra (Old)'
            ]
        ],
        'Mardan' => [
            'country' => 'PK',
            'areas' => [
                'Mardan City', 'Mardan Cantt', 'Takht Bhai', 'Katlang', 'Rustam', 'Model Town',
                'Mardan City Area', 'Charsadda Road', 'Hoti Mardan', 'Gulshan-e-Mardan', 'Madina Colony Mardan',
                'Peoples Colony Mardan', 'Satellite Town Mardan', 'Jinnah Colony Mardan', 'Mardan Bazaar',
                'Railway Road Mardan', 'Mardan Bypass', 'Takht Bhai Road', 'Katlang Road', 'Rustam Road'
            ]
        ],
        'Mastung' => [
            'country' => 'PK',
            'areas' => [
                'Mastung City', 'Mastung Cantt', 'Dasht', 'Kirdgap', 'Killi Mastung', 'Mastung Bazaar',
                'Mastung Road', 'Quetta Road', 'Mastung Colony', 'Gulshan-e-Mastung', 'Madina Colony Mastung',
                'Killi Zafarabad', 'Killi Allah Dad', 'Mastung Fort Area', 'Mastung Valley', 'Shahbaz Nagar Mastung'
            ]
        ],
        'Matiari' => [
            'country' => 'PK',
            'areas' => [
                'Matiari City', 'Matiari Cantt', 'Hala', 'Saeedabad', 'Basti Matiari', 'Matiari Bazaar',
                'Hyderabad Road Matiari', 'Matiari Colony', 'Gulshan-e-Matiari', 'Madina Colony Matiari',
                'Peoples Colony Matiari', 'Matiari Model Town', 'Jinnah Colony Matiari', 'Basti Shah', 'Basti Laghari'
            ]
        ],
        'Matli' => [
            'country' => 'PK',
            'areas' => [
                'Matli Town', 'Badin (part)', 'Tando Bago', 'Golarchi', 'Basti Matli', 'Matli City',
                'Matli Cantt', 'Badin Road Matli', 'Matli Bazaar', 'Gulshan-e-Matli', 'Madina Colony Matli',
                'Peoples Colony Matli', 'Matli Model Town', 'Jinnah Colony Matli', 'Basti Pathan', 'Basti Solangi'
            ]
        ],
        'Mehar' => [
            'country' => 'PK',
            'areas' => [
                'Mehar Town', 'Dadu (part)', 'Khairpur Nathan Shah', 'Basti Mehar', 'Mehar City',
                'Mehar Cantt', 'Dadu Road Mehar', 'Mehar Bazaar', 'Gulshan-e-Mehar', 'Madina Colony Mehar',
                'Peoples Colony Mehar', 'Mehar Model Town', 'Jinnah Colony Mehar', 'Basti Khosa', 'Basti Jatoi'
            ]
        ],
        'Mehmand Chak' => [
            'country' => 'PK',
            'areas' => [
                'Mehmand Chak Town', 'Gujrat (part)', 'Kharian', 'Dinga', 'Basti Mehmand', 'Mehmand Chak City',
                'Mehmand Chak Cantt', 'Gujrat Road', 'Kharian Road', 'Mehmand Chak Bazaar', 'Gulshan-e-Mehmand',
                'Madina Colony Mehmand', 'Peoples Colony Mehmand', 'Mehmand Chak Model Town', 'Jinnah Colony Mehmand'
            ]
        ],
        'Mehrabpur' => [
            'country' => 'PK',
            'areas' => [
                'Mehrabpur Town', 'Naushahro Feroze (part)', 'Bhiria', 'Kandiaro', 'Basti Mehrab',
                'Mehrabpur City', 'Mehrabpur Cantt', 'Naushahro Feroze Road', 'Mehrabpur Bazaar',
                'Gulshan-e-Mehrabpur', 'Madina Colony Mehrabpur', 'Peoples Colony Mehrabpur',
                'Mehrabpur Model Town', 'Jinnah Colony Mehrabpur', 'Basti Buriro', 'Basti Unar'
            ]
        ],
        'Mian Channun' => [
            'country' => 'PK',
            'areas' => [
                'Mian Channun City', 'Khanewal (part)', 'Kabirwala', 'Jahanian Shah', 'Model Town',
                'Mian Channun Cantt', 'Khanewal Road', 'Mian Channun Bazaar', 'Gulshan-e-Mian', 'Madina Colony MC',
                'Peoples Colony MC', 'Satellite Town MC', 'Jinnah Colony MC', 'Chak No 2', 'Chak No 5',
                'Basti Khokhar', 'Basti Mochian'
            ]
        ],
        'Mianke Mor' => [
            'country' => 'PK',
            'areas' => [
                'Mianke Mor Town', 'Lahore (part)', 'Muridke (part)', 'Basti Mianke', 'Mianke Mor City',
                'Mianke Mor Cantt', 'Lahore Road', 'Muridke Road', 'Mianke Mor Bazaar', 'Gulshan-e-Mianke',
                'Madina Colony Mianke', 'Peoples Colony Mianke', 'Mianke Mor Model Town', 'Jinnah Colony Mianke',
                'Chak No 1', 'Basti Kamboh', 'Basti Sheikh'
            ]
        ],
        'Mianwali' => [
            'country' => 'PK',
            'areas' => [
                'Mianwali City', 'Mianwali Cantt', 'Kundian', 'Daud Khel', 'Isa Khel', 'Model Town',
                'Mianwali City Area', 'Mianwali Bypass', 'Railway Road Mianwali', 'Gulshan-e-Mianwali',
                'Madina Colony Mianwali', 'Peoples Colony Mianwali', 'Satellite Town Mianwali', 'Jinnah Colony Mianwali',
                'Chak No 5', 'Chak No 6', 'Basti Harnah', 'Basti Mochi'
            ]
        ],
        'Minchianabad' => [
            'country' => 'PK',
            'areas' => [
                'Minchianabad City (Minchinabad)', 'Bahawalnagar (part)', 'Chishtian', 'Haroonabad', 'Basti Minchia',
                'Minchianabad Cantt', 'Bahawalnagar Road', 'Minchianabad Bazaar', 'Gulshan-e-Minchian',
                'Madina Colony Minch', 'Peoples Colony Minch', 'Minchianabad Model Town', 'Jinnah Colony Minch',
                'Chak No 1/M', 'Chak No 2/M', 'Basti Nizam', 'Basti Joiya'
            ]
        ],
        'Mingora' => [
            'country' => 'PK',
            'areas' => [
                'Mingora City', 'Swat Cantt', 'Saidu Sharif', 'Kanju', 'Model Town', 'Mingora Bazaar',
                'Swat Road Mingora', 'Mingora Colony', 'Gulshan-e-Mingora', 'Madina Colony Mingora',
                'Peoples Colony Mingora', 'Satellite Town Mingora', 'Jinnah Colony Mingora', 'Karakoram Highway Mingora',
                'Bahrain Road Mingora', 'Basti Shah', 'Basti Mardan'
            ]
        ],
        'Miran Shah' => [
            'country' => 'PK',
            'areas' => [
                'Miran Shah Town', 'North Waziristan (part)', 'Razmak', 'Dattakhel', 'Basti Miran',
                'Miran Shah Cantt', 'Miran Shah Bazaar', 'Waziristan Road', 'Miran Shah Colony', 'Gulshan-e-Miran',
                'Madina Colony Miran', 'Miran Shah Model Town', 'Jinnah Colony Miran', 'Basti Ahmadzai', 'Basti Darwesh'
            ]
        ],
        'Miro Khan' => [
            'country' => 'PK',
            'areas' => [
                'Miro Khan Town', 'Larkana (part)', 'Kambar', 'Basti Miro', 'Miro Khan City',
                'Miro Khan Cantt', 'Larkana Road', 'Miro Khan Bazaar', 'Gulshan-e-Miro', 'Madina Colony Miro',
                'Peoples Colony Miro', 'Miro Khan Model Town', 'Jinnah Colony Miro', 'Basti Buriro', 'Basti Mangrio'
            ]
        ],
        'Mirpur Bhtoro' => [
            'country' => 'PK',
            'areas' => [
                'Mirpur Bhtoro Town', 'Sujawal (part)', 'Jati', 'Daro', 'Basti Bhtoro', 'Mirpur Bhtoro City',
                'Mirpur Bhtoro Cantt', 'Sujawal Road', 'Mirpur Bhtoro Bazaar', 'Gulshan-e-Bhtoro', 'Madina Colony Bhtoro',
                'Peoples Colony Bhtoro', 'Mirpur Bhtoro Model Town', 'Jinnah Colony Bhtoro', 'Basti Mallah', 'Basti Mirza'
            ]
        ],
        'Mirpur Khas' => [
            'country' => 'PK',
            'areas' => [
                'Mirpur Khas City', 'Mirpur Khas Cantt', 'Digri', 'Jhuddo', 'Kot Ghulam Muhammad', 'Model Town',
                'Mirpur Khas City Area', 'Hyderabad Road', 'Mirpur Khas Bazaar', 'Gulshan-e-Mirpur', 'Madina Colony MK',
                'Peoples Colony MK', 'Satellite Town MK', 'Jinnah Colony MK', 'Mirpur Khas Bypass', 'Basti Shah', 'Basti Lakho'
            ]
        ],
        'Mirpur Mathelo' => [
            'country' => 'PK',
            'areas' => [
                'Mirpur Mathelo Town', 'Ghotki (part)', 'Daharki', 'Khanpur Mahar', 'Basti Mathelo',
                'Mirpur Mathelo City', 'Mirpur Mathelo Cantt', 'Ghotki Road', 'Mirpur Mathelo Bazaar',
                'Gulshan-e-Mathelo', 'Madina Colony Mathelo', 'Peoples Colony Mathelo', 'Mirpur Mathelo Model Town',
                'Jinnah Colony Mathelo', 'Basti Kori', 'Basti Panhwar'
            ]
        ],
        'Mirpur Sakro' => [
            'country' => 'PK',
            'areas' => [
                'Mirpur Sakro Town', 'Thatta (part)', 'Gharo', 'Basti Sakro', 'Mirpur Sakro City',
                'Mirpur Sakro Cantt', 'Thatta Road', 'Mirpur Sakro Bazaar', 'Gulshan-e-Sakro', 'Madina Colony Sakro',
                'Peoples Colony Sakro', 'Mirpur Sakro Model Town', 'Jinnah Colony Sakro', 'Basti Malkani', 'Basti Jokhio'
            ]
        ],
        'Mirwah Gorchani' => [
            'country' => 'PK',
            'areas' => [
                'Mirwah Gorchani Town', 'Mirpur Khas (part)', 'Tando Allahyar (part)', 'Basti Gorchani',
                'Mirwah Gorchani City', 'Mirwah Gorchani Cantt', 'Mirpur Khas Road', 'Mirwah Gorchani Bazaar',
                'Gulshan-e-Gorchani', 'Madina Colony Gorchani', 'Peoples Colony Gorchani', 'Mirwah Gorchani Model Town',
                'Jinnah Colony Gorchani', 'Basti Narejo', 'Basti Khaskheli'
            ]
        ],
        'Mitha Tiwana' => [
            'country' => 'PK',
            'areas' => [
                'Mitha Tiwana Town', 'Khushab (part)', 'Jauharabad', 'Basti Mitha', 'Mitha Tiwana City',
                'Mitha Tiwana Cantt', 'Khushab Road', 'Mitha Tiwana Bazaar', 'Gulshan-e-Mitha', 'Madina Colony Mitha',
                'Peoples Colony Mitha', 'Mitha Tiwana Model Town', 'Jinnah Colony Mitha', 'Chak No 1', 'Chak No 2'
            ]
        ],
        'Mithi' => [
            'country' => 'PK',
            'areas' => [
                'Mithi City', 'Tharparkar (part)', 'Diplo', 'Islamkot', 'Nagarparkar', 'Basti Mithi',
                'Mithi Cantt', 'Mithi Bazaar', 'Gulshan-e-Mithi', 'Madina Colony Mithi', 'Peoples Colony Mithi',
                'Mithi Model Town', 'Jinnah Colony Mithi', 'Basti Sodha', 'Basti Meghwar', 'Basti Bheel'
            ]
        ],
        'Moro' => [
            'country' => 'PK',
            'areas' => [
                'Moro Town', 'Naushahro Feroze (part)', 'Bhiria', 'Mehrabpur', 'Basti Moro', 'Moro City',
                'Moro Cantt', 'Naushahro Feroze Road', 'Moro Bazaar', 'Gulshan-e-Moro', 'Madina Colony Moro',
                'Peoples Colony Moro', 'Moro Model Town', 'Jinnah Colony Moro', 'Basti Shaikh', 'Basti Unar'
            ]
        ],
        'Moza Shahwala' => [
            'country' => 'PK',
            'areas' => [
                'Moza Shahwala Town', 'Muzaffargarh (part)', 'Kot Addu', 'Basti Shahwala', 'Moza Shahwala City',
                'Moza Shahwala Cantt', 'Muzaffargarh Road', 'Moza Shahwala Bazaar', 'Gulshan-e-Shahwala',
                'Madina Colony Shahwala', 'Peoples Colony Shahwala', 'Moza Shahwala Model Town', 'Jinnah Colony Shahwala',
                'Chak No 1', 'Basti Khokhar', 'Basti Joiya'
            ]
        ],
        'Multan' => [
            'country' => 'PK',
            'areas' => [
                'Multan City', 'Multan Cantt', 'Shujabad', 'Jalalpur Pirwala', 'Model Town',
                'Satellite Town', 'Gulgasht Colony', 'Bosan Road', 'Multan City Area', 'M.A. Jinnah Road',
                'Multan Bazaar', 'Gulshan-e-Multan', 'Madina Colony Multan', 'Peoples Colony Multan',
                'Jinnah Colony Multan', 'Chowk Kumharanwala', 'DHA Multan', 'Bahria Town Multan',
                'Multan Bypass', 'Qasim Bela', 'Suraj Miani', 'Makhdoom Rashid'
            ]
        ],
        'Muridke' => [
            'country' => 'PK',
            'areas' => [
                'Muridke City', 'Sheikhupura (part)', 'Narang Mandi', 'Basti Muridke', 'Muridke Cantt',
                'Muridke City Area', 'Lahore Road Muridke', 'Muridke Bazaar', 'Gulshan-e-Muridke', 'Madina Colony Muridke',
                'Peoples Colony Muridke', 'Muridke Model Town', 'Jinnah Colony Muridke', 'Chak No 1', 'Basti Bhatti'
            ]
        ],
        'Murree' => [
            'country' => 'PK',
            'areas' => [
                'Murree City', 'Mall Road', 'Kashmir Point', 'Pindi Point', 'Bhurban',
                'Guldana', 'Sunny Bank', 'Changla Gali', 'Mohallah Murree', 'Murree Cantt',
                'Lower Topa', 'Patriata', 'Barian', 'Jhika Gali', 'Charra Pani', 'Murree Bazaar',
                'Government House Murree', 'Murree Hills', 'Bhurban Town', 'Kuldana'
            ]
        ],
        'Musa Khel Bazar' => [
            'country' => 'PK',
            'areas' => [
                'Musa Khel Bazar', 'Musa Khel Cantt', 'Kingri', 'Killi Musa', 'Basti Musa',
                'Musa Khel City', 'Musa Khel Bazaar', 'Musa Khel Road', 'Gulshan-e-Musakhel', 'Madina Colony Musa',
                'Peoples Colony Musa', 'Musa Khel Model Town', 'Jinnah Colony Musa', 'Killi Hassan', 'Killi Shahbaz'
            ]
        ],
        'Mustafābād' => [
            'country' => 'PK',
            'areas' => [
                'Mustafabad Town', 'Kasur (part)', 'Pattoki', 'Basti Mustafa', 'Mustafabad City',
                'Mustafabad Cantt', 'Kasur Road', 'Mustafabad Bazaar', 'Gulshan-e-Mustafa', 'Madina Colony Mustafa',
                'Peoples Colony Mustafa', 'Mustafabad Model Town', 'Jinnah Colony Mustafa', 'Chak No 1', 'Basti Chishti'
            ]
        ],
        'Muzaffargarh' => [
            'country' => 'PK',
            'areas' => [
                'Muzaffargarh City', 'Muzaffargarh Cantt', 'Kot Addu', 'Alipur', 'Jatoi', 'Model Town',
                'Muzaffargarh City Area', 'Dera Ghazi Khan Road', 'Muzaffargarh Bazaar', 'Gulshan-e-Muzaffar',
                'Madina Colony MZG', 'Peoples Colony MZG', 'Satellite Town MZG', 'Jinnah Colony MZG',
                'Muzaffargarh Bypass', 'Chowk Sarwar Shaheed', 'Kot Addu Road', 'Alipur Road', 'Jatoi Road'
            ]
        ],
        'Muzaffarābād' => [
            'country' => 'PK',
            'areas' => [
                'Muzaffarabad City', 'Muzaffarabad Cantt', 'Chattar', 'Garhi Dupatta', 'Mohallah Muzaffar',
                'Muzaffarabad City Area', 'Neelum Road', 'Jhelum Valley Road', 'Muzaffarabad Bazaar',
                'Gulshan-e-Muzaffar', 'Madina Colony MZD', 'Peoples Colony MZD', 'Satellite Town MZD',
                'Jinnah Colony MZD', 'Kashmir Highway MZD', 'Basti Syedan', 'Basti Qureshian'
            ]
        ],
                'Nabisar' => [
            'country' => 'PK',
            'areas' => [
                'Nabisar Town', 'Mithi (part)', 'Islamkot', 'Basti Nabisar', 'Nabisar City',
                'Nabisar Cantt', 'Nabisar Bazaar', 'Mithi Road Nabisar', 'Islamkot Road',
                'Gulshan-e-Nabisar', 'Madina Colony Nabisar', 'Peoples Colony Nabisar',
                'Nabisar Model Town', 'Jinnah Colony Nabisar', 'Basti Kolhi', 'Basti Meghwar',
                'Basti Bheel', 'Nabisar Taluka', 'Khetlari', 'Chhachhro Road Nabisar'
            ]
        ],
        'Nankana Sahib' => [
            'country' => 'PK',
            'areas' => [
                'Nankana Sahib City', 'Nankana Sahib Cantt', 'Shahkot', 'Sangla Hill', 'Model Town',
                'Nankana Sahib City Area', 'Lahore Road Nankana', 'Nankana Sahib Bazaar', 'Gurdwara Nankana Sahib',
                'Gulshan-e-Nankana', 'Madina Colony Nankana', 'Peoples Colony Nankana', 'Satellite Town Nankana',
                'Jinnah Colony Nankana', 'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan',
                'Nankana Sahib Bypass', 'Shahkot Road', 'Sangla Hill Road', 'Mandiali', 'Bucheki'
            ]
        ],
        'Narang Mandi' => [
            'country' => 'PK',
            'areas' => [
                'Narang Mandi Town', 'Sheikhupura (part)', 'Muridke', 'Basti Narang', 'Narang Mandi City',
                'Narang Mandi Cantt', 'Lahore Road Narang', 'Narang Mandi Bazaar', 'Gulshan-e-Narang',
                'Madina Colony Narang', 'Peoples Colony Narang', 'Narang Mandi Model Town', 'Jinnah Colony Narang',
                'Chak No 1', 'Chak No 2', 'Basti Kambo', 'Basti Gujjar', 'Sheikhupura Road Narang'
            ]
        ],
        'Narowal' => [
            'country' => 'PK',
            'areas' => [
                'Narowal City', 'Narowal Cantt', 'Shakargarh', 'Zafarwal', 'Model Town',
                'Narowal City Area', 'Sialkot Road Narowal', 'Narowal Bazaar', 'Gulshan-e-Narowal',
                'Madina Colony Narowal', 'Peoples Colony Narowal', 'Satellite Town Narowal', 'Jinnah Colony Narowal',
                'Shakargarh Road', 'Zafarwal Road', 'Chak No 1', 'Chak No 2', 'Basti Sheikh',
                'Basti Khokhar', 'Narowal Bypass', 'Gurdwara Sri Kartarpur Sahib (nearby)'
            ]
        ],
        'Nasirabad' => [
            'country' => 'PK',
            'areas' => [
                'Nasirabad Town', 'Nasirabad Cantt', 'Killi Nasir', 'Basti Nasirabad', 'Nasirabad City',
                'Nasirabad Bazaar', 'Quetta Road Nasirabad', 'Gulshan-e-Nasirabad', 'Madina Colony Nasirabad',
                'Peoples Colony Nasirabad', 'Nasirabad Model Town', 'Jinnah Colony Nasirabad',
                'Killi Jan Muhammad', 'Killi Abdul Sattar', 'Basti Mirza', 'Nasirabad Valley',
                'Sibi Road Nasirabad', 'Dhadhar', 'Mithri'
            ]
        ],
        'Naudero' => [
            'country' => 'PK',
            'areas' => [
                'Naudero Town', 'Larkana (part)', 'Dokri', 'Basti Naudero', 'Naudero City',
                'Naudero Cantt', 'Larkana Road Naudero', 'Naudero Bazaar', 'Gulshan-e-Naudero',
                'Madina Colony Naudero', 'Peoples Colony Naudero', 'Naudero Model Town', 'Jinnah Colony Naudero',
                'Basti Solangi', 'Basti Khoso', 'Basti Buriro', 'Dokri Road', 'Mohenjo Daro Road'
            ]
        ],
        'Naukot' => [
            'country' => 'PK',
            'areas' => [
                'Naukot Town', 'Umerkot (part)', 'Samaro', 'Basti Naukot', 'Naukot City',
                'Naukot Cantt', 'Umerkot Road Naukot', 'Naukot Bazaar', 'Gulshan-e-Naukot',
                'Madina Colony Naukot', 'Peoples Colony Naukot', 'Naukot Model Town', 'Jinnah Colony Naukot',
                'Basti Rajper', 'Basti Sodha', 'Basti Meghwar', 'Mithi Road Naukot', 'Naukot Fort'
            ]
        ],
        'Naushahra Virkan' => [
            'country' => 'PK',
            'areas' => [
                'Naushahra Virkan Town', 'Gujranwala (part)', 'Wazirabad (part)', 'Basti Virkan',
                'Naushahra Virkan City', 'Naushahra Virkan Cantt', 'Gujranwala Road Naushahra',
                'Wazirabad Road', 'Naushahra Virkan Bazaar', 'Gulshan-e-Naushahra', 'Madina Colony Naushahra',
                'Peoples Colony Naushahra', 'Naushahra Model Town', 'Jinnah Colony Naushahra',
                'Chak No 1', 'Chak No 2', 'Basti Cheema', 'Basti Virk'
            ]
        ],
        'Naushahro Firoz' => [
            'country' => 'PK',
            'areas' => [
                'Naushahro Firoz City', 'Naushahro Feroze Cantt', 'Moro', 'Kandiaro', 'Model Town',
                'Naushahro Firoz City Area', 'Mehrabpur Road', 'Naushahro Bazaar', 'Gulshan-e-Naushahro',
                'Madina Colony NF', 'Peoples Colony NF', 'Satellite Town NF', 'Jinnah Colony NF',
                'Basti Bhutto', 'Basti Unar', 'Basti Shaikh', 'Kandiaro Road', 'Moro Road',
                'Naushahro Firoz Bypass', 'Chak No 1', 'Chak No 2'
            ]
        ],
        'Nawabshah' => [
            'country' => 'PK',
            'areas' => [
                'Nawabshah City', 'Nawabshah Cantt', 'Sakrand', 'Daur', 'Qazi Ahmed', 'Model Town',
                'Nawabshah City Area', 'Hyderabad Road Nawabshah', 'Nawabshah Bazaar', 'Gulshan-e-Nawabshah',
                'Madina Colony NS', 'Peoples Colony NS', 'Satellite Town NS', 'Jinnah Colony NS',
                'Sakrand Road', 'Daur Road', 'Qazi Ahmed Road', 'Nawabshah Bypass',
                'Basti Bukhari', 'Basti Jatoi', 'Basti Qazi', 'Mehrabpur (part)'
            ]
        ],
        'Nazir Town' => [
            'country' => 'PK',
            'areas' => [
                'Nazir Town', 'Bhimber (part)', 'Samahni', 'Basti Nazir', 'Nazir Town City',
                'Nazir Town Cantt', 'Bhimber Road', 'Nazir Town Bazaar', 'Gulshan-e-Nazir',
                'Madina Colony Nazir', 'Peoples Colony Nazir', 'Nazir Town Model Town', 'Jinnah Colony Nazir',
                'Basti Gujjar', 'Basti Rajput', 'Samahni Road', 'Nazir Town Valley'
            ]
        ],
        'New Bādāh' => [
            'country' => 'PK',
            'areas' => [
                'New Badah Town', 'Shikarpur (part)', 'Khanpur', 'Basti Badah', 'New Badah City',
                'New Badah Cantt', 'Shikarpur Road', 'New Badah Bazaar', 'Gulshan-e-Badah',
                'Madina Colony Badah', 'Peoples Colony Badah', 'New Badah Model Town', 'Jinnah Colony Badah',
                'Basti Baloch', 'Basti Khosa', 'Khanpur Road', 'Badah Mor'
            ]
        ],
        'New Mirpur' => [
            'country' => 'PK',
            'areas' => [
                'New Mirpur City', 'Mirpur Cantt', 'Jatlan', 'Mohallah New Mirpur', 'New Mirpur City Area',
                'Mirpur Road', 'New Mirpur Bazaar', 'Gulshan-e-Mirpur', 'Madina Colony New Mirpur',
                'Peoples Colony New Mirpur', 'Satellite Town New Mirpur', 'Jinnah Colony New Mirpur',
                'Jatlan Road', 'Islamgarh', 'Kakra', 'Basti Syedan', 'Mangla Dam View',
                'New Mirpur Bypass', 'Chakswari Road'
            ]
        ],
        'Noorabad' => [
            'country' => 'PK',
            'areas' => [
                'Noorabad Town', 'Charsadda (part)', 'Tangi', 'Basti Noorabad', 'Noorabad City',
                'Noorabad Cantt', 'Charsadda Road Noorabad', 'Noorabad Bazaar', 'Gulshan-e-Noorabad',
                'Madina Colony Noorabad', 'Peoples Colony Noorabad', 'Noorabad Model Town', 'Jinnah Colony Noorabad',
                'Tangi Road', 'Basti Kaka Khel', 'Basti Mohmand', 'Noorabad Bypass'
            ]
        ],
        'Nowshera' => [
            'country' => 'PK',
            'areas' => [
                'Nowshera City', 'Nowshera Cantt', 'Pabbi', 'Akora Khattak', 'Risalpur', 'Model Town',
                'Nowshera City Area', 'Grand Trunk Road', 'Nowshera Bazaar', 'Gulshan-e-Nowshera',
                'Madina Colony Nowshera', 'Peoples Colony Nowshera', 'Satellite Town Nowshera', 'Jinnah Colony Nowshera',
                'Pabbi Road', 'Akora Khattak Road', 'Risalpur Road', 'Nowshera Bypass',
                'Cantt Bazar Nowshera', 'Khairabad', 'Mohallah Sethian', 'Nowshera Fort'
            ]
        ],
        'Nowshera Cantonment' => [
            'country' => 'PK',
            'areas' => [
                'Nowshera Cantt', 'Risalpur (part)', 'Khairabad', 'Cantt Bazar', 'Nowshera Cantt Area',
                'Cantt Colony', 'Officers Colony Nowshera', 'Soldiers Colony', 'Gulshan-e-Cantt',
                'Railway Station Nowshera Cantt', 'Mall Road Cantt', 'Risalpur Road Cantt',
                'Akora Khattak Road Cantt', 'Pabbi Road Cantt', 'Nowshera Cantt Bypass',
                'Khairabad Colony', 'Cantt Market', 'Jinnah Park Cantt', 'Cantt Church Area'
            ]
        ],
        'Nushki' => [
            'country' => 'PK',
            'areas' => [
                'Nushki City', 'Nushki Cantt', 'Dalbandin (part)', 'Killi Nushki', 'Nushki City Area',
                'Nushki Bazaar', 'Quetta Road Nushki', 'Dalbandin Road', 'Gulshan-e-Nushki',
                'Madina Colony Nushki', 'Peoples Colony Nushki', 'Nushki Model Town', 'Jinnah Colony Nushki',
                'Killi Haji', 'Killi Malik', 'Basti Nushki', 'Nushki Fort', 'Nushki Valley',
                'Chaghi Road Nushki', 'Mashkhel Road'
            ]
        ],
        'Okara' => [
            'country' => 'PK',
            'areas' => [
                'Okara City', 'Okara Cantt', 'Dipalpur', 'Renala Khurd', 'Basirpur',
                'Hujra Shah Muqeem', 'Model Town', 'Okara City Area', 'Depalpur Road',
                'Okara Bazaar', 'Gulshan-e-Okara', 'Madina Colony Okara', 'Peoples Colony Okara',
                'Satellite Town Okara', 'Jinnah Colony Okara', 'Chak No 1/4L', 'Chak No 2/4L',
                'Basti Gujjar', 'Basti Jattan', 'Okara Bypass', 'Renala Khurd Road', 'Basirpur Road',
                'Hujra Shah Muqeem Road', 'Dipalpur Cantt (area)', 'Okara Industrial Estate'
            ]
        ],
        'Ormara' => [
            'country' => 'PK',
            'areas' => [
                'Ormara Town', 'Gwadar (part)', 'Pasni (part)', 'Ormara Beach Area', 'Ormara City',
                'Ormara Cantt', 'Ormara Bazaar', 'Gulshan-e-Ormara', 'Madina Colony Ormara',
                'Peoples Colony Ormara', 'Ormara Model Town', 'Jinnah Colony Ormara', 'Ormara Port',
                'Ormara Fish Harbour', 'Basti Ormara', 'Kalmat', 'Ormara Hills', 'Makran Coastal Highway Ormara'
            ]
        ],
        'Pabbi' => [
            'country' => 'PK',
            'areas' => [
                'Pabbi Town', 'Nowshera (part)', 'Akora Khattak (part)', 'Basti Pabbi', 'Pabbi City',
                'Pabbi Cantt', 'Grand Trunk Road Pabbi', 'Pabbi Bazaar', 'Gulshan-e-Pabbi',
                'Madina Colony Pabbi', 'Peoples Colony Pabbi', 'Pabbi Model Town', 'Jinnah Colony Pabbi',
                'Basti Khel', 'Basti Afghani', 'Akora Khattak Road', 'Nowshera Road Pabbi', 'Pabbi Railway Station'
            ]
        ],
        'Pad Idan' => [
            'country' => 'PK',
            'areas' => [
                'Pad Idan Town (Padidan)', 'Naushahro Feroze (part)', 'Bhiria', 'Basti Pad', 'Pad Idan City',
                'Pad Idan Cantt', 'Pad Idan Bazaar', 'Naushahro Feroze Road', 'Gulshan-e-Padidan',
                'Madina Colony Padidan', 'Peoples Colony Padidan', 'Padidan Model Town', 'Jinnah Colony Padidan',
                'Basti Bhutto', 'Basti Unar', 'Bhiria Road', 'Padidan Railway Station'
            ]
        ],
        'Paharpur' => [
            'country' => 'PK',
            'areas' => [
                'Paharpur Town', 'Dera Ismail Khan (part)', 'Kulachi', 'Basti Paharpur', 'Paharpur City',
                'Paharpur Cantt', 'Paharpur Bazaar', 'Dera Ismail Khan Road', 'Gulshan-e-Paharpur',
                'Madina Colony Paharpur', 'Peoples Colony Paharpur', 'Paharpur Model Town', 'Jinnah Colony Paharpur',
                'Basti Khar', 'Basti Mian', 'Kulachi Road', 'Paharpur Fort Area'
            ]
        ],
        'Pakpattan' => [
            'country' => 'PK',
            'areas' => [
                'Pakpattan City', 'Pakpattan Cantt', 'Arifwala (part)', 'Basti Pakpattan', 'Model Town',
                'Pakpattan City Area', 'Shrine of Baba Farid', 'Pakpattan Bazaar', 'Gulshan-e-Pakpattan',
                'Madina Colony Pakpattan', 'Peoples Colony Pakpattan', 'Satellite Town Pakpattan', 'Jinnah Colony Pakpattan',
                'Arifwala Road', 'Chak No 1', 'Chak No 2', 'Basti Kamboh', 'Basti Jattan', 'Pakpattan Bypass',
                'Farid Town', 'Malka Hans'
            ]
        ],
        'Panjgur' => [
            'country' => 'PK',
            'areas' => [
                'Panjgur City', 'Panjgur Cantt', 'Gichk', 'Killi Panjgur', 'Panjgur City Area',
                'Panjgur Bazaar', 'Turbat Road Panjgur', 'Gulshan-e-Panjgur', 'Madina Colony Panjgur',
                'Peoples Colony Panjgur', 'Panjgur Model Town', 'Jinnah Colony Panjgur', 'Killi Gichk',
                'Killi Siah', 'Basti Panjgur', 'Panjgur Fort', 'Panjgur Valley', 'Chitkan', 'Sargodha (nearby)'
            ]
        ],
        'Pano Aqil' => [
            'country' => 'PK',
            'areas' => [
                'Pano Aqil Town', 'Sukkur (part)', 'Rohri', 'Basti Pano', 'Pano Aqil City',
                'Pano Aqil Cantt', 'Sukkur Road Pano', 'Pano Aqil Bazaar', 'Gulshan-e-Pano',
                'Madina Colony Pano', 'Peoples Colony Pano', 'Pano Aqil Model Town', 'Jinnah Colony Pano',
                'Basti Laghari', 'Basti Khoso', 'Rohri Road', 'Pano Aqil Railway Station', 'Pano Aqil Bypass'
            ]
        ],
        'Parachinar' => [
            'country' => 'PK',
            'areas' => [
                'Parachinar City', 'Kurram (part)', 'Sadda', 'Basti Parachinar', 'Parachinar Cantt',
                'Parachinar Bazaar', 'Kurram Road', 'Gulshan-e-Parachinar', 'Madina Colony Parachinar',
                'Peoples Colony Parachinar', 'Parachinar Model Town', 'Jinnah Colony Parachinar',
                'Basti Turi', 'Basti Mangal', 'Sadda Road', 'Parachinar Valley', 'Terri Mangal',
                'Peiwar Pass Area', 'Kurram Agency HQ'
            ]
        ],
        'Pasni' => [
            'country' => 'PK',
            'areas' => [
                'Pasni Town', 'Gwadar (part)', 'Ormara', 'Pasni Fish Harbour', 'Pasni City',
                'Pasni Cantt', 'Pasni Bazaar', 'Gwadar Road Pasni', 'Gulshan-e-Pasni', 'Madina Colony Pasni',
                'Peoples Colony Pasni', 'Pasni Model Town', 'Jinnah Colony Pasni', 'Basti Pasni',
                'Pasni Port', 'Koh-e-Batil', 'Jiwani (nearby)', 'Pasni Beach', 'Makran Coastal Highway Pasni'
            ]
        ],
        'Pasrur' => [
            'country' => 'PK',
            'areas' => [
                'Pasrur City', 'Kotli Loharan', 'Hadali', 'Chak 1', 'Basti Pasrur', 'Model Town',
                'Pasrur Cantt', 'Sialkot Road Pasrur', 'Pasrur Bazaar', 'Gulshan-e-Pasrur',
                'Madina Colony Pasrur', 'Peoples Colony Pasrur', 'Satellite Town Pasrur', 'Jinnah Colony Pasrur',
                'Chak No 2', 'Chak No 3', 'Basti Bhatti', 'Basti Cheema', 'Kotli Loharan Road',
                'Hadali Road', 'Pasrur Bypass'
            ]
        ],
        'Pattoki' => [
            'country' => 'PK',
            'areas' => [
                'Pattoki City', 'Kasur (part)', 'Kanganpur', 'Kot Radha Kishan', 'Basti Pattoki', 'Model Town',
                'Pattoki Cantt', 'Lahore Road Pattoki', 'Pattoki Bazaar', 'Gulshan-e-Pattoki',
                'Madina Colony Pattoki', 'Peoples Colony Pattoki', 'Satellite Town Pattoki', 'Jinnah Colony Pattoki',
                'Kanganpur Road', 'Kot Radha Kishan Road', 'Chak No 1', 'Chak No 2', 'Basti Jattan',
                'Pattoki Bypass', 'Phool Nagar (nearby)'
            ]
        ],
        'Peshawar' => [
            'country' => 'PK',
            'areas' => [
                'Peshawar City', 'Peshawar Cantt', 'University Town', 'Hayatabad', 'Gulbahar',
                'Gulshan Colony', 'Faqirabad', 'Kohat Road', 'Ring Road', 'Saddar', 'Model Town',
                'Peshawar City Area', 'Jama Masjid Peshawar', 'Qissa Khwani Bazaar', 'Khyber Road',
                'Charsadda Road', 'Warsak Road', 'Gulshan-e-Rahim', 'Madina Colony Peshawar',
                'Peoples Colony Peshawar', 'Satellite Town Peshawar', 'Jinnah Colony Peshawar',
                'Phase 1 Hayatabad', 'Phase 2 Hayatabad', 'Phase 3 Hayatabad', 'Phase 4 Hayatabad',
                'Phase 5 Hayatabad', 'Phase 6 Hayatabad', 'Phase 7 Hayatabad', 'Dabgari Garden',
                'Karkhano Bazaar', 'Board Bazaar', 'Hashtnagri', 'Kohati Gate', 'Mughalpura',
                'Peshawar Bypass', 'Bara Road', 'Mardan Road', 'Regi Model Town', 'Tahkal'
            ]
        ],
        'Phalia' => [
            'country' => 'PK',
            'areas' => [
                'Phalia City', 'Mandi Bahauddin (part)', 'Malakwal (part)', 'Basti Phalia', 'Model Town',
                'Phalia Cantt', 'Mandi Bahauddin Road', 'Phalia Bazaar', 'Gulshan-e-Phalia',
                'Madina Colony Phalia', 'Peoples Colony Phalia', 'Satellite Town Phalia', 'Jinnah Colony Phalia',
                'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Malakwal Road Phalia',
                'Phalia Bypass', 'Mohallah Arain'
            ]
        ],
        'Pind Dadan Khan' => [
            'country' => 'PK',
            'areas' => [
                'Pind Dadan Khan Town', 'Jhelum (part)', 'Khewra', 'Dandot RS', 'Basti Dadan',
                'Pind Dadan Khan City', 'Pind Dadan Khan Cantt', 'Jhelum Road', 'Pind Dadan Khan Bazaar',
                'Gulshan-e-Pind', 'Madina Colony Pind', 'Peoples Colony Pind', 'Pind Dadan Khan Model Town',
                'Jinnah Colony Pind', 'Khewra Road', 'Dandot Road', 'Chak No 1', 'Basti Kharal',
                'Salt Range Area', 'Khewra Salt Mines Colony'
            ]
        ],
        'Pindi Bhattian' => [
            'country' => 'PK',
            'areas' => [
                'Pindi Bhattian Town', 'Hafizabad (part)', 'Sukheke Mandi', 'Basti Bhattian', 'Pindi Bhattian City',
                'Pindi Bhattian Cantt', 'Lahore Road Pindi', 'Hafizabad Road', 'Pindi Bhattian Bazaar',
                'Gulshan-e-Pindi', 'Madina Colony Pindi', 'Peoples Colony Pindi', 'Pindi Bhattian Model Town',
                'Jinnah Colony Pindi', 'Chak No 1', 'Chak No 2', 'Basti Kamboh', 'Sukheke Mandi Road',
                'Pindi Bhattian Bypass'
            ]
        ],
        'Pindi Gheb' => [
            'country' => 'PK',
            'areas' => [
                'Pindi Gheb Town', 'Attock (part)', 'Fateh Jang', 'Jand', 'Basti Gheb', 'Pindi Gheb City',
                'Pindi Gheb Cantt', 'Attock Road', 'Pindi Gheb Bazaar', 'Gulshan-e-Pindi Gheb',
                'Madina Colony Gheb', 'Peoples Colony Gheb', 'Pindi Gheb Model Town', 'Jinnah Colony Gheb',
                'Fateh Jang Road', 'Jand Road', 'Chak No 1', 'Basti Malik', 'Basti Khel', 'Pindi Gheb Bypass'
            ]
        ],
        'Pir Jo Goth' => [
            'country' => 'PK',
            'areas' => [
                'Pir Jo Goth Town', 'Khairpur (part)', 'Ranipur', 'Kot Diji', 'Basti Pir', 'Pir Jo Goth City',
                'Pir Jo Goth Cantt', 'Khairpur Road', 'Pir Jo Goth Bazaar', 'Gulshan-e-Pir',
                'Madina Colony Pir', 'Peoples Colony Pir', 'Pir Jo Goth Model Town', 'Jinnah Colony Pir',
                'Ranipur Road', 'Kot Diji Road', 'Basti Soomro', 'Basti Khairpur', 'Pir Jo Goth Bypass'
            ]
        ],
        'Pir Mahal' => [
            'country' => 'PK',
            'areas' => [
                'Pir Mahal Town', 'Toba Tek Singh (part)', 'Kamalia', 'Gojra', 'Basti Pir', 'Pir Mahal City',
                'Pir Mahal Cantt', 'Toba Tek Singh Road', 'Pir Mahal Bazaar', 'Gulshan-e-Pir Mahal',
                'Madina Colony Pir Mahal', 'Peoples Colony Pir Mahal', 'Pir Mahal Model Town', 'Jinnah Colony Pir Mahal',
                'Kamalia Road', 'Gojra Road', 'Chak No 1', 'Chak No 2', 'Basti Kharal', 'Pir Mahal Bypass'
            ]
        ],
        'Pishin' => [
            'country' => 'PK',
            'areas' => [
                'Pishin City', 'Pishin Cantt', 'Khanozai', 'Killi Pishin', 'Model Town',
                'Pishin City Area', 'Quetta Road Pishin', 'Pishin Bazaar', 'Gulshan-e-Pishin',
                'Madina Colony Pishin', 'Peoples Colony Pishin', 'Satellite Town Pishin', 'Jinnah Colony Pishin',
                'Killi Khanozai', 'Killi Malik', 'Basti Pishin', 'Pishin Fort', 'Pishin Valley',
                'Khanozai Road', 'Pishin Bypass'
            ]
        ],
        'Pithoro' => [
            'country' => 'PK',
            'areas' => [
                'Pithoro Town', 'Umerkot (part)', 'Kunri', 'Samaro', 'Basti Pithoro', 'Pithoro City',
                'Pithoro Cantt', 'Umerkot Road Pithoro', 'Pithoro Bazaar', 'Gulshan-e-Pithoro',
                'Madina Colony Pithoro', 'Peoples Colony Pithoro', 'Pithoro Model Town', 'Jinnah Colony Pithoro',
                'Kunri Road', 'Samaro Road', 'Basti Kolhi', 'Basti Meghwar', 'Pithoro Bypass', 'Nabisar Road'
            ]
        ],
        'Qadirpur Ran' => [
            'country' => 'PK',
            'areas' => [
                'Qadirpur Ran Town', 'Bahawalpur (part)', 'Hasilpur', 'Yazman', 'Basti Qadir',
                'Qadirpur Ran City', 'Qadirpur Ran Cantt', 'Bahawalpur Road', 'Qadirpur Ran Bazaar',
                'Gulshan-e-Qadir', 'Madina Colony Qadir', 'Peoples Colony Qadir', 'Qadirpur Ran Model Town',
                'Jinnah Colony Qadir', 'Hasilpur Road', 'Yazman Road', 'Chak No 1', 'Chak No 2',
                'Basti Ran', 'Basti Khokhar', 'Qadirpur Ran Bypass'
            ]
        ],
        'Qila Abdullah' => [
            'country' => 'PK',
            'areas' => [
                'Qila Abdullah Town', 'Chaman (part)', 'Killi Qila', 'Basti Abdullah', 'Qila Abdullah City',
                'Qila Abdullah Cantt', 'Chaman Road', 'Qila Abdullah Bazaar', 'Gulshan-e-Qila',
                'Madina Colony Qila', 'Peoples Colony Qila', 'Qila Abdullah Model Town', 'Jinnah Colony Qila',
                'Killi Kakar', 'Killi Ahmadzai', 'Basti Malik', 'Qila Abdullah Fort', 'Sheila Bagh',
                'Khojak Pass Area', 'Qila Abdullah Bypass'
            ]
        ],
        'Qila Saifullah' => [
            'country' => 'PK',
            'areas' => [
                'Qila Saifullah City', 'Muslim Bagh', 'Killi Saifullah', 'Model Town', 'Qila Saifullah Cantt',
                'Qila Saifullah City Area', 'Loralai Road', 'Qila Saifullah Bazaar', 'Gulshan-e-Saifullah',
                'Madina Colony Saifullah', 'Peoples Colony Saifullah', 'Satellite Town Saifullah', 'Jinnah Colony Saifullah',
                'Muslim Bagh Road', 'Killi Abdul Khel', 'Killi Mirdad', 'Basti Saifullah', 'Qila Saifullah Fort',
                'Zhob Road', 'Qila Saifullah Bypass'
            ]
        ],
        'Quetta' => [
            'country' => 'PK',
            'areas' => [
                'Quetta City', 'Quetta Cantt', 'Satellite Town', 'Airport Road', 'Jinnah Town',
                'Hazara Town', 'Kuchlak', 'Mariabad', 'Model Town', 'Quetta City Area',
                'Jinnah Road', 'M.A. Jinnah Road', 'Zarghoon Road', 'Brewery Road', 'Sariab Road',
                'Sariab Colony', 'Joint Road', 'Kandahari Bazaar', 'Shahbaz Town', 'Nawa Killi',
                'Killi Sheikhan', 'Pashtunabad', 'Bosti', 'Gulshan-e-Quetta', 'Madina Colony Quetta',
                'Peoples Colony Quetta', 'Jinnah Colony Quetta', 'Quetta Bypass', 'Chiltan Town',
                'Bolan Medical College Area', 'University Road', 'Hanna Urak', 'Hanna Valley',
                'Zarghoon Town', 'Baleli', 'Samungli Road', 'Quetta Airport Area'
            ]
        ],
        'Rahim Yar Khan' => [
            'country' => 'PK',
            'areas' => [
                'Rahim Yar Khan City', 'Rahim Yar Khan Cantt', 'Sadiqabad', 'Khanpur', 'Zahir Pir', 'Model Town',
                'Rahim Yar Khan City Area', 'Bahawalpur Road', 'Rahim Yar Khan Bazaar', 'Gulshan-e-Rahim',
                'Madina Colony RYK', 'Peoples Colony RYK', 'Satellite Town RYK', 'Jinnah Colony RYK',
                'Sadiqabad Road', 'Khanpur Road', 'Zahir Pir Road', 'Chak No 1', 'Chak No 2',
                'Basti Khokhar', 'Basti Jattan', 'Rahim Yar Khan Bypass', 'Sheikh Zayed Medical College Area',
                'Shaikh Zayed Colony', 'Faisal Colony', 'Abbas Town RYK'
            ]
        ],
        'Raiwind' => [
            'country' => 'PK',
            'areas' => [
                'Raiwind City', 'Raiwind Cantt', 'Basti Raiwind', 'Tableeghi Markaz Area', 'Mohallah Raiwind',
                'Raiwind City Area', 'Lahore Road Raiwind', 'Raiwind Bazaar', 'Gulshan-e-Raiwind',
                'Madina Colony Raiwind', 'Peoples Colony Raiwind', 'Raiwind Model Town', 'Jinnah Colony Raiwind',
                'Chak No 1', 'Chak No 2', 'Basti Kambo', 'Basti Gujjar', 'Raiwind Bypass',
                'Raiwind Railway Station', 'Raiwind Road', 'Khudian Khas', 'Kot Abdul Malik (nearby)'
            ]
        ],
        'Raja Jang' => [
            'country' => 'PK',
            'areas' => [
                'Raja Jang Town', 'Kasur (part)', 'Pattoki (part)', 'Basti Raja', 'Raja Jang City',
                'Raja Jang Cantt', 'Kasur Road', 'Raja Jang Bazaar', 'Gulshan-e-Raja', 'Madina Colony Raja',
                'Peoples Colony Raja', 'Raja Jang Model Town', 'Jinnah Colony Raja', 'Chak No 1',
                'Basti Jattan', 'Basti Gujjar', 'Pattoki Road', 'Raja Jang Bypass'
            ]
        ],
        'Rajanpur' => [
            'country' => 'PK',
            'areas' => [
                'Rajanpur City', 'Rajanpur Cantt', 'Jampur', 'Rojhan', 'Fazilpur', 'Basti Rajan',
                'Rajanpur City Area', 'Dera Ghazi Khan Road', 'Rajanpur Bazaar', 'Gulshan-e-Rajanpur',
                'Madina Colony Rajanpur', 'Peoples Colony Rajanpur', 'Satellite Town Rajanpur', 'Jinnah Colony Rajanpur',
                'Jampur Road', 'Rojhan Road', 'Fazilpur Road', 'Chak No 1', 'Basti Khar', 'Basti Mian',
                'Rajanpur Bypass', 'Saraiki Colony', 'Indus Highway Rajanpur'
            ]
        ],
        'Rajo Khanani' => [
            'country' => 'PK',
            'areas' => [
                'Rajo Khanani Town', 'Tando Allahyar (part)', 'Chamber (part)', 'Basti Rajo', 'Rajo Khanani City',
                'Rajo Khanani Cantt', 'Tando Allahyar Road', 'Rajo Khanani Bazaar', 'Gulshan-e-Rajo',
                'Madina Colony Rajo', 'Peoples Colony Rajo', 'Rajo Khanani Model Town', 'Jinnah Colony Rajo',
                'Basti Soomro', 'Basti Khaskheli', 'Chamber Road', 'Rajo Khanani Bypass'
            ]
        ],
        'Ranipur' => [
            'country' => 'PK',
            'areas' => [
                'Ranipur Town', 'Khairpur (part)', 'Kot Diji', 'Pir Jo Goth', 'Basti Ranipur', 'Ranipur City',
                'Ranipur Cantt', 'Khairpur Road Ranipur', 'Ranipur Bazaar', 'Gulshan-e-Ranipur',
                'Madina Colony Ranipur', 'Peoples Colony Ranipur', 'Ranipur Model Town', 'Jinnah Colony Ranipur',
                'Kot Diji Road', 'Pir Jo Goth Road', 'Basti Soomro', 'Basti Khairpur', 'Ranipur Bypass',
                'Kot Diji Fort Area'
            ]
        ],
        'Rasulnagar' => [
            'country' => 'PK',
            'areas' => [
                'Rasulnagar Town', 'Gujranwala (part)', 'Wazirabad (part)', 'Basti Rasul', 'Rasulnagar City',
                'Rasulnagar Cantt', 'Gujranwala Road Rasul', 'Rasulnagar Bazaar', 'Gulshan-e-Rasul',
                'Madina Colony Rasul', 'Peoples Colony Rasul', 'Rasulnagar Model Town', 'Jinnah Colony Rasul',
                'Wazirabad Road', 'Chak No 1', 'Basti Cheema', 'Basti Virk', 'Rasulnagar Bypass'
            ]
        ],
        'Ratodero' => [
            'country' => 'PK',
            'areas' => [
                'Ratodero Town', 'Larkana (part)', 'Dokri', 'Naudero', 'Basti Ratodero', 'Ratodero City',
                'Ratodero Cantt', 'Larkana Road Ratodero', 'Ratodero Bazaar', 'Gulshan-e-Ratodero',
                'Madina Colony Ratodero', 'Peoples Colony Ratodero', 'Ratodero Model Town', 'Jinnah Colony Ratodero',
                'Dokri Road', 'Naudero Road', 'Basti Khoso', 'Basti Buriro', 'Ratodero Bypass'
            ]
        ],
        'Rawala Kot' => [
            'country' => 'PK',
            'areas' => [
                'Rawala Kot City', 'Poonch (part)', 'Hajira', 'Basti Rawala', 'Rawala Kot Cantt',
                'Rawala Kot City Area', 'Poonch River Road', 'Rawala Kot Bazaar', 'Gulshan-e-Rawala',
                'Madina Colony Rawala', 'Peoples Colony Rawala', 'Rawala Kot Model Town', 'Jinnah Colony Rawala',
                'Hajira Road', 'Basti Syedan', 'Basti Qureshian', 'Rawala Kot Fort', 'Rawala Kot Valley',
                'Tatta Pani Road', 'Rawala Kot Bypass'
            ]
        ],
        'Rawalpindi' => [
            'country' => 'PK',
            'areas' => [
                'Rawalpindi City', 'Rawalpindi Cantt', 'Saddar', 'Satellite Town', 'Commercial Market',
                'Westridge', 'Tench Bhatta', 'Dheri Hassanabad', 'Model Town', 'Rawalpindi City Area',
                'Murree Road', 'Peshawar Road', 'Islamabad Highway', 'Mall Road Rawalpindi',
                'Bank Road', 'Committee Chowk', 'Raja Bazaar', 'Liaquat Bazaar', 'Bhabra Bazaar',
                'Karyana Bazaar', 'Gulshan-e-Rawalpindi', 'Madina Colony Rawalpindi', 'Peoples Colony Rawalpindi',
                'Jinnah Colony Rawalpindi', 'Chaklala Scheme 1', 'Chaklala Scheme 2', 'Chaklala Scheme 3',
                'Chaklala Cantonment', 'PAF Base Chaklala', 'Police Foundation', 'Bani Gala (nearby)',
                'Bahria Town Rawalpindi', 'DHA Rawalpindi', 'Sohan', 'Tarnol', 'Fauji Colony',
                'Afshan Colony', 'Dhoke Kala Khan', 'Dhoke Hassu', 'Dhoke Ratta', 'Pindora Colony',
                'Kallar Syedan Road', 'Rawalpindi Bypass', 'Double Road', 'Adiala Road', 'Sadiqabad Rawalpindi',
                'Gulshan Abad', 'Rehmanabad', 'Moti Mahal', 'Waris Khan', 'Ganj Mandi'
            ]
        ],
        'Renala Khurd' => [
            'country' => 'PK',
            'areas' => [
                'Renala Khurd Town', 'Okara (part)', 'Dipalpur', 'Hujra Shah Muqeem', 'Basti Renala',
                'Renala Khurd City', 'Renala Khurd Cantt', 'Okara Road Renala', 'Renala Khurd Bazaar',
                'Gulshan-e-Renala', 'Madina Colony Renala', 'Peoples Colony Renala', 'Renala Khurd Model Town',
                'Jinnah Colony Renala', 'Dipalpur Road', 'Hujra Shah Muqeem Road', 'Chak No 1',
                'Basti Gujjar', 'Basti Jattan', 'Renala Khurd Bypass', 'Renala Railway Station'
            ]
        ],
        'Risalpur Cantonment' => [
            'country' => 'PK',
            'areas' => [
                'Risalpur Cantt', 'Nowshera (part)', 'Risalpur Bazar', 'PAF Academy Area', 'Cantt Colony',
                'Risalpur Cantt Area', 'PAF Risalpur Base', 'Officers Colony Risalpur', 'Soldiers Colony Risalpur',
                'Gulshan-e-Risalpur', 'Madina Colony Risalpur', 'Peoples Colony Risalpur', 'Risalpur Model Town',
                'Jinnah Colony Risalpur', 'Nowshera Road Risalpur', 'Cantt Bazar Risalpur', 'Risalpur Railway Station',
                'Risalpur Bypass', 'Khairabad Road', 'Mohallah Cantt', 'Risalpur Educational Institutes Area'
            ]
        ],
        'Rohri' => [
            'country' => 'PK',
            'areas' => [
                'Rohri Town', 'Sukkur (part)', 'Pano Aqil', 'Basti Rohri', 'Rohri City',
                'Rohri Cantt', 'Sukkur Road Rohri', 'Rohri Bazaar', 'Gulshan-e-Rohri', 'Madina Colony Rohri',
                'Peoples Colony Rohri', 'Rohri Model Town', 'Jinnah Colony Rohri', 'Pano Aqil Road',
                'Basti Laghari', 'Basti Khoso', 'Rohri Railway Station', 'Rohri Bypass', 'Rohri Fort Area',
                'Lansdowne Bridge Area', 'Ayub Gate', 'Rohri Hills'
            ]
        ],
        'Rojhan' => [
            'country' => 'PK',
            'areas' => [
                'Rojhan Town', 'Rajanpur (part)', 'Dajal', 'Basti Rojhan', 'Rojhan City',
                'Rojhan Cantt', 'Rajanpur Road', 'Rojhan Bazaar', 'Gulshan-e-Rojhan', 'Madina Colony Rojhan',
                'Peoples Colony Rojhan', 'Rojhan Model Town', 'Jinnah Colony Rojhan', 'Dajal Road',
                'Basti Khar', 'Basti Mian', 'Rojhan Bypass', 'Indus Highway Rojhan', 'Rojhan Fort Area'
            ]
        ],
        'Rustam' => [
            'country' => 'PK',
            'areas' => [
                'Rustam Town', 'Shikarpur (part)', 'Khanpur', 'Basti Rustam', 'Rustam City',
                'Rustam Cantt', 'Shikarpur Road', 'Rustam Bazaar', 'Gulshan-e-Rustam', 'Madina Colony Rustam',
                'Peoples Colony Rustam', 'Rustam Model Town', 'Jinnah Colony Rustam', 'Khanpur Road',
                'Basti Baloch', 'Basti Khosa', 'Rustam Bypass', 'Rustam Fort Area', 'Miro Khan Road'
            ]
        ],
        'Saddiqabad' => [
            'country' => 'PK',
            'areas' => [
                'Saddiqabad Town (Sadiqabad)', 'Rahim Yar Khan (part)', 'Khanpur', 'Zahir Pir', 'Model Town',
                'Sadiqabad City', 'Sadiqabad Cantt', 'Rahim Yar Khan Road', 'Sadiqabad Bazaar', 'Gulshan-e-Sadiq',
                'Madina Colony Sadiqabad', 'Peoples Colony Sadiqabad', 'Satellite Town Sadiqabad', 'Jinnah Colony Sadiqabad',
                'Khanpur Road', 'Zahir Pir Road', 'Chak No 1', 'Chak No 2', 'Basti Khokhar', 'Basti Jattan',
                'Sadiqabad Bypass', 'Airport Sadiqabad', 'Faisal Colony Sadiqabad'
            ]
        ],
        'Sahiwal' => [ // Sahiwal (formerly Montgomery)
            'country' => 'PK',
            'areas' => [
                'Sahiwal City (formerly Montgomery)', 'Sahiwal Cantt', 'Chichawatni (part)', 'Noorshah', 'Kassowal', 'Model Town',
                'Sahiwal City Area', 'Harappa Road', 'Gulshan-e-Sahiwal', 'Madina Colony Sahiwal', 'Peoples Colony Sahiwal',
                'Satellite Town Sahiwal', 'Jinnah Colony Sahiwal', 'Chichawatni Road', 'Noorshah Road', 'Kassowal Road',
                'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Sahiwal Bypass', 'Railway Road Sahiwal',
                'Shahbaz Nagar', 'Faisal Nagar', 'Iqbal Nagar', 'Kachehri Road', 'Harappa Town Area'
            ]
        ],
        'Sahiwal' => [ // second Sahiwal (Sargodha)
            'country' => 'PK',
            'areas' => [
                'Sahiwal (Sargodha)', 'Sahiwal Cantt', 'Bhera (part)', 'Faruka (part)', 'Basti Sahiwal',
                'Sahiwal City (Sargodha)', 'Sargodha Road Sahiwal', 'Sahiwal Bazaar', 'Gulshan-e-Sahiwal Sargodha',
                'Madina Colony Sahiwal Sargodha', 'Peoples Colony Sahiwal Sargodha', 'Sahiwal Model Town',
                'Jinnah Colony Sahiwal Sargodha', 'Bhera Road', 'Faruka Road', 'Chak No 1', 'Chak No 2',
                'Basti Kamboh', 'Basti Cheema', 'Sahiwal Bypass Sargodha', 'Shahbaz Nagar Sahiwal'
            ]
        ],
        'Saidu Sharif' => [
            'country' => 'PK',
            'areas' => [
                'Saidu Sharif City', 'Mingora (adjacent)', 'Saidu Teaching Hospital Area', 'Swat Museum Area', 'Basti Saidu',
                'Saidu Sharif Cantt', 'Mingora Road Saidu', 'Saidu Sharif Bazaar', 'Gulshan-e-Saidu', 'Madina Colony Saidu',
                'Peoples Colony Saidu', 'Saidu Sharif Model Town', 'Jinnah Colony Saidu', 'Swat River Road', 'Saidu Sharif Fort',
                'Government Post Graduate College Area', 'Saadabad', 'Green Hills', 'Basti Shangla', 'Saidu Sharif Bypass'
            ]
        ],
        'Sakrand' => [
            'country' => 'PK',
            'areas' => [
                'Sakrand Town', 'Nawabshah (part)', 'Daur', 'Basti Sakrand', 'Sakrand City',
                'Sakrand Cantt', 'Nawabshah Road Sakrand', 'Sakrand Bazaar', 'Gulshan-e-Sakrand',
                'Madina Colony Sakrand', 'Peoples Colony Sakrand', 'Sakrand Model Town', 'Jinnah Colony Sakrand',
                'Daur Road', 'Basti Jatoi', 'Basti Bukhari', 'Sakrand Bypass', 'Sakrand Railway Station'
            ]
        ],
        'Samaro' => [
            'country' => 'PK',
            'areas' => [
                'Samaro Town', 'Umerkot (part)', 'Kunri', 'Pithoro', 'Basti Samaro', 'Samaro City',
                'Samaro Cantt', 'Umerkot Road Samaro', 'Samaro Bazaar', 'Gulshan-e-Samaro', 'Madina Colony Samaro',
                'Peoples Colony Samaro', 'Samaro Model Town', 'Jinnah Colony Samaro', 'Kunri Road', 'Pithoro Road',
                'Basti Kolhi', 'Basti Meghwar', 'Samaro Bypass'
            ]
        ],
        'Sambrial' => [
            'country' => 'PK',
            'areas' => [
                'Sambrial City', 'Sialkot (part)', 'Daska (part)', 'Model Town', 'Sambrial Cantt',
                'Sialkot Road Sambrial', 'Sambrial Bazaar', 'Gulshan-e-Sambrial', 'Madina Colony Sambrial',
                'Peoples Colony Sambrial', 'Satellite Town Sambrial', 'Jinnah Colony Sambrial', 'Daska Road',
                'Chak No 1', 'Chak No 2', 'Basti Bhatti', 'Basti Cheema', 'Sambrial Bypass', 'Sambrial Railway Station'
            ]
        ],
        'Sanghar' => [
            'country' => 'PK',
            'areas' => [
                'Sanghar City', 'Sanghar Cantt', 'Shahdadpur', 'Tando Adam', 'Jam Nawaz Ali', 'Model Town',
                'Sanghar City Area', 'Hyderabad Road Sanghar', 'Sanghar Bazaar', 'Gulshan-e-Sanghar',
                'Madina Colony Sanghar', 'Peoples Colony Sanghar', 'Satellite Town Sanghar', 'Jinnah Colony Sanghar',
                'Shahdadpur Road', 'Tando Adam Road', 'Jam Nawaz Ali Road', 'Basti Malkani', 'Basti Khaskheli',
                'Sanghar Bypass', 'Sanghar Sugar Mills Area'
            ]
        ],
        'Sangla Hill' => [
            'country' => 'PK',
            'areas' => [
                'Sangla Hill Town', 'Nankana Sahib (part)', 'Shahkot', 'Basti Sangla', 'Sangla Hill City',
                'Sangla Hill Cantt', 'Nankana Sahib Road', 'Sangla Hill Bazaar', 'Gulshan-e-Sangla',
                'Madina Colony Sangla', 'Peoples Colony Sangla', 'Sangla Hill Model Town', 'Jinnah Colony Sangla',
                'Shahkot Road', 'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Sangla Hill Bypass'
            ]
        ],
        'Sanjwal' => [
            'country' => 'PK',
            'areas' => [
                'Sanjwal Town', 'Attock (part)', 'Hazro', 'Kamra', 'Basti Sanjwal', 'Sanjwal City',
                'Sanjwal Cantt', 'Attock Road Sanjwal', 'Sanjwal Bazaar', 'Gulshan-e-Sanjwal', 'Madina Colony Sanjwal',
                'Peoples Colony Sanjwal', 'Sanjwal Model Town', 'Jinnah Colony Sanjwal', 'Hazro Road', 'Kamra Road',
                'Basti Khel', 'Sanjwal Bypass', 'Kamra Airbase Area (nearby)'
            ]
        ],
        'Sann' => [
            'country' => 'PK',
            'areas' => [
                'Sann Town', 'Jamshoro (part)', 'Sehwan (part)', 'Basti Sann', 'Sann City',
                'Sann Cantt', 'Jamshoro Road Sann', 'Sann Bazaar', 'Gulshan-e-Sann', 'Madina Colony Sann',
                'Peoples Colony Sann', 'Sann Model Town', 'Jinnah Colony Sann', 'Sehwan Road',
                'Basti Mirza', 'Basti Malkani', 'Sann Bypass', 'Indus Highway Sann'
            ]
        ],
        'Sarai Alamgir' => [
            'country' => 'PK',
            'areas' => [
                'Sarai Alamgir Town', 'Gujrat (part)', 'Jalalpur Jattan', 'Basti Alamgir', 'Sarai Alamgir City',
                'Sarai Alamgir Cantt', 'Gujrat Road Sarai', 'Sarai Alamgir Bazaar', 'Gulshan-e-Sarai',
                'Madina Colony Sarai', 'Peoples Colony Sarai', 'Sarai Alamgir Model Town', 'Jinnah Colony Sarai',
                'Jalalpur Jattan Road', 'Basti Cheema', 'Basti Virk', 'Sarai Alamgir Bypass'
            ]
        ],
        'Sarai Naurang' => [
            'country' => 'PK',
            'areas' => [
                'Sarai Naurang Town', 'Lakki Marwat (part)', 'Tajori', 'Basti Naurang', 'Sarai Naurang City',
                'Sarai Naurang Cantt', 'Lakki Marwat Road', 'Sarai Naurang Bazaar', 'Gulshan-e-Naurang',
                'Madina Colony Naurang', 'Peoples Colony Naurang', 'Sarai Naurang Model Town', 'Jinnah Colony Naurang',
                'Tajori Road', 'Basti Khel', 'Basti Afghani', 'Sarai Naurang Bypass'
            ]
        ],
        'Sarai Sidhu' => [
            'country' => 'PK',
            'areas' => [
                'Sarai Sidhu Town', 'Khanewal (part)', 'Kabirwala', 'Basti Sidhu', 'Sarai Sidhu City',
                'Sarai Sidhu Cantt', 'Khanewal Road', 'Sarai Sidhu Bazaar', 'Gulshan-e-Sarai Sidhu',
                'Madina Colony Sarai Sidhu', 'Peoples Colony Sarai Sidhu', 'Sarai Sidhu Model Town',
                'Jinnah Colony Sarai Sidhu', 'Kabirwala Road', 'Chak No 1', 'Basti Kharal', 'Sarai Sidhu Bypass'
            ]
        ],
        'Sargodha' => [
            'country' => 'PK',
            'areas' => [
                'Sargodha City', 'Sargodha Cantt', 'Satellite Town', 'University Road', 'Club Road', 'Model Town',
                'Sargodha City Area', 'Lahore Road Sargodha', 'Gulshan-e-Sargodha', 'Madina Colony Sargodha',
                'Peoples Colony Sargodha', 'Jinnah Colony Sargodha', 'Crescent Road', 'Airport Road Sargodha',
                'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Sargodha Bypass', 'Canal Road',
                'Faisalabad Road Sargodha', 'Mianwali Road', 'Shahbaz Nagar', 'Faisal Colony', 'Abbas Town Sargodha',
                'Askari Colony', 'Punjab Cooperative Society', 'Sargodha City Housing Scheme'
            ]
        ],
        'Sehwan' => [
            'country' => 'PK',
            'areas' => [
                'Sehwan Town', 'Sehwan Cantt', 'Bhit Shah (part)', 'Lal Shahbaz Qalandar Area', 'Basti Sehwan',
                'Sehwan City', 'Hyderabad Road Sehwan', 'Sehwan Bazaar', 'Gulshan-e-Sehwan', 'Madina Colony Sehwan',
                'Peoples Colony Sehwan', 'Sehwan Model Town', 'Jinnah Colony Sehwan', 'Bhit Shah Road',
                'Basti Malkani', 'Basti Mirza', 'Sehwan Bypass', 'Indus Highway Sehwan', 'Lal Shahbaz Town',
                'Dargah Area Sehwan', 'Sehwan Fort Area'
            ]
        ],
        'Setharja Old' => [
            'country' => 'PK',
            'areas' => [
                'Setharja Old Town', 'Khairpur (part)', 'Ranipur', 'Basti Setharja', 'Setharja City',
                'Setharja Cantt', 'Khairpur Road Setharja', 'Setharja Bazaar', 'Gulshan-e-Setharja',
                'Madina Colony Setharja', 'Peoples Colony Setharja', 'Setharja Model Town', 'Jinnah Colony Setharja',
                'Ranipur Road', 'Basti Soomro', 'Basti Khairpur', 'Setharja Bypass'
            ]
        ],
        'Shabqadar' => [
            'country' => 'PK',
            'areas' => [
                'Shabqadar Town', 'Charsadda (part)', 'Tangi', 'Basti Shabqadar', 'Shabqadar City',
                'Shabqadar Cantt', 'Charsadda Road Shabqadar', 'Shabqadar Bazaar', 'Gulshan-e-Shabqadar',
                'Madina Colony Shabqadar', 'Peoples Colony Shabqadar', 'Shabqadar Model Town', 'Jinnah Colony Shabqadar',
                'Tangi Road', 'Basti Kaka Khel', 'Basti Mohmand', 'Shabqadar Bypass'
            ]
        ],
        'Shahdad Kot' => [
            'country' => 'PK',
            'areas' => [
                'Shahdad Kot City', 'Qambar (part)', 'Miro Khan', 'Basti Shahdad', 'Shahdad Kot Cantt',
                'Qambar Road', 'Shahdad Kot Bazaar', 'Gulshan-e-Shahdad', 'Madina Colony Shahdad',
                'Peoples Colony Shahdad', 'Shahdad Kot Model Town', 'Jinnah Colony Shahdad', 'Miro Khan Road',
                'Basti Khoso', 'Basti Buriro', 'Shahdad Kot Bypass'
            ]
        ],
        'Shahdadpur' => [
            'country' => 'PK',
            'areas' => [
                'Shahdadpur City', 'Tando Adam (part)', 'Jam Nawaz Ali', 'Basti Shahdad', 'Model Town',
                'Shahdadpur Cantt', 'Sanghar Road Shahdadpur', 'Shahdadpur Bazaar', 'Gulshan-e-Shahdadpur',
                'Madina Colony Shahdadpur', 'Peoples Colony Shahdadpur', 'Satellite Town Shahdadpur', 'Jinnah Colony Shahdadpur',
                'Tando Adam Road', 'Jam Nawaz Ali Road', 'Basti Malkani', 'Basti Khaskheli', 'Shahdadpur Bypass',
                'Shahdadpur Railway Station'
            ]
        ],
        'Shahkot' => [
            'country' => 'PK',
            'areas' => [
                'Shahkot City', 'Nankana Sahib (part)', 'Sangla Hill', 'Basti Shahkot', 'Shahkot Cantt',
                'Nankana Sahib Road Shahkot', 'Shahkot Bazaar', 'Gulshan-e-Shahkot', 'Madina Colony Shahkot',
                'Peoples Colony Shahkot', 'Shahkot Model Town', 'Jinnah Colony Shahkot', 'Sangla Hill Road',
                'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Shahkot Bypass'
            ]
        ],
        'Shahpur' => [
            'country' => 'PK',
            'areas' => [
                'Shahpur City', 'Sargodha (part)', 'Jhawarian', 'Bhalwal (part)', 'Basti Shahpur',
                'Shahpur Cantt', 'Sargodha Road Shahpur', 'Shahpur Bazaar', 'Gulshan-e-Shahpur',
                'Madina Colony Shahpur', 'Peoples Colony Shahpur', 'Shahpur Model Town', 'Jinnah Colony Shahpur',
                'Jhawarian Road', 'Bhalwal Road', 'Chak No 1', 'Basti Kharal', 'Shahpur Bypass', 'Shahpur Fort Area'
            ]
        ],
        'Shahpur Chakar' => [
            'country' => 'PK',
            'areas' => [
                'Shahpur Chakar Town', 'Sanghar (part)', 'Shahdadpur (part)', 'Basti Chakar', 'Shahpur Chakar City',
                'Shahpur Chakar Cantt', 'Sanghar Road', 'Shahpur Chakar Bazaar', 'Gulshan-e-Shahpur Chakar',
                'Madina Colony Chakar', 'Peoples Colony Chakar', 'Shahpur Chakar Model Town', 'Jinnah Colony Chakar',
                'Shahdadpur Road', 'Basti Malkani', 'Basti Khaskheli', 'Shahpur Chakar Bypass'
            ]
        ],
        'Shahr Sultan' => [
            'country' => 'PK',
            'areas' => [
                'Shahr Sultan Town', 'Bahawalpur (part)', 'Hasilpur', 'Basti Sultan', 'Shahr Sultan City',
                'Shahr Sultan Cantt', 'Bahawalpur Road', 'Shahr Sultan Bazaar', 'Gulshan-e-Sultan',
                'Madina Colony Sultan', 'Peoples Colony Sultan', 'Shahr Sultan Model Town', 'Jinnah Colony Sultan',
                'Hasilpur Road', 'Chak No 1', 'Basti Khokhar', 'Shahr Sultan Bypass'
            ]
        ],
        'Shakargarh' => [
            'country' => 'PK',
            'areas' => [
                'Shakargarh City', 'Narowal (part)', 'Zafarwal', 'Basti Shakargarh', 'Shakargarh Cantt',
                'Narowal Road Shakargarh', 'Shakargarh Bazaar', 'Gulshan-e-Shakargarh', 'Madina Colony Shakargarh',
                'Peoples Colony Shakargarh', 'Shakargarh Model Town', 'Jinnah Colony Shakargarh', 'Zafarwal Road',
                'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Shakargarh Bypass'
            ]
        ],
        'Sharqpur Sharif' => [
            'country' => 'PK',
            'areas' => [
                'Sharqpur Sharif Town', 'Sheikhupura (part)', 'Nankana Sahib (part)', 'Basti Sharqpur',
                'Sharqpur Sharif City', 'Sharqpur Sharif Cantt', 'Sheikhupura Road', 'Sharqpur Bazaar',
                'Gulshan-e-Sharqpur', 'Madina Colony Sharqpur', 'Peoples Colony Sharqpur', 'Sharqpur Model Town',
                'Jinnah Colony Sharqpur', 'Nankana Sahib Road', 'Chak No 1', 'Basti Kamboh', 'Sharqpur Bypass'
            ]
        ],
        'Shekhupura' => [
            'country' => 'PK',
            'areas' => [
                'Sheikhupura City', 'Sheikhupura Cantt', 'Muridke', 'Narang Mandi', 'Model Town',
                'Sheikhupura City Area', 'Lahore Road Sheikhupura', 'Gulshan-e-Sheikhupura', 'Madina Colony Sheikhupura',
                'Peoples Colony Sheikhupura', 'Satellite Town Sheikhupura', 'Jinnah Colony Sheikhupura',
                'Muridke Road', 'Narang Mandi Road', 'Chak No 1', 'Chak No 2', 'Basti Gujjar',
                'Basti Jattan', 'Sheikhupura Bypass', 'Hafizabad Road', 'Faisalabad Road Sheikhupura',
                'Kot Abdul Malik (nearby)', 'Shahbaz Nagar', 'Sheikhupura Industrial Area'
            ]
        ],
        'Shikarpur' => [
            'country' => 'PK',
            'areas' => [
                'Shikarpur City', 'Shikarpur Cantt', 'Lakhi', 'Madeji', 'Khanpur', 'Model Town',
                'Shikarpur City Area', 'Larkana Road Shikarpur', 'Shikarpur Bazaar', 'Gulshan-e-Shikarpur',
                'Madina Colony Shikarpur', 'Peoples Colony Shikarpur', 'Satellite Town Shikarpur', 'Jinnah Colony Shikarpur',
                'Lakhi Road', 'Madeji Road', 'Khanpur Road', 'Basti Khosa', 'Basti Baloch', 'Shikarpur Bypass',
                'Shikarpur Fort Area', 'Gur Mandi Shikarpur', 'Railway Road Shikarpur'
            ]
        ],
        'Shingli Bala' => [
            'country' => 'PK',
            'areas' => [
                'Shingli Bala Town', 'Battagram (part)', 'Allai', 'Basti Shingli', 'Shingli Bala City',
                'Shingli Bala Cantt', 'Battagram Road', 'Shingli Bala Bazaar', 'Gulshan-e-Shingli',
                'Madina Colony Shingli', 'Peoples Colony Shingli', 'Shingli Bala Model Town', 'Jinnah Colony Shingli',
                'Allai Road', 'Basti Khan', 'Basti Gujjar', 'Shingli Bala Valley'
            ]
        ],
        'Shinpokh' => [
            'country' => 'PK',
            'areas' => [
                'Shinpokh Town', 'Khyber (part)', 'Landi Kotal (part)', 'Basti Shinpokh', 'Shinpokh City',
                'Shinpokh Cantt', 'Khyber Road', 'Shinpokh Bazaar', 'Gulshan-e-Shinpokh', 'Madina Colony Shinpokh',
                'Peoples Colony Shinpokh', 'Shinpokh Model Town', 'Jinnah Colony Shinpokh', 'Landi Kotal Road',
                'Basti Afridi', 'Basti Shinwari', 'Shinpokh Bypass'
            ]
        ],
        'Shorkot' => [
            'country' => 'PK',
            'areas' => [
                'Shorkot City', 'Jhang (part)', 'Garh Maharaja', 'Basti Shorkot', 'Shorkot Cantt',
                'Jhang Road Shorkot', 'Shorkot Bazaar', 'Gulshan-e-Shorkot', 'Madina Colony Shorkot',
                'Peoples Colony Shorkot', 'Shorkot Model Town', 'Jinnah Colony Shorkot', 'Garh Maharaja Road',
                'Chak No 1', 'Chak No 2', 'Basti Kharal', 'Basti Jattan', 'Shorkot Bypass'
            ]
        ],
        'Shujaabad' => [
            'country' => 'PK',
            'areas' => [
                'Shujaabad City', 'Multan (part)', 'Jalalpur Pirwala', 'Basti Shuja', 'Shujaabad Cantt',
                'Multan Road Shujaabad', 'Shujaabad Bazaar', 'Gulshan-e-Shujaabad', 'Madina Colony Shujaabad',
                'Peoples Colony Shujaabad', 'Shujaabad Model Town', 'Jinnah Colony Shujaabad', 'Jalalpur Pirwala Road',
                'Chak No 1', 'Basti Khokhar', 'Basti Jattan', 'Shujaabad Bypass'
            ]
        ],
        'Sialkot' => [
            'country' => 'PK',
            'areas' => [
                'Sialkot City', 'Sialkot Cantt', 'Pasrur', 'Daska', 'Sambrial', 'Kotli Loharan', 'Model Town',
                'Sialkot City Area', 'Zafarwal Road', 'Pasrur Road', 'Gulshan-e-Sialkot', 'Madina Colony Sialkot',
                'Peoples Colony Sialkot', 'Satellite Town Sialkot', 'Jinnah Colony Sialkot', 'Daska Road',
                'Sambrial Road', 'Kotli Loharan Road', 'Chak No 1', 'Chak No 2', 'Basti Bhatti', 'Basti Cheema',
                'Sialkot Bypass', 'Sialkot Airport Road', 'Paris Road', 'Kashmir Road Sialkot', 'Sialkot Export Processing Zone'
            ]
        ],
        'Sibi' => [
            'country' => 'PK',
            'areas' => [
                'Sibi City', 'Sibi Cantt', 'Kohlu (part)', 'Killi Sibi', 'Model Town',
                'Sibi City Area', 'Quetta Road Sibi', 'Sibi Bazaar', 'Gulshan-e-Sibi', 'Madina Colony Sibi',
                'Peoples Colony Sibi', 'Satellite Town Sibi', 'Jinnah Colony Sibi', 'Kohlu Road',
                'Killi Kakar', 'Killi Ahmadzai', 'Sibi Bypass', 'Sibi Fort Area', 'Sibi Railway Station',
                'Bibi Nani Area', 'Sibi Mela Ground'
            ]
        ],
        'Sillanwali' => [
            'country' => 'PK',
            'areas' => [
                'Sillanwali Town', 'Sargodha (part)', 'Sahiwal (Sargodha)', 'Basti Sillan', 'Sillanwali City',
                'Sillanwali Cantt', 'Sargodha Road Sillanwali', 'Sillanwali Bazaar', 'Gulshan-e-Sillan',
                'Madina Colony Sillan', 'Peoples Colony Sillan', 'Sillanwali Model Town', 'Jinnah Colony Sillan',
                'Sahiwal Road', 'Chak No 1', 'Basti Kharal', 'Sillanwali Bypass'
            ]
        ],
        'Sinjhoro' => [
            'country' => 'PK',
            'areas' => [
                'Sinjhoro Town', 'Sanghar (part)', 'Shahdadpur (part)', 'Basti Sinjhoro', 'Sinjhoro City',
                'Sinjhoro Cantt', 'Sanghar Road Sinjhoro', 'Sinjhoro Bazaar', 'Gulshan-e-Sinjhoro',
                'Madina Colony Sinjhoro', 'Peoples Colony Sinjhoro', 'Sinjhoro Model Town', 'Jinnah Colony Sinjhoro',
                'Shahdadpur Road', 'Basti Malkani', 'Basti Khaskheli', 'Sinjhoro Bypass'
            ]
        ],
        'Skardu' => [
            'country' => 'PK',
            'areas' => [
                'Skardu City', 'Skardu Cantt', 'Kachura', 'Shigar', 'Satpara', 'Basti Skardu',
                'Skardu City Area', 'Karakoram Highway Skardu', 'Skardu Bazaar', 'Gulshan-e-Skardu',
                'Madina Colony Skardu', 'Peoples Colony Skardu', 'Skardu Model Town', 'Jinnah Colony Skardu',
                'Kachura Road', 'Shigar Road', 'Satpara Road', 'Basti Balti', 'Skardu Fort', 'Manthal',
                'Skardu Bypass', 'Skardu Airport Area', 'Katpana', 'Shigar Valley', 'Deosai Road (nearby)'
            ]
        ],
        'Sobhodero' => [
            'country' => 'PK',
            'areas' => [
                'Sobhodero Town', 'Khairpur (part)', 'Gambat', 'Basti Sobho', 'Sobhodero City',
                'Sobhodero Cantt', 'Khairpur Road Sobho', 'Sobhodero Bazaar', 'Gulshan-e-Sobho',
                'Madina Colony Sobho', 'Peoples Colony Sobho', 'Sobhodero Model Town', 'Jinnah Colony Sobho',
                'Gambat Road', 'Basti Soomro', 'Basti Khairpur', 'Sobhodero Bypass'
            ]
        ],
        'Sodhri' => [
            'country' => 'PK',
            'areas' => [
                'Sodhri Town', 'Gujranwala (part)', 'Wazirabad (part)', 'Basti Sodhri', 'Sodhri City',
                'Sodhri Cantt', 'Gujranwala Road Sodhri', 'Sodhri Bazaar', 'Gulshan-e-Sodhri',
                'Madina Colony Sodhri', 'Peoples Colony Sodhri', 'Sodhri Model Town', 'Jinnah Colony Sodhri',
                'Wazirabad Road', 'Chak No 1', 'Basti Cheema', 'Basti Virk', 'Sodhri Bypass'
            ]
        ],
        'Sohbatpur' => [
            'country' => 'PK',
            'areas' => [
                'Sohbatpur Town', 'Jafarabad (part)', 'Usta Muhammad', 'Basti Sohbat', 'Sohbatpur City',
                'Sohbatpur Cantt', 'Jafarabad Road', 'Sohbatpur Bazaar', 'Gulshan-e-Sohbat', 'Madina Colony Sohbat',
                'Peoples Colony Sohbat', 'Sohbatpur Model Town', 'Jinnah Colony Sohbat', 'Usta Muhammad Road',
                'Basti Khoso', 'Basti Baloch', 'Sohbatpur Bypass'
            ]
        ],
        'Sukheke Mandi' => [
            'country' => 'PK',
            'areas' => [
                'Sukheke Mandi Town', 'Hafizabad (part)', 'Pindi Bhattian', 'Basti Sukheke', 'Sukheke Mandi City',
                'Sukheke Mandi Cantt', 'Hafizabad Road', 'Sukheke Bazaar', 'Gulshan-e-Sukheke', 'Madina Colony Sukheke',
                'Peoples Colony Sukheke', 'Sukheke Model Town', 'Jinnah Colony Sukheke', 'Pindi Bhattian Road',
                'Chak No 1', 'Chak No 2', 'Basti Kamboh', 'Sukheke Bypass'
            ]
        ],
        'Sukkur' => [
            'country' => 'PK',
            'areas' => [
                'Sukkur City', 'Sukkur Cantt', 'Rohri', 'Pano Aqil', 'Model Town',
                'Sukkur City Area', 'Lansdowne Bridge Road', 'Sukkur Bazaar', 'Gulshan-e-Sukkur',
                'Madina Colony Sukkur', 'Peoples Colony Sukkur', 'Satellite Town Sukkur', 'Jinnah Colony Sukkur',
                'Rohri Road', 'Pano Aqil Road', 'Basti Laghari', 'Basti Khoso', 'Sukkur Bypass',
                'Sukkur Railway Station', 'Sukkur Barrage Area', 'Airport Road Sukkur', 'Military Road Sukkur',
                'Shahi Bazaar', 'Ghauspur Road', 'Sukkur Industrial Area'
            ]
        ],
        'Surab' => [
            'country' => 'PK',
            'areas' => [
                'Surab Town', 'Kalat (part)', 'Killi Surab', 'Basti Surab', 'Surab City',
                'Surab Cantt', 'Kalat Road', 'Surab Bazaar', 'Gulshan-e-Surab', 'Madina Colony Surab',
                'Peoples Colony Surab', 'Surab Model Town', 'Jinnah Colony Surab', 'Killi Abdul Khel',
                'Killi Mirdad', 'Surab Bypass', 'Surab Valley'
            ]
        ],
        'Surkhpur' => [
            'country' => 'PK',
            'areas' => [
                'Surkhpur Town', 'Gujrat (part)', 'Kharian', 'Basti Surkhpur', 'Surkhpur City',
                'Surkhpur Cantt', 'Gujrat Road Surkhpur', 'Surkhpur Bazaar', 'Gulshan-e-Surkhpur',
                'Madina Colony Surkhpur', 'Peoples Colony Surkhpur', 'Surkhpur Model Town', 'Jinnah Colony Surkhpur',
                'Kharian Road', 'Basti Cheema', 'Basti Gujjar', 'Surkhpur Bypass'
            ]
        ],
        'Swabi' => [
            'country' => 'PK',
            'areas' => [
                'Swabi City', 'Swabi Cantt', 'Topi', 'Zaida', 'Basti Swabi', 'Model Town',
                'Swabi City Area', 'Mardan Road Swabi', 'Swabi Bazaar', 'Gulshan-e-Swabi', 'Madina Colony Swabi',
                'Peoples Colony Swabi', 'Satellite Town Swabi', 'Jinnah Colony Swabi', 'Topi Road', 'Zaida Road',
                'Basti Kaka Khel', 'Basti Mohmand', 'Swabi Bypass', 'Swabi Interchange', 'Yar Hussain',
                'Kunda', 'Manki', 'Swabi Industrial Area'
            ]
        ],
        'Sīta Road' => [
            'country' => 'PK',
            'areas' => [
                'Sita Road Town', 'Jamshoro (part)', 'Kotri (part)', 'Basti Sita', 'Sita Road City',
                'Sita Road Cantt', 'Jamshoro Road Sita', 'Sita Road Bazaar', 'Gulshan-e-Sita', 'Madina Colony Sita',
                'Peoples Colony Sita', 'Sita Road Model Town', 'Jinnah Colony Sita', 'Kotri Road',
                'Basti Mirza', 'Basti Malkani', 'Sita Road Bypass', 'Indus Highway Sita'
            ]
        ],
        'Talagang' => [
            'country' => 'PK',
            'areas' => [
                'Talagang City', 'Chakwal (part)', 'Lawa', 'Basti Talagang', 'Talagang Cantt',
                'Talagang City Area', 'Chakwal Road Talagang', 'Talagang Bazaar', 'Gulshan-e-Talagang',
                'Madina Colony Talagang', 'Peoples Colony Talagang', 'Talagang Model Town', 'Jinnah Colony Talagang',
                'Lawa Road', 'Basti Malik', 'Basti Khel', 'Talagang Bypass', 'Talagang Fort Area',
                'Mianwali Road Talagang', 'Dhurnal', 'Multan Khurd'
            ]
        ],
        'Talamba' => [
            'country' => 'PK',
            'areas' => [
                'Talamba Town', 'Khanewal (part)', 'Mian Channun', 'Basti Talamba', 'Talamba City',
                'Talamba Cantt', 'Khanewal Road Talamba', 'Talamba Bazaar', 'Gulshan-e-Talamba',
                'Madina Colony Talamba', 'Peoples Colony Talamba', 'Talamba Model Town', 'Jinnah Colony Talamba',
                'Mian Channun Road', 'Chak No 1', 'Chak No 2', 'Basti Kharal', 'Basti Jattan', 'Talamba Bypass'
            ]
        ],
        'Talhar' => [
            'country' => 'PK',
            'areas' => [
                'Talhar Town', 'Badin (part)', 'Matli', 'Basti Talhar', 'Talhar City',
                'Talhar Cantt', 'Badin Road Talhar', 'Talhar Bazaar', 'Gulshan-e-Talhar', 'Madina Colony Talhar',
                'Peoples Colony Talhar', 'Talhar Model Town', 'Jinnah Colony Talhar', 'Matli Road',
                'Basti Mallah', 'Basti Mirza', 'Talhar Bypass', 'Talhar Fish Harbour Area'
            ]
        ],
        'Tandlianwala' => [
            'country' => 'PK',
            'areas' => [
                'Tandlianwala Town', 'Faisalabad (part)', 'Jaranwala', 'Basti Tandlian', 'Tandlianwala City',
                'Tandlianwala Cantt', 'Faisalabad Road Tandlian', 'Tandlianwala Bazaar', 'Gulshan-e-Tandlian',
                'Madina Colony Tandlian', 'Peoples Colony Tandlian', 'Tandlianwala Model Town', 'Jinnah Colony Tandlian',
                'Jaranwala Road', 'Chak No 1', 'Chak No 2', 'Basti Kamboh', 'Basti Gujjar', 'Tandlianwala Bypass',
                'Tandlianwala Railway Station'
            ]
        ],
        'Tando Adam' => [
            'country' => 'PK',
            'areas' => [
                'Tando Adam City', 'Sanghar (part)', 'Shahdadpur', 'Model Town', 'Tando Adam Cantt',
                'Tando Adam City Area', 'Sanghar Road Tando Adam', 'Tando Adam Bazaar', 'Gulshan-e-Tando Adam',
                'Madina Colony Tando Adam', 'Peoples Colony Tando Adam', 'Satellite Town Tando Adam', 'Jinnah Colony Tando Adam',
                'Shahdadpur Road', 'Basti Malkani', 'Basti Khaskheli', 'Tando Adam Bypass', 'Tando Adam Railway Station'
            ]
        ],
        'Tando Allahyar' => [
            'country' => 'PK',
            'areas' => [
                'Tando Allahyar City', 'Tando Allahyar Cantt', 'Chamber (part)', 'Basti Allahyar', 'Tando Allahyar City Area',
                'Hyderabad Road Tando Allahyar', 'Tando Allahyar Bazaar', 'Gulshan-e-Allahyar', 'Madina Colony Allahyar',
                'Peoples Colony Allahyar', 'Satellite Town Allahyar', 'Jinnah Colony Allahyar', 'Chamber Road',
                'Basti Soomro', 'Basti Khaskheli', 'Tando Allahyar Bypass', 'Rajo Khanani Road'
            ]
        ],
        'Tando Bago' => [
            'country' => 'PK',
            'areas' => [
                'Tando Bago Town', 'Badin (part)', 'Matli (part)', 'Basti Bago', 'Tando Bago City',
                'Tando Bago Cantt', 'Badin Road Bago', 'Tando Bago Bazaar', 'Gulshan-e-Bago', 'Madina Colony Bago',
                'Peoples Colony Bago', 'Tando Bago Model Town', 'Jinnah Colony Bago', 'Matli Road',
                'Basti Mallah', 'Basti Mirza', 'Tando Bago Bypass'
            ]
        ],
        'Tando Jam' => [
            'country' => 'PK',
            'areas' => [
                'Tando Jam Town', 'Hyderabad (part)', 'Tando Hyder', 'Basti Jam', 'Tando Jam City',
                'Tando Jam Cantt', 'Hyderabad Road Tando Jam', 'Tando Jam Bazaar', 'Gulshan-e-Tando Jam',
                'Madina Colony Tando Jam', 'Peoples Colony Tando Jam', 'Tando Jam Model Town', 'Jinnah Colony Tando Jam',
                'Tando Hyder Road', 'Basti Mirza', 'Basti Malkani', 'Tando Jam Bypass', 'Sindh Agriculture University Area'
            ]
        ],
        'Tando Mitha Khan' => [
            'country' => 'PK',
            'areas' => [
                'Tando Mitha Khan Town', 'Sanghar (part)', 'Tando Adam (part)', 'Basti Mitha', 'Tando Mitha Khan City',
                'Tando Mitha Khan Cantt', 'Sanghar Road', 'Tando Mitha Bazaar', 'Gulshan-e-Mitha', 'Madina Colony Mitha',
                'Peoples Colony Mitha', 'Tando Mitha Model Town', 'Jinnah Colony Mitha', 'Tando Adam Road',
                'Basti Khaskheli', 'Basti Malkani', 'Tando Mitha Bypass'
            ]
        ],
        'Tando Muhammad Khan' => [
            'country' => 'PK',
            'areas' => [
                'Tando Muhammad Khan City', 'Tando Muhammad Khan Cantt', 'Basti Muhammad Khan', 'Tando Muhammad Khan City Area',
                'Hyderabad Road TMK', 'Tando Muhammad Khan Bazaar', 'Gulshan-e-Tando Muhammad', 'Madina Colony TMK',
                'Peoples Colony TMK', 'Satellite Town TMK', 'Jinnah Colony TMK', 'Basti Soomro', 'Basti Khaskheli',
                'TMK Bypass', 'Tando Muhammad Khan Railway Station'
            ]
        ],
        'Tangi' => [
            'country' => 'PK',
            'areas' => [
                'Tangi Town', 'Charsadda (part)', 'Shabqadar', 'Basti Tangi', 'Tangi City',
                'Tangi Cantt', 'Charsadda Road Tangi', 'Tangi Bazaar', 'Gulshan-e-Tangi', 'Madina Colony Tangi',
                'Peoples Colony Tangi', 'Tangi Model Town', 'Jinnah Colony Tangi', 'Shabqadar Road',
                'Basti Kaka Khel', 'Basti Mohmand', 'Tangi Bypass'
            ]
        ],
        'Tangwani' => [
            'country' => 'PK',
            'areas' => [
                'Tangwani Town', 'Kashmore (part)', 'Kandhkot', 'Basti Tangwani', 'Tangwani City',
                'Tangwani Cantt', 'Kashmore Road', 'Tangwani Bazaar', 'Gulshan-e-Tangwani', 'Madina Colony Tangwani',
                'Peoples Colony Tangwani', 'Tangwani Model Town', 'Jinnah Colony Tangwani', 'Kandhkot Road',
                'Basti Khoso', 'Basti Baloch', 'Tangwani Bypass'
            ]
        ],
        'Tank' => [
            'country' => 'PK',
            'areas' => [
                'Tank City', 'Tank Cantt', 'Jandola', 'Basti Tank', 'Tank City Area',
                'Dera Ismail Khan Road Tank', 'Tank Bazaar', 'Gulshan-e-Tank', 'Madina Colony Tank',
                'Peoples Colony Tank', 'Satellite Town Tank', 'Jinnah Colony Tank', 'Jandola Road',
                'Basti Khel', 'Basti Afghani', 'Tank Bypass', 'Tank Fort Area'
            ]
        ],
        'Taunsa' => [
            'country' => 'PK',
            'areas' => [
                'Taunsa Town (Taunsa Sharif)', 'Dera Ghazi Khan (part)', 'Kot Chutta', 'Basti Taunsa', 'Taunsa City',
                'Taunsa Cantt', 'Dera Ghazi Khan Road Taunsa', 'Taunsa Bazaar', 'Gulshan-e-Taunsa', 'Madina Colony Taunsa',
                'Peoples Colony Taunsa', 'Taunsa Model Town', 'Jinnah Colony Taunsa', 'Kot Chutta Road',
                'Basti Khar', 'Basti Mian', 'Taunsa Bypass', 'Taunsa Barrage Area', 'Taunsa Sharif Shrine Area'
            ]
        ],
        'Thal' => [
            'country' => 'PK',
            'areas' => [
                'Thal Town', 'Dir (part)', 'Wari', 'Basti Thal', 'Thal City',
                'Thal Cantt', 'Dir Road Thal', 'Thal Bazaar', 'Gulshan-e-Thal', 'Madina Colony Thal',
                'Peoples Colony Thal', 'Thal Model Town', 'Jinnah Colony Thal', 'Wari Road',
                'Basti Khan', 'Basti Gujjar', 'Thal Bypass', 'Thal Valley'
            ]
        ],
        'Tharu Shah' => [
            'country' => 'PK',
            'areas' => [
                'Tharu Shah Town', 'Naushahro Feroze (part)', 'Moro', 'Basti Tharu', 'Tharu Shah City',
                'Tharu Shah Cantt', 'Naushahro Feroze Road', 'Tharu Shah Bazaar', 'Gulshan-e-Tharu', 'Madina Colony Tharu',
                'Peoples Colony Tharu', 'Tharu Shah Model Town', 'Jinnah Colony Tharu', 'Moro Road',
                'Basti Bhutto', 'Basti Unar', 'Tharu Shah Bypass'
            ]
        ],
        'Thatta' => [
            'country' => 'PK',
            'areas' => [
                'Thatta City', 'Thatta Cantt', 'Mirpur Bathoro', 'Makli', 'Basti Thatta', 'Thatta City Area',
                'Karachi Road Thatta', 'Thatta Bazaar', 'Gulshan-e-Thatta', 'Madina Colony Thatta', 'Peoples Colony Thatta',
                'Satellite Town Thatta', 'Jinnah Colony Thatta', 'Mirpur Bathoro Road', 'Makli Road', 'Basti Mallah',
                'Basti Mirza', 'Thatta Bypass', 'Makli Necropolis Area', 'Keenjhar Lake Road', 'Thatta Fort Area'
            ]
        ],
        'Thul' => [
            'country' => 'PK',
            'areas' => [
                'Thul Town', 'Jacobabad (part)', 'Garhi Khairo', 'Basti Thul', 'Thul City',
                'Thul Cantt', 'Jacobabad Road Thul', 'Thul Bazaar', 'Gulshan-e-Thul', 'Madina Colony Thul',
                'Peoples Colony Thul', 'Thul Model Town', 'Jinnah Colony Thul', 'Garhi Khairo Road',
                'Basti Khoso', 'Basti Baloch', 'Thul Bypass'
            ]
        ],
        'Timargara' => [
            'country' => 'PK',
            'areas' => [
                'Timargara City', 'Dir (part)', 'Chakdara', 'Basti Timargara', 'Timargara Cantt',
                'Dir Road Timargara', 'Timargara Bazaar', 'Gulshan-e-Timargara', 'Madina Colony Timargara',
                'Peoples Colony Timargara', 'Timargara Model Town', 'Jinnah Colony Timargara', 'Chakdara Road',
                'Basti Khan', 'Basti Gujjar', 'Timargara Bypass', 'Timargara Valley'
            ]
        ],
        'Toba Tek Singh' => [
            'country' => 'PK',
            'areas' => [
                'Toba Tek Singh City', 'Toba Tek Singh Cantt', 'Kamalia', 'Gojra', 'Model Town',
                'Toba Tek Singh City Area', 'Faisalabad Road TTS', 'Toba Tek Singh Bazaar', 'Gulshan-e-Toba',
                'Madina Colony TTS', 'Peoples Colony TTS', 'Satellite Town TTS', 'Jinnah Colony TTS',
                'Kamalia Road', 'Gojra Road', 'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan',
                'Toba Bypass', 'Toba Tek Singh Railway Station', 'Pir Mahal Road', 'Shahbaz Nagar'
            ]
        ],
        'Topi' => [
            'country' => 'PK',
            'areas' => [
                'Topi Town', 'Swabi (part)', 'Zaida', 'Basti Topi', 'Topi City',
                'Topi Cantt', 'Swabi Road Topi', 'Topi Bazaar', 'Gulshan-e-Topi', 'Madina Colony Topi',
                'Peoples Colony Topi', 'Topi Model Town', 'Jinnah Colony Topi', 'Zaida Road',
                'Basti Kaka Khel', 'Basti Mohmand', 'Topi Bypass', 'Topi Industrial Area', 'Gadoon Amazai'
            ]
        ],
        'Turbat' => [
            'country' => 'PK',
            'areas' => [
                'Turbat City', 'Turbat Cantt', 'Kech', 'Mand', 'Basti Turbat', 'Turbat City Area',
                'Kech Road', 'Turbat Bazaar', 'Gulshan-e-Turbat', 'Madina Colony Turbat', 'Peoples Colony Turbat',
                'Satellite Town Turbat', 'Jinnah Colony Turbat', 'Mand Road', 'Basti Baloch', 'Basti Mirza',
                'Turbat Bypass', 'Turbat Fort Area', 'Turbat River Area', 'Gichk', 'Turbat Airport Area'
            ]
        ],
        'Ubauro' => [
            'country' => 'PK',
            'areas' => [
                'Ubauro Town', 'Ghotki (part)', 'Daharki', 'Basti Ubauro', 'Ubauro City',
                'Ubauro Cantt', 'Ghotki Road Ubauro', 'Ubauro Bazaar', 'Gulshan-e-Ubauro',
                'Madina Colony Ubauro', 'Peoples Colony Ubauro', 'Ubauro Model Town', 'Jinnah Colony Ubauro',
                'Daharki Road', 'Basti Khoso', 'Basti Mahar', 'Ubauro Bypass', 'Ubauro Railway Station'
            ]
        ],
        'Umarkot' => [
            'country' => 'PK',
            'areas' => [
                'Umarkot City (Umerkot)', 'Umerkot Cantt', 'Kunri', 'Samaro', 'Basti Umerkot',
                'Umarkot City Area', 'Mirpur Khas Road', 'Umarkot Bazaar', 'Gulshan-e-Umarkot',
                'Madina Colony Umarkot', 'Peoples Colony Umarkot', 'Satellite Town Umarkot', 'Jinnah Colony Umarkot',
                'Kunri Road', 'Samaro Road', 'Basti Kolhi', 'Basti Meghwar', 'Umarkot Bypass',
                'Umerkot Fort Area', 'Kingsri', 'Pithoro Road'
            ]
        ],
        'Upper Dir' => [
            'country' => 'PK',
            'areas' => [
                'Upper Dir City (Dir)', 'Dir Cantt', 'Wari', 'Sheringal', 'Basti Dir', 'Upper Dir City Area',
                'Chitral Road Dir', 'Dir Bazaar', 'Gulshan-e-Dir', 'Madina Colony Dir', 'Peoples Colony Dir',
                'Satellite Town Dir', 'Jinnah Colony Dir', 'Wari Road', 'Sheringal Road', 'Basti Khan',
                'Basti Gujjar', 'Dir Bypass', 'Dir Valley', 'Laram Top', 'Shahi Bagh', 'Kalkot', 'Doog'
            ]
        ],
        'Usta Muhammad' => [
            'country' => 'PK',
            'areas' => [
                'Usta Muhammad Town', 'Jafarabad (part)', 'Dera Allahyar', 'Basti Usta', 'Usta Muhammad City',
                'Usta Muhammad Cantt', 'Jafarabad Road', 'Usta Muhammad Bazaar', 'Gulshan-e-Usta',
                'Madina Colony Usta', 'Peoples Colony Usta', 'Usta Muhammad Model Town', 'Jinnah Colony Usta',
                'Dera Allahyar Road', 'Basti Khoso', 'Basti Baloch', 'Usta Muhammad Bypass'
            ]
        ],
        'Uthal' => [
            'country' => 'PK',
            'areas' => [
                'Uthal Town', 'Lasbela (part)', 'Bela', 'Hub (part)', 'Basti Uthal', 'Uthal City',
                'Uthal Cantt', 'Karachi Road Uthal', 'Uthal Bazaar', 'Gulshan-e-Uthal', 'Madina Colony Uthal',
                'Peoples Colony Uthal', 'Uthal Model Town', 'Jinnah Colony Uthal', 'Bela Road', 'Hub Road',
                'Basti Mirza', 'Basti Baloch', 'Uthal Bypass', 'Uthal Industrial Area'
            ]
        ],
        'Utmanzai' => [
            'country' => 'PK',
            'areas' => [
                'Utmanzai Town', 'Charsadda (part)', 'Rajjar', 'Basti Utmanzai', 'Utmanzai City',
                'Utmanzai Cantt', 'Charsadda Road Utmanzai', 'Utmanzai Bazaar', 'Gulshan-e-Utmanzai',
                'Madina Colony Utmanzai', 'Peoples Colony Utmanzai', 'Utmanzai Model Town', 'Jinnah Colony Utmanzai',
                'Rajjar Road', 'Basti Kaka Khel', 'Basti Mohmand', 'Utmanzai Bypass'
            ]
        ],
        'Vihari' => [
            'country' => 'PK',
            'areas' => [
                'Vehari City (Vihari)', 'Vehari Cantt', 'Mailsi', 'Burewala', 'Basti Vehari', 'Vehari City Area',
                'Multan Road Vehari', 'Vehari Bazaar', 'Gulshan-e-Vehari', 'Madina Colony Vehari',
                'Peoples Colony Vehari', 'Satellite Town Vehari', 'Jinnah Colony Vehari', 'Mailsi Road',
                'Burewala Road', 'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Vehari Bypass',
                'Vehari Railway Station', 'Luddan Road', 'Shahbaz Nagar'
            ]
        ],
        'Wana' => [
            'country' => 'PK',
            'areas' => [
                'Wana Town', 'South Waziristan (part)', 'Tank (part)', 'Basti Wana', 'Wana City',
                'Wana Cantt', 'Tank Road Wana', 'Wana Bazaar', 'Gulshan-e-Wana', 'Madina Colony Wana',
                'Peoples Colony Wana', 'Wana Model Town', 'Jinnah Colony Wana', 'Basti Mehsud',
                'Basti Wazir', 'Wana Bypass', 'Wana Fort Area', 'Kani Gurram', 'Shakai'
            ]
        ],
        'Warah' => [
            'country' => 'PK',
            'areas' => [
                'Warah Town', 'Qambar (part)', 'Shahdad Kot', 'Basti Warah', 'Warah City',
                'Warah Cantt', 'Qambar Road Warah', 'Warah Bazaar', 'Gulshan-e-Warah', 'Madina Colony Warah',
                'Peoples Colony Warah', 'Warah Model Town', 'Jinnah Colony Warah', 'Shahdad Kot Road',
                'Basti Khoso', 'Basti Buriro', 'Warah Bypass'
            ]
        ],
        'Wazirabad' => [
            'country' => 'PK',
            'areas' => [
                'Wazirabad City', 'Wazirabad Cantt', 'Rasoolnagar', 'Basti Wazirabad', 'Wazirabad City Area',
                'Gujranwala Road Wazirabad', 'Wazirabad Bazaar', 'Gulshan-e-Wazirabad', 'Madina Colony Wazirabad',
                'Peoples Colony Wazirabad', 'Satellite Town Wazirabad', 'Jinnah Colony Wazirabad', 'Rasoolnagar Road',
                'Chak No 1', 'Chak No 2', 'Basti Cheema', 'Basti Virk', 'Wazirabad Bypass', 'Wazirabad Railway Station',
                'Wazirabad Industrial Area', 'Qadirpur Road', 'Sialkot Road Wazirabad'
            ]
        ],
        'Yazman' => [
            'country' => 'PK',
            'areas' => [
                'Yazman Town', 'Bahawalpur (part)', 'Hasilpur', 'Basti Yazman', 'Yazman City',
                'Yazman Cantt', 'Bahawalpur Road Yazman', 'Yazman Bazaar', 'Gulshan-e-Yazman',
                'Madina Colony Yazman', 'Peoples Colony Yazman', 'Yazman Model Town', 'Jinnah Colony Yazman',
                'Hasilpur Road', 'Chak No 1', 'Chak No 2', 'Basti Khokhar', 'Yazman Bypass', 'Yazman Fort Area'
            ]
        ],
        'Zafarwal' => [
            'country' => 'PK',
            'areas' => [
                'Zafarwal Town', 'Narowal (part)', 'Shakargarh', 'Basti Zafarwal', 'Zafarwal City',
                'Zafarwal Cantt', 'Narowal Road Zafarwal', 'Zafarwal Bazaar', 'Gulshan-e-Zafarwal',
                'Madina Colony Zafarwal', 'Peoples Colony Zafarwal', 'Zafarwal Model Town', 'Jinnah Colony Zafarwal',
                'Shakargarh Road', 'Chak No 1', 'Chak No 2', 'Basti Gujjar', 'Basti Jattan', 'Zafarwal Bypass'
            ]
        ],
        'Zahir Pir' => [
            'country' => 'PK',
            'areas' => [
                'Zahir Pir Town', 'Rahim Yar Khan (part)', 'Khanpur', 'Basti Zahir', 'Zahir Pir City',
                'Zahir Pir Cantt', 'Rahim Yar Khan Road', 'Zahir Pir Bazaar', 'Gulshan-e-Zahir', 'Madina Colony Zahir',
                'Peoples Colony Zahir', 'Zahir Pir Model Town', 'Jinnah Colony Zahir', 'Khanpur Road',
                'Chak No 1', 'Basti Khokhar', 'Zahir Pir Bypass'
            ]
        ],
        'Zaida' => [
            'country' => 'PK',
            'areas' => [
                'Zaida Town', 'Swabi (part)', 'Topi', 'Basti Zaida', 'Zaida City',
                'Zaida Cantt', 'Swabi Road Zaida', 'Zaida Bazaar', 'Gulshan-e-Zaida', 'Madina Colony Zaida',
                'Peoples Colony Zaida', 'Zaida Model Town', 'Jinnah Colony Zaida', 'Topi Road',
                'Basti Kaka Khel', 'Basti Mohmand', 'Zaida Bypass'
            ]
        ],
        'Zhob' => [
            'country' => 'PK',
            'areas' => [
                'Zhob City', 'Zhob Cantt', 'Qilla Saifullah (part)', 'Killi Zhob', 'Zhob City Area',
                'Quetta Road Zhob', 'Zhob Bazaar', 'Gulshan-e-Zhob', 'Madina Colony Zhob', 'Peoples Colony Zhob',
                'Satellite Town Zhob', 'Jinnah Colony Zhob', 'Qilla Saifullah Road', 'Killi Kakar', 'Killi Ahmadzai',
                'Zhob Bypass', 'Zhob Fort Area', 'Zhob River Area', 'Sherani Road'
            ]
        ],
        'Ziarat' => [
            'country' => 'PK',
            'areas' => [
                'Ziarat City', 'Ziarat Cantt', 'Sandeman', 'Killi Ziarat', 'Juniper Forest Area',
                'Ziarat City Area', 'Quetta Road Ziarat', 'Ziarat Bazaar', 'Gulshan-e-Ziarat', 'Madina Colony Ziarat',
                'Peoples Colony Ziarat', 'Ziarat Model Town', 'Jinnah Colony Ziarat', 'Sandeman Road',
                'Killi Kakar', 'Ziarat Bypass', 'Ziarat Residency Area', 'Juniper Bagh', 'Khawaja Khizar Area',
                'Ziarat Valley', 'Gharibabad Ziarat'
            ]
        ]
        ];

        foreach ($citiesData as $cityName => $data) {
            $city = City::where('name', $cityName)
                        ->where('country', $data['country'])
                        ->first();

            if (!$city) {
                $this->command->error("City \"{$cityName}\" ({$data['country']}) not found. Skipping.");
                continue;
            }

            $count = 0;
            foreach ($data['areas'] as $areaName) {
                Region::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'name'    => $areaName,
                    ],
                    []
                );
                $count++;
            }

            $this->command->info("{$count} regions for {$cityName} seeded.");
        }
    }
}