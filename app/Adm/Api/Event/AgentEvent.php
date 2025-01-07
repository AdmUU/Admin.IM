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

namespace App\Adm\Api\Event;

use Hyperf\SocketIOServer\Socket;

/**
 * Event for Socket Agent.
 */
class AgentEvent
{
    protected Socket $socket;

    protected ?array $agentData;

    public function __construct(Socket $socket, ?array $agentData)
    {
        $this->socket = $socket;
        $this->agentData = $agentData;
    }

    /**
     * Get Agent socket.
     */
    public function getSocket(): Socket
    {
        return $this->socket;
    }

    /**
     * Get Agent data.
     */
    public function getAgentData(): ?array
    {
        return $this->agentData;
    }
}
