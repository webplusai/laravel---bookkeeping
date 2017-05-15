<?php

namespace App\Helper;

use Illuminate\Http\Request;

class RestResponseMessages {

    public static function authenticationErrorMessage() {
        return response()->json(
            [
                'status'        =>      'failure',
                'message'       =>      'Authentication Error',
                'content'       =>      [ 'You don\'t have access to this resource' ]
            ], 401
        );
    }

    public static function CRUDSuccessMessage( $crud, $table, $content, $code ) {
        return response()->json(
            [
                'status'        =>      'success',
                'message'       =>      StringConversionFunctions::tableNameToTableTitle( $table ) . ' ' . $crud . ' Success',
                'content'       =>      $content
            ], $code
        );
    }

	public static function formValidationErrorMessage( $content ) {
		return response()->json(
			[
				'status'        =>      'failure',
				'message'       =>      'Form Validation Error',
				'content'       =>      $content
			], 422
		);
	}

    public static function MiscSuccessMessage( $misc, $content, $code ) {
        return response()->json(
            [
                'status'        =>      'success',
                'message'       =>      $misc . ' Success',
                'content'       =>      $content
            ], $code
        );
    }

    public static function reportRetrieveSuccessMessage( $report_name, $content ) {
        return response()->json(
            [
                'status'        =>      'success',
                'message'       =>      $report_name . ' Retrieval Success',
                'content'       =>      $content
            ], 200
        );
    }

    public static function signupSuccessMessage( $content ) {
        return response()->json(
            [
                'status'        =>      'success',
                'message'       =>      'Signup Success',
                'content'       =>      $content
            ], 201
        );
    }

    public static function TRXNSuccessMessage( $trxn, $content, $code ) {
        return response()->json(
            [
                'status'        =>      'success',
                'message'       =>      $trxn . ' Success',
                'content'       =>      $content
            ], $code
        );
    }

}