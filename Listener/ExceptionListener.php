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

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Yipikai\LogBundle\Configuration\LogConfiguration;
use Yipikai\LogBundle\Event\LogEvent;
use Yipikai\LogBundle\Services\Log;

/**
 * Yipikai Exception Listener.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class ExceptionListener
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
   * @param ExceptionEvent $event
   *
   * @return void
   */
  public function execute(ExceptionEvent $event): void
  {
    $logEvent = new LogEvent();
    $logEvent->setType("error");
    $logEvent->setIsEnabled((bool) $this->logConfiguration->get('enabled.exception'));
    $this->dispatcher?->dispatch($logEvent, LogEvent::EVENT_YIPIKAI_LOG_ENABLED);
    if($logEvent->getIsEnabled()) {
      try {
        $this->log->sendError($event->getThrowable(), $event->getRequest());
      } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|\Exception $e) {
      }
    }
  }

}