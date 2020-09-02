<?php

namespace TemplateMonster\SocialLogin\Observer;

use TemplateMonster\SocialLogin\Model\ResourceModel\Provider\Collection as ProviderCollection;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Magento\Customer\Model\Customer;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;

/**
 * Class RedirectToAccountEditObserver.
 */
class RedirectToAccountEditObserver implements ObserverInterface
{
    /**
     * @var ProviderCollection
     */
    protected $_collection;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Encryption model
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * RedirectToAccountEditObserver constructor.
     *
     * @param ProviderCollection $collection
     * @param ResponseInterface  $response
     * @param ManagerInterface   $messageManager
     * @param UrlInterface       $urlBuilder
     */
    public function __construct(
        ProviderCollection $collection,
        ResponseInterface $response,
        ManagerInterface $messageManager,
        UrlInterface $urlBuilder,
        EncryptorInterface $encryptor,
        CustomerRegistry $customerRegistry,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_collection = $collection;
        $this->_response = $response;
        $this->_messageManager = $messageManager;
        $this->_urlBuilder = $urlBuilder;
        $this->encryptor = $encryptor;
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $password = $observer->getEvent()->getData('password');
        /** @var \Magento\Customer\Model\Customer $model */
        $model = $observer->getEvent()->getData('model');

        $customerSecure = $this->customerRegistry->retrieveSecureData($model->getId());
        $hash = $customerSecure->getPasswordHash();
        if (!$hash || !$this->encryptor->validateHashVersion($hash, true)) {
            $customerSecure->setPasswordHash($this->encryptor->getHash($password, true));
        }

        if (!($observer->getData('isUsingOAuth') && $observer->getData('isFirstLogin'))) {
            return;
        }

        $code = $observer->getDataByPath('data/provider_code');
        $provider = $this->_collection->getItemById($code);

        if ($provider->isHasMissingData()) {
            $this->_messageManager->addNotice('Your email address was auto generated. Please specify your correct email!');
            $this->_response->setRedirect($this->_urlBuilder->getUrl('/customer/account/edit'));
        }
    }
}
