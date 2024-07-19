<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use GuzzleHttp\Client as GuzzleClient;
use Brevo\Client;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $userId = $user->id;
        $verificationToken = Str::random(40);
        $user->remember_token = $verificationToken;
        $user->save();

        $verificationUrl = URL::to('/verify-email/' . $userId . '/' . urlencode($verificationToken));

        $apiKey = env('API_KEY');
        
        $config = Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new Client\Api\TransactionalEmailsApi(new GuzzleClient(), $config);
        $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
            'subject' => 'Verify Your Email',
            'sender' => [
                'name' => 'bteams-mvt',
                'email' => 'bteams-mvt@techstaged.co.in'
            ],
            'to' => [
                [
                    'name' => $validated['name'],
                    'email' => $validated['email']
                ]
            ],
            'htmlContent' => '
                <html>
                    <body>
                        <h1>Hello ' . htmlspecialchars($validated['name']) . '</h1>
                        <p>Please click the button below to verify your email address.</p>
                        <a class="btn btn-primary" href="' . $verificationUrl . '">Verify Email</a>
                        <p>If you did not create an account, no further action is required.</p>
                        <p>Regards, Laravel</p>
                        <p>© 2024 Laravel. All rights reserved.</p>
                    </body>
                </html>',
            'params' => [
                'disable_track_clicks' => true
            ]
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            return view('auth.verify-email');
        } catch (\Exception $e) {
            return view('auth.verify-email', ['message' => 'Exception when sending email: ' . $e->getMessage()]);
        }

        // Uncomment the following lines if you want to login the user after registration
        // event(new Registered($user));
        // Auth::login($user);

        // return redirect(route('dashboard'));
    }
}
