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

namespace App\Adm\Aspect;

use Composer\InstalledVersions;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Mine\Command\InstallProjectCommand;
use Mine\Exception\NormalStatusException;

use function Hyperf\Translation\trans;

/**
 * Aspect for InstallProjectCommand.
 */
#[Aspect]
class InstallProjectCommandAspect extends AbstractAspect
{
    public array $classes = [
        'Mine\Command\InstallProjectCommand',
    ];

    public array $annotations = [
    ];

    /**
     * process.
     *
     * @throws Exception
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        try {
            $methodName = $proceedingJoinPoint->methodName;
            if ($methodName === 'installLocalModule') {
                return $this->installLocalModule($proceedingJoinPoint);
            }
            if ($methodName === 'setOthers') {
                return $this->setOthers($proceedingJoinPoint);
            }
            if ($methodName === 'initUserData') {
                return null;
            }
            if ($methodName === 'finish') {
                return $this->finish();
            }
            return $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            logger('SystemUserServiceAspect')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            throw new NormalStatusException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * install Warning Messages.
     */
    public function installLocalModule(ProceedingJoinPoint $proceedingJoinPoint): null
    {
        $default = isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] === '-n' ? true : false;
        $command = container()->get(InstallProjectCommand::class);
        $install = $command->confirm(trans('adm.reinstall_warning'), $default);
        if (! $install) {
            exit;
        }
        return $proceedingJoinPoint->process();
    }

    /**
     * Set others.
     */
    public function setOthers(ProceedingJoinPoint $proceedingJoinPoint): null
    {
        $command = container()->get(InstallProjectCommand::class);
        $command->line(PHP_EOL . ' MineAdmin set others items... ...' . PHP_EOL, 'comment');
        $command->call('mine:update');
        $command->call('mine:jwt-gen', ['--jwtSecret' => 'JWT_SECRET']);
        $command->call('mine:jwt-gen', ['--jwtSecret' => 'JWT_API_SECRET']);

        if (! file_exists(BASE_PATH . '/config/autoload/mineadmin.php')) {
            $command->call('vendor:publish', ['package' => 'xmo/mine']);
        }

        return null;
    }

    protected function finish()
    {
        $command = container()->get(InstallProjectCommand::class);

        $projectBasePath = realpath(
            dirname(
                InstalledVersions::getInstallPath('xmo/mine-core'),
                2
            )
        );

        $welcome = '';
        if (
            env('WELCOME_FILE')
            && file_exists(
                $projectBasePath
                . DIRECTORY_SEPARATOR
                . env('WELCOME_FILE')
            )
        ) {
            $welcome = file_get_contents($projectBasePath
                . DIRECTORY_SEPARATOR
                . env('WELCOME_FILE'));
        }
        $welcome = str_replace([
            '%y',
        ], [
            date('Y'),
        ], $welcome);

        $command->line(PHP_EOL . sprintf('%s
Admin.IM Version: %s
Default username: admin
Default password: %s' . PHP_EOL, $welcome . PHP_EOL, env('APP_VERSION'), getenv('DEFAULT_ADMIN_PASSWORD')), 'comment');
    }
}
