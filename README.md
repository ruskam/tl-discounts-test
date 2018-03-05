# Service calculating discounts for orders
**This is a REST Service implemented using SlimPHP framework**

### System Requirements

* Web server with URL rewriting
* PHP 5.5 or newer

### Software requirements

The best way to install Slim framework is using [Composer](https://getcomposer.org/).
Once Composer is installed you can run:

`composer require slim/slim "^3.0"`

### REST API endpoint

The service is available at http://<'your-host'>/<'your-app'>/api/discount/

One should send a HTTP POST request. Sending [data](
https://github.com/teamleadercrm/coding-test/blob/master/example-orders/order1.json) is required.


### Business logic

The logic is represented by 3 main methods:
* discountForSwitchesCategory(...$args)
* discountForToolsCategory(...$args)
* discountOnTotalAmount()



