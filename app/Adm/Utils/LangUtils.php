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

use App\Adm\Service\AdmSystemDictDataService;

/**
 * Language utils.
 */
class LangUtils
{
    public const WORLD_COUNTRY = [
        'AD' => '安道尔',
        'AE' => '阿拉伯联合酋长国',
        'AF' => '阿富汗',
        'AG' => '安提瓜和巴布达',
        'AI' => '安圭拉',
        'AL' => '阿尔巴尼亚',
        'AM' => '亚美尼亚',
        'AO' => '安哥拉',
        'AQ' => '南极洲',
        'AR' => '阿根廷',
        'AS' => '美属萨摩亚',
        'AT' => '奥地利',
        'AU' => '澳大利亚',
        'AW' => '阿鲁巴',
        'AX' => '奥兰群岛',
        'AZ' => '阿塞拜疆',
        'BA' => '波斯尼亚和黑塞哥维那',
        'BB' => '巴巴多斯',
        'BD' => '孟加拉国',
        'BE' => '比利时',
        'BF' => '布基纳法索',
        'BG' => '保加利亚',
        'BH' => '巴林',
        'BI' => '布隆迪',
        'BJ' => '贝宁',
        'BL' => '圣巴泰勒米',
        'BM' => '百慕大',
        'BN' => '文莱',
        'BO' => '玻利维亚',
        'BQ' => '荷兰加勒比区',
        'BR' => '巴西',
        'BS' => '巴哈马',
        'BT' => '不丹',
        'BV' => '布韦岛',
        'BW' => '博茨瓦纳',
        'BY' => '白俄罗斯',
        'BZ' => '伯利兹',
        'CA' => '加拿大',
        'CC' => '科科斯（基林）群岛',
        'CD' => '刚果民主共和国',
        'CF' => '中非共和国',
        'CG' => '刚果共和国',
        'CH' => '瑞士',
        'CI' => '科特迪瓦',
        'CK' => '库克群岛',
        'CL' => '智利',
        'CM' => '喀麦隆',
        'CN' => '中国',
        'CO' => '哥伦比亚',
        'CR' => '哥斯达黎加',
        'CU' => '古巴',
        'CV' => '佛得角',
        'CW' => '库拉索',
        'CX' => '圣诞岛',
        'CY' => '塞浦路斯',
        'CZ' => '捷克',
        'DE' => '德国',
        'DJ' => '吉布提',
        'DK' => '丹麦',
        'DM' => '多米尼克',
        'DO' => '多米尼加共和国',
        'DZ' => '阿尔及利亚',
        'EC' => '厄瓜多尔',
        'EE' => '爱沙尼亚',
        'EG' => '埃及',
        'EH' => '西撒哈拉',
        'ER' => '厄立特里亚',
        'ES' => '西班牙',
        'ET' => '埃塞俄比亚',
        'FI' => '芬兰',
        'FJ' => '斐济',
        'FK' => '福克兰群岛',
        'FM' => '密克罗尼西亚联邦',
        'FO' => '法罗群岛',
        'FR' => '法国',
        'GA' => '加蓬',
        'GB' => '英国',
        'GD' => '格林纳达',
        'GE' => '格鲁吉亚',
        'GF' => '法属圭亚那',
        'GG' => '根西岛',
        'GH' => '加纳',
        'GI' => '直布罗陀',
        'GL' => '格陵兰',
        'GM' => '冈比亚',
        'GN' => '几内亚',
        'GP' => '瓜德罗普',
        'GQ' => '赤道几内亚',
        'GR' => '希腊',
        'GS' => '南乔治亚和南桑威奇群岛',
        'GT' => '危地马拉',
        'GU' => '关岛',
        'GW' => '几内亚比绍',
        'GY' => '圭亚那',
        'HK' => '中国香港',
        'HM' => '赫德岛和麦克唐纳群岛',
        'HN' => '洪都拉斯',
        'HR' => '克罗地亚',
        'HT' => '海地',
        'HU' => '匈牙利',
        'ID' => '印度尼西亚',
        'IE' => '爱尔兰',
        'IL' => '以色列',
        'IM' => '马恩岛',
        'IN' => '印度',
        'IO' => '英属印度洋领地',
        'IQ' => '伊拉克',
        'IR' => '伊朗',
        'IS' => '冰岛',
        'IT' => '意大利',
        'JE' => '泽西岛',
        'JM' => '牙买加',
        'JO' => '约旦',
        'JP' => '日本',
        'KE' => '肯尼亚',
        'KG' => '吉尔吉斯斯坦',
        'KH' => '柬埔寨',
        'KI' => '基里巴斯',
        'KM' => '科摩罗',
        'KN' => '圣基茨和尼维斯',
        'KP' => '朝鲜',
        'KR' => '韩国',
        'KW' => '科威特',
        'KY' => '开曼群岛',
        'KZ' => '哈萨克斯坦',
        'LA' => '老挝',
        'LB' => '黎巴嫩',
        'LC' => '圣卢西亚',
        'LI' => '列支敦士登',
        'LK' => '斯里兰卡',
        'LR' => '利比里亚',
        'LS' => '莱索托',
        'LT' => '立陶宛',
        'LU' => '卢森堡',
        'LV' => '拉脱维亚',
        'LY' => '利比亚',
        'MA' => '摩洛哥',
        'MC' => '摩纳哥',
        'MD' => '摩尔多瓦',
        'ME' => '黑山',
        'MF' => '法属圣马丁',
        'MG' => '马达加斯加',
        'MH' => '马绍尔群岛',
        'MK' => '北马其顿',
        'ML' => '马里',
        'MM' => '缅甸',
        'MN' => '蒙古',
        'MO' => '中国澳门',
        'MP' => '北马里亚纳群岛',
        'MQ' => '马提尼克',
        'MR' => '毛里塔尼亚',
        'MS' => '蒙特塞拉特',
        'MT' => '马耳他',
        'MU' => '毛里求斯',
        'MV' => '马尔代夫',
        'MW' => '马拉维',
        'MX' => '墨西哥',
        'MY' => '马来西亚',
        'MZ' => '莫桑比克',
        'NA' => '纳米比亚',
        'NC' => '新喀里多尼亚',
        'NE' => '尼日尔',
        'NF' => '诺福克岛',
        'NG' => '尼日利亚',
        'NI' => '尼加拉瓜',
        'NL' => '荷兰',
        'NO' => '挪威',
        'NP' => '尼泊尔',
        'NR' => '瑙鲁',
        'NU' => '纽埃',
        'NZ' => '新西兰',
        'OM' => '阿曼',
        'PA' => '巴拿马',
        'PE' => '秘鲁',
        'PF' => '法属波利尼西亚',
        'PG' => '巴布亚新几内亚',
        'PH' => '菲律宾',
        'PK' => '巴基斯坦',
        'PL' => '波兰',
        'PM' => '圣皮埃尔和密克隆',
        'PN' => '皮特凯恩群岛',
        'PR' => '波多黎各',
        'PS' => '巴勒斯坦',
        'PT' => '葡萄牙',
        'PV' => '私有地址',
        'PW' => '帕劳',
        'PY' => '巴拉圭',
        'QA' => '卡塔尔',
        'RE' => '留尼汪',
        'RO' => '罗马尼亚',
        'RS' => '塞尔维亚',
        'RU' => '俄罗斯',
        'RW' => '卢旺达',
        'SA' => '沙特阿拉伯',
        'SB' => '所罗门群岛',
        'SC' => '塞舌尔',
        'SD' => '苏丹',
        'SE' => '瑞典',
        'SG' => '新加坡',
        'SH' => '圣赫勒拿',
        'SI' => '斯洛文尼亚',
        'SJ' => '斯瓦尔巴和扬马延',
        'SK' => '斯洛伐克',
        'SL' => '塞拉利昂',
        'SM' => '圣马力诺',
        'SN' => '塞内加尔',
        'SO' => '索马里',
        'SR' => '苏里南',
        'SS' => '南苏丹',
        'ST' => '圣多美和普林西比',
        'SV' => '萨尔瓦多',
        'SX' => '荷属圣马丁',
        'SY' => '叙利亚',
        'SZ' => '斯威士兰',
        'TC' => '特克斯和凯科斯群岛',
        'TD' => '乍得',
        'TF' => '法属南部领地',
        'TG' => '多哥',
        'TH' => '泰国',
        'TJ' => '塔吉克斯坦',
        'TK' => '托克劳',
        'TL' => '东帝汶',
        'TM' => '土库曼斯坦',
        'TN' => '突尼斯',
        'TO' => '汤加',
        'TR' => '土耳其',
        'TT' => '特立尼达和多巴哥',
        'TV' => '图瓦卢',
        'TW' => '中国台湾',
        'TZ' => '坦桑尼亚',
        'UA' => '乌克兰',
        'UG' => '乌干达',
        'UM' => '美国本土外小岛屿',
        'US' => '美国',
        'UY' => '乌拉圭',
        'UZ' => '乌兹别克斯坦',
        'VA' => '梵蒂冈',
        'VC' => '圣文森特和格林纳丁斯',
        'VE' => '委内瑞拉',
        'VG' => '英属维尔京群岛',
        'VI' => '美属维尔京群岛',
        'VN' => '越南',
        'VU' => '瓦努阿图',
        'WF' => '瓦利斯和富图纳',
        'WS' => '萨摩亚',
        'YE' => '也门',
        'YT' => '马约特',
        'ZA' => '南非',
        'ZM' => '赞比亚',
        'ZW' => '津巴布韦',
    ];

