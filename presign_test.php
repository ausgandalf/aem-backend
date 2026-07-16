<?php
use App\Models\User;
use App\Models\Application;
use App\Enums\ApplicationStatus;

$admin = User::where('email','damon@wrblo.org')->first();
$app = Application::first() ?? Application::create([
    'applicant_id'=>$admin->id,'organization_id'=>1,'project_title'=>'Key Test',
    'project_location'=>'X','requested_amount'=>1,'currency'=>'GBP','project_details'=>[],
    'current_stage'=>'submit','current_status'=>ApplicationStatus::PENDING,'updated_by'=>$admin->id,
]);

$req = Illuminate\Http\Request::create("/api/applications/{$app->id}/documents/presign",'POST',[],[],[],[
    'CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json',
], json_encode(['filename'=>'budget.xlsx','mime_type'=>'application/vnd.ms-excel']));
$req->setUserResolver(fn()=>$admin);
auth()->setUser($admin);
$res = app(Illuminate\Contracts\Http\Kernel::class)->handle($req);
$b = json_decode($res->getContent(), true);
echo "status=".$res->getStatusCode()."\n";
echo "object_key=".($b['object_key'] ?? 'MISSING')."\n";
echo "prefix ok: ".(str_starts_with($b['object_key'] ?? '', "documents/{$app->id}/") ? 'yes' : 'NO')."\n";
