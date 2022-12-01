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

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Yipikai\LogBundle\Configuration\LogConfiguration;
use Yipikai\LogBundle\Event\LogEvent;
use Yipikai\LogBundle\Services\Log;

/**
 * Yipikai Doctrine Listener.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class DoctrineListener implements EventSubscriber
{

  /**
   * @var Log
   */
  protected Log $log;

  /**
   * @var LogConfiguration
   */
  protected LogConfiguration $logConfiguration;

  /**
   * @var string
   */
  protected string $doctrineId;

  /**
   * @var EventDispatcherInterface|null
   */
  protected ?EventDispatcherInterface $dispatcher;

  /**
   * @param Log $log
   * @param LogConfiguration $logConfiguration
   * @param EventDispatcherInterface|null $dispatcher
   *
   * @throws \Exception
   */
  public function __construct(Log $log, LogConfiguration $logConfiguration, ?EventDispatcherInterface $dispatcher)
  {
    $this->log = $log;
    $this->logConfiguration = $logConfiguration;
    $this->doctrineId = Uuid::uuid4()->toString();
    $this->dispatcher = $dispatcher;
  }

  /**
   * @return string[]
   */
  public function getSubscribedEvents()
  {
    return array(
      Events::postPersist,
      Events::preUpdate,
      Events::postUpdate,
      Events::postRemove
    );
  }

  /**
   * @var array
   */
  protected array $valuesChanged = array();

  /**
   * @param PreUpdateEventArgs $args
   */
  public function preUpdate(PreUpdateEventArgs $args): void
  {
    foreach($args->getEntityChangeSet() as $keyChange => $values)
    {
      $this->valuesChanged[$keyChange] = array(
        "old"   =>  $args->getOldValue($keyChange),
        "new"   =>  $args->getNewValue($keyChange),
      );
    }
  }

  /**
   * @param LifecycleEventArgs $args
   */
  public function postPersist(LifecycleEventArgs $args): void
  {
    $this->sendDoctrineLog($args->getObject(), Log::DOCTRINE_LOG_CREATE);
  }

  /**
   * @param LifecycleEventArgs $args
   */
  public function postUpdate(LifecycleEventArgs $args): void
  {
    $this->sendDoctrineLog($args->getObject(), Log::DOCTRINE_LOG_EDIT);
  }

  /**
   * @param LifecycleEventArgs $args
   */
  public function postRemove(LifecycleEventArgs $args): void
  {
    $this->sendDoctrineLog($args->getObject(), Log::DOCTRINE_LOG_REMOVE);
  }

  /**
   * @param $object
   * @param string $type
   *
   * @return void
   */
  protected function sendDoctrineLog($object, string $type = Log::DOCTRINE_LOG_CREATE)
  {
    $logEvent = new LogEvent();
    $logEvent->setType("doctrine.{$type}");
    $logEvent->setIsEnabled($this->logConfiguration->get('enabled.doctrine'));
    if($this->dispatcher)
    {
      $this->dispatcher->dispatch($logEvent, LogEvent::EVENT_YIPIKAI_LOG_ENABLED);
    }
    if($logEvent->getIsEnabled()) {
      try {
        $this->log->sendDoctrineLog($object, $this->valuesChanged, $this->doctrineId, $type);
      } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
      }
    }
  }



  /**
   * @param EventArgs $args
   *
   * @return EventArgs
   */
  protected function getEventAdapter(EventArgs $args): EventArgs
  {
    return $args;
  }

  /**
   * @return string
   */
  protected function getNamespace(): string
  {
    return __NAMESPACE__;
  }
}