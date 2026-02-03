<?php

return [
    'login' => [
        'title' => 'Log in to your account',
        'description' => 'Enter your email and password below to log in',
        'head_title' => 'Log in',
        'status_success' => 'You have successfully logged in.',
        'email' => [
            'label' => 'Email Address',
            'placeholder' => 'email@example.com',
        ],
        'password' => [
            'label' => 'Password',
            'placeholder' => 'Password',
            'forgot' => 'Forgot password?',
        ],
        'remember_me' => 'Remember me',
        'button_login' => 'Log in',
        'no_account' => "Don't have an account?",
        'signup_link' => 'Sign up',
    ],

    'forgot_password' => [
        'title' => 'Forgot password',
        'description' => 'Enter your email to receive a password reset link',
        'head_title' => 'Forgot password',
        'alert' => [
            'title' => 'Email Sent',
        ],
        'email' => [
            'label' => 'Email address',
            'placeholder' => 'email@example.com',
        ],
        'button' => 'Email password reset link',
        'return' => [
            'text' => 'Or, return to',
            'login' => 'log in',
        ],
    ],

    'confirm_password' => [
        'title' => 'Confirm your password',
        'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
        'head_title' => 'Confirm password',
        'password' => [
            'label' => 'Password',
            'placeholder' => 'Enter your password',
        ],
        'button' => 'Confirm Password',
    ],

    'register' => [
        'title' => 'Create an account',
        'description' => 'Enter your details below to create your account',
        'head_title' => 'Register',
        'fields' => [
            'name' => [
                'label' => 'Name',
                'placeholder' => 'Full name',
            ],
            'email' => [
                'label' => 'Email address',
                'placeholder' => 'email@example.com',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Password',
            ],
            'password_confirmation' => [
                'label' => 'Confirm password',
                'placeholder' => 'Confirm password',
            ],
        ],
        'button' => 'Create account',
        'already_have_account' => 'Already have an account?',
        'login' => 'Log in',
    ],

    'reset_password' => [
        'title' => 'Reset password',
        'description' => 'Please enter your new password below',
        'head_title' => 'Reset password',
        'fields' => [
            'email' => [
                'label' => 'Email',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Password',
            ],
            'password_confirmation' => [
                'label' => 'Confirm Password',
                'placeholder' => 'Confirm password',
            ],
        ],
        'button' => 'Reset password',
    ],

    'verify_email' => [
        'title' => 'Verify email',
        'description' => 'Please verify your email address by clicking on the link we just emailed to you.',
        'head_title' => 'Email verification',
        'status' => [
            'verification_link_sent' => 'A new verification link has been sent to the email address you provided during registration.',
        ],
        'button' => [
            'resend' => 'Resend verification email',
            'logout' => 'Log out',
        ],
    ],
];
