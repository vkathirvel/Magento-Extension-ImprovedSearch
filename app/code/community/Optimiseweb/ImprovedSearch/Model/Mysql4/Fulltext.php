<?php

/**
 * Optimiseweb ImprovedSearch Model Mysql4 Fulltext
 *
 * @package     Optimiseweb_ImprovedSearch
 * @author      Kathir Vel (sid@optimiseweb.co.uk)
 * @copyright   Copyright (c) 2014 Optimise Web
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Optimiseweb_ImprovedSearch_Model_Mysql4_Fulltext extends Mage_CatalogSearch_Model_Mysql4_Fulltext
{

    /**
     * Prepare results for query
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string $queryText
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Mage_CatalogSearch_Model_Mysql4_Fulltext
     */
    public function prepareResult($object, $queryText, $query)
    {
        if (!$query->getIsProcessed()) {
            $searchType = $object->getSearchType($query->getStoreId());

            $stringHelper = Mage::helper('core/string');
            /* @var $stringHelper Mage_Core_Helper_String */

            $bind = array(
                    ':query' => $queryText
            );
            $like = array();

            $fulltextCond = '';
            $likeCond = '';
            $separateCond = '';

            if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE || $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE) {
                $words = $stringHelper->splitWords($queryText, true, $query->getMaxQueryWords());
                $likeI = 0;
                foreach ($words as $word) {
                    $like[] = '`s`.`data_index` LIKE :likew' . $likeI;
                    $bind[':likew' . $likeI] = '%' . $word . '%';
                    $likeI ++;
                }
                if ($like) {
                    // OW Improved Search Modification
                    if (Mage::helper('ow_improvedsearch')->isEnabled()) {
                        $likeCond = '(' . join(' AND ', $like) . ')';
                    } else {
                        $likeCond = '(' . join(' OR ', $like) . ')';
                    }
                }
            }
            if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_FULLTEXT || $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE) {
                $fulltextCond = 'MATCH (`s`.`data_index`) AGAINST (:query IN BOOLEAN MODE)';
            }
            if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE && $likeCond) {
                $separateCond = ' OR ';
            }

            $sql = sprintf("INSERT INTO `{$this->getTable('catalogsearch/result')}` "
                . "(SELECT STRAIGHT_JOIN '%d', `s`.`product_id`, MATCH (`s`.`data_index`) "
                . "AGAINST (:query IN BOOLEAN MODE) FROM `{$this->getMainTable()}` AS `s` "
                . "INNER JOIN `{$this->getTable('catalog/product')}` AS `e` "
                . "ON `e`.`entity_id`=`s`.`product_id` WHERE (%s%s%s) AND `s`.`store_id`='%d')"
                . " ON DUPLICATE KEY UPDATE `relevance`=VALUES(`relevance`)", $query->getId(), $fulltextCond, $separateCond, $likeCond, $query->getStoreId()
            );

            $this->_getWriteAdapter()->query($sql, $bind);

            $query->setIsProcessed(1);
        }

        return $this;
    }

}
