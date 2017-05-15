<?php

namespace App\Http\Controllers\Rprt\Chart;

use Illuminate\Http\Request;
use App\Http\Controllers\Rprt\Base\BaseReportController;
use App\Models\Sales;

use App\Helper\DateFromToCalculator;
use App\Helper\RestResponseMessages;

class SalesController extends BaseReportController
{
    public function index( $period ) {
    	
        $result             =   [];
    	$date_segments      =   DateFromToCalculator::calculateFromToSegments( $period );

    	foreach ( $date_segments as $date_range ) {

            $segment[ 'sales' ] = $this->getIncome( $date_range[ 'from' ], $date_range[ 'to' ] );
            $segment[ 'display' ] = $date_range[ 'display' ];
            $result[] = $segment;
            
    	}

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Sales Report', $result );
    }
}
