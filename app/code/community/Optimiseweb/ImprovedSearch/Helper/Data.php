<?php

/**
 * Optimiseweb ImprovedSearch Helper Data
 *
 * @package     Optimiseweb_ImprovedSearch
 * @author      Sid Vel (sid@optimiseweb.co.uk)
 * @copyright   Copyright (c) 2014 Optimise Web
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Optimiseweb_ImprovedSearch_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Check if enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('optimisewebimprovedsearch/general/enabled');
    }

}
