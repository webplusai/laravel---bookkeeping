<?php

namespace App\Http\Controllers\Auth;

use DB;
use App;
use Artisan;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

use App\Models\UserProfile;

use App\Models\Base\BaseModel;
use App\Helper\RestInputValidators;
use App\Helper\RestResponseMessages;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';
    protected $tableName = 'User';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     * 
     */
    protected function create( Request $request )
    {
        $data = $request->all();

        $validator = RestInputValidators::signupValidator( $data );

        if ( $validator->fails() ) {
            return RestResponseMessages::formValidationErrorMessage( $validator->errors()->all() );
        }

        $data[ 'password' ] = Hash::make( $data[ 'password' ] );
        $user = User::create( $data );

        $user[ 'db_name' ] = 'db_' . strtr( md5( 1 . microtime( true ) * 10000 ), range( 'a', 'z' ) );
        $user->update( $data );

        DB::getSchemaBuilder()->getConnection()->statement( 'CREATE DATABASE ' . $user[ 'db_name' ] );

        configureDBConnectionByName( $user[ 'db_name' ] );
        Artisan::call( 'migrate', array( '--database' => $user[ 'db_name' ], '--path' => '/database/migrations/personals' ) );
        Artisan::call( 'db:seed', array( '--database' => $user[ 'db_name' ] ) );

        UserProfile::create( $data );
        DB::table( 'user_profile' )->where( 'id', 1 )->update( [ 'password'  => $data[ 'password' ] ] );

        return RestResponseMessages::signupSuccessMessage( $this->tableName, $user );
    }
}
