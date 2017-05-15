<?php

namespace App\Http\Controllers\Auth;

use App;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\Models\AuditLog;
use App\Helper\RestInputValidators;
use App\Helper\RestResponseMessages;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function login( Request $request )
    {
        $data           =       $request->all();
        $validator      =       RestInputValidators::loginValidator( $data );

        if ( $validator->fails() ) {
            return RestResponseMessages::formValidationErrorMessage( $validator->errors()->all() );
        }

        if (Auth::attempt( [ 'email' => $data[ 'email' ], 'password' => $data[ 'password' ] ] ) ) {

            $http = new Client;
            $user = Auth::user();

            $response = $http->post( url( '/oauth/token' ), 
                [
                    'form_params' => 
                        [
                            'grant_type'    =>      'password',
                            'client_id'     =>      '1',
                            'client_secret' =>      'pi583aXU5JqRBL6QJzMn5f3JFOLIABSXChFsMhoa',
                            'username'      =>      $data[ 'email' ],
                            'password'      =>      $data[ 'password' ],
                            'scope'         =>      '',
                        ],
                ]
            );

            configureDBConnectionByName( $user->db_name );
            App::make( 'config' )->set( 'database.default', $user->db_name );

            AuditLog::create( [ 'table_id' => 1, 'record_id' => $user->id, 'date_changed' => date( 'Y-m-d H:i:s' ), 'user_email' => $user->email ] );

            configureDBConnectionByName( 'masterdb' );
            App::make( 'config' )->set( 'database.default', 'masterdb' );

            return response()->json(
                [
                    'status'    =>      'success',
                    'message'   =>      'Login Success',
                    'content'   =>      json_decode( (string) $response->getBody(), true )
                ], 200
            );
        }

        return response()->json(
            [
                'status'    =>      'failure',
                'message'   =>      'Authentication Error',
                'content'   =>      [ 'Invalid username or password' ]
            ],  401
        );
    }
}
