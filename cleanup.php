<?php
use App\Models\User;
use App\Models\UserLog;

$u = User::find(3);
if (!$u || $u->email !== 'charity37@example.org') {
    echo "ABORT: user #3 is not the expected test user, doing nothing.\n";
    return;
}

// Safety: only remove if it is the orphaned test user (no applications)
if (App\Models\Application::where('applicant_id', 3)->exists()) {
    echo "ABORT: user #3 has applications, doing nothing.\n";
    return;
}

UserLog::where('user_id', 3)->orWhere('actioned_by', 3)->delete();
$u->forceDelete();

echo "Removed test user charity37@example.org (#3) and its logs.\n";
echo "Remaining users: " . User::orderBy('id')->pluck('email')->implode(', ') . "\n";
