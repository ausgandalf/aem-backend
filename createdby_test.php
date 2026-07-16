<?php
use App\Models\User;
use App\Models\Application;
use App\Models\Document;
use App\Models\ApplicationLog;
use App\Models\File;

$admin = User::find(1);
$applicant = User::find(2); // owns application 1
$app = Application::first();
$file = File::first();

function callApi($method, $uri, $body, $user) {
    $req = Illuminate\Http\Request::create($uri, $method, [], [], [], [
        'CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json',
    ], $body ? json_encode($body) : null);
    $req->setUserResolver(fn()=>$user);
    auth()->setUser($user);
    return app(Illuminate\Contracts\Http\Kernel::class)->handle($req);
}

// Create a doc row directly as admin (bypassing S3 exists-check) to test the audit pair
$doc = Document::create([
    'file_id'=>$file->id,'application_id'=>$app->id,'stage_key'=>$app->current_stage,
    'flag'=>'ok','created_by'=>$admin->id,'updated_by'=>$admin->id,
]);
echo "after create: created_by={$doc->created_by} updated_by={$doc->updated_by}\n";

// Update as the APPLICANT (different user)
$res = callApi('PATCH', "/api/applications/{$app->id}/documents/{$doc->id}",
    ['description'=>'checked','flag'=>'warning','flag_note'=>'n'], $applicant);
echo "update status=".$res->getStatusCode()."\n";
$doc->refresh();
echo "after update: created_by={$doc->created_by} (expect still 1) updated_by={$doc->updated_by} (expect 2)\n";

ApplicationLog::where('document_id',$doc->id)->delete();
$doc->delete();
echo "cleaned up\n";
