<?php
namespace PIXELINK\PxSolrstats\Controller;

class SearchController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController  {

	/**
	 * @var \PIXELINK\PxSolrstats\Utility\SolrStatistic
	 * @inject
	 */
	protected $solrStats;

	const PHPEXCEL_PATH = '../../typo3conf/ext/px_solrstats/Lib/phpexcel/PHPExcel.php';
	
	/**
	 * Main Page. Shows only Filter Form
	 */
	public function indexAction() {

		$test = $this->solrStats->getSearchRequests();


        $num_words = array();

		// how many words does an request had
        $num_words = $this->solrStats->getWordCount();
        $this->view->assign('wordcount', $num_words);

		// highest performing search words
		$searchWords = $this->solrStats->getTopSearchWords();
		$this->view->assign('topkeywords', $searchWords);

		// how many search requests has been made
		$requests = $this->solrStats->getSearchRequests();
		$this->view->assign('searchrequests', $requests);

		// how many requests didn't get a result
		$noResults = $this->solrStats->noResults();
		$this->view->assign('noresults', $noResults);

		// average processing time
		$avgTime = $this->solrStats->processingTime('avg');
		$this->view->assign('avgtime', $avgTime);

		// fastest processing time
		$fastestTime = $this->solrStats->processingTime('fastest');
		$this->view->assign('fasttime', $fastestTime);

		// slowest processing time
		$slowestTime = $this->solrStats->processingTime('slowest');
		$this->view->assign('slowesttime', $slowestTime);

	}

	/**
	 * ErrorPage. Shows "No Query" Error Message
	 * 
	 */
	public function errorAction() {
	
	}
	
	/**
	 * Export Action. callback ExportFile with Filter Parameters
	 */
    public function exportAction() {
    	$requestArguments = $this->request->getArguments();
	    $this->exportFile($requestArguments['export']);
	}
	
	/**
	 * Export File Generate. File Types CSV, XLS or XLSX
	 * For File Generate used PhpExcelService Extesion. 
	 * @param Array $exportArguments
	 */
	private function exportFile($exportArguments) {

		$output_file_name = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( 'filename', 'px_solrstats' )."_".date('Y_m_d');
		$lng_time = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( 'time', 'px_solrstats' );
		
		require_once(self::PHPEXCEL_PATH);


		// We'll be outputting an excel file
		$output_Instance = "";
		switch ( $exportArguments['fileType'] ) {
			case 'XLS':
				header('Content-type: application/vnd.ms-excel');
				$output_file_name .= ".xls";
				$output_Instance = "Excel5";
				break;
			case 'XLSX':
				header('Content-type: application/vnd.ms-excel');
				$output_file_name .= ".xlsx";
				$output_Instance = "Excel2007";
				break;
			default: //CSV
				header('Content-Type: application/csv');
				$output_file_name .= ".csv";
				$output_Instance = "CSV";
				echo pack("CCC",0xef,0xbb,0xbf);
				break;
		}

		// It will be called $output_file_name
		header('Content-Disposition: attachment; filename="'.$output_file_name.'"');
		
		
		//Time to timestampt
		$from = strtotime( $exportArguments['from'] );
		//$from = $this->solrStats->stringTimeConvert( $exportArguments['from'] );
		$to = strtotime( $exportArguments['to'] ) + ( 60*60*24 - 1 ); //end of day


		//Set Fields
		$dbSelect = "tstamp";
		$topLine = array($lng_time);
		foreach($exportArguments['fields'] as $key=>$value)
		{
			if(is_null($value) || $value == '') {
				unset($exportArguments['fields'][$key]);
			} else {
				$dbSelect .= ",".$exportArguments['fields'][$key];
				$topLine[] = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $exportArguments['fields'][$key], 'px_solrstats' );
			}
		}
		//Query from DB
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($dbSelect, 'tx_px_solrstats', 'tstamp <= '. intval($to) .' AND tstamp >= '.intval($from) );

		if ( $res->num_rows < 1) {
			$this->redirect("error");
		}
		
		$phpExcel = new \PHPExcel();
		
		
		$rowCount = 1;
		$column = 'A';
		
		//start of printing column names as names of MySQL fields
		for ($i = 0; $i < count($topLine); $i++)  
		{
		    $phpExcel->getActiveSheet()->setCellValue($column.$rowCount, $topLine[$i]);
		    $column++;
		}
		//end of adding column names  
		
		//start while loop to get data  
		$rowCount = 2;  
		foreach ($res as $line) { 
		  
		    $column = 'A';
		    //Date Format
			$line['tstamp'] = date("d.m.Y H:i" , $line['tstamp']);
			
			// decode HTML entities
			$line['keywords'] = html_entity_decode($line['keywords']);
			$line['filters'] = $this->convertSerializedToText($line['filters']);

		    foreach ($line as $value) {
		        $phpExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
		        $column++;
		    }  
		    
		    $rowCount++;
		} 
		
		
		
		// PHPExcel_Writer_{FILETYPE} $excelWriter 
        $excelWriter = \PHPExcel_IOFactory::createWriter($phpExcel, $output_Instance);
		
		//Send the file to browser
		$excelWriter->save('php://output');
		
		
	}
	
	/**
	 * Convert Serialized text to Comma Separated String
	 * @param Serialzed Text $param
	 * @return Comma Separated String
	 */
	private function convertSerializedToText($param) {
		
		// Check isSerialized
		if ($param == serialize(false) || @unserialize($param) !== false) {
			return urldecode (implode(", ", (array)unserialize($param) ) );
		}
	
		return urldecode($param);
	}
}
?>