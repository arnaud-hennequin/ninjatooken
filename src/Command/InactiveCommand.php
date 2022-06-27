<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InactiveCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('inactive:get')
            ->setDescription('Récupère les joueurs inactifs')
        ;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('---start');

        if (!$handle = fopen(dirname(__FILE__).'/../../backup/users.csv', 'w')) {
            $io->writeln('<error>Can\'t write to file</error>');

            return Command::FAILURE;
        }

        $request = 'SELECT `nt_user`.`id`, `nt_user`.`username`, `nt_user`.`email`, `nt_user`.`auto_login`, `nt_user`.`locale`, `nt_ninja`.`experience` FROM `nt_user` INNER JOIN `nt_ninja` ON `nt_user`.`id` = `nt_ninja`.`user_id` WHERE `nt_user`.`id`=10 OR (`nt_ninja`.`classe` != "" AND `nt_user`.`updated_at` < DATE_SUB(curdate(), INTERVAL 2 MONTH) AND `nt_user`.`email_canonical` NOT LIKE "%yopmail%") ORDER BY `nt_ninja`.`experience` DESC';

        $stmt = $this->em->getConnection()->prepare($request);
        $result = $stmt->executeQuery();

        foreach ($result->fetchAllAssociative() as $row) {
            try {
                if (empty($row['locale'])) {
                    $locale = 'fr';
                } else {
                    $locale = $row['locale'];
                }

                $fields = [
                    $row['id'],
                    $row['email'],
                    $row['username'],
                    $locale,
                    $row['experience'],
                    $row['auto_login'],
                ];
                if (false === fputcsv($handle, $fields)) {
                    $io->writeln('<error>Can\'t write to file</error>');
                }
            } catch (\Exception $e) {
                $io->writeln('<error>'.$e->getMessage().'</error>');
            }
        }

        fclose($handle);

        return Command::SUCCESS;
    }
}
