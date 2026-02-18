declare namespace App.Data {
export type AdData = {
id: number;
user_id: number;
category_id: number;
brand_id: number | null;
ad_title: string;
description: string | null;
price: number | null;
city: string | null;
location: string | null;
seller_name: string | null;
seller_phone: string | null;
images: Array<any> | null;
brand: App.Data.BrandData | null;
category: any | null;
search_keywords: Array<any> | null;
};
export type AdImageData = {
id: number;
image_path: string;
};
export type BrandData = {
id: number;
name: string;
};
export type CountryData = {
country: string | null;
dial_code: number | null;
country_code: string | null;
};
export type FileData = {
id: number | null;
fileable_id: number | null;
fileable_type: string | null;
file_location: string | null;
file_url: string | null;
collection: string | null;
file_name: string | null;
created_at: string | null;
updated_at: string | null;
};
export type PermissionData = {
id: number | null;
name: string | null;
guard_name: string | null;
group: string | null;
roles: Array<any> | null;
};
export type RoleData = {
id: number | null;
name: string | null;
description: string | null;
guard_name: string | null;
users_count: number | null;
permissions: Array<any> | null;
};
export type UserData = {
role: string | null;
id: number | null;
tenant_id: number | null;
name: string | null;
phone: string | null;
email: string | null;
email_verified_at: string | null;
remember_token: string | null;
status: string | null;
created_at: string | null;
updated_at: string | null;
avatar: string | null;
roles: Array<any> | null;
permission: Array<any> | null;
files: Array<any> | null;
profile: any | null;
preferences: any | null;
notificationSettings: Array<any> | null;
lastLogin: App.Data.UserLoginLogData | null;
};
export type UserLoginLogData = {
id: number | null;
user_id: number | null;
ip_address: string | null;
device: string | null;
user_agent: string | null;
created_at: string | null;
updated_at: string | null;
user: App.Data.UserData | null;
};
export type UserNotificationSettingData = {
id: number | null;
user_id: number | null;
type: string | null;
methods: Array<any> | null;
timing: string | null;
frequency: string | null;
created_at: string | null;
updated_at: string | null;
};
export type UserPreferencesData = {
id: number | null;
user_id: number | null;
language: string | null;
timezone: string | null;
date_format: string | null;
currency: string | null;
created_at: string | null;
updated_at: string | null;
};
export type UserProfileData = {
id: number | null;
user_id: number | null;
company_name: string | null;
address: string | null;
phone_1: string | null;
phone_2: string | null;
contact_person: string | null;
company_email: string | null;
verified_at: string | null;
verified_by: string | null;
created_at: string | null;
updated_at: string | null;
};
}
