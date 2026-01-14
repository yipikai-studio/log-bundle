<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Yipikai\LogBundle\Message;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Yipikai\LogBundle\Services\Log;

/**
 * Austral Log MessagerHandler.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class LogMessageHandler implements MessageHandlerInterface
{

  /**
   * LogMessageHandler constructor.
   */
  public function __construct(
    #[Autowire(service: "yipikai.log")] protected Log $log
  )
  {
  }

  /**
   * @param LogMessage $logMessage
   */
  public function __invoke(LogMessage $logMessage): void
  {
    try {
      $this->log->sendRequest($logMessage->getUri(), $logMessage->getMethod(), $logMessage->getRequestParameters());
    } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
    }
  }

}