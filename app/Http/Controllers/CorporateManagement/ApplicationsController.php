<?php

namespace Mesa\Http\Controllers\CorporateManagement;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Mesa\Application;
use Mesa\Http\Api\EsiCharacter;

/**
 * Application Constructor.
 */
class ApplicationsController extends EsiController
{
    /**
     * Render application page.
     *
     * @return mixed
     */
    public function index()
    {
        return view('apply.form');
    }

    /**
     * Submit application.
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function submit(Request $request)
    {
        try {
            $information = $this->character->getInfoRequiredForApplication();
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());

            return redirect()->back();
        }

        if(Application::where('character_name', $information['name'])->first()) {
            return view('apply.confirmation', [
                'character' => $information['name'],
                'message' => 'We have already received an application for ' . $information['name'] . ', please contact Solomon Kaldari in-game for assistance.'
            ]);
        }

        $application = new Application();

        $application->character_name = $information['name'];
        $application->character_corporation = $information['current_corporation'];

        $application->length_playing = $request->get('length_playing');
        $application->favourite_activities = $request->get('favourite_activities');
        $application->reason_joining = $request->get('reason_joining');
        $application->real_life = $request->get('real_life');
        $application->haiku = $request->get('haiku');

        if (!$application->save()) {
            return response()->json(['error' => 'There was an error with your application, please try again.']);
        }

        return view('apply.confirmation', [
            'character' => $information['name'],
            'message' => 'Success! your application has been received. Please keep an eye on your EVEmail, we\'ll send you an invite or an update shortly.'
        ]);
    }
}