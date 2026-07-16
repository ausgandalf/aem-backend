<?php
use App\Models\User;
use App\Models\Application;
use App\Models\Document;
use App\Models\ApplicationLog;
use App\Models\File;

$admin = User::where('email','damon@wrblo.org')->first();
$app = Application::first();
$file = File::first();

$doc = Document::create([
    'file_id'=>$file->id,'application_id'=>$app->id,
    'stage_key'=>$app->current_stage,'flag'=>'ok','updated_by'=>$admin->id,
]);

$req = Illuminate\Http\Request::create("/api/applications/{$app->id}/documents/{$doc->id}",'PATCH',[],[],[],[
    'CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json',
], json_encode(['description'=>'Because it proves budget','flag'=>'warning','flag_note'=>'Numbers need checking']));
$req->setUserResolver(fn()=>$admin);
auth()->setUser($admin);
$res = app(Illuminate\Contracts\Http\Kernel::class)->handle($req);
echo "status=".$res->getStatusCode()."\n";
$doc->refresh();
echo "desc={$doc->description} | flag={$doc->flag} | note={$doc->flag_note} | updated_by={$doc->updated_by}\n";
echo "log written: ".(ApplicationLog::where('document_id',$doc->id)->where('description','like','%updated document%')->exists()?'yes':'NO')."\n";

// bad flag value rejected?
$req2 = Illuminate\Http\Request::create("/api/applications/{$app->id}/documents/{$doc->id}",'PATCH',[],[],[],[
    'CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json',
], json_encode(['flag'=>'bogus']));
$req2->setUserResolver(fn()=>$admin);
$res2 = app(Illuminate\Contracts\Http\Kernel::class)->handle($req2);
echo "bad flag status=".$res2->getStatusCode()." (expect 422)\n";

ApplicationLog::where('document_id',$doc->id)->delete();
$doc->delete();
echo "cleaned up\n";