    public const CN_PROVINCE = [
        'beijing' => '北京',
        'tianjin' => '天津',
        'liaoning' => '辽宁',
        'jilin' => '吉林',
        'heilongjiang' => '黑龙江',
        'shanghai' => '上海',
        'jiangsu' => '江苏',
        'zhejiang' => '浙江',
        'anhui' => '安徽',
        'fujian' => '福建',
        'jiangxi' => '江西',
        'shandong' => '山东',
        'shanxi' => '山西',
        'hebei' => '河北',
        'henan' => '河南',
        'hubei' => '湖北',
        'hunan' => '湖南',
        'guangdong' => '广东',
        'guangxi' => '广西',
        'hainan' => '海南',
        'chongqing' => '重庆',
        'sichuan' => '四川',
        'guizhou' => '贵州',
        'yunnan' => '云南',
        'tibet' => '西藏',
        'shaanxi' => '陕西',
        'gansu' => '甘肃',
        'qinghai' => '青海',
        'ningxia' => '宁夏',
        'mongol' => '内蒙古',
        'xinjiang' => '新疆',
        'hongkong' => '香港',
        'macau' => '澳门',
        'taiwan' => '台湾',
    ];

    public const US_PROVINCE = [
        'alabama' => '阿拉巴马州',
        'alaska' => '阿拉斯加州',
        'arizona' => '亚利桑那州',
        'arkansas' => '阿肯色州',
        'california' => '加利福尼亚州',
        'colorado' => '科罗拉多州',
        'connecticut' => '康涅狄格州',
        'delaware' => '特拉华州',
        'florida' => '佛罗里达州',
        'georgia' => '佐治亚州',
        'hawaii' => '夏威夷州',
        'idaho' => '爱达荷州',
        'illinois' => '伊利诺伊州',
        'indiana' => '印第安纳州',
        'iowa' => '艾奥瓦州',
        'kansas' => '堪萨斯州',
        'kentucky' => '肯塔基州',
        'louisiana' => '路易斯安那州',
        'maine' => '缅因州',
        'maryland' => '马里兰州',
        'massachusetts' => '麻萨诸塞州',
        'michigan' => '密歇根州',
        'minnesota' => '明尼苏达州',
        'mississippi' => '密西西比州',
        'missouri' => '密苏里州',
        'montana' => '蒙大拿州',
        'nebraska' => '内布拉斯加州',
        'nevada' => '内华达州',
        'new hampshire' => '新罕布什尔州',
        'new jersey' => '新泽西州',
        'new mexico' => '新墨西哥州',
        'new york' => '纽约州',
        'north carolina' => '北卡罗来纳州',
        'north dakota' => '北达科他州',
        'ohio' => '俄亥俄州',
        'oklahoma' => '俄克拉何马州',
        'oregon' => '俄勒冈州',
        'pennsylvania' => '宾夕法尼亚州',
        'rhode island' => '罗得岛州',
        'south carolina' => '南卡罗来纳州',
        'south dakota' => '南达科他州',
        'tennessee' => '田纳西州',
        'texas' => '得克萨斯州',
        'utah' => '犹他州',
        'vermont' => '佛蒙特州',
        'virginia' => '弗吉尼亚州',
        'washington' => '华盛顿州',
        'washington dc' => '华盛顿特区',
        'district of columbia' => '华盛顿特区',
        'west virginia' => '西弗吉尼亚州',
        'wisconsin' => '威斯康星州',
        'wyoming' => '怀俄明州',
    ];

