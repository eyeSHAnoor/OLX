<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;    
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\PageContent;
use App\Data\CategoryData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AboutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('about/Index', [
        ]);
    }

    public function show($pageKey)
    {
        $page = PageContent::where('page_key', $pageKey)
        ->where('is_active', true)
        ->firstOrFail();
        
        if($page->page_key == 'about') {
            return Inertia::render('about/Index', [
                'page' => $page
            ]);
        }
         return Inertia::render('about/Contact', [
                'page' => $page
            ]);
    }

    public function nav()
    {
        return Inertia::render('about/Navigation', [
        ]);
    }

    public function send(Request $request)
    {
        // ==========================================
        // 1. HONEYPOT - Catches most basic bots
        // ==========================================
        if ($request->filled('website')) {
            // Log::warning('Contact form honeypot triggered', [
            //     'ip' => $request->ip(),
            //     'user_agent' => $request->userAgent(),
            // ]);
            
            // Return a fake success to confuse the bot
            return back()->with('success', 'Your message has been sent successfully.');
        }

        // ==========================================
        // 2. RATE LIMITING - Prevents flooding
        // ==========================================
        $rateLimitKey = 'contact:'.$request->ip();
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            
            return back()->with('error', "Too many attempts. Please wait {$seconds} seconds before trying again.");
        }

        // ==========================================
        // 3. BLOCK COMMON BOT USER AGENTS
        // ==========================================
        $botUserAgents = [
            'curl', 'python', 'java', 'go-http-client', 'wget', 
            'scrapy', 'http-client', 'requests', 'postman', 'insomnia',
            'bot', 'crawler', 'spider', 'scanner'
        ];
        
        $userAgent = $request->userAgent();
        foreach ($botUserAgents as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                // Log::warning('Bot blocked by user agent', [
                //     'ip' => $request->ip(),
                //     'user_agent' => $userAgent
                // ]);
                return back()->with('success', 'Your message has been sent successfully.');
            }
        }

        // ==========================================
        // 4. STRICT VALIDATION with NAME and EMAIL
        // ==========================================
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'min:5', 'max:255'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        // ==========================================
        // 5. SPAM DETECTION
        // ==========================================
        $message = $validated['message'];
        
        // Check for too many URLs
        preg_match_all('/https?:\/\/[^\s]+/', $message, $urls);
        if (count($urls[0]) > 3) {
            // Log::warning('Contact form blocked - too many URLs', [
            //     'ip' => $request->ip(),
            //     'url_count' => count($urls[0]),
            // ]);
            
            return back()->with('error', 'Your message contains too many links. Please reduce them and try again.');
        }
        
        // Check for common spam keywords
        $spamKeywords = ['casino', 'viagra', 'porn', 'xxx', 'gambling', 'loan', 'bitcoin', 'crypto', 'cialis', 'levitra'];
        $lowerMessage = strtolower($message);
        $lowerSubject = strtolower($validated['subject']);
        
        foreach ($spamKeywords as $keyword) {
            if (strpos($lowerMessage, $keyword) !== false || strpos($lowerSubject, $keyword) !== false) {
                // Log::warning('Contact form blocked - spam keyword detected', [
                //     'ip' => $request->ip(),
                //     'keyword' => $keyword,
                // ]);
                
                return back()->with('error', 'Your message contains prohibited content. Please revise and try again.');
            }
        }

        // ==========================================
        // 6. CHECK FOR REPETITIVE MESSAGES
        // ==========================================
        $messageHash = md5($message);
        $cacheKey = 'contact_message_'.$messageHash;
        
        if (cache()->has($cacheKey)) {
            // Log::warning('Duplicate message blocked', [
            //     'ip' => $request->ip(),
            //     'message_hash' => $messageHash,
            // ]);
            
            return back()->with('error', 'This message appears to be a duplicate. Please wait before sending again.');
        }
        
        cache()->put($cacheKey, true, 3600); // Remember for 1 hour

        // ==========================================
        // 7. SEND EMAIL
        // ==========================================
        try {
            // Log::info('Attempting to send email', [
            //     'to' => 'amomercatus@gmail.com',
            //     'subject' => $validated['subject'],
            //     'name' => $validated['name'],
            //     'email' => $validated['email'],
            // ]);
            $mailer = config('mail.default');
            $host = config('mail.mailers.smtp.host');
            // Log::info('Mail configuration', [
            //     'mailer' => $mailer,
            //     'host' => $host,
            // ]);
            Mail::raw(
                "Name: {$validated['name']}\n"
                . "Email: {$validated['email']}\n"
                . "IP: {$request->ip()}\n"
                . "User Agent: {$request->userAgent()}\n"
                . "Submitted: " . now()->format('Y-m-d H:i:s') . "\n\n"
                . str_repeat('-', 50) . "\n\n"
                . $validated['message'],
                function ($mail) use ($validated) {
                    $mail->to('amomercatus@gmail.com')
                        ->replyTo($validated['email'], $validated['name'])
                        ->subject('[Contact] ' . $validated['subject']);
                }
            );
        } catch (\Exception $e) {
            // Log::error('Failed to send contact email', [
            //     'error' => $e->getMessage(),
            //     'ip' => $request->ip(),
            // ]);
            
            return back()->with('error', 'Something went wrong. Please try again later.');
        }

        // ==========================================
        // 8. RECORD THE ATTEMPT (for rate limiting)
        // ==========================================
        RateLimiter::hit($rateLimitKey, 600); // 10 minute cooldown

        // Log::info('Contact form submitted successfully', [
        //     'ip' => $request->ip(),
        //     'email' => $validated['email'],
        // ]);

        return back()->with('success', 'Your message has been sent successfully!');
    }


}
