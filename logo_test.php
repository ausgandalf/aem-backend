<?php
use App\Support\EmailTemplate;
use App\Models\User;
use App\Models\Application;
use App\Enums\ApplicationStatus;

// 1. Renderer now points the img at the cid
$r = EmailTemplate::render('verify-email', ['NAME' => 'T', 'ACTION_URL' => 'http://x']);
preg_match('/<img[^>]*src="([^"]*)"/', $r['html'], $m);
echo "img src: " . ($m[1] ?? 'NONE') . " (expect cid:wrblo-logo)\n";

// 2. Real send through the log mailer — the MIME must contain the inline part
config(['mail.default' => 'log']);
$logFile = storage_path('logs/laravel.log');
$before = file_exists($logFile) ? filesize($logFile) : 0;

$user = User::where('email', 'damon@wrblo.org')->first();
$app = Application::first() ?? Application::create([
    'applicant_id' => $user->id, 'organization_id' => 1, 'project_title' => 'Logo Test',
    'project_location' => 'X', 'requested_amount' => 1, 'currency' => 'GBP', 'project_details' => [],
    'current_stage' => 'submit', 'current_status' => ApplicationStatus::PENDING, 'updated_by' => $user->id,
]);
$user->notifyNow(new App\Notifications\ApplicationReceived($app));

$mime = file_get_contents($logFile, false, null, $before);
echo "MIME has Content-ID <wrblo-logo>: " . (str_contains($mime, 'wrblo-logo') ? 'yes' : 'NO') . "\n";
echo "MIME has inline disposition:      " . (str_contains($mime, 'Content-Disposition: inline') ? 'yes' : 'NO') . "\n";
echo "MIME has image/png part:          " . (str_contains($mime, 'image/png') ? 'yes' : 'NO') . "\n";
