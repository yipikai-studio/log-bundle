<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Austral <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yipikai\LogBundle\Services;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Yipikai\LogBundle\Configuration\LogConfiguration;
use Yipikai\LogBundle\Message\LogMessage;

/**
 * Yipikai Log.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 */
class Log
{

  const DOCTRINE_LOG_CREATE = "create";
  const DOCTRINE_LOG_EDIT = "edit";
  const DOCTRINE_LOG_REMOVE = "remove";

  const SEND_ERROR = "error";
  const SEND_DOCTRINE_LOG = "doctrine";

  /**
   * @var ContainerInterface
   */
  protected ContainerInterface $container;

  /**
   * @var LogConfiguration
   */
  protected LogConfiguration $logConfiguration;

  /**
   * @var MessageBusInterface|null
   */
  protected ?MessageBusInterface $bus = null;

  /** @var ConsoleCommandEvent|null */
  protected ?ConsoleCommandEvent $consoleCommandEvent = null;

  /**
   * @var array|mixed
   */
  protected array $excludes = array();

  /**
   * @param ContainerInterface $container
   * @param LogConfiguration $logConfiguration
   * @param MessageBusInterface|null $bus
   */
  public function __construct(ContainerInterface $container, LogConfiguration $logConfiguration, ?MessageBusInterface $bus)
  {
    $this->container = $container;
    $this->logConfiguration = $logConfiguration;
    $this->bus = $bus;
    $this->excludes = array_key_exists("excludes", $this->logConfiguration->allConfig()) ? $this->logConfiguration->allConfig()["excludes"] : array();
  }

  /**
   * @param ConsoleCommandEvent $consoleCommandEvent
   *
   * @return $this
   */
  public function setConsoleCommandEvent(ConsoleCommandEvent $consoleCommandEvent): Log
  {
    $this->consoleCommandEvent = $consoleCommandEvent;
    return $this;
  }

  /**
   * @param string $typeSend
   * @param Request|null $request
   * @param array $body
   *
   * @throws ClientExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws ServerExceptionInterface
   */
  protected function send(string $typeSend, ?Request $request = null, array $body = array())
  {
    if($typeSend === self::SEND_ERROR || $typeSend === self::SEND_DOCTRINE_LOG)
    {
      $dateNow = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
      $requestParameters["headers"] = array(
        "User-Agent"			      =>	"Yipikai-Log/1.0.0",
        "Accept" 					      =>	"*/*",
        "Content-Type"          =>  'application/json',
        "x-yipikai-timestamp"   =>  $dateNow->getTimestamp(),
        "x-yipikai-client-ip"   =>  $request ? $request->headers->get("x-real-ip", $request->getClientIp()) : "127.0.0.1",
        "x-yipikai-host"        =>  $request ? $request->getHost() : $this->logConfiguration->get('domain', "localhost"),
        "x-yipikai-token"       =>  $this->logConfiguration->get('token.public'),
        "x-yipikai-hash"        =>  self::generateToken(
          $request ? $request->getHost() : $this->logConfiguration->get('domain', "localhost"),
          $dateNow,
          $this->logConfiguration->get('token.private'),
          $this->logConfiguration->get('token.salt')
        )
      );

      $body = $this->addRequestParameters($request, $body);
      $body = $this->addUserParameters($body);
      $requestParameters["body"] = json_encode($body);

      $uri = rtrim($this->logConfiguration->get('uri'));
      if($this->bus && $this->logConfiguration->get('async'))
      {
        $this->bus->dispatch(new LogMessage(
            "{$uri}/{$typeSend}",
            "POST",
            $requestParameters
          )
        );
      }
      else
      {
        $this->sendRequest("{$uri}/{$typeSend}", "POST", $requestParameters);
      }
    }
  }

  /**
   * @param string $uri
   * @param string $method
   * @param array $requestParameters
   *
   * @return false|mixed
   * @throws ClientExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws ServerExceptionInterface
   */
  public function sendRequest(string $uri, string $method = "POST", array $requestParameters = array())
  {
    try {
      $httpClient = new NativeHttpClient();
      $response = $httpClient->request($method, $uri, $requestParameters);
      $responseObject = json_decode($response->getContent(false));
      if($responseObject->status === "success")
      {
        return $responseObject;
      }
    }  catch (TransportExceptionInterface|\Exception $e) {
    }
    return false;
  }

