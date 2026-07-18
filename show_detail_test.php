<?php
use App\Models\User;
use App\Models\Application;

$app = Application::first();
$owner = User::find($app->applicant_id);
$req = Illuminate\Http\Request::create("/api/applications/{$app->id}", 'GET', [], [], [], ['HTTP_ACCEPT'=>'application/json']);
$req->setUserResolver(fn()=>$owner);
auth()->setUser($owner);
$res = app(Illuminate\Contracts\Http\Kernel::class)->handle($req);
$b = json_decode($res->getContent(), true);
$a = $b['application'] ?? [];
echo "status=".$res->getStatusCode()."\n";
echo "applicant present: ".(isset($a['applicant']) ? 'yes' : 'NO')."\n";
echo "applicant name: ".(($a['applicant']['first_name'] ?? '?').' '.($a['applicant']['last_name'] ?? ''))."\n";
echo "org type key present: ".(array_key_exists('type', $a['organization'] ?? []) ? 'yes' : 'no')."\n";
echo "project_details keys: ".count($a['project_details'] ?? [])."\n";
