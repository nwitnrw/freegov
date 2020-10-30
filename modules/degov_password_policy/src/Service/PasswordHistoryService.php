<?php

declare(strict_types = 1);

namespace Drupal\degov_password_policy\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Session\AccountInterface;
use function array_pop;
use function count;

/**
 * Class PasswordHistoryService.
 *
 * @package Drupal\degov_password_policy\Service
 */
class PasswordHistoryService {

  public const TABLE = 'degov_password_history';

  /**
   * Number of password history entries to save for a user.
   */
  public const MAX_HISTORY_ENTRIES = 8;

  /**
   * The EntityStorageInterface.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $userStore;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The TimeInterface.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * The PasswordInterface.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  private $passwordService;

  /**
   * PasswordHistoryService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManagerInterface.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The TimeInterface.
   * @param \Drupal\Core\Password\PasswordInterface $passwordService
   *   The PasswordInterface.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    Connection $database,
    TimeInterface $time,
    PasswordInterface $passwordService
  ) {
    $this->userStore = $entityTypeManager->getStorage('user');
    $this->database = $database;
    $this->time = $time;
    $this->passwordService = $passwordService;
  }

  /**
   * Initially import all user password hashes.
   */
  public function initialImport(): void {
    $users = $this->userStore->loadMultiple();

    /** @var \Drupal\user\UserInterface $user */
    foreach ($users as $user) {
      $hashedPassword = $user->getPassword();
      if ($hashedPassword) {
        $values = [
          $user->id(),
          $hashedPassword,
          $this->time->getRequestTime(),
        ];

        $this->database->insert(self::TABLE)
          ->fields(['uid', 'pass_hash', 'timestamp'], $values)
          ->execute();
      }
    }
  }

  /**
   * Import a single user password hash.
   *
   * @param int $uid
   *   The ID of the current user.
   * @param string $password
   *   The password to store.
   * @param bool $isHashed
   *   Indicates whether we still need to hash the password.
   *
   * @throws \Exception
   */
  public function insertPasswordHash(int $uid, string $password, bool $isHashed = FALSE): void {
    $values = [
      $uid,
      !$isHashed ? $this->passwordService->hash($password) : $password,
      $this->time->getRequestTime(),
    ];

    $this->database->insert(self::TABLE)
      ->fields(['uid', 'pass_hash', 'timestamp'], $values)
      ->execute();

    $this->keepMaxHistory($uid);
  }

  /**
   * Delete all password history entries for a user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user whose history we want to delete.
   */
  public function deleteUserEntry(AccountInterface $account): void {
    $this->database->delete(self::TABLE)
      ->condition('uid', $account->id())
      ->execute();
  }

  /**
   * Check if a given password was already used the last 8 times.
   *
   * @param int $uid
   *   The ID of the current user.
   * @param string $password
   *   The password to check.
   *
   * @return bool
   *   Whether or not the password is in the history.
   */
  public function isPasswordInHistory(int $uid, string $password): bool {
    $hashes = $this->database->select(self::TABLE, 'ph')
      ->fields('ph', ['pass_hash'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchAll();

    foreach ($hashes as $hash) {
      if ($this->passwordService->check($password, $hash->pass_hash)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Keep a maximum of 8 entries for a given user in the password history table.
   *
   * @param int $uid
   *   The ID of the current user.
   */
  private function keepMaxHistory(int $uid): void {
    $entries = $this->getAllUserEntries($uid);
    if (count($entries) > self::MAX_HISTORY_ENTRIES) {
      // Delete the very last entry in the database.
      $deleteEntry = array_pop($entries);
      $this->database->delete(self::TABLE)
        ->condition('id', $deleteEntry->id)
        ->execute();
    }
  }

  /**
   * Get password history entries for a given user.
   *
   * @param int $uid
   *   The ID of the current user.
   *
   * @return array
   *   An array of password history entries.
   */
  private function getAllUserEntries(int $uid): array {
    return $this->database->select(self::TABLE, 'ph')
      ->fields('ph', ['id', 'pass_hash', 'timestamp'])
      ->orderBy('timestamp', 'DESC')
      ->condition('uid', $uid)
      ->execute()
      ->fetchAll();
  }

}
