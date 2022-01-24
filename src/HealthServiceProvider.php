<?php

namespace Kilofox\Actuator;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Actuator\Health\Health;
use Actuator\Health\Indicator\CompositeHealthIndicator;
use Actuator\Health\OrderedHealthAggregator;

class HealthServiceProvider
{
    /**
     * @var \Actuator\Health\HealthAggregatorInterface
     */
    protected $aggregator;

    /**
     * @var \Actuator\Health\Indicator\HealthIndicatorInterface
     */
    protected $indicators = [];

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->aggregator = $container->get(OrderedHealthAggregator::class);
    }

    /**
     * Add a health indicator.
     *
     * @param string $name Indicator name
     * @param mixed $arguments
     * @return mixed
     */
    public function addIndicator(string $name, $arguments = null)
    {
        switch ($name) {
            case 'disk':
            case 'diskspace':
                $indicator = 'DiskSpace';
                break;
            case 'doctrine':
                $indicator = 'DoctrineConnection';
                break;
            case 'memcached':
            case 'memcache':
                $indicator = ucfirst($name);
                break;
            default:
                return $this;
        }

        $indicator = '\\Actuator\\Health\\Indicator\\' . ucfirst($indicator) . 'HealthIndicator';
        $this->indicators[$name] = new $indicator($arguments);

        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function getHealth(ResponseInterface $response)
    {
        $healthResult = $this->getHealthResult();
        $healthBody = $this->formatHealthResult($healthResult);

        $response = $response->withHeader('Content-Type', 'application/json');
        $body = $response->getBody();
        $body->write(json_encode($healthBody));

        return $response;
    }

    /**
     * @return Health
     */
    private function getHealthResult()
    {
        $healthIndicator = new CompositeHealthIndicator($this->aggregator);

        foreach ($this->indicators as $key => $entry) {
            $healthIndicator->addHealthIndicator($key, $entry);
        }

        return $healthIndicator->health();
    }

    /**
     * @param Health $healthResult
     * @return array
     */
    private function formatHealthResult(Health $healthResult)
    {
        $healthDetails = [];

        foreach ($healthResult->getDetails() as $key => $healthDetail) {
            $healthDetails[$key] = array_merge(
                ['status' => $healthDetail->getStatus()->getCode()],
                $healthDetail->getDetails()
            );
        }

        $healthDetails = array_merge(
            ['status' => $healthResult->getStatus()->getCode()],
            $healthDetails
        );

        return $healthDetails;
    }

}