    public const CN_REGION = [
        'north' => ['beijing', 'tianjin', 'hebei', 'shanxi', 'mongol'],
        'northeast' => ['liaoning', 'jilin', 'heilongjiang'],
        'east' => ['shanghai', 'jiangsu', 'zhejiang', 'anhui', 'fujian', 'jiangxi', 'shandong'],
        'central' => ['henan', 'hubei', 'hunan'],
        'south' => ['guangdong', 'guangxi', 'hainan'],
        'southwest' => ['chongqing', 'sichuan', 'guizhou', 'yunnan', 'tibet'],
        'northwest' => ['shaanxi', 'gansu', 'qinghai', 'ningxia', 'xinjiang'],
        'hkmotw' => ['hongkong', 'macau', 'taiwan'],
    ];

    /**
     * Get the English name of the country, province, or region.
     */
    public static function getEnName(string $name, string $type = 'province', string $country = 'CN'): string
    {
        $enName = '';
        switch ($type) {
            case 'country':
                $dictService = container()->get(AdmSystemDictDataService::class);
                $countries = $dictService->getCountriesList();
                if (array_key_exists($name, $countries)) {
                    $enName = $countries[$name];
                }
                break;
            case 'province':
                $arr = self::{strtoupper($country) . '_PROVINCE'};
                if (is_array($arr)) {
                    $enName = self::searchLangKey($arr, $name);
                }
                break;
            case 'region':
                break;
            default:
                return '';
        }
        return $enName;
    }

