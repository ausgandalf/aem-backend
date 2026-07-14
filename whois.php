<?php
use App\Models\User;
use App\Models\UserLog;

$u = User::find(3);
echo "user #3: {$u->email} status={$u->status} role={$u->role}\n";
echo "logs for user #3:\n";
foreach (UserLog::where('user_id',3)->get(['action','details','actioned_by','created_at']) as $l) {
    echo "  {$l->action} | {$l->details} | by={$l->actioned_by} | {$l->created_at}\n";
}
echo "applications for user #3: ".App\Models\Application::where('applicant_id',3)->count()."\n";
