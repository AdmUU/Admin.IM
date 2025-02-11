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

namespace App\Adm\Service;

use App\Adm\Interfaces\AdmIpLocationInterface;
use App\Adm\Utils\LangUtils;
use MaxMind\Db\Reader;
use Mine\Annotation\DependProxy;
use Mine\Helper\Ip2region;

/**
 * Ip location service.
 */
#[DependProxy(values: [AdmIpLocationInterface::class])]
class AdmIpLocationService implements AdmIpLocationInterface
{
    private $reader;

    /**
     * Search for an IP address.
     *
     * @param string $ip IP address
     * @param string $format Return format, 'array' or 'string'
     */
    public function search(string $ip, string $format = 'array'): array|string
    {
        $data = ['country' => '', 'province' => '', 'region' => '', 'continent' => '', 'isp' => '', 'as_name' => '', 'asn' => ''];
        $cnSar = ['HK' => 'hongkong', 'MO' => 'macao', 'TW' => 'taiwan'];

        if (! $this->reader) {
            $this->reader = $this->maxMind();
        }
        if (! $readerData = $this->reader->get($ip)) {
            $data['country'] = 'PV';
            return $format === 'string' ? 'Unknown' : $data;
        }
        $data['country'] = strtoupper($readerData['country']);
        $data['continent'] = strtoupper($readerData['continent']);
        $data['as_name'] = $readerData['as_name'] . ' ' . $readerData['as_domain'];
        $data['asn'] = $readerData['asn'];
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $data['country'] === 'CN') {
            $cndata = $this->ip2region($ip);
            $data['province'] = $cndata['province'];
            $data['region'] = $cndata['region'];
        }
        if (in_array($data['country'], ['HK', 'MO', 'TW'])) {
            $data['province'] = $cnSar[$data['country']];
            $data['country'] = 'CN';
            $data['region'] = 'hkmotw';
        }
        $data['isp'] = LangUtils::getIsp($data['as_name'], $data['country']) ?: $data['isp'];
        return $format === 'string' ? $data['country'] . ' ' . $data['as_name'] : $data;
    }

    /**
     * MaxMind Reader.
     */
    public function maxMind(): ?Reader
    {
        try {
            $dbFile = storage_path() . '/components/ip/GeoLite2.mmdb';
            $dbContents = file_get_contents($dbFile, false);
            return new Reader($dbFile, $dbContents);
        } catch (\ErrorException $e) {
            logger('IpLocation')->error('Failed to init maxMind reader', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Ip2region Reader.
     *
     * @param string $ip IP address
     */
    public function ip2region($ip): array
    {
        $data = ['country' => 'CN', 'province' => '', 'region' => '', 'continent' => 'AS', 'isp' => ''];
        $regionData = (new Ip2region(logger()))->search($ip);
        $regionData = explode('-', $regionData);
        if (count($regionData) === 2) {
            $province = LangUtils::getEnName($regionData[0], 'province', 'cn');
            if (! empty($province)) {
                $data['province'] = $province;
                $data['region'] = LangUtils::getRegion($province);
            }
        }
        return $data;
    }

    /**
     * Get location name according locale.
     *
     * @param string $ip IP address
     * @param string $locale Locale, default is 'zh-CN'
     */
    public function getName($ip, $locale = 'zh-CN'): string
    {
        if (! $this->reader) {
            $this->reader = $this->maxMind();
        }
        if (! $readerData = $this->reader->get($ip)) {
            return $locale == 'zh-CN' ? '私有地址' : 'Private Address';
        }
        $country = strtoupper($readerData['country']);
        $country_name = $readerData['country_name'];
        $asn = $readerData['asn'];
        $as_name = $readerData['as_name'];
        if (in_array(strtolower($country_name), ['hongkong', 'hong kong', 'macao', 'taiwan'])) {
            $country_name = 'China/' . $country_name;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $locale == 'zh-CN' && in_array($country, ['CN'])) {
            $regionData = (new Ip2region(logger()))->search($ip);
            $reparr = ['北京-' => '', '上海-' => '', '天津-' => '', '重庆-' => '', '-' => '/', ':' => '/'];
            foreach ($reparr as $s => $r) {
                $regionData = str_replace($s, $r, $regionData);
            }
        } else {
            $regionData = $locale == 'zh-CN' ? LangUtils::getCnName($country, 'country') : $country_name;
        }
        return "{$regionData}/{$asn}/{$as_name}";
    }
}
