<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateMailSettingRequest;
use App\Services\Tenant\MailSettingService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class MailSettingController extends Controller
{
    protected MailSettingService $mailSettingService;

    public function __construct(MailSettingService $mailSettingService)
    {
        $this->mailSettingService = $mailSettingService;
    }

    public function edit(): Response
    {
        $settingsData = $this->mailSettingService->getSettingsForEdit();

        return Inertia::render('Tenant/Settings/Edit', [
            'settings' => $settingsData
        ]);
    }

    public function update(UpdateMailSettingRequest $request): RedirectResponse
    {
        $this->mailSettingService->updateSettings($request->validated());

        return back()->with('success', 'SMTP settings updated successfully.');
    }
}