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

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
#[AsDoctrineListener(event: 'postPersist', connection: 'default')]
#[AsDoctrineListener(event: 'preUpdate', connection: 'default')]
#[AsDoctrineListener(event: 'postUpdate', connection: 'default')]
#[AsDoctrineListener(event: 'postRemove', connection: 'default')]
class DoctrineListener implements EventSubscriber
{

  /**
   * @var string
   */
  protected string $doctrineId;


  /**
   * @param Log $log
   * @param LogConfiguration $logConfiguration
   * @param EventDispatcherInterface|null $dispatcher
   * @throws \Exception
   */
  public function __construct(
    #[Autowire(service: "yipikai.log")] protected Log $log,
    #[Autowire(service: "yipikai.log.config")] protected LogConfiguration $logConfiguration,
    #[Autowire(service: "event_dispatcher")] protected ?EventDispatcherInterface $dispatcher)
  {
    $this->doctrineId = Uuid::uuid4()->toString();
  }

  /**
   * @return string[]
   */
  public function getSubscribedEvents(): array
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
  protected function sendDoctrineLog($object, string $type = Log::DOCTRINE_LOG_CREATE): void
  {
    $logEvent = new LogEvent();
    $logEvent->setType("doctrine.{$type}");
    $logEvent->setIsEnabled($this->logConfiguration->get('enabled.doctrine'));
    $this->dispatcher?->dispatch($logEvent, LogEvent::EVENT_YIPIKAI_LOG_ENABLED);
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