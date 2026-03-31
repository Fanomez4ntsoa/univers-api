<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Requests\UpdateCompanySettingRequest;
use App\Modules\CRM\Services\CompanySettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    public function __construct(private CompanySettingService $companySettingService) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->companySettingService->getForUser($user->id));
    }

    public function update(UpdateCompanySettingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->companySettingService->update($user->id, $request->validated())
        );
    }
}
