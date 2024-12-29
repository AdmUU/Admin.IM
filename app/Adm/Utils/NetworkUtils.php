<?php

declare(strict_types=1);
/**
 * This file is part of Admin.IM
 *
 * @link     https://www.admin.im
 * @github   https://github.com/AdmUU/Admin.IM
 * @contact  dev@admin.im
 * @license  https://github.com/AdmUU/Admin.IM/blob/main/LICENSE
 */

namespace App\Adm\Utils;

use App\Adm\Interfaces\AdmIpLocationInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mine\Abstracts\AbstractService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Network utils.
 */
class NetworkUtils extends AbstractService
{
    /**
     * Get IP addresses from a domain name.
     *
     * @param string $domain Domain name
     * @return array Array containing IPv4 and IPv6 addresses
     */
    public static function getDomainIPs(string $domain): array
    {
        $result = [
            'ipv4' => [],
            'ipv6' => [],
        ];

        $ipv4 = gethostbyname($domain);
        if (! $ipv4 or $ipv4 == $domain) {
            return $result;
        }
        $result['ipv4'][] = $ipv4;

        $v6_records = dns_get_record($domain, DNS_AAAA);
        foreach ($v6_records as $record) {
            if (isset($record['ipv6'])) {
                $result['ipv6'][] = $record['ipv6'];
            }
        }
        return $result;
    }

    /**
     * Validate IP, domain or URL.
     *
     * @param string $input Input string
     * @return array|bool Array containing address type and address
     */
    public static function validateIPDomain(string $input): array|bool
    {
        $input = preg_replace('/^[\s\/\:]*([^\s\/\:]+)[\s\/\:]*$/', '$1', $input);
        if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return ['address_type' => 'ipv4', 'address' => $input];
        }

        if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return ['address_type' => 'ipv6', 'address' => $input];
        }

        if ($domain = idn_to_ascii($input, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46)) {
            if (preg_match('/^((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,6}(:\d{1,5})?$/', $domain, $matches)) {
                $parts = explode(':', $domain);
                $result = ['address_type' => 'domain', 'address' => $parts[0]];
                if (isset($parts[1])) {
                    $result['port'] = $parts[1];
                }
                return $result;
            }
        }

        $parsed = parse_url($input);
        if (filter_var($input, FILTER_VALIDATE_URL) || isset($parsed['host']) || preg_match('/\[.*\]/', $input)) {
            $parsed['host'] = idn_to_ascii($parsed['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $result = ['type' => 'URL'];

            if (isset($parsed['host'])) {
                if (preg_match('/^\[(.*)\]$/', $parsed['host'], $matches)) {
                    $ipv6 = $matches[1];
                    if (filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        $result['address_type'] = 'ipv6';
                        $result['address'] = $ipv6;
                    }
                } elseif (filter_var($parsed['host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $result['address_type'] = 'ipv4';
                    $result['address'] = $parsed['host'];
                } elseif (filter_var($parsed['host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $result['address_type'] = 'ipv6';
                    $result['address'] = $parsed['host'];
                } else {
                    $result['address_type'] = 'domain';
                    $result['address'] = $parsed['host'];
                }
            }

            if (isset($parsed['port'])) {
                $result['port'] = $parsed['port'];
            }

            return $result;
        }

        return false;
    }

    /**
     * Get client IP address.
     *
     * @param null|RequestInterface|ServerRequestInterface $request Request object
     * @param bool $ip_segment if true, return IP range instead of IP address
     * @return null|string Client IP address or null if not found
     */
    public static function getClientIp(null|RequestInterface|ServerRequestInterface $request = null, bool $ip_segment = false): ?string
    {
        $request = $request ?: get_request();
        if ($request == null) {
            return null;
        }

        $ip = null;

        $headers = [
            'X-Forwarded-For',
            'Http_X_Forwarded_For',
            'CF-Connecting-IP',
            'X-Real-IP',
        ];

        foreach ($headers as $header) {
            if ($request->hasHeader($header)) {
                $ip = self::getIpFromHeader($request->getHeaderLine($header));
                break;
            }
        }

        if (empty($ip)) {
            $serverParams = $request->getServerParams();
            $ip = $serverParams['remote_addr'] ?? '';
            if (preg_match('/^::ffff:(\d+\.\d+\.\d+\.\d+)$/', $ip, $matches)) {
                $ip = $matches[1];
            }
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        if ($ip_segment) {
            return self::getIPRange($ip);
        }
        return $ip;
    }

    /**
     * Get IP segment or range.
     *
     * @param string $ip IP address
     * @param string $type Range type: segment (default) or range
     * @param int $prefix Prefix length (default: 24 for IPv4, 48 for IPv6)
     * @return array|bool|string IP range or false on error
     */
    public static function getIPRange(string $ip, string $type = 'segment', ?int $prefix = null): array|bool|string
    {
        $version = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 :
            (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 6 : false);

        if ($version === false) {
            return false;
        }

        if ($version == 4) {
            $prefix = $prefix ?? 24;
            $binary = ip2long($ip);
            $mask = -1 << (32 - $prefix);
            $network = $binary & $mask;

            if ($type == 'segment') {
                return long2ip($network) . '/' . $prefix;
            }
            $broadcast = $network | ~$mask;
            $first = long2ip($network);
            $last = long2ip($broadcast);
        } else {
            $prefix = $prefix ?? 48;
            $addr = inet_pton($ip);
            $mask = str_repeat("\xFF", $prefix / 8) . str_repeat("\x00", 16 - $prefix / 8);
            if ($prefix % 8 > 0) {
                $mask[$prefix / 8] = chr(256 - pow(2, 8 - $prefix % 8));
            }

            $network = $addr & $mask;

            if ($type == 'segment') {
                return inet_ntop((string) $network) . '/' . $prefix;
            }
            $broadcast = $network | ~$mask;
            $first = inet_ntop((string) $network);
            $last = inet_ntop((string) $broadcast);
        }
        return [$first, $last];
    }

    /**
     * Check if the IP is cloudflare.
     *
     * @param string $ip IP address
     */
    public static function isCFIp(?string $ip): bool
    {
        if ($ip == null) {
            return false;
        }
        $ipLocation = container()->get(AdmIpLocationInterface::class);
        $ipinfo = $ipLocation->search($ip);
        if ($ipinfo && $ipinfo['isp'] == 'cf') {
            return true;
        }
        return false;
    }

    /**
     * Get prefer IP type from a domain name.
     *
     * @param string $domain Domain name
     * @return string IPv4|IPv6|all
     */
    public static function preferIPType(string $domain): bool|string
    {
        $ips = self::getDomainIPs($domain);
        if (count($ips['ipv4']) == 0) {
            return count($ips['ipv6']) == 0 ? false : 'ipv6';
        }
        if (count($ips['ipv6']) == 0) {
            return 'ipv4';
        }
        return 'dual';
    }

    /**
     * Get IP address from header.
     *
     * @param string $headerValue Header value
     * @return string IP address
     */
    private static function getIpFromHeader(string $headerValue): string
    {
        $ips = array_map('trim', explode(',', $headerValue));
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return '';
    }
}
