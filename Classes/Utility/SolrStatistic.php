<?php
namespace PIXELINK\PxSolrstats\Utility;

/**
 * Created by PhpStorm.
 * User: briezler
 * Date: 03.09.15
 * Time: 23:32
 */

class SolrStatistic {

    /*
     * @var string
     */
   public $solr_table = 'tx_solr_statistics';


    /*
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

    /*
     * get top words of solr search statistic
     * @param
     * @return array
     *
     */

    public function getTopSearchWords(){

        $select = array(
            'fields' => 'keywords, count(*) as cnt ',
            'table' => 'tx_solr_statistics',
            'where' => 'tstamp = ' . time() - (86400*7),
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
}