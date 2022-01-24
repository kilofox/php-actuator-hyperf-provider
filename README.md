# php-actuator-hyperf-provider

[![Release Version](https://img.shields.io/github/release/kilofox/php-actuator-hyperf-provider.svg)](https://github.com/kilofox/php-actuator-hyperf-provider/releases/latest) [![Latest Release Download](https://img.shields.io/github/downloads/kilofox/php-actuator-hyperf-provider/latest/total.svg)](https://github.com/kilofox/php-actuator-hyperf-provider/releases/latest) [![Total Download](https://img.shields.io/github/downloads/kilofox/php-actuator-hyperf-provider/total.svg)](https://github.com/kilofox/php-actuator-hyperf-provider/releases)

Hyperf provider for php-actuator.

## Install

Via Composer

``` bash
$ composer require kilofox/php-actuator-hyperf-provider
```

## Usage

Setup the route you would like your health check on. e.g.:

```php
Router::addRoute(['GET'], '/health', 'App\Controller\IndexController@health');
```

Then visit your endpoint. In this case: `/health`

## Getting Started

* Create /app/Controller/IndexController.php

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Kilofox\Actuator\HealthServiceProvider;

class IndexController extends AbstractController
{
    /**
     * @Inject
     * @var HealthServiceProvider
     */
    private $health;

    public function index()
    {
        $memcached = new \Memcached();
        $memcached->addServer('127.0.0.1', 11211);

        return $this->health
                ->addIndicator('disk')
                ->addIndicator('memcached', $memcached)
                ->getHealth($this->response);
    }

}

```

* Run the service `php bin/hyperf.php start`
* Go to http://localhost:9501/health to see your health indicator.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
