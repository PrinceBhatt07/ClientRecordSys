<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use GuzzleHttp\Client as GuzzleClient;
use Brevo\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function store(Request $request)
    {
        $userEmailId = $request->email;
        $user = User::where('email', $userEmailId)->first();
        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Email address does not exist in our records.');
        }

        $verificationToken = Str::random(40);

        $user->update([
            'remember_token' => $verificationToken
        ]);

        $verificationUrl = URL::to('/reset-password/' . $user->id . '/' . urlencode($verificationToken));
        $apiKey = env('API_KEY');

        $config = Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new Client\Api\TransactionalEmailsApi(new GuzzleClient(), $config);
        $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
            'subject' => 'Reset Your Password',
            'sender' => [
                'name' => 'bteams-mvt',
                'email' => 'bteams-mvt@techstaged.co.in'
            ],
            'to' => [
                [
                    'name' => $user->name,
                    'email' => $request->email
                ]
            ],
            'htmlContent' => '
            <html>
    <head>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                background-color: #f9f9f9;
            }
            .email-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .email-content {
                text-align: center;
                margin-bottom: 20px;
            }
            .email-footer {
                text-align: center;
                margin-top: 30px;
                color: #6c757d;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1 class="display-4">Hello! ' . htmlspecialchars($user->name) . '</h1>
                <p>You are receiving this email because we received a password reset request for your account.</p>
            </div>
            <div class="email-content">
            <a href="' . $verificationUrl . '" style="background-color: #2d3748;color: white;text-decoration: none;padding: 7px 7px;border-radius: 7px;">Reset Password</a>
                <p class="lead">If you did not request a password reset, no further action is required.</p>
            </div>
            <div class="email-footer">
                <p>Regards, Laravel</p>
                <p>© 2024 Laravel. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>',
            'params' => [
                'disable_track_clicks' => true
            ]
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            return redirect()->route('password.request')->with('message', 'An Email For Reset Password sent successfully!');
        } catch (\Exception $e) {
            return redirect()->route('password.request')->with('error', 'Exception when sending email: ' . $e->getMessage());
        }
    }

    public function create($id,$token){
        $email = User::find($id)->email;
        return view('auth.custom-reset-password',compact(['id','token','email']));
    }

    public function resetPassword(Request $request)
{
    try {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed'],
            'password_confirmation' => ['required'],
            'token' => ['required'],
            'id' => ['required']
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::where('id', $request->id)
                    ->where('remember_token', $request->token)
                    ->first();

        if (!$user) {
            return redirect()->route('password.reset')->with('error', 'Invalid token or user ID.');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => null
        ]);

        return redirect()->route('login')->with('message', 'Password Reset Successfully');
    } catch (\Exception $e) {
        return redirect()->route('password.reset')->with('error', 'Exception when resetting password: ' . $e->getMessage());
    }
}

}

