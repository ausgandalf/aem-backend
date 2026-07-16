<?php

use App\Mail\TemplatedMail;
use App\Support\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/__test/email', function () {
    abort_unless(app()->environment('local'), 404);

    $recipients = [
        [
            'name' => 'Damon Simpson',
            'to' => 'damon@wrblo.org',
        ],
        [
            'name' => 'Damon Simpson',
            'to' => 'jdkls7725@gmail.com',
        ],
    ];

    foreach ($recipients as $recipient) {
        $rendered = EmailTemplate::render('email-test', [
            'NAME' => $recipient['name'],
            'TO' => $recipient['to'],
            'LOGIN_URL' => rtrim(
                (string) config('app.frontend_url'),
                '/'
            ) . '/login',
        ]);

        Mail::to($recipient['to'])->send(
            new TemplatedMail(
                'Eamil Test',
                $rendered['html'],
                $rendered['text']
            )
        );
    }

    return 'Emails sent successfully.';
});

Route::get('/__test/s3', function () {
    abort_unless(app()->isLocal(), 404);

    $s3 = Storage::disk('s3');
    $s3public = Storage::disk('s3_public');

    $path = 'test/hello.txt';

    $uploaded = $s3->put(
        $path,
        'Hello from Laravel!'
    );

    $uploaded2public = $s3public->put(
        $path,
        'Hello from Laravel!'
    );

    return response()->json([
        [
            'uploaded' => $uploaded,
            'exists' => $s3->exists($path),
            'path' => $path,
            'url' => $s3->url($path),
        ],
        [
            'uploaded' => $uploaded2public,
            'exists' => $s3public->exists($path),
            'path' => $path,
            'url' => $s3public->url($path),
        ]
    ]);
});