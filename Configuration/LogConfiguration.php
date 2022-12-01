<?php
/*
 * This file is part of the Yipikai Log Bundle package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Yipikai\LogBundle\Configuration;

use Austral\ToolsBundle\Configuration\BaseConfiguration;

/**
 * Yipikai Log Bundle.
 * @author Matthieu Beurel <matthieu@yipikai.studio>
 * @final
 */
Class LogConfiguration extends BaseConfiguration
{

  /**
   * @var string|null
   */
  protected ?string $prefix = "log";

  /**
   * @var int|null
   */
  protected ?int $niveauMax = null;

}