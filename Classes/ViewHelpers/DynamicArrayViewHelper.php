<?php
namespace PIXELINK\PxSolrstats\ViewHelpers;
/**
 * Created by PhpStorm.
 * User: briezler
 * Date: 05.09.15
 * Time: 23:19
 */


use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class DynamicArrayViewHelper extends AbstractViewHelper
{

    /**
     * renders an numeric multidimensional array and sorts by key
     *
     * @param array $wordcount numeric multidimensional array
     * @param string $label label of wordcount
     * @return string
     */



    public function render($wordcount, $label) {

        if ( is_array( $wordcount )) {


            ksort($wordcount);

            $do = '';

            foreach ($wordcount as $key => $value) {

                $do .= "['$key $label', " . $value['sum'] . " ],";

            }

            return $do;


        } else {
            return false;
        }

    }

}