  /**
   * @param Request|null $request
   * @param array $body
   *
   * @return array
   */
  protected function addRequestParameters(?Request $request = null, array $body = array()): array
  {
    if(!$this->consoleCommandEvent)
    {
      $body["isCommand"] = false;
      if($request)
      {
        $body["request"] =  array(
          "content"         =>  $request->getContent(),
          "languages"       =>  $request->getLanguages(),
          "pathInfo"        =>  $request->getPathInfo(),
          "requestUri"      =>  $request->getRequestUri(),
          "method"          =>  $request->getMethod(),
          "local"           =>  $request->getLocale(),
          "defaultLocal"    =>  $request->getDefaultLocale(),
        );
        $body["parameters"] =  array(
          "query"             =>  (array) $request->query->getIterator(),
          "request"           =>  (array) $request->request->getIterator(),
          "attributes"        =>  (array) $request->attributes->getIterator(),
          "server"            =>  (array) $request->server->getIterator(),
          "header"            =>  (array) $request->headers->getIterator(),
          "files"             =>  (array) $request->files->getIterator(),
          "session"           =>  (array) $request->getSession()->getIterator(),
        );
      }
    }
    else
    {
      $body["isCommand"] = true;

      /** @var KernelInterface $kernel */
      $kernel = $this->consoleCommandEvent->getCommand()->getApplication()->getKernel();
      $body["command"] = array(
        "name"      =>  $this->consoleCommandEvent->getCommand()->getName(),
        "aliases"   =>  $this->consoleCommandEvent->getCommand()->getAliases(),
        "help"      =>  $this->consoleCommandEvent->getCommand()->getHelp(),
        "kernel"    =>  array(
          "env"           =>  $kernel->getEnvironment(),
          "isDebug"       =>  $kernel->isDebug(),
          "projectDir"    =>  $kernel->getProjectDir(),
        )
      );
      $body["commandInput"] = array(
        "options"   =>  $this->consoleCommandEvent->getInput()->getOptions(),
        "arguments"   =>  $this->consoleCommandEvent->getInput()->getArguments()
      );
    }

    return $body;
  }

  /**
   * @param array $body
   *
   * @return array
   */
  protected function addUserParameters(array $body = array()): array
  {
    $body["user"] = array(
      "roles"           =>  "",
      "authenticated"   =>  false,
      "values"            =>  array(
        "id"            =>  "",
        "username"      =>  "anon."
      )
    );

    if(!$this->consoleCommandEvent)
    {
      if($this->container->get('security.authorization_checker')->isGranted("IS_AUTHENTICATED"))
      {
        if($token = $this->container->get("security.token_storage")->getToken())
        {
          $body["user"] = array(
            "roles"           =>  $token->getRoleNames(),
            "authenticated"   =>  $this->container->get('security.authorization_checker')->isGranted("IS_AUTHENTICATED"),
          );
          if(($user = $token->getUser()) && ($values = $this->objectToArray($user)))
          {
            $body["user"]["values"] = $values;
          }
        }
      }
    }
    return $body;
  }


  /**
   * @param $object
   *
   * @return array
   */
  protected function objectToArray($object): array
  {
    $objectArray = array();
    try {
      if(is_object($object))
      {
        $objectClass = get_class($object);
        $exclude = false;
        $excludeFields = array();
        if(array_key_exists($objectClass, $this->excludes))
        {
          $exclude = array_key_exists("all", $this->excludes[$objectClass]) ? $this->excludes[$objectClass]["all"] : false;
          $excludeFields = array_key_exists("fields", $this->excludes[$objectClass]) ? $this->excludes[$objectClass]["fields"] : false;
        }
        if(!$exclude)
        {
          $reflectionClass = new \ReflectionClass($objectClass);
          foreach ($reflectionClass->getProperties() as $property) {
            if(in_array($property->getName(), $excludeFields))
            {
              $objectArray[$property->getName()] = "exclude";
            }
            else
            {
              $objectArray[$property->getName()] = null;
              try {
                $property->setAccessible(true);
                if($property->isInitialized($object)) {
                  $objectArray[$property->getName()] = $property->getValue($object);
                }
                $property->setAccessible(false);
              } catch(\Exception $e) {
              }
            }
          }
        }
      }
    } catch (\Exception $e) {

    }
    return $objectArray;
  }


  /**
   * @param $object
   * @param array $valuesChanged
   * @param string|null $doctineId
   * @param string $doctrineLogType
   *
   * @throws ClientExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws ServerExceptionInterface
   */
  public function sendDoctrineLog($object, array $valuesChanged = array(), string $doctineId = null, string $doctrineLogType = self::DOCTRINE_LOG_CREATE)
  {
    $body = array();
    $request = $this->container->get('request_stack')->getCurrentRequest();

    if($objectValues = $this->objectToArray($object))
    {
      $body["object"] = array(
        "classname"     =>  get_class($object),
        "values"        =>  $objectValues
      );
      $body["doctrine_log_type"] = $doctrineLogType;
      $body["doctrine_id"] = $doctineId;
      $body["valuesChanged"] = $valuesChanged;
      $this->send(self::SEND_DOCTRINE_LOG, $request, $body);
    }
  }

  /**
   * @param \Throwable $exception
   * @param ?Request $request
   *
   * @throws ClientExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws ServerExceptionInterface
   * @throws \Exception
   */
  public function sendError(\Throwable $exception, ?Request $request = null)
  {
    $body = array(
      "exception" => array(
        "code"            =>  $exception->getCode(),
        "message"         =>  $exception->getMessage(),
        "file"            =>  $exception->getFile(),
        "line"            =>  $exception->getLine(),
        "traceString"     =>  $exception->getTraceAsString(),
      ),
    );
    $this->send(self::SEND_ERROR, $request, $body);
  }

  /**
   * @param string $host
   * @param \DateTime $date
   * @param string $privateKey
   * @param string $salt
   *
   * @return string
   */
  public static function generateToken(string $host, \DateTime $date, string $privateKey, string $salt = "YOUR_SALT"): string
  {
    return hash("sha256", sprintf("%s_%s-%s_%s", $salt, $host, $date->format("Y-m-d_h:i:s"), $privateKey));
  }

}