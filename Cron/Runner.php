<?php
/**
 * @todo: get Orders with Subscriptions inside. check for the date of order. check server current date.
 * If the interval exceeds subscription time get Products and create new order per customer
 * send this data to as parameter to $orderInfo param of createOrder($orderInfo)
 * $orderInfo info would be place by Injection dependency
 */

namespace Osio\Subscriptions\Cron;


use Osio\Subscriptions\Order\Place;

class Runner
{
    private Place $orderPlace;
    private OrderInfo $orderInfo; // (the mising object which searchs for the subscriptions)

    public function __construct(
        Place $orderPlace,
        OrderInfo $orderInfo
    ) {
        $this->orderPlace = $orderPlace;
        $this->orderInfo = $orderInfo;
    }
    public function execute()
    {
        $this->orderPlace->createOrder($this->orderInfo);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        return $this;

    }
}
