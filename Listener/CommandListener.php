<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yipikai\LogBundle\Listener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Yipikai\LogBundle\Configuration\LogConfiguration;
use Yipikai\LogBundle\Event\LogEvent;
use Yipikai\LogBundle\Services\Log;

/**
 * Yipikai Command Listener.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class CommandListener
{

  /**
   * @param Log $log
   * @param LogConfiguration $logConfiguration
   * @param EventDispatcherInterface|null $dispatcher
   */
  public function __construct(
    #[Autowire(service: "yipikai.log")] protected Log $log,
    #[Autowire(service: "yipikai.log.config")] protected LogConfiguration $logConfiguration,
    #[Autowire(service: "event_dispatcher")] protected ?EventDispatcherInterface $dispatcher)
  {
  }

  /**
   * @param ConsoleCommandEvent $event
   *
   * @return void
   */
  public function initialize(ConsoleCommandEvent $event): void
  {
    if($this->logConfiguration->get('enabled.doctrine') || $this->logConfiguration->get('enabled.exception')) {
      try {
        $this->log->setConsoleCommandEvent($event);
      } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|\Exception $e) {
      }
    }
  }

  /**
   * @param ConsoleErrorEvent $event
   *
   * @return void
   */
  public function error(ConsoleErrorEvent $event): void
  {
    $logEvent = new LogEvent();
    $logEvent->setType("error");
    $logEvent->setIsEnabled((bool) $this->logConfiguration->get('enabled.exception'));
    $this->dispatcher?->dispatch($logEvent, LogEvent::EVENT_YIPIKAI_LOG_ENABLED);
    if($logEvent->getIsEnabled()) {
      try {
        $this->log->sendError($event->getError());
      } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|\Exception $e) {
      }
    }
  }


}