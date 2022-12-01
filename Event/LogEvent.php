<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yipikai\LogBundle\Event;

/**
 * Austral Log Event.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class LogEvent
{

  const EVENT_YIPIKAI_LOG_ENABLED = "yipikai.event.log.enabled";

  /**
   * @var string|null
   */
  protected ?string $type = null;

  /**
   * @var bool
   */
  protected bool $isEnabled = false;

  public function __construct()
  {

  }

  /**
   * @return string|null
   */
  public function getType(): ?string
  {
    return $this->type;
  }

  /**
   * @param string|null $type
   *
   * @return LogEvent
   */
  public function setType(?string $type): LogEvent
  {
    $this->type = $type;
    return $this;
  }

  /**
   * @return bool
   */
  public function getIsEnabled(): bool
  {
    return $this->isEnabled;
  }

  /**
   * @param bool $isEnabled
   *
   * @return LogEvent
   */
  public function setIsEnabled(bool $isEnabled): LogEvent
  {
    $this->isEnabled = $isEnabled;
    return $this;
  }

}