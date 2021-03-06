<?php
/**
 *
 * @package Lillik\PriceDecimal\Model
 *
 * @author  Lilian Codreanu <lilian.codreanu@gmail.com>
 */

namespace Lillik\PriceDecimal\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class Config implements ConfigInterface
{

    const XML_PATH_PRICE_PRECISION
        = 'catalog_price_decimal/general/price_precision';

    const XML_PATH_CAN_SHOW_PRICE_DECIMAL
        = 'catalog_price_decimal/general/can_show_decimal';

    const XML_PATH_GENERAL_ENABLE
        = 'catalog_price_decimal/general/enable';

    const XML_PATH_DISABLE_FOR_ACTIONS
        = 'catalog_price_decimal/general/disable_for_actions';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * Return Config Value by XML Config Path
     * @param $path
     * @param $scopeType
     *
     * @return mixed
     */
    public function getValueByPath($path, $scopeType = 'website')
    {
        return $this->getScopeConfig()->getValue($path, $scopeType);
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getValueByPath(self::XML_PATH_GENERAL_ENABLE, 'website') && !$this->isDisabledForAction();        
    }

    /**
     * @return mixed
     */
    public function canShowPriceDecimal()
    {
        return $this->getValueByPath(self::XML_PATH_CAN_SHOW_PRICE_DECIMAL, 'website');
    }

    /**
     * Return Price precision from store config
     *
     * @return mixed
     */
    public function getPricePrecision()
    {
        return $this->getValueByPath(self::XML_PATH_PRICE_PRECISION, 'website');
    }

    private function isDisabledForAction()
    {        
        $currentAction = [
            $this->request->getModuleName(), 
            $this->request->getControllerName(), 
            $this->request->getActionName()
        ];        

        foreach (explode(',', $this->getValueByPath(self::XML_PATH_DISABLE_FOR_ACTIONS, 'website')) as $action) {
            $action = trim($action);
            $action = explode('_', $action);
            
            // this action was entered in admin in the wrong format
            if (count($action) != 3) {
                continue;
            }
            
            $match = true;
            for ($i=0;$i<3;$i++) {
                // if action step does NOT matches current action and is NOT a wildcard
                // then we know that this action does not match current and can continue with next action
                if ($action[$i] != $currentAction[$i] && $action[$i] != '*') {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                return true;
            }
        }
        return false;
    }
}
