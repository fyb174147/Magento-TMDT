<?php

namespace Bookstore\Utility\Block;

use Bookstore\Utility\Model\Feed;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

class Info extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly Feed $feed,
        private readonly UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getWeather(): array
    {
        return $this->feed->getWeather();
    }

    public function getRates(): array
    {
        return $this->feed->getRates();
    }

    public function getNews(): array
    {
        return $this->feed->getNews();
    }

    public function getAjaxFeedUrl(): string
    {
        return $this->urlBuilder->getDirectUrl('bookstore-utility-ajax/feed');
    }
}