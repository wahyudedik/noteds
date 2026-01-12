<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockPrice;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdxApiService
{
    /**
     * Base URL for IDX API.
     */
    protected string $baseUrl;

    /**
     * API key for authentication (if required).
     */
    protected ?string $apiKey;

    /**
     * Request timeout in seconds.
     */
    protected int $timeout;

    /**
     * Create a new IdxApiService instance.
     */
    public function __construct()
    {
        $config = config('services.idx_api');
        $this->baseUrl = $config['base_url'] ?? 'https://www.idx.co.id';
        $this->apiKey = $config['api_key'] ?? null;
        $this->timeout = $config['timeout'] ?? 30;
    }

    /**
     * Get all listed stocks.
     *
     * @return Collection<Stock>
     */
    public function fetchStockList(): Collection
    {
        try {
            // Attempt to fetch from API endpoint if available
            // Note: IDX may not have a public API, so this might need web scraping
            $response = $this->makeRequest('/api/stock/getList', [
                'method' => 'GET',
            ]);

            if ($response && isset($response['data'])) {
                return $this->parseStockList($response['data']);
            }

            // Fallback: Return empty collection if API not available
            // In production, this would implement web scraping
            Log::warning('IDX API endpoint for stock list not available, returning empty collection');

            return collect();
        } catch (\Exception $e) {
            Log::error('Failed to fetch stock list from IDX API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return collect();
        }
    }

    /**
     * Get stock price for a specific date.
     *
     * @param string $code Stock code (e.g., 'BBRI', 'BBCA')
     * @param Carbon|null $date Date to fetch price for (default: today)
     * @return StockPrice|null
     */
    public function fetchStockPrice(string $code, ?Carbon $date = null): ?StockPrice
    {
        if ($date === null) {
            $date = Carbon::today();
        }

        try {
            $stock = Stock::where('code', $code)->first();
            if (!$stock) {
                Log::warning('Stock not found for code', ['code' => $code]);
                return null;
            }

            // Format date for API request
            $dateString = $date->format('Y-m-d');

            // Attempt to fetch from API
            $response = $this->makeRequest('/api/stock/getStockPrice', [
                'method' => 'GET',
                'params' => [
                    'code' => $code,
                    'date' => $dateString,
                ],
            ]);

            if ($response && isset($response['data'])) {
                return $this->parseStockPrice($stock, $response['data'], $date);
            }

            // Fallback: Try to get from database if API not available
            return $stock->getPriceAt($date);
        } catch (\Exception $e) {
            Log::error('Failed to fetch stock price from IDX API', [
                'code' => $code,
                'date' => $date->format('Y-m-d'),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get historical prices for a stock within a date range.
     *
     * @param string $code Stock code
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection<StockPrice>
     */
    public function fetchHistoricalPrices(string $code, Carbon $startDate, Carbon $endDate): Collection
    {
        try {
            $stock = Stock::where('code', $code)->first();
            if (!$stock) {
                Log::warning('Stock not found for code', ['code' => $code]);
                return collect();
            }

            // Attempt to fetch from API
            $response = $this->makeRequest('/api/stock/getHistoricalPrices', [
                'method' => 'GET',
                'params' => [
                    'code' => $code,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
            ]);

            if ($response && isset($response['data'])) {
                return $this->parseHistoricalPrices($stock, $response['data']);
            }

            // Fallback: Get from database
            return $stock->prices()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('is_intraday', false)
                ->orderBy('date')
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch historical prices from IDX API', [
                'code' => $code,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Get real-time intraday price for a stock.
     *
     * @param string $code Stock code
     * @return StockPrice|null
     */
    public function fetchIntradayPrice(string $code): ?StockPrice
    {
        try {
            $stock = Stock::where('code', $code)->first();
            if (!$stock) {
                Log::warning('Stock not found for code', ['code' => $code]);
                return null;
            }

            // Attempt to fetch from API
            $response = $this->makeRequest('/api/stock/getIntradayPrice', [
                'method' => 'GET',
                'params' => [
                    'code' => $code,
                ],
            ]);

            if ($response && isset($response['data'])) {
                return $this->parseIntradayPrice($stock, $response['data']);
            }

            // Fallback: Get latest intraday from database
            return $stock->prices()
                ->where('is_intraday', true)
                ->latest('timestamp')
                ->first();
        } catch (\Exception $e) {
            Log::error('Failed to fetch intraday price from IDX API', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get stock metadata (company info, sector, etc.).
     *
     * @param string $code Stock code
     * @return array
     */
    public function fetchStockMetadata(string $code): array
    {
        try {
            // Attempt to fetch from API
            $response = $this->makeRequest('/api/stock/getMetadata', [
                'method' => 'GET',
                'params' => [
                    'code' => $code,
                ],
            ]);

            if ($response && isset($response['data'])) {
                return $this->parseStockMetadata($response['data']);
            }

            // Fallback: Get from database
            $stock = Stock::where('code', $code)->first();
            if ($stock) {
                return [
                    'code' => $stock->code,
                    'name' => $stock->name,
                    'sector' => $stock->sector,
                    'sub_sector' => $stock->sub_sector,
                    'listing_date' => $stock->listing_date?->format('Y-m-d'),
                    'market_cap' => $stock->market_cap,
                    'category' => $stock->category,
                    'metadata' => $stock->metadata,
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch stock metadata from IDX API', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get active stocks.
     *
     * @return Collection<Stock>
     */
    public function getActiveStocks(): Collection
    {
        return Stock::active()->get();
    }

    /**
     * Get stocks by category.
     *
     * @param string $category Category (LQ45, IDX30, IDX80, Kompas100, etc.)
     * @return Collection<Stock>
     */
    public function getStocksByCategory(string $category): Collection
    {
        return Stock::byCategory($category)->get();
    }

    /**
     * Make HTTP request to IDX API.
     *
     * @param string $endpoint API endpoint
     * @param array $options Request options
     * @return array|null
     */
    protected function makeRequest(string $endpoint, array $options = []): ?array
    {
        $method = $options['method'] ?? 'GET';
        $params = $options['params'] ?? [];
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        try {
            $client = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                ]);

            // Add API key if available
            if ($this->apiKey) {
                $client->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey]);
            }

            if ($method === 'GET') {
                $response = $client->get($url, $params);
            } else {
                $response = $client->post($url, $params);
            }

            if ($response->successful()) {
                return $response->json();
            }

            // If API endpoint doesn't exist or returns error, return null
            // This allows fallback to web scraping or database
            Log::debug('IDX API request not successful', [
                'url' => $url,
                'status' => $response->status(),
                'method' => $method,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::debug('IDX API request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse stock list from API response.
     *
     * @param array $data API response data
     * @return Collection<Stock>
     */
    protected function parseStockList(array $data): Collection
    {
        $stocks = collect();

        foreach ($data as $item) {
            $stock = Stock::firstOrCreate(
                ['code' => $item['code'] ?? $item['Code'] ?? null],
                [
                    'name' => $item['name'] ?? $item['Name'] ?? '',
                    'sector' => $item['sector'] ?? $item['Sector'] ?? null,
                    'sub_sector' => $item['sub_sector'] ?? $item['SubSector'] ?? null,
                    'listing_date' => isset($item['listing_date']) ? Carbon::parse($item['listing_date']) : null,
                    'is_active' => $item['is_active'] ?? $item['IsActive'] ?? true,
                    'market_cap' => $item['market_cap'] ?? $item['MarketCap'] ?? null,
                    'category' => $item['category'] ?? $item['Category'] ?? null,
                    'metadata' => $item['metadata'] ?? $item['Metadata'] ?? [],
                ]
            );

            $stocks->push($stock);
        }

        return $stocks;
    }

    /**
     * Parse stock price from API response.
     *
     * @param Stock $stock Stock model
     * @param array $data API response data
     * @param Carbon $date Price date
     * @return StockPrice|null
     */
    protected function parseStockPrice(Stock $stock, array $data, Carbon $date): ?StockPrice
    {
        try {
            $priceData = [
                'stock_id' => $stock->id,
                'date' => $date->format('Y-m-d'),
                'open' => $data['open'] ?? $data['Open'] ?? $data['open_price'] ?? null,
                'high' => $data['high'] ?? $data['High'] ?? $data['high_price'] ?? null,
                'low' => $data['low'] ?? $data['Low'] ?? $data['low_price'] ?? null,
                'close' => $data['close'] ?? $data['Close'] ?? $data['close_price'] ?? $data['price'] ?? null,
                'volume' => $data['volume'] ?? $data['Volume'] ?? $data['trade_volume'] ?? 0,
                'value' => $data['value'] ?? $data['Value'] ?? $data['trade_value'] ?? 0,
                'frequency' => $data['frequency'] ?? $data['Frequency'] ?? $data['trade_count'] ?? null,
                'is_intraday' => false,
            ];

            // Remove null values for optional fields
            $priceData = array_filter($priceData, fn($value) => $value !== null);

            return StockPrice::updateOrCreate(
                [
                    'stock_id' => $stock->id,
                    'date' => $date->format('Y-m-d'),
                    'is_intraday' => false,
                ],
                $priceData
            );
        } catch (\Exception $e) {
            Log::error('Failed to parse stock price', [
                'stock_id' => $stock->id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse historical prices from API response.
     *
     * @param Stock $stock Stock model
     * @param array $data API response data
     * @return Collection<StockPrice>
     */
    protected function parseHistoricalPrices(Stock $stock, array $data): Collection
    {
        $prices = collect();

        foreach ($data as $item) {
            $date = Carbon::parse($item['date'] ?? $item['Date'] ?? $item['trade_date'] ?? now());

            $priceData = [
                'stock_id' => $stock->id,
                'date' => $date->format('Y-m-d'),
                'open' => $item['open'] ?? $item['Open'] ?? $item['open_price'] ?? null,
                'high' => $item['high'] ?? $item['High'] ?? $item['high_price'] ?? null,
                'low' => $item['low'] ?? $item['Low'] ?? $item['low_price'] ?? null,
                'close' => $item['close'] ?? $item['Close'] ?? $item['close_price'] ?? $item['price'] ?? null,
                'volume' => $item['volume'] ?? $item['Volume'] ?? $item['trade_volume'] ?? 0,
                'value' => $item['value'] ?? $item['Value'] ?? $item['trade_value'] ?? 0,
                'frequency' => $item['frequency'] ?? $item['Frequency'] ?? $item['trade_count'] ?? null,
                'is_intraday' => false,
            ];

            // Remove null values for optional fields
            $priceData = array_filter($priceData, fn($value) => $value !== null);

            $price = StockPrice::updateOrCreate(
                [
                    'stock_id' => $stock->id,
                    'date' => $date->format('Y-m-d'),
                    'is_intraday' => false,
                ],
                $priceData
            );

            $prices->push($price);
        }

        return $prices;
    }

    /**
     * Parse intraday price from API response.
     *
     * @param Stock $stock Stock model
     * @param array $data API response data
     * @return StockPrice|null
     */
    protected function parseIntradayPrice(Stock $stock, array $data): ?StockPrice
    {
        try {
            $timestamp = isset($data['timestamp']) 
                ? Carbon::parse($data['timestamp']) 
                : (isset($data['time']) ? Carbon::parse($data['time']) : now());
            
            $date = $timestamp->copy()->startOfDay();

            $priceData = [
                'stock_id' => $stock->id,
                'date' => $date->format('Y-m-d'),
                'open' => $data['open'] ?? $data['Open'] ?? $data['open_price'] ?? null,
                'high' => $data['high'] ?? $data['High'] ?? $data['high_price'] ?? null,
                'low' => $data['low'] ?? $data['Low'] ?? $data['low_price'] ?? null,
                'close' => $data['close'] ?? $data['Close'] ?? $data['close_price'] ?? $data['price'] ?? $data['last_price'] ?? null,
                'volume' => $data['volume'] ?? $data['Volume'] ?? $data['trade_volume'] ?? 0,
                'value' => $data['value'] ?? $data['Value'] ?? $data['trade_value'] ?? 0,
                'frequency' => $data['frequency'] ?? $data['Frequency'] ?? $data['trade_count'] ?? null,
                'is_intraday' => true,
                'timestamp' => $timestamp,
            ];

            // Remove null values for optional fields
            $priceData = array_filter($priceData, fn($value) => $value !== null);

            return StockPrice::create($priceData);
        } catch (\Exception $e) {
            Log::error('Failed to parse intraday price', [
                'stock_id' => $stock->id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse stock metadata from API response.
     *
     * @param array $data API response data
     * @return array
     */
    protected function parseStockMetadata(array $data): array
    {
        return [
            'code' => $data['code'] ?? $data['Code'] ?? null,
            'name' => $data['name'] ?? $data['Name'] ?? $data['company_name'] ?? null,
            'sector' => $data['sector'] ?? $data['Sector'] ?? null,
            'sub_sector' => $data['sub_sector'] ?? $data['SubSector'] ?? null,
            'listing_date' => isset($data['listing_date']) ? Carbon::parse($data['listing_date'])->format('Y-m-d') : null,
            'market_cap' => $data['market_cap'] ?? $data['MarketCap'] ?? null,
            'category' => $data['category'] ?? $data['Category'] ?? null,
            'metadata' => $data['metadata'] ?? $data['Metadata'] ?? $data,
        ];
    }
}
