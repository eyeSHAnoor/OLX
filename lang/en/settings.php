<?php

return [
    'acc_setting' => [
        'heading' => 'Account Settings',
        'title' => 'Account information',
        'desc' => 'Update your name and email address',
        'labels' => [
            'name' => 'Name',
            'email' => 'Email address',
            'save' => 'Save',
        ],
        'messages' => [
            'err' => 'Your email address is unverified.',
            'sol' => 'Click here to resend the verification email.',
            'new' => 'A new verification link has been sent to your email address.',
        ],
    ],

    'app_setting' => [
        'title' => 'Appearance settings',
        'description' => "Update your account's appearance settings",
    ],

    'noti_setting' => [
        'title' => 'Notification Setting',
        'description' => 'Update your notification methods and frequency',
        'table' => [
            'type' => 'Notification Type',
            'methods' => 'Methods',
            'timing' => 'Timing',
            'frequency' => 'Frequency',
        ],
        'actions' => [
            'save' => 'Save',
            'saved' => 'Saved.',
        ],
    ],

    'password_setting' => [
        'title' => 'Password settings',
        'heading' => 'Update password',
        'description' => 'Ensure your account is using a long, random password to stay secure',
        'fields' => [
            'current_password' => 'Current password',
            'new_password' => 'New password',
            'confirm_password' => 'Confirm password',
        ],
        'actions' => [
            'save' => 'Save password',
            'saved' => 'Saved.',
        ],
    ],

    'preferences' => [
        'title' => 'Preference Settings',
        'description' => 'Update your language, timezone and other preferences',
        'language' => [
            'label' => 'System Language',
            'placeholder' => 'Select language',
            'en' => 'English',
            'zh_CN' => 'Simplified Chinese',
            'zh_TW' => 'Traditional Chinese',
            'ja' => 'Japanese',
        ],
        'timezone' => 'Timezone',
        'date_format' => 'Date Format',
        'currency' => 'Currency',
        'actions' => [
            'save' => 'Save',
            'saved' => 'Saved.',
        ],
    ],

    'profile' => [
        'title' => 'Profile Settings',
        'description' => 'Update your name and email address',
        'form' => [
            'company_name' => 'Company Name',
            'address' => 'Address',
            'phone_1' => 'Primary Phone',
            'phone_2' => 'Backup Phone',
            'contact_person' => 'Contact Person',
            'company_email' => 'Company Email Address',
            'business_license' => [
                'label' => 'Business License',
                'upload_new' => 'Upload New Business License',
                'uploaded' => 'Uploaded Business License',
            ],
        ],
        'actions' => [
            'save' => 'Save',
            'saved' => 'Saved.',
        ],
    ],

    'app_settings' => [
        'title' => 'App Settings',
        'heading' => 'Application General Settings and Values',
        'tax' => [
            'card_title' => 'General Tax Rate',
            'label' => 'Tax Rate',
            'placeholder' => 'Enter tax rate %',
            'append_text' => '%',
        ],
        'extra_fee_labels' => [
            'card_title' => 'Extra Fee Labels',
            'label' => 'Extra Fee Labels',
            'placeholder' => 'Type label and hit Enter',
        ],
    ],

    'inactive_session' => [
        'title' => 'Inactive Session',
        'enabled_label' => 'Enable inactive session logout',
        'time_label' => 'Inactive Session Time (minutes)',
        'time_placeholder' => 'Enter inactive session time (minutes)',
        'save' => 'Save Settings',
    ],

    'delete_account' => [
        'heading' => 'Delete account',
        'description' => 'Delete your account and all of its resources',
        'warning_title' => 'Warning',
        'warning_text' => 'Please proceed with caution, this cannot be undone.',
        'button_delete' => 'Delete account',
        'modal' => [
            'title' => 'Are you sure you want to delete your account?',
            'description' => 'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
            'password_placeholder' => 'Password',
            'cancel' => 'Cancel',
            'confirm_delete' => 'Delete account',
        ],
    ],
    "role_permissions" => [
        "title" => "Role & Permissions",
        "new" => "New Role",
        "role" => "Role",
        "permissions" => "Permissions"
    ],
];
