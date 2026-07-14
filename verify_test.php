<?php
use App\Models\User;

$admin = User::where('email','damon@wrblo.org')->first();

function call($method, $uri, $admin) {
    $req = Illuminate\Http\Request::create($uri, $method, [], [], [], ['HTTP_ACCEPT'=>'application/json']);
    $req->setUserResolver(fn()=>$admin);
    auth()->setUser($admin);
    return app(Illuminate\Contracts\Http\Kernel::class)->handle($req);
}

echo "before: ".($admin->fresh()->email_verified_at ? 'verified' : 'unverified')."\n";
$res = call('PATCH',"/api/admin/users/{$admin->id}/verify-email",$admin);
echo "toggle 1 -> ".json_decode($res->getContent(),true)['message']." | now: ".($admin->fresh()->email_verified_at ? 'verified' : 'unverified')."\n";
$res = call('PATCH',"/api/admin/users/{$admin->id}/verify-email",$admin);
echo "toggle 2 -> ".json_decode($res->getContent(),true)['message']." | now: ".($admin->fresh()->email_verified_at ? 'verified' : 'unverified')."\n";
