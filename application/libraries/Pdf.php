<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');  
 
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf extends Dompdf
{
	public function __construct()
	{
		 parent::__construct();
	} 

	public function create()
	{
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('enable_svg', false);

		$dompdf = new Dompdf($options);
		return $dompdf;
	}
}

?>