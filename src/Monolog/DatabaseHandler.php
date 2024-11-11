<?php

namespace App\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\LogEntry;
use Monolog\LogRecord;

class DatabaseHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, $level = \Monolog\Logger::INFO, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->entityManager = $entityManager;
    }

    protected function write(LogRecord $record): void
    {
        $logEntry = new LogEntry();
        $logEntry->setChannel($record['channel']);
        $logEntry->setLevel($record['level_name']);
        $logEntry->setMessage($record['message']);
        $logEntry->setContext($record['context']);
        $logEntry->setCreatedAt(new \DateTime());

        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();
    }
}
