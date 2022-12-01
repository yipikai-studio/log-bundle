<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yipikai\LogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use \Exception;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Yipikai Log Extension.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class YipikaiLogExtension extends Extension
{
  /**
   * {@inheritdoc}
   * @throws Exception
   */
  public function load(array $configs, ContainerBuilder $container)
  {
    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $defaultConfig = $configuration->getConfigDefault();
    $config = array_replace_recursive($defaultConfig, $config);
    $container->setParameter('yipikai_log', $config);

    $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('services.yaml');
    $loader->load('parameters.yaml');
  }

  /**
   * @param ContainerBuilder $container
   *
   * @throws Exception
   */
  public function prepend(ContainerBuilder $container)
  {
    if (interface_exists(MessageBusInterface::class)) {
      $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
      $loader->load('messenger.yaml');
    }
  }

  /**
   * @return string
   */
  public function getNamespace(): string
  {
    return 'https://yipikai.app/schema/dic/log';
  }

}
