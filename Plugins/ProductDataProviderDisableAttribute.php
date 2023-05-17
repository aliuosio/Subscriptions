<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Plugins;

use Osio\Subscriptions\Helper\Data as Helper;

class ProductDataProviderDisableAttribute
{

    public function __construct(
        private readonly Helper $helper
    ) {
    }

    public function afterGetMeta($subject, $meta)
    {
        if (!$this->helper->isEnabled()) {
            unset($meta['product-details']['children'][$this->getContainer()]['children'][$this->helper->getCode()]);
        }

        return $meta;
    }

    private function getContainer(): string
    {
        return 'container_' . $this->helper->getCode();
    }
}
