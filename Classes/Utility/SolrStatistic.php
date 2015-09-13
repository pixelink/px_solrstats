<?php
namespace PIXELINK\PxSolrstats\Utility;

/**
 * Created by PhpStorm.
 * User: briezler
 * Date: 03.09.15
 * Time: 23:32
 */

class SolrStatistic {

    /**
     * @var string
     */
   public $solr_table = 'tx_solr_statistics';


    /**
     * Count appearance of search words
     * @return array
     */
    public function getWordCount(){

        $select = array(
            'fields' => 'keywords',
            'table' => 'tx_solr_statistics',
            'where' => '',
            'group' => 'keywords',
            'order' => '',
            'limit' => '',
        );

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select['fields'], $select['table'], $select['where'], $select['group'], $select['order'], $select['limit'] );
        if ( $res->num_rows >= 1) {

            $num_words = array();

            foreach ($res as $result) {

            $words = count(explode(' ',trim($result['keywords'])));

                //$words = str_word_count($result['keywords']);
                $num_words[$words]['sum'] += 1;

            }

            return $num_words;

        } else {
            return false;
        }


    }

    /**
     * get top searched words of solr search statistic
     * @param string $startTime define start date for sql range
     * @param string $endTime define end date for sql range
     * @return array
     *
     */

    public function getTopSearchWords($startTime = '', $endTime = ''){

        $select = array(
            'fields' => 'keywords, count(*) as cnt ',
            'table' => 'tx_solr_statistics',
            'where' => $whereClause,
            'group' => 'keywords',
            'order' => 'cnt DESC',
            'limit' => '0, 20',
        );

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select['fields'], $select['table'], $select['where'], $select['group'], $select['order'], $select['limit'] );

        if ( $res->num_rows >= 1) {

            return $res;

        } else {
            return false;
        }

    }
    /**
     * get keywords wich returned no results
     * @param string $startTime define start date for sql range
     * @param string $endTime define end date for sql range
     * @return array
     *
     */

    public function noResults($startTime = '', $endTime = ''){

        $select = array(
            'fields' => 'keywords, count(*) as cnt ',
            'table' => 'tx_solr_statistics',
            'where' => 'num_found = 0 ' . $whereClause,
            'group' => 'keywords',
            'order' => 'cnt DESC',
            'limit' => '0, 20',
        );

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select['fields'], $select['table'], $select['where'], $select['group'], $select['order'], $select['limit'] );

        if ( $res->num_rows >= 1) {

            return $res;

        } else {
            return false;
        }

    }

     /**
     * get how many search requests were done by day
     * @param string $startTime define start date for sql range
     * @param string $endTime define end date for sql range
     * @return array
     *
     */

    public function getSearchRequests($startTime = '', $endTime = ''){

        $select = array(
            'fields' => 'tstamp, COUNT(*) as cnt,
                        DAY(FROM_UNIXTIME(tstamp)) as search_day',
            'table' => 'tx_solr_statistics',
            'where' => $whereClause,
            'group' => 'search_day',
            'order' => 'tstamp ASC',
            'limit' => '',
        );

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select['fields'], $select['table'], $select['where'], $select['group'], $select['order'], $select['limit'] );

        if ( $res->num_rows >= 1) {

            foreach ($res as $key => $value) {

                $do[] = '[new Date('. $this->jsDate($value['tstamp']) .'), ' . $value['cnt'] . '],'; //date('d.m.Y', $value['tstamp'])  . ' - ' . $value['cnt']  . ' - ' . $value['search_day'] .'<br/>';

            }

            return $do;

        } else {
            return false;
        }

    }

    /**
     * Top sorting action
     *
     */

    public function topSorting(){
        return true;
    }

    /**
     * Top used filters
     *
     */

    public function topFilter(){
        return true;
    }

    /**
     * processing time
     *
     * @param string time
     *
     *
     */

    public function processingTime($time = 'avg'){

        switch($time){
            case 'avg':
                    $fields = 'time_total, AVG(time_total) AS avg_time';
                    $returnField = 'avg_time';
                    $order = '';

                break;
            case 'fastest':
                $fields = 'time_total';
                $returnField = 'time_total';
                $order = 'time_total ASC';

                break;

            case 'slowest':
                $fields = 'time_total';
                $returnField = 'time_total';
                $order = 'time_total DESC';

                break;
            default:

                break;

        }

        $select = array(
            'fields' => $fields,
            'table' => 'tx_solr_statistics',
            'where' => $whereClause,
            'group' => '',
            'order' => $order,
            'limit' => '0,1',
        );

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select['fields'], $select['table'], $select['where'], $select['group'], $select['order'], $select['limit'] );

        if ( $res->num_rows >= 1) {

            foreach ($res as $key => $value) {

                $time = $value[$returnField];

            }

            return number_format($time,0,'.','.');

        } else {
            return false;
        }

    }

    /**
     * defines the timerange within the sql search should bed processed
     * @param string $startTime define start date of results
     * @param string $endTime defines end date of results
     *
     * @ return string - additional Where clause string for SQL Query
    */

    private function defineTimeRange( $startTime = '', $endTime = '') {

        if ($startTime != '' AND $endTime != '') {

            $whereClause = 'tstamp >= ' . $startTime . ' AND tstamp <= ' . $endTime;

        } elseif ($startTime != '' AND $endTime == '') {

            $whereClause = 'tstamp >= ' . $startTime;

        } else {

            $whereClause = 'tstamp = ' . time() - (86400*7);

        }

        return $whereClause;

    }

    /**
     * converts the given datpicker date in unix timestamp
     * uses just strtotime, but is set up as a function if change is needed
     *
     * @todo check if latifs integration is proper
     * @param string $dateString
     *
     * @return int  unix timestamp
     */
    public function stringTimeConvert($dateString) {

        $timeStamp = strtotime( $dateString );

        if ( is_numeric($timeStamp) && (int)$timeStamp == $timeStamp ) {

            return $timeStamp;

        } else {

            return false;

        }

    }

    /**
     * js Date
     * for javascript purposes the month needs to be -1 one month
     * php starts january as 1, javascript as 0
     *
     * @param int $timestamp
     * @return string
     */

    public function jsDate($timestamp){

            $jsDate = new \DateTime();
            $jsDate->setTimestamp($timestamp);
            $jsDate->modify('-1 month');
            $chartDate = $jsDate->format('Y,m,d');

            return $chartDate;

    }
}