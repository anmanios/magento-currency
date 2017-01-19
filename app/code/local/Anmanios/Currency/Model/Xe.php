<?php
/**
 * Anmanios
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Anmanios
 * @package     Anmanios_Currency
 * @copyright   Copyright (c) 2017
 * @author		Manios Anastasios <an.manios@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 

class Anmanios_Currency_Model_Xe extends Mage_Directory_Model_Currency_Import_Abstract
{
    
	protected $_url = 'http://www.xe.com/currencyconverter/convert/?Amount=1&From={{CURRENCY_FROM}}&To={{CURRENCY_TO}}';
    protected $_messages = array();
	
	/**
     * HTTP client
     *
     * @var Varien_Http_Client
     */
    protected $_httpClient;

    public function __construct()
    {
        $this->_httpClient = new Varien_Http_Client();
    }
 
    protected function _convert($currencyFrom, $currencyTo, $retry = 0){
		require_once(Mage::getBaseDir('lib') . '/SimpleHtmlDom/simple_html_dom.php');
		
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);
 
        try{
			$response = $this->_httpClient
                ->setUri($url)
                ->setConfig(array('timeout' => Mage::getStoreConfig('currency/webservicex/timeout')))
                ->request('GET')
                ->getBody();
				
			$html = str_get_html($response);
			
			$exchange_rate = $html->find('#ucc-container .uccAmountWrap .uccResultAmount', 0)->plaintext;

            if (!$exchange_rate){
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
                return null;
            }
 
            return number_format((float)$exchange_rate, 4);
        }catch (Exception $e){
            if ($retry == 0){
                $this->_convert($currencyFrom, $currencyTo, 1);
            }else{
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
            }
        }
    }
	
}
