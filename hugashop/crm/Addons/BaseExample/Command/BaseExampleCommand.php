<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 * 
 * 
 * 1. Запуск комманды вручную
 * php bin/console cron:agents
 * 
 * Запуск по cron (каждую 1 минуту):
 * *\/5 * * * * /usr/bin/php /var/www/hugashop/bin/console cron:agents >> /var/www/hugashop/var/log/cron_agents.log 2>&1
 * 
 */

namespace HugaShop\Addons\BaseExample\Command;

use Symfony\Component\Lock\LockFactory;
use HugaShop\Addons\BaseExample\BaseExample;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'cron:agents', description: 'Run Cron agents.')]
final class BaseExampleCommand extends Command
{

    /**
     * Execute command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        // 1) Блокируем параллельные запуски (файл-лок в /tmp)
        $store   = new FlockStore(sys_get_temp_dir());
        $factory = new LockFactory($store);
        $lock    = $factory->createLock('hugashop_addon_cron_agent_lock', 300); // 300 сек TTL

        if (!$lock->acquire()) {
            $io->warning('Another "cron:agents" process is already running. Exit.');
            return Command::FAILURE;
        }

        try {

            // 2) Проверка включения аддона и настройки
            if (!BaseExample::isEnabled()) {
                $io->note('CronAgent addon is disabled or not configured for cron.');
                return Command::FAILURE;
            }

            // 3) Запуск агентов
            $io->text('Running agents...');

            // Do somthing ....

            $io->success('Agents executed successfully.');
            return Command::SUCCESS;
        } catch (\Throwable $e) {

            // 4) Чёткий вывод ошибки для логов cron
            $io->error(sprintf(
                'Cron agents failed: %s at %s:%d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            // Можно дополнительно логировать stack trace в ваш логгер
            return Command::FAILURE;
        } finally {

            // 5) Гарантированно освобождаем лок
            if ($lock->isAcquired()) {
                $lock->release();
            }
        }
    }
}
