<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // use your custom Permission model
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ['name' => 'view_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'create_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'edit_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'delete_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'export_internal_order', 'group' => 'internal_order_management'],
            // ['name' => 'send-shown_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'draft-shown_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'send_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'confirm_item_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'partial_item_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'not_available_item_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'confirm_receive_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'correction_receive_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'close_internal_order', 'group' => 'internal_order_management'],
            ['name' => 'ship_internal_order', 'group' => 'internal_order_management'],

            ['name' => 'order_tracker_view', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_create', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_edit_own', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_edit_all', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_delete_own', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_delete_all', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_admin', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_order', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_ship', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_deliver', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_upload_files', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_send_performa', 'group' => 'order_tracker'],
            ['name' => 'order_tracker_receive_performa', 'group' => 'order_tracker'],

            ['name' => 'receive_order_notification', 'group' => 'notification'],
            ['name' => 'confirm_notification', 'group' => 'notification'],
            ['name' => 'update_notification', 'group' => 'notification'],
            ['name' => 'confirm_receive_notification', 'group' => 'notification'],
            ['name' => 'correction_item_notification', 'group' => 'notification'],
            ['name' => 'close_order_notification', 'group' => 'notification'],
            ['name' => 'ship_order_notification', 'group' => 'notification'],
            ['name' => 'tracking_order_deliver_notification', 'group' => 'notification'],
            ['name' => 'tracking_order_shipped_notification', 'group' => 'notification'],
            ['name' => 'tracking_order_ordered_notification', 'group' => 'notification'],
            ['name' => 'tracking_order_update_notification', 'group' => 'notification'],




        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']], // find by name
                [
                    'group' => $permission['group'],
                    'guard_name' => 'web',
                ]
            );

        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
