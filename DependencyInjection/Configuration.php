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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Yipikai Log Configuration.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
class Configuration implements ConfigurationInterface
{

  /**
   * {@inheritdoc}
   */
  public function getConfigTreeBuilder(): TreeBuilder
  {
    $treeBuilder = new TreeBuilder('yipikai_log');
    $rootNode = $treeBuilder->getRootNode();
    $node = $rootNode->children();

    $node->scalarNode("uri")->end();
    $node->scalarNode("domain")->end();
    $node->booleanNode("async")->end();

    $node->arrayNode("enabled")
      ->children()
        ->booleanNode("exception")->isRequired()->end()
        ->booleanNode("doctrine")->isRequired()->end()
      ->end()
    ->end();

    $node->arrayNode("token")
      ->children()
        ->scalarNode("public")->isRequired()->end()
        ->scalarNode("private")->isRequired()->end()
        ->scalarNode("salt")->isRequired()->end()
      ->end()
    ->end();

    $node = $this->buildExcludeNode($node
      ->arrayNode('excludes')
      ->arrayPrototype()
    );
    $node->end()->end()->end();

    return $treeBuilder;
  }

  /**
   * @param ArrayNodeDefinition $node
   *
   * @return mixed
   */
  protected function buildExcludeNode(ArrayNodeDefinition $node)
  {
    $node = $node
      ->children()
        ->booleanNode("all")->defaultFalse()->end()
        ->arrayNode('fields')
          ->scalarPrototype()->end()
        ->end()
      ->end();
    return $node->end();
  }

  /**
   * @return array
   */
  public function getConfigDefault(): array
  {
    return array(
      "uri"                 =>  "",
      "domain"              =>  "",
      "enabled"             =>  array(
        "exception"           =>  false,
        "doctrine"            =>  false
      ),
      "async"               =>  false,
      "token"               =>  array(
        "public"              =>  "",
        "private"             =>  "",
        "salt"                =>  "YOUR_SALT"
      ),
    );
  }






}
