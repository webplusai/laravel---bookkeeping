<?php

namespace App\Http\Controllers\Upld;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function upload( Request $request ) {
    	$tempName = $_FILES[ 'file' ][ 'tmp_name' ];
    	$fileName = time() . '_' . $_FILES[ 'file' ][ 'name' ];
    	move_uploaded_file( $tempName, 'uploads/' . $fileName );
    	return $fileName;
    }
}
