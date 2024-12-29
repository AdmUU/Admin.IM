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

namespace App\Adm\Api\Listener;

use App\Adm\Api\Event\ApiFinish;
use App\Adm\Utils\NetworkUtils;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\Logger;
use Hyperf\Logger\LoggerFactory;
use Mine\MineRequest;

/**
 * Save API access logs.
 */
#[Listener]
class AdmApiLogListener implements ListenerInterface
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = container()->get(LoggerFactory::class)->get('admApi');
    }

    public function listen(): array
    {
        return [
            ApiFinish::class,
        ];
    }

    /**
     * Process the Event.
     */
    public function process(object $event): void
    {
        $data = $event->getApiData();
        $request = container()->get(MineRequest::class);

        if (empty($data)) {
            alog('The API data is empty:' . json_encode($request), 'error', 'Api Access Log');
        } else {
            $reqData = $request->getParsedBody();
            unset($reqData['apiData']);
            $response = $event->getResult();

            $app_id = $data['id'] ?? 0;
            $app_id = is_numeric($app_id) ? $app_id : 0;

            $queryParams = $request->getQueryParams();
            $responseCode = $response->getStatusCode();

            $ip = NetworkUtils::getClientIp($request);
            Coroutine::create(function () use ($app_id, $data, $queryParams, $responseCode, $ip) {
                alog(sprintf('%s %s %s %s', $app_id, $data['access_name'], json_encode($queryParams), $responseCode), 'info', 'Api Access Log', $ip);
            });
        }
    }
}
