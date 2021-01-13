<?php


namespace Moln\IpQuery\Provider;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ChainProvider implements ProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array|ProviderInterface[]
     */
    private $providers = [];
    /**
     * @var array
     */
    private $ignoreCountries;

    /**
     * ChainProvider constructor.
     *
     * @param ProviderInterface[] $providers
     */
    public function __construct(array $providers, ?LoggerInterface $logger = null)
    {
        foreach ($providers as $key => $providerConfig) {
            if ($providerConfig instanceof ProviderInterface) {
                $providers[$key] = ['provider' => $providerConfig];
            } elseif (!isset($providerConfig['provider'])) {
                throw new \InvalidArgumentException('Invalid providers config.');
            }
        }
        $this->providers = $providers;
        $this->logger = $logger ?: new NullLogger();
    }

    public function query(string $ip, array $context = []): array
    {
        $ex = null;
        $candidate = null;
        foreach ($this->providers as $providerConfig) {
            try {
                $provider = $providerConfig['provider'];

                $result = $provider->query($ip, $context);

                if (isset($providerConfig['allow_countries']) &&
                    !in_array($result['country'], $providerConfig['allow_countries'])) {
                    $candidate = $result;
                    continue;
                }

                return $result;
            } catch(\Exception $e) {
                $this->logger->notice(get_class($provider) . ' query error: ' . $e->getMessage());
                $ex = $e;
            }
        }

        if ($candidate) {
            return $candidate;
        }

        throw new \RuntimeException('Providers query error.', 0, $ex);
    }
}
