<?php

namespace Mesa\Http\Controllers\Corporation;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mesa\Models\Application;

class ApplicationsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View
     */
    public function index()
    {
        $applications = Application::all();

        return view('management.applications', compact('applications'));
    }

    /**
     * View character application.
     *
     * @param Application $applicant
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View
     */
    public function view(Application $applicant)
    {
        $applicant->character_raw_data = json_decode($applicant->character_raw_data);

        return view('management.application', compact('applicant'));
    }

    /**
     * Decide character application.
     *
     * @param Application $applicant
     * @param Request $request
     * @return RedirectResponse
     */
    public function decideApplication(Application $applicant, Request $request): RedirectResponse
    {
        if (! $request->get('status')) {
            return redirect()->back();
        }

        $applicant->status = $request->get('status');
        $applicant->save();

        return redirect()->route('corporation.applications');
    }
}