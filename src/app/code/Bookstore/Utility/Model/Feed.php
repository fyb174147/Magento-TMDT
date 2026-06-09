<?php

namespace Bookstore\Utility\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Store\Model\ScopeInterface;

class Feed
{
    private const VCB_URL = 'https://portal.vietcombank.com.vn/Usercontrols/TVPortal.TyGia/pXML.aspx?b=68';
    private const NEWS_URL = 'https://vnexpress.net/rss/kinh-doanh.rss';

    public function __construct(
        private readonly CurlFactory $curlFactory,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getWeather(): array
    {
        $city = $this->scopeConfig->getValue(
            'bookstore_utility/weather/city',
            ScopeInterface::SCOPE_STORE
        ) ?: 'Hanoi,VN';

        $key = (string) $this->scopeConfig->getValue(
            'bookstore_utility/weather/api_key',
            ScopeInterface::SCOPE_STORE
        );

        if ($key === '') {
            return ['city' => $city, 'message' => 'Chưa cấu hình OpenWeatherMap API key'];
        }

        $url = 'https://api.openweathermap.org/data/2.5/weather?q='
            . rawurlencode($city)
            . '&appid=' . rawurlencode($key)
            . '&units=metric&lang=vi';

        $body = $this->getUrl($url);
        $data = json_decode($body, true);

        if (!is_array($data) || empty($data['main'])) {
            return ['city' => $city, 'message' => 'Không lấy được dữ liệu thời tiết'];
        }

        return [
            'city'          => $data['name'] ?? $city,
            'temperature'   => $data['main']['temp'] ?? null,
            'feels_like'    => $data['main']['feels_like'] ?? null,
            'humidity'      => $data['main']['humidity'] ?? null,
            'pressure'      => $data['main']['pressure'] ?? null,
            'wind_speed'    => $data['wind']['speed'] ?? null,
            'description'   => $data['weather'][0]['description'] ?? '',
            'icon'          => $data['weather'][0]['icon'] ?? '01d',
            'visibility'    => $data['visibility'] ?? null,
        ];
    }

    public function getRates(): array
    {
        $body = $this->getUrl(self::VCB_URL);
        $xml = @simplexml_load_string($body);

        if (!$xml) {
            return [];
        }

        $rates = [];
        foreach ($xml->Exrate as $rate) {
            $rates[] = [
                'code'     => (string) $rate['CurrencyCode'],
                'name'     => trim((string) $rate['CurrencyName']),
                'buy'      => (string) $rate['Buy'],
                'transfer' => (string) $rate['Transfer'],
                'sell'     => (string) $rate['Sell'],
            ];
        }

        return $rates;
    }

    public function getNews(): array
    {
        $body = $this->getUrl(self::NEWS_URL);
        $xml = @simplexml_load_string($body);

        if (!$xml || empty($xml->channel->item)) {
            return [];
        }

        $items = [];
        foreach ($xml->channel->item as $item) {
            $description = trim(strip_tags((string) $item->description));
            $items[] = [
                'title'       => (string) $item->title,
                'link'        => (string) $item->link,
                'description' => mb_substr($description, 0, 200),
                'date'        => (string) $item->pubDate,
                'image'       => $this->extractImage((string) $item->description),
            ];
            if (count($items) >= 8) {
                break;
            }
        }

        return $items;
    }

    public function getRatesJson(): string
    {
        return json_encode($this->getRates());
    }

    public function getWeatherJson(): string
    {
        return json_encode($this->getWeather());
    }

    public function getNewsJson(): string
    {
        return json_encode($this->getNews());
    }

    private function extractImage(string $html): string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)/', $html, $m)) {
            return $m[1];
        }
        return '';
    }

    private function getUrl(string $url): string
    {
        try {
            $curl = $this->curlFactory->create();
            $curl->setTimeout(8);
            $curl->addHeader('Accept', '*/*');
            $curl->addHeader('User-Agent', 'BookStore-Utility/1.0');
            $curl->get($url);
            return (string) $curl->getBody();
        } catch (\Throwable) {
            return '';
        }
    }
}