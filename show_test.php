<?php
use App\Models\User;
use App\Models\Application;
use App\Models\Stage;
use Illuminate\Support\Facades\Cache;

Cache::forget(Stage::CACHE_KEY); // drop the corrupt entry

$app = Application::first();
$owner = User::find($app->applicant_id);

function show($appId, $owner) {
    $req = Illuminate\Http\Request::create("/api/applications/{$appId}", 'GET', [], [], [], ['HTTP_ACCEPT'=>'application/json']);
    $req->setUserResolver(fn()=>$owner);
    auth()->setUser($owner);
    $res = app(Illuminate\Contracts\Http\Kernel::class)->handle($req);
    $body = json_decode($res->getContent(), true);
    return [$res->getStatusCode(), $body];
}

// First call = cache miss, second = cache hit (the one that used to break)
[$s1,$b1] = show($app->id, $owner);
echo "call 1 (miss): status={$s1} keys=".implode(',',array_keys($b1))." org_id=".($b1['application']['organization_id'] ?? 'MISSING')." progress=".count($b1['progress'] ?? [])."\n";
[$s2,$b2] = show($app->id, $owner);
echo "call 2 (hit):  status={$s2} keys=".implode(',',array_keys($b2))." org_id=".($b2['application']['organization_id'] ?? 'MISSING')." progress=".count($b2['progress'] ?? [])."\n";
