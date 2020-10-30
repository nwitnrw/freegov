<?php

declare(strict_types = 1);

namespace Drupal\degov_password_policy\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Class PasswordChangeService.
 *
 * @package Drupal\degov_password_policy\Service
 */
class PasswordChangeService {

  private const DAYS_UNTL_MESSAGE = 35;

  private const DAYS_UNTL_REDIRECT = 40;

  /**
   * The DateFormatterInterface.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * The TimeInterface.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * The EntityStorageInterface.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $userStorage;

  /**
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  private $userQuery;

  /**
   * PasswordChangeService constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    DateFormatterInterface $dateFormatter,
    TimeInterface $time,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->dateFormatter = $dateFormatter;
    $this->time = $time;
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->userQuery = $this->userStorage->getQuery();
  }

  /**
   * Handles expired passwords.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function expirePasswords(): void {
    $this->expireUsersByType('message');
    $this->expireUsersByType('redirect');
  }

  /**
   * Gets the current time.
   *
   * @return string
   *   The formatted current time.
   */
  public function getCurrentTime(): string {
    return $this->dateFormatter->format(
      $this->time->getRequestTime(),
      'custom',
      DateTimeItemInterface::DATETIME_STORAGE_FORMAT,
      DateTimeItemInterface::STORAGE_TIMEZONE
    );
  }

  /**
   * Expire users depending on expiration type.
   *
   * @param string $type
   *   Could be either 'message' or 'redirect'.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function expireUsersByType(string $type): void {
    $currentTime = $this->time->getRequestTime();
    $timestamp = $this->getStorageDateFormat(
      strtotime(
        '-' . ($type === 'message' ? self::DAYS_UNTL_MESSAGE : self::DAYS_UNTL_REDIRECT) . ' days',
        $currentTime
      )
    );
    $users = $this->getUnexpiredUsers($timestamp);

    foreach ($users as $user) {
      $user->set('field_password_expiration', $type);
      $user->save();
    }
  }

  /**
   * Get the given date in storage format.
   *
   * @param int $date
   *   The date as a UNIX timestamp.
   *
   * @return string
   *   The formatted date.
   */
  private function getStorageDateFormat(int $date): string {
    return $this->dateFormatter->format(
      $date,
      'custom',
      DateTimeItemInterface::DATETIME_STORAGE_FORMAT,
      DateTimeItemInterface::STORAGE_TIMEZONE
    );
  }

  /**
   * Get a list of users with unexpired passwords.
   *
   * @param string $expireDate
   *   The expiration date for passwords.
   *
   * @return \Drupal\user\UserInterface[]
   *   Array of unexpired users.
   */
  private function getUnexpiredUsers(string $expireDate): array {
    $query = $this->userQuery
      ->condition('status', 1)
      ->condition('field_last_password_reset', $expireDate, '<=')
      ->condition('uid', 0, '>');
    $userList = $query->execute();

    return $this->userStorage->loadMultiple($userList);
  }

}
