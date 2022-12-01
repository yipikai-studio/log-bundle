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

/**
 * Austral Log Messenger.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class LogMessage
{
  /**
   * @var string
   */
  protected string $uri;

  /**
   * @var string
   */
  protected string $method;

  /**
   * @var array
   */
  protected array $requestParameters;

  /**
   * LogMessage constructor.
   *
   */
  public function __construct(string $uri, string $method = "POST", array $requestParameters = array())
  {
    $this->uri = $uri;
    $this->method = $method;
    $this->requestParameters = $requestParameters;
  }

  /**
   * @return string
   */
  public function getUri(): string
  {
    return $this->uri;
  }

  /**
   * @param string $uri
   *
   * @return LogMessage
   */
  public function setUri(string $uri): LogMessage
  {
    $this->uri = $uri;
    return $this;
  }

  /**
   * @return string
   */
  public function getMethod(): string
  {
    return $this->method;
  }

  /**
   * @param string $method
   *
   * @return LogMessage
   */
  public function setMethod(string $method): LogMessage
  {
    $this->method = $method;
    return $this;
  }

  /**
   * @return array
   */
  public function getRequestParameters(): array
  {
    return $this->requestParameters;
  }

  /**
   * @param array $requestParameters
   *
   * @return LogMessage
   */
  public function setRequestParameters(array $requestParameters): LogMessage
  {
    $this->requestParameters = $requestParameters;
    return $this;
  }

}