    /**
     * Get the Chinese name of the country, province, or region.
     */
    public static function getCnName(string $name, string $type = 'province', string $country = 'CN'): string
    {
        $cnName = $name;
        switch ($type) {
            case 'country':
                $arr = self::WORLD_COUNTRY;
                break;
            case 'province':
                $arr = self::{strtoupper($country) . '_PROVINCE'};
                break;
            default:
                return '';
        }
        if (is_array($arr) && array_key_exists($name, $arr)) {
            $cnName = $arr[$name];
        }
        return $cnName;
    }

    /**
     * Get the region in China by the province.
     */
    public static function getRegion(string $name, string $country = 'CN'): string
    {
        $region = '';
        $arr = self::{strtoupper($country) . '_REGION'};
        if (isset($arr)) {
            foreach ($arr as $key => $value) {
                if (array_search($name, $value) !== false) {
                    $region = $key;
                    break;
                }
            }
        }
        return $region;
    }

    /**
     * Get the ISP by name.
     */
    public static function getIsp(string $name, string $country): string
    {
        $dictService = container()->get(AdmSystemDictDataService::class);
        $isp = $dictService->getIspList();
        foreach ($isp as $key => $value) {
            foreach ($value as $v) {
                if (str_contains(strtolower($name), $v)) {
                    return $key;
                }
            }
        }
        if ($country === 'CN') {
            $region = 'cn';
        } else {
            $region = 'os';
        }
        return $region;
    }

    /**
     * Search language key.
     */
    private static function searchLangKey(array $arr, string $name): string
    {
        $enName = '';
        foreach ($arr as $key => $value) {
            if (strpos($name, $value) !== false) {
                $enName = $key;
                break;
            }
        }
        return $enName;
    }
}
