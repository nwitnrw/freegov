<?php

namespace Drupal\degov_demo_content\Generator;

/**
 * Interface GeneratorInterface.
 *
 * @package Drupal\degov_demo_content\Generator
 */
interface GeneratorInterface {

  /**
   * Deletes, then regenerates demo content.
   */
  public function resetContent(): void;

  /**
   * Generates content from a definitions file.
   */
  public function generateContent(): void;

  /**
   * Deletes the generated content.
   */
  public function deleteContent(): void;

}
