<?php

namespace App\Http\Controllers\Front;

use App\Company;
use App\Currency;
use App\Helper\Reply;
use App\Http\Requests\Front\Register\StoreRequest;
use App\Notifications\EmailVerification;
use App\Notifications\EmailVerificationSuccess;
use App\Notifications\NewCompanyRegister;
use App\Role;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\GlobalSetting;

class RegisterController extends FrontBaseController
{
    public function index() {
        $this->pageTitle = 'Sign Up';
        return view('front.register', $this->data);
    }

    public function store(StoreRequest $request) {

        if(!is_null($this->global->google_recaptcha_key))
        {
            $gRecaptchaResponseInput = 'g-recaptcha-response';
            $gRecaptchaResponse = $request->{$gRecaptchaResponseInput};
            $validateRecaptcha = $this->validateGoogleRecaptcha($gRecaptchaResponse);
            if(!$validateRecaptcha)
            {
                return Reply::error('Recaptcha not validated.');
            }
        }

        DB::beginTransaction();
        // Save company name
        $globalSetting = GlobalSetting::first();

        $company = new Company();
        $company->company_name = $request->company_name;
        $company->company_email = $request->email;
        $company->timezone = $globalSetting->timezone;
        $company->save();

        $currency = Currency::where('company_id', $company->id)->first();
        $company->currency_id = $currency->id;
        $company->save();

        // Save Admin
        $user = new User();
        $user->company_id = $company->id;
        $user->name       = 'admin';
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status = 'active';
        $user->email_verification_code = str_random(40);
        $user->save();

        if($globalSetting->email_verification == 1) {
            $user->notify(new EmailVerification($user));
            $user->status = 'deactive';
            $user->save();
        } else {
            $adminRole = Role::where('name', 'admin')->where('company_id', $user->company_id)->first();
            $user->roles()->attach($adminRole->id);

            $employeeRole = Role::where('name', 'employee')->where('company_id', $user->company_id)->first();
            $user->roles()->attach($employeeRole->id);
        }

        $superAdmin = User::whereNull('company_id')->get();

        Notification::send($superAdmin, new NewCompanyRegister($company));

        DB::commit();

        if($globalSetting->email_verification == 1) {
            $message = 'Thank you for signing up. Please verify your email to get started.';
        } else {
            $message = 'Thank you for signing up. click <a href="'.route('login').'">here</a> for login.';
        }

        return Reply::success($message);
    }

    public function validateGoogleRecaptcha($googleRecaptchaResponse)
    {
        $client = new Client();
        $response = $client->post(
            'https://www.google.com/recaptcha/api/siteverify',
            ['form_params'=>
                [
                    'secret'=> $this->global->google_recaptcha_secret,
                    'response'=> $googleRecaptchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ]
            ]
        );

        $body = json_decode((string)$response->getBody());

        return $body->success;
    }

    public function getEmailVerification($code)
    {
        $this->pageTitle = 'Email Verification';

        $user = User::where('email_verification_code', $code)->whereNotNull('email_verification_code')->withoutGlobalScope('active')->first();

        if($user) {
            $user->status = 'active';
            $user->email_verification_code = '';
            $user->save();

            $user->notify(new EmailVerificationSuccess($user));

            $adminRole = Role::where('name', 'admin')->where('company_id', $user->company_id)->first();
            $user->roles()->attach($adminRole->id);

            $employeeRole = Role::where('name', 'employee')->where('company_id', $user->company_id)->first();
            $user->roles()->attach($employeeRole->id);

            $this->messsage = 'Your have successfully verified your email address. You must click  <a href="'.route('login').'">Here</a> to login.';
            $this->class = 'success';
            return view('front.email-verification', $this->data);


        } else {

            $this->messsage = 'Verification url doesn\'t exist. Click <a href="'.route('login').'">Here</a> to login.';
            $this->class = 'success';
            return view('front.email-verification', $this->data);
        }

    }
}
