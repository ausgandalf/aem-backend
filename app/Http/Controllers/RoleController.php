<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Role::get(['name', 'label'])
            // Role::orderBy('label')->get(['name', 'label'])
        );
    }
}