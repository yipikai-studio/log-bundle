<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yipikai\LogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Yipikai Messenger Pass.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class MessengerPass implements CompilerPassInterface
{
  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container)
  {
    if($container->hasDefinition('messenger.senders_locator'))
    {
      $definition = $container->findDefinition('messenger.senders_locator');
      $resolveMessengers = $container->getParameter("yipikai.resolve.messenger.routing.log");

      $arguments = $definition->getArguments();
      foreach ($arguments as $key => $argument)
      {
        if(is_array($argument))
        {
          $argument = array_merge($argument, $resolveMessengers);
          $definition->replaceArgument($key, $argument);
        }
      }
    }
  }
}