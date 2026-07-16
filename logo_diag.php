<?php
use App\Support\EmailTemplate;

echo "config logo_url = " . config('mail.logo_url') . "\n";
$r = EmailTemplate::render('verify-email', ['NAME' => 'T', 'ACTION_URL' => 'http://x']);
preg_match('/<img[^>]*>/', $r['html'], $m);
echo "rendered img tag: " . ($m[0] ?? 'NO IMG TAG') . "\n";
