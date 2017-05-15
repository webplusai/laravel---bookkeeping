<?php

namespace App\Helper;

class StringConversionFunctions {

	public static function tableNameToModelName( $tableName ) {
		return 'App\Models\\' . implode( '', array_map( 'ucfirst', explode( '_', $tableName ) ) );
	}

	public static function tableNameToTableTitle( $tableName ) {
		return implode( ' ', array_map( 'ucfirst', explode( '_', $tableName ) ) );
	}

	public static function tableNameToValidatorName( $tableName ) {
		return lcfirst( implode( '', array_map( 'ucfirst', explode( '_', $tableName ) ) ) ) . 'Validator';
	}

	public static function endPointIdToValidatorName( $endPointName ) {
		return lcfirst( implode( '', array_map( 'ucfirst', explode( '_', $endPointName ) ) ) ) . 'EndPointValidator';
	}
}

?>