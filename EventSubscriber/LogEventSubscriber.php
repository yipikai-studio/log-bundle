<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yipikai\LogBundle\EventSubscriber;

use Yipikai\LogBundle\Event\LogEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Yipikai Log EventSubscriber.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 */
class LogEventSubscriber implements EventSubscriberInterface
{

  /**
   * @return array[]
   */
  public static function getSubscribedEvents(): array
  {
    return [
      LogEvent::EVENT_YIPIKAI_LOG_ENABLED     =>  ["enabled", 1024],
    ];
  }

  /**
   * @param LogEvent $firewallEvent
   *
   * @return void
   */
  public function enabled(LogEvent $firewallEvent)
  {

  }

}