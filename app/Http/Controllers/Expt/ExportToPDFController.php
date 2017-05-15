<?php

namespace App\Http\Controllers\Expt;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Dompdf\Dompdf;

class ExportToPDFController extends Controller
{
    public function exportToPDF( Request $request ) {
    	$dompdf = new Dompdf();
		$dompdf->loadHtml( $request->input( 'content' ) );
		$dompdf->setPaper( 'A4', 'landscape' );
		$dompdf->render();
		$dompdf->stream( "report.pdf" );
    }
}
