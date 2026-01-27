<?php

declare(strict_types=1);

/**
 * RSS Widget - Displays RSS feed items
 *
 * Fetches and parses RSS/Atom feeds
 */
class RssWidget extends Widget
{
    /**
     * Fetch widget data
     *
     * @return array Widget data containing feed items
     */
    public function getData(): array
    {
        $feedUrl = $this->getSetting('feed_url');

        if (empty($feedUrl)) {
            return ['error' => 'Feed URL not configured'];
        }

        // Reason: Check cache first (15 min TTL)
        $cacheKey = 'rss_' . md5($feedUrl);
        $cached = $this->getCachedData($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $itemCount = (int) $this->getSetting('item_count', 5);

            // Fetch RSS feed
            $response = $this->httpGet($feedUrl, 10);

            if ($response === null) {
                return ['error' => 'Failed to fetch RSS feed'];
            }

            // Reason: Parse XML with error suppression to handle malformed feeds gracefully
            $prevErrorLevel = error_reporting(0);
            libxml_use_internal_errors(true);

            $xml = simplexml_load_string($response);

            error_reporting($prevErrorLevel);
            libxml_clear_errors();

            if ($xml === false) {
                return ['error' => 'Invalid RSS feed format'];
            }

            $items = $this->parseRssFeed($xml, $itemCount);

            if (empty($items)) {
                return ['error' => 'No items found in feed'];
            }

            $data = [
                'items' => $items,
                'feed_url' => $feedUrl
            ];

            // Reason: Cache for 15 minutes
            $this->setCachedData($cacheKey, $data, 900);

            return $data;

        } catch (Exception $e) {
            return ['error' => 'Failed to parse RSS feed: ' . $e->getMessage()];
        }
    }

    /**
     * Parse RSS feed XML into array of items
     *
     * @param SimpleXMLElement $xml Parsed XML
     * @param int $limit Maximum number of items to return
     * @return array Array of feed items
     */
    private function parseRssFeed(SimpleXMLElement $xml, int $limit): array
    {
        $items = [];

        // Reason: Try RSS 2.0 format first
        if (isset($xml->channel->item)) {
            $count = 0;
            foreach ($xml->channel->item as $item) {
                if ($count >= $limit) {
                    break;
                }

                $items[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'description' => $this->truncateHtml((string) $item->description, 150),
                    'pub_date' => $this->formatDate((string) $item->pubDate)
                ];

                $count++;
            }
        }
        // Reason: Try Atom format
        elseif (isset($xml->entry)) {
            $count = 0;
            foreach ($xml->entry as $entry) {
                if ($count >= $limit) {
                    break;
                }

                $link = '';
                if (isset($entry->link['href'])) {
                    $link = (string) $entry->link['href'];
                }

                $items[] = [
                    'title' => (string) $entry->title,
                    'link' => $link,
                    'description' => $this->truncateHtml((string) $entry->summary, 150),
                    'pub_date' => $this->formatDate((string) $entry->updated)
                ];

                $count++;
            }
        }

        return $items;
    }

    /**
     * Truncate HTML content to specified length
     *
     * @param string $html HTML content
     * @param int $length Maximum length
     * @return string Truncated text
     */
    private function truncateHtml(string $html, int $length): string
    {
        // Reason: Strip HTML tags and decode entities
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '...';
        }

        return $text;
    }

    /**
     * Format date string to human-readable format
     *
     * @param string $dateString Date string from feed
     * @return string Formatted date
     */
    private function formatDate(string $dateString): string
    {
        if (empty($dateString)) {
            return '';
        }

        try {
            $date = new DateTime($dateString);
            $now = new DateTime();
            $diff = $now->diff($date);

            // Reason: Show relative time for recent items
            if ($diff->days === 0) {
                if ($diff->h > 0) {
                    return $diff->h . 'h ago';
                } elseif ($diff->i > 0) {
                    return $diff->i . 'm ago';
                } else {
                    return 'Just now';
                }
            } elseif ($diff->days === 1) {
                return 'Yesterday';
            } elseif ($diff->days < 7) {
                return $diff->days . ' days ago';
            } else {
                return $date->format('M j, Y');
            }
        } catch (Exception $e) {
            return $dateString;
        }
    }

    /**
     * Validate widget settings
     *
     * @param array $settings Settings to validate
     * @return array Validation result
     */
    public function validateSettings(array $settings): array
    {
        $errors = [];

        // Reason: Feed URL is required
        if (empty($settings['feed_url'])) {
            $errors[] = 'Feed URL is required';
        } elseif (!filter_var($settings['feed_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Feed URL must be a valid URL';
        }

        // Reason: Item count must be positive integer
        if (isset($settings['item_count'])) {
            $count = (int) $settings['item_count'];
            if ($count < 1 || $count > 20) {
                $errors[] = 'Item count must be between 1 and 20';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get default settings
     *
     * @return array Default settings
     */
    public function getDefaultSettings(): array
    {
        return [
            'feed_url' => '',
            'item_count' => 5
        ];
    }
}
