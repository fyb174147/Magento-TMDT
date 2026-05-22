<?php

namespace Bookstore\Utility\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Store\Model\ScopeInterface;

class Feed
{
    private const VCB_URL = 'https://portal.vietcombank.com.vn/Usercontrols/TVPortal.TyGia/pXML.aspx?b=68';
    private const NEWS_URL = 'https://vnexpress.net/rss/kinh-doanh.rss';

    public function __construct(
        private readonly CurlFactory $curlFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function getWeather(): array
    {
        $city = $this->scopeConfig->getValue('bookstore_utility/weather/city', ScopeInterface::SCOPE_STORE) ?: 'Hanoi,VN';
        $key = (string)$this->scopeConfig->getValue('bookstore_utility/weather/api_key', ScopeInterface::SCOPE_STORE);
        $key = $key ? $this->encryptor->decrypt($key) : '';
        if ($key === '') {
            return ['city' => $city, 'message' => 'Chua cau hinh OpenWeatherMap API key'];
        }

        $url = 'https://api.openweathermap.org/data/2.5/weather?q=' . rawurlencode($city) . '&appid=' . rawurlencode($key) . '&units=metric&lang=vi';
        $data = json_decode($this->getUrl($url), true);
        if (!is_array($data) || empty($data['main'])) {
            return ['city' => $city, 'message' => 'Khong lay duoc du lieu thoi tiet'];
        }

        return [
            'city' => $data['name'] ?? $city,
            'temperature' => $data['main']['temp'] ?? null,
            'humidity' => $data['main']['humidity'] ?? null,
            'description' => $data['weather'][0]['description'] ?? '',
        ];
    }

    public function getRates(): array
    {
        $xml = @simplexml_load_string($this->getUrl(self::VCB_URL));
        if (!$xml) {
            return [];
        }

        $wanted = ['USD', 'EUR', 'JPY', 'AUD', 'GBP'];
        $rates = [];
        foreach ($xml->Exrate as $rate) {
            $code = (string)$rate['CurrencyCode'];
            if (in_array($code, $wanted, true)) {
                $rates[] = [
                    'code' => $code,
                    'name' => (string)$rate['CurrencyName'],
                    'buy' => (string)$rate['Buy'],
                    'sell' => (string)$rate['Sell'],
                ];
            }
        }

        return $rates;
    }

    public function getNews(): array
    {
        $xml = @simplexml_load_string($this->getUrl(self::NEWS_URL));
        if (!$xml || empty($xml->channel->item)) {
            return [];
        }

        $items = [];
        foreach ($xml->channel->item as $item) {
            $items[] = [
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'description' => trim(strip_tags((string)$item->description)),
                'date' => (string)$item->pubDate,
            ];
            if (count($items) >= 6) {
                break;
            }
        }

        return $items;
    }

    private function getUrl(string $url): string
    {
        try {
            $curl = $this->curlFactory->create();
            $curl->setTimeout(8);
            $curl->get($url);
            return (string)$curl->getBody();
        } catch (\Throwable) {
            return '';
        }
    }
}
