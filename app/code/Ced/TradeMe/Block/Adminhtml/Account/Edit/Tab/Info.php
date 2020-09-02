<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Block\Adminhtml\Account\Edit\Tab;

/**
 * Class Info
 * @package Ced\TradeMe\Block\Adminhtml\Account\Edit\Tab
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Info constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $account = $this->_objectManager->get('Ced\TradeMe\Model\Accounts')->load($id);
        } else {
            $account = $this->_objectManager->get('Ced\TradeMe\Model\Accounts');
        }

        $fieldset = $form->addFieldset('Account_info', ['legend' => __('Account Information')]);

        $fieldset->addField('account_code', 'text',
            [
                'name' => "account_code",
                'label' => __('Account Code'),
                'note' => __('To Identify the Account'),
                'required' => true,
                'note' => __('For internal use. Must be unique with no spaces'),
                'class' => 'validate-code',
                'value' => $account->getData('account_code'),
            ]
        );

        $fieldset->addField('account_env', 'select',
            array(
                'name' => "account_env",
                'label' => __('Account Environment'),
                'required' => true,
                'value' => $account->getData('account_env'),
                'values' => $this->_objectManager->get('Ced\TradeMe\Model\Config\Environment')->getOptionArray(),
            )
        );

        $fieldset->addField('account_status', 'select',
            array(
                'name' => "account_status",
                'label' => __('Account Status'),
                'required' => true,
                'value' => $account->getData('account_status'),
                'values' => $this->_objectManager->get('Ced\TradeMe\Model\Source\Account\Status')->getOptionArray(),
            )
        );

        $fieldset->addField('account_store', 'select',
            array(
                'name' => "account_store",
                'label' => __('Account Store'),
                'required' => true,
                'value' => $account->getData('account_store'),
                'values' => $this->_objectManager->get('Magento\Config\Model\Config\Source\Store')->toOptionArray(),
            )
        );
        $fieldset->addField('outh_verifier', 'text',
            [
                'name' => "outh_token_secret",
                'label' => __('OAuth Verifier'),
                'value' => $account->getData('outh_verifier'),
            ]
        );
        $fieldset->addField('outh_consumer_key', 'text',
            [
                'name' => "outh_consumer_key",
                'label' => __('OAuth Consumer Key'),
                'note' => __('Credentials for Fetching Token'),
                'required' => true,
                'value' => $account->getData('outh_consumer_key'),
            ]
        );
        $fieldset->addField('outh_consumer_secret', 'text',
            [
                'name' => "outh_consumer_secret",
                'label' => __('OAuth Consumer Secret'),
                'note' => __('Credentials for Fetching Token'),
                'required' => true,
                'value' => $account->getData('outh_consumer_secret'),
            ]
        );
        $fieldset->addField('outh_token_secret', 'text',
            [
                'name' => "outh_token_secret",
                'label' => __('OAuth Token Secret'),
                'value' => $account->getData('outh_token_secret'),
            ]
        );
        $fieldset->addField('outh_access_token', 'text',
            [
                'name' => "outh_access_token",
                'label' => __('OAuth Access Token'),
                'value' => $account->getData('outh_access_token'),
            ]
        );

        if ($account->getId()) {
            $form->getElement('account_code')->setDisabled(1);
        }
        $form->getElement('outh_token_secret')->setDisabled(1);
        $form->getElement('outh_access_token')->setDisabled(1);
        $form->getElement('outh_verifier')->setDisabled(1);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}