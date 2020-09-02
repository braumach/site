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
 * @package   Ced_GXpress
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Block\Adminhtml\Account\Edit\Tab;

/**
 * Class Info
 * @package Ced\GXpress\Block\Adminhtml\Account\Edit\Tab
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
            $account = $this->_objectManager->get('Ced\GXpress\Model\Accounts')->load($id);
        } else {
            $account = $this->_objectManager->get('Ced\GXpress\Model\Accounts');
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
                'values' => $this->_objectManager->get('Ced\GXpress\Model\Config\Environment')->getOptionArray(),
            )
        );

        $fieldset->addField('merchant_id', 'text',
            array(
                'name' => "merchant_id",
                'label' => __('Merchant Id'),
                'required' => true,
                'value' => $account->getData('merchant_id'),
            )
        );

        $fieldset->addField('account_status', 'select',
            array(
                'name' => "account_status",
                'label' => __('Account Status'),
                'required' => true,
                'value' => $account->getData('account_status'),
                'values' => $this->_objectManager->get('Ced\GXpress\Model\Source\Account\Status')->getOptionArray(),
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

        if(!$account->getData('account_file')) {
            $fieldset->addField('account_file', 'file',
                array(
                    'name' => "account_file",
                    'label' => __('Client Secret File'),
                    'required' => true,
                    'value' => $account->getData('account_file')
                )
            );
        } else {
            $fieldset->addField('account_file', 'file',
                array(
                    'name' => "account_file",
                    'label' => __('Client Secret File'),
                    'required' => false,
                    'value' => $account->getData('account_file')
                )
            );
        }


        $fieldset->addField('account_token', 'textarea',
            array(
                'name' => "account_token",
                'label' => __('Token'),
                'value' => $account->getData('account_token'),
            )
        );

        if ($account->getId()) {
            $form->getElement('account_code')->setDisabled(1);
        }
        $form->getElement('account_token')->setDisabled(1);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}