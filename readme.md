# Cloud Royale API

Unoffical API for Cloud Royale. (They don't have any offical API at the moment.)

## Installation

`composer require s4wny/cloud-royale-api`


## Documentation

Example usage:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use CloudRoyaleAPI\CloudRoyaleAPI;

$api = new CloudRoyaleAPI("name@domain.com", "123456");

// Login
var_dump($api->login());

// Get all your servers ( [ID => server name] )
$servers = $api->getServers();
print_r($servers);

// Get status about a specific server
$firstServer = key($servers);
print_r(json_decode($api->getStatus($firstServer)));

/* Output:

bool(true)

Array
(
    [sadffsd34rfxd3] => node-server
    [oxcujv8324sfdk] => http-server
    [sdfxvujdf328fd] => vpn
)

stdClass Object
(
    [sadffsd34rfxd3] => stdClass Object
        (
            [status] => On
            [memory] => 8
            [cpus] => 3
            [disk_size] => 30
            [disks] => stdClass Object
                (
                    [1337] => stdClass Object
                        (
                            [size] => 30
                            [storage] => 13
                        )
                )
        )
)
*/

?>
```

### Methods

 - __construct($username, $password)
 - login()
 - getStatus($serverID)
 - getServers()
 - startServer($serverID)
 - stopServer($serverID)
 - addSSHKeys($serverID)
 - createServer($config)
 


## License

This library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)