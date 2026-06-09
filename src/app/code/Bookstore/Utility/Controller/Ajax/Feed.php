<?php

namespace Bookstore\Utility\Controller\Ajax;

use Bookstore\Utility\Model\Feed as FeedModel;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Feed implements HttpGetActionInterface
{
    public function __construct(
        private readonly FeedModel $feed,
        private readonly JsonFactory $jsonFactory
    ) {
    }

    public function execute()
    {
        $section = (string) $this->getRequest()->getParam('section', 'all');

        $result = $this->jsonFactory->create();

        switch ($section) {
            case 'weather':
                $data = $this->feed->getWeather();
                break;
            case 'rates':
                $data = $this->feed->getRates();
                break;
            case 'news':
                $data = $this->feed->getNews();
                break;
            default:
                $data = [
                    'weather' => $this->feed->getWeather(),
                    'rates'   => $this->feed->getRates(),
                    'news'    => $this->feed->getNews(),
                ];
                break;
        }

        return $result->setData($data);
    }
}