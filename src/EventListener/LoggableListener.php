<?php

namespace App\EventListener;

use App\Attribute\Loggable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LoggableListener
{
    private LoggerInterface $fileLogger;
    private LoggerInterface $dbLogger;
    private Security $security;

    public function __construct(
        #[Autowire(service: 'monolog.logger.file')] LoggerInterface $fileLogger,
        #[Autowire(service: 'monolog.logger.database')] LoggerInterface $dbLogger,
        Security $security
    ) {
        $this->fileLogger = $fileLogger;
        $this->dbLogger = $dbLogger;
        $this->security = $security;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        if (is_array($controller)) {
            [$controllerObject, $methodName] = $controller;
            $reflectionMethod = new \ReflectionMethod($controllerObject, $methodName);

            // Check for Loggable attribute
            $attributes = $reflectionMethod->getAttributes(Loggable::class);

            foreach ($attributes as $attribute) {
                /** @var Loggable $loggable */
                $loggable = $attribute->newInstance();
                $user = $this->security->getUser();

                $message = sprintf('Action: %s by User: %s', $loggable->action, $user?->getUserIdentifier() ?? 'guest');

                // Log to file
                $this->fileLogger->info($message, ['action' => $loggable->action]);

                // Log to database
                $this->dbLogger->info($message, ['action' => $loggable->action]);
            }
        }
    }
}
