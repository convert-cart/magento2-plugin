<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model;

use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\OauthService;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class IntegrationTokenManager
{
    private IntegrationFactory $integrationFactory;
    private OauthService $oauthService;
    private WriterInterface $configWriter;
    private ScopeConfigInterface $scopeConfig;
    private EncryptorInterface $encryptor;

    public function __construct(
        IntegrationFactory $integrationFactory,
        OauthService $oauthService,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->oauthService = $oauthService;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function getOrCreateTokens(): array
    {
        $integration = $this->integrationFactory->create()->load('ConvertCart Analytics', 'name');
        
        if (!$integration->getId()) {
            throw new \Exception('ConvertCart Analytics integration not found');
        }

        $consumer = $this->oauthService->loadConsumer($integration->getConsumerId());
        
        if (!$consumer->getId()) {
            throw new \Exception('OAuth consumer not found for ConvertCart integration');
        }

        $accessToken = $this->oauthService->getAccessToken($consumer->getId());
        $this->configWriter->save(
            'convertcart/integration/access_token',
            $this->encryptor->encrypt($accessToken->getToken())
        );
        $this->configWriter->save(
            'convertcart/integration/access_token_secret',
            $this->encryptor->encrypt($accessToken->getSecret())
        );

        return [
            'consumer_key' => $consumer->getKey(),
            'consumer_secret' => $consumer->getSecret(),
            'access_token' => $accessToken->getToken(),
            'access_token_secret' => $accessToken->getSecret()
        ];
    }

    public function getStoredTokens(): ?array
    {
        $accessToken = $this->scopeConfig->getValue('convertcart/integration/access_token');
        $accessTokenSecret = $this->scopeConfig->getValue('convertcart/integration/access_token_secret');

        if (!$accessToken || !$accessTokenSecret) {
            return null;
        }

        return [
            'access_token' => $this->encryptor->decrypt($accessToken),
            'access_token_secret' => $this->encryptor->decrypt($accessTokenSecret)
        ];
    }
}
