<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    CatchSdk-Sdk
 * @package     Ced_CatchSdk
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace CatchSdk\Core;


class Config implements \CatchSdk\Core\ConfigInterface
{

    /**
     * Catch Api Key
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * Catch Endpoint Url
     * @var string $endpoint
     * @refer var $marketplaceIds
     */
    protected $apiUrl;

    /**
     * Mute Logging
     * @var boolean $debugMode
     */
    protected $debugMode;

    /**
     * Base Directory
     * @var boolean $baseDirectory
     */
    protected $baseDirectory;

    /**
     * Xml Parser
     * @var $parser
     */
    protected $parser;

    /**
     * Xml Generator
     * @var $generator
     */
    protected $generator;

    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * [
     * 'apiKey' => 'rOnMU7LxyLSE1VtaNUtTcpEbXje/0FFrE29g+isl',
     * 'apiUrl' => 'https://catch-dev.mirakl.net/',
     * 'baseDirectory' => ''
     * 'debugMode' => false
     * 'logger' => object
     * ]
     * @inheritdoc
     */
    public function __construct($params = [])
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @inheritdoc
     */
    public function setDebugMode($debugMode = true)
    {
        $this->debugMode = $debugMode;
    }

    /**
     * Get Catch ApiKey
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set Catch ApiKey
     * @param string $apiKey
     * @return boolean
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return true;
    }

    /**
     * Get Catch Api Url
     * @return string
     */
    public function getApiUrl()
    {
        if (!isset($this->apiUrl)) {
            $this->apiUrl = \CatchSdk\Core\RequestInterface::CATCH_SANDBOX_API_URL;
        }
        return $this->apiUrl;
    }

    /**
     * Set Catch Api Url
     * @param string $apiUrl
     * @return boolean
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
        return true;
    }

    public function getBaseDirectory()
    {
        if (!isset($this->baseDirectory)) {
            $this->baseDirectory = __DIR__ . DS . '..'.DS.'..'.DS.'tmp';
        }
        return $this->baseDirectory;
    }

    public function setBaseDirectory($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
        return true;
    }

    public function getParser()
    {
        if (!isset($this->parser)) {
            $this->parser = new \CatchSdk\Core\Parser();
        }

        return $this->parser;
    }

    public function setParser($parser)
    {
        $this->parser = $parser;
    }

    public function getGenerator()
    {
        if (!isset($this->generator)) {
            $this->generator = new \CatchSdk\Core\Generator();
        }
        return $this->generator;
    }

    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    /**
     * Get Logger
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = new \Monolog\Logger('CATCH');
            $this->logger->pushHandler(
                new \Monolog\Handler\StreamHandler(
                    $this->getFile($this->baseDirectory, 'catch_api.log'),
                    \Monolog\Logger::DEBUG
                )
            );
        }

        return $this->logger;
    }

    /**
     * Set Logger
     * @param \Psr\Log\LoggerInterface $logger
     * @return mixed
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return true;
    }

    /**
     * Get a File or Create
     * @param $path
     * @param null $name
     * @return string
     */
    public function getFile($path, $name = null)
    {

        if (!file_exists($path)) {
            @mkdir($path, 0775, true);
        }

        if ($name != null) {
            $path = $path . DS . $name;

            if (!file_exists($path)) {
                @file($path);
            }
        }

        return $path;
    }
}