<h1>Hello {{ $user->name }},</h1>

<p>Your subscription for plan <strong>{{ $plan->name }}</strong> has expired on {{ $ends_at->format('d M Y') }}.</p>

<p>Please renew your subscription to continue enjoying our services.</p>