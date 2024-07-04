<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Scopes;
use App\Contracts\EsiClientContract;
use App\Http\Api\JwtValidator;

class SsoController extends Controller
{
    /** @var JwtValidator $validator */
    private JwtValidator $validator;

    public function __construct(EsiClientContract $esi, JwtValidator $jwtValidator)
    {
        parent::__construct($esi);
        $this->validator = $jwtValidator;
    }

    /**
     * Perform SSO login.
     *
     * @return mixed
     */
    public function login()
    {
        $scopes = Scopes::where('access', 'all')
            ->pluck('name')
            ->toArray();

        $authorizationURL = $this->esi->getAuthorizationServerURL($scopes);

        return redirect($authorizationURL);
    }

    /**
     * Forget SSO character tokens
     */
    public function logout()
    {
        Session::flush();
        return redirect()->route('home');
    }

    /**
     * Receive token from ESI via callback.
     *
     * @param Request $request
     * @return mixed
     * @throws GuzzleException
     */
    public function callback(Request $request)
    {
        try {
            $auth = $this->esi->issueAccessToken($request);

            $expires_on = Carbon::parse(Carbon::now())
                ->addSeconds($auth->expires_in)
                ->toIso8601String();

            $this->validator->validate($auth->access_token);

            Session::put('character.access_token', $auth->access_token);
            Session::put('character.expires_on', $expires_on);
            Session::put('character.refresh_token', $auth->refresh_token);

            return $this->verify();
        } catch (Exception $e) {
            Log::error('SSO error ' . $e->getMessage());

            Session::flush();
            return redirect()->route('home');
        }
    }

    /**
     * Verify login and return character information.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function verify()
    {
        $auth = $this->esi->verifyAuthorization();
        $character = $this->esi->fetchCharacterInformation($auth->CharacterID);
        $corporation = $this->esi->fetchCorporationInformation($character->corporation_id);

        Session::put('character.id', $auth->CharacterID);
        Session::put('character.name', $auth->CharacterName);
        Session::put('character.scopes', explode(" ", $auth->Scopes));
        Session::put('character.portrait', 'https://images.evetech.net/characters/'.
            $auth->CharacterID.'/portrait?tenant=tranquility&size=128');

        Session::put('character.security_status', $character->security_status);
        Session::put('character.corporation', $corporation);

        Session::put('character.corporation_access', false);
        if (intval(config('eve.esi.corporation')) === intval($character->corporation_id)) {
            Session::put('character.corporation_access', true);
        }

        return redirect(route('home'))
            ->with('logged_in', true);
    }
}
