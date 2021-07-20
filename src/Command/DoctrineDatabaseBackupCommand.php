<?php

namespace App\Command;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Ifsnop\Mysqldump as IMysqldump;

class DoctrineDatabaseBackupCommand extends Command
{
    protected static $defaultName = 'doctrine:database:backup';
    protected static $defaultDescription = 'Make a backup of the database';
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $dump = new IMysqldump\Mysqldump(
                'mysql:'.implode(';', [
                    'host='.$this->params->get('database_host'),
                    'port='.$this->params->get('database_port'),
                    'dbname='.$this->params->get('database_name'),
                    'charset=UTF8'
                ]),
                $this->params->get('database_user'),
                $this->params->get('database_password'),
                [
                    'exclude-tables' => ['acl_classes', 'acl_entries', 'acl_object_identities', 'acl_object_identity_ancestors', 'acl_security_identities', 'ajax_chat_bans', 'ajax_chat_invitations', 'ajax_chat_messages', 'ajax_chat_online', 'reset_password_request',' session' ],
                    'compress' => IMysqldump\Mysqldump::GZIP
                ]
            );
            $dir = $this->params->get('kernel.project_dir').'/backup/';

            // generate dump
            $file = 'dump-'.date('Y-m-d').'.sql.gzip';
            $dump->start($dir.$file);
            $io->success('Done backup.');

            // delete -4 days backup
            $previous = 'dump-'.date('Y-m-d', strtotime('-4 days')).'.sql.gzip';
            if (file_exists($dir.$previous)) {
                unlink($dir.$previous);
            }

        } catch (\Exception $e) {
            $io->warning('Error during backup '.$e